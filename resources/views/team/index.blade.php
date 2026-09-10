@extends('layouts.app')

@section('content')

@php
    $roles = App\Models\UserClass::getClasses();

    $customOrder = [
        App\Models\UserClass::OWNER,
        App\Models\UserClass::ADMIN,
        App\Models\UserClass::MODERATOR,
        App\Models\UserClass::UPLOADER,
    ];

    $remainingRoles = array_diff(array_keys($roles), $customOrder);
    $orderedRoles = array_merge($customOrder, $remainingRoles);

    $roleIcons = [
        App\Models\UserClass::OWNER       => 'bi-crown-fill',
        App\Models\UserClass::ADMIN       => 'bi-shield-fill-check',
        App\Models\UserClass::MODERATOR   => 'bi-hammer',
        App\Models\UserClass::UPLOADER    => 'bi-cloud-arrow-up-fill',
    ];

    $roleDescriptions = [
        App\Models\UserClass::OWNER       => 'The founder and architect of the platform',
        App\Models\UserClass::ADMIN       => 'Full administrative control over the tracker',
        App\Models\UserClass::MODERATOR   => 'Keeping the community safe and on track',
        App\Models\UserClass::UPLOADER    => 'Contributors powering the content library',
    ];
@endphp

<div class="container team-page py-4">

    {{-- HERO --}}
    <div class="team-hero">

        <div class="hero-orbs">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
        </div>

        <div class="hero-content text-center">

            <div class="hero-badge">
                <i class="bi bi-people-fill"></i>
                {{ config('app.name') }}
            </div>

            <h1 class="hero-title">Our Team</h1>

            <p class="hero-subtitle">
                The dedicated people who keep everything running 24/7
            </p>

            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-number">{{ $staff->count() }}</span>
                    <span class="stat-label">Members</span>
                </div>
                <div class="hero-stat-divider"></div>
                @foreach($customOrder as $classId)
                    @php $count = $staff->where('user_class', $classId)->count(); @endphp
                    @if($count > 0)
                        <div class="hero-stat">
                            <span class="stat-number" style="color: {{ App\Models\UserClass::getClassColor($classId) }}">{{ $count }}</span>
                            <span class="stat-label">{{ $roles[$classId] }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="hero-stat-divider"></div>
                        @endif
                    @endif
                @endforeach
            </div>

        </div>
    </div>

    {{-- FILTER PILLS --}}
    <div class="team-filters" id="teamFilters">
        <button class="filter-pill active" data-role="all">
            <i class="bi bi-grid-3x3-gap me-1"></i> All
        </button>
        @foreach ($orderedRoles as $classId)
            @php
                $roleName = $roles[$classId] ?? null;
                $roleMembers = $staff->where('user_class', $classId);
            @endphp
            @if ($roleName && $roleMembers->isNotEmpty())
                <button class="filter-pill" data-role="{{ $classId }}">
                    <i class="bi {{ $roleIcons[$classId] ?? 'bi-person-fill' }} me-1"></i>
                    {{ $roleName }}
                    <span class="pill-count">{{ $roleMembers->count() }}</span>
                </button>
            @endif
        @endforeach
    </div>

    {{-- ROLE SECTIONS --}}
    @foreach ($orderedRoles as $classId)

        @php
            $roleName = $roles[$classId] ?? null;
            $roleMembers = $staff->where('user_class', $classId);
            $classColor = App\Models\UserClass::getClassColor($classId);
        @endphp

        @if ($roleName && $roleMembers->isNotEmpty())

            <div class="role-section" data-role-section="{{ $classId }}">

                <div class="role-header" style="--role-color: {{ $classColor }}">
                    <div class="role-icon-wrap">
                        <i class="bi {{ $roleIcons[$classId] ?? 'bi-person-fill' }}"></i>
                    </div>
                    <div class="role-info">
                        <h2 class="role-title">{{ $roleName }}s</h2>
                        @if(isset($roleDescriptions[$classId]))
                            <p class="role-desc">{{ $roleDescriptions[$classId] }}</p>
                        @endif
                    </div>
                    <div class="role-count-badge" style="--role-color: {{ $classColor }}">
                        {{ $roleMembers->count() }} {{ Str::plural('member', $roleMembers->count()) }}
                    </div>
                </div>

                <div class="row g-4">
                    @foreach ($roleMembers as $member)
                        @php
                            $memberColor = App\Models\UserClass::getClassColor($member->user_class);
                            $leaderClasses = [
                                App\Models\UserClass::OWNER,
                                App\Models\UserClass::ADMIN
                            ];
                            $isLeader = in_array($member->user_class, $leaderClasses);
                        @endphp

                        <div class="{{ $isLeader ? 'col-md-6 col-xl-4' : 'col-md-6 col-lg-4 col-xl-3' }} member-col" data-role-col="{{ $member->user_class }}">
                            <div class="team-card {{ $isLeader ? 'team-card--leader' : '' }}" style="--card-color: {{ $memberColor }}">

                                <div class="card-accent"></div>
                                <div class="card-glow-blob"></div>

                                <div class="card-inner">

                                    <div class="card-avatar">
                                        <img
                                            src="{{ $member->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                                            alt="{{ $member->name }}"
                                            loading="lazy"
                                        >
                                        <div class="avatar-ring"></div>
                                        @if($isLeader)
                                            <div class="avatar-crown">
                                                <i class="bi bi-star-fill"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <a href="{{ route('profile.show', ['id'=>$member->id,'name'=>$member->name]) }}"
                                       class="card-name">
                                        {{ $member->name }}
                                    </a>

                                    <div class="card-role-tag">
                                        <i class="bi {{ $roleIcons[$member->user_class] ?? 'bi-person-fill' }}"></i>
                                        {{ $roleName }}
                                    </div>

                                    <div class="card-actions">
                                        <a href="{{ route('messages.create', ['receiver_id' => $member->id]) }}"
                                           class="card-btn card-btn--primary">
                                            <i class="bi bi-chat-dots-fill"></i>
                                            <span>Message</span>
                                        </a>
                                        <a href="{{ route('profile.show', ['id' => $member->id, 'name' => $member->name]) }}"
                                           class="card-btn card-btn--ghost">
                                            <i class="bi bi-person-fill"></i>
                                            <span>Profile</span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

        @endif

    @endforeach

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* filter pills */
    const pills = document.querySelectorAll('.filter-pill');
    const sections = document.querySelectorAll('[data-role-section]');

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const role = this.dataset.role;
            if (role === 'all') {
                sections.forEach(s => s.style.display = '');
            } else {
                sections.forEach(s => {
                    s.style.display = s.dataset.roleSection === role ? '' : 'none';
                });
            }
        });
    });

    /* scroll reveal */
    const revealEls = document.querySelectorAll('.team-card, .role-header, .hero-content');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    revealEls.forEach(el => observer.observe(el));

});
</script>
@endpush

