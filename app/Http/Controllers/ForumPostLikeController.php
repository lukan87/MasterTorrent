<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\ForumPostLike;
use App\Notifications\ForumLikeNotification;
use Illuminate\Http\Request;

class ForumPostLikeController extends Controller
{
    /**
     * Like or unlike a forum post. Supports AJAX (JSON) and standard form submission.
     */
    public function toggle(
        Request $request,
        ForumCategory $category,
        ForumTopic $topic,
        ForumPost $post
    ) {
        $reaction = $request->input('reaction', 'like');

        $allowedReactions = ['like', 'love', 'laugh', 'wow', 'sad'];

        if (!in_array($reaction, $allowedReactions, true)) {
            abort(422);
        }

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        if ($post->topic_id !== $topic->id) {
            abort(404);
        }

        // Users cannot react to their own posts.
        if ($post->user_id === auth()->id()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You cannot react to your own post.'], 403);
            }
            return back()->with('error', 'You cannot like your own post.');
        }

        // Check whether the current user already reacted to this post.
        $like = ForumPostLike::where('user_id', auth()->id())
            ->where('post_id', $post->id)
            ->first();

        $action = '';

        if ($like) {
            if ($like->reaction === $reaction) {
                $like->delete();
                $action = 'removed';
            } else {
                $like->update(['reaction' => $reaction]);
                $action = 'changed';
            }
        } else {
            ForumPostLike::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'reaction' => $reaction,
            ]);
            $action = 'added';

            // Notify the post owner.
            $post->loadMissing('user');
            if ($post->user && $post->user_id !== auth()->id()) {
                $post->user->notify(
                    new ForumLikeNotification($post, auth()->user())
                );
            }
        }

        // AJAX: return fresh reaction data as JSON.
        if ($request->ajax()) {
            $freshLikes = ForumPostLike::where('post_id', $post->id)->get();
            $userLike = $freshLikes->firstWhere('user_id', auth()->id());
            $counts = $freshLikes->groupBy('reaction')->map->count();

            return response()->json([
                'action' => $action,
                'user_reaction' => $userLike?->reaction,
                'counts' => $counts->toArray(),
                'total' => $freshLikes->count(),
            ]);
        }

        $flashMsg = match ($action) {
            'removed' => 'Reaction removed.',
            'changed' => 'Reaction changed.',
            default   => 'Post liked.',
        };

        return back()->with('success', $flashMsg);
    }
}
