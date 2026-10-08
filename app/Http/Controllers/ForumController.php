<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\User;
use App\Notifications\ForumMentionNotification;
use App\Notifications\ForumReplyNotification;
use App\Services\ForumAccess;
use App\Services\ForumReadService;
use App\Services\ForumRenderer;
use App\Services\ForumService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function __construct(private ForumService $forumService) {}

    private function verifyTopic(ForumCategory $category, ForumTopic $topic): void
    {
        ForumAccess::authorizeView($category);
        abort_unless($topic->category_id === $category->id, 404);
    }

    private function verifyPost(ForumCategory $category, ForumTopic $topic, ForumPost $post): void
    {
        $this->verifyTopic($category, $topic);
        abort_unless($post->topic_id === $topic->id, 404);
    }

    public function index()
    {
        $staff = ForumAccess::allows(auth()->user(), 'manage_topics');
        $categories = $this->forumService->categories(includePrivate: $staff);
        $deletedCategories = ForumAccess::allows(auth()->user(), 'delete_categories')
            ? $this->forumService->categories(deleted: true) : collect();

        return view('forum.index', compact('categories', 'deletedCategories'));
    }

    public function category(Request $request, ForumCategory $category)
    {
        ForumAccess::authorizeView($category);
        $sort = $request->query('sort', 'latest');
        if (! is_string($sort) || ! in_array($sort, ['latest', 'created', 'views', 'replies'], true)) {
            $sort = 'latest';
        }
        $topics = $this->forumService->categoryTopics($category, $sort, max(1, Paginator::resolveCurrentPage()));
        $unreadCounts = auth()->check()
            ? (new ForumReadService)->unread(auth()->id())->whereIn('topic_id', $topics->pluck('id'))
                ->selectRaw('topic_id, COUNT(*) as unread_count')->groupBy('topic_id')->pluck('unread_count', 'topic_id')
            : collect();
        foreach ($topics as $topic) {
            $topic->new_replies_count = (int) $unreadCounts->get($topic->id, 0);
        }
        $topics->appends(['sort' => $sort]);

        return view('forum.category', compact('category', 'topics', 'sort'));
    }

    public function create(ForumCategory $category)
    {
        ForumAccess::authorizeView($category);
        ForumAccess::authorizeParticipation();
        ForumAccess::authorize('create_topics');

        return view('forum.create', compact('category'));
    }

    public function store(Request $request, ForumCategory $category)
    {
        ForumAccess::authorizeView($category);
        ForumAccess::authorizeParticipation();
        ForumAccess::authorize('create_topics');
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body' => ['required', 'string', 'min:3', 'max:10000'],
        ]);
        $topic = DB::transaction(function () use ($category, $validated) {
            // Serialize topic creation within this category, including slug allocation.
            $currentCategory = ForumCategory::whereKey($category->id)->lockForUpdate()->firstOrFail();
            ForumAccess::authorizeView($currentCategory);
            $base = substr(Str::slug($validated['title']) ?: 'discussion', 0, 230);
            if ($base === 'create') {
                $base = 'create-topic';
            }
            $slug = $base;
            for ($counter = 2; ForumTopic::where('category_id', $category->id)->where('slug', $slug)->exists(); $counter++) {
                $slug = $base.'-'.$counter;
            }
            $topic = ForumTopic::create([
                'category_id' => $category->id, 'user_id' => auth()->id(),
                'title' => $validated['title'], 'slug' => $slug,
            ]);
            $post = $topic->posts()->create(['user_id' => auth()->id(), 'body' => $validated['body']]);
            $topic->update(['last_post_id' => $post->id]);
            DB::afterCommit(fn () => $this->notifyParticipants($post, false));

            return $topic;
        }, 3);

        return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug])
            ->with('success', 'Topic created successfully.')->with('forum_draft_saved', 'create:'.$category->id);
    }

    public function topic(Request $request, ForumCategory $category, ForumTopic $topic)
    {
        $this->verifyTopic($category, $topic);
        $request->validate(['post' => ['nullable', 'integer', 'min:1'], 'unread' => ['nullable', 'boolean']]);
        if ($request->boolean('unread')) {
            abort_unless(auth()->check(), 403);
            $targetId = (new ForumReadService)->unread(auth()->id())->where('topic_id', $topic->id)
                ->oldest('created_at')->oldest('id')->value('id');
            if ($targetId) {
                return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $targetId]);
            }

            return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug])
                ->with('success', 'You are up to date with this topic.');
        }
        if ($request->filled('post')) {
            $firstPostId = $topic->posts()->oldest('id')->value('id');
            $target = $topic->posts()->findOrFail($request->query('post'));
            $page = 1;
            if ($target->id !== $firstPostId) {
                $newer = $topic->posts()->where('id', '!=', $firstPostId)->where(function ($query) use ($target) {
                    $query->where('created_at', '>', $target->created_at)->orWhere(function ($query) use ($target) {
                        $query->where('created_at', $target->created_at)->where('id', '>', $target->id);
                    });
                })->count();
                $page = intdiv($newer, 15) + 1;
            }
            session()->reflash();

            return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'page' => $page])
                ->withFragment('post-'.$target->id);
        }
        $viewKey = 'forum_topic_view_'.$topic->id;
        if (now()->timestamp - (int) session($viewKey, 0) > 86400) {
            ForumTopic::withoutTimestamps(fn () => $topic->increment('views'));
            session([$viewKey => now()->timestamp]);
        }
        $postData = $this->forumService->topicPosts($topic, max(1, Paginator::resolveCurrentPage()));
        $firstPost = $postData['firstPost'];
        $topic->setRelation('user', $postData['author']);
        $replies = $postData['replies'];
        $latestReplyPage = $replies->total() > 0 ? 1 : 0;
        $displayed = collect([$firstPost])->concat($replies->getCollection())->filter();
        $renderer = new ForumRenderer;
        $renderer->prepareMentions($displayed);
        $isFollowing = auth()->check() && $topic->subscriptions()->where('user_id', auth()->id())->exists();
        // Render before marking posts read: a failed response must not consume unread posts.
        $html = view('forum.topic', compact('category', 'topic', 'firstPost', 'replies', 'isFollowing', 'latestReplyPage', 'renderer'))->render();
        if (auth()->check()) {
            (new ForumReadService)->markDisplayed(auth()->id(), $displayed);
        }

        return response($html);
    }

    public function preview(Request $request)
    {
        ForumAccess::authorizeParticipation();
        $validated = $request->validate(['body' => ['nullable', 'string', 'max:10000']]);
        $renderer = new ForumRenderer;
        $renderer->prepareMentions(collect([(object) ['body' => $validated['body'] ?? '']]));

        return response()->json(['html' => $renderer->render($validated['body'] ?? '')]);
    }

    public function reply(Request $request, ForumCategory $category, ForumTopic $topic)
    {
        $this->verifyTopic($category, $topic);
        ForumAccess::authorizeParticipation();
        ForumAccess::authorize('reply');
        $validated = $request->validate(['body' => ['required', 'string', 'min:3', 'max:10000']]);
        $post = DB::transaction(function () use ($topic, $validated) {
            $locked = ForumTopic::whereKey($topic->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->is_locked, 403, 'This topic is locked.');
            $post = $locked->posts()->create(['user_id' => auth()->id(), 'body' => $validated['body']]);
            $locked->update(['last_post_id' => $post->id]);
            DB::afterCommit(fn () => $this->notifyParticipants($post, true));

            return $post;
        }, 3);

        return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $post->id])
            ->with('success', 'Reply posted successfully.')->with('forum_draft_saved', 'reply:'.$topic->id);
    }

    public function toggleLock(ForumCategory $category, ForumTopic $topic)
    {
        return $this->toggle($category, $topic, 'is_locked', 'Topic lock updated.');
    }

    public function togglePin(ForumCategory $category, ForumTopic $topic)
    {
        return $this->toggle($category, $topic, 'is_pinned', 'Topic pin updated.');
    }

    private function toggle(ForumCategory $category, ForumTopic $topic, string $field, string $message)
    {
        $this->verifyTopic($category, $topic);
        ForumAccess::authorize('manage_topics');
        DB::transaction(function () use ($topic, $field) {
            $current = ForumTopic::whereKey($topic->id)->lockForUpdate()->firstOrFail();
            $current->update([$field => ! $current->{$field}]);
        }, 3);

        return back()->with('success', $message);
    }

    public function deleteTopic(ForumCategory $category, ForumTopic $topic)
    {
        $this->verifyTopic($category, $topic);
        ForumAccess::authorize('delete_topics');
        DB::transaction(fn () => ForumTopic::whereKey($topic->id)->lockForUpdate()->firstOrFail()->delete(), 3);

        return redirect()->route('forum.category', $category->slug)->with('success', 'Topic deleted successfully.');
    }

    private function authorizeEdit(ForumPost $post): void
    {
        ForumAccess::authorizeParticipation();
        abort_unless($post->user_id === auth()->id() || ForumAccess::allows(auth()->user(), 'edit_posts'), 403);
    }

    public function editPost(ForumCategory $category, ForumTopic $topic, ForumPost $post)
    {
        $this->verifyPost($category, $topic, $post);
        $this->authorizeEdit($post);

        return view('forum.edit-post', compact('category', 'topic', 'post'));
    }

    public function updatePost(Request $request, ForumCategory $category, ForumTopic $topic, ForumPost $post)
    {
        $this->verifyPost($category, $topic, $post);
        $this->authorizeEdit($post);
        $validated = $request->validate(['body' => ['required', 'string', 'min:3', 'max:10000']]);
        $post->update(['body' => $validated['body'], 'edited_at' => now()]);

        return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $post->id])
            ->with('success', 'Post updated successfully.')->with('forum_draft_saved', 'edit:'.$post->id);
    }

    public function deletePost(ForumCategory $category, ForumTopic $topic, ForumPost $post)
    {
        $this->verifyPost($category, $topic, $post);
        ForumAccess::authorize('delete_posts');
        $deleted = DB::transaction(function () use ($post, $topic) {
            $current = ForumTopic::whereKey($topic->id)->lockForUpdate()->firstOrFail();
            if ($post->id === $current->posts()->oldest('id')->value('id')) {
                return false;
            }
            $post->delete();
            $current->update(['last_post_id' => $current->posts()->latest('id')->value('id')]);

            return true;
        }, 3);
        if (! $deleted) {
            return back()->with('error', 'The main post cannot be deleted. Delete the topic instead.');
        }

        return redirect()->route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug])
            ->with('success', 'Forum post deleted successfully.');
    }

    private function notifyParticipants(ForumPost $post, bool $reply): void
    {
        $text = preg_replace('/\[code(?:=\w+)?\].*?\[\/code\]/is', '', $post->body);
        preg_match_all('/(?<![\pL\pN_@])@(?:"([^"\r\n]{1,100})"|([\pL\pN_][\pL\pN_.-]{0,99}))/u', $text, $matches, PREG_SET_ORDER);
        $names = array_unique(array_map(fn ($match) => $match[1] !== '' ? $match[1] : rtrim($match[2], '.'), $matches));
        $mentions = $names ? User::whereIn('name', $names)->get()->keyBy('id') : collect();
        $post->loadMissing(['user', 'topic.category']);
        $recipients = $mentions;
        if ($reply) {
            $post->topic->loadMissing(['user', 'subscriptions.user']);
            $recipients = $recipients->union($post->topic->subscriptions->pluck('user')->filter()->keyBy('id'));
            if ($post->topic->user) {
                $recipients->put($post->topic->user_id, $post->topic->user);
            }
        }
        foreach ($recipients as $user) {
            if ($user->id === $post->user_id || ! ForumAccess::canView($user, $post->topic->category)) {
                continue;
            }
            $user->notify($mentions->has($user->id) ? new ForumMentionNotification($post) : new ForumReplyNotification($post));
        }
    }

    public function search(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $query = trim($request->query('q') ?? '');
        $results = $this->forumService->search($query, max(1, Paginator::resolveCurrentPage()), ForumAccess::allows(auth()->user(), 'manage_topics'));
        $results->appends(['q' => $query]);

        return view('forum.search', compact('query', 'results'));
    }

    public function myTopics()
    {
        $topics = $this->forumService->participatedTopics(auth()->id(), max(1, Paginator::resolveCurrentPage()), ForumAccess::allows(auth()->user(), 'manage_topics'));

        return view('forum.my-topics', compact('topics'));
    }
}