<style>

/* BACKGROUND */
.team-page{
    position:relative;
    z-index:1;
}

/* HERO */
.team-hero{
    position:relative;
    overflow:hidden;
    padding:64px 40px 56px;
    margin-bottom:40px;
    border-radius:28px;
    background:
        linear-gradient(160deg, rgba(255,255,255,.05) 0%, rgba(255,255,255,.01) 100%);
    border:1px solid rgba(255,255,255,.07);
    backdrop-filter:blur(24px);
    box-shadow:
        0 30px 80px rgba(0,0,0,.4),
        inset 0 1px 0 rgba(255,255,255,.06);
}

.hero-orbs{
    position:absolute;
    inset:0;
    overflow:hidden;
    pointer-events:none;
}

.orb{
    position:absolute;
    border-radius:50%;
    filter:blur(80px);
    opacity:.5;
}

.orb-1{
    width:300px; height:300px;
    top:-100px; left:10%;
    background:rgba(45,212,191,.20);
    animation:orbFloat 8s ease-in-out infinite;
}

.orb-2{
    width:250px; height:250px;
    top:20%; right:5%;
    background:rgba(99,210,198,.18);
    animation:orbFloat 10s ease-in-out infinite reverse;
}

.orb-3{
    width:200px; height:200px;
    bottom:-80px; left:40%;
    background:rgba(6,182,212,.15);
    animation:orbFloat 12s ease-in-out infinite 2s;
}

@keyframes orbFloat{
    0%, 100%{ transform:translate(0, 0) scale(1); }
    33%{ transform:translate(30px, -20px) scale(1.05); }
    66%{ transform:translate(-20px, 15px) scale(.95); }
}

.hero-content{
    position:relative;
    z-index:2;
}

.hero-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 18px;
    margin-bottom:20px;
    border-radius:999px;
    font-size:.78rem;
    font-weight:700;
    letter-spacing:1.5px;
    text-transform:uppercase;
    color:var(--ui-accent);
    background:rgba(45,212,191,.10);
    border:1px solid rgba(45,212,191,.2);
}

