<rss version="2.0">
    <channel>

        <title>FileIplay RSS Feed</title>

        <link>{{ url('/') }}</link>

        <description>A feed of the most recent torrents from FileIplay</description>

        <language>en-gb</language>

        @foreach ($torrents as $torrent)
            <item>

    <title>{{ htmlspecialchars($torrent->name, ENT_XML1) }}</title>

    <link>{{ route('rss.download', [
        'fileName' => $torrent->file_name,
        'passkey' => $passkey
    ]) }}</link>

    <!-- <description>{{ htmlspecialchars($torrent->rss_description ?? '', ENT_XML1) }}</description> -->

    @if (!empty($torrent->rss_image))
        <rssImage>{{ htmlspecialchars($torrent->rss_image, ENT_XML1) }}</rssImage>
    @endif

    <pubDate>{{ $torrent->created_at->toRssString() }}</pubDate>

    <category>{{ htmlspecialchars($torrent->category->name ?? 'Other', ENT_XML1) }}</category>

    <guid isPermaLink="true">
        {{ url('/torrents/' . $torrent->id . '/' . $torrent->slug) }}
    </guid>

</item>
        @endforeach

    </channel>
</rss>