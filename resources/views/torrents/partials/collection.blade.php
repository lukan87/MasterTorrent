@php

    $collection = data_get($display, 'collection');

    $collectionMovies = collect(data_get($display, 'collection_movies', []));

    $collectionCollapseId = $collection ? 'collection-'.data_get($collection, 'id') : null;



    $total = $collectionMovies->count();

    $inDb = $collectionMovies->where('in_db', true)->count();

    $percent = $total ? round(($inDb / $total) * 100) : 0;

    $currentTmdbId = data_get($collection, 'current_tmdb_id');

@endphp



@if($display['type'] === 'movie' && $collection && $collectionMovies->isNotEmpty())



<div class="collection-card mt-5 mb-5">



    {{-- HEADER --}}

    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">



        {{-- LEFT: Clickable Collection Name --}}

        <a href="{{ route('collections.show', $display['collection']['id']) }}"

           class="fw-bold fs-5 text-decoration-none d-flex align-items-center gap-2">

            <i class="bi bi-collection-play"></i>

            {{ $display['collection']['name'] }}

        </a>



        {{-- RIGHT: Collapse Toggle --}}

        <div class="d-flex align-items-center gap-3">



            <small class="text-muted">

                {{ $inDb }} / {{ $total }} in database

            </small>



            <div class="cursor-pointer"

                 data-bs-toggle="collapse"

                 data-bs-target="#{{ $collectionCollapseId }}"

                 aria-expanded="false"

                 role="button">



                <i class="bi bi-chevron-down collapse-icon"></i>

            </div>

        </div>

    </div>



    {{-- COLLAPSIBLE CONTENT --}}

    <div id="{{ $collectionCollapseId }}" class="collapse">

        <div class="card-body p-0">



        {{-- MOVIES --}}

        @foreach($collectionMovies as $movie)

            @php

                $torrents = collect($movie['torrents'] ?? []);

                $isCurrent = $movie['tmdb_id'] === $currentTmdbId;

                 $collapseId = 'movie-'.$movie['tmdb_id'];

            @endphp



            <div class="collection-movie {{ $isCurrent ? 'is-current' : '' }}">



                {{-- MOVIE HEADER --}}

               <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center

     cursor-pointer"

     data-bs-toggle="collapse"

     data-bs-target="#movie-{{ $movie['tmdb_id'] }}"

     aria-expanded="{{ $isCurrent ? 'true' : 'false' }}"

     role="button">



    <div>

        <strong class="fs-5">{{ $movie['title'] }}</strong>



        @if($isCurrent)

            <span class="badge bg-info ms-2">Current</span>

        @endif

    </div>



    <div class="d-flex align-items-center gap-2">

        @if($movie['in_db'])

            <span class="badge bg-success">Available</span>

        @else

            <span class="badge bg-warning text-dark">Missing</span>

        @endif



        <i class="bi bi-chevron-down ms-2"></i>

    </div>

</div>



{{-- COLLAPSIBLE CONTENT --}}

                <div id="{{ $collapseId }}"

                     class="collapse {{ $isCurrent ? 'show' : '' }}">





                {{-- TORRENT TABLE HEADER --}}

                @if($torrents->isNotEmpty())

                <div class="row g-0 px-4 py-2 small text-uppercase text-muted border-bottom">

                    <div class="col-md-5">Torrent</div>

                    <div class="d-none d-md-block col-md-2 text-center">

                        <i class="bi bi-calendar-week fs-5" data-bs-toggle="tooltip" title="Uploaded"></i>

                    </div>

                    <div class="d-none d-md-block col-md-2 text-center">

                        <i class="bi bi-floppy fs-5" data-bs-toggle="tooltip" title="Size"></i>

                    </div>

                    <div class="col-md-2 text-center">

                        <i class="bi bi-arrow-up-circle text-success fs-5" data-bs-toggle="tooltip" title="Seeders"></i> /

                        <i class="bi bi-arrow-down-circle text-danger fs-5" data-bs-toggle="tooltip" title="Leechers"></i>

                    </div>

                    <div class="d-none d-md-block col-md-1 text-center">

                        <i class="bi bi-check-circle fs-5" data-bs-toggle="tooltip" title="Completed"></i>

                    </div>

                </div>

                @endif



                {{-- TORRENTS --}}

                @forelse($torrents as $torrent)

                <div class="row g-0 align-items-center px-4 py-2 border-bottom collection-torrent">



                    <div class="col-md-5">

                        <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}"

                           class="fw-semibold text-decoration-none">

                            {{ $torrent->name }}

                        </a>

                    </div>



                    <div class="d-none d-md-block col-md-2 text-center small text-muted">

                        {{ $torrent->created_at->diffForHumans(null, true) }}

                    </div>



                    <div class="d-none d-md-block col-md-2 text-center text-info">

                        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}

                    </div>



                    <div class="col-md-2 text-center fw-bold">

                        <span class="text-success">{{ $torrent->seeders }}</span> /

                        <span class="text-danger">{{ $torrent->leechers }}</span>

                    </div>



                    <div class="d-none d-md-block col-md-1 text-center text-success fw-bold">

                        {{ $torrent->times_completed }}

                    </div>



                </div>

                @empty

                    {{-- MISSING MOVIE --}}

                    <div class="px-4 py-4  text-muted">

                        <p class="mb-2">No torrents available</p>



                        @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))

                            <a href="{{ route('torrents.create') }}"

                               class="btn btn-sm btn-outline-success">

                                <i class="bi bi-upload"></i> Upload

                            </a>

                        @elseif(Auth::check())

                            <a href="{{ route('requests.create', ['tmdb' => $movie['tmdb_id']]) }}"

                               class="btn btn-sm btn-outline-warning">

                                Request

                            </a>

                        @else

                            <a href="{{ route('login') }}"

                               class="btn btn-sm btn-outline-secondary">

                                Login

                            </a>

                        @endif

                    </div>

                @endforelse



            </div>

            </div>

        @endforeach



    </div>