.hero-title{
    color:white;
    font-size:3.2rem;
    font-weight:900;
    letter-spacing:-1px;
    margin:0 0 12px;
    line-height:1.1;
    background:linear-gradient(135deg, #ffffff 30%, #94a3b8 100%);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}

.hero-subtitle{
    color:rgba(255,255,255,.5);
    font-size:1.1rem;
    max-width:500px;
    margin:0 auto 36px;
    line-height:1.6;
}

.hero-stats{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:24px;
    flex-wrap:wrap;
}

.hero-stat{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:4px;
}

.stat-number{
    font-size:1.5rem;
    font-weight:800;
    color:white;
    line-height:1;
}

.stat-label{
    font-size:.72rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:1px;
    color:rgba(255,255,255,.4);
}

.hero-stat-divider{
    width:1px;
    height:32px;
    background:rgba(255,255,255,.1);
}

/* FILTER PILLS */
.team-filters{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:40px;
    padding:8px 12px;
    border-radius:18px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
}

.team-filters::-webkit-scrollbar{ display:none; }

.filter-pill{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:10px 18px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.55);
    font-size:.82rem;
    font-weight:600;
    white-space:nowrap;
    cursor:pointer;
    transition:all .25s ease;
}

.filter-pill:hover{
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.85);
    border-color:rgba(255,255,255,.14);
}

.filter-pill.active{
    background:rgba(45,212,191,.15);
    color:var(--ui-accent);
    border-color:rgba(45,212,191,.3);
}

.pill-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:22px;
    height:22px;
    padding:0 6px;
    border-radius:8px;
    font-size:.7rem;
    font-weight:700;
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.5);
}

.filter-pill.active .pill-count{
    background:rgba(45,212,191,.2);
    color:var(--ui-accent);
}

/* ROLE SECTION */
.role-section{
    margin-bottom:48px;
}

.role-header{
    display:flex;
    align-items:center;
    gap:16px;
    margin-bottom:28px;
    padding:18px 24px;
    border-radius:20px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
    border-left:3px solid var(--role-color, rgba(255,255,255,.15));
}

.role-icon-wrap{
    display:flex;
    align-items:center;
    justify-content:center;
    width:44px;
    height:44px;
    border-radius:14px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08);
    color:var(--role-color, white);
    font-size:1.1rem;
    flex-shrink:0;
}

.role-info{
    flex:1;
    min-width:0;
}

.role-title{
    margin:0;
    font-size:1.2rem;
    font-weight:800;
    color:white;
    line-height:1.2;
}

.role-desc{
    margin:4px 0 0;
    font-size:.82rem;
    color:rgba(255,255,255,.4);
    line-height:1.4;
}

.role-count-badge{
    padding:6px 14px;
    border-radius:10px;
    font-size:.75rem;
    font-weight:700;
    color:var(--role-color, white);
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08);
    white-space:nowrap;
    flex-shrink:0;
}

/* TEAM CARD */
.team-card{
    position:relative;
    overflow:hidden;
    height:100%;
    border-radius:22px;
    background:
        linear-gradient(160deg, rgba(255,255,255,.05) 0%, rgba(255,255,255,.015) 100%);
    border:1px solid rgba(255,255,255,.06);
    backdrop-filter:blur(16px);
    transition:all .4s cubic-bezier(.25,.46,.45,.94);
    box-shadow:0 10px 30px rgba(0,0,0,.25);
    opacity:0;
    transform:translateY(24px);
}

.team-card.revealed{
    opacity:1;
    transform:translateY(0);
}

.team-card:hover{
    transform:translateY(-8px);
    border-color:rgba(255,255,255,.12);
    box-shadow:
        0 20px 50px rgba(0,0,0,.4),
        0 0 40px color-mix(in srgb, var(--card-color) 10%, transparent);
}

.team-card--leader{
    border-color:rgba(255,255,255,.1);
    background:
        linear-gradient(160deg, rgba(255,255,255,.07) 0%, rgba(255,255,255,.02) 100%);
    box-shadow:
        0 15px 40px rgba(0,0,0,.4),
        inset 0 1px 0 rgba(255,255,255,.08);
}

.team-card--leader:hover{
    transform:translateY(-10px) scale(1.01);
}

.card-accent{
    height:3px;
    background:linear-gradient(90deg, var(--card-color), transparent 80%);
    opacity:.6;
}

.card-glow-blob{
    position:absolute;
    top:-60px;
    right:-60px;
    width:140px;
    height:140px;
    border-radius:50%;
    background:var(--card-color);
    opacity:.06;
    filter:blur(40px);
    pointer-events:none;
}

.card-inner{
    position:relative;
    z-index:2;
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:32px 24px 28px;
    text-align:center;
}

/* AVATAR */
.card-avatar{
    position:relative;
    width:100px;
    height:100px;
    margin-bottom:18px;
    flex-shrink:0;
}

.card-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:50%;
    border:3px solid rgba(255,255,255,.08);
    position:relative;
    z-index:2;
    box-shadow:
        0 0 20px color-mix(in srgb, var(--card-color) 25%, transparent),
        0 8px 30px rgba(0,0,0,.5);
    transition:all .4s ease;
}

