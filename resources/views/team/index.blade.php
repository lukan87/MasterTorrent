@extends('layouts.app')

@section('content')

<div class="container-fluid glass mt-3 py-5 staff-page">

    {{-- =========================================
        HEADER
    ========================================= --}}
    <div class="staff-hero mb-5">

        <div class="hero-glow"></div>

        <div class="position-relative">

            <div class="staff-kicker">

                LASTFILES COMMUNITY

            </div>

            <h1 class="staff-title">

                Staff Team

            </h1>

            <p class="staff-subtitle">

                Meet the people keeping the tracker running smoothly

            </p>

        </div>

    </div>

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
    @endphp

    {{-- =========================================
        ROLE SECTIONS
    ========================================= --}}
    @foreach ($orderedRoles as $classId)

        @php
            $roleName = $roles[$classId] ?? null;
            $roleMembers = $staff->where('user_class', $classId);
        @endphp

        @if ($roleName && $roleMembers->isNotEmpty())

            <div class="staff-role-section mb-5">

                {{-- ROLE HEADER --}}
                <div class="role-header">

                    <div class="role-line"></div>

                    <div class="role-badge">

                        {{ $roleName }}

                    </div>

                </div>

                <div class="row g-4">

                    @foreach ($roleMembers as $member)

                        @php
                            $classColor = App\Models\UserClass::getClassColor($member->user_class);

                            $leaderClasses = [
                                App\Models\UserClass::OWNER,
                                App\Models\UserClass::ADMIN
                            ];

                            $isLeader = in_array($member->user_class, $leaderClasses);

                            $pulseClass = $isLeader ? 'pulse' : '';
                        @endphp

                        <div class="{{ $isLeader ? 'col-md-6 col-xl-4' : 'col-md-6 col-lg-4 col-xl-3' }}">

                            <div class="staff-card {{ $isLeader ? 'leader-card' : '' }}">

                                {{-- GLOW --}}
                                <div class="card-glow"
                                     style="background: {{ $classColor }}">
                                </div>

                                {{-- STRIPE --}}
                                <div class="role-stripe {{ $pulseClass }}"
                                     style="background: {{ $classColor }};
                                            --pulse-color: {{ $classColor }}">
                                </div>

                                <div class="staff-body">

                                    {{-- LEADER CROWN --}}
                                    @if($isLeader)

                                        <div class="leader-crown">

                                            👑

                                        </div>

                                    @endif

                                    {{-- AVATAR --}}
                                    <div class="staff-avatar-wrapper">

                                        <img
                                            src="{{ $member->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                                            class="staff-avatar"
                                            style="--avatar-color: {{ $classColor }}"
                                            alt="{{ $member->name }}"
                                        >

                                        <div class="avatar-ring"
                                             style="border-color: {{ $classColor }}">
                                        </div>

                                    </div>

                                    {{-- INFO --}}
                                    <div class="staff-info">

                                        <h5 class="staff-name">

                                            <a href="{{ route('profile.show', ['id'=>$member->id,'name'=>$member->name]) }}"
                                               style="color: {{ $classColor }}">

                                                {{ $member->name }}

                                            </a>

                                        </h5>

                                        <div class="staff-role-text">

                                            {{ $roleName }}

                                        </div>

                                    </div>

                                    {{-- ACTIONS --}}
                                    <div class="staff-actions">

                                        <a href="{{ route('messages.create',['receiver_id'=>$member->id]) }}"
                                           class="staff-btn">

                                            <i class="bi bi-envelope-fill me-2"></i>

                                            Message

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

<style>

/* =========================================
   BACKGROUND
========================================= */

body{

    background:
        radial-gradient(
            circle at top,
            #172033,
            #0f172a 45%,
            #020617
        );

    min-height:100vh;
}

/* =========================================
   PAGE
========================================= */

.staff-page{

    position:relative;

    z-index:1;
}

/* =========================================
   HERO
========================================= */

.staff-hero{

    position:relative;

    overflow:hidden;

    padding:50px 40px;

    border-radius:32px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.06),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(18px);

    box-shadow:
        0 25px 60px rgba(0,0,0,.35);
}

.hero-glow{

    position:absolute;

    top:-120px;
    right:-120px;

    width:320px;
    height:320px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.35),
            transparent 70%
        );
}

.staff-kicker{

    color:#60a5fa;

    font-size:.8rem;

    font-weight:800;

    letter-spacing:2px;

    margin-bottom:10px;
}

.staff-title{

    color:white;

    font-size:3rem;

    font-weight:900;

    margin:0;
}

.staff-subtitle{

    color:rgba(255,255,255,.6);

    margin-top:10px;

    font-size:1rem;
}

/* =========================================
   ROLE HEADER
========================================= */

