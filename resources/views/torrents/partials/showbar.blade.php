

<div class="showbar-card modern-showbar mb-4 mt-4">

    {{-- HEADER --}}
    <div class="card-header modern-showbar-header">

        {{-- LEFT --}}
        <div class="d-flex align-items-center gap-3 flex-wrap">

            <div class="torrent-icon-box">
                <i class="bi bi-collection-play-fill"></i>
            </div>

            <div>

                <h4 class="modern-torrent-title mb-1">
                    {{ $torrent->name }}
                </h4>

                <div class="modern-subinfo">

                    <span>
                        <i class="bi bi-calendar3"></i>
                        {{ $torrent->created_at->format('M d, Y') }}
                    </span>

                    <span>
                        <i class="bi bi-person-circle"></i>
                        {{ $torrent->uploader->name ?? 'Unknown' }}
                    </span>

                </div>

            </div>

        </div>

        {{-- TAGS --}}
        <div class="modern-tags-wrap ms-md-auto">

            @if($torrent->free)
                <span class="modern-badge free-badge"
                      data-bs-toggle="tooltip"
                      title="This torrent is freeleech — downloading won't reduce your ratio.">

                    <i class="bi bi-lightning-fill"></i>
                    FREE

                </span>
            @endif

            @if($torrent->double)
                <span class="modern-badge double-badge"
                      data-bs-toggle="tooltip"
                      title="Double upload credit — seeding counts double toward your ratio.">

                    <i class="bi bi-chevron-double-up"></i>
                    DOUBLE

                </span>
            @endif

            @if($torrent->seedbox)
                <span class="modern-badge seedbox-badge"
                      data-bs-toggle="tooltip"
                      title="This torrent is hosted on a seedbox — expect high seed speed.">

                    <i class="bi bi-cloud-fill"></i>
                    SEEDBOX

                </span>
            @endif

            @if($torrent->sticky)
                <span class="modern-badge sticky-badge"
                      data-bs-toggle="tooltip"
                      title="Sticky torrent — always pinned to the top of all torrents.">

                    <i class="bi bi-pin-angle-fill"></i>
                    STICKY

                </span>
            @endif

            <a href="{{ route('tickets.create', ['torrent_id' => $torrent->id, 'category_id' => '6']) }}"
               class="modern-badge report-badge-modern text-decoration-none"
               data-bs-toggle="tooltip"
               title="Report a problem with this torrent">

                <i class="bi bi-flag-fill"></i>
                REPORT

            </a>

        </div>

    </div>

    {{-- BODY --}}
    <div class="card-body modern-showbar-body">

        {{-- LEFT ACTIONS --}}
        <div class="d-flex flex-wrap align-items-center gap-2">

            @if (Auth::check() && Auth::user()->hit_and_run_count > 20)

                <div class="modern-alert-danger">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    Download restricted: more than 20 H&R.

                </div>

            @else

                @if (Auth::check())

                    @php
                        $slots = Auth::user()->slots;
                        $userSeedboxes = \App\Models\Seedbox::where('user_id', auth()->id())->get();
                        $hasSeedboxes = $userSeedboxes->isNotEmpty();
                    @endphp

                    @if($slots > 0 || $hasSeedboxes)

                        <div class="btn-group">

                            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
                               class="btn modern-download-btn">

                                <i class="bi bi-download me-1"></i>

                                Download

                            </a>

                            <button type="button"
                                    class="btn modern-download-btn dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown">
                            </button>

                            <ul class="dropdown-menu dropdown-menu-dark modern-dropdown-menu shadow-lg">

                                {{-- FREE --}}
                                @if($slots > 0)

                                    <li>

                                        <a class="dropdown-item modern-dropdown-item"
                                           href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?free=1">

                                            <i class="bi bi-lightning-fill me-2"></i>

                                            Free Download

                                        </a>

                                    </li>

                                    <li>

                                        <a class="dropdown-item modern-dropdown-item"
                                           href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?double=1">

                                            <i class="bi bi-chevron-double-up me-2"></i>

                                            Double Upload

                                        </a>

                                    </li>

                                @endif

                                {{-- SEEDBOXES --}}
                                @if($hasSeedboxes)

    <li>
        <hr class="dropdown-divider">
    </li>

    <li class="dropdown-header text-info">

        <i class="bi bi-cloud-arrow-up-fill me-1"></i>
        Send to Seedbox

    </li>

    @foreach($userSeedboxes as $seedbox)

        <li>

            <form action="{{ route('torrents.sendToSeedbox', $torrent) }}"
                  method="POST"
                  class="m-0 p-0">

                @csrf

                <input type="hidden"
                       name="seedbox_id"
                       value="{{ $seedbox->id }}">

                <button type="submit"
                        class="dropdown-item modern-dropdown-item">

                    <i class="bi bi-hdd-network-fill me-2"></i>

                    {{ $seedbox->name }}

                </button>

            </form>

        </li>

    @endforeach

