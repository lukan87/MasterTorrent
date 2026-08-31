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

                <div class="stat-card stat-primary flex-fill">

                    <div class="stat-icon">

                        <i class="bi bi-server"></i>

                    </div>

                    <div class="stat-label">

                        Torrents

                    </div>

                    <div class="stat-value count-up"
                         data-value="{{ $torrentCount }}">

                        0

                    </div>

                </div>

            </div>

            <!-- Active Torrents -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card stat-info flex-fill">

                    <div class="stat-icon">

                        <i class="bi bi-gear-fill"></i>

                    </div>

                    <div class="stat-label">

                        Active Torrents

                    </div>

                    <div class="stat-value count-up"
                         data-value="{{ $torrentActive }}">

                        0

                    </div>

                </div>

            </div>

            <!-- Users -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card stat-danger flex-fill">

                    <div class="stat-icon">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <div class="stat-label">

                        Users

                    </div>

                    <div class="stat-value count-up"
                         data-value="{{ $userCount }}">

                        0

                    </div>

                </div>

            </div>

            <!-- Visitors -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card stat-success flex-fill">

                    <div class="stat-icon">

                        <i class="bi bi-clock-history"></i>

                    </div>

                    <div class="stat-label">

                        Visitors 24h

                    </div>

                    <div class="stat-value count-up"
                         data-value="{{ $activeUsers24hCount }}">

                        0

                    </div>

                </div>

            </div>

            <!-- Seeders -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card stat-warning flex-fill">

                    <div class="stat-icon">

                        <i class="bi bi-cloud-upload-fill"></i>

                    </div>

                    <div class="stat-label">

                        Active Seeders

                    </div>

                    <div class="stat-value count-up"
                         data-value="{{ $uniqueSeeders }}">

                        0

                    </div>

                </div>

            </div>

            <!-- Leechers -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2 d-flex">

                <div class="stat-card stat-secondary flex-fill">

                    <div class="stat-icon">

                        <i class="bi bi-cloud-download-fill"></i>

                    </div>

                    <div class="stat-label">

                        Active Leechers

                    </div>

                    <div class="stat-value count-up"
                         data-value="{{ $uniqueLeechers }}">

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

    const increment = Math.max(1, Math.floor(target / 100));

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

            if (rect.top < window.innerHeight && rect.bottom >= 0) {

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

.stats-wrapper{

    position:relative;

    overflow:hidden;

    padding:1.4rem;

    border-radius:28px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.045),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(14px);

    box-shadow:
        0 14px 40px rgba(0,0,0,.22);
}

/* =========================================
   HEADER
========================================= */

.stats-header{

    display:flex;

    align-items:center;

    justify-content:space-between;

    margin-bottom:1.4rem;

    padding-bottom:1rem;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.stats-header-icon{

    width:42px;
    height:42px;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6
        );

    color:white;

    font-size:1rem;

    box-shadow:
        0 8px 20px rgba(99,102,241,.28);
}

.stats-title{

    color:white;

    font-size:1rem;

    font-weight:700;
}

.stats-subtitle{

    color:rgba(255,255,255,.45);

    font-size:.78rem;

    margin-top:2px;
}

/* =========================================
   STAT CARD
========================================= */

.stat-card{

    position:relative;

    overflow:hidden;

    border-radius:22px;

    padding:1.2rem;

    min-height:165px;

    display:flex;

    flex-direction:column;

    justify-content:center;

    align-items:center;

    text-align:center;

    transition:
        transform .25s ease,
        box-shadow .25s ease;
}

.stat-card:hover{

    transform:
        translateY(-5px);

    box-shadow:
        0 14px 30px rgba(0,0,0,.22);
}

/* =========================================
   COLORS
========================================= */

.stat-primary{

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1d4ed8
        );
}

.stat-info{

    background:
        linear-gradient(
            135deg,
            #0891b2,
            #0e7490
        );
}

.stat-danger{

    background:
        linear-gradient(
            135deg,
            #dc2626,
            #991b1b
        );
}

.stat-success{

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #166534
        );
}

.stat-warning{

    background:
        linear-gradient(
            135deg,
            #eab308,
            #a16207
        );

    color:#111827;
}

.stat-secondary{

    background:
        linear-gradient(
            135deg,
            #4b5563,
            #1f2937
        );
}

/* =========================================
   ICON
========================================= */

.stat-icon{

    width:58px;
    height:58px;

    border-radius:18px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        rgba(255,255,255,.14);

    color:white;

    font-size:1.5rem;

    margin-bottom:1rem;

    box-shadow:
        inset 0 0 10px rgba(255,255,255,.18);
}

.stat-warning .stat-icon{

    color:#111827;
}

/* =========================================
   TEXT
========================================= */

.stat-label{

    font-size:.78rem;

    font-weight:700;

    text-transform:uppercase;

    letter-spacing:.5px;

    opacity:.92;

    margin-bottom:.45rem;
}

.stat-value{

    font-size:1.9rem;

    font-weight:800;

    line-height:1;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .stats-wrapper{

        padding:1rem;
    }

    .stat-card{

        min-height:145px;

        padding:1rem;
    }

    .stat-icon{

        width:50px;
        height:50px;

        font-size:1.25rem;

        margin-bottom:.8rem;
    }

    .stat-value{

        font-size:1.5rem;
    }

    .stat-label{

        font-size:.72rem;
    }

}

</style>