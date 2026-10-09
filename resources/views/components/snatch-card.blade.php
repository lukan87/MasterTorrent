@php
    $isPeer = $history instanceof \App\Models\Peer;
    $record = $isPeer ? ($history->relationLoaded('snatchHistory') ? $history->getRelation('snatchHistory') : null) : $history;
    $torrent = $history->torrent;
    $isOwner = $torrent && auth()->check() && (int) $torrent->owner === (int) auth()->id();
    $ownsHistory = auth()->check() && (int) auth()->id() === (int) $history->user_id;
    $uploaded = (int) ($isPeer && $type === 'leeching' ? ($record->uploaded ?? $history->uploaded) : $history->uploaded);
    $downloaded = (int) ($isPeer && $type === 'leeching' ? ($record->downloaded ?? $history->downloaded) : $history->downloaded);
    $actualDownloaded = (int) ($isPeer && $type === 'leeching' ? ($record->actual_downloaded ?? $history->downloaded) : ($history->actual_downloaded ?? $downloaded));
    $actualUploaded = (int) ($isPeer && $type === 'leeching' ? ($record->actual_uploaded ?? $history->uploaded) : ($history->actual_uploaded ?? $uploaded));
    $seedtime = (int) ($isPeer ? ($history->total_seedtime ?? $record->seedtime ?? 0) : $history->seedtime);
    $seedTarget = max(1, (int) $requiredSeed);
    $remainingSeed = max(0, $seedTarget - $seedtime);
    $seedProgress = max(0, min(100, $seedtime / $seedTarget * 100));
    $ratio = $actualDownloaded > 0 ? $uploaded / $actualDownloaded : ($uploaded > 0 ? INF : 0);
    $ratioDisplay = is_infinite($ratio) ? '∞' : number_format($ratio, 2);
    $size = (int) ($torrent->size ?? 0);
    $downloadProgress = $size > 0 ? max(0, min(100, $actualDownloaded / $size * 100)) : 0;
    $completedAt = $record->completed_at ?? null;
    $complete = $completedAt || ($size > 0 && $actualDownloaded >= $size);
    $active = (bool) $history->active;
    $seeding = $active && (bool) $history->seeder;
    $hitrun = (bool) ($record->hitrun ?? false);
    $requirementsMet = $ratio >= 1 || $remainingSeed === 0;
    $canDownload = $ownsHistory && $torrent && !$requirementsMet && !$isOwner;
    $canBuySeedtime = $ownsHistory && $torrent && $record && !$isOwner && !$requirementsMet
        && ($actualDownloaded > 0 || $downloaded > 0) && !in_array($type, ['hnr', 'fixer']);
    $canClearHitrun = $ownsHistory && $torrent && $record && !$isOwner && $hitrun;
    $hasDownloaded = $actualDownloaded > 0 || $downloaded > 0;
    $status = $hitrun ? 'Hit & Run' : ($seeding ? 'Seeding' : ($active ? 'Downloading' : 'Offline'));
    $tone = $hitrun ? 'danger' : ($seeding ? 'success' : ($active ? 'info' : 'muted'));
    $addedAt = $record->created_at ?? $history->created_at;
    $deadline = $completedAt && !$requirementsMet && !$hitrun ? $completedAt->copy()->addDays(config('hitrun.enforce_days', 7)) : null;
