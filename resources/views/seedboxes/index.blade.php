@extends('layouts.app')

@section('content')

@php
    $authUserId = auth()->id();

    $isDev = auth()->user()->user_class == \App\Models\UserClass::WEB_DEVELOPER;

    $userSeedboxes = $seedboxes->where('user_id', $authUserId);

    $otherSeedboxes = $isDev
        ? $seedboxes->where('user_id', '!=', $authUserId)
        : collect();
@endphp

<div class="seedbox-page-wrapper">

    <div class="container py-5">

        {{-- HERO --}}
        <div class="seedbox-hero mb-4">

            <div class="hero-content">

                <div class="hero-kicker">
                    STORAGE • SERVERS • AUTOMATION
                </div>

                <h1 class="hero-title">

                    <i class="bi bi-hdd-network-fill me-2"></i>

                    Seedbox Management

                </h1>

                <div class="hero-subtitle">

                    Manage your personal seedboxes and remote torrent clients.

                </div>

            </div>

            <a href="{{ route('seedboxes.create') }}"
               class="btn hero-add-btn">

                <i class="bi bi-plus-circle-fill me-2"></i>

                Add Seedbox

            </a>

        </div>

        {{-- ALERTS --}}
        @foreach (['success', 'error'] as $msg)

            @if(session($msg))

                <div class="modern-alert {{ $msg === 'success'
                    ? 'modern-alert-success'
                    : 'modern-alert-danger' }}">

                    <div class="d-flex align-items-center gap-2">

                        <i class="bi {{ $msg === 'success'
                            ? 'bi-check-circle-fill'
                            : 'bi-exclamation-triangle-fill' }}"></i>

                        {{ session($msg) }}

                    </div>

                    <button class="btn-close btn-close-white"
                            data-bs-dismiss="alert"></button>

                </div>

            @endif

        @endforeach

        {{-- USER SEEDBOXES --}}
        <div class="modern-section-card mb-4">

            <div class="section-header">

                <div>

                    <h4 class="section-title text-info">

                        <i class="bi bi-person-circle me-2"></i>

                        Your Seedboxes

                    </h4>

                    <div class="section-subtitle">

                        Your connected remote servers

                    </div>

                </div>

                <div class="section-count">

                    {{ $userSeedboxes->count() }}

                </div>

            </div>

            @if($userSeedboxes->isEmpty())

                <div class="empty-state">

                    <i class="bi bi-hdd-stack"></i>

                    <h5>No Seedboxes Added</h5>

                    <p>
                        You haven't added any seedboxes yet.
                    </p>

                </div>

            @else

                @include('seedboxes.partials.seedbox-table', [
                    'boxes' => $userSeedboxes,
                    'highlightOwner' => true
                ])

            @endif

        </div>

        {{-- OTHER SEEDBOXES --}}
        @if($isDev && $otherSeedboxes->isNotEmpty())

            <div class="modern-section-card">

                <div class="section-header">

                    <div>

                        <h4 class="section-title text-warning">

                            <i class="bi bi-people-fill me-2"></i>

                            Other Seedboxes

                        </h4>

                        <div class="section-subtitle">

                            Developer overview of all servers

                        </div>

                    </div>

                    <div class="section-count warning-count">

                        {{ $otherSeedboxes->count() }}

                    </div>

                </div>

                @include('seedboxes.partials.seedbox-table', [
                    'boxes' => $otherSeedboxes,
                    'highlightOwner' => false
                ])

            </div>

        @endif

    </div>

</div>

<style>

/* =========================================
   PAGE BACKGROUND
========================================= */

body{

    background:
        radial-gradient(
            circle at top,
            #1e293b 0%,
            #0f172a 50%,
            #020617 100%
        );

    min-height:100vh;
}

.seedbox-page-wrapper{
    position:relative;
    z-index:1;
}

/* =========================================
   HERO
========================================= */

.seedbox-hero{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    padding:30px;

    border-radius:28px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.07),
            rgba(255,255,255,.03)
        );

    backdrop-filter:blur(18px);

    border:
        1px solid rgba(255,255,255,.08);

    box-shadow:
        0 25px 60px rgba(0,0,0,.45);

    position:relative;

    overflow:hidden;
}

.seedbox-hero::before{

    content:'';

    position:absolute;

    top:-100px;
    right:-100px;

    width:260px;
    height:260px;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.28),
            transparent 70%
        );

    pointer-events:none;
}

.hero-kicker{

    color:#60a5fa;

    font-size:.75rem;

    letter-spacing:2px;

    font-weight:800;

    margin-bottom:10px;
}

