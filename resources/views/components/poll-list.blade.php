
<div class="mt-2">

    @if($polls->isEmpty())

        <div class="modern-poll-empty">

            <div class="poll-empty-glow"></div>

            <div class="position-relative z-2 text-center">

                <div class="poll-empty-icon">
                    <i class="bi bi-bar-chart-line-fill"></i>
                </div>

                <h5 class="theme-text mb-2">
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


                    {{-- ADMIN ACTIONS --}}
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
                                        onclick="return confirm('Are you sure?');"
                                        title="Delete Poll">

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

                        <span>
                            {{ $poll->description }}
                        </span>

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
   Larger typography
   Matches the established forum UI
   ========================================================= */


/* =========================================================
   CARD
   ========================================================= */

.modern-poll-card,
.modern-poll-empty {

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            var(--theme-surface, rgba(14,21,33,.95)),
            var(--theme-surface, rgba(10,15,27,.84))
        );

    border: 1px solid var(--ui-border);

    border-radius: 1rem;

    box-shadow:
        0 10px 26px var(--theme-shadow, rgba(0, 0, 0, .16)),
        inset 0 1px 0 var(--theme-shadow, rgba(255, 255, 255, .025));

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
            var(--theme-surface-alt, rgba(255,255,255,0.049)),
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
            var(--theme-teal-action, var(--ui-accent)),
            var(--theme-teal-action, var(--ui-accent-strong))
        );

    border-radius: 0 4px 4px 0;

    opacity: .75;

    pointer-events: none;
}


.modern-poll-card:hover {

    transform: translateY(-2px);

    border-color: var(--theme-teal-border, rgba(99, 210, 198, .20));

    box-shadow:
        0 14px 32px var(--theme-shadow, rgba(0, 0, 0, .22)),
        0 0 0 1px var(--theme-shadow, rgba(99, 210, 198, .025));
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
            var(--theme-teal-soft, rgba(99, 210, 198, .055)),
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
        1px solid var(--theme-border, rgba(148, 163, 184, .08));
}


.poll-icon-box {

    display: flex;

    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    color: var(--ui-accent);

    background:
        var(--theme-teal-soft, rgba(99, 210, 198, .075));

    border:
        1px solid var(--theme-teal-border, rgba(99, 210, 198, .14));

    border-radius: .65rem;

    font-size: 1.1rem;

    box-shadow:
        inset 0 1px 0 var(--theme-shadow, rgba(255, 255, 255, .025));

    transition:
        background 160ms ease,
        border-color 160ms ease;
}


.modern-poll-card:hover .poll-icon-box {

    background: var(--theme-teal-soft, rgba(99, 210, 198, .11));

    border-color: var(--theme-teal-border, rgba(99, 210, 198, .22));
}


