@if(!empty($currentHappyHour) && $currentHappyHour->isActive())
<div id="happy-hour-popup" class="happy-hour-popup">
    🎉 <strong>{{ $currentHappyHour->theme }}</strong> Happy Hour<br class="d-md-none">
    Started by: <strong>{{ $currentHappyHour->activated_by ? $currentHappyHour->user->name : 'System (Automatic)' }}</strong>!
    <br>
    {{ $currentHappyHour->upload_multiplier }}x Upload Count 
    @if($currentHappyHour->free_download)
        & Free Downloads! 
    @endif
    <br>
    ⏰ Ends in: <span id="happy-hour-countdown"></span>
</div>

<div id="happy-hour-alert" class="alert alert-success mt-3 d-none shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-1 text-center text-md-start">
        <div>
            🎉 <strong>{{ $currentHappyHour->theme }}</strong> Happy Hour is live!  
            {{ $currentHappyHour->upload_multiplier }}x Upload Count 
            @if($currentHappyHour->free_download)
                & Free Downloads! + {{ $currentHappyHour->upload_multiplier }}x seedbonus per seeded torrent!
            @endif
        </div>
        <div class="mt-2 mt-md-0"><span id="happy-hour-countdown-2"></span></div>
    </div>
    <div class="progress" style="height: 18px;">
        <div id="happy-hour-progress" 
             class="progress-bar progress-bar-striped progress-bar-animated bg-success"
             role="progressbar" style="width: 100%">
        </div>
    </div>
</div>

<style>
/* POPUP STYLES */
.happy-hour-popup {
    position: fixed;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #28a745, #218838);
    color: #fff;
    padding: 14px 20px;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
    z-index: 9999;
    max-width: 92%;
    animation: fadeInUp 0.6s ease-out;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    cursor: pointer;
    transition: all 0.4s ease;
}

/* Fade + slide animation */
@keyframes fadeInUp {
    from { opacity: 0; transform: translate(-50%, 30px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}

/* Mobile friendly adjustments */
@media (max-width: 576px) {
    .happy-hour-popup {
        font-size: 0.85rem;
        padding: 10px 14px;
        bottom: 15px;
        border-radius: 10px;
        line-height: 1.4;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("happy-hour-popup");
    const alertEl = document.getElementById("happy-hour-alert");
    const countdownEl = document.getElementById("happy-hour-countdown");
    const countdownEl2 = document.getElementById("happy-hour-countdown-2");
    const progressEl = document.getElementById("happy-hour-progress");

    const startTime = new Date("{{ $currentHappyHour->start_at->toIso8601String() }}").getTime();
    const endTime = new Date("{{ $currentHappyHour->end_at->toIso8601String() }}").getTime();
    const totalDuration = endTime - startTime;

    function updateCountdown() {
        const now = new Date().getTime();
        const remaining = endTime - now;

        if (remaining <= 0) {
            countdownEl.textContent = "Expired!";
            countdownEl2.textContent = "Expired!";
            progressEl.style.width = "0%";
            progressEl.classList.remove("bg-success", "bg-warning");
            progressEl.classList.add("bg-danger");
            popup.style.display = "none";
            alertEl.classList.add("d-none");
            clearInterval(interval);
            return;
        }

        const hours = Math.floor(remaining / (1000 * 60 * 60));
        const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

        countdownEl.textContent = `${hours}h ${minutes}m ${seconds}s`;
        countdownEl2.textContent = `${hours}h ${minutes}m ${seconds}s`;

        const percent = (remaining / totalDuration) * 100;
        progressEl.style.width = percent + "%";

        // Change progress color based on remaining time
        if (percent <= 25) {
            progressEl.classList.remove("bg-success", "bg-warning");
            progressEl.classList.add("bg-danger");
        } else if (percent <= 50) {
            progressEl.classList.remove("bg-success", "bg-danger");
            progressEl.classList.add("bg-warning");
        } else {
            progressEl.classList.remove("bg-warning", "bg-danger");
            progressEl.classList.add("bg-success");
        }
    }

    const interval = setInterval(updateCountdown, 1000);
    updateCountdown();

    // Hide popup after 5s, show alert
    setTimeout(() => {
        popup.style.opacity = "0";
        popup.style.transform = "translate(-50%, 40px)";
        setTimeout(() => {
            popup.remove();
            alertEl.classList.remove("d-none");
        }, 500);
    }, 5000);

    // Allow manual dismiss
    popup.addEventListener("click", () => {
        popup.remove();
        alertEl.classList.remove("d-none");
    });
});
</script>
@endif
