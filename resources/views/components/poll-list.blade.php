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

/* =========================================
   EMPTY
========================================= */

.modern-poll-empty{

    position:relative;

    overflow:hidden;

    padding:36px 20px;

    border-radius:18px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(14px);

    text-align:center;

    box-shadow:
        0 10px 28px rgba(0,0,0,.24);
}

.poll-empty-glow{

    position:absolute;

    top:-70px;
    right:-70px;

    width:160px;
    height:160px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.16),
            transparent 70%
        );
}

.poll-empty-icon{

    width:58px;
    height:58px;

    margin:0 auto 14px;

    border-radius:16px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-size:1.2rem;
}

.modern-poll-create-btn{

    display:inline-flex;

    align-items:center;

    padding:10px 16px;

    border-radius:12px;

    text-decoration:none;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-size:1rem;

    font-weight:700;

    transition:.2s ease;
}

.modern-poll-create-btn:hover{

    transform:translateY(-2px);

    color:white;
}

/* =========================================
   POLL CARD
========================================= */

.modern-poll-card{

    position:relative;

    overflow:hidden;

    margin-bottom:18px;

    border-radius:18px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(14px);

    box-shadow:
        0 10px 28px rgba(0,0,0,.22);

    transition:.25s ease;
}

.modern-poll-card:hover{

    transform:translateY(-2px);
}

.poll-card-glow{

    position:absolute;

    top:-80px;
    right:-80px;

    width:180px;
    height:180px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.12),
            transparent 70%
        );
}

/* =========================================
   HEADER
========================================= */

.modern-poll-header{

    position:relative;

    z-index:2;

    padding:18px 20px 16px;

    display:flex;

    justify-content:space-between;

    gap:14px;

    flex-wrap:wrap;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.poll-icon-box{

    width:42px;
    height:42px;

    border-radius:12px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-size:1rem;
}

.modern-poll-title{

    color:white;

    margin:0;

    font-size:1rem;

    font-weight:800;
}

.modern-poll-meta{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    margin-top:4px;

    color:rgba(255,255,255,.55);

    font-size:1rem;
}

/* =========================================
   DESCRIPTION
========================================= */

.modern-poll-description{

    margin:16px 20px 0;

    padding:10px 14px;

    border-radius:12px;

    background:
        rgba(59,130,246,.08);

    border:
        1px solid rgba(59,130,246,.12);

    color:#cbd5e1;

    font-size:1rem;
}

/* =========================================
   BODY
========================================= */

.modern-poll-body{

    position:relative;

    z-index:2;

    padding:18px 20px 20px;
}

.poll-select-title{

    color:white;

    font-size:1rem;

    font-weight:700;

    margin-bottom:14px;
}

/* =========================================
   OPTIONS
========================================= */

.modern-poll-options{

    display:flex;

    flex-direction:column;

    gap:10px;

    margin-bottom:18px;
}

.modern-poll-option{

    padding:12px 14px;

    border-radius:14px;

    cursor:pointer;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.06);

    transition:.2s ease;
}

.modern-poll-option:hover{

    background:
        rgba(59,130,246,.08);

    transform:translateX(2px);
}

.modern-radio{

    width:16px;
    height:16px;

    margin-right:10px;

    accent-color:#3b82f6;
}

.option-text{

    color:#e2e8f0;

    font-size:1rem;

    font-weight:600;
}

/* =========================================
   BUTTON
========================================= */

.modern-vote-btn{

    border:none;

    padding:10px 16px;

    border-radius:12px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-size:1rem;

    font-weight:700;

    transition:.2s ease;
}

.modern-vote-btn:hover{

    transform:translateY(-1px);
}

/* =========================================
   RESULTS
========================================= */

.modern-result-item{

    margin-bottom:16px;
}

.result-option-name{

    color:#e2e8f0;

    font-size:1rem;

    font-weight:700;
}

.selected-option{

    color:#4ade80;
}

.result-votes{

    color:rgba(255,255,255,.6);

    font-size:1rem;
}

.modern-progress{

    overflow:hidden;

    height:10px;

    border-radius:999px;

    background:
        rgba(255,255,255,.06);
}

.modern-progress-bar{

    height:100%;

    border-radius:999px;

    background:
        linear-gradient(
            90deg,
            #3b82f6,
            #8b5cf6
        );

    transition:width .6s ease;
}

.selected-progress{

    background:
        linear-gradient(
            90deg,
            #22c55e,
            #4ade80
        );
}

.winner-progress{

    background:
        linear-gradient(
            90deg,
            #06b6d4,
            #3b82f6
        );
}

.modern-empty-votes{

    padding:12px;

    border-radius:12px;

    text-align:center;

    color:#cbd5e1;

    font-size:1rem;

    background:
        rgba(255,255,255,.04);

    margin-bottom:18px;
}

/* =========================================
   ACTIONS
========================================= */

.modern-poll-actions{

    display:flex;

    gap:6px;
}

.poll-action-btn{

    width:34px;
    height:34px;

    border:none;

    border-radius:10px;

    display:flex;

    align-items:center;
    justify-content:center;

    text-decoration:none;

    background:
        rgba(255,255,255,.06);

    color:#cbd5e1;

    font-size:1rem;

    transition:.2s ease;
}

.poll-action-btn:hover{

    transform:translateY(-1px);

    color:white;
}

.danger-btn:hover{

    background:
        rgba(239,68,68,.2);
}

.success-btn:hover{

    background:
        rgba(34,197,94,.2);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-poll-header,
    .modern-poll-body{

        padding-left:16px;
        padding-right:16px;
    }

    .modern-poll-description{

        margin-left:16px;
        margin-right:16px;
    }

    .modern-poll-actions{

        width:100%;
    }
}

</style>