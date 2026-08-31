<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PollList extends Component
{
    public $polls;
    public $pollData;

    public function __construct($polls)
    {
        $this->polls = $polls;

        // Pre-compute all poll data for the view
        $this->pollData = $polls->map(function ($poll) {
            $userVote = auth()->check() ? $poll->votes->where('user_id', auth()->id())->first() : null;
            $sortedOptions = $poll->options->sortByDesc(fn($o) => $o->votes->count())->values();
            $maxVotes = $sortedOptions->max(fn($o) => $o->votes->count());
            $totalVotes = $poll->votes->count();

            return [
                'poll' => $poll,
                'userVote' => $userVote,
                'sortedOptions' => $sortedOptions,
                'maxVotes' => $maxVotes,
                'totalVotes' => $totalVotes,
            ];
        });
    }

    public function render()
    {
        return view('components.poll-list');
    }
}
