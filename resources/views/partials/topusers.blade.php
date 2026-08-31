<div class="container mt-4">

    <div class="leaderboard-wrapper">

        {{-- HEADER --}}
        <div class="leaderboard-header">

            <div class="leaderboard-title-wrap">

                <div class="leaderboard-icon">

                    <i class="bi bi-bar-chart-line-fill"></i>

                </div>

                <div>

                    <h4 class="leaderboard-title">

                        Community Leaderboard

                    </h4>

                    <div class="leaderboard-subtitle">

                        Top members by upload and download activity

                    </div>

                </div>

            </div>

        </div>

        {{-- TABS --}}
        <ul class="nav leaderboard-tabs px-3 pt-2"
            id="leaderboardTabs"
            role="tablist">

            <li class="nav-item">

                <button class="nav-link active"
                        id="uploaders-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#top-uploaders"
                        type="button"
                        role="tab">

                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>

                    Top Uploaders

                </button>

            </li>

            <li class="nav-item">

                <button class="nav-link"
                        id="downloaders-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#top-downloaders"
                        type="button"
                        role="tab">

                    <i class="bi bi-cloud-arrow-down-fill me-2"></i>

                    Top Downloaders

                </button>

            </li>

        </ul>

        {{-- CONTENT --}}
        <div class="tab-content px-3 pb-3">

            {{-- UPLOADERS --}}
            <div class="tab-pane fade show active"
                 id="top-uploaders"
                 role="tabpanel">

                <div class="table-responsive mt-2">

                    <table class="table leaderboard-table align-middle mb-0">

                        <tbody>

                            @foreach($topUploaders as $index => $user)

                                <tr class="leaderboard-row">

                                    <td class="rank">

                                        @if($index < 3)

                                            <div class="rank-badge trophy-rank trophy-{{ $index + 1 }}">

                                                <i class="bi bi-trophy-fill"></i>

                                            </div>

                                        @else

                                            <div class="rank-badge">

                                                #{{ $index + 1 }}

                                            </div>

                                        @endif

                                    </td>

                                    <td class="user-name">

                                        <div class="leader-user-wrap">

                                            <div class="leader-avatar">

                                                {{ strtoupper(substr($user->name, 0, 1)) }}

                                            </div>

                                            <span>

                                                {{ $user->name }}

                                            </span>

                                        </div>

                                    </td>

                                    <td class="value text-end">

                                        <i class="bi bi-arrow-up-circle-fill me-1 text-success"></i>

                                        {{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

            {{-- DOWNLOADERS --}}
            <div class="tab-pane fade"
                 id="top-downloaders"
                 role="tabpanel">

                <div class="table-responsive mt-2">

                    <table class="table leaderboard-table align-middle mb-0">

                        <tbody>

                            @foreach($topDownloaders as $index => $user)

                                <tr class="leaderboard-row">

                                    <td class="rank">

                                        @if($index < 3)

                                            <div class="rank-badge trophy-rank trophy-{{ $index + 1 }}">

                                                <i class="bi bi-trophy-fill"></i>

                                            </div>

                                        @else

                                            <div class="rank-badge">

                                                #{{ $index + 1 }}

                                            </div>

                                        @endif

                                    </td>

                                    <td class="user-name">

                                        <div class="leader-user-wrap">

                                            <div class="leader-avatar">

                                                {{ strtoupper(substr($user->name, 0, 1)) }}

                                            </div>

                                            <span>

                                                {{ $user->name }}

                                            </span>

                                        </div>

                                    </td>

                                    <td class="value text-end">

                                        <i class="bi bi-arrow-down-circle-fill me-1 text-info"></i>

                                        {{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<style>

/* =========================================
   WRAPPER
========================================= */

.leaderboard-wrapper{

    position:relative;

    overflow:hidden;

    border-radius:24px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(16px);

    box-shadow:
        0 14px 40px rgba(0,0,0,.28);
}

/* =========================================
   HEADER
========================================= */

.leaderboard-header{

    padding:22px 24px 14px;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.leaderboard-title-wrap{

    display:flex;

    align-items:center;

    gap:14px;
}

.leaderboard-icon{

    width:48px;
    height:48px;

    border-radius:16px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #7c3aed
        );

    color:white;

    font-size:1.15rem;

    box-shadow:
        0 10px 24px rgba(99,102,241,.35);
}

.leaderboard-title{

    color:white;

    margin:0;

    font-size:1.1rem;

    font-weight:800;
}

.leaderboard-subtitle{

    color:rgba(255,255,255,.55);

    font-size:.88rem;

    margin-top:2px;
}

/* =========================================
   TABS
========================================= */

.leaderboard-tabs{

    border:none;

    gap:10px;
}

.leaderboard-tabs .nav-link{

    border:none;

    border-radius:14px;

    padding:10px 16px;

    background:
        rgba(255,255,255,.05);

    color:#cbd5e1;

    font-size:1rem;

    font-weight:700;

    transition:.2s ease;
}

.leaderboard-tabs .nav-link:hover{

    background:
        rgba(255,255,255,.08);

    color:white;
}

.leaderboard-tabs .nav-link.active{

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #7c3aed
        );

    color:white;

    box-shadow:
        0 10px 22px rgba(99,102,241,.28);
}

/* =========================================
   TABLE
========================================= */

.leaderboard-table{

    color:white;
}

.leaderboard-row{

    transition:.2s ease;

    border-bottom:
        1px solid rgba(255,255,255,.04);
}

.leaderboard-row:last-child{

    border-bottom:none;
}

.leaderboard-row:hover{

    background:
        rgba(255,255,255,.04);
}

/* =========================================
   RANK
========================================= */

.rank{

    width:72px;

    text-align:center;
}

.rank-badge{

    width:40px;
    height:40px;

    margin:auto;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        rgba(255,255,255,.06);

    color:#e2e8f0;

    font-size:1rem;

    font-weight:700;
}

.trophy-rank{

    color:white;
}

.trophy-1{

    background:
        linear-gradient(
            135deg,
            #facc15,
            #f59e0b
        );

    box-shadow:
        0 6px 18px rgba(250,204,21,.3);
}

.trophy-2{

    background:
        linear-gradient(
            135deg,
            #cbd5e1,
            #94a3b8
        );

    box-shadow:
        0 6px 18px rgba(203,213,225,.2);
}

.trophy-3{

    background:
        linear-gradient(
            135deg,
            #d97706,
            #92400e
        );

    box-shadow:
        0 6px 18px rgba(217,119,6,.2);
}

/* =========================================
   USER
========================================= */

.user-name{

    font-size:1rem;

    font-weight:700;
}

.leader-user-wrap{

    display:flex;

    align-items:center;

    gap:12px;
}

.leader-avatar{

    width:38px;
    height:38px;

    border-radius:50%;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            rgba(79,70,229,.9),
            rgba(124,58,237,.9)
        );

    color:white;

    font-size:.95rem;

    font-weight:800;

    box-shadow:
        0 6px 16px rgba(99,102,241,.25);
}

/* =========================================
   VALUE
========================================= */

.value{

    color:#c4b5fd;

    font-size:1rem;

    font-weight:800;

    white-space:nowrap;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .leaderboard-header{

        padding:18px 18px 12px;
    }

    .leaderboard-tabs{

        gap:8px;
    }

    .leaderboard-tabs .nav-link{

        width:100%;

        justify-content:center;
    }

    .leader-user-wrap{

        gap:10px;
    }

    .leader-avatar{

        width:34px;
        height:34px;
    }

    .value{

        font-size:.92rem;
    }
}

</style>