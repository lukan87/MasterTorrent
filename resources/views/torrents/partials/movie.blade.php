@include('torrents.partials.css.media-css')
@include('torrents.partials.backdrop-slideshow')
@include('torrents.partials.media-header', [
    'torrent' => $torrent,
    'display' => $display
])