@endif

                            </ul>

                        </div>

                    @else

                        <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
                           class="btn modern-download-btn">

                            <i class="bi bi-download me-1"></i>

                            Download

                        </a>

                    @endif

                @endif

            @endif

            {{-- SUBTITLE --}}
            @php
                $allowedCategoryIds = [1,5,9,11,13,18,20,24,31,54,56,82];
            @endphp

            @if(in_array($torrent->category_id, $allowedCategoryIds))

                <button class="btn modern-action-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#uploadSubtitleModal">

                    <i class="bi bi-badge-cc-fill me-1"></i>

                    Subtitle

                </button>

            @endif

            {{-- EDIT --}}
            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::id() === $torrent->owner))

                <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
                   class="btn modern-action-btn info-btn"
                   data-bs-toggle="tooltip"
                   title="Edit Torrent">

                    <i class="bi bi-pencil-square"></i>

                    Edit

                </a>

            @endif

            {{-- BUMP --}}
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                @php
                    $recentlyUploaded = $torrent->created_at->gt(now()->subDays(15));
                    $recentlyBumped = $torrent->bumped_at && $torrent->bumped_at->gt(now()->subDays(30));
                @endphp

                @if (!$recentlyUploaded)

                    @if ($recentlyBumped)

                        <button class="btn modern-action-btn disabled opacity-75"
                                disabled
                                data-bs-toggle="tooltip"
                                title="Can be bumped again on {{ $torrent->bumped_at->copy()->addDays(30)->format('M d, Y') }}">

                            <i class="bi bi-x-circle"></i>

                            Bumped

                        </button>

                    @else

                        <form action="{{ route('torrents.bump', $torrent->id) }}"
                              method="POST">

                            @csrf

                            <button type="submit"
                                    class="btn modern-action-btn success-btn"
                                    data-bs-toggle="tooltip"
                                    title="Bump Torrent">

                                <i class="bi bi-arrow-up-circle"></i>

                                Bump

                            </button>

                        </form>

                    @endif

                @endif

            @endif

            {{-- THANK --}}
            @if(Auth::check() && !$hasThanked)

                <form action="{{ route('torrents.thank', $torrent->id) }}"
                      method="POST">

                    @csrf

                    <button type="submit"
                            class="btn modern-action-btn thank-btn"
                            data-bs-toggle="tooltip"
                            title="{{ $thankTooltip }}">

                        {{ $thankCount }}

                        <i class="bi bi-hand-thumbs-up"></i>

                    </button>

                </form>

            @else

                <button type="button"
                        class="btn modern-action-btn thanked-btn"
                        data-bs-toggle="tooltip"
                        title="{{ $thankTooltip }}">

                    {{ $thankCount }}

                    <i class="bi bi-hand-thumbs-up-fill"></i>

                </button>

            @endif

            {{-- SUBSCRIBE --}}
            @if($subscribeAvailable)

                @if($isSubscribed)

                    <form action="{{ route('torrents.unsubscribe', $torrent->id) }}"
                          method="POST">

                        @csrf

                        <button type="submit"
                                class="btn modern-action-btn unsubscribe-btn"
                                data-bs-toggle="tooltip"
                                title="Stop receiving notifications when a new version of this title is uploaded">

                            <i class="bi bi-bell-fill me-1"></i>

                            Subscribed

                        </button>

                    </form>

                @else

                    <form action="{{ route('torrents.subscribe', $torrent->id) }}"
                          method="POST">

                        @csrf

                        <button type="submit"
                                class="btn modern-action-btn subscribe-btn"
                                data-bs-toggle="tooltip"
                                title="Get notified whenever a new version of this title is uploaded">

                            <i class="bi bi-bell me-1"></i>

                            Subscribe

                        </button>

                    </form>

                @endif

            @endif

            {{-- SUBSCRIBERS (count + names beside the subscribe button).
                 Only shown when the torrent carries a TMDB id. --}}
            @if(!empty($torrent->tmdbid))
                @include('torrents.partials._subscribers-label', ['subscribers' => $subscribers ?? collect()])
            @endif

        </div>
        <div class="modern-stats-wrap ms-md-auto">

            <span class="modern-stat-badge category-badge"
                  data-bs-toggle="tooltip"
                  title="Category">

                <i class="bi bi-tags-fill"></i>

                {{ $torrent->category->name }}

            </span>

            @if(!empty($fileTree))

                <button type="button"
                        class="modern-stat-badge files-badge border-0"
                        data-bs-toggle="modal"
                        data-bs-target="#torrentFilesModal"
                        title="View torrent files">

                    <i class="bi bi-folder2-open"></i>

                    {{ $torrent->files->count() }} Files

                </button>

            @endif

            {{-- SEEDERS --}}
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                <a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?seeders"
                   class="modern-stat-badge seeders-badge text-decoration-none">

                    <i class="bi bi-cloud-arrow-up-fill"></i>

                    {{ $torrent->seeders }}

                </a>

            @else

                <span class="modern-stat-badge seeders-badge">

                    <i class="bi bi-cloud-arrow-up-fill"></i>

                    {{ $torrent->seeders }}

                </span>

            @endif

            {{-- LEECHERS --}}
            @if($torrent->leechers > 0)

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                    <a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?leechers"
                       class="modern-stat-badge leechers-badge text-decoration-none">

                        <i class="bi bi-cloud-arrow-down-fill"></i>

                        {{ $torrent->leechers }}

                    </a>

                @else

                    <span class="modern-stat-badge leechers-badge">

                        <i class="bi bi-cloud-arrow-down-fill"></i>

                        {{ $torrent->leechers }}

                    </span>

                @endif

            @endif

            {{-- COMPLETED --}}
            <span class="modern-stat-badge completed-badge">

                <i class="bi bi-download"></i>

                {{ $torrent->times_completed }}

            </span>

            {{-- SIZE --}}
            <span class="modern-stat-badge size-badge">

                <i class="bi bi-pie-chart-fill"></i>

                {{ \App\Helpers\FormatHelper::formatSize($torrent->size) }}

            </span>

        </div>

    </div>

