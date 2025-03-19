<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>

<rss version="2.0">
    <channel>
        <title>|MySite-RSS-Feed|</title>
        <link>{{ url('/') }}</link>
        <description>A feed of the most recent torrents</description>
        @foreach ($torrents as $torrent)
            <item>
                <title>{{ htmlspecialchars($torrent->name, ENT_XML1) }}</title>
                <link>{{ route('rss.download', ['fileName' => $torrent->file_name, 'passkey' => $passkey]) }}</link>
                <description>{{ htmlspecialchars($torrent->description, ENT_XML1) }}</description>
                <pubDate>{{ $torrent->created_at->toRssString() }}</pubDate>
                <category>{{ htmlspecialchars($torrent->category->name, ENT_XML1) }}</category>
                <guid>{{ url('/torrents' .'/' . $torrent->id . '/' . $torrent->slug) }}</guid>
            </item>
        @endforeach
    </channel>
</rss>
