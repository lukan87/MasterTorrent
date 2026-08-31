@if($groupedTorrents->count())
<div class="container-fluid my-5">
    <div class="p-4"
         style="background: rgba(30,30,40,0.6);
                backdrop-filter: blur(2px);
                border-radius: 20px;
                padding: 25px;
                box-shadow: 0 20px 50px rgba(0,0,0,0.7);
                border: 1px solid rgba(255,255,255,0.08);
                color: #fff;">
        
        <h2 class="mb-4 text-white">
            <i class="bi bi-cloud-arrow-down"></i> Available Torrents
        </h2>

        <div class="accordion" id="seasonsAccordion">
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
                                <h5 class="mt-3 text-info">
                                    <i class="bi bi-archive-fill me-2"></i> Complete Season Pack(s)
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped table-hover rounded shadow-sm mb-4 align-middle">
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
                                                    <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        <a class="text-muted" 
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
                                                           class="btn btn-sm btn-outline-success rounded-circle" 
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
                                <h5 class="mt-3 text-info">
                                    <i class="bi bi-film me-2"></i> Episodes
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped table-hover rounded shadow-sm align-middle">
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
                                                    <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        <a class="text-muted" 
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
                                                           class="btn btn-sm btn-outline-success rounded-circle" 
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