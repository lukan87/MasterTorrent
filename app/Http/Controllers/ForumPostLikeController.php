<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumPostLike;
use App\Models\ForumTopic;
use App\Notifications\ForumLikeNotification;
use App\Services\ForumAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ForumPostLikeController extends Controller
{
    public function toggle(Request $request, ForumCategory $category, ForumTopic $topic, ForumPost $post)
    {
        ForumAccess::authorizeView($category);
        ForumAccess::authorizeParticipation();
        abort_unless($topic->category_id === $category->id && $post->topic_id === $topic->id, 404);
        abort_if($post->user_id === auth()->id(), 403, 'You cannot react to your own post.');
        $validated = $request->validate(['reaction' => ['required', Rule::in(['like', 'love', 'laugh', 'wow', 'sad'])]]);
        $reaction = $validated['reaction'];
        $action = DB::transaction(function () use ($topic, $post, $reaction) {
            ForumTopic::whereKey($topic->id)->lockForUpdate()->firstOrFail();
            ForumPost::whereKey($post->id)->lockForUpdate()->firstOrFail();
            $like = ForumPostLike::where('user_id', auth()->id())->where('post_id', $post->id)->first();
            if ($like) {
                if ($like->reaction === $reaction) {
                    $like->delete();

                    return 'removed';
                }
                $like->update(['reaction' => $reaction]);

                return 'changed';
            }
            ForumPostLike::create(['user_id' => auth()->id(), 'post_id' => $post->id, 'reaction' => $reaction]);
            $actor = auth()->user();
            DB::afterCommit(function () use ($post, $actor) {
                $post->loadMissing('user');
                if ($post->user) {
                    $post->user->notify(new ForumLikeNotification($post, $actor));
                }
            });

            return 'added';
        }, 3);
        if ($request->expectsJson()) {
            $post->load('likes');
            $likes = $post->likes;

            return response()->json([
                'action' => $action, 'user_reaction' => $likes->firstWhere('user_id', auth()->id())?->reaction,
                'counts' => $likes->groupBy('reaction')->map->count()->toArray(), 'total' => $likes->count(),
                'html' => view('forum.partials.reactions', compact('category', 'topic', 'post'))->render(),
            ])->header('Cache-Control', 'private, no-store');
        }

        return back()->with('success', match ($action) {
            'removed' => 'Reaction removed.', 'changed' => 'Reaction changed.', default => 'Reaction added.',
        });
    }
}
