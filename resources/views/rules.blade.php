@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/rules.css') }}?v={{ filemtime(public_path('css/rules.css')) }}">

<div class="rules-page" id="fileiplay-rules">
    <header class="rules-hero">
        <div class="rules-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> FileIplay community guide</div>
        <h1>Great releases.<br><span>Even better community.</span></h1>
        <p>A few shared standards keep FileIplay a place worth being part of. Read the rules, respect each other, and keep seeding.</p>
        <div class="rules-principles" aria-label="Community values">
            <span><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Seed generously</span>
            <span><i class="bi bi-gem" aria-hidden="true"></i> Upload quality</span>
            <span><i class="bi bi-people" aria-hidden="true"></i> Respect everyone</span>
        </div>
        <i class="bi bi-shield-check rules-hero-mark" aria-hidden="true"></i>
    </header>

    <div class="rules-layout">
        <nav class="rules-navigation" aria-label="Rule sections">
            <div class="rules-navigation-heading">THE RULEBOOK <span>10 sections</span></div>
            <div class="rules-tabs" aria-label="Rules categories">
                <a class="rules-tab" id="tab-generalRules" href="#generalRules" data-rules-tab="generalRules">
                    <span class="rules-tab-number">01</span><i class="bi bi-shield-check" aria-hidden="true"></i><span>General Rules</span>
                </a>
                <a class="rules-tab" id="tab-seedingRules" href="#seedingRules" data-rules-tab="seedingRules">
                    <span class="rules-tab-number">02</span><i class="bi bi-arrow-down-up" aria-hidden="true"></i><span>Downloading &amp; Seeding</span>
                </a>
                <a class="rules-tab" id="tab-forumRules" href="#forumRules" data-rules-tab="forumRules">
                    <span class="rules-tab-number">03</span><i class="bi bi-envelope" aria-hidden="true"></i><span>Forum &amp; Private Messages</span>
                </a>
                <a class="rules-tab" id="tab-uploadingRules" href="#uploadingRules" data-rules-tab="uploadingRules">
                    <span class="rules-tab-number">04</span><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span>Uploading</span>
                </a>
                <a class="rules-tab" id="tab-commentsRules" href="#commentsRules" data-rules-tab="commentsRules">
                    <span class="rules-tab-number">05</span><i class="bi bi-chat-left-text" aria-hidden="true"></i><span>Comments</span>
                </a>
                <a class="rules-tab" id="tab-chatRules" href="#chatRules" data-rules-tab="chatRules">
                    <span class="rules-tab-number">06</span><i class="bi bi-chat-dots" aria-hidden="true"></i><span>Chat</span>
                </a>
                <a class="rules-tab" id="tab-forumsRules" href="#forumsRules" data-rules-tab="forumsRules">
                    <span class="rules-tab-number">07</span><i class="bi bi-layout-text-window-reverse" aria-hidden="true"></i><span>Forums</span>
                </a>
                <a class="rules-tab" id="tab-profileRules" href="#profileRules" data-rules-tab="profileRules">
                    <span class="rules-tab-number">08</span><i class="bi bi-person-badge" aria-hidden="true"></i><span>Avatars &amp; Profiles</span>
                </a>
                <a class="rules-tab" id="tab-featuresRules" href="#featuresRules" data-rules-tab="featuresRules">
                    <span class="rules-tab-number">09</span><i class="bi bi-stars" aria-hidden="true"></i><span>Site Features</span>
                </a>
                <a class="rules-tab" id="tab-userClasses" href="#userClasses" data-rules-tab="userClasses">
                    <span class="rules-tab-number">10</span><i class="bi bi-trophy" aria-hidden="true"></i><span>User Classes</span>
                </a>
            </div>
            <p class="rules-navigation-note"><i class="bi bi-info-circle" aria-hidden="true"></i> All sections apply to every member.</p>
        </nav>

        <div class="rules-content">
            <section class="rules-panel" id="generalRules" aria-labelledby="heading-generalRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 01 / 10</p><h2 id="heading-generalRules">1. General Rules</h2><p class="rules-panel-description">The foundations of a trusted community.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
                                <li>
                                    Respect staff – their decisions are final.
                                </li>
                                <li>
                                    Multiple accounts are strictly forbidden.
                                </li>
                                <li>
                                    Impersonating staff members is prohibited.
                                </li>
                                <li>
                                    Redistributing torrents to other trackers is prohibited.
                                </li>
                                <li>
                                    Selling accounts or invites is forbidden.
                                </li>
                                <li>
                                    Access to {{ config('app.name') }} is a privilege, not a right.
                                </li>
                                <li>
                                    Racist, discriminatory or offensive behavior is strictly prohibited.
                                </li>
                            </ul>
                </div>
            </section>
            <section class="rules-panel" id="seedingRules" aria-labelledby="heading-seedingRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-arrow-down-up" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 02 / 10</p><h2 id="heading-seedingRules">2. Downloading &amp; Seeding</h2><p class="rules-panel-description">Keep the swarm alive. Give back what you take.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
                                <li>
                                    Keep torrents seeding after download.
                                </li>
                                <li>
                                    To avoid a Hit &amp; Run, reach a <strong>1:1 ratio</strong>
                                    or seed each torrent for at least <strong>12 hours within 7 days</strong>
                                    of completing the download.
                                </li>
                                <li>
                                    Hit & Run over 20 → download restriction.
                                </li>
                                <li>
                                    Freeleech torrents still require seeding.
                                </li>
                                <li>
                                    VIP class is exempt from Hit & Run rules.
                                </li>
                            </ul>
                </div>
            </section>
            <section class="rules-panel" id="forumRules" aria-labelledby="heading-forumRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 03 / 10</p><h2 id="heading-forumRules">3. Forum &amp; Private Messages</h2><p class="rules-panel-description">Keep conversations constructive and respectful.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
                                <li>
                                    No spam or aggressive behavior.
                                </li>
                                <li>
                                    Use edit instead of multi-posting.
                                </li>
                                <li>
                                    Advertising other trackers is prohibited.
                                </li>
                                <li>
                                    Comments must be constructive.
                                </li>
                            </ul>
                </div>
            </section>
            <section class="rules-panel" id="uploadingRules" aria-labelledby="heading-uploadingRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 04 / 10</p><h2 id="heading-uploadingRules">4. Uploading</h2><p class="rules-panel-description">Quality releases start with thoughtful uploads.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
    <li>Search before you upload; duplicate torrents will be trumped or deleted.</li>
    <li>Upload to the correct category with an accurate, non-misleading title and description.</li>
    <li>Do not upload torrent files from other trackers, and do not upload our torrents to other trackers.</li>
    <li>Fill out media info / description fields properly. Lazy or empty uploads may be removed.</li>
    <li>Personal releases and internal releases follow the same quality standards as everything else.</li>
