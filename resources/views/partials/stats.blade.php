<div class="container mt-4">

    <div class="stats-wrapper">

        {{-- Header --}}
        <div class="stats-header">

            <div class="d-flex align-items-center gap-2">

                <div class="stats-header-icon">
                    <i class="bi bi-bar-chart-fill"></i>
                </div>

                <div>
                    <div class="stats-title">
                        Tracker Statistics
                    </div>

                    <div class="stats-subtitle">
                        Live community activity overview
                    </div>
                </div>

            </div>

        </div>

        {{-- Stats Grid --}}
        <div class="row g-3">

            <!-- Torrents -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card flex-fill">

                    <div class="stat-icon">
                        <i class="bi bi-server"></i>
                    </div>

                    <div class="stat-label">
                        Torrents
                    </div>

                    <div
                        class="stat-value count-up"
                        data-value="{{ $torrentCount }}"
                    >
                        0
                    </div>

                </div>

            </div>

            <!-- Active Torrents -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card flex-fill">

                    <div class="stat-icon">
                        <i class="bi bi-gear-fill"></i>
                    </div>

                    <div class="stat-label">
                        Active Torrents
                    </div>

                    <div
                        class="stat-value count-up"
                        data-value="{{ $torrentActive }}"
                    >
                        0
                    </div>

                </div>

            </div>

            <!-- Users -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card flex-fill">

                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div class="stat-label">
                        Users
                    </div>

                    <div
                        class="stat-value count-up"
                        data-value="{{ $userCount }}"
                    >
                        0
                    </div>

                </div>

            </div>

            <!-- Visitors -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card flex-fill">

                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div class="stat-label">
                        Visitors 24h
                    </div>

                    <div
                        class="stat-value count-up"
                        data-value="{{ $activeUsers24hCount }}"
                    >
                        0
                    </div>

                </div>

            </div>

            <!-- Seeders -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card flex-fill">

                    <div class="stat-icon">
                        <i class="bi bi-cloud-upload-fill"></i>
                    </div>

                    <div class="stat-label">
                        Active Seeders
                    </div>

                    <div
                        class="stat-value count-up"
                        data-value="{{ $uniqueSeeders }}"
                    >
                        0
                    </div>

                </div>

            </div>

            <!-- Leechers -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card flex-fill">

                    <div class="stat-icon">
                        <i class="bi bi-cloud-download-fill"></i>
                    </div>

                    <div class="stat-label">
                        Active Leechers
                    </div>

                    <div
                        class="stat-value count-up"
                        data-value="{{ $uniqueLeechers }}"
                    >
                        0
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

/* =========================================
   COUNT UP
========================================= */

function animateCount(el) {

    const target = +el.dataset.value;

    let count = 0;

    const increment = Math.max(
        1,
        Math.floor(target / 100)
    );

    const update = () => {

        count += increment;

        if (count < target) {

            el.innerText = count.toLocaleString();

            requestAnimationFrame(update);

        } else {

            el.innerText = target.toLocaleString();

        }

    };

    update();

}


/* =========================================
   TRIGGER WHEN VISIBLE
========================================= */

function handleScroll() {

    document.querySelectorAll(".count-up").forEach(el => {

        if (!el.classList.contains("counted")) {

            const rect = el.getBoundingClientRect();

            if (
                rect.top < window.innerHeight &&
                rect.bottom >= 0
            ) {

                el.classList.add("counted");

                animateCount(el);

            }

        }

    });

}

window.addEventListener("scroll", handleScroll);

window.addEventListener("load", handleScroll);

</script>


<style>

/* =========================================
   WRAPPER
========================================= */

.stats-wrapper {

    position: relative;

    overflow: hidden;

    padding: 1rem;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .95),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);

    border-radius: .9rem;

    box-shadow:
        0 10px 28px rgba(0, 0, 0, .24);
}

.stats-wrapper::before {

    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 3px;

    background:
        linear-gradient(
            180deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    opacity: .9;
}


/* =========================================
   HEADER
========================================= */

.stats-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 1rem;

    padding: .25rem .15rem .85rem;

    border-bottom:
        1px solid var(--ui-border);
}

.stats-header-icon {

    width: 36px;
    height: 36px;

    display: flex;

    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: .65rem;

    color: var(--ui-accent);

    background:
        rgba(45, 212, 191, .08);

    border:
        1px solid rgba(45, 212, 191, .18);

    font-size: 15px;
}

.stats-title {

    color: #fff;

    font-size: 14px;

    font-weight: 700;

    line-height: 1.25;
}

.stats-subtitle {

    color:
        rgba(255, 255, 255, .55);

    font-size: 13px;

    margin-top: .15rem;
}


/* =========================================
   STAT CARD
========================================= */

.stat-card {

    position: relative;

    overflow: hidden;

    min-height: 145px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    padding: 1rem;

    text-align: center;

    background:
        rgba(255, 255, 255, .025);

    border:
        1px solid var(--ui-border);

    border-radius: .75rem;

    box-shadow:
        0 6px 18px rgba(0, 0, 0, .16);

    transition:
        transform .2s ease,
        border-color .2s ease,
        background .2s ease,
        box-shadow .2s ease;
}

.stat-card::after {

    content: "";

    position: absolute;

    left: 0;
    top: 0;

    width: 100%;
    height: 2px;

    background:
        linear-gradient(
            90deg,
            transparent,
            var(--ui-accent),
            transparent
        );

    opacity: .65;
}

.stat-card:hover {

    transform: translateY(-3px);

    background:
        rgba(45, 212, 191, .045);

    border-color:
        rgba(45, 212, 191, .22);

    box-shadow:
        0 10px 24px rgba(0, 0, 0, .24);
}


/* =========================================
   ICON
========================================= */

.stat-icon {

    width: 48px;
    height: 48px;

    display: flex;

    align-items: center;
    justify-content: center;

    margin-bottom: .7rem;

    border-radius: .7rem;

    color: var(--ui-accent);

    background:
        rgba(45, 212, 191, .08);

    border:
        1px solid rgba(45, 212, 191, .15);

    font-size: 20px;

    box-shadow:
        inset 0 0 12px rgba(45, 212, 191, .04);
}


/* =========================================
   TEXT
========================================= */

.stat-label {

    margin-bottom: .35rem;

    color:
        rgba(255, 255, 255, .62);

    font-size: 13px;

    font-weight: 600;

    line-height: 1.3;
}

.stat-value {

    color: #fff;

    font-size: 14px;

    font-weight: 800;

    line-height: 1.2;

    letter-spacing: .2px;
}


/* =========================================
   VALUE SIZE
========================================= */

.stat-value {

    font-size: 14px;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 768px) {

    .stats-wrapper {

        padding: .8rem;
    }

    .stats-header {

        margin-bottom: .8rem;

        padding-bottom: .75rem;
    }

    .stats-header-icon {

        width: 34px;
        height: 34px;

        font-size: 14px;
    }

    .stats-title {

        font-size: 14px;
    }

    .stats-subtitle {

        font-size: 13px;
    }

    .stat-card {

        min-height: 135px;

        padding: .85rem;
    }

    .stat-icon {

        width: 44px;
        height: 44px;

        margin-bottom: .6rem;

        font-size: 18px;
    }

    .stat-label {

        font-size: 13px;
    }

    .stat-value {

        font-size: 14px;
    }

}

</style>