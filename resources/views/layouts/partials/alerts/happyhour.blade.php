@if(!empty($currentHappyHour) && $currentHappyHour->isActive())

<div id="happy-hour-notice" class="happy-hour-notice mb-3 mt-2">

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
   FILEIPLAY HAPPY HOUR
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.happy-hour-notice {
    position: sticky;
    top: 10px;
    z-index: 1000;
    overflow: hidden;
    max-width: 920px;
    margin: 0 auto;
    padding: .85rem 1rem;

    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-left: 3px solid var(--ui-accent, #22d3ee);
    border-radius: .85rem;

    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );

    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    box-shadow: 0 10px 28px rgba(0,0,0,.18);

    transition: all .3s ease;
}

.happy-hour-glow {
    position: absolute;
    top: -80px;
    right: -80px;
    width: 190px;
    height: 190px;
    border-radius: 50%;

    background: radial-gradient(
        circle,
        rgba(34,211,238,.12),
        transparent 70%
    );

    pointer-events: none;
}

/* =========================================
   LAYOUT
========================================= */

.happy-hour-inner {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1rem;
    flex-wrap: wrap;
}

.happy-hour-left {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
}

.happy-hour-right {
    text-align: right;
}

/* =========================================
   ICON
========================================= */

.happy-hour-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .7rem;

    background: rgba(34,211,238,.08);

    box-shadow: inset 0 0 12px rgba(34,211,238,.05);

    font-size: 1.1rem;
}

/* =========================================
   TEXT
========================================= */

.happy-hour-title {
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.25;
}

.happy-hour-details {
    margin-top: 3px;
    color: rgba(226,232,240,.72);
    font-size: 12px;
    font-weight: 500;
}

/* =========================================
   TIMER
========================================= */

.happy-hour-timer-label {
    margin-bottom: 2px;

    color: rgba(226,232,240,.55);

    font-size: 11px;
    font-weight: 600;

    text-transform: uppercase;
    letter-spacing: .5px;
}

.happy-hour-timer {
    color: var(--ui-accent, #22d3ee);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

/* =========================================
   PROGRESS
========================================= */

.happy-hour-progress-wrapper {
    position: relative;
    z-index: 2;

    overflow: hidden;

    height: 5px;
    margin-top: .75rem;

    border-radius: 999px;
    background: rgba(255,255,255,.07);
}

.happy-hour-progress-bar {
    height: 100%;
    width: 100%;

    border-radius: 999px;

    background: var(--ui-accent, #22d3ee);

    transition:
        width 1s linear,
        background .3s ease;
}

/* =========================================
   COMPACT
========================================= */

.happy-hour-notice.compact {
    padding: .55rem .85rem;
}

.happy-hour-notice.compact .happy-hour-details,
.happy-hour-notice.compact .happy-hour-progress-wrapper,
.happy-hour-notice.compact .happy-hour-timer-label {
    display: none;
}

.happy-hour-notice.compact .happy-hour-title {
    font-size: 13px;
}

.happy-hour-notice.compact .happy-hour-timer {
    font-size: 12px;
}

/* =========================================
   HOVER
========================================= */

.happy-hour-notice:hover {
    border-left-color: var(--ui-accent-strong, #67e8f9);
    box-shadow: 0 12px 30px rgba(0,0,0,.22);
}

/* =========================================
   MOBILE
========================================= */

@media (max-width: 768px) {

    .happy-hour-notice {
        top: 6px;
        border-radius: .75rem;
        padding: .75rem .85rem;
    }

    .happy-hour-inner {
        align-items: flex-start;
    }

    .happy-hour-left {
        width: 100%;
    }

    .happy-hour-right {
        width: 100%;
        text-align: left;
    }

    .happy-hour-title {
        font-size: 14px;
    }

    .happy-hour-details {
        font-size: 12px;
    }

    .happy-hour-timer {
        font-size: 13px;
    }

    .happy-hour-progress-wrapper {
        margin-top: .65rem;
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

        const hours = Math.floor(
            remaining / (1000 * 60 * 60)
        );

        const minutes = Math.floor(
            (remaining % (1000 * 60 * 60)) / (1000 * 60)
        );

        const seconds = Math.floor(
            (remaining % (1000 * 60)) / 1000
        );

        if (countdownEl) {
            countdownEl.textContent =
                `${hours}h ${minutes}m ${seconds}s`;
        }

        if (progressEl) {

            const percent =
                (remaining / totalDuration) * 100;

            progressEl.style.width = percent + "%";

            if (percent <= 25) {

                progressEl.style.background =
                    "linear-gradient(90deg,#ef4444,#f87171)";

            } else if (percent <= 50) {

                progressEl.style.background =
                    "linear-gradient(90deg,#f59e0b,#fbbf24)";

            } else {

                progressEl.style.background =
                    "var(--ui-accent, #22d3ee)";
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