</ul>
                    @include('rules.partials.release-naming-guide')
                </div>
            </section>
            <section class="rules-panel" id="commentsRules" aria-labelledby="heading-commentsRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-chat-left-text" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 05 / 10</p><h2 id="heading-commentsRules">5. Comments</h2><p class="rules-panel-description">Talk about the release. Make every comment count.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
    <li>Keep comments about the release itself. Complaints about actors, directors, or “why isn’t this X format” belong elsewhere, not in the comments.</li>
    <li>No “first” comments, no spam, and don’t comment just to farm an achievement.</li>
    <li>No links of any kind in comments.</li>
    <li>Report a comment if it breaks the rules; don’t argue with it publicly.</li>
</ul>
                </div>
            </section>
            <section class="rules-panel" id="chatRules" aria-labelledby="heading-chatRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 06 / 10</p><h2 id="heading-chatRules">6. Chat</h2><p class="rules-panel-description">A welcoming room for everyone at FileIplay.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
    <li>Be respectful. No harassment, slurs, or targeted insults toward staff or other members.</li>
    <li>No requesting freeleech, upload credit, or invites in chat.</li>
    <li>No spoilers, no politics, no religion. Keep it easy to be in.</li>
    <li>Excessive caps, spam, or flooding the room will get you muted.</li>
    <li>Staff instructions in chat are not optional. If a staff member asks you to stop something, stop.</li>
</ul>
                </div>
            </section>
            <section class="rules-panel" id="forumsRules" aria-labelledby="heading-forumsRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-layout-text-window-reverse" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 07 / 10</p><h2 id="heading-forumsRules">7. Forums</h2><p class="rules-panel-description">Good threads stay organised and on topic.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
    <li>Post in the correct section. Use the edit button to add to your own recent post instead of double-posting.</li>
    <li>No links to other trackers, cracks, or unrelated advertising.</li>
    <li>No sharing personal information, yours or anyone else’s.</li>
    <li>Keep threads on topic. Repeated derailing may get a thread locked.</li>
    <li>Requests only belong in the designated request section, never in general forums or comments.</li>
</ul>
                </div>
            </section>
            <section class="rules-panel" id="profileRules" aria-labelledby="heading-profileRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 08 / 10</p><h2 id="heading-profileRules">8. Avatars &amp; Profiles</h2><p class="rules-panel-description">Express yourself with respect for the community.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
    <li>No avatars or profile content containing nudity, racism, sexism, or anything targeting religion, or that could reasonably offend other members.</li>
    <li>Don’t use an avatar or name that could be mistaken for a staff member.</li>
    <li>Staff can remove a rule-breaking avatar without warning. If you’re unsure whether something’s okay, ask first.</li>
