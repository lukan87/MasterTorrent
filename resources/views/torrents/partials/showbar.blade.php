

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

        </div>

        {{-- RIGHT STATS --}}
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

.modern-showbar{
    position:relative;

    background:
        linear-gradient(
            145deg,
            rgba(20, 25, 40, 0),
            rgba(10,14,24,.96)
        );

    border-radius:24px;

    border:
        1px solid rgba(255,255,255,.06);

    overflow:visible;

    isolation:isolate;

    backdrop-filter:blur(18px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.45);

    color:#fff;

    z-index:1;
}

.modern-showbar::before{
    pointer-events:none;
    max-width:100vw;
}



.dropdown-menu{
    z-index:99999 !important;
}

.modern-showbar::before{

    content:'';

    position:absolute;

    top:-120px;
    
    width:280px;
    height:280px;

    background:
        radial-gradient(
            circle,
            rgba(59, 131, 246, 0.413),
            transparent 70%
        );

    pointer-events:none;
}

.modern-showbar-header{

    padding:22px 24px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.torrent-icon-box{

    width:62px;
    height:62px;

    border-radius:18px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    font-size:1.5rem;

    box-shadow:
        0 10px 25px rgba(59,130,246,.35);
}

.modern-torrent-title{

    font-size:1.5rem;

    font-weight:800;

    margin:0;
}

.modern-subinfo{

    display:flex;

    gap:15px;

    flex-wrap:wrap;

    margin-top:6px;

    color:rgba(255,255,255,.65);

    font-size:1.1rem;
}

.modern-badge{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:9px 14px;

    border-radius:50px;

    font-size:.78rem;

    font-weight:800;

    border:
        1px solid rgba(255,255,255,.05);
}

.free-badge{
    background:rgba(34,197,94,.18);
    color:#4ade80;
}

.double-badge{
    background:rgba(250,204,21,.18);
    color:#fde047;
}

.seedbox-badge{
    background:rgba(6,182,212,.18);
    color:#67e8f9;
}

.sticky-badge{
    background:rgba(239,68,68,.18);
    color:#f87171;
}

.report-badge-modern{

    background:
        rgba(190,24,93,.18);

    color:#f9a8d4;

    transition:.25s ease;
}

.report-badge-modern:hover{

    transform:translateY(-2px);

    color:white;

    box-shadow:
        0 8px 20px rgba(190,24,93,.3);
}

.modern-showbar-body{

    padding:24px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    flex-wrap:wrap;

    gap:24px;
}

.modern-download-btn{

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    border:none;

    color:white;

    padding:12px 18px;

    font-weight:700;

    border-radius:14px;
}

.modern-action-btn{

    border:none;

    background:
        rgba(255,255,255,.06);

    color:white;

    padding:12px 16px;

    border-radius:14px;

    font-weight:700;

    transition:.25s ease;
}

.modern-action-btn:hover,
.modern-download-btn:hover{

    transform:translateY(-2px);

    color:white;
}

.modern-dropdown-menu{

    border-radius:18px;

    background:
        rgba(15,20,35,.98);

    border:
        1px solid rgba(255,255,255,.06);

    padding:10px;
}

.modern-dropdown-item{

    border-radius:12px;

    padding:11px 14px;

    transition:.2s ease;
}

.modern-dropdown-item:hover{

    background:
        rgba(59,130,246,.18);

    color:#93c5fd;

    transform:translateX(4px);
}

.modern-alert-danger{

    background:
        rgba(220,38,38,.15);

    border:
        1px solid rgba(220,38,38,.25);

    color:#fca5a5;

    padding:12px 16px;

    border-radius:14px;

    font-weight:600;
}

.modern-stat-badge{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:11px 15px;

    border-radius:14px;

    font-weight:700;

    font-size:.9rem;

    border:
        1px solid rgba(255,255,255,.05);
}

.category-badge{
    background:rgba(148,163,184,.16);
    color:#cbd5e1;
}

.files-badge{
    background:rgba(250,204,21,.16);
    color:#fde047;
}

.seeders-badge{
    background:rgba(34,197,94,.16);
    color:#4ade80;
}

.leechers-badge{
    background:rgba(239,68,68,.16);
    color:#f87171;
}

.completed-badge{
    background:rgba(59,130,246,.16);
    color:#93c5fd;
}

.size-badge{
    background:rgba(6,182,212,.16);
    color:#67e8f9;
}

.info-btn{
    background:rgba(59,130,246,.16);
    color:#93c5fd;
}

.success-btn{
    background:rgba(34,197,94,.16);
    color:#4ade80;
}

.thank-btn{
    background:rgba(59,130,246,.16);
    color:#93c5fd;
}

.thanked-btn{
    background:rgba(34,197,94,.18);
    color:#4ade80;
}


.modern-dropdown-menu{
    min-width:260px;
}

.btn-group{
    position:relative;
}

.btn-group .dropdown-menu{
    position:absolute;
    top:100%;
    left:0;
    z-index:999999;
}




/* =========================
   RIGHT ALIGN TAGS
========================= */

.modern-tags-wrap{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    justify-content:flex-end;

    align-items:center;

    margin-left:auto;
}

/* =========================
   RIGHT ALIGN STATS
========================= */

.modern-stats-wrap{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    justify-content:flex-end;

    align-items:center;

    margin-left:auto;
}

/* MOBILE */
@media(max-width:768px){

    .modern-tags-wrap,
    .modern-stats-wrap{

        width:100%;

        margin-left:0;

        justify-content:flex-start;

        overflow-x:auto;

        overflow-y:hidden;

        flex-wrap:nowrap;

        padding-bottom:4px;

        scrollbar-width:none;

        -ms-overflow-style:none;
    }

    .modern-tags-wrap::-webkit-scrollbar,
    .modern-stats-wrap::-webkit-scrollbar{
        display:none;
    }

    .modern-tags-wrap .modern-badge,
    .modern-stats-wrap .modern-stat-badge{

        flex:25% 0 auto;

        white-space:nowrap;
    }
}


/* =========================
   TORRENT TITLE FIX
========================= */

.modern-torrent-title{

    font-size:1.5rem;

    font-weight:800;

    margin:0;

    line-height:1.3;

    overflow-wrap:anywhere;

    word-break:break-word;
}

/* MOBILE */
@media(max-width:768px){

    .modern-torrent-title{

        font-size:1.1rem;

        max-width:100%;

        white-space:normal;
    }

    .modern-showbar-header{

        overflow:hidden;
    }
}

/* =========================================
   FINAL MOBILE FIX
========================================= */

@media (max-width:768px){

    html,
    body{
        overflow-x:hidden !important;
    }

   .modern-showbar{
    width:100%;
    max-width:100%;
    overflow:visible;
}

    .modern-showbar-header,
.modern-showbar-body{
    width:100%;
    max-width:100%;
    overflow:visible;
    box-sizing:border-box;
}
    /* LEFT SIDE */
    .modern-showbar-header > div:first-child{
        width:100%;
        min-width:0;
    }

    .modern-torrent-title{

        font-size:1rem;

        line-height:1.35;

        width:100%;

        max-width:100%;

        overflow-wrap:anywhere;

        word-break:break-word;
    }

    .modern-subinfo{

        display:flex;

        flex-wrap:wrap;

        gap:8px;

        font-size:.8rem;
    }

    /* TAGS */
    .modern-tags-wrap,
    .modern-stats-wrap{

        display:flex;

        flex-wrap:nowrap;

        gap:8px;

        width:100%;

        max-width:100%;

        overflow-x:auto;
        overflow-y:hidden;

        padding-bottom:4px;

        margin-left:0;

        justify-content:flex-start;

        scrollbar-width:none;

        -webkit-overflow-scrolling:touch;

        box-sizing:border-box;
    }

    .modern-tags-wrap::-webkit-scrollbar,
    .modern-stats-wrap::-webkit-scrollbar{
        display:none;
    }

    .modern-badge,
    .modern-stat-badge{

        flex:0 0 auto;

        white-space:nowrap;

        max-width:none;
    }

    /* ACTION BUTTONS */
/* ACTION BUTTONS */
.modern-showbar-body > div:first-child{

    width:100%;

    min-width:0;

    display:flex;

    flex-wrap:wrap;

    gap:8px;

    overflow:visible;
}

    .modern-showbar-body > div:first-child::-webkit-scrollbar{
        display:none;
    }

    .modern-download-btn,
    .modern-action-btn{

        flex:0 0 auto;

        white-space:nowrap;

        font-size:.85rem;

        padding:10px 14px;
    }
}

.btn-group{
    position:relative;
}

.btn-group .dropdown-menu{
    position:absolute;
    z-index:999999;
}

</style>
