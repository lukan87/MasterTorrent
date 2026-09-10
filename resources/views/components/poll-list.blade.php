<div class="mt-2">

    @if($polls->isEmpty())

        <div class="modern-poll-empty">

            <div class="poll-empty-glow"></div>

            <div class="position-relative z-2 text-center">

                <div class="poll-empty-icon">

                    <i class="bi bi-bar-chart-line-fill"></i>

                </div>

                <h5 class="text-white mb-2">

                    No polls available

                </h5>

                <p class="text-muted mb-3">

                    There are currently no active community polls.

                </p>

                @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                    <a href="{{ route('polls.create') }}"
                       class="modern-poll-create-btn">

                        <i class="bi bi-plus-circle-fill me-2"></i>

                        Create New Poll

                    </a>

                @endif

            </div>

        </div>

    @else

        @foreach($pollData as $data)

            @php($poll = $data['poll'])
            @php($userVote = $data['userVote'])
            @php($sortedOptions = $data['sortedOptions'])
            @php($maxVotes = $data['maxVotes'])
            @php($totalVotes = $data['totalVotes'])

            <div class="modern-poll-card">

                {{-- Glow --}}
                <div class="poll-card-glow"></div>

                {{-- HEADER --}}
                <div class="modern-poll-header">

                    <div class="d-flex align-items-center gap-2 flex-grow-1">

                        <div class="poll-icon-box">

                            <i class="bi bi-bar-chart-fill"></i>

                        </div>

                        <div class="flex-grow-1 min-w-0">

                            <h5 class="modern-poll-title">

                                {{ $poll->title }}

                            </h5>

                            <div class="modern-poll-meta">

                                <span>

                                    <i class="bi bi-clock-history"></i>

                                    {{ $poll->created_at->diffForHumans() }}

                                </span>

                                <span>

                                    <i class="bi bi-check2-circle"></i>

                                    {{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }}

                                </span>

                            </div>

                        </div>

                    </div>

                    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                        <div class="modern-poll-actions">

                            <a href="{{ route('polls.show', $poll->id) }}"
                               class="poll-action-btn"
                               title="View Poll">

                                <i class="bi bi-eye-fill"></i>

                            </a>

                            <form action="{{ route('polls.destroy', $poll->id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="poll-action-btn danger-btn"
                                        onclick="return confirm('Are you sure?');">

                                    <i class="bi bi-trash-fill"></i>

                                </button>

                            </form>

                            <a href="{{ route('polls.create') }}"
                               class="poll-action-btn success-btn"
                               title="Create Poll">

                                <i class="bi bi-plus-circle-fill"></i>

                            </a>

                        </div>

                    @endif

                </div>

                {{-- DESCRIPTION --}}
                @if($poll->description)

                    <div class="modern-poll-description">

                        <i class="bi bi-info-circle-fill me-2"></i>

                        {{ $poll->description }}

                    </div>

                @endif

                {{-- BODY --}}
                <div class="modern-poll-body">

                    @if(auth()->check() && !$userVote)

                        <form action="{{ route('polls.vote', $poll->id) }}"
                              method="POST">

                            @csrf

                            <div class="poll-select-title">

                                <i class="bi bi-hand-index-thumb-fill me-2"></i>

                                Select your choice

                            </div>

                            <div class="modern-poll-options">

                                @foreach($sortedOptions as $option)

                                    <label class="modern-poll-option">

                                        <div class="d-flex align-items-center">

                                            <input type="radio"
                                                   name="option_id"
                                                   value="{{ $option->id }}"
                                                   class="modern-radio"
                                                   required>

                                            <span class="option-text">

                                                {{ $option->option_text }}

                                            </span>

                                        </div>

                                    </label>

                                @endforeach

                            </div>

                            <button type="submit"
                                    class="modern-vote-btn">

                                <i class="bi bi-check-circle-fill me-2"></i>

                                Submit Vote

                            </button>

                        </form>

                    @else

                        <div class="poll-select-title mb-3">

                            <i class="bi bi-pie-chart-fill me-2"></i>

                            Poll Results

                        </div>

                        @if($totalVotes === 0)

                            <div class="modern-empty-votes">

                                No votes yet. Be the first to vote!

                            </div>

                        @endif

                        @foreach($sortedOptions as $option)

                            @php($votesCount = $option->votes->count())
                            @php($percentage = $totalVotes ? number_format(($votesCount / $totalVotes) * 100, 1) : 0)
                            @php($isUserChoice = $userVote && $userVote->option_id === $option->id)

                            <div class="modern-result-item">

                                <div class="d-flex justify-content-between align-items-center mb-1">

                                    <div class="result-option-name {{ $isUserChoice ? 'selected-option' : '' }}">

                                        {{ $option->option_text }}

                                        @if($isUserChoice)

                                            <i class="bi bi-check-circle-fill ms-1"></i>

                                        @endif

                                    </div>

                                    <div class="result-votes">

                                        {{ $votesCount }} • {{ $percentage }}%

                                    </div>

                                </div>

                                <div class="modern-progress">

                                    <div class="modern-progress-bar
                                        @if($isUserChoice)
                                            selected-progress
                                        @elseif($votesCount === $maxVotes && $maxVotes > 0)
                                            winner-progress
                                        @endif"
                                         style="width: {{ $percentage }}%">

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    @endif

                </div>

            </div>

        @endforeach

    @endif

