@extends('layouts.app')

@section('content')

@php
    $happyHour = \App\Models\HappyHour::where('active', true)->latest('start_at')->first();
    $multiplier = $happyHour ? $happyHour->upload_multiplier : 1;
    $remaining = $happyHour ? now()->diffForHumans($happyHour->end_at, ['short' => true, 'parts' => 2]) : null;
@endphp

<div class="container py-4 seedbonus-shop-page">

    <div class="shop-hero mb-4">
        <div class="hero-badge"><i class="bi bi-coin me-2"></i>SEEDBONUS SHOP</div>
        <h1 class="hero-title">Seed Bonus & Rewards</h1>
        <p class="hero-subtitle">Use your Seed Bonus points to boost your account and unlock useful rewards.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="shop-card h-100">
                <div class="card-body">
                    <div class="section-title"><i class="bi bi-speedometer2"></i>Seed Bonus</div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span>Your Seed Bonus Points</span>
                            <strong>{{ Auth::user()->seedbonus }}</strong>
                        </div>
                        <div class="stat-item">
                            <span>Currently Seeding</span>
                            <strong>{{ Auth::user()->seeding_torrent_count }} <small>Torrent{{ Auth::user()->seeding_torrent_count == 1 ? '' : 's' }}</small></strong>
                        </div>
                    </div>

                    <p class="info-line">
                        <strong>Your Earning Rate:</strong>
                        {{ Auth::user()->seedbonus_per_hour * $multiplier }} Points per hour
                        @if($happyHour)
                            <span class="status-badge success ms-2"><i class="bi bi-stars"></i>{{ $multiplier }}x Happy Hour</span>
                        @endif
                    </p>

                    @if($happyHour)
                        <div class="happy-hour-note">
                            <i class="bi bi-clock"></i>
                            Duration remaining: <strong>{{ $remaining }}</strong>
                            @if($happyHour->free_download)
                                <span class="status-badge warning">Free Downloads Enabled</span>
                            @endif
                        </div>
                    @else
                        <p class="muted-note mb-0">Standard: {{ config('seedbonus.points_per_hour') }} points per hour for seeding a torrent</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="shop-card h-100">
                <div class="card-body">
                    <div class="section-title"><i class="bi bi-lightning-charge"></i>Other Ways to Earn Seedbonus</div>
                    <ul class="earning-list">
                        <li><span>Uploading a Torrent</span><strong>{{ config('seedbonus.upload_torrent_points') }} points</strong></li>
                        <li><span>Thanking a Torrent</span><strong>{{ config('seedbonus.thank_points') }} points</strong></li>
                        <li><span>Commenting a Torrent</span><strong>{{ config('seedbonus.comment_points') }} point</strong></li>
                    </ul>
                    <div class="notice-box">
                        <p>*Note: The site administrator can change your seedbonus points without prior notice.</p>
                        <p>*Notă: Administratorul site-ului poate modifica punctele tale de seedbonus fără o notificare prealabilă.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="shop-section-title">
        <span><i class="bi bi-bag-check me-2"></i>Spend Your Points</span>
        <span class="points-badge"><i class="bi bi-coin me-1"></i>{{ Auth::user()->seedbonus }} Points</span>
    </div>

    <div class="row g-3">

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon"><i class="bi bi-upload"></i></div>
                <div class="reward-content">
                    <h3>10 GB Upload</h3><p class="reward-cost">{{ config('seedbonus.shop.upload.10') }} Points</p>
                    <form action="{{ route('shop.upload') }}" method="POST">@csrf<input type="hidden" name="amount" value="10">
                        <button type="submit" class="shop-btn {{ Auth::user()->seedbonus < config('seedbonus.shop.upload.10') ? 'disabled' : '' }}"><i class="bi bi-cart-plus"></i> Buy</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon"><i class="bi bi-upload"></i></div>
                <div class="reward-content">
                    <h3>25 GB Upload</h3><p class="reward-cost">{{ number_format(config('seedbonus.shop.upload.25')) }} Points</p>
                    <form action="{{ route('shop.upload') }}" method="POST">@csrf<input type="hidden" name="amount" value="25">
                        <button type="submit" class="shop-btn {{ Auth::user()->seedbonus < config('seedbonus.shop.upload.25') ? 'disabled' : '' }}"><i class="bi bi-cart-plus"></i> Buy</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <div class="reward-content">
                    <h3>100 GB Upload</h3><p class="reward-cost">{{ number_format(config('seedbonus.shop.upload.100')) }} Points</p>
                    <form action="{{ route('shop.upload') }}" method="POST">@csrf<input type="hidden" name="amount" value="100">
                        <button type="submit" class="shop-btn {{ Auth::user()->seedbonus < config('seedbonus.shop.upload.100') ? 'disabled' : '' }}"><i class="bi bi-cart-plus"></i> Buy</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon vip"><i class="bi bi-gem"></i></div>
                <div class="reward-content">
                    <h3>@if(Auth::user()->user_class >= 3 && Auth::user()->vip_until) VIP Status @else VIP Promotion (1 Year) @endif</h3>
                    @if(Auth::user()->user_class >= 3 && Auth::user()->vip_until)
                        <p class="reward-meta">Expires on {{ \Carbon\Carbon::parse(Auth::user()->vip_until)->format('F j, Y') }}</p>
                    @endif
                    <p class="reward-cost">{{ number_format(config('seedbonus.shop.vip')) }} Points</p>
                    <form action="{{ route('bonus.buyVip') }}" method="POST">@csrf
                        <button type="submit" class="shop-btn warning {{ Auth::user()->seedbonus < config('seedbonus.shop.vip') || Auth::user()->user_class >= 3 ? 'disabled' : '' }}">
                            @if(Auth::user()->user_class >= 3)<i class="bi bi-gem"></i> You are already VIP or higher
                            @elseif(Auth::user()->seedbonus < config('seedbonus.shop.vip'))<i class="bi bi-x-circle"></i> Not enough points
                            @else<i class="bi bi-gem"></i> Buy VIP @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon"><i class="bi bi-person-plus"></i></div>
                <div class="reward-content">
                    <h3>Buy Invites</h3><p class="reward-cost">{{ number_format(config('seedbonus.shop.invite')) }} Points per Invite</p>
                    <form action="{{ route('buy.invites') }}" method="POST">@csrf
                        <button type="submit" class="shop-btn success {{ Auth::user()->seedbonus < config('seedbonus.shop.invite') ? 'disabled' : '' }}"><i class="bi bi-person-plus"></i> Buy Invite</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon"><i class="bi bi-grid-1x2"></i></div>
                <div class="reward-content">
                    <h3>Buy Slots</h3>
                    <p class="reward-meta">Mark a torrent as double upload or freeleech</p>
                    <p class="reward-cost">{{ number_format(config('seedbonus.shop.slot')) }} Points per Slot</p>
                    <form action="{{ route('buy.slots') }}" method="POST">@csrf
                        <button type="submit" class="shop-btn info {{ Auth::user()->seedbonus < config('seedbonus.shop.slot') ? 'disabled' : '' }}"><i class="bi bi-grid-1x2"></i> Buy Slot</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card surprise-card h-100">
                <div class="reward-icon surprise"><i class="bi bi-gift"></i></div>
                <div class="reward-content">
                    <h3>Buy a Surprise!</h3><p class="reward-cost">{{ number_format(config('seedbonus.shop.surprise')) }} Points</p>
                    <p class="reward-meta strong">Possible Rewards</p>
                    <ul class="reward-list">
                        <li><i class="bi bi-hdd"></i> 100GB, 250GB, or 500GB Upload</li>
                        <li><i class="bi bi-gem"></i> VIP for 1, 2, or 3 months</li>
                        <li><i class="bi bi-person-plus"></i> 3, 6, or 10 Invites</li>
                        <li><i class="bi bi-grid-1x2"></i> 5, 10, or 15 Slots</li>
                    </ul>
                    <form action="{{ route('bonus.surprise') }}" method="POST">@csrf
                        <button type="submit" class="shop-btn danger {{ Auth::user()->seedbonus < config('seedbonus.shop.surprise') ? 'disabled' : '' }}"><i class="bi bi-gift"></i> Buy Surprise!</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon" style="background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.2);color:#fca5a5"><i class="bi bi-tools"></i></div>
                <div class="reward-content">
                    <h3>Clear 1 Hit &amp; Run</h3>
                    <p class="reward-meta">Auto-clears your oldest H&amp;R and tops up your upload so that torrent reaches a 1:1 ratio.</p>
                    @php($hnrCount = Auth::user()->hit_and_run_count)
                    <p class="reward-cost">{{ number_format(config('seedbonus.shop.clear_hnr')) }} Points</p>
                    <form action="{{ route('bonus.clearHnr') }}" method="POST">@csrf
                        <button type="submit" class="shop-btn danger {{ Auth::user()->seedbonus < config('seedbonus.shop.clear_hnr') || $hnrCount < 1 ? 'disabled' : '' }}">
                            @if($hnrCount < 1)<i class="bi bi-check-circle"></i> No hit &amp; runs
                            @else<i class="bi bi-tools"></i> Clear your oldest H&amp;R @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="reward-card h-100">
                <div class="reward-icon" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.2);color:#fbbf24"><i class="bi bi-shield-check"></i></div>
                <div class="reward-content">
                    <h3>Reset Warning</h3>
                    <p class="reward-meta">Removes your active site warning immediately.</p>
                    @php($hasWarning = Auth::user()->warned == 1)
                    <p class="reward-cost">{{ number_format(config('seedbonus.shop.reset_warning')) }} Points</p>
                    <form action="{{ route('bonus.resetWarning') }}" method="POST">@csrf
                        <button type="submit" class="shop-btn warning {{ Auth::user()->seedbonus < config('seedbonus.shop.reset_warning') || !$hasWarning ? 'disabled' : '' }}">
                            @if(!$hasWarning)<i class="bi bi-check-circle"></i> No active warning
                            @else<i class="bi bi-shield-check"></i> Reset warning @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.seedbonus-shop-page{color:#e5e7eb}
.shop-hero,.shop-card,.reward-card{background:linear-gradient(135deg,rgba(22,32,51,.96),rgba(15,23,42,.88));border:1px solid var(--ui-border,rgba(148,163,184,.16));box-shadow:0 14px 34px rgba(0,0,0,.25)}
.shop-hero{padding:26px 30px;border-radius:.75rem;position:relative;overflow:hidden}
.shop-hero:after{content:"";position:absolute;width:240px;height:240px;right:-120px;top:-140px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.12),transparent 68%);pointer-events:none}
.hero-badge{display:inline-flex;align-items:center;padding:.4rem .7rem;margin-bottom:.75rem;border-radius:.5rem;background:rgba(20,184,166,.09);border:1px solid rgba(20,184,166,.22);color:#67e8df;font-size:.82rem;font-weight:800;letter-spacing:.7px}
.hero-title{margin:0 0 .4rem;color:#f8fafc;font-size:clamp(1.7rem,3vw,2.3rem);font-weight:800}.hero-subtitle{margin:0;color:#94a3b8;font-size:.9rem}
.shop-card{border-radius:.7rem}.shop-card .card-body{padding:20px}.section-title,.shop-section-title{color:#f1f5f9;font-weight:800}.section-title{display:flex;align-items:center;gap:.55rem;margin-bottom:1rem;font-size:.95rem}.section-title i,.shop-section-title i{color:#67e8df}
.stats-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem;margin-bottom:.9rem}.stat-item{padding:.7rem .8rem;background:rgba(2,6,23,.42);border:1px solid rgba(148,163,184,.12);border-radius:.5rem}.stat-item span{display:block;color:#64748b;font-size:.72rem;margin-bottom:.15rem}.stat-item strong{color:#67e8df;font-size:1.05rem}.stat-item small{color:#94a3b8;font-size:.7rem;font-weight:500}
.info-line,.muted-note{color:#cbd5e1;font-size:.82rem}.info-line{margin-bottom:.75rem}.muted-note{color:#94a3b8}
.status-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .5rem;border-radius:.4rem;font-size:.78rem;font-weight:700}.status-badge.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#86efac}.status-badge.warning{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);color:#fbbf24}
.happy-hour-note{display:flex;flex-wrap:wrap;align-items:center;gap:.55rem;padding:.65rem .75rem;background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.16);border-radius:.5rem;color:#fcd34d;font-size:.88rem}
.earning-list{list-style:none;padding:0;margin:0 0 .9rem}.earning-list li{display:flex;justify-content:space-between;gap:1rem;padding:.48rem 0;border-bottom:1px solid rgba(148,163,184,.09);color:#cbd5e1;font-size:.88rem}.earning-list li:last-child{border-bottom:0}.earning-list strong{color:#67e8df;white-space:nowrap}
.notice-box{padding:.65rem .75rem;border-left:2px solid rgba(20,184,166,.5);background:rgba(20,184,166,.04);color:#94a3b8;font-size:.82rem}.notice-box p{margin:0 0 .35rem}.notice-box p:last-child{margin-bottom:0}
.shop-section-title{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:.25rem 0 .85rem;font-size:.95rem}.points-badge{padding:.35rem .6rem;border-radius:.45rem;background:rgba(20,184,166,.08);border:1px solid rgba(20,184,166,.2);color:#67e8df;font-size:.7rem}
.reward-card{display:flex;gap:.9rem;padding:18px;border-radius:.7rem;transition:transform .18s ease,border-color .18s ease,background .18s ease}.reward-card:hover{transform:translateY(-2px);border-color:rgba(20,184,166,.3)}
.reward-icon{flex:0 0 40px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:.55rem;background:rgba(20,184,166,.1);border:1px solid rgba(20,184,166,.2);color:#67e8df;font-size:1.05rem}.reward-icon.vip{background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.2);color:#fbbf24}.reward-icon.surprise{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.2);color:#fca5a5}
.reward-content{min-width:0;flex:1}.reward-content h3{margin:.05rem 0 .2rem;color:#f8fafc;font-size:.92rem;font-weight:750}.reward-cost{margin:0 0 .65rem;color:#67e8df;font-size:.88rem;font-weight:700}.reward-meta{margin:0 0 .5rem;color:#94a3b8;font-size:.72rem;line-height:1.45}.reward-meta.strong{color:#cbd5e1;font-weight:700}
.reward-list{padding-left:0;margin:0 0 .8rem;list-style:none;color:#94a3b8;font-size:.82rem}.reward-list li{margin-bottom:.25rem}.reward-list i{width:17px;color:#67e8df}
.shop-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;min-height:36px;padding:.5rem .8rem;border-radius:.5rem;border:1px solid rgba(20,184,166,.3);background:rgba(20,184,166,.1);color:#67e8df;font-size:.76rem;font-weight:700;transition:.18s}.shop-btn:hover:not(.disabled){transform:translateY(-1px);background:rgba(20,184,166,.18);color:#99f6ef}.shop-btn.warning{border-color:rgba(245,158,11,.28);background:rgba(245,158,11,.08);color:#fbbf24}.shop-btn.success{border-color:rgba(34,197,94,.28);background:rgba(34,197,94,.08);color:#86efac}.shop-btn.info{border-color:rgba(56,189,248,.28);background:rgba(56,189,248,.08);color:#7dd3fc}.shop-btn.danger{border-color:rgba(239,68,68,.28);background:rgba(239,68,68,.08);color:#fca5a5}.shop-btn.disabled{opacity:.48;cursor:not-allowed;pointer-events:none}
@media(max-width:768px){.seedbonus-shop-page{padding-top:1.25rem!important}.shop-hero{padding:20px}.shop-card .card-body,.reward-card{padding:16px}.stats-grid{grid-template-columns:1fr}.shop-section-title{align-items:flex-start;flex-direction:column}.reward-card{gap:.75rem}}
@media(prefers-reduced-motion:reduce){.reward-card,.shop-btn{transition:none}}
</style>

@endsection
