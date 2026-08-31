<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SnatchCard extends Component
{
    public $history;
    public $type;
    public $requiredSeed;

    public function __construct($history, $type = 'default')
    {
        $this->history = $history;
        $this->type = $type;
        $this->requiredSeed = config('hitrun.seedtime',43200);
    }

    public function render()
    {
        return view('components.snatch-card');
    }
}