.team-card:hover .card-avatar img{
    transform:scale(1.06);
    border-color:color-mix(in srgb, var(--card-color) 30%, transparent);
}

.avatar-ring{
    position:absolute;
    inset:-6px;
    border-radius:50%;
    border:1.5px solid var(--card-color);
    opacity:.2;
    transition:opacity .4s ease;
}

.team-card:hover .avatar-ring{
    opacity:.4;
}

.avatar-crown{
    position:absolute;
    top:-2px;
    right:-2px;
    z-index:3;
    display:flex;
    align-items:center;
    justify-content:center;
    width:28px;
    height:28px;
    border-radius:50%;
    background:linear-gradient(135deg, #f59e0b, #d97706);
    color:white;
    font-size:.7rem;
    box-shadow:0 4px 12px rgba(245,158,11,.35);
}

/* NAME */
.card-name{
    display:block;
    margin-bottom:6px;
    font-size:1.1rem;
    font-weight:800;
    color:white;
    text-decoration:none;
    transition:color .25s ease;
}

.card-name:hover{
    color:var(--card-color);
}

/* ROLE TAG */
.card-role-tag{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 14px;
    margin-bottom:20px;
    border-radius:999px;
    font-size:.72rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.8px;
    color:var(--card-color);
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.08);
}

/* ACTIONS */
.card-actions{
    display:flex;
    gap:10px;
    width:100%;
}

.card-btn{
    flex:1;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    padding:10px 14px;
    border-radius:12px;
    font-size:.8rem;
    font-weight:700;
    text-decoration:none;
    transition:all .25s ease;
}

.card-btn--primary{
    background:linear-gradient(135deg, #63d2c6, #2dd4bf);
    color:#04211e;
    border:1px solid rgba(45,212,191,.4);
    box-shadow:0 6px 20px rgba(45,212,191,.25);
}

.card-btn--primary:hover{
    transform:translateY(-2px);
    color:#04211e;
    box-shadow:0 10px 30px rgba(45,212,191,.35);
}

.card-btn--ghost{
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.6);
    border:1px solid rgba(255,255,255,.08);
}

.card-btn--ghost:hover{
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.9);
    transform:translateY(-2px);
}

/* SCROLL REVEAL */
.role-header{
    opacity:0;
    transform:translateY(16px);
    transition:all .6s cubic-bezier(.25,.46,.45,.94);
}

.role-header.revealed{
    opacity:1;
    transform:translateY(0);
}

.hero-content{
    opacity:0;
    transform:translateY(20px);
    transition:all .7s cubic-bezier(.25,.46,.45,.94);
}

.hero-content.revealed{
    opacity:1;
    transform:translateY(0);
}

.team-card:nth-child(1){ transition-delay:.05s; }
.team-card:nth-child(2){ transition-delay:.1s; }
.team-card:nth-child(3){ transition-delay:.15s; }
.team-card:nth-child(4){ transition-delay:.2s; }
.team-card:nth-child(5){ transition-delay:.25s; }
.team-card:nth-child(6){ transition-delay:.3s; }
.team-card:nth-child(7){ transition-delay:.35s; }
.team-card:nth-child(8){ transition-delay:.4s; }

/* RESPONSIVE */
@media(max-width:768px){
    .team-hero{
        padding:40px 20px 36px;
        border-radius:22px;
    }

    .hero-title{
        font-size:2.2rem;
    }

    .hero-subtitle{
        font-size:.95rem;
    }

    .hero-stats{
        gap:16px;
    }

    .stat-number{
        font-size:1.2rem;
    }

    .role-header{
        padding:14px 16px;
        gap:12px;
        border-radius:16px;
    }

    .role-icon-wrap{
        width:38px;
        height:38px;
        font-size:.95rem;
    }

    .role-title{
        font-size:1rem;
    }

    .role-desc{
        display:none;
    }

    .card-inner{
        padding:26px 18px 24px;
    }

    .card-avatar{
        width:84px;
        height:84px;
    }

    .card-name{
        font-size:1rem;
    }

    .card-actions{
        flex-direction:column;
        gap:8px;
    }

    .team-filters{
        padding:6px 8px;
        gap:8px;
        margin-bottom:28px;
    }

    .filter-pill{
        padding:8px 14px;
        font-size:.78rem;
    }
}

@media(max-width:480px){
    .hero-title{
        font-size:1.8rem;
    }

    .hero-stats{
        gap:12px;
    }

    .hero-stat-divider{
        display:none;
    }

    .stat-number{
        font-size:1rem;
    }

    .stat-label{
        font-size:.65rem;
    }
}

</style>

@endsection
