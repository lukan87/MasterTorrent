@extends('layouts.admin')

@section('admin-content')

    <div class="container-fluid user-history-page">

        {{-- =========================================================
         USER HEADER
    ========================================================== --}}
        <div class="history-header admin-page-header">
            <div>
                <div class="history-eyebrow">
                    <i class="bi bi-clock-history"></i>
                    User History
                </div>

                <h2 class="history-title">
                    {{ $user->name }}
                </h2>

                <div class="history-meta">
                    <span>
                        <i class="bi bi-person-badge"></i>
                        User ID: {{ $user->id }}
                    </span>

                    <span>
                        <i class="bi bi-envelope"></i>
                        {{ $user->email }}
                    </span>
                </div>
            </div>
        </div>


        {{-- =========================================================
         ACCOUNT DETAILS
    ========================================================== --}}
        <div class="history-card mb-3">

            <div class="history-card-header">
                <div class="history-card-title">
                    <i class="bi bi-person-circle"></i>
                    Account Details
                </div>
            </div>

            <div class="history-card-body detail-grid">

                <div class="detail-item">
                    <span>Name</span>
                    <strong>{{ $user->name }}</strong>
                </div>

                <div class="detail-item">
                    <span>Email</span>
                    <strong>{{ $user->email }}</strong>
                </div>

                <div class="detail-item">
                    <span>User ID</span>
                    <strong>#{{ $user->id }}</strong>
                </div>

            </div>

        </div>


        {{-- =========================================================
         TORRENT ACTIVITY
    ========================================================== --}}
        <div class="row g-3 mb-3">

            @foreach ([
            [
                'data' => $torrentsUploaded,
                'title' => 'Torrents Uploaded',
                'icon' => 'bi-cloud-arrow-up',
                'type' => 'uploaded',
            ],
            [
                'data' => $torrentsDownloaded,
                'title' => 'Torrents Downloaded',
                'icon' => 'bi-cloud-arrow-down',
                'type' => 'downloaded',
            ],
            [
                'data' => $seedingTorrents,
                'title' => 'Torrents Seeding',
                'icon' => 'bi-arrow-up-circle',
                'type' => 'peer',
            ],
            [
                'data' => $leechingTorrents,
                'title' => 'Torrents Leeching',
                'icon' => 'bi-arrow-down-circle',
                'type' => 'peer',
            ],
        ] as $section)
                @if ($section['data']->isNotEmpty())
                    <div class="col-12 col-md-4 col-lg-4 d-flex">

                        <div class="history-card history-section-card w-100">

                            {{-- Card Header --}}
                            <div class="history-card-header">

                                <div class="history-card-title">
                                    <i class="bi {{ $section['icon'] }}"></i>

                                    <span>
                                        {{ $section['title'] }}
                                    </span>
                                </div>

                                <span class="history-count">
                                    {{ $section['data']->total() }}
                                </span>

                            </div>


                            {{-- Scrollable List --}}
                            <div class="history-list">

                                @foreach ($section['data'] as $item)
                                    <div class="history-row">

                                        <div class="history-row-icon">
                                            <i class="bi {{ $section['icon'] }}"></i>
                                        </div>

                                        <div class="history-row-content">

                                            {{-- Uploaded --}}
                                            @if ($section['type'] === 'uploaded')
                                                <div class="history-row-title">
                                                    {{ $item->name }}
                                                </div>

                                                <div class="history-row-meta">
                                                    Torrent ID: {{ $item->id }}
                                                </div>


                                                {{-- Downloaded --}}
                                            @elseif($section['type'] === 'downloaded')
                                                <div class="history-row-title">
                                                    {{ $item->torrent->name ?? 'Unknown name' }}
                                                </div>

                                                <div class="history-row-meta">
                                                    History ID: {{ $item->id }}
                                                </div>


                                                {{-- Seeding / Leeching --}}
                                            @else
                                                <div class="history-row-title">
                                                    {{ $item->torrent->name ?? 'Unknown name' }}
                                                </div>

                                                <div class="history-row-meta">
                                                    Torrent ID:
                                                    {{ $item->torrent->id ?? 'Unknown' }}
                                                </div>
                                            @endif

                                        </div>

                                    </div>
                                @endforeach

                            </div>


                            {{-- Pagination --}}
                            @if ($section['data']->hasPages())
                                <div class="history-pagination">
                                    {{ $section['data']->links('pagination::bootstrap-5') }}
                                </div>
                            @endif

                        </div>

                    </div>
                @endif
            @endforeach

        </div>


        {{-- =========================================================
         MESSAGES SENT
    ========================================================== --}}
        @if ($messages->isNotEmpty())
            <div class="history-card mb-3">

                <div class="history-card-header">

                    <div class="history-card-title">
                        <i class="bi bi-chat-left-text"></i>
                        Messages Sent
                    </div>

                    <span class="history-count">
                        {{ $messages->total() }}
                    </span>

                </div>


                <div class="history-list messages-history-list">

                    @foreach ($messages as $message)
                        <div class="history-row align-items-start">

                            <div class="history-row-icon">
                                <i class="bi bi-envelope-paper"></i>
                            </div>

                            <div class="history-row-content">

                                <div class="history-row-meta mb-1">
                                    To:
                                    <strong>
                                        {{ $message->receiver->name ?? 'Unknown user' }}
                                    </strong>
                                </div>

                                <div class="history-message">
                                    {{ $message->body }}
                                </div>

                            </div>

                        </div>
                    @endforeach

                </div>


                @if ($messages->hasPages())
                    <div class="history-pagination">
                        {{ $messages->links('pagination::bootstrap-5') }}
                    </div>
                @endif

            </div>
        @endif

    </div>


    <style>
        /* =========================================================
       PAGE
    ========================================================= */

        .user-history-page {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.25rem .75rem 2.5rem;
            color: var(--theme-text, #e2e8f0);
        }


        /* =========================================================
       HEADER + CARDS
    ========================================================= */

        .history-header,
        .history-card {
            background:
                linear-gradient(135deg,
                    var(--theme-surface, rgba(22, 32, 51, .96)),
                    var(--theme-surface, rgba(15, 23, 42, .90)));

            border: 1px solid var(--theme-border, rgba(148, 163, 184, .15));
            border-radius: .65rem;

            box-shadow:
                0 9px 25px var(--theme-shadow, rgba(0, 0, 0, .20));
        }


        /* =========================================================
       PAGE HEADER
    ========================================================= */

        .history-header {
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
        }

        .history-eyebrow {
            display: flex;
            align-items: center;
            gap: .35rem;

            color: var(--theme-teal-text, #5eead4);

            font-size: var(--site-font-small, 13px);
            font-weight: 800;

            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .history-title {
            margin: .25rem 0 0;

            color: var(--theme-text, #f8fafc);

            font-size: 1.35rem;
            font-weight: 800;
        }

        .history-meta {
            display: flex;
            flex-wrap: wrap;

            gap: .4rem 1rem;

            margin-top: .4rem;

            color: var(--theme-muted, #94a3b8);

            font-size: var(--site-font-body, 13px);
        }

        .history-meta span {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }


        /* =========================================================
       CARD
    ========================================================= */

        .history-card {
            overflow: hidden;
        }

        .history-section-card {
            display: flex;
            flex-direction: column;

            width: 100%;
            height: 100%;

            min-width: 0;
        }


        /* =========================================================
       CARD HEADER
    ========================================================= */

        .history-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: .75rem;

            min-height: 48px;

            padding: .7rem .85rem;

            background: var(--theme-surface-alt, rgba(2, 6, 23, .24));

            border-bottom:
                1px solid var(--theme-border, rgba(148, 163, 184, .12));
        }

        .history-card-title {
            display: flex;
            align-items: center;

            gap: .4rem;

            min-width: 0;

            color: var(--theme-text, #f1f5f9);

            font-size: var(--site-font-body, 13px);
            font-weight: 800;
        }

        .history-card-title span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .history-card-title i {
            flex-shrink: 0;
            color: var(--theme-teal-text, #5eead4);
        }


        /* =========================================================
       COUNTER
    ========================================================= */

        .history-count {
            flex-shrink: 0;

            padding: .25rem .45rem;

            border-radius: .4rem;

            background: var(--theme-teal-soft, rgba(20, 184, 166, .10));

            border:
                1px solid var(--theme-teal-border, rgba(45, 212, 191, .20));

            color: var(--theme-teal-text, #99f6e4);

            font-size: var(--site-font-small, 13px);
            font-weight: 800;
        }


        /* =========================================================
       ACCOUNT DETAILS
    ========================================================= */

        .history-card-body {
            padding: .85rem;
        }

        .detail-grid {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: .6rem;
        }

        .detail-item {
            min-width: 0;

            padding: .65rem .7rem;

            background: var(--theme-surface, rgba(2, 6, 23, .25));

            border:
                1px solid var(--theme-border, rgba(148, 163, 184, .10));

            border-radius: .45rem;
        }

        .detail-item span {
            display: block;

            margin-bottom: .15rem;

            color: var(--theme-muted, #64748b);

            font-size: var(--site-font-small, 13px);
            font-weight: 800;

            text-transform: uppercase;
        }

        .detail-item strong {
            display: block;

            color: var(--theme-text, #e2e8f0);

            font-size: var(--site-font-body, 13px);

            overflow-wrap: anywhere;
        }


        /* =========================================================
       SCROLLABLE LIST
    ========================================================= */

        .history-list {
            max-height: 450px;

            overflow-y: auto;
            overflow-x: auto;

            scrollbar-width: thin;

            scrollbar-color:
                var(--theme-teal-border, rgba(94, 234, 212, .45)) var(--theme-border, rgba(15, 23, 42, .45));
        }


        /* Make the four cards use available space */
        .history-section-card .history-list {
            flex: 1;
        }


        /* =========================================================
       SCROLLBAR - CHROME / EDGE / SAFARI
    ========================================================= */

        .history-list::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        .history-list::-webkit-scrollbar-track {
            background:
                var(--theme-surface, rgba(15, 23, 42, .45));
        }

        .history-list::-webkit-scrollbar-thumb {
            background:
                var(--theme-teal-soft, rgba(94, 234, 212, .35));

            border-radius: 10px;
        }

        .history-list::-webkit-scrollbar-thumb:hover {
            background:
                var(--theme-teal-soft, rgba(94, 234, 212, .55));
        }


        /* =========================================================
       HISTORY ROW
    ========================================================= */

        .history-row {
            display: flex;
            align-items: center;

            gap: .65rem;

            width: 100%;
            min-width: 0;

            padding: .65rem .85rem;

            border-bottom:
                1px solid var(--theme-border, rgba(148, 163, 184, .09));

            transition:
                background-color .15s ease;
        }

        .history-row:last-child {
            border-bottom: 0;
        }

        .history-row:hover {
            background:
                var(--theme-surface, rgba(30, 41, 59, .30));
        }


        /* =========================================================
       ROW ICON
    ========================================================= */

        .history-row-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 31px;
            height: 31px;

            flex: 0 0 31px;

            border-radius: .4rem;

            background:
                var(--theme-teal-soft, rgba(20, 184, 166, .08));

            border:
                1px solid var(--theme-teal-border, rgba(45, 212, 191, .14));

            color: var(--theme-teal-text, #5eead4);

            font-size: .76rem;
        }


        /* =========================================================
       ROW CONTENT
    ========================================================= */

        .history-row-content {
            min-width: 0;
            flex: 1;
        }

        .history-row-title {
            color: var(--theme-text, #e2e8f0);

            font-size: var(--site-font-body, 13px);
            font-weight: 650;

            line-height: 1.35;

            white-space: normal;

            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .history-row-meta {
            margin-top: .1rem;

            color: var(--theme-muted, #64748b);

            font-size: var(--site-font-small, 13px);
        }


        /* =========================================================
       MESSAGES
    ========================================================= */

        .history-message {
            color: var(--theme-text, #cbd5e1);

            font-size: var(--site-font-body, 13px);
            line-height: 1.45;

            white-space: pre-wrap;

            overflow-wrap: anywhere;
        }


        /* =========================================================
       PAGINATION
    ========================================================= */

        .history-pagination {
            padding: .65rem .85rem;

            border-top:
                1px solid var(--theme-border, rgba(148, 163, 184, .10));

            background:
                var(--theme-surface-alt, rgba(2, 6, 23, .14));
        }

        .history-pagination .pagination {
            margin: 0;

            gap: .15rem;

            flex-wrap: wrap;
        }

        .history-pagination .page-link {
            padding: .3rem .5rem;

            background:
                var(--theme-surface, rgba(30, 41, 59, .60));

            border-color:
                var(--theme-border, rgba(148, 163, 184, .14));

            color: var(--theme-muted, #94a3b8);

            font-size: var(--site-font-small, 13px);

            border-radius: .35rem !important;
        }

        .history-pagination .page-item.active .page-link {
            background: var(--theme-teal-soft, #0f766e);
            border-color: var(--theme-teal-border, #14b8a6);
            color:  var(--theme-text, #fff);
        }

        .history-pagination .page-item.disabled .page-link {
            background:
                var(--theme-surface, rgba(15, 23, 42, .35));

            color: var(--theme-muted, #475569);
        }


        /* =========================================================
       LARGE DESKTOP
       4 CARDS PER ROW
    ========================================================= */

        @media (min-width: 992px) {

            .history-section-card .history-list {
                max-height: 450px;
            }

        }


        /* =========================================================
       TABLET
       2 CARDS PER ROW
    ========================================================= */

        @media (max-width: 991.98px) {

            .user-history-page {
                padding-left: .5rem;
                padding-right: .5rem;
            }

            .history-section-card .history-list {
                max-height: 400px;
            }

        }


        /* =========================================================
       MOBILE
       1 CARD PER ROW
    ========================================================= */

        @media (max-width: 767.98px) {

            .user-history-page {
                padding:
                    .75rem .25rem 2rem;
            }

            .history-header {
                padding: .85rem;
            }

            .history-title {
                font-size: 1.15rem;
            }

            .history-meta {
                flex-direction: column;

                gap: .25rem;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .history-card-header {
                padding: .65rem .7rem;
            }

            .history-row {
                padding: .6rem .7rem;
            }

            .history-section-card .history-list,
            .messages-history-list {
                max-height: 350px;
            }

        }


        /* =========================================================
       VERY SMALL MOBILE
    ========================================================= */

        @media (max-width: 420px) {

            .history-card-title {
                font-size: var(--site-font-body, 13px);
            }

            .history-row-title {
                font-size: var(--site-font-body, 13px);
            }

            .history-row-icon {
                width: 28px;
                height: 28px;

                flex-basis: 28px;
            }

        }
    </style>

@endsection