@endphp
<article class="snatch-entry snatch-entry-{{ $tone }}">
    <div class="snatch-entry-top">
        <div class="snatch-torrent">
            <div class="snatch-file-icon"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i></div>
            <div>
                <div class="snatch-entry-label">{{ $isPeer ? 'Active session' : 'Torrent history' }} @if($torrent) · {{ \App\Helpers\FormatHelper::formatSize($size) }} @endif</div>
                <h2>@if($torrent)<a href="{{ route('torrents.show', ['id' => $torrent->id]) }}">{{ $torrent->name }}</a>@else Torrent unavailable @endif</h2>
                <div class="snatch-dates">
                    @if($addedAt)<span>{{ $record ? 'Snatched' : 'Session started' }} <time datetime="{{ $addedAt->toIso8601String() }}">{{ $addedAt->format('Y-m-d H:i') }}</time></span>@endif
                    @if($completedAt && !$isOwner)<span>Completed <time datetime="{{ $completedAt->toIso8601String() }}">{{ $completedAt->format('Y-m-d H:i') }}</time></span>@endif
                </div>
            </div>
        </div>
        <div class="snatch-entry-controls">
            @if($isOwner)<span class="snatch-owner"><i class="bi bi-person-check" aria-hidden="true"></i> Owner</span>@endif
        <span class="snatch-status snatch-status-{{ $tone }}"><i class="bi {{ $hitrun ? 'bi-exclamation-triangle' : ($seeding ? 'bi-cloud-upload' : ($active ? 'bi-arrow-down-circle' : 'bi-pause-circle')) }}" aria-hidden="true"></i> {{ $status }}</span>
            @if($record) @include('snatch.partials.delete-history', ['history' => $record, 'torrent' => $torrent]) @endif
        </div>
    </div>

    <dl class="snatch-metrics">
        <div><dt><i class="bi bi-arrow-up" aria-hidden="true"></i> Uploaded</dt><dd>{{ \App\Helpers\FormatHelper::formatSize($uploaded) }}</dd><small>Actual: {{ \App\Helpers\FormatHelper::formatSize($actualUploaded) }}</small></div>
        <div><dt><i class="bi bi-arrow-down" aria-hidden="true"></i> Downloaded</dt><dd>{{ \App\Helpers\FormatHelper::formatSize($downloaded) }}</dd><small>Actual: {{ \App\Helpers\FormatHelper::formatSize($actualDownloaded) }}</small></div>
        <div><dt><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Ratio</dt><dd>{{ $ratioDisplay }}</dd><small>{{ $ratio >= 1 ? 'Ratio target reached' : 'Target: 1.00' }}</small></div>
        <div><dt><i class="bi bi-clock" aria-hidden="true"></i> Seed time</dt><dd>{{ \App\Helpers\FormatHelper::formatTime($seedtime) }}</dd><small>{{ $remainingSeed ? \App\Helpers\FormatHelper::formatTime($remainingSeed).' remaining' : 'Seed target reached' }}</small></div>
    </dl>

    <div class="snatch-progress-grid">
        <div>
            <div class="snatch-progress-label"><span>Seeding requirement</span><strong>{{ number_format($seedProgress) }}%</strong></div>
            <div class="snatch-progress" role="progressbar" aria-label="Seeding requirement" aria-valuenow="{{ round($seedProgress) }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $seedProgress }}%"></span></div>
        </div>
        <div>
            <div class="snatch-progress-label"><span>Download progress</span><strong>{{ $complete ? 'Completed' : ($size > 0 ? number_format($downloadProgress).'% transferred' : 'Size unknown') }}</strong></div>
            <div class="snatch-progress snatch-progress-download" role="progressbar" aria-label="Download progress" aria-valuenow="{{ $complete ? 100 : round($downloadProgress) }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $complete ? 100 : $downloadProgress }}%"></span></div>
        </div>
    </div>

    <div class="snatch-guidance {{ $hitrun || (!$requirementsMet && $hasDownloaded) ? 'snatch-guidance-warning' : '' }}">
        <i class="bi {{ $requirementsMet && !$hitrun ? 'bi-check-circle' : 'bi-info-circle' }}" aria-hidden="true"></i>
        <span>@if($isOwner)You uploaded this torrent. Continuing to seed helps other members.
        @elseif($hitrun)This torrent has an H&R flag. Resume seeding to resolve it or clear it using bonus points.
        @elseif($requirementsMet)Seeding requirements met. Continuing to seed helps other members.
        @elseif($hasDownloaded){{ $complete ? 'Keep seeding' : 'Complete the download and seed' }} to meet the seed time or 1.00 ratio target.
        @else No downloaded data recorded for this torrent.
        @endif
        @if($deadline && !$isOwner) <span class="snatch-deadline">H&R review: {{ $deadline->format('Y-m-d H:i') }}</span>@endif</span>
    </div>

    @include('snatch.partials.announce', ['history' => $record])

    @if($canDownload || $canBuySeedtime || $canClearHitrun)
    <div class="snatch-actions">
        @if($canDownload)
            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download" aria-hidden="true"></i> {{ $seeding ? 'Download torrent' : 'Resume in client' }}</a>
        @endif
            @if($canBuySeedtime)
                <form action="{{ route('bonus.buySeedtime') }}" method="POST">
                    @csrf
                    <input type="hidden" name="torrent_id" value="{{ $history->torrent_id }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-coin" aria-hidden="true"></i> Buy seedtime <span>· {{ number_format(config('seedbonus.shop.seedtime', 1000)) }} points</span></button>
                </form>
            @elseif($canClearHitrun)
                <form action="{{ route('bonus.removeHNR') }}" method="POST">
                    @csrf
                    <input type="hidden" name="torrent_id" value="{{ $history->torrent_id }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-coin" aria-hidden="true"></i> Clear H&R · {{ number_format(config('seedbonus.shop.remove_hnr', 5000)) }} points</button>
                </form>
            @endif
    </div>
    @endif
</article>
