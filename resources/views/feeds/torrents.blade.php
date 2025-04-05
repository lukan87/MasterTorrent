{{-- resources/views/feeds/torrents.blade.php --}}
{{ '<?xml version="1.0" encoding="UTF-8"?>' }}
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ $feed->title }}</title>
    <link href="{{ $feed->url }}" />
    <updated>{{ now()->toAtomString() }}</updated>
    <id>{{ $feed->url }}</id>

    @foreach($feed->items as $item)
        <entry>
            <title>{{ $item->title }}</title>
            <link href="{{ $item->link }}" />
            <updated>{{ $item->updated->toAtomString() }}</updated>
            <id>{{ $item->id }}</id>
            <summary>{{ $item->summary }}</summary>
            <author>
                <name>{{ $item->authorName }}</name>
            </author>
        </entry>
    @endforeach
</feed>