</div>

<style>

/* =========================================================
   FILEIPLAY POLLS
   Matches the established forum UI
   ========================================================= */

.modern-poll-card,
.modern-poll-empty {
    position: relative;
    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .95),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 10px 26px rgba(0, 0, 0, .16),
        inset 0 1px 0 rgba(255, 255, 255, .025);

    transition:
        transform 160ms ease,
        border-color 160ms ease,
        box-shadow 160ms ease;
}

.modern-poll-card {
    margin-bottom: 1rem;
}

.modern-poll-card::before,
.modern-poll-empty::before {
    content: "";
    position: absolute;
    top: 0;
    left: 8%;
    right: 8%;
    height: 1px;
    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, .07),
            transparent
        );
    pointer-events: none;
}

.modern-poll-card::after,
.modern-poll-empty::after {
    content: "";
    position: absolute;
    top: 18px;
    bottom: 18px;
    left: 0;
    width: 3px;
    background:
        linear-gradient(
            180deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );
    border-radius: 0 4px 4px 0;
    opacity: .75;
    pointer-events: none;
}

.modern-poll-card:hover {
    transform: translateY(-2px);
    border-color: rgba(99, 210, 198, .20);
    box-shadow:
        0 14px 32px rgba(0, 0, 0, .22),
        0 0 0 1px rgba(99, 210, 198, .025);
}


/* =========================================================
   SUBTLE GLOW
   ========================================================= */

.poll-card-glow,
.poll-empty-glow {
    position: absolute;
    top: -80px;
    right: -80px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background:
        radial-gradient(
            circle,
            rgba(99, 210, 198, .055),
            transparent 70%
        );
    pointer-events: none;
}


/* =========================================================
   HEADER
   ========================================================= */

.modern-poll-header {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    padding: 1rem 1.15rem .9rem;

    border-bottom:
        1px solid rgba(148, 163, 184, .08);
}

.poll-icon-box {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    color: var(--ui-accent);

    background:
        rgba(99, 210, 198, .075);

    border:
        1px solid rgba(99, 210, 198, .14);

    border-radius: .65rem;

    font-size: .95rem;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .025);

    transition:
        background 160ms ease,
        border-color 160ms ease;
}

.modern-poll-card:hover .poll-icon-box {
    background: rgba(99, 210, 198, .11);
    border-color: rgba(99, 210, 198, .22);
}

.modern-poll-title {
    margin: 0;

    color: #f1f5f9;

    font-size: .98rem;
    font-weight: 800;
    line-height: 1.35;

    overflow-wrap: anywhere;
}

.modern-poll-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .45rem .9rem;
    margin-top: .35rem;

    color: #71859b;
    font-size: .72rem;
}

.modern-poll-meta span {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}

