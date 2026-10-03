<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Torrent;
use App\Models\TorrentMovie;
use App\Models\TorrentRequest;
use App\Models\TorrentSeries;
use App\Models\User;
use App\Services\TorrentActivityNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        abort_if(Auth::user()->commentblock, 403, 'Your account is restricted from commenting.');
        $targets = [TorrentRequest::class => TorrentRequest::class, 'torrent' => Torrent::class, Movie::class => Movie::class, Series::class => Series::class,
            TorrentMovie::class => TorrentMovie::class, TorrentSeries::class => TorrentSeries::class];
        $data = $request->validate([
            'commentable_type' => ['required', Rule::in(array_keys($targets))],
            'commentable_id' => 'required|integer',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'comment' => 'required|string|max:10000',
        ]);
        $target = $targets[$data['commentable_type']]::findOrFail($data['commentable_id']);
        $data['torrent_id'] = $data['commentable_type'] === 'torrent' ? $data['commentable_id'] : null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::findOrFail($data['parent_id']);
            if ($parent->commentable_type !== $data['commentable_type'] || (int) $parent->commentable_id !== (int) $data['commentable_id'] || $parent->parent_id) {
                throw ValidationException::withMessages(['parent_id' => 'Choose a top-level comment in this discussion to reply to.']);
            }
        }
        DB::transaction(function () use ($data, $target) {
            if ($target instanceof TorrentRequest) {
                TorrentRequest::whereKey($target->id)->lockForUpdate()->firstOrFail();
            }
            $user = User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            if (Comment::where('user_id', $user->id)->whereDate('created_at', today())->count() >= 5) {
                throw ValidationException::withMessages(['comment' => 'You can post up to 5 comments or replies per day.']);
            }
            $comment = Comment::create($data + ['user_id' => $user->id]);
            if ($data['commentable_type'] === 'torrent') {
                app(TorrentActivityNotifier::class)->send($target, $user, $comment->id);
            }
            $user->increment('seedbonus');
        });

        return back()->with('success', 'Comment posted. You earned 1 bonus point!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorizeEdit($comment);
        DB::transaction(function () use ($comment) {
            // Preserve replies as standalone comments when their parent is removed.
            $comment->replies()->update(['parent_id' => null]);
            $comment->delete();
        });

        return back()->with('success', 'Comment deleted.');
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorizeEdit($comment);
        abort_if(Auth::user()->commentblock, 403, 'Your account is restricted from commenting.');
        $comment->update($request->validate(['comment' => 'required|string|max:10000']));

        return back()->with('success', 'Comment updated.');
    }

    public function react(Request $request, $id)
    {
        abort_if(Auth::user()->commentblock, 403, 'Your account is restricted from reacting.');
        $data = $request->validate(['reaction' => ['required', Rule::in(array_keys(CommentReaction::TYPES))]]);
        DB::transaction(function () use ($id, $data) {
            $comment = Comment::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if((int) $comment->user_id === (int) Auth::id(), 403, 'You cannot react to your own comment.');
            $reaction = $comment->reactions()->where('user_id', Auth::id())->first();
            if ($reaction && $reaction->reaction === $data['reaction']) {
                $reaction->delete();
            } else {
                $comment->reactions()->updateOrCreate(['user_id' => Auth::id()], $data);
            }
        });

        if ($request->expectsJson()) {
            $reactions = Comment::findOrFail($id)->reactions()->with('user:id,name')->get();

            return response()->json([
                'selected' => $reactions->firstWhere('user_id', Auth::id())?->reaction,
                'reactors' => collect(CommentReaction::TYPES)->map(fn ($emoji, $type) => $reactions->where('reaction', $type)->map(fn ($reaction) => $reaction->user?->name ?? 'Deleted user')->values()),
                'counts' => collect(CommentReaction::TYPES)->map(fn ($emoji, $type) => $reactions->where('reaction', $type)->count()),
            ]);
        }

        return back()->with('success', 'Reaction updated.');
    }

    private function authorizeEdit(Comment $comment): void
    {
        abort_unless((int) $comment->user_id === (int) Auth::id() || Auth::user()->user_class > 5, 403);
    }
}