.hero-title{

    color:white;

    font-size:2.4rem;

    font-weight:900;

    margin:0;
}

.hero-subtitle{

    color:rgba(255,255,255,.65);

    margin-top:8px;

    font-size:1rem;
}

/* =========================================
   BUTTON
========================================= */

.hero-add-btn{

    border:none;

    border-radius:18px;

    padding:14px 24px;

    font-weight:800;

    color:white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    box-shadow:
        0 12px 25px rgba(37,99,235,.35);

    transition:.25s ease;
}

.hero-add-btn:hover{

    transform:translateY(-3px);

    color:white;
}

/* =========================================
   SECTION CARD
========================================= */

.modern-section-card{

    padding:28px;

    border-radius:26px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.06),
            rgba(255,255,255,.03)
        );

    backdrop-filter:blur(16px);

    border:
        1px solid rgba(255,255,255,.06);

    box-shadow:
        0 20px 50px rgba(0,0,0,.35);
}

/* =========================================
   SECTION HEADER
========================================= */

.section-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    margin-bottom:24px;

    flex-wrap:wrap;
}

.section-title{

    font-weight:800;

    margin:0;
}

.section-subtitle{

    color:rgba(255,255,255,.5);

    font-size:.9rem;

    margin-top:5px;
}

.section-count{

    min-width:44px;
    height:44px;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:50%;

    background:
        rgba(59,130,246,.16);

    color:#93c5fd;

    font-weight:800;
}

.warning-count{

    background:
        rgba(245,158,11,.15);

    color:#facc15;
}

/* =========================================
   ALERTS
========================================= */

.modern-alert{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:16px 18px;

    border-radius:18px;

    margin-bottom:18px;

    color:white;

    backdrop-filter:blur(10px);
}

.modern-alert-success{

    background:
        rgba(34,197,94,.14);

    border:
        1px solid rgba(34,197,94,.25);
}

.modern-alert-danger{

    background:
        rgba(239,68,68,.14);

    border:
        1px solid rgba(239,68,68,.25);
}

/* =========================================
   EMPTY STATE
========================================= */

.empty-state{

    text-align:center;

    padding:60px 20px;
}

.empty-state i{

    font-size:3rem;

    color:#60a5fa;

    margin-bottom:18px;
}

.empty-state h5{

    color:white;

    font-weight:800;
}

.empty-state p{

    color:rgba(255,255,255,.6);
}

/* =========================================
   TABLE MODERNIZATION
========================================= */

.table{

    color:white !important;

    margin-bottom:0;
}

.table thead th{

    border:none !important;

    background:
        rgba(255,255,255,.04);

    color:#cbd5e1;

    font-size:.78rem;

    letter-spacing:1px;

    text-transform:uppercase;

    font-weight:700;

    padding:16px !important;
}

.table tbody tr{

    border-color:
        rgba(255,255,255,.05) !important;

    transition:.2s ease;
}

.table tbody tr:hover{

    background:
        rgba(255,255,255,.03);
}

.table td{

    border-color:
        rgba(255,255,255,.05) !important;

    vertical-align:middle;

    padding:18px 16px !important;
}

/* =========================================
   BUTTONS INSIDE TABLE
========================================= */

.btn{

    border-radius:12px !important;

    font-weight:700 !important;
}

.btn-primary{

    background:
        linear-gradient(135deg,#2563eb,#3b82f6);

    border:none;
}

.btn-danger{

    background:
        linear-gradient(135deg,#dc2626,#ef4444);

    border:none;
}

.btn-warning{

    background:
        linear-gradient(135deg,#f59e0b,#facc15);

    border:none;

    color:#111827 !important;
}

/* =========================================
   FORM CONTROLS
========================================= */

.form-control{

    background:
        rgba(255,255,255,.04) !important;

    border:
        1px solid rgba(255,255,255,.06) !important;

    color:white !important;
}

.form-control:focus{

    box-shadow:
        0 0 0 3px rgba(59,130,246,.2) !important;

    border-color:
        rgba(59,130,246,.45) !important;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .seedbox-hero{

        padding:24px;
    }

    .hero-title{

        font-size:1.8rem;
    }

    .modern-section-card{

        padding:20px;
    }

    .table-responsive{

        border-radius:16px;
    }
}

</style>

<script>

function togglePassword(id){

    const input = document.getElementById('password-' + id);

    const button = input.nextElementSibling;

    if(input.type === 'password'){

        input.type = 'text';

        button.textContent = 'Hide';

    }else{

        input.type = 'password';

        button.textContent = 'Show';
    }
}

</script>

@endsection