.modern-poll-meta i {
    color: #60758b;
    font-size: .7rem;
}


/* =========================================================
   ADMIN ACTIONS
   ========================================================= */

.modern-poll-actions {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-shrink: 0;
}

.poll-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 30px;
    height: 30px;

    padding: 0;

    color: #8295aa;

    background:
        rgba(148, 163, 184, .045);

    border:
        1px solid rgba(148, 163, 184, .09);

    border-radius: .45rem;

    font-size: .72rem;
    text-decoration: none;

    cursor: pointer;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.poll-action-btn:hover {
    color: var(--ui-accent);
    background: rgba(99, 210, 198, .07);
    border-color: rgba(99, 210, 198, .17);
    transform: translateY(-1px);
}

.poll-action-btn.danger-btn:hover {
    color: #ffc4c8;
    background: rgba(220, 53, 69, .09);
    border-color: rgba(220, 53, 69, .18);
}

.poll-action-btn.success-btn:hover {
    color: #c9f3ee;
    background: rgba(99, 210, 198, .07);
    border-color: rgba(99, 210, 198, .17);
}


/* =========================================================
   DESCRIPTION
   ========================================================= */

.modern-poll-description {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: flex-start;

    margin: 1rem 1.15rem 0;
    padding: .65rem .8rem;

    color: #a9bacb;

    background:
        rgba(99, 210, 198, .045);

    border:
        1px solid rgba(99, 210, 198, .10);

    border-radius: .6rem;

    font-size: .78rem;
    line-height: 1.55;
}

.modern-poll-description i {
    flex-shrink: 0;
    color: var(--ui-accent);
    margin-top: .1rem;
}


/* =========================================================
   BODY
   ========================================================= */

.modern-poll-body {
    position: relative;
    z-index: 2;

    padding: 1rem 1.15rem 1.15rem;
}

.poll-select-title {
    color: #e5edf7;

    font-size: .8rem;
    font-weight: 800;
}

.poll-select-title i {
    color: var(--ui-accent);
}


/* =========================================================
   OPTIONS
   ========================================================= */

.modern-poll-options {
    display: flex;
    flex-direction: column;
    gap: .5rem;

    margin-bottom: .9rem;
}

