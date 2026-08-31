@if(isset($globalPoll) && $globalPoll && Auth::check() && !$hasVotedPoll)

<div id="pollNotice" class="poll-notice mt-2">

    <div class="poll-glow"></div>

    <div class="poll-notice-inner">

        {{-- LEFT --}}
        <div class="poll-left">

            <div class="poll-icon-wrap">

                <span class="poll-icon">📊</span>

            </div>

            <div class="poll-text">

                <div class="poll-label">

                    Community Poll

                </div>

                <div class="poll-title">

                    {{ $globalPoll->title }}

                </div>

            </div>

        </div>

        {{-- RIGHT --}}
        <div class="poll-right">

            <span class="poll-votes">

                {{ $globalPoll->votes_count }} votes

            </span>

            <a href="{{ route('home') }}"
               class="poll-btn">

                Vote Now

            </a>

        </div>

    </div>

</div>

<style>

/* =========================================
   MAIN
========================================= */

.poll-notice{

    position:relative;

    overflow:hidden;

    max-width:920px;

    margin:0 auto;

    padding:1rem 1.15rem;

    border-radius:20px;

    background:
        linear-gradient(
            135deg,
            rgba(59,130,246,.16),
            rgba(139,92,246,.12)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(12px);

    box-shadow:
        0 12px 30px rgba(0,0,0,.18);

    transition:
        all .3s ease;
}

/* =========================================
   GLOW
========================================= */

.poll-glow{

    position:absolute;

    top:-90px;
    right:-90px;

    width:220px;
    height:220px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(96,165,250,.18),
            transparent 70%
        );

    pointer-events:none;
}

/* =========================================
   COMPACT
========================================= */

.poll-notice.compact{

    padding:.65rem .9rem;
}

.poll-notice.compact .poll-label,
.poll-notice.compact .poll-votes{

    display:none;
}

.poll-notice.compact .poll-title{

    font-size:.9rem;
}

.poll-notice.compact .poll-btn{

    padding:.4rem .8rem;

    font-size:.76rem;
}

/* =========================================
   LAYOUT
========================================= */

.poll-notice-inner{

    position:relative;

    z-index:2;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:1rem;

    flex-wrap:wrap;
}

.poll-left{

    display:flex;

    align-items:center;

    gap:.9rem;

    min-width:0;
}

.poll-right{

    display:flex;

    align-items:center;

    gap:.75rem;
}

/* =========================================
   ICON
========================================= */

.poll-icon-wrap{

    width:46px;
    height:46px;

    border-radius:15px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        rgba(255,255,255,.08);

    box-shadow:
        inset 0 0 10px rgba(255,255,255,.1);
}

.poll-icon{

    font-size:1.2rem;

    animation:pollPulse 2s infinite ease-in-out;
}

@keyframes pollPulse{

    0%{
        transform:scale(1);
    }

    50%{
        transform:scale(1.18);
    }

    100%{
        transform:scale(1);
    }

}

/* =========================================
   TEXT
========================================= */

.poll-text{

    min-width:0;
}

.poll-label{

    color:rgba(255,255,255,.6);

    font-size:.72rem;

    text-transform:uppercase;

    letter-spacing:.6px;

    margin-bottom:2px;
}

.poll-title{

    color:#fff;

    font-size:1rem;

    font-weight:700;

    line-height:1.3;

    overflow:hidden;

    text-overflow:ellipsis;

    white-space:nowrap;

    max-width:480px;
}

/* =========================================
   VOTES
========================================= */

.poll-votes{

    padding:.45rem .8rem;

    border-radius:999px;

    background:
        rgba(255,255,255,.08);

    color:#dbeafe;

    font-size:.82rem;

    font-weight:600;

    white-space:nowrap;
}

/* =========================================
   BUTTON
========================================= */

.poll-btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    padding:.55rem 1rem;

    border-radius:12px;

    text-decoration:none;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:#fff;

    font-size:.82rem;

    font-weight:700;

    transition:
        all .2s ease;
}

.poll-btn:hover{

    transform:translateY(-2px);

    color:#fff;

    box-shadow:
        0 8px 20px rgba(59,130,246,.3);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .poll-notice{

        border-radius:18px;

        padding:.9rem 1rem;
    }

    .poll-notice-inner{

        align-items:flex-start;
    }

    .poll-right{

        width:100%;

        justify-content:flex-start;
    }

    .poll-title{

        max-width:100%;

        white-space:normal;
    }

}

</style>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const poll = document.getElementById("pollNotice");

    if (!poll) return;

    let shrinkTimer;

    /* =========================================
       SHRINK
    ========================================= */

    function shrinkPoll(){

        poll.classList.add("compact");

    }

    function expandPoll(){

        poll.classList.remove("compact");

    }

    function scheduleShrink(){

        clearTimeout(shrinkTimer);

        shrinkTimer = setTimeout(shrinkPoll, 3000);

    }

    /* =========================================
       INIT
    ========================================= */

    scheduleShrink();

    /* =========================================
       HOVER
    ========================================= */

    poll.addEventListener("mouseenter", function(){

        expandPoll();

        clearTimeout(shrinkTimer);

    });

    poll.addEventListener("mouseleave", function(){

        scheduleShrink();

    });

});

</script>

@endif