@if($similarTorrents->isEmpty())
@else
<div class="card card-blur mt-3">
    <div class="card-header">
        <h5>Similar Torrents</h5>
    </div>
    <div class="card-body">

            <ul class="list-group">
                @foreach($similarTorrents as $similar)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('torrents.show', ['id' => $similar->id, 'slug' => $similar->slug]) }}">
                                {{ $similar->name }}
                            </a>
                            <div class="text-muted small">
                                Seeders: <strong>{{ $similar->seeders }}</strong> /
                                Leechers: <strong>{{ $similar->leechers }}</strong> /
                                Times Completed: <strong>{{ $similar->times_completed }}</strong>
                            </div>
                        </div>
                        <span>{{ \App\Helpers\FormatHelper::formatSize($similar->size) }}</span>
                    </li>
                @endforeach
            </ul>

    </div>
</div>
@endif
