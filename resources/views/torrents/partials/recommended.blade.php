@if(!$recommendedTorrents->isEmpty())
    <div class="card card-blur mt-3 shadow-sm border-0">
        <div class="card-header bg-secondary text-white py-2">
            <h6 class="mb-0">Recommended Torrents</h6>
        </div>
        <div class="card-body p-2">
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-6 g-3">
                @foreach($recommendedTorrents as $recommended)
                    <div class="col">
                        <a href="{{ route('torrents.show', ['id' => $recommended->id, 'slug' => $recommended->slug]) }}"
                           class="text-decoration-none" 
                           data-bs-toggle="tooltip" 
                           title="{{ $recommended->name }}">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="{{ $recommended->poster ?? '/images/noposter.jpg' }}"
                                     alt="{{ $recommended->name }}"
                                     class="card-img-top img-fluid rounded">
                                <div class="card-body text-center p-2">
                                    <div class="small text-muted">
                                        S: <strong>{{ $recommended->seeders }}</strong> |
                                        L: <strong>{{ $recommended->leechers }}</strong> |
                                        C: <strong>{{ $recommended->times_completed }}</strong>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
