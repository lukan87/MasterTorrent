<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PollController extends Controller
{
    /* =========================
     *  LIST POLLS
     * ========================= */
    public function index()
    {
        $user = Auth::user();

        $polls = ($user && $user->user_class >= UserClass::ADMIN)
            ? Poll::withTrashed()->withCount('votes')->latest()->get()
            : Poll::open()->withCount('votes')->latest()->get();

        return view('polls.index', compact('polls'));
    }

    /* =========================
     *  SHOW SINGLE POLL
     * ========================= */
    public function show(Request $request, $id)
    {
        $poll = Poll::with(['options.votes'])->withTrashed()->findOrFail($id);

        $user = $request->user();
        $userVote = $user
            ? $poll->votes()->where('user_id', $user->id)->first()
            : null;

        return view('polls.show', compact('poll', 'user', 'userVote'));
    }

    /* =========================
     *  CREATE POLL FORM
     * ========================= */
    public function create()
    {
        return view('polls.create');
    }

    /* =========================
     *  STORE POLL
     * ========================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at'  => 'nullable|date|after:now',
            'options'     => 'required|array|min:2',
            'options.*'   => 'required|string|max:255',
        ]);

        $poll = Poll::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id'     => $request->user()->id,
            'expires_at'  => $validated['expires_at'] ?? null,
            'is_active'   => true,
        ]);

        foreach ($validated['options'] as $optionText) {
            $poll->options()->create(['option_text' => $optionText]);
        }

        Cache::forget('polls.index');

        return redirect()->route('polls.index')
            ->with('success', 'Poll created successfully.');
    }

    /* =========================
     *  VOTE
     * ========================= */
    public function vote(Request $request, $pollId)
    {
        $user = $request->user();
        $poll = Poll::with('options')->findOrFail($pollId);

        if (! $poll->isOpen()) {
            return back()->with('error', 'This poll is closed.');
        }

        $request->validate([
            'option_id' => 'required|integer',
        ]);

        if (! $poll->options()->where('id', $request->option_id)->exists()) {
            abort(403);
        }

        if (PollVote::where('poll_id', $poll->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You have already voted in this poll.');
        }

        PollVote::create([
            'poll_id'   => $poll->id,
            'option_id' => $request->option_id,
            'user_id'   => $user->id,
        ]);

        Cache::forget("poll.{$poll->id}");

        return back()->with('success', 'Your vote has been counted!');
    }

    /* =========================
     *  EDIT POLL (ADMIN)
     * ========================= */
    public function edit(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $poll = Poll::withTrashed()->findOrFail($id);

        return view('polls.edit', compact('poll'));
    }

    /* =========================
     *  UPDATE POLL (ADMIN)
     * ========================= */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $poll = Poll::with('options')->withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at'  => 'nullable|date',
            'options'     => 'required|array|min:2',
            'options.*'   => 'required|string|max:255',
        ]);

        $poll->update($validated);

        foreach ($poll->options as $option) {
            if (! array_key_exists($option->id, $validated['options'])) {
                $option->delete();
            }
        }

        foreach ($validated['options'] as $optionId => $text) {
            $poll->options()->updateOrCreate(
                ['id' => $optionId],
                ['option_text' => $text]
            );
        }

        Cache::forget("poll.{$poll->id}");
        Cache::forget('polls.index');

        return redirect()->route('polls.index')
            ->with('success', 'Poll updated successfully.');
    }

    /* =========================
     *  TOGGLE OPEN / CLOSE
     * ========================= */
    public function toggle(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $poll = Poll::withTrashed()->findOrFail($id);

        $poll->update([
            'is_active' => ! $poll->is_active,
        ]);

        return back()->with(
            'success',
            $poll->is_active ? 'Poll opened.' : 'Poll closed.'
        );
    }

    /* =========================
     *  SOFT DELETE
     * ========================= */
    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        Poll::findOrFail($id)->delete();

        Cache::forget('polls.index');

        return redirect()->route('polls.index')
            ->with('success', 'Poll deleted successfully.');
    }

    /* =========================
     *  RESTORE
     * ========================= */
    public function restore(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        Poll::withTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'Poll restored successfully.');
    }

    /* =========================
     *  HARD DELETE
     * ========================= */
    public function forceDelete(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        Poll::withTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('polls.index')
            ->with('success', 'Poll permanently deleted.');
    }

    /* =========================
     *  ADMIN CHECK
     * ========================= */
    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()->user_class < UserClass::ADMIN) {
            abort(403);
        }
    }
}
