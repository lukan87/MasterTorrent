<div class="container mt-5">
    <div class="row g-4">

        <!-- Torrents Box -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex">
            <div class="stat-card bg-gradient-primary text-white shadow-lg rounded-4 p-4 text-center flex-fill">
                <div class="icon-wrapper mb-3">
                    <i class="bi bi-server"></i>
                </div>
                <h6 class="text-uppercase fw-bold mb-1">Torrents</h6>
                <h3 class="fw-bold count-up" data-value="{{ $torrentCount }}">0</h3>
            </div>
        </div>

        <!-- Active Torrents Box -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex">
            <div class="stat-card bg-gradient-info text-white shadow-lg rounded-4 p-4 text-center flex-fill">
                <div class="icon-wrapper mb-3">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <h6 class="text-uppercase fw-bold mb-1">Active Torrents</h6>
                <h3 class="fw-bold count-up" data-value="{{ $torrentActive }}">0</h3>
            </div>
        </div>

        <!-- Users Box -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex">
            <div class="stat-card bg-gradient-danger text-white shadow-lg rounded-4 p-4 text-center flex-fill">
                <div class="icon-wrapper mb-3">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h6 class="text-uppercase fw-bold mb-1">Users</h6>
                <h3 class="fw-bold count-up" data-value="{{ $userCount }}">0</h3>
            </div>
        </div>

        <!-- Forum Topics Box -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex">
            <div class="stat-card bg-gradient-success text-white shadow-lg rounded-4 p-4 text-center flex-fill">
                <div class="icon-wrapper mb-3">
                    <i class="bi bi-cart-fill"></i>
                </div>
                <h6 class="text-uppercase fw-bold mb-1">Forum Topics</h6>
                <h3 class="fw-bold count-up" data-value="{{ $forumTopicCount }}">0</h3>
            </div>
        </div>

        <!-- Active Seeders Box -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex">
            <div class="stat-card bg-gradient-warning text-dark shadow-lg rounded-4 p-4 text-center flex-fill">
                <div class="icon-wrapper mb-3">
                    <i class="bi bi-cloud-upload-fill"></i>
                </div>
                <h6 class="text-uppercase fw-bold mb-1">Active Seeders</h6>
                <h3 class="fw-bold count-up" data-value="{{ $uniqueSeeders }}">0</h3>
            </div>
        </div>

        <!-- Active Leechers Box -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex">
            <div class="stat-card bg-gradient-secondary text-white shadow-lg rounded-4 p-4 text-center flex-fill">
                <div class="icon-wrapper mb-3">
                    <i class="bi bi-cloud-download-fill"></i>
                </div>
                <h6 class="text-uppercase fw-bold mb-1">Active Leechers</h6>
                <h3 class="fw-bold count-up" data-value="{{ $uniqueLeechers }}">0</h3>
            </div>
        </div>

    </div>
</div>

<script>
// Simple Count-Up Animation
function animateCount(el) {
    const target = +el.getAttribute("data-value");
    let count = 0;
    const increment = target / 100; // adjust speed
    const updateCount = () => {
        count += increment;
        if (count < target) {
            el.innerText = Math.ceil(count);
            requestAnimationFrame(updateCount);
        } else {
            el.innerText = target;
        }
    };
    updateCount();
}

// Run animation when in viewport
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
/* Darker Gradient backgrounds */
.bg-gradient-primary { background: linear-gradient(135deg, #0a58ca, #004085); }
.bg-gradient-info    { background: linear-gradient(135deg, #0c5460, #055160); }
.bg-gradient-danger  { background: linear-gradient(135deg, #842029, #5a0d16); }
.bg-gradient-success { background: linear-gradient(135deg, #146c43, #0b3d24); }
.bg-gradient-warning { background: linear-gradient(135deg, #997404, #664d03); }
.bg-gradient-secondary { background: linear-gradient(135deg, #495057, #212529); }


/* Card hover effect */
.stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
   
    min-height: 200px; /* adjust as needed */
}
.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.2);
}

/* Icon styling */
.icon-wrapper {
    font-size: 2.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    box-shadow: inset 0 0 10px rgba(255,255,255,0.3);
}
</style>
