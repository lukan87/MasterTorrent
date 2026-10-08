<rss version="2.0" xmlns:torznab="http://torznab.com/schemas/2015/feed" xmlns:newznab="http://www.newznab.com/DTD/2010/feeds/attributes/" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>FileIplay</title>
        <description>FileIplay torrent releases</description>
        <link>{{ url('/') }}</link>
        <newznab:response offset="{{ $offset }}" total="{{ $total }}" />
        @foreach($items as $torrent)
            @php
                $downloadUrl = route('torznab.api', ['t' => 'get', 'id' => $torrent->id, 'apikey' => $apiKey]);
                $detailsUrl = route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]);
            @endphp
            <item>
                <title>{{ $torrent->name }}</title>
                <guid isPermaLink="false">fileiplay-{{ $torrent->id }}</guid>
                <link>{{ $downloadUrl }}</link>
                <comments>{{ $detailsUrl }}</comments>
                <description>{{ $torrent->name }}</description>
                <pubDate>{{ $torrent->created_at->toRssString() }}</pubDate>
                <size>{{ $torrent->size }}</size>
                <enclosure url="{{ $downloadUrl }}" length="{{ $torrent->size }}" type="application/x-bittorrent" />
                <torznab:attr name="category" value="{{ $map[$torrent->category_id] }}" />
                <torznab:attr name="category" value="{{ 100000 + $torrent->category_id }}" />
                <torznab:attr name="size" value="{{ $torrent->size }}" />
                <torznab:attr name="seeders" value="{{ $torrent->seeders }}" />
                <torznab:attr name="peers" value="{{ $torrent->seeders + $torrent->leechers }}" />
                <torznab:attr name="grabs" value="{{ $torrent->times_completed }}" />
                <torznab:attr name="downloadvolumefactor" value="{{ $torrent->free ? 0 : 1 }}" />
                <torznab:attr name="uploadvolumefactor" value="{{ $torrent->double ? 2 : 1 }}" />
                @if($torrent->imdbid)
                    <torznab:attr name="imdb" value="{{ preg_replace('/^tt/', '', $torrent->imdbid) }}" />
                @endif
                @if($torrent->tmdbid)
                    <torznab:attr name="tmdbid" value="{{ $torrent->tmdbid }}" />
                @endif
            </item>
        @endforeach
    </channel>
</rss>
