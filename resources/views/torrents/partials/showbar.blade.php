<div class="card card-blur mb-3 mt-3">
    <div class="card-header">
        <h6 class="card-title">{{ $torrent->name }}</h6>
    </div>
    <div class="card-body d-flex justify-content-between align-items-center">

<!-- Left: Download and Edit Links -->
<div class="d-flex align-items-center gap-1">
    <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-primary btn-sm mr-2">
        Download
    </a>
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::id() === $torrent->owner))
    <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-secondary btn-sm mr-2">
        Edit
    </a>
@endif


    @if(!$hasThanked)
    <form action="{{ route('torrents.thank', $torrent->id) }}" method="POST" class="mr-2">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Thank you!">
       {{ $torrent->thanksCount() }}  <i class="bi bi-hand-thumbs-up-fill"></i>
        </button>
    </form>
    @else
    <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Thanked by: {{ implode(', ', $thankUserNames) }}">
    {{ $torrent->thanksCount() }} <i class="bi bi-hand-thumbs-up-fill"></i>
    </button>
    @endif
</div>



<!-- Right: Seeders, Leechers, and Times Completed -->
<div class="d-flex ms-auto">


<p class="mb-0 mx-2">
        <strong><i class="bi bi-tags" data-bs-toggle="tooltip" title="Category"></i></strong>
        {{ $torrent->category->name }}
    </p>

@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
<a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?seeders">
    <p class="mb-0 mx-2">
        <strong><i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i></strong>
        {{ $torrent->seeders }}
    </p>
</a>
@else
    <p class="mb-0 mx-2">
        <strong><i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i></strong>
        {{ $torrent->seeders }}
    </p>
@endif

@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
<a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?leechers">
    <p class="mb-0 mx-2"> <strong><i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i></strong>
        {{ $torrent->leechers }}
    </p>
</a>
@else
<p class="mb-0 mx-2">
    <strong><i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i></strong>
     {{ $torrent->leechers }}</p>
@endif




    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
    <p class="mb-0 mx-2">
    <strong>
        <i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i>
    </strong>
    <a href="{{ route('torrent.history', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-decoration-none">
        {{ $torrent->times_completed }}
    </a>
</p>

    @else
    <p class="mb-0 mx-2"><strong><i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i></strong> {{ $torrent->times_completed }}</p>
    @endif
    <p class="mb-0 mx-2"><strong><i class="bi bi-pie-chart-fill" data-bs-toggle="tooltip" title="Size"></i></strong> {{ \App\Helpers\FormatHelper::formatSize($torrent->size) }}</p>
</div>

</div>


</div>
