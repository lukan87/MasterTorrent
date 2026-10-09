@if(!empty($achievementCategories))
@php
    $achievementTotal = collect($achievementCategories)->sum(fn ($category) => count($category['tiers']));
    $achievementEarned = collect($achievementCategories)->sum('earned_count');
@endphp
<div class="modal fade achievement-modal achievement-overview-modal" id="achievements" tabindex="-1" aria-labelledby="achievementsTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="achievement-eyebrow"><i class="bi bi-trophy" aria-hidden="true"></i> Every contribution counts</span>
                    <h2 class="modal-title" id="achievementsTitle">Achievements</h2>
                    <span class="achievement-overview-count">{{ $achievementEarned }} of {{ $achievementTotal }} unlocked</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div class="achievement-hero">
                <div>
                    <span class="achievement-eyebrow">Build your legacy</span>
                    <h3>Small steps. Lasting achievements.</h3>
                    <p>Share something great, join a conversation, or keep the swarm alive. Your next milestone is waiting.</p>
                </div>
                <div class="achievement-score"><strong>{{ $achievementEarned }}<span> / {{ $achievementTotal }}</span></strong><span>milestones unlocked</span></div>
            </div>
            <div class="achievement-rules">
                <i class="bi bi-gift" aria-hidden="true"></i>
                <span>Each milestone awards fixed bonus points and tokens shown in its tier. Tokens can be used for free download or double upload. Bonus points are limited by the {{ number_format(config('seedbonus.cap', 999999.99), 2) }} point balance cap. Selected milestones also award invites and time-limited VIP. Rewards are paid once, including when your balance is zero.</span>
            </div>
            <div class="achievement-grid">
                @foreach($achievementCategories as $category)
                    @php($next = collect($category['tiers'])->first(fn ($tier) => !$tier['award']))
                    <article class="achievement-category {{ !$next ? 'achievement-mastered' : '' }}">
                        <div class="achievement-category-heading">
                            <span class="achievement-category-icon"><i class="bi {{ $category['icon'] }}" aria-hidden="true"></i></span>
                            <div><h4>{{ $category['name'] }}</h4><span>{{ $category['earned_count'] }} / {{ count($category['tiers']) }} unlocked</span></div>
                            @if(!$next)<i class="bi bi-patch-check-fill achievement-complete-icon" aria-label="All milestones unlocked"></i>@endif
                        </div>
                        <p class="achievement-description">{{ $category['description'] }}</p>
                        <div class="achievement-next">
                            <span>{{ $next ? 'Next milestone · Tier '.$next['tier'] : 'Category complete' }}</span>
                            <strong>{{ $next['target'] ?? 'Every milestone unlocked' }}</strong>
                            <div class="achievement-progress" role="progressbar" aria-label="{{ $category['name'] }} next milestone" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $next['percent'] ?? 100 }}"><span style="width: {{ $next['percent'] ?? 100 }}%"></span></div>
                            <small>{{ $next['remaining'] ?? 'Thank you for helping the community grow.' }}</small>
                        </div>
                        <button type="button" class="achievement-card-open" data-achievement-target="#achievementModal-{{ $category['key'] }}" aria-haspopup="dialog" aria-label="View all milestones for {{ $category['name'] }}">
                            <span>Explore milestones</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </button>
                    </article>
                @endforeach
            </div>
            <p class="achievement-footnote"><i class="bi bi-info-circle" aria-hidden="true"></i> @if(config('achievements.awarding_enabled', false)) Activity rewards arrive after processing. Tracker milestones and anniversaries are checked every five minutes. @else Achievement rewards are not active yet. Your progress is shown below each milestone. @endif Unlocked achievements stay yours even if your active seed count changes.</p>
            </div>
            <div class="modal-footer"><button type="button" class="achievement-modal-close" data-bs-dismiss="modal">Back to profile</button></div>
        </div>
    </div>
</div>
@if(empty($lazyAchievements) || request()->boolean('full_details'))
@foreach($achievementCategories as $category)
    @include('profile.partials.achievement-modal', ['category' => $category])
@endforeach
@endif
@include('profile.partials.achievements-css')
@push('scripts')
<script>
(() => {
    const overview = document.getElementById('achievements');
    document.querySelectorAll('.achievement-modal').forEach(modal => document.body.appendChild(modal));
    let switching = false;
    overview.addEventListener('click', event => {
        const trigger = event.target.closest('[data-achievement-target]');
        if (!trigger || switching || !window.bootstrap) return;
        const detail = document.querySelector(trigger.dataset.achievementTarget);
        if (!detail) return;
        switching = true;
        const scrollTop = overview.querySelector('.modal-body').scrollTop;
        overview.addEventListener('hidden.bs.modal', () => {
            detail.addEventListener('hidden.bs.modal', () => {
                overview.addEventListener('shown.bs.modal', () => {
                    overview.querySelector('.modal-body').scrollTop = scrollTop;
                    trigger.focus({ preventScroll: true });
                    switching = false;
                }, { once: true });
                bootstrap.Modal.getOrCreateInstance(overview).show();
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(detail).show();
        }, { once: true });
        bootstrap.Modal.getOrCreateInstance(overview).hide();
    });
    const revealAchievements = () => {
        if (!window.bootstrap || switching) return;
        const tier = document.getElementById(window.location.hash.slice(1));
        if (tier && tier.classList.contains('achievement-modal-tier')) {
            const detail = tier.closest('.achievement-modal');
            const focusTier = () => {
                tier.scrollIntoView({ block: 'center', behavior: 'auto' });
                tier.focus({ preventScroll: true });
            };
            const showDetail = () => {
                detail.addEventListener('shown.bs.modal', focusTier, { once: true });
                bootstrap.Modal.getOrCreateInstance(detail).show();
                if (detail.classList.contains('show')) focusTier();
            };
            const open = document.querySelector('.achievement-modal.show');
            if (open && open !== detail) {
                open.addEventListener('hidden.bs.modal', showDetail, { once: true });
                bootstrap.Modal.getOrCreateInstance(open).hide();
            } else {
                showDetail();
            }
        } else if (window.location.hash === '#achievements') {
            bootstrap.Modal.getOrCreateInstance(overview).show();
        }
    };
    revealAchievements();
    window.addEventListener('hashchange', revealAchievements);
})();
</script>
@endpush
@endif
