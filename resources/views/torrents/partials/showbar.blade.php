<div class="card card-blur mb-3 mt-3">
    <div class="card-header">
        <h6 class="card-title">{{ $torrent->name }}</h6>
    </div>
    <div class="card-body d-flex justify-content-between align-items-center">

<!-- Left: Download and Edit Links -->
<div class="d-flex align-items-center gap-1">
    <style>
        /* Gradient Button Styles */
.btn-gradient-primary {
    background: linear-gradient(to right, #007bff, #0056b3);
    border-color: #0056b3;
}

.btn-gradient-info {
    background: linear-gradient(to right, #17a2b8, #138496);
    border-color: #138496;
}

.btn-gradient-primary:hover, .btn-gradient-info:hover {
    background: linear-gradient(to right, #0056b3, #003366);
}
</style>


    @if (Auth::user()->hit_and_run_count > '10' ) 
    <div class="alert alert-danger" role="alert">
        Download restricted, you have more than 10 Hit&Run's.
    </div>
    
    @else
    @if (Auth::check() && Auth::user()->slots >= 1)
    <div class="btn-group">
        <!-- Download Button -->
        <button type="button" class="btn btn-gradient-primary btn-sm d-flex align-items-center" aria-current="page" data-bs-toggle="tooltip" title="Download Torrent">
            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-white text-decoration-none">
                <i class="bi bi-file-earmark-arrow-down-fill d-sm-none me-2"></i> <!-- Hide on small screens and larger -->
                <span class="d-none d-sm-inline"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Download</span> <!-- Hide on extra small screens (mobile) -->
            </a>
        </button>

        <!-- Dropdown Button -->
        <button type="button" class="btn btn-gradient-primary btn-sm dropdown-toggle dropdown-toggle-split text-white" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
        </button>

        <!-- Dropdown Menu -->
        <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
            <li><a class="dropdown-item" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?free=1" data-bs-toggle="tooltip" title="No Download Recoreded For This Torrent">
                <i class="bi bi-0-circle-fill me-2"></i> Free
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?double=1" data-bs-toggle="tooltip" title="Double Upload Recoreded For This Torrent">
                <i class="bi bi-chevron-double-down me-2"></i> Double
            </a></li>
        </ul>
    </div>
@else
    <button type="button" class="btn btn-gradient-info btn-sm d-flex align-items-center" aria-current="page" data-bs-toggle="tooltip" title="Download Torrent">
        <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-white text-decoration-none">
            <i class="bi bi-file-earmark-arrow-down-fill d-sm-none me-2"></i> <!-- Hide on small screens and larger -->
            <span class="d-none d-sm-inline"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Download</span> <!-- Hide on extra small screens (mobile) -->
        </a>
    </button>
@endif
@endif




    
@if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::id() === $torrent->owner))
<a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-secondary btn-sm mr-2" data-bs-toggle="tooltip" title="Edit Torrent">
    <i class="fas fa-edit d-inline d-sm-none"></i>  <!-- Show only the icon on small screens -->
    <span class="d-none d-sm-inline">Edit</span>  <!-- Show the text on medium+ screens -->
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


    <p class="mb-0 mx-2 d-none d-sm-block">
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