</ul>
                </div>
            </section>
            <section class="rules-panel" id="featuresRules" aria-labelledby="heading-featuresRules" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 09 / 10</p><h2 id="heading-featuresRules">9. Site Features</h2><p class="rules-panel-description">Enjoy the rewards. Play fair.</p></div>
                </header>
                <div class="rules-panel-body">
                    <ul>
    <li>Achievements, reports, and other automated systems exist to reward genuine activity. Gaming them (spam commenting, fake reports, etc.) is treated as cheating.</li>
    <li>Bonus Points earned through seeding, bets, the lottery, or events are yours to spend on the site’s own features. Buying or selling them off-site is not allowed.</li>
    <li>Betting and the lottery are for fun. Please gamble responsibly and only wager what you’re comfortable losing.</li>
</ul>
                </div>
            </section>
            <section class="rules-panel" id="userClasses" aria-labelledby="heading-userClasses" data-rules-panel>
                <header class="rules-panel-header">
                    <span class="rules-panel-icon"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                    <div><p class="rules-section-label">SECTION 10 / 10</p><h2 id="heading-userClasses">10. User Classes</h2><p class="rules-panel-description">Contribution, trust, and your next milestone.</p></div>
                </header>
                <div class="rules-panel-body">
                    <p>
                                User classes represent contribution level and trust within the tracker.
                            </p>
                            {{-- USER CLASSES --}}
                            <ul class="rules-classes">
                                <li>
                                    <span style="color: var(--theme-muted, SlateGrey);">
                                        <strong>User</strong>
                                    </span>
                                    – Default class.
                                </li>
                                <li>
                                    <span style="color: var(--theme-teal-text, cyan);">
                                        <strong>Elite User</strong>
                                    </span>
                                    – Stable member with solid ratio and activity.
                                    Can create requests and send invites.
                                </li>
                                <li>
                                    <span style="color: var(--theme-amber-text, orange);">
                                        <strong>Uploader</strong>
                                    </span>
                                    – Active content contributor.
                                </li>
                                <li>
                                    <span style="color: var(--theme-green-text, green);">
                                        <strong>VIP</strong>
                                    </span>
                                    – Exempt from Hit & Run rules.
                                </li>
                                <li>
                                    <span style="color: var(--theme-amber-text, gold);">
                                        <strong>Special User</strong>
                                    </span>
                                    – High contribution member.
                                </li>
                                <li>
                                    <span style="color: var(--theme-amber-text, yellow);">
                                        <strong>Moderator</strong>
                                    </span>
                                    – Enforces rules.
                                </li>
                                <li>
                                    <span style="color: var(--theme-red-text, red);">
                                        <strong>Admin</strong>
                                    </span>
                                    – Full administrative authority.
                                </li>
                                <li>
                                    <span style="color: var(--theme-teal-text, DarkCyan);">
                                        <strong>Owner</strong>
                                    </span>
                                    – Final authority.
                                </li>
                                <li>
                                    <span style="color: var(--theme-amber-text, BurlyWood);">
                                        <strong>Web Developer</strong>
                                    </span>
                                    – Platform architect.
                                </li>
                            </ul>
                            <hr>
                            {{-- AUTOMATIC PROMOTION --}}
                            <h3 class="rules-promotion-title">
                                ⬆ Automatic Promotion: User → Elite User
                            </h3>
                            <p class="text-danger fw-bold">
                                Promotion is fully automatic. No requests. No exceptions.
                            </p>
                            <ul>
                                <li>
                                    Account must be at least 5 months old.
                                </li>
                                <li>
                                    Minimum 500GB upload and 250GB download.
                                </li>
                                <li>
                                    Minimum overall ratio of 1.1.
                                </li>
                                <li>
                                    Consistent seeding activity.
                                </li>
                                <li>
                                    No active Hit & Run violations.
                                </li>
                                <li>
                                    No rule violations or warnings.
                                </li>
                                <li>
                                    At least 50 forum posts.
                                </li>
                                <li>
                                    At least 50 torrent comments.
                                </li>
                                <li>
                                    At least 50 torrent likes.
                                </li>
                                <li>
                                    Hit and Run count to be 0.
                                </li>
                            </ul>
                </div>
            </section>
            <footer class="rules-footer"><i class="bi bi-heart" aria-hidden="true"></i><span>FileIplay · Seed more than you take. Quality over quantity.</span></footer>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/rules.js') }}?v={{ filemtime(public_path('js/rules.js')) }}" defer></script>
@endpush
