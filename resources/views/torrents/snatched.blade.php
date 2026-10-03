@extends('layouts.app')

@section('content')
@include('forum.partials.index-css')
@include('torrents.partials.css.snatched-css')
<div class="container-fluid py-4 forum-index snatch-history">
    <header class="forum-index-header">
        <div>
            <div class="forum-eyebrow"><i class="bi bi-activity" aria-hidden="true"></i> Torrent activity</div>
            <h1 class="forum-page-title">{{ $completedOnly ? 'Completed downloads' : 'Snatch history' }}<span class="snatch-title-dot">.</span></h1>
            <p class="snatch-torrent-name">{{ $torrent->name }}</p>
            <p class="forum-page-subtitle">{{ $completedOnly ? 'Members who fully completed this torrent, with their transfer totals and seeding activity.' : 'A closer look at the swarm. Download progress, transfer totals, and seeding activity in one place.' }}</p>
            <div class="forum-header-actions">
                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="forum-secondary-btn">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to torrent
                </a>
                <span class="snatch-access"><i class="bi bi-shield-lock" aria-hidden="true"></i> Moderator view</span>
            </div>
        </div>
        <aside class="forum-overview" aria-label="Torrent statistics">
            <div class="forum-overview-symbol"><i class="bi bi-hdd-network" aria-hidden="true"></i></div>
            <p>Swarm overview</p>
            <dl class="forum-stats">
                <div><dt>Times completed</dt><dd>{{ number_format($torrent->times_completed) }}</dd></div>
                <div><dt>{{ $completedOnly ? 'Completed records' : 'History records' }}</dt><dd>{{ number_format($histories->total()) }}</dd></div>
            </dl>
            <div class="snatch-swarm">
                <span><i class="bi bi-arrow-up" aria-hidden="true"></i> {{ number_format($torrent->seeders) }} seeders</span>
                <span><i class="bi bi-arrow-down" aria-hidden="true"></i> {{ number_format($torrent->leechers) }} leechers</span>
            </div>
        </aside>
    </header>
    <section class="snatch-ledger" aria-labelledby="snatch-ledger-title">
        <div class="forum-section-heading">
            <div>
                <div class="forum-eyebrow">Transfer ledger</div>
                <h2 id="snatch-ledger-title">{{ $completedOnly ? 'Completed torrent history' : 'Download & seed activity' }}</h2>
            </div>
            <span class="forum-category-total"><i class="bi bi-sort-down me-1" aria-hidden="true"></i> Newest first</span>
        </div>
        <p class="snatch-explainer">{{ $completedOnly ? 'Only records with a confirmed completion date are shown, newest completion first.' : 'Includes downloads in progress and seeders. Credited totals may differ from actual traffic.' }}</p>
        <p id="snatch-status" role="status" aria-live="polite"></p>
        <div id="snatch-results" aria-busy="false">
            @include('torrents.partials.snatched-table')
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const results = document.getElementById('snatch-results');
    const status = document.getElementById('snatch-status');
    let pending;

    async function loadPage(url, push = true) {
        if (pending) pending.abort();
        const controller = new AbortController();
        pending = controller;
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'Loading history…';
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('Unable to load history');
            const data = await response.json();
            if (typeof data.html !== 'string') throw new Error('Invalid response');
            results.innerHTML = data.html;
            if (push) window.history.pushState({}, '', url);
            status.textContent = 'History loaded.';
            const summary = results.querySelector('[data-snatch-summary]');
            summary?.focus({ preventScroll: true });
            results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Could not load history. ';
                const retry = document.createElement('a');
                retry.href = url;
                retry.textContent = 'Retry this page';
                status.append(retry);
            }
        } finally {
            if (pending === controller) {
                results.setAttribute('aria-busy', 'false');
                pending = null;
            }
        }
    }

    results.addEventListener('click', event => {
        const link = event.target.closest('.pagination a');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        loadPage(link.href);
    });
    window.addEventListener('popstate', () => loadPage(window.location.href, false));
})();
</script>
@endpush
