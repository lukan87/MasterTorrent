<div class="modal fade achievement-modal" id="achievementModal-{{ $category['key'] }}" tabindex="-1" aria-labelledby="achievementModalTitle-{{ $category['key'] }}" aria-describedby="achievementModalDescription-{{ $category['key'] }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div class="achievement-modal-heading">
                    <span class="achievement-category-icon"><i class="bi {{ $category['icon'] }}" aria-hidden="true"></i></span>
                    <div>
                        <span class="achievement-eyebrow">Achievement journey</span>
                        <h2 class="modal-title" id="achievementModalTitle-{{ $category['key'] }}">{{ $category['name'] }}</h2>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="achievementModalDescription-{{ $category['key'] }}" class="achievement-modal-description">{{ $category['description'] }}</p>
                <div class="achievement-modal-summary">
                    <span><i class="bi bi-trophy" aria-hidden="true"></i> <strong>{{ $category['earned_count'] }}</strong> of {{ count($category['tiers']) }} milestones unlocked</span>
                    <span>One award per milestone · VIP is time limited</span>
                </div>
                <ol class="achievement-modal-tiers">
                    @foreach($category['tiers'] as $tier)
                        <li id="achievement-{{ $category['key'] }}-{{ $category['thresholds'][$tier['tier'] - 1] }}" tabindex="-1" class="achievement-modal-tier {{ $tier['award'] ? 'is-earned' : '' }}">
                            <div class="achievement-tier-heading">
                                <span class="achievement-tier-number">@if($tier['award'])<i class="bi bi-check-lg" aria-hidden="true"></i>@else{{ $tier['tier'] }}@endif</span>
                                <div>
                                    <span class="achievement-tier-label">Tier {{ $tier['tier'] }}</span>
                                    <h3>{{ $tier['target'] }}</h3>
                                </div>
                                <span class="achievement-tier-state">{{ $tier['award'] ? 'Unlocked' : ($tier['qualified'] ? 'Reward pending' : 'In progress') }}</span>
                            </div>
                            <div class="achievement-progress" role="progressbar" aria-label="Tier {{ $tier['tier'] }} progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $tier['percent'] }}"><span style="width: {{ $tier['percent'] }}%"></span></div>
                            <div class="achievement-tier-meta">
                                @if($tier['award'])
                                    <span><i class="bi bi-calendar-check" aria-hidden="true"></i> Unlocked {{ $tier['award']->earned_at->format('M j, Y · H:i') }}</span>
                                @else
                                    <span>{{ $tier['remaining'] }}</span>
                                @endif
                                <span>{{ $tier['percent'] }}% complete</span>
                            </div>
                            <div class="achievement-tier-reward">
                                <i class="bi bi-gift" aria-hidden="true"></i>
                                @if($tier['award'])
                                    <span><strong>+{{ number_format($tier['award']->bonus_awarded, 2) }} points</strong> credited @if($tier['award']->vip_months_awarded) · {{ $tier['award']->vip_months_awarded }} months VIP @endif @if($tier['award']->tokens_awarded) · +{{ $tier['award']->tokens_awarded }} {{ \Illuminate\Support\Str::plural('token', $tier['award']->tokens_awarded) }} @endif @if($tier['award']->invites_awarded) · +{{ $tier['award']->invites_awarded }} {{ \Illuminate\Support\Str::plural('invite', $tier['award']->invites_awarded) }} @endif</span>
                                @else
                                    <span><strong>+{{ number_format($tier['reward_points'], 2) }} points</strong> at unlock @if($tier['vip_months']) · {{ $tier['vip_months'] }} months VIP @endif @if($tier['reward_tokens']) · +{{ $tier['reward_tokens'] }} {{ \Illuminate\Support\Str::plural('token', $tier['reward_tokens']) }} @endif @if($tier['invites']) · +{{ $tier['invites'] }} {{ \Illuminate\Support\Str::plural('invite', $tier['invites']) }} @endif</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="achievement-footnote"><i class="bi bi-info-circle" aria-hidden="true"></i> Bonus rewards are fixed per tier and limited by the {{ number_format(config('seedbonus.cap', 999999.99), 2) }} point balance cap. Unlocks remain yours even if activity changes.</p>
            </div>
            <div class="modal-footer"><button type="button" class="achievement-modal-close" data-bs-dismiss="modal">Back to achievements</button></div>
        </div>
    </div>
</div>
