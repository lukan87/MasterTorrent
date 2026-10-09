@php
    $boards = [
        'seeders' => ['label' => 'Seeders', 'users' => collect($topSeeders24h)->take(7), 'metric' => 'seed_count',
            'rank' => $userSeederRank ?? null, 'streak' => $seederStreak ?? null, 'percentile' => $seederPercentile ?? null,
            'movement' => null, 'movementClass' => null, 'empty' => 'No active seeders.'],
        'uploaders' => ['label' => 'Uploaders', 'users' => collect($topUploaders24h)->take(7), 'metric' => 'uploaded_24h',
            'rank' => $userUploadRank24h ?? null, 'streak' => $uploadStreak ?? null, 'percentile' => $uploadPercentile ?? null,
            'movement' => $uploadMovement ?? null, 'movementClass' => $uploadMovementClass ?? null, 'empty' => 'No uploads recorded.'],
        'downloaders' => ['label' => 'Downloaders', 'users' => collect($topDownloaders24h)->take(7), 'metric' => 'downloaded_24h',
            'rank' => $userDownloadRank24h ?? null, 'streak' => $downloadStreak ?? null, 'percentile' => $downloadPercentile ?? null,
            'movement' => $downloadMovement ?? null, 'movementClass' => $downloadMovementClass ?? null, 'empty' => 'No downloads recorded.'],
    ];
@endphp
<section class="torrent-featured-card torrent-featured-card--community" id="lbAccordion" aria-label="Community leaderboards">
    <header class="torrent-featured-header">
        <span class="torrent-featured-icon"><i class="bi bi-trophy-fill" aria-hidden="true"></i></span>
        <div class="torrent-featured-heading">
            <h2>Community leaderboards</h2>
            <p>Last 24 hours · Top 7 members</p>
        </div>
        <div class="torrent-featured-nav">
            <button type="button" data-bs-toggle="collapse" data-bs-target="#lbLeaderboards" aria-expanded="true" aria-controls="lbLeaderboards" aria-label="Toggle community leaderboards"><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
        </div>
    </header>
    <div class="collapse show community-leaderboard-content" id="lbLeaderboards">
        <nav class="torrent-featured-toolbar community-leaderboard-tabs nav nav-pills" role="tablist" aria-label="Community leaderboards">
            @foreach($boards as $key => $board)
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="lb-{{ $key }}-tab" data-bs-toggle="pill" data-bs-target="#lb-{{ $key }}" type="button" role="tab" aria-controls="lb-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $board['label'] }}</button>
            @endforeach
        </nav>
        <div class="tab-content">
            @foreach($boards as $key => $board)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lb-{{ $key }}" role="tabpanel" aria-labelledby="lb-{{ $key }}-tab" tabindex="0">
                    <ol class="torrent-featured-list community-leaderboard-list">
                        @forelse($board['users'] as $user)
                            @php($isMe = auth()->id() === $user->id)
                            <li class="{{ $isMe ? 'community-leaderboard-me' : '' }}">
                                <span class="torrent-featured-rank">{{ $loop->iteration }}</span>
                                <div class="torrent-featured-title">
                                    <a href="{{ route('profile.show', $user->id) }}" title="{{ $user->name }}">{{ $user->name }}@if($isMe) <i class="bi bi-person-check" aria-label="You"></i>@endif</a>
                                    <span>{{ $isMe ? 'You · ' : '' }}Top {{ strtolower($board['label']) }} · Last 24 hours</span>
                                </div>
                                <span class="community-leaderboard-value">{{ $key === 'seeders' ? number_format($user->seed_count).' torrents' : App\Helpers\FormatHelper::formatSize($user->{$board['metric']}) }}</span>
                            </li>
                        @empty
                            <li class="torrent-featured-empty"><i class="bi bi-trophy" aria-hidden="true"></i><strong>{{ $board['empty'] }}</strong><span>Member activity will appear here.</span></li>
                        @endforelse
                    </ol>
                    <footer class="torrent-featured-footer community-leaderboard-footer">
                        @if(auth()->check() && $board['rank'])
                            <div class="community-leaderboard-personal">
                                <span>Your rank <strong>#{{ $board['rank']['rank'] }}</strong></span>
                                <span>{{ $key === 'seeders' ? number_format($board['rank']['value']).' torrents' : App\Helpers\FormatHelper::formatSize($board['rank']['value']) }}</span>
                            </div>
                            <div class="community-leaderboard-personal-meta">
                                @if($board['movement'] !== null)
                                    <span class="{{ $board['movementClass'] }}" title="Compared to yesterday">{{ $board['movement'] > 0 ? '↑ +'.$board['movement'] : ($board['movement'] < 0 ? '↓ '.$board['movement'] : '—') }}</span>
                                @endif
                                @if($board['streak'] >= 2)<span><i class="bi bi-fire" aria-hidden="true"></i> {{ $board['streak'] }} day streak</span>@endif
                                @if($board['percentile'])<span>Top {{ $board['percentile'] }}%</span>@endif
                            </div>
                        @else
                            <span>Community activity</span><span>Top 7 · {{ $board['users']->count() }} members</span>
                        @endif
                    </footer>
                </div>
            @endforeach
        </div>
    </div>
</section>