.modern-poll-title {

    margin: 0;

    color: var(--theme-text, #f1f5f9);

    font-size: 1.15rem;

    font-weight: 800;

    line-height: 1.4;

    overflow-wrap: anywhere;
}


.modern-poll-meta {

    display: flex;

    align-items: center;
    flex-wrap: wrap;

    gap: .45rem .9rem;

    margin-top: .4rem;

    color: var(--theme-muted, #71859b);

    font-size: var(--site-font-body, 13px);
}


.modern-poll-meta span {

    display: inline-flex;

    align-items: center;

    gap: .3rem;
}


.modern-poll-meta i {

    color: var(--theme-muted, #60758b);

    font-size: .82rem;
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

    width: 34px;
    height: 34px;

    padding: 0;

    color: var(--theme-muted, #8295aa);

    background:
        var(--theme-surface-alt, rgba(148,163,184,0.0315));

    border:
        1px solid var(--theme-border, rgba(148, 163, 184, .09));

    border-radius: .45rem;

    font-size: var(--site-font-body, 13px);

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

    background: var(--theme-teal-soft, rgba(99, 210, 198, .07));

    border-color: var(--theme-teal-border, rgba(99, 210, 198, .17));

    transform: translateY(-1px);
}


.poll-action-btn.danger-btn:hover {

    color: var(--theme-text, #ffc4c8);

    background: var(--theme-red-soft, rgba(220, 53, 69, .09));

    border-color: var(--theme-red-border, rgba(220, 53, 69, .18));
}


.poll-action-btn.success-btn:hover {

    color:  var(--theme-text, #c9f3ee);

    background: var(--theme-teal-soft, rgba(99, 210, 198, .07));

    border-color: var(--theme-teal-border, rgba(99, 210, 198, .17));
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

    padding: .75rem .9rem;

    color:  var(--theme-muted, #a9bacb);

    background:
        var(--theme-teal-soft, rgba(99, 210, 198, .045));

    border:
        1px solid var(--theme-teal-border, rgba(99, 210, 198, .10));

    border-radius: .6rem;

    font-size: var(--site-font-body, 13px);

    line-height: 1.65;
}


.modern-poll-description i {

    flex-shrink: 0;

    color: var(--ui-accent);

    margin-top: .15rem;

    font-size: .95rem;
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

    color: var(--theme-text, #e5edf7);

    font-size: var(--site-font-body, 13px);

    font-weight: 800;

    margin-bottom: .75rem;
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

    gap: .55rem;

    margin-bottom: .9rem;
}


.modern-poll-option {

    display: block;

    padding: .75rem .85rem;

    color: var(--theme-text, #d5e0eb);

    background:
        var(--theme-surface-alt, rgba(148,163,184,0.0245));

    border:
        1px solid var(--theme-border, rgba(148, 163, 184, .09));

    border-radius: .6rem;

    cursor: pointer;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}


.modern-poll-option:hover {

    color:  var(--theme-text, #eef6fb);

    background:
        var(--theme-teal-soft, rgba(99, 210, 198, .055));

    border-color:
        var(--theme-teal-border, rgba(99, 210, 198, .15));

    transform: translateX(2px);
}


.modern-poll-option:has(.modern-radio:checked) {

    background:
        var(--theme-teal-soft, rgba(99, 210, 198, .075));

    border-color:
        var(--theme-teal-border, rgba(99, 210, 198, .22));
}


.modern-radio {

    width: 17px;
    height: 17px;

    flex-shrink: 0;

    margin: 0 .65rem 0 0;

    accent-color: var(--ui-accent);
}


.option-text {

    color: var(--theme-text, #d9e4ee);

    font-size: var(--site-font-body, 13px);

    font-weight: 650;

    line-height: 1.5;
}


/* =========================================================
   VOTE BUTTON
   ========================================================= */

.modern-vote-btn {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 40px;

    padding: .5rem .9rem;

    color: var(--theme-on-action, #062523);

    background:
        linear-gradient(
            135deg,
            var(--theme-teal-action, var(--ui-accent)),
            var(--theme-teal-action, var(--ui-accent-strong))
        );

    border:
        1px solid var(--theme-teal-border, rgba(99, 210, 198, .22));

    border-radius: .5rem;

    font-size: var(--site-font-body, 13px);

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 4px 12px var(--theme-shadow, rgba(0, 0, 0, .14));

    transition:
        transform 160ms ease,
        filter 160ms ease,
        box-shadow 160ms ease;
}


.modern-vote-btn:hover {

    color: var(--theme-text, #062523);

    transform: translateY(-1px);

    filter: brightness(1.04);

    box-shadow:
        0 6px 16px var(--theme-shadow, rgba(0, 0, 0, .18));
}


/* =========================================================
   RESULTS
   ========================================================= */

.modern-result-item {
    margin-bottom: 1rem;
}


.modern-result-item:last-child {
    margin-bottom: 0;
}


.result-option-name {

    color: var(--theme-text, #dce7f2);

    font-size: var(--site-font-body, 13px);

    font-weight: 750;

    line-height: 1.45;

    overflow-wrap: anywhere;
}


.result-option-name.selected-option {
    color: var(--ui-accent);
}


.result-option-name i {

    color: var(--ui-accent);

    font-size: .85rem;
}


.result-votes {

    flex-shrink: 0;

    color: var(--theme-muted, #71859b);

    font-size: var(--site-font-body, 13px);

    font-weight: 650;

    margin-left: .75rem;
}


.modern-progress {

    overflow: hidden;

    height: 9px;

    margin-top: .4rem;

    background:
        var(--theme-surface-alt, rgba(148,163,184,0.049));

    border:
        1px solid var(--theme-border, rgba(148, 163, 184, .05));

    border-radius: 999px;
}


.modern-progress-bar {

    height: 100%;

    min-width: 0;

    border-radius: inherit;

    background:
        linear-gradient(
            90deg,
            var(--theme-teal-soft, rgba(99, 210, 198, .65)),
            var(--theme-teal-action, var(--ui-accent))
        );

    transition: width 450ms ease;
}


.modern-progress-bar.winner-progress {

    background:
        linear-gradient(
            90deg,
            var(--theme-teal-soft, rgba(99, 210, 198, .72)),
            var(--theme-teal-action, var(--ui-accent-strong))
        );
}


.modern-progress-bar.selected-progress {

    background:
        linear-gradient(
            90deg,
            var(--theme-teal-action, var(--ui-accent-strong)),
            var(--theme-teal-action, var(--ui-accent))
        );

    box-shadow:
        0 0 10px var(--theme-shadow, rgba(99, 210, 198, .12));
}


.modern-empty-votes {

    padding: .8rem .9rem;

    color: var(--theme-muted, #71859b);

    background:
        var(--theme-surface-alt, rgba(148,163,184,0.0245));

    border:
        1px solid var(--theme-border, rgba(148, 163, 184, .08));

    border-radius: .6rem;

    text-align: center;

    font-size: var(--site-font-body, 13px);

    line-height: 1.5;
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

    width: 52px;
    height: 52px;

    margin: 0 auto .8rem;

    color: var(--ui-accent);

    background:
        var(--theme-teal-soft, rgba(99, 210, 198, .075));

    border:
        1px solid var(--theme-teal-border, rgba(99, 210, 198, .14));

    border-radius: .75rem;

    font-size: 1.15rem;
}


.modern-poll-empty h5 {

    color: var(--theme-text, #f1f5f9);

    font-size: 1.1rem;

    font-weight: 800;
}


.modern-poll-empty p {

    color: var(--theme-muted, #71859b) !important;

    font-size: var(--site-font-body, 13px);

    line-height: 1.55;
}


/* =========================================================
   CREATE POLL BUTTON
   ========================================================= */

.modern-poll-create-btn {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 40px;

    padding: .5rem .85rem;

    color: var(--theme-on-action, #062523);

    background:
        linear-gradient(
            135deg,
            var(--theme-teal-action, var(--ui-accent)),
            var(--theme-teal-action, var(--ui-accent-strong))
        );

    border:
        1px solid var(--theme-teal-border, rgba(99, 210, 198, .22));

    border-radius: .5rem;

    font-size: var(--site-font-body, 13px);

    font-weight: 800;

    text-decoration: none;

    box-shadow:
        0 4px 12px var(--theme-shadow, rgba(0, 0, 0, .14));

    transition:
        transform 160ms ease,
        filter 160ms ease,
        box-shadow 160ms ease;
}


.modern-poll-create-btn:hover {

    color: var(--theme-text, #062523);

    transform: translateY(-1px);

    filter: brightness(1.04);

    box-shadow:
        0 6px 16px var(--theme-shadow, rgba(0, 0, 0, .18));
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

        font-size: var(--site-font-body, 13px);

    }


    .modern-poll-actions {

        width: auto;

    }


    .modern-poll-title {

        font-size: 1.05rem;

    }


    .modern-poll-meta {

        font-size: var(--site-font-body, 13px);

        gap: .35rem .7rem;

    }


    .poll-icon-box {

        width: 38px;
        height: 38px;

        flex-basis: 38px;

        font-size: 1rem;

    }


    .modern-poll-option {

        padding: .7rem .75rem;

    }


    .option-text {

        font-size: var(--site-font-body, 13px);

    }


    .poll-select-title {

        font-size: var(--site-font-body, 13px);

    }


    .result-option-name {

        font-size: var(--site-font-body, 13px);

    }


    .result-votes {

        font-size: var(--site-font-body, 13px);

    }


    .modern-poll-empty {

        padding: 1.5rem 1rem;

    }


    .modern-poll-empty h5 {

        font-size: 1.05rem;

    }


    .modern-poll-empty p {

        font-size: var(--site-font-body, 13px);

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

        font-size: var(--site-font-body, 13px);

    }


    .option-text {

        font-size: var(--site-font-body, 13px);

    }

}

</style>
