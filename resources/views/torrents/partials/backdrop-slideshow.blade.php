@once
@if(count($display['backdrops'] ?? []) > 1)
<div data-torrent-backdrops data-images="{{ json_encode($display['backdrops']) }}" data-initial="{{ $fanartBackground ?? ($torrent->background ?: ($display['backdrop'] ?? '')) }}" hidden></div>
@unless(request()->routeIs('torrents.show'))
@push('scripts')
<script src="{{ asset('js/torrent-backdrops.js') }}?v={{ filemtime(public_path('js/torrent-backdrops.js')) }}" defer></script>
@endpush
@endunless
@endif
@endonce
