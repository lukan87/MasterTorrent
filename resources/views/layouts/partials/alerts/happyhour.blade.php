@if(!empty($currentHappyHour) && $currentHappyHour->isActive())
<aside class="hh-notice my-3" aria-label="Happy Hour rewards" data-happy-hour-end="{{ $currentHappyHour->end_at->toIso8601String() }}" data-server-time="{{ now()->toIso8601String() }}">
    <div><span class="badge bg-success mb-2">Happy Hour · Live</span>
        <h2 class="h5 mb-1">{{ $currentHappyHour->theme }}</h2>
        <p class="mb-1"><strong>{{ $currentHappyHour->upload_multiplier }}× upload credit</strong>{{ $currentHappyHour->free_download ? ' · Freeleech enabled' : '' }}</p>
    </div>
    <div class="hh-notice-time"><strong data-countdown>Ends {{ $currentHappyHour->end_at->format('H:i') }} {{ config('app.timezone') }}</strong>
        <small class="d-block">Until {{ $currentHappyHour->end_at->format('M j, H:i') }} {{ config('app.timezone') }}</small>
    </div>
</aside>
<style>
.hh-notice { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; padding:1.25rem; border:1px solid var(--theme-teal-border, #2dd4bf); border-radius:.75rem; background:linear-gradient(120deg,var(--theme-surface, #0c2120),var(--theme-surface, #0b1019)); color:var(--theme-text, #f1f5f9); }
.hh-notice small { color:var(--theme-text, #cbd5e1); } .hh-notice-time { font-variant-numeric:tabular-nums; }
</style>
<script>
(() => {
    const notice = document.querySelector('[data-happy-hour-end]');
    if (!notice) return;
    const end = Date.parse(notice.dataset.happyHourEnd);
    const server = Date.parse(notice.dataset.serverTime);
    const loaded = performance.now();
    function tick() {
        const remaining = Math.max(0, Math.ceil((end - server - (performance.now() - loaded)) / 1000));
        if (!remaining) { notice.hidden = true; notice.style.display = 'none'; return false; }
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor(remaining % 3600 / 60);
        notice.querySelector('[data-countdown]').textContent = `${hours}h ${minutes}m ${remaining % 60}s remaining`;
        return true;
    }
    if (tick()) { const timer = setInterval(() => { if (!tick()) clearInterval(timer); }, 1000); }
})();
</script>
@endif
