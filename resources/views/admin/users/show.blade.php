@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>History for: {{ $user->name }} with ID: {{ $user->id }}</h2>
        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            @if ($torrentsUploaded->isNotEmpty())
                <tr>
                    <th>Torrents Uploaded</th>
                    <td>
                        @foreach($torrentsUploaded as $torrent)
                            <p>{{ $torrent->name }} (ID: {{ $torrent->id }})</p>
                        @endforeach
                        <div class="mt-2">
                            {{ $torrentsUploaded->links('pagination::bootstrap-5') }}
                        </div>
                    </td>
                </tr>
            @endif
            @if ($torrentsDownloaded->isNotEmpty())
                <tr>
                    <th>Torrents Downloaded</th>
                    <td>
                        @foreach($torrentsDownloaded as $torrent)
                            <p>{{ $torrent->torrent->name }} (ID: {{ $torrent->id }})</p>
                        @endforeach
                        <div class="mt-2">
                            {{ $torrentsDownloaded->links('pagination::bootstrap-5') }}
                        </div>
                    </td>
                </tr>
            @endif
            @if ($seedingTorrents->isNotEmpty())
                <tr>
                    <th>Torrents Seeding</th>
                    <td>
                        @foreach($seedingTorrents as $torrentPeer)
                            <p>{{ $torrentPeer->torrent->name }} (ID: {{ $torrentPeer->torrent->id }})</p>
                        @endforeach
                        <div class="mt-2">
                            {{ $seedingTorrents->links('pagination::bootstrap-5') }}
                        </div>
                    </td>
                </tr>
            @endif
            @if ($leechingTorrents->isNotEmpty())
                <tr>
                    <th>Torrents Leeching</th>
                    <td>
                        @foreach($leechingTorrents as $torrentPeer)
                            <p>{{ $torrentPeer->torrent->name }} (ID: {{ $torrentPeer->torrent->id }})</p>
                        @endforeach
                        <div class="mt-2">
                            {{ $leechingTorrents->links('pagination::bootstrap-5') }}
                        </div>
                    </td>
                </tr>
            @endif
            @if ($messages->isNotEmpty())
                <tr>
                    <th>Messages Sent</th>
                    <td>
                        @foreach($messages as $message)
                            <p>To: {{ $message->receiver->name }} - {{ $message->body }}</p>
                        @endforeach
                        <div class="mt-2">
                            {{ $messages->links('pagination::bootstrap-5') }}
                        </div>
                    </td>
                </tr>
            @endif
        </table>
    </div>
@endsection
