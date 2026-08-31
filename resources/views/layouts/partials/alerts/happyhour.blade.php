@if(!empty($currentHappyHour) && $currentHappyHour->isActive())

<div id="happy-hour-notice" class="happy-hour-notice mt-2">

    <div class="happy-hour-glow"></div>

    <div class="happy-hour-inner">

        {{-- LEFT --}}
        <div class="happy-hour-left">

            <div class="happy-hour-icon">

                🎉

            </div>

            <div class="happy-hour-text">

                <div class="happy-hour-title">

                    {{ $currentHappyHour->theme }} Happy Hour

                </div>

                <div class="happy-hour-details">

                    {{ $currentHappyHour->upload_multiplier }}x Upload

                    @if($currentHappyHour->free_download)

                        • Free Downloads

                    @endif

                </div>

            </div>

        </div>

        {{-- RIGHT --}}
        <div class="happy-hour-right">

            <div class="happy-hour-timer-label">

                Ends In

            </div>

            <div class="happy-hour-timer">

                ⏰ <span id="happy-hour-countdown"></span>

            </div>

        </div>

    </div>

    {{-- Progress --}}
    <div class="happy-hour-progress-wrapper">

        <div id="happy-hour-progress"
             class="happy-hour-progress-bar">

        </div>

    </div>

</div>

<style>

/* =========================================
   MAIN
========================================= */

.happy-hour-notice{

    position:sticky;

    top:10px;

    z-index:1000;

    overflow:hidden;

    max-width:920px;

    margin:0 auto;

    padding:1rem 1.2rem;

    border-radius:22px;

    background:
        linear-gradient(
            135deg,
            rgba(22,163,74,.22),
            rgba(21,128,61,.14)
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

.happy-hour-glow{

    position:absolute;

    top:-90px;
    right:-90px;

    width:220px;
    height:220px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(34,197,94,.22),
            transparent 70%
        );

    pointer-events:none;
}

/* =========================================
   COMPACT
========================================= */

.happy-hour-notice.compact{

    padding:.65rem 1rem;
}

.happy-hour-notice.compact .happy-hour-details,
.happy-hour-notice.compact .happy-hour-progress-wrapper,
.happy-hour-notice.compact .happy-hour-timer-label{

    display:none;
}

.happy-hour-notice.compact .happy-hour-title{

    font-size:.92rem;
}

.happy-hour-notice.compact .happy-hour-timer{

    font-size:.82rem;
}

/* =========================================
   LAYOUT
========================================= */

.happy-hour-inner{

    position:relative;

    z-index:2;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:1rem;

    flex-wrap:wrap;
}

.happy-hour-left{

    display:flex;

    align-items:center;

    gap:.9rem;
}

.happy-hour-right{

    text-align:right;
}

/* =========================================
   ICON
========================================= */

.happy-hour-icon{

    width:46px;
    height:46px;

    border-radius:16px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        rgba(255,255,255,.1);

    font-size:1.25rem;

    box-shadow:
        inset 0 0 12px rgba(255,255,255,.12);
}

/* =========================================
   TEXT
========================================= */

.happy-hour-title{

    color:#fff;

    font-size:1rem;

    font-weight:700;

    line-height:1.2;
}

.happy-hour-details{

    margin-top:3px;

    color:rgba(255,255,255,.72);

    font-size:.84rem;

    font-weight:500;
}

/* =========================================
   TIMER
========================================= */

.happy-hour-timer-label{

    color:rgba(255,255,255,.55);

    font-size:.72rem;

    text-transform:uppercase;

    letter-spacing:.5px;

    margin-bottom:2px;
}

.happy-hour-timer{

    color:#fff;

    font-size:.92rem;

    font-weight:700;
}

/* =========================================
   PROGRESS
========================================= */

.happy-hour-progress-wrapper{

    position:relative;

    z-index:2;

    overflow:hidden;

    height:8px;

    margin-top:1rem;

    border-radius:999px;

    background:
        rgba(255,255,255,.08);
}

.happy-hour-progress-bar{

    height:100%;

    width:100%;

    border-radius:999px;

    background:
        linear-gradient(
            90deg,
            #22c55e,
            #4ade80
        );

    transition:
        width 1s linear,
        background .3s ease;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .happy-hour-notice{

        border-radius:18px;

        padding:.9rem 1rem;
    }

    .happy-hour-inner{

        align-items:flex-start;
    }

    .happy-hour-right{

        width:100%;

        text-align:left;
    }

    .happy-hour-title{

        font-size:.95rem;
    }

    .happy-hour-details{

        font-size:.8rem;
    }

    .happy-hour-timer{

        font-size:.85rem;
    }

}

</style>

<script>

document.addEventListener("DOMContentLoaded", () => {

    const notice = document.getElementById("happy-hour-notice");

    if (!notice) return;

    const countdownEl = document.getElementById("happy-hour-countdown");

    const progressEl = document.getElementById("happy-hour-progress");

    const startTime = new Date("{{ $currentHappyHour->start_at->toIso8601String() }}").getTime();

    const endTime = new Date("{{ $currentHappyHour->end_at->toIso8601String() }}").getTime();

    const totalDuration = endTime - startTime;

    let shrinkTimer;

    /* =========================================
       COMPACT
    ========================================= */

    function shrinkNotice() {

        notice.classList.add("compact");

    }

    function expandNotice() {

        notice.classList.remove("compact");

    }

    function scheduleShrink() {

        clearTimeout(shrinkTimer);

        shrinkTimer = setTimeout(shrinkNotice, 3000);

    }

    /* =========================================
       COUNTDOWN
    ========================================= */

    function updateCountdown() {

        const now = Date.now();

        const remaining = endTime - now;

        if (remaining <= 0) {

            notice.remove();

            clearInterval(interval);

            return;

        }

        const hours = Math.floor(remaining / (1000 * 60 * 60));

        const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));

        const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

        if (countdownEl) {

            countdownEl.textContent = `${hours}h ${minutes}m ${seconds}s`;

        }

        if (progressEl) {

            const percent = (remaining / totalDuration) * 100;

            progressEl.style.width = percent + "%";

            if (percent <= 25) {

                progressEl.style.background =
                    "linear-gradient(90deg,#ef4444,#f87171)";

            } else if (percent <= 50) {

                progressEl.style.background =
                    "linear-gradient(90deg,#f59e0b,#fbbf24)";

            } else {

                progressEl.style.background =
                    "linear-gradient(90deg,#22c55e,#4ade80)";

            }

        }

    }

    const interval = setInterval(updateCountdown, 1000);

    updateCountdown();

    /* =========================================
       AUTO SHRINK
    ========================================= */

    scheduleShrink();

    notice.addEventListener("mouseenter", () => {

        expandNotice();

        clearTimeout(shrinkTimer);

    });

    notice.addEventListener("mouseleave", () => {

        scheduleShrink();

    });

});

</script>

@endif