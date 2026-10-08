@extends('layouts.app')

@section('title', 'Browse Torrents')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/torrent-index.css') }}?v={{ filemtime(public_path('css/torrent-index.css')) }}">
<link rel="stylesheet" href="{{ asset('css/torrent-browser.css') }}?v={{ filemtime(public_path('css/torrent-browser.css')) }}">
<link rel="stylesheet" href="{{ asset('css/movie-of-the-day.css') }}?v={{ filemtime(public_path('css/movie-of-the-day.css')) }}">
@endpush

@push('scripts')
    @vite('resources/js/torrent-browser.js')
@endpush

@section('content')


@include('torrents.partials.movieoftheday')
<div data-torrent-filters>
    @include('torrents.partials.indexsearch')
</div>

<div data-torrent-browser data-browse-url="{{ route('torrents.index') }}">
    @include('torrents.partials.index-results')
</div>

@include('torrents.partials.css.list-common-css')





<script>
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
