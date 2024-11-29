@if($recommendedTorrents->isEmpty())

@else
    <div class="card card-blur mt-3">
        <div class="card-header">
            <h5>Recommended Torrents</h5>
        </div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
                @foreach($recommendedTorrents as $recommended)
                @if($recommended->poster)
                    <div class="col">
                    <a href="{{ route('torrents.show', ['id' => $recommended->id, 'slug' => $recommended->slug]) }}"
                    class="text-decoration-none h5 mb-2 d-block" data-bs-toggle="tooltip" title="{{ $recommended->name }}">
                    <div class="card shadow-lg rounded-lg border-0 overflow-hidden h-100">

                            <img src="{{ $recommended->poster }}"
                                 alt="{{ $recommended->name }}"
                                 class="card-img-top img-fluid rounded-3">

                            <div class="card-body text-center">

                                <div class="text-muted small mb-2">
                                    S: <strong>{{ $recommended->seeders }}</strong> |
                                    L: <strong>{{ $recommended->leechers }}</strong> |
                                    Times Completed: <strong>{{ $recommended->times_completed }}</strong>
                                </div>



                            </div>
                        </div></a>
                    </div>
                    @else

                    <div class="col">
                    <a href="{{ route('torrents.show', ['id' => $recommended->id, 'slug' => $recommended->slug]) }}"
                    class="text-decoration-none h5 mb-2 d-block" data-bs-toggle="tooltip" title="{{ $recommended->name }}">
                    <div class="card shadow-lg rounded-lg border-0 overflow-hidden h-100">

                            <img src="/images/noposter.jpg"
                                 alt="{{ $recommended->name }}"
                                 class="card-img-top img-fluid rounded-3">

                            <div class="card-body text-center">

                                <div class="text-muted small mb-2">
                                    S: <strong>{{ $recommended->seeders }}</strong> |
                                    L: <strong>{{ $recommended->leechers }}</strong> |
                                    C: <strong>{{ $recommended->times_completed }}</strong>
                                </div>



                            </div>
                        </div></a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif
