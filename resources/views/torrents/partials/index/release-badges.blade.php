@php
    $releaseDetails = \App\Helpers\TorrentReleaseParser::parse($torrent->name);
    $releaseDetails['video'] = \App\Helpers\TorrentReleaseParser::videoCodec($torrent->name);
@endphp
@foreach (['resolution' => 'Resolution', 'video' => 'Video codec', 'audio' => 'Audio codec', 'source' => 'Source'] as $key => $label)
    @if($releaseDetails[$key] !== null)
        <span class="tx-release-badge tx-release-{{ $key }}" title="{{ $label }}: {{ $releaseDetails[$key] }} (from release name)" aria-label="{{ $label }}: {{ $releaseDetails[$key] }}">{{ $releaseDetails[$key] }}</span>
    @endif
@endforeach