</div>


<style>
.modern-showbar{position:relative;width:100%;max-width:100%;background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84));border:1px solid var(--ui-border);border-left:3px solid var(--ui-accent);border-radius:.9rem;overflow:visible;backdrop-filter:blur(14px);box-shadow:0 10px 30px rgba(0,0,0,.22);color:#e6edf3;z-index:1}
.modern-showbar::before{display:none}
.modern-showbar-header{padding:16px 18px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:transparent;border-bottom:1px solid var(--ui-border)}
.torrent-icon-box{width:48px;height:48px;border-radius:.7rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(45,212,191,.10);border:1px solid rgba(45,212,191,.22);color:var(--ui-accent);font-size:1.2rem;box-shadow:none}
.modern-torrent-title{margin:0;font-size:14px;line-height:1.4;font-weight:700;color:#f1f5f9;overflow-wrap:anywhere;word-break:break-word}
.modern-subinfo{display:flex;gap:12px;flex-wrap:wrap;margin-top:5px;color:rgba(255,255,255,.58);font-size:13px}
.modern-subinfo span{display:inline-flex;align-items:center;gap:5px}.modern-subinfo i{color:var(--ui-accent)}
.modern-tags-wrap,.modern-stats-wrap{display:flex;flex-wrap:wrap;gap:7px;justify-content:flex-end;align-items:center;margin-left:auto}
.modern-badge,.modern-stat-badge{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:6px 9px;border-radius:.55rem;font-size:12px;line-height:1.2;font-weight:600;border:1px solid var(--ui-border);white-space:nowrap;transition:background .15s ease,border-color .15s ease,transform .15s ease}
.modern-badge:hover,.modern-stat-badge:hover{transform:translateY(-1px)}
.free-badge{background:rgba(34,197,94,.10);color:#70e0a1}.double-badge{background:rgba(250,204,21,.10);color:#f3d46a}.seedbox-badge{background:rgba(6,182,212,.10);color:#67dce9}.sticky-badge{background:rgba(239,68,68,.10);color:#f58b8b}
.report-badge-modern{background:rgba(239,68,68,.08);color:#f58b8b;text-decoration:none}.report-badge-modern:hover{background:rgba(239,68,68,.14);color:#ffaaaa;box-shadow:none}
.modern-showbar-body{padding:16px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}
.modern-download-btn,.modern-action-btn{border:1px solid var(--ui-border);color:#dce7ef;background:rgba(255,255,255,.045);padding:8px 12px;border-radius:.55rem;font-size:13px;font-weight:600;transition:background .15s ease,border-color .15s ease,color .15s ease,transform .15s ease}
.modern-download-btn{background:rgba(45,212,191,.12);border-color:rgba(45,212,191,.28);color:var(--ui-accent)}
.modern-download-btn:hover{background:rgba(45,212,191,.18);border-color:rgba(45,212,191,.42);color:#b8fff5;transform:translateY(-1px)}
.modern-action-btn:hover{background:rgba(45,212,191,.09);border-color:rgba(45,212,191,.25);color:var(--ui-accent);transform:translateY(-1px)}
.info-btn{color:#8fd5ff;background:rgba(59,130,246,.08)}.success-btn{color:#70e0a1;background:rgba(34,197,94,.08)}.thank-btn{color:#8fd5ff;background:rgba(59,130,246,.08)}.thanked-btn{color:#70e0a1;background:rgba(34,197,94,.10)}.subscribe-btn{color:#8fd5ff;background:rgba(59,130,246,.08)}.unsubscribe-btn{color:#ff8f8f;background:rgba(239,68,68,.10)}
.modern-action-btn.disabled,.modern-action-btn:disabled{opacity:.55!important;cursor:not-allowed;transform:none!important}
.modern-dropdown-menu{min-width:250px;padding:7px;background:rgba(15,23,42,.98);border:1px solid var(--ui-border);border-radius:.7rem;box-shadow:0 14px 35px rgba(0,0,0,.35)!important}
.modern-dropdown-item{padding:8px 10px;border-radius:.45rem;font-size:13px;color:#d8e2eb;transition:background .15s ease,color .15s ease}
.modern-dropdown-item:hover{background:rgba(45,212,191,.09);color:var(--ui-accent);transform:none}
.modern-dropdown-menu .dropdown-header{font-size:12px;color:var(--ui-accent)!important}.modern-dropdown-menu .dropdown-divider{border-color:var(--ui-border)}
.modern-alert-danger{background:rgba(220,38,38,.09);border:1px solid rgba(220,38,38,.22);color:#f4a0a0;padding:9px 12px;border-radius:.6rem;font-size:13px;font-weight:600}
.category-badge{background:rgba(148,163,184,.08);color:#c6d0da}.files-badge{background:rgba(250,204,21,.08);color:#e8cf6d}.seeders-badge{background:rgba(34,197,94,.08);color:#70e0a1}.leechers-badge{background:rgba(239,68,68,.08);color:#f58b8b}.completed-badge{background:rgba(59,130,246,.08);color:#8fc8f5}.size-badge{background:rgba(6,182,212,.08);color:#67dce9}
.modern-stat-badge.text-decoration-none:hover{text-decoration:none!important;border-color:rgba(45,212,191,.25)}
.btn-group{position:relative}.btn-group .dropdown-menu,.dropdown-menu{z-index:999999!important}
@media(max-width:768px){
html,body{overflow-x:hidden!important}.modern-showbar{border-radius:.75rem}.modern-showbar-header{padding:13px 14px;gap:12px}.modern-showbar-body{padding:13px 14px;gap:12px}
.torrent-icon-box{width:40px;height:40px;font-size:1rem}.modern-torrent-title{font-size:14px;line-height:1.4;max-width:100%}.modern-subinfo{font-size:12px;gap:8px}
.modern-tags-wrap,.modern-stats-wrap{width:100%;margin-left:0;justify-content:flex-start;flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;padding-bottom:3px;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.modern-tags-wrap::-webkit-scrollbar,.modern-stats-wrap::-webkit-scrollbar{display:none}.modern-badge,.modern-stat-badge{flex:0 0 auto;font-size:12px}
.modern-showbar-body>div:first-child{width:100%;min-width:0;display:flex;flex-wrap:wrap;gap:7px}.modern-download-btn,.modern-action-btn{font-size:12px;padding:8px 10px}
.modern-dropdown-menu{max-width:calc(100vw - 28px)}
}
/* Subscriber count + names next to subscribe button */
.subscribers-label{display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12.5px;color:var(--ui-text-muted);line-height:1.3;padding:2px 0}
.subscribers-label i{color:var(--ui-accent)}
.subscribers-label .subscribers-count{font-weight:700;color:#f1f5f9;white-space:nowrap}
.subscribers-label .subscribers-names{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px}
.subscribers-label .subscribers-more{color:var(--ui-accent);font-weight:700}
</style>

