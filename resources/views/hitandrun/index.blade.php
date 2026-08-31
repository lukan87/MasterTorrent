@extends('layouts.app')

@section('content')
    <h1>{{ isset($viewedUser) ? $viewedUser->name . "'s" : "Your" }} Torrents That Need Seeding</h1>

    @if($torrents->isEmpty())
        <p>{{ isset($viewedUser) ? $viewedUser->name : 'You' }} don't have any torrents that need seeding.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Torrent Name</th>
                    <th>Seeding Duration (hours)</th>
                    <th>Ratio</th>
                    <th>Created At</th>
                    <th>Completed At</th>
                    <th>Updated at</th>
                    <th>Prewarned</th>
                    <th>Remaining Seeding Time</th>
                    <th>Seeding Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($torrents as $torrent)
                    <tr>
                        <td><a href="{{ route('torrents.show', $torrent->torrent_id) }}">{{ $torrent->torrent->name }}</a><br>
                        Seeders: {{ $torrent->torrent->seeders }} / Leechers: {{ $torrent->torrent->leechers }}
                    </td>
                        <td>{{ \App\Helpers\FormatHelper::formatTime($torrent->seedtime) }}</td>
                        <td>
    @php
        $uploaded = $torrent->uploaded ?? 0; // Ensure no null values
        $downloaded = $torrent->downloaded ?? 0;

        if ($downloaded > 0) {
            $ratio = number_format($uploaded / $downloaded, 2); // Calculate and format the ratio
        } elseif ($uploaded > 0) {
            $ratio = '∞'; // Infinite ratio for non-downloaded torrents
        } else {
            $ratio = 'N/A'; // Not available when both are 0
        }
    @endphp

    {{ $ratio }}
</td>
                        <td>{{ $torrent->created_at->toDayDateTimeString() }}</td>
                        <td>{{ $torrent->completed_at ? $torrent->completed_at->toDayDateTimeString() : 'N/A' }}</td>
                        <!-- <td>{{ App\Helpers\FormatHelper::formatSize($torrent->left) }}</td> -->
                         <td>{{ $torrent->updated_at }}</td>
                        <td>
                            <!-- {{ $torrent->prewarn ? 'Yes' : 'No' }} -->
                            @if ($torrent->prewarned_at)
                                ({{ $torrent->prewarned_at->diffForHumans() }})
                            @endif
                        </td>
                        <td>
                            @php
                                $remainingTime = config('hitrun.seedtime') - $torrent->seedtime;
                            @endphp

                            @if ($remainingTime > 0)
                                {{ intdiv($remainingTime, 3600) }}h {{ intdiv($remainingTime % 3600, 60) }}m
                            @else
                                Fully seeded.
                            @endif
                        </td>
                        <td>
                            @if ($torrent->active == 1)
                                <span class="text-success">Yes</span> <!-- Green for active -->
                            @else
                                <span class="text-danger">No</span> <!-- Red for inactive -->
                            @endif
                        </td>
                        <td>
                        @if (auth()->id() === $torrent->user_id)
    <form action="{{ route('bonus.buySeedtime') }}" method="POST">
        @csrf
        <input type="hidden" name="torrent_id" value="{{ $torrent->torrent_id }}" hidden>
        <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="5000 seedbonus points">Buy Seedtime</button>
    </form>
@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $torrents->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
