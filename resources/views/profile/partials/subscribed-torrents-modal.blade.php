<div class="modal fade profile-guide-modal subscriptions-modal" id="subscribedTorrentsModal" tabindex="-1" aria-labelledby="subscribedTorrentsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="seeder-guide-eyebrow"><i class="bi bi-bell" aria-hidden="true"></i> Following the next release</span>
                    <h2 class="modal-title" id="subscribedTorrentsModalTitle">Subscribed Torrents</h2>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="seeder-guide-current">
                    <span>{{ $user->name }}’s subscriptions</span>
                    <strong>{{ number_format(count($subscribedTorrents)) }} {{ \Illuminate\Support\Str::plural('title', count($subscribedTorrents)) }}</strong>
                </div>
                        <p class="rank-intro">
                            Titles you are subscribed to. When a new upload
                            matches one of these, you'll be notified by private message.
                        </p>


                        <div class="subscribed-list">

                            @forelse($subscribedTorrents as $subTorrent)

                                @php
                                    $libraryRoute =
                                        !empty($subTorrent->tmdbid)
                                        && !empty($subTorrent->library_type)
                                            ? (
                                                $subTorrent->library_type === 'series'
                                                    ? 'library.series.show'
                                                    : 'library.movies.show'
                                            )
                                            : null;

                                    $libraryHref = $libraryRoute
                                        ? route($libraryRoute, [
                                            'tmdbid' => $subTorrent->tmdbid,
                                            'slug' => $subTorrent->library_slug,
                                        ])
                                        : route('torrents.show', [
                                            'id' => $subTorrent->id,
                                            'slug' => $subTorrent->slug
                                        ]);
                                @endphp


                                <a
                                    href="{{ $libraryHref }}"
                                    class="sub-torrent-row"
                                >

                                    <img
                                        class="subscribed-poster"
                                        src="{{ $subTorrent->poster ?: asset('images/noposter.jpg') }}"
                                        alt=""
                                        loading="lazy"
                                    >


                                    <div class="subscribed-info">

                                        <span class="subscribed-name">
                                            {{ $subTorrent->name }}
                                        </span>

                                        <div class="subscribed-meta">

                                            <span title="Seeders">
                                                <i class="bi bi-arrow-up-circle-fill text-success"></i>
                                                {{ $subTorrent->seeders ?? 0 }}
                                            </span>

                                            <span title="Leechers">
                                                <i class="bi bi-arrow-down-circle-fill text-danger"></i>
                                                {{ $subTorrent->leechers ?? 0 }}
                                            </span>

                                            <span title="Times completed">
                                                <i class="bi bi-check-circle-fill text-info"></i>
                                                {{ $subTorrent->times_completed ?? 0 }}
                                            </span>

                                        </div>

                                    </div>

                                    <i class="bi bi-chevron-right subscribed-arrow"></i>

                                </a>

                            @empty

                                <div class="subscribed-empty">

                                    <i class="bi bi-bell-slash"></i>

                                    <strong>No subscriptions yet</strong>

                                    <span>
                                        This member hasn't subscribed to any titles.
                                    </span>

                                </div>

                            @endforelse

                        </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="seeder-guide-close" data-bs-dismiss="modal">Back to profile</button>
            </div>
        </div>
    </div>
</div>
<style>
.subscriptions-card { width: 100%; text-align: left; font: inherit; cursor: pointer; }
.subscriptions-card .community-stat-icon.subscriptions { color: var(--theme-teal-text, #67e8f9); background: var(--theme-teal-soft, #22d3ee12); }
.subscriptions-card:focus-visible { outline: 2px solid var(--theme-teal-border, #80e0cf); outline-offset: 3px; }
.subscriptions-modal .sub-torrent-row { padding: 1rem; border: 1px solid var(--theme-border, #2b3a4c); border-radius: 12px; background: var(--theme-surface, #0e1822); }
.subscriptions-modal .sub-torrent-row:hover { border-color: var(--theme-teal-border, #80e0cf66); background: var(--theme-surface, #121f2a); }
.subscriptions-modal .sub-torrent-row:focus-visible { outline: 2px solid var(--theme-teal-border, #80e0cf); outline-offset: -3px; }
.subscriptions-modal .subscribed-list { gap: .75rem; }
.subscriptions-modal .subscribed-name { overflow-wrap: anywhere; line-height: 1.5; }
.subscriptions-modal .subscribed-meta, .subscriptions-modal .subscribed-empty { color: var(--theme-muted, #a5b4c7); }
</style>
@push('scripts')
<script>
(() => {
    const modal = document.getElementById('subscribedTorrentsModal');
    if (modal) document.body.appendChild(modal);
})();
</script>
@endpush
