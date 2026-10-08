<caps>
    <server version="1.0" title="FileIplay" url="{{ url('/') }}" />
    <limits max="100" default="50" />
    <registration available="no" open="no" />
    <searching>
        <search available="yes" supportedParams="q" />
        <movie-search available="yes" supportedParams="q,imdbid,tmdbid,year" />
        <tv-search available="yes" supportedParams="q,imdbid,tmdbid,tvdbid,season,ep" />
        <audio-search available="no" supportedParams="q" />
        <book-search available="no" supportedParams="q" />
    </searching>
    <categories>
        @foreach($categories as $id => $name)
            <category id="{{ $id }}" name="{{ $name }}" />
        @endforeach
    </categories>
</caps>
