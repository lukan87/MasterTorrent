<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\ForumPostLike;
use Illuminate\Http\RedirectResponse;
use App\Notifications\ForumLikeNotification;
use Illuminate\Http\Request;

class ForumPostLikeController extends Controller
{
    /**
     * Like or unlike a forum post.
     */
    public function toggle(
    Request $request,
    ForumCategory $category,
    ForumTopic $topic,
    ForumPost $post
): RedirectResponse {

$reaction = $request->input('reaction', 'like');

$allowedReactions = ['like', 'love', 'laugh', 'wow', 'sad'];

if (!in_array($reaction, $allowedReactions, true)) {
    abort(422);
}

        /*
        |--------------------------------------------------------------------------
        | Make sure the topic belongs to the category.
        |--------------------------------------------------------------------------
        */

        if ($topic->category_id !== $category->id) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Make sure the post belongs to the topic.
        |--------------------------------------------------------------------------
        */

        if ($post->topic_id !== $topic->id) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Users cannot like their own posts.
        |--------------------------------------------------------------------------
        */

        if ($post->user_id === auth()->id()) {

            return back()->with(
                'error',
                'You cannot like your own post.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Check whether the current user already liked this post.
        |--------------------------------------------------------------------------
        */

$like = ForumPostLike::where('user_id', auth()->id())
    ->where('post_id', $post->id)
    ->first();

if ($like) {
    if ($like->reaction === $reaction) {
        $like->delete();

        return back()->with('success', 'Reaction removed.');
    }

    $like->update([
        'reaction' => $reaction,
    ]);

    return back()->with('success', 'Reaction changed.');
}

ForumPostLike::create([
    'user_id' => auth()->id(),
    'post_id' => $post->id,
    'reaction' => $reaction,
]);

        /*
|--------------------------------------------------------------------------
| Notify the post owner
|--------------------------------------------------------------------------
*/

$post->loadMissing('user');

if ($post->user && $post->user_id !== auth()->id()) {

    $post->user->notify(
       new ForumLikeNotification($post, auth()->user())
    );

}


        return back()->with(
            'success',
            'Post liked.'
        );
    }
}