.modern-poll-option {
    display: block;

    padding: .65rem .75rem;

    color: #d5e0eb;

    background:
        rgba(148, 163, 184, .035);

    border:
        1px solid rgba(148, 163, 184, .09);

    border-radius: .6rem;

    cursor: pointer;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.modern-poll-option:hover {
    color: #eef6fb;

    background:
        rgba(99, 210, 198, .055);

    border-color:
        rgba(99, 210, 198, .15);

    transform: translateX(2px);
}

.modern-poll-option:has(.modern-radio:checked) {
    background:
        rgba(99, 210, 198, .075);

    border-color:
        rgba(99, 210, 198, .22);
}

.modern-radio {
    width: 15px;
    height: 15px;

    margin: 0 .55rem 0 0;

    accent-color: var(--ui-accent);
}

.option-text {
    color: #d9e4ee;

    font-size: .8rem;
    font-weight: 650;
}


/* =========================================================
   VOTE BUTTON
   ========================================================= */

.modern-vote-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 36px;

    padding: .45rem .8rem;

    color: #062523;

    background:
        linear-gradient(
            135deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border:
        1px solid rgba(99, 210, 198, .22);

    border-radius: .5rem;

    font-size: .73rem;
    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 4px 12px rgba(0, 0, 0, .14);

    transition:
        transform 160ms ease,
        filter 160ms ease,
        box-shadow 160ms ease;
}

.modern-vote-btn:hover {
    color: #062523;

    transform: translateY(-1px);
    filter: brightness(1.04);

    box-shadow:
        0 6px 16px rgba(0, 0, 0, .18);
}


/* =========================================================
   RESULTS
   ========================================================= */

.modern-result-item {
    margin-bottom: .9rem;
}

.modern-result-item:last-child {
    margin-bottom: 0;
}

.result-option-name {
    color: #dce7f2;

    font-size: .8rem;
    font-weight: 750;

    overflow-wrap: anywhere;
}

.result-option-name.selected-option {
    color: var(--ui-accent);
}

.result-option-name i {
    color: var(--ui-accent);
    font-size: .7rem;
}

.result-votes {
    flex-shrink: 0;

    color: #71859b;

    font-size: .7rem;
    font-weight: 650;
}

.modern-progress {
    overflow: hidden;

    height: 8px;

    margin-top: .35rem;

    background:
        rgba(148, 163, 184, .07);

    border:
        1px solid rgba(148, 163, 184, .05);

    border-radius: 999px;
}

.modern-progress-bar {
    height: 100%;
    min-width: 0;

    border-radius: inherit;

    background:
        linear-gradient(
            90deg,
            rgba(99, 210, 198, .65),
            var(--ui-accent)
        );

    transition: width 450ms ease;
}

.modern-progress-bar.winner-progress {
    background:
        linear-gradient(
            90deg,
            rgba(99, 210, 198, .72),
            var(--ui-accent-strong)
        );
}

.modern-progress-bar.selected-progress {
    background:
        linear-gradient(
            90deg,
            var(--ui-accent-strong),
            var(--ui-accent)
        );

    box-shadow:
        0 0 10px rgba(99, 210, 198, .12);
}

.modern-empty-votes {
    padding: .7rem .8rem;

    color: #71859b;

    background:
        rgba(148, 163, 184, .035);

    border:
        1px solid rgba(148, 163, 184, .08);

    border-radius: .6rem;

    text-align: center;

    font-size: .76rem;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.modern-poll-empty {
    padding: 1.75rem 1.15rem;

    text-align: center;
}

.poll-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 48px;
    height: 48px;

    margin: 0 auto .8rem;

    color: var(--ui-accent);

    background:
        rgba(99, 210, 198, .075);

    border:
        1px solid rgba(99, 210, 198, .14);

    border-radius: .75rem;

    font-size: 1rem;
}

.modern-poll-empty h5 {
    color: #f1f5f9;

    font-size: .95rem;
    font-weight: 800;
}

.modern-poll-empty p {
    color: #71859b !important;

    font-size: .76rem;
}


/* =========================================================
   CREATE POLL BUTTON
   ========================================================= */

.modern-poll-create-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 34px;

    padding: .42rem .75rem;

    color: #062523;

    background:
        linear-gradient(
            135deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border:
        1px solid rgba(99, 210, 198, .22);

    border-radius: .5rem;

    font-size: .73rem;
    font-weight: 800;

    text-decoration: none;

    box-shadow:
        0 4px 12px rgba(0, 0, 0, .14);

    transition:
        transform 160ms ease,
        filter 160ms ease,
        box-shadow 160ms ease;
}

.modern-poll-create-btn:hover {
    color: #062523;

    transform: translateY(-1px);
    filter: brightness(1.04);

    box-shadow:
        0 6px 16px rgba(0, 0, 0, .18);
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767.98px) {

    .modern-poll-header {
        padding: .85rem .9rem .75rem;
    }

    .modern-poll-body {
        padding: .85rem .9rem .95rem;
    }

    .modern-poll-description {
        margin-left: .9rem;
        margin-right: .9rem;
    }

    .modern-poll-actions {
        width: auto;
    }

    .modern-poll-title {
        font-size: .92rem;
    }

    .modern-poll-meta {
        font-size: .68rem;
        gap: .35rem .7rem;
    }

    .poll-icon-box {
        width: 35px;
        height: 35px;
        flex-basis: 35px;
    }

    .modern-poll-option {
        padding: .6rem .7rem;
    }

    .option-text {
        font-size: .77rem;
    }

    .modern-poll-empty {
        padding: 1.5rem 1rem;
    }
}

@media (max-width: 480px) {

    .modern-poll-header {
        align-items: flex-start;
    }

    .modern-poll-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: .2rem;
    }

    .modern-poll-actions {
        margin-left: auto;
    }

    .result-votes {
        font-size: .65rem;
    }
}

</style>