</div>

</div>

@endif







<style>
.collection-card{overflow:hidden;background:linear-gradient(135deg,rgba(14,21,33,.96),rgba(10,15,27,.90));border:1px solid rgba(148,163,184,.16);border-radius:.75rem;box-shadow:0 14px 34px rgba(0,0,0,.26);color:#e2e8f0}.collection-card>.border-bottom{background:rgba(1,4,15,.24);border-color:rgba(148,163,184,.14)!important}.collection-card a{color:#e2e8f0;transition:color .18s ease}.collection-card a:hover{color:#5eead4}.collection-card .text-muted{color:#94a3b8!important}.collection-card .text-info{color:#67e8df!important}.collection-card .border-bottom{border-color:rgba(148,163,184,.13)!important}.cursor-pointer{cursor:pointer}.collapse-icon,.collection-movie .bi-chevron-down{color:#94a3b8;transition:transform .2s ease,color .2s ease}.collection-card [aria-expanded="true"] .collapse-icon,.collection-movie [aria-expanded="true"] .bi-chevron-down{transform:rotate(180deg);color:#5eead4}.collection-movie{background:rgba(1,4,15,.12);transition:background .18s ease}.collection-movie:hover{background:rgba(20,27,38,.32)}.collection-movie.is-current{background:rgba(20,184,166,.055);box-shadow:inset 3px 0 0 rgba(45,212,191,.55)}.collection-movie strong.fs-5{color:#f8fafc;font-size:.95rem!important;font-weight:700!important}.collection-card .badge{border-radius:.4rem;padding:.3rem .5rem;font-size:.7rem;font-weight:700}.collection-card .badge.bg-info{background:rgba(8,145,178,.2)!important;border:1px solid rgba(34,211,238,.24);color:#a5f3fc!important}.collection-card .badge.bg-success{background:rgba(6,78,59,.42)!important;border:1px solid rgba(45,212,191,.22);color:#a7f3d0!important}.collection-card .badge.bg-warning{background:rgba(120,53,15,.38)!important;border:1px solid rgba(251,191,36,.24);color:#fde68a!important}.collection-torrent{background:rgba(1,4,15,.16);transition:background .18s ease}.collection-torrent:hover{background:rgba(20,27,38,.42)}.collection-torrent a{color:#cbd5e1;font-size:.88rem}.collection-torrent a:hover{color:#5eead4}.collection-torrent .text-success{color:#86efac!important}.collection-torrent .text-danger{color:#fca5a5!important}.collection-card .btn{border-radius:.5rem;font-size:.78rem;font-weight:700;padding:.4rem .7rem}.collection-card .btn-outline-success{border-color:rgba(45,212,191,.35);color:#5eead4}.collection-card .btn-outline-warning{border-color:rgba(251,191,36,.35);color:#fde68a}.collection-card .btn-outline-secondary{border-color:rgba(148,163,184,.28);color:#cbd5e1}
@@media(max-width:768px){.collection-card{border-radius:.65rem}.collection-card>.border-bottom,.collection-movie>.border-bottom,.collection-torrent{padding-left:.9rem!important;padding-right:.9rem!important}.collection-card>.border-bottom{align-items:flex-start!important;gap:.75rem;flex-direction:column}.collection-card>.border-bottom>.d-flex{width:100%;justify-content:space-between}.collection-movie>.border-bottom{gap:.65rem}.collection-movie strong.fs-5{font-size:.9rem!important}.collection-torrent{row-gap:.45rem;padding-top:.75rem!important;padding-bottom:.75rem!important}.collection-torrent .col-md-5,.collection-torrent .col-md-2{width:100%;text-align:left!important}}
</style>
