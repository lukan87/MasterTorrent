<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use Illuminate\Http\Request;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use Illuminate\Support\Facades\DB;
use App\Notifications\ForumReplyNotification;
use App\Notifications\ForumMentionNotification;

class ForumController extends Controller
{
    public function index()
{
    $categories = ForumCategory::where('is_private', false)
        ->withCount('topics')
        ->orderBy('position')
        ->get();

    $deletedCategories = collect();

    if (auth()->check() && auth()->user()->user_class > \App\Models\UserClass::ADMIN) {
        $deletedCategories = ForumCategory::onlyTrashed()
            ->withCount('topics')
            ->orderByDesc('deleted_at')
            ->get();
    }

    return view('forum.index', compact('categories', 'deletedCategories'));
}

    public function category(ForumCategory $category)
    {
        if ($category->is_private) {
            abort(403);
        }

$topics = $category->topics()
    ->with([
        'user',
        'lastPost.user',
    ])
    ->withCount('posts')
    ->orderByDesc('is_pinned')
    ->latest('updated_at')
    ->paginate(25);

if (auth()->check()) {

    $topicViews = \App\Models\ForumTopicView::where('user_id', auth()->id())
        ->whereIn('topic_id', $topics->pluck('id'))
        ->get()
        ->keyBy('topic_id');

    foreach ($topics as $topic) {

        $topicView = $topicViews->get($topic->id);

        $topic->new_replies_count = 0;

if ($topicView) {

    $topic->new_replies_count = $topic->posts()
        ->where('created_at', '>', $topicView->updated_at)
        ->count();

}

    }

} else {

    foreach ($topics as $topic) {
       $topic->new_replies_count = 0;
    }

}

        return view('forum.category', compact('category', 'topics'));
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
        ],
    ]);

    $topic = \App\Models\ForumTopic::create([
        'category_id' => $category->id,
        'user_id'     => auth()->id(),
        'title'       => $validated['title'],
        'slug'        => \Illuminate\Support\Str::slug($validated['title']),
    ]);

   $post = ForumPost::create([
    'topic_id' => $topic->id,
    'user_id'  => auth()->id(),
    'body'     => $validated['body'],
]);

$topic->update([
    'last_post_id' => $post->id,
]);

$this->notifyMentionedUsers($post);

    return redirect()
        ->route('forum.topic', [
            'category' => $category->slug,
            'topic'    => $topic->slug,
        ])
        ->with('success', 'Topic created successfully.');
}

public function topic(ForumCategory $category, ForumTopic $topic)
{
    if ($topic->category_id !== $category->id) {
        abort(404);
    }

    $topic->increment('views');

    $topic->load('user');

 if (auth()->check()) {

    $topicView = \App\Models\ForumTopicView::firstOrNew([
        'user_id'  => auth()->id(),
        'topic_id' => $topic->id,
    ]);

    $topicView->touch();

}
    $firstPost = $topic->posts()
        ->with('user')
        ->oldest('id')
        ->first();

    $replies = $topic->posts()
        ->with('user')
        ->when($firstPost, function ($query) use ($firstPost) {
            $query->where('id', '!=', $firstPost->id);
        })
        ->oldest('id')
        ->paginate(20);

        $latestReplyPage = $replies->lastPage();

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
        ],
    ]);

 $post = ForumPost::create([
    'topic_id' => $topic->id,
    'user_id'  => auth()->id(),
    'body'     => $validated['body'],
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

    if (!$subscriber) {
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
            'topic'    => $topic->slug,
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

    if (auth()->user()->user_class <= \App\Models\UserClass::MODERATOR) {
        abort(403, 'You are not allowed to lock or unlock topics.');
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY TOPIC BELONGS TO CATEGORY
    |--------------------------------------------------------------------------
    */

    if ($topic->category_id !== $category->id) {
        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE LOCK
    |--------------------------------------------------------------------------
    */

    $topic->update([
        'is_locked' => !$topic->is_locked,
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

    if (auth()->user()->user_class <= \App\Models\UserClass::MODERATOR) {
        abort(403, 'You are not allowed to pin or unpin topics.');
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY TOPIC BELONGS TO CATEGORY
    |--------------------------------------------------------------------------
    */

    if ($topic->category_id !== $category->id) {
        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE PIN
    |--------------------------------------------------------------------------
    */

    $topic->update([
        'is_pinned' => !$topic->is_pinned,
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

    if (auth()->user()->user_class <= \App\Models\UserClass::MODERATOR) {
        abort(403, 'You are not allowed to delete topics.');
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY TOPIC BELONGS TO CATEGORY
    |--------------------------------------------------------------------------
    */

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
        || $user->user_class > \App\Models\UserClass::MODERATOR
    );

    if (!$canEdit) {
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
        || $user->user_class > \App\Models\UserClass::MODERATOR
    );

    if (!$canEdit) {
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

    if ($user->user_class <= \App\Models\UserClass::MODERATOR) {
        abort(403, 'You are not allowed to delete forum posts.');
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY CATEGORY
    |--------------------------------------------------------------------------
    */

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

    $users = \App\Models\User::whereIn('name', $usernames)->get();


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


}