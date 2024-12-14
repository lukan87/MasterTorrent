<table class="content-table">
    <thead>
        <tr>
            <th>Poster</th>
            <th>Name</th>
            <th>Seeders</th>
            <th>Leechers</th>
            <th>Completed</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($recommendedTorrents as $torrent)
            <tr>
                <td class="text-center">

                        <img src="{{ $torrent->poster ?? asset('images/default-poster.jpg') }}" alt="{{ $torrent->name }}" class="poster-image">

                </td>
                <td><a href="{{ route('torrents.show', $torrent->id) }}">{{ $torrent->name }}</a></td>
                <td class="text-center">{{ $torrent->seeders }}</td>
                <td class="text-center">{{ $torrent->leechers }}</td>
                <td class="text-center">{{ $torrent->times_completed }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
