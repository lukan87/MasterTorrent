@extends('layouts.app')

@section('title', 'Adult Torrents')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 mt-5">
    
</div>

{{-- 🔍 SEARCH --}}
@include('torrents.partials.adultsearch')

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm rounded-lg">
            <div class="card-body p-0">

              {{-- HEADER --}}
<div class="row g-0 border-bottom text-muted small text-uppercase align-items-center">
    <div class="col-12 col-md-5 px-3 py-3"></div>

    {{-- AGE --}}
    <div class="d-none d-md-block col-md-1 text-center py-3">
        <a data-bs-toggle="tooltip" title="Sort by age"
           href="{{ route('torrents.index', array_merge(request()->all(), [
               'sort' => 'created_at',
               'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
           ])) }}">
            <i class="bi bi-clock text-warning fs-5"></i>
        </a>
    </div>

    {{-- COMPLETED --}}
    <div class="d-none d-md-block col-md-1 text-center py-3">
        <a data-bs-toggle="tooltip" title="Sort by completed"
           href="{{ route('torrents.index', array_merge(request()->all(), [
               'sort' => 'times_completed',
               'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
           ])) }}">
            <i class="bi bi-floppy text-success fs-5"></i>
        </a>
    </div>

    {{-- SIZE --}}
    <div class="d-none d-md-block col-md-1 text-center py-3">
        <a data-bs-toggle="tooltip" title="Sort by size"
           href="{{ route('torrents.index', array_merge(request()->all(), [
               'sort' => 'size',
               'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
           ])) }}">
            <i class="bi bi-aspect-ratio text-info fs-5"></i>
        </a>
    </div>

    {{-- SEED / LEECH --}}
    <div class="col-6 col-md-1 text-center py-3">
        <span class="text-success">
            <a data-bs-toggle="tooltip" title="Sort by seeders"
               href="{{ route('torrents.index', array_merge(request()->all(), [
                   'sort' => 'seeders',
                   'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
               ])) }}">
                <i class="bi bi-arrow-up-circle text-success fs-5"></i>
            </a>
        </span>
        /
        <span class="text-danger">
            <a data-bs-toggle="tooltip" title="Sort by leechers"
               href="{{ route('torrents.index', array_merge(request()->all(), [
                   'sort' => 'leechers',
                   'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
               ])) }}">
                <i class="bi bi-arrow-down-circle text-danger fs-5"></i>
            </a>
        </span>
    </div>

    {{-- UPLOADER --}}
    <div class="col-6 col-md-1 text-center py-3" data-bs-toggle="tooltip" title="Uploader" ><i class="bi bi-person-up text-success fs-5"></i></div>

    {{-- ACTIONS --}}
    <div class="col-12 col-md-2 text-center px-3 py-3"></div>
</div>

              {{-- ROWS --}}
@forelse($adult as $torrent)
<div class="row g-0 align-items-center border-bottom py-3 {{ $torrent->sticky ? 'torrent-sticky' : '' }}">

    {{-- TORRENT INFO --}}
    <div class="col-12 col-md-5 d-flex px-3">
         <a href="{{ route('torrents.index', [
        'keyword' => '',
        'categories' => [$torrent->category->id],
        'genre' => '',
        'torrent_status' => 'active'
    ]) }}"
   class="me-3 d-inline-block"
   data-bs-toggle="tooltip"
   title="{{ $torrent->category->name }}">

    <img src="{{ asset($torrent->category->image) }}?v={{ filemtime(public_path($torrent->category->image)) }}"
     class="rounded shadow-sm"
     style="width:74px;height:40px"
     alt="{{ $torrent->category->name }}">
</a>

         <div class="overflow-hidden">
            <a href="{{ route('torrents.show', [$torrent->id, urlencode($torrent->slug)]) }}"
   class="d-block text-decoration-none"
   data-bs-toggle="tooltip"
   data-bs-html="true"
   data-bs-title="<img src='{{ $torrent->poster }}' class='img-fluid rounded' style='max-width:180px'>">

    <small class="torrent-title text-truncate d-block">
        {{ $torrent->name }}
    </small>

</a>

            <div class="d-md-none small text-muted mt-1 fs-6">
    {{ $torrent->created_at->format('M d, Y') }} ·
    {{ App\Helpers\FormatHelper::formatSize($torrent->size) }} ·
    {{ $torrent->times_completed }}
    {{ Str::plural('Time', $torrent->times_completed) }}
</div>


            <div class="mt-1 flex-wrap gap-2">
                {{-- @include('torrents.partials.tags') --}}
                @foreach($torrent->genres as $genre)
                    <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}"
                                       class="badge bg-secondary text-decoration-none">
                                        {{ $genre->name }}
                                    </a>
                @endforeach
                 @include('torrents.partials.tags')
            </div>

            <div class="mt-1 d-flex flex-wrap gap-1">
                 
            </div>
        </div>
    </div>

    {{-- AGE --}}
    <div class="d-none d-md-block col-md-1 text-center small text-muted">
        {{ $torrent->created_at->format('M d, Y') }}
    </div>

    {{-- COMPLETED --}}
    <div class="d-none d-md-block col-md-1 text-center small text-muted">
        {{ $torrent->times_completed }}
    </div>

    {{-- SIZE --}}
    <div class="d-none d-md-block col-md-1 text-center small text-muted">
        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
    </div>

    {{-- SEED / LEECH --}}
    <div class="col-6 col-md-1 text-center">
        <span class="text-success fw-bold">{{ $torrent->seeders }}</span>
        <span class="text-muted">/</span>
        <span class="text-danger fw-bold">{{ $torrent->leechers }}</span>
    </div>

  {{-- UPLOADER --}}
