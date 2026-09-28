<nav class="profile-jump-links" aria-label="Profile sections">
    <a href="#profile-overview"><i class="bi bi-grid" aria-hidden="true"></i> Overview</a>
    @if(!empty($user->info))
        <a href="#profile-about"><i class="bi bi-person" aria-hidden="true"></i> About</a>
    @endif
    @if(($isOwner || $isModerator) && $user->timeline->isNotEmpty())
        <a href="#profile-timeline"><i class="bi bi-clock-history" aria-hidden="true"></i> Timeline</a>
    @endif
    <a href="#seederRankAccordion"><i class="bi bi-award" aria-hidden="true"></i> Rank guide</a>
</nav>

<div class="profile-section-heading">
    <div><span class="profile-eyebrow">Member overview</span><h2>At a glance</h2></div>
    <span class="profile-tenure"><i class="bi bi-calendar-check" aria-hidden="true"></i> Joined {{ $user->created_at->format('d M Y') }}</span>
</div>

<div class="profile-highlights">
    <article class="profile-highlight">
        <span class="profile-highlight-icon"><i class="bi bi-broadcast" aria-hidden="true"></i></span>
        <div><h3>Active seeds</h3><strong>{{ number_format($activeSeeds) }}</strong><p>Currently seeding peers</p></div>
    </article>
    <article class="profile-highlight">
        <span class="profile-highlight-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
        <div><h3>Total seed time</h3><strong>{{ number_format(floor($totalSeedTime / 86400)) }}<small>d</small> {{ floor(($totalSeedTime % 86400) / 3600) }}<small>h</small></strong><p>Combined across torrent history</p></div>
    </article>
    <article class="profile-highlight">
        <span class="profile-highlight-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
        <div><h3>{{ $user->uploaded >= $user->downloaded ? 'Upload surplus' : 'Download surplus' }}</h3><strong>{{ \App\Helpers\FormatHelper::formatSize(abs($user->uploaded - $user->downloaded)) }}</strong><p>Difference between transfer totals</p></div>
    </article>
</div>