.role-header{

    display:flex;

    align-items:center;

    gap:16px;

    margin-bottom:24px;
}

.role-line{

    flex:1;

    height:1px;

    background:
        linear-gradient(
            90deg,
            rgba(255,255,255,.12),
            transparent
        );
}

.role-badge{

    padding:10px 20px;

    border-radius:999px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid rgba(255,255,255,.08);

    color:white;

    font-size:.95rem;

    font-weight:800;

    letter-spacing:.5px;

    white-space:nowrap;
}

/* =========================================
   STAFF CARD
========================================= */

.staff-card{

    position:relative;

    overflow:hidden;

    height:100%;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:28px;

    backdrop-filter:blur(14px);

    transition:.35s ease;

    box-shadow:
        0 15px 35px rgba(0,0,0,.28);
}

.staff-card:hover{

    transform:
        translateY(-8px);

    box-shadow:
        0 25px 50px rgba(0,0,0,.45);
}

/* =========================================
   GLOW
========================================= */

.card-glow{

    position:absolute;

    top:-80px;
    right:-80px;

    width:180px;
    height:180px;

    opacity:.12;

    border-radius:50%;

    filter:blur(30px);
}

/* =========================================
   ROLE STRIPE
========================================= */

.role-stripe{

    position:absolute;

    left:0;
    top:0;

    width:5px;
    height:100%;
}

/* =========================================
   BODY
========================================= */

.staff-body{

    position:relative;

    z-index:2;

    text-align:center;

    padding:34px 24px;
}

/* =========================================
   LEADER CARD
========================================= */

.leader-card{

    border:
        1px solid rgba(255,255,255,.12);

    box-shadow:
        0 20px 50px rgba(0,0,0,.45),
        0 0 30px rgba(255,215,0,.08);

    background:
        linear-gradient(
            145deg,
            rgba(40,40,40,.95),
            rgba(18,18,18,.95)
        );
}

.leader-card:hover{

    transform:
        translateY(-10px)
        scale(1.02);
}

/* =========================================
   CROWN
========================================= */

.leader-crown{

    position:absolute;

    top:18px;
    right:18px;

    font-size:1.4rem;

    filter:
        drop-shadow(0 0 8px gold);
}

/* =========================================
   AVATAR
========================================= */

.staff-avatar-wrapper{

    position:relative;

    width:110px;
    height:110px;

    margin:0 auto 18px;
}

.staff-avatar{

    width:100%;
    height:100%;

    object-fit:cover;

    border-radius:50%;

    border:
        4px solid rgba(255,255,255,.08);

    position:relative;

    z-index:2;

    box-shadow:
        0 0 18px var(--avatar-color),
        0 0 40px rgba(0,0,0,.55);

    transition:.35s ease;
}

.staff-card:hover .staff-avatar{

    transform:scale(1.05);
}

.avatar-ring{

    position:absolute;

    inset:-7px;

    border-radius:50%;

    border:1px solid;

    opacity:.35;
}

/* =========================================
   NAME
========================================= */

.staff-name{

    margin-bottom:6px;

    font-size:1.2rem;

    font-weight:800;
}

.staff-name a{

    text-decoration:none;
}

.staff-name a:hover{

    opacity:.85;
}

/* =========================================
   ROLE
========================================= */

.staff-role-text{

    color:rgba(255,255,255,.55);

    font-size:.82rem;

    text-transform:uppercase;

    letter-spacing:1px;

    font-weight:700;
}

/* =========================================
   BUTTON
========================================= */

.staff-actions{

    margin-top:22px;
}

.staff-btn{

    width:100%;

    display:inline-flex;

    align-items:center;
    justify-content:center;

    padding:12px 18px;

    border-radius:16px;

    text-decoration:none;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    border:none;

    color:white;

    font-weight:700;

    transition:.25s ease;

    box-shadow:
        0 10px 25px rgba(59,130,246,.28);
}

.staff-btn:hover{

    transform:translateY(-2px);

    color:white;

    box-shadow:
        0 18px 35px rgba(59,130,246,.38);
}

/* =========================================
   PULSE
========================================= */

@keyframes pulseGlow{

    0%{

        box-shadow:
            0 0 8px var(--pulse-color);
    }

    50%{

        box-shadow:
            0 0 18px var(--pulse-color);
    }

    100%{

        box-shadow:
            0 0 8px var(--pulse-color);
    }
}

.pulse{

    animation:
        pulseGlow 2s infinite ease-in-out;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .staff-hero{

        padding:34px 24px;

        border-radius:24px;
    }

    .staff-title{

        font-size:2rem;
    }

    .staff-subtitle{

        font-size:.92rem;
    }

    .staff-body{

        padding:28px 20px;
    }

    .staff-avatar-wrapper{

        width:92px;
        height:92px;
    }
}

</style>

@endsection