<div class="col-6 col-md-1 text-center small">
    @if($torrent->uploader)
        @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
         <i class="bi bi-arrow-90deg-up"></i>
            <a href="{{ route('profile.show', $torrent->uploader->id) }}"
               class="fw-semibold text-decoration-none"
               style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}">
                {{ $torrent->uploader->name }}
            </a>
        @else
           <span class="text-muted"><div style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}"> <i class="bi bi-arrow-90deg-up"></i> {{ $torrent->uploader->name }}</div></span>
        @endif
    @else
        <span class="text-muted">Unknown</span>
    @endif
</div>

    {{-- ACTIONS --}}
    <div class="col-12 col-md-2 px-3 mt-2 mt-md-0">
       

        <div class="d-flex justify-content-center gap-1 flex-wrap">

            {{-- DOWNLOAD / SEEDBOX --}}
            @if(Auth::check() && Auth::user()->hit_and_run_count <= 20)
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
                       class="btn btn-secondary">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </a>

                    @if($seedboxes->isNotEmpty())
                        <button class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown"></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach($seedboxes as $seedbox)
                                <li>
                                    <button type="button"
            class="dropdown-item seedbox-send-btn"
            data-torrent="{{ $torrent->id }}"
            data-seedbox="{{ $seedbox->id }}">
        <i class="bi bi-cloud-upload-fill me-2"></i>
        {{ $seedbox->name }}
    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{-- MODERATOR --}}
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <a href="{{ route('torrents.edit', [$torrent->id, $torrent->slug]) }}"
                   class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i>
                </a>
            @endif

            {{-- ADMIN --}}
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <form method="POST" action="{{ route('torrents.destroy', $torrent->slug) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif

        </div>
    </div>

</div>
@empty
<div class="text-center py-5 text-muted">No torrents found.</div>
@endforelse

            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $adult->links('pagination::bootstrap-5') }}
</div>

<style>


    .torrent-title {
    position: relative;
    display: inline-block;
    color: #fff;
    font-size: 1rem;
    letter-spacing: .3px;
    line-height: 1.15;
    padding-bottom: 2px; /* space for the line */
    transition: color .15s ease;
}

/* animated line INSIDE the element */
.torrent-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0; /* <-- inside, not outside */
    width: 100%;
    height: 1px;
    opacity: .9;
    background: linear-gradient(90deg, #aca9a9, #f0527f);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform .25s ease;
}

/* hover */
a:hover .torrent-title {
    color: #aca9a9;
}

a:hover .torrent-title::after {
    transform: scaleX(1);
    transform-origin: left;
}

/* Sticky torrent highlight */
.torrent-sticky {
    position: relative;
    background: linear-gradient(
        90deg,
        rgba(120, 140, 255, 0.08),
        transparent 35%
    );
}

/* Dark accent line on the left */
.torrent-sticky::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(
        180deg,
        #9aa4ff,
        #d41a6b
    );
    border-radius: 0 4px 4px 0;
}

.torrent-sticky {
    background: rgba(134, 151, 252, 0.1);
}

</style>

<script>
document.querySelectorAll('.seedbox-send-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        fetch("{{ route('torrents.sendToSeedbox', '__ID__') }}".replace('__ID__', this.dataset.torrent), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                seedbox_id: this.dataset.seedbox
            })
        })
        .then(r => r.json())
        .then(data => {
            showToast(data.message || 'Sent to seedbox');
        })
        .catch(() => showToast('Seedbox error', 'danger'));
    });
});

 function swalSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 3500,
        showConfirmButton: true,
        timerProgressBar: true
    });
}

function swalError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
    });
}



document.addEventListener('click', function (e) {
    const btn = e.target.closest('.seedbox-send-btn');
    if (!btn) return;

    e.preventDefault();

    // Prevent double-click
    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';
    btn.classList.add('disabled');

    // Optional loading alert
    Swal.fire({
        title: 'Sending torrent…',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch("{{ route('torrents.sendToSeedbox', '__ID__') }}".replace('__ID__', btn.dataset.torrent), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            seedbox_id: btn.dataset.seedbox
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    })
    .then(data => {
        Swal.close();
        swalSuccess(data.message || 'Torrent sent to seedbox');

        // Optional: mark as sent
        btn.innerHTML = '✔ Sent';
        btn.classList.add('text-success');
    })
    .catch(error => {
        Swal.close();
        swalError(error.message || 'Failed to send torrent');

        btn.dataset.loading = '0';
        btn.classList.remove('disabled');
    });
});


</script>

@endsection
