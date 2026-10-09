@php
    // An unsaved model supplies the same interface without requiring an uploaded torrent.
    $heroTorrent = $torrents->first();
    if (! $heroTorrent) {
        $heroTorrent = new \App\Models\Torrent;
        $heroTorrent->tmdbid = $tmdbid;
        $heroTorrent->imdbid = $display['external_ids']['imdb_id'] ?? $movie['imdb_id'] ?? null;
        $heroTorrent->poster = $display['poster'] ?? null;
        $heroTorrent->background = $display['backdrop'] ?? null;
        $heroTorrent->setRelation('genres', collect());
    }
@endphp

@include('torrents.partials.media-header', [
    'torrent' => $heroTorrent,
    'display' => $display,
])
