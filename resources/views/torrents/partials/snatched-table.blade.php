@php($completedOnly = $completedOnly ?? false)
<div class="snatch-table-panel">
<div class="snatch-table-toolbar">
<p class="snatch-summary" tabindex="-1" data-snatch-summary>
    Showing {{ $histories->firstItem() ?? 0 }}–{{ $histories->lastItem() ?? 0 }} of {{ number_format($histories->total()) }} {{ $completedOnly ? 'completed records' : 'history records' }}
</p>
<span class="snatch-page-size">30 per page</span>
</div>
<div class="table-responsive" tabindex="0" role="region" aria-label="{{ $completedOnly ? 'Completed downloads table' : 'Snatch history table' }}">
    <table class="table snatch-table">
        <caption class="visually-hidden">Download and seeding history for {{ $torrent->name }}</caption>
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Status</th>
                <th scope="col"><i class="bi bi-arrow-up" aria-hidden="true"></i> Uploaded</th>
                <th scope="col"><i class="bi bi-arrow-down" aria-hidden="true"></i> Downloaded</th>
                <th scope="col" title="Credited upload divided by actual download">Ratio</th>
                <th scope="col">Seed time</th>
                <th scope="col">Started</th>
                <th scope="col">Completed</th>
                <th scope="col">Last event</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $history)
                <tr>
                    <td class="snatch-member">
                        <span class="snatch-avatar" aria-hidden="true">{{ mb_substr($history->user?->name ?? '?', 0, 1) }}</span>
                        <div>
                        @if($history->user)
                            <a href="{{ route('profile.show', ['id' => $history->user->id, 'name' => $history->user->name]) }}">{{ $history->user->name }}</a>
                        @else
                            <span class="text-muted">Deleted user</span>
                        @endif
                        @if((int) $history->user_id === (int) $torrent->owner)
                            <span class="snatch-owner">Uploader</span>
                        @endif
                        </div>
                    </td>
                    <td>
                        @if($history->active && $history->seeder)
                            <span class="snatch-state is-seeding">Seeding</span>
                        @elseif($history->active)
                            <span class="snatch-state is-downloading">Downloading</span>
                        @else
                            <span class="snatch-state is-inactive">Inactive</span>
                        @endif
                        @if($history->hitrun)
                            <span class="snatch-state is-warning">Hit &amp; Run</span>
                        @endif
                    </td>
                    <td class="snatch-transfer upload"><strong>{{ \App\Helpers\FormatHelper::formatSize($history->uploaded ?? 0) }}</strong><small>{{ \App\Helpers\FormatHelper::formatSize($history->actual_uploaded ?? 0) }} actual</small></td>
                    <td class="snatch-transfer download"><strong>{{ \App\Helpers\FormatHelper::formatSize($history->downloaded ?? 0) }}</strong><small>{{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded ?? 0) }} actual</small></td>
                    <td class="snatch-ratio">{{ $history->actual_downloaded > 0 ? number_format($history->uploaded / $history->actual_downloaded, 2) : ($history->uploaded > 0 ? '∞' : '—') }}</td>
                    <td class="snatch-seedtime">{{ \App\Helpers\FormatHelper::formatTime($history->seedtime ?? 0) }}</td>
                    <td class="snatch-date">{{ $history->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="snatch-date">{{ $history->completed_at?->format('Y-m-d H:i') ?? 'Not completed' }}</td>
                    <td class="snatch-date">{{ $history->last_event_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="snatch-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>{{ $completedOnly ? 'No completed downloads for this torrent yet.' : 'No snatch history for this torrent yet.' }}</strong><span>{{ $completedOnly ? 'Members will appear here once their download is confirmed complete.' : 'Download and seeding activity will appear here.' }}</span></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="snatch-table-footer">
    <span><i class="bi bi-clock-history" aria-hidden="true"></i> Page {{ $histories->currentPage() }} of {{ $histories->lastPage() }}</span>
    {{ $histories->links('pagination::bootstrap-5') }}
</div>
</div>
