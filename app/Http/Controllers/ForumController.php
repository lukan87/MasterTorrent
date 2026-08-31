<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use Illuminate\Http\Request;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use Illuminate\Support\Facades\DB;

class ForumController extends Controller
{
    public function index()
    {
        $categories = ForumCategory::where('is_private', false)
            ->withCount('topics')
            ->orderBy('position')
            ->get();

        return view('forum.index', compact('categories'));
    }

    public function category(ForumCategory $category)
    {
        if ($category->is_private) {
            abort(403);
        }

        $topics = $category->topics()
            ->with('user')
            ->withCount('posts')
            ->orderByDesc('is_pinned')
            ->latest('updated_at')
            ->paginate(25);

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

    return view('forum.topic', compact(
        'category',
        'topic',
        'firstPost',
        'replies'
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

    return redirect()
        ->route('forum.topic', [
            'category' => $category->slug,
            'topic'    => $topic->slug,
        ])
        ->with('success', 'Reply posted successfully.');
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


}