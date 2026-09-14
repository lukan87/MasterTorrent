
<style>
/* FileIplay — Series Torrents / Seasons */
.series-torrents-wrap{margin:1.25rem 0}
.series-torrents-card{
    background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.88));
    border:1px solid var(--ui-border,rgba(255,255,255,.08));
    border-radius:.75rem;
    padding:1rem;
    box-shadow:0 10px 30px rgba(0,0,0,.28);
    color:#e7eef7;
}
.series-torrents-title{
    display:flex;align-items:center;gap:.5rem;
    margin:0 0 1rem;font-size:1.15rem;font-weight:700;color:#f1f5f9;
}
.series-torrents-title i{color:var(--ui-accent,#22d3ee)}
.series-torrents-accordion .accordion-item{
    background:rgba(11,18,32,.72)!important;
    border:1px solid rgba(255,255,255,.07)!important;
    border-radius:.6rem!important;
    overflow:hidden;
}
.series-torrents-accordion .accordion-button{
    background:rgba(17,27,45,.92)!important;
    color:#e8eef7!important;
    border:0!important;
    box-shadow:none!important;
    padding:.7rem .85rem;
    font-size:.9rem;font-weight:600;
}
.series-torrents-accordion .accordion-button:not(.collapsed){
    color:#fff!important;
    background:rgba(20,38,57,.96)!important;
}
.series-torrents-accordion .accordion-button::after{filter:invert(1) brightness(1.4);opacity:.75}
.series-torrents-accordion .accordion-button .text-info,
.series-torrents-accordion .accordion-button i.bi-collection-play{color:var(--ui-accent,#22d3ee)!important}
.series-torrents-accordion .toggle-icon{color:#94a3b8;font-size:.75rem}
.series-torrents-accordion .accordion-body{
    background:rgba(8,14,25,.72)!important;
    padding:.8rem;
}
.series-torrents-section-title{
    color:var(--ui-accent,#22d3ee)!important;
    font-size:.82rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.03em;
    margin:.35rem 0 .65rem;
}
.series-torrents-table{
    --bs-table-bg:transparent;
    --bs-table-color:#dbe5ef;
    --bs-table-border-color:rgba(255,255,255,.06);
    margin-bottom:1rem!important;
    font-size:.82rem;
}
.series-torrents-table thead th{
    background:rgba(255,255,255,.035);
    color:#94a3b8;
    border-bottom:1px solid rgba(255,255,255,.08);
    font-size:.7rem;text-transform:uppercase;letter-spacing:.035em;
    font-weight:700;padding:.55rem .6rem;
}
.series-torrents-table tbody td{padding:.55rem .6rem;vertical-align:middle}
.series-torrents-table tbody tr{transition:background .15s ease}
.series-torrents-table tbody tr:hover{background:rgba(34,211,238,.045)!important}
.series-torrent-link{
    color:#dce8f2!important;text-decoration:none;
    font-weight:600;
}
.series-torrent-link:hover{color:var(--ui-accent,#22d3ee)!important}
.series-torrents-table .badge{
    font-size:.68rem;font-weight:600;padding:.3rem .45rem;
}
.series-download-btn{
    width:30px;height:30px;padding:0;
    display:inline-flex;align-items:center;justify-content:center;
    border-radius:.45rem!important;
    border:1px solid rgba(34,197,94,.4)!important;
}
.series-download-btn:hover{transform:translateY(-1px)}
@media(max-width:767.98px){
    .series-torrents-wrap{margin:.8rem 0}
    .series-torrents-card{padding:.7rem;border-radius:.6rem}
    .series-torrents-title{font-size:1rem;margin-bottom:.75rem}
    .series-torrents-accordion .accordion-body{padding:.55rem}
    .series-torrents-table{font-size:.78rem}
    .series-torrents-table tbody td{padding:.5rem .4rem}
    .series-torrent-link{display:block;max-width:calc(100vw - 125px);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
}
</style>

@if($groupedTorrents->count())
<div class="container-fluid series-torrents-wrap">
    <div class="series-torrents-card">
        
        <h2 class="series-torrents-title">
            <i class="bi bi-cloud-arrow-down"></i> Available Torrents
        </h2>

        <div class="accordion series-torrents-accordion" id="seasonsAccordion">
            @foreach($groupedTorrents as $season => $seasonTorrents)
                <div class="accordion-item bg-dark text-white border-0 mb-3 shadow-sm rounded">
                    <h2 class="accordion-header" id="heading-{{ Str::slug($season) }}">
                        <button class="accordion-button collapsed bg-dark text-white d-flex justify-content-between align-items-center" 
                                type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ Str::slug($season) }}" 
                                aria-expanded="false" 
                                aria-controls="collapse-{{ Str::slug($season) }}">
                            <span><i class="bi bi-collection-play me-2 text-info"></i> {{ $season }}</span>
                            <i class="bi bi-plus-lg toggle-icon"></i>
                        </button>
                    </h2>

                    <div id="collapse-{{ Str::slug($season) }}" 
                         class="accordion-collapse collapse" 
                         aria-labelledby="heading-{{ Str::slug($season) }}" 
                         data-bs-parent="#seasonsAccordion">
                        <div class="accordion-body bg-dark">

                            @php
                                $completePacks = $seasonTorrents->filter(fn($t) => preg_match('/S\d{1,2}(?!E\d)/i', $t->name));
                                $episodes = $seasonTorrents->filter(fn($t) => preg_match('/S\d{1,2}E\d{1,2}/i', $t->name) || preg_match('/\d{1,2}x\d{1,2}/i', $t->name));

                                $mergedComplete = $completePacks->groupBy('tmdbid')->map(fn($group) => [
                                    'title' => $group->first()->name,
                                    'torrents' => $group,
                                ]);

                                $sortedEpisodes = $episodes->sortBy(fn($torrent) => 
                                    preg_match('/S\d{1,2}E(\d{1,2})/i', $torrent->name, $m) ? (int)$m[1] :
                                    (preg_match('/(\d{1,2})x(\d{1,2})/i', $torrent->name, $m) ? (int)$m[2] : 999)
                                );
                            @endphp

                            {{-- Complete Season Pack(s) --}}
                            @if($mergedComplete->count())
                                <h5 class="series-torrents-section-title">
                                    <i class="bi bi-archive-fill me-2"></i> Complete Season Pack(s)
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped table-hover rounded shadow-sm mb-4 align-middle series-torrents-table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th class="d-none d-sm-table-cell">Seeders / Leechers</th>
                                                <th class="text-center">DL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mergedComplete as $pack)
                                                @php $torrent = $pack['torrents']->first(); @endphp
                                                <tr>
                                                    <td>
                                                        <a class="series-torrent-link" 
                                                           href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}">
                                                            <strong>{{ $pack['title'] }}</strong>
                                                        </a>
                                                        {{-- Mobile seeders/leechers under title --}}
                                                        <div class="d-block d-sm-none mt-1">
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-arrow-up-circle"></i> {{ $torrent->seeders ?? 0 }}
                                                            </span>
                                                            <span class="badge bg-danger ms-1">
                                                                <i class="bi bi-arrow-down-circle"></i> {{ $torrent->leechers ?? 0 }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="d-none d-sm-table-cell">
                                                        <span class="badge bg-success">{{ $torrent->seeders ?? 0 }}</span>
                                                        /
                                                        <span class="badge bg-danger">{{ $torrent->leechers ?? 0 }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                                                           class="btn btn-sm btn-outline-success series-download-btn" 
                                                           data-bs-toggle="tooltip" data-bs-title="Download Torrent">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Episodes --}}
                            @if($sortedEpisodes->count())
                                <h5 class="series-torrents-section-title">
                                    <i class="bi bi-film me-2"></i> Episodes
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped table-hover rounded shadow-sm align-middle series-torrents-table">
                                        <thead>
                                            <tr>
                                                <th class="d-none d-md-table-cell">Ep</th>
                                                <th>Title</th>
                                                <th class="d-none d-lg-table-cell">Size</th>
                                                <th class="d-none d-sm-table-cell">Seeders / Leechers</th>
                                                <th class="text-center">DL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sortedEpisodes as $torrent)
                                                @php
                                                    $episode = null;
                                                    if (preg_match('/S\d{1,2}E(\d{1,2})/i', $torrent->name, $m)) $episode = (int)$m[1];
                                                    elseif (preg_match('/(\d{1,2})x(\d{1,2})/i', $torrent->name, $m)) $episode = (int)$m[2];
                                                @endphp
                                                <tr>
                                                    <td class="d-none d-md-table-cell">{{ $episode ?? 'N/A' }}</td>
                                                    <td>
                                                        <a class="series-torrent-link" 
                                                           href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}"
                                                           data-bs-toggle="tooltip" data-bs-html="true"
                                                           data-bs-title="<img src='{{ $torrent->poster }}' loading='lazy' class='img-fluid rounded' style='max-width: 180px;'>">
                                                            <strong>{{ $torrent->name }}</strong>
                                                        </a>
                                                        {{-- Mobile seeders/leechers under title --}}
                                                        <div class="d-block d-sm-none mt-1">
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-arrow-up-circle"></i> {{ $torrent->seeders ?? 0 }}
                                                            </span>
                                                            <span class="badge bg-danger ms-1">
                                                                <i class="bi bi-arrow-down-circle"></i> {{ $torrent->leechers ?? 0 }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="d-none d-lg-table-cell">{{ \App\Helpers\FormatHelper::formatSize($torrent->size) ?? 'N/A' }}</td>
                                                    <td class="d-none d-sm-table-cell">
                                                        <span class="badge bg-success">{{ $torrent->seeders ?? 0 }}</span>
                                                        /
                                                        <span class="badge bg-danger">{{ $torrent->leechers ?? 0 }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                                                           class="btn btn-sm btn-outline-success series-download-btn" 
                                                           data-bs-toggle="tooltip" data-bs-title="Download Torrent">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endif