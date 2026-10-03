<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\ForumTopicView;
use App\Models\User;
use App\Models\UserClass;
use App\Notifications\ForumMentionNotification;
use App\Notifications\ForumReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function index()
    {
        $categories = ForumCategory::where('is_private', false)
            ->with('latestTopic.lastPost.user')
            ->withCount('topics')
            ->orderBy('position')
            ->get();

        $deletedCategories = collect();

        if (auth()->check() && auth()->user()->user_class > UserClass::ADMIN) {
            $deletedCategories = ForumCategory::onlyTrashed()
                ->withCount('topics')
                ->orderByDesc('deleted_at')
                ->get();
        }

        return view('forum.index', compact('categories', 'deletedCategories'));
    }

    public function category(Request $request, ForumCategory $category)
    {
        if ($category->is_private) {
            abort(403);
        }

        $sort = $request->query('sort', 'latest');

        $allowedSorts = ['latest', 'created', 'views', 'replies'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        $topics = $category->topics()
            ->with([
                'user',
                'lastPost.user',
            ])
            ->withCount('posts')
            ->orderByDesc('is_pinned');

        switch ($sort) {
            case 'created':
                $topics->latest('created_at');
                break;
            case 'views':
                $topics->orderByDesc('views');
                break;
            case 'replies':
                $topics->orderByDesc('posts_count');
                break;
            default:
                $topics->latest('updated_at');
                break;
        }

        $topics = $topics->paginate(25);

        // Count unread replies for the whole page in one query.
        $unreadCounts = auth()->check()
            ? DB::table('forum_posts')
                ->join('forum_topic_views', 'forum_topic_views.topic_id', '=', 'forum_posts.topic_id')
                ->where('forum_topic_views.user_id', auth()->id())
                ->whereIn('forum_posts.topic_id', $topics->pluck('id'))
                ->whereColumn('forum_posts.created_at', '>', 'forum_topic_views.updated_at')
                ->where('forum_posts.user_id', '!=', auth()->id())
                ->groupBy('forum_posts.topic_id')
                ->selectRaw('forum_posts.topic_id, COUNT(*) as unread_count')
                ->pluck('unread_count', 'topic_id')
            : collect();
        foreach ($topics as $topic) {
            $topic->new_replies_count = (int) $unreadCounts->get($topic->id, 0);
        }

        $topics->appends(['sort' => $sort]);

        return view('forum.category', compact('category', 'topics', 'sort'));
    }

    public function create(ForumCategory $category)
    {
        if ($category->is_private) {
            abort(403);
        }

        if (auth()->user()->forumblock) {
            abort(403, 'You are not allowed to post in the forum.');
        }

        return view('forum.create', compact('category'));
    }

    public function store(Request $request, ForumCategory $category)
    {
        if ($category->is_private) {
            abort(403);
        }

        if (auth()->user()->forumblock) {
            abort(403, 'You are not allowed to post in the forum.');
        }

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'body' => [
                'required',
                'string',
                'min:3',
                'max:10000',
            ],
        ]);

        $baseSlug = Str::slug($validated['title']) ?: 'discussion';
        $slug = $baseSlug;
        $counter = 2;

        while (ForumTopic::where('category_id', $category->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $topic = ForumTopic::create([
            'category_id' => $category->id,
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $slug,
        ]);

        $post = ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $topic->update([
            'last_post_id' => $post->id,
        ]);

        $this->notifyMentionedUsers($post);

        return redirect()
            ->route('forum.topic', [
                'category' => $category->slug,
                'topic' => $topic->slug,
            ])
            ->with('success', 'Topic created successfully.');
    }

    public function topic(Request $request, ForumCategory $category, ForumTopic $topic)
    {
        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        $viewKey = 'forum_topic_view_'.$topic->id;
        $lastView = (int) session($viewKey, 0);
        if (now()->timestamp - $lastView > 86400) {
            ForumTopic::withoutTimestamps(fn () => $topic->increment('views'));
            session([$viewKey => now()->timestamp]);
        }

        $topic->load('user');

        if (auth()->check()) {

            $topicView = ForumTopicView::firstOrNew([
                'user_id' => auth()->id(),
                'topic_id' => $topic->id,
            ]);

            $topicView->touch();

        }
        $firstPost = $topic->posts()
            ->with(['user' => fn ($q) => $q->withCount('forumPosts'), 'likes'])
            ->oldest('id')
            ->first();

        // Resolve a post permalink to its page in the newest-first reply list.
        if ($request->filled('post')) {
            $request->validate(['post' => ['required', 'integer', 'min:1']]);
            $target = $topic->posts()->findOrFail($request->query('post'));
            $page = 1;
            if ($target->id !== $firstPost?->id) {
                $newer = $topic->posts()->where('id', '!=', $firstPost?->id)
                    ->where(function ($q) use ($target) {
                        $q->where('created_at', '>', $target->created_at)
                            ->orWhere(function ($q) use ($target) {
                                $q->where('created_at', $target->created_at)->where('id', '>', $target->id);
                            });
                    })->count();
                $page = intdiv($newer, 15) + 1;
            }

            return redirect()->route('forum.topic', [
                'category' => $category->slug, 'topic' => $topic->slug, 'page' => $page,
            ])->withFragment('post-'.$target->id);
        }

        $replies = $topic->posts()
            ->with(['user' => fn ($q) => $q->withCount('forumPosts'), 'likes'])
            ->when($firstPost, function ($query) use ($firstPost) {
                $query->where('id', '!=', $firstPost->id);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        // With newest-first replies, the latest post is always on page 1.
        $latestReplyPage = $replies->total() > 0 ? 1 : 0;

        $isFollowing = false;

        if (auth()->check()) {
            $isFollowing = $topic->subscriptions()
                ->where('user_id', auth()->id())
                ->exists();
        }

        return view('forum.topic', compact(
            'category',
            'topic',
            'firstPost',
            'replies',
            'isFollowing',
            'latestReplyPage'
        ));
    }

    public function reply(
        Request $request,
        ForumCategory $category,
        ForumTopic $topic
    ) {
        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        if ($topic->is_locked) {
            return back()->with('error', 'This topic is locked.');
        }

        if (auth()->user()->forumblock) {
            abort(403, 'You are not allowed to post in the forum.');
        }

        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'min:3',
                'max:10000',
            ],
        ]);

        $post = ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $topic->update([
            'last_post_id' => $post->id,
        ]);

        $this->notifyMentionedUsers($post);

        /*
        |--------------------------------------------------------------------------
        | Notify topic owner
        |--------------------------------------------------------------------------
        |
        | Do not notify the user if they are replying to their own topic.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Notify topic owner and followers
        |--------------------------------------------------------------------------
        |
        | The person who made the reply is never notified.
        | The topic owner is notified automatically.
        | Followers are also notified.
        | Duplicate notifications are prevented.
        |
        */

        $topic->loadMissing([
            'user',
            'subscriptions.user',
        ]);

        $notifiedUserIds = [];

        /*
        |--------------------------------------------------------------------------
        | Notify topic owner
        |--------------------------------------------------------------------------
        */

        if (
            $topic->user &&
            $topic->user_id !== auth()->id()
        ) {

            $topic->user->notify(
                new ForumReplyNotification($post)
            );

            $notifiedUserIds[] = $topic->user_id;
        }

        /*
        |--------------------------------------------------------------------------
        | Notify topic followers
        |--------------------------------------------------------------------------
        */

        foreach ($topic->subscriptions as $subscription) {

            $subscriber = $subscription->user;

            if (! $subscriber) {
                continue;
            }

            /*
             * Don't notify the person who made the reply.
             */
            if ($subscriber->id === auth()->id()) {
                continue;
            }

            /*
             * Don't notify the topic owner twice.
             */
            if (in_array($subscriber->id, $notifiedUserIds)) {
                continue;
            }

            $subscriber->notify(
                new ForumReplyNotification($post)
            );

            $notifiedUserIds[] = $subscriber->id;
        }

        return redirect()
            ->route('forum.topic', [
                'category' => $category->slug,
                'topic' => $topic->slug,
            ])
            ->with('success', 'Reply posted successfully.');
    }

    public function toggleLock(
        ForumCategory $category,
        ForumTopic $topic
    ) {
        /*
        |--------------------------------------------------------------------------
        | STAFF ONLY
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->user_class <= UserClass::MODERATOR) {
            abort(403, 'You are not allowed to lock or unlock topics.');
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY TOPIC BELONGS TO CATEGORY
        |--------------------------------------------------------------------------
        */

        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | TOGGLE LOCK
        |--------------------------------------------------------------------------
        */

        $topic->update([
            'is_locked' => ! $topic->is_locked,
        ]);

        /*
        |--------------------------------------------------------------------------
        | MESSAGE
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            $topic->is_locked
                ? 'Topic locked successfully.'
                : 'Topic unlocked successfully.'
        );
    }

    public function togglePin(
        ForumCategory $category,
        ForumTopic $topic
    ) {
        /*
        |--------------------------------------------------------------------------
        | STAFF ONLY
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->user_class <= UserClass::MODERATOR) {
            abort(403, 'You are not allowed to pin or unpin topics.');
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY TOPIC BELONGS TO CATEGORY
        |--------------------------------------------------------------------------
        */

        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | TOGGLE PIN
        |--------------------------------------------------------------------------
        */

        $topic->update([
            'is_pinned' => ! $topic->is_pinned,
        ]);

        /*
        |--------------------------------------------------------------------------
        | MESSAGE
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            $topic->is_pinned
                ? 'Topic pinned successfully.'
                : 'Topic unpinned successfully.'
        );
    }

    public function deleteTopic(
        ForumCategory $category,
        ForumTopic $topic
    ) {
        /*
        |--------------------------------------------------------------------------
        | STAFF ONLY
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->user_class <= UserClass::MODERATOR) {
            abort(403, 'You are not allowed to delete topics.');
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY TOPIC BELONGS TO CATEGORY
        |--------------------------------------------------------------------------
        */

        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE TOPIC
        |--------------------------------------------------------------------------
        */

        $topic->delete();

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('forum.category', [
                'category' => $category->slug,
            ])
            ->with('success', 'Topic deleted successfully.');
    }

    public function editPost(
        ForumCategory $category,
        ForumTopic $topic,
        ForumPost $post
    ) {
        // Make sure the topic belongs to this category
        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        // Make sure the post belongs to this topic
        if ($post->topic_id !== $topic->id) {
            abort(404);
        }

        $user = auth()->user();

        // Author can edit their own post.
        // Staff above Moderator can edit any post.
        $canEdit = (
            $post->user_id === $user->id
            || $user->user_class > UserClass::MODERATOR
        );

        if (! $canEdit) {
            abort(403, 'You are not allowed to edit this post.');
        }

        return view('forum.edit-post', compact(
            'category',
            'topic',
            'post'
        ));
    }

    public function updatePost(
        Request $request,
        ForumCategory $category,
        ForumTopic $topic,
        ForumPost $post
    ) {
        // Make sure the topic belongs to this category
        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        // Make sure the post belongs to this topic
        if ($post->topic_id !== $topic->id) {
            abort(404);
        }

        $user = auth()->user();

        // Author can edit their own post.
        // Staff above Moderator can edit any post.
        $canEdit = (
            $post->user_id === $user->id
            || $user->user_class > UserClass::MODERATOR
        );

        if (! $canEdit) {
            abort(403, 'You are not allowed to edit this post.');
        }

        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'min:1',
                'max:10000',
            ],
        ]);

        $post->update([
            'body' => $validated['body'],
            'edited_at' => now(),
        ]);

        return redirect()
            ->route('forum.topic', [
                'category' => $category->slug,
                'topic' => $topic->slug,
                'post' => $post->id,
            ])
            ->with('success', 'Post updated successfully.');
    }

    public function deletePost(
        ForumCategory $category,
        ForumTopic $topic,
        ForumPost $post
    ) {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | STAFF ONLY
        |--------------------------------------------------------------------------
        */

        if ($user->user_class <= UserClass::MODERATOR) {
            abort(403, 'You are not allowed to delete forum posts.');
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY CATEGORY
        |--------------------------------------------------------------------------
        */

        abort_if($category->is_private, 403);

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY POST BELONGS TO TOPIC
        |--------------------------------------------------------------------------
        */

        if ($post->topic_id !== $topic->id) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | NEVER DELETE ORIGINAL POST
        |--------------------------------------------------------------------------
        */

        $originalPostId = $topic->posts()
            ->orderBy('id')
            ->value('id');

        if ($post->id === $originalPostId) {
            return back()->with(
                'error',
                'The main post cannot be deleted. Delete the topic instead.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE POST + UPDATE TOPIC
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($post, $topic) {

            $wasLastPost = $topic->last_post_id === $post->id;

            $post->delete();

            if ($wasLastPost) {

                $newLastPost = $topic->posts()
                    ->latest('id')
                    ->first();

                $topic->update([
                    'last_post_id' => $newLastPost?->id,
                ]);
            }
        });

        return redirect()
            ->route('forum.topic', [
                'category' => $category->slug,
                'topic' => $topic->slug,
            ])
            ->with('success', 'Forum post deleted successfully.');
    }

    private function notifyMentionedUsers(ForumPost $post): void
    {
        /*
        |--------------------------------------------------------------------------
        | Find @username mentions
        |--------------------------------------------------------------------------
        */

        preg_match_all(
            '/@([A-Za-z0-9_]+)/',
            $post->body,
            $matches
        );

        if (empty($matches[1])) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove duplicate usernames
        |--------------------------------------------------------------------------
        */

        $usernames = array_unique($matches[1]);

        /*
        |--------------------------------------------------------------------------
        | Load the users
        |--------------------------------------------------------------------------
        */

        $users = User::whereIn('name', $usernames)->get();

        /*
        |--------------------------------------------------------------------------
        | Notify each mentioned user
        |--------------------------------------------------------------------------
        */

        foreach ($users as $user) {

            /*
             * Never notify the person who wrote the post.
             */
            if ($user->id === $post->user_id) {
                continue;
            }

            $user->notify(
                new ForumMentionNotification($post)
            );
        }
    }

    /**
     * Search forum topics and posts.
     */
    public function search(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $query = trim($request->query('q') ?? '');
        $results = collect();

        if (mb_strlen($query) >= 2) {
            $searchTerm = '%'.$query.'%';

            $topics = ForumTopic::visible()->with(['user', 'category'])
                ->withCount('posts')
                ->where('title', 'LIKE', $searchTerm)
                ->latest()
                ->limit(50)
                ->get();

            $posts = ForumPost::whereHas('topic', fn ($q) => $q->visible())
                ->with(['user', 'topic.category'])
                ->where('body', 'LIKE', $searchTerm)
                ->latest()
                ->limit(50)
                ->get();

            $results = $topics->toBase()->concat($posts)->sortByDesc('created_at')->take(50);
        }

        return view('forum.search', compact('query', 'results'));
    }

    /**
     * Show topics that the current user has participated in.
     */
    public function myTopics()
    {
        $user = auth()->user();

        $topicIds = ForumPost::where('user_id', $user->id)
            ->distinct()
            ->pluck('topic_id');

        $topics = ForumTopic::visible()->whereIn('id', $topicIds)
            ->with(['user', 'lastPost.user', 'category'])
            ->withCount('posts')
            ->latest('updated_at')
            ->paginate(25);

        return view('forum.my-topics', compact('topics'));
    }
}
