@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 torrent-log-details-page">

    <div class="log-details-header mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="log-kicker">
                    <i class="bi bi-journal-text me-1"></i> Torrent Logs
                </div>
                <h1 class="log-details-title mb-1">
                    <i class="bi bi-file-earmark-text me-2"></i>Log Details
                </h1>
                <div class="log-details-subtitle">
                    Detailed information about this torrent activity record.
                </div>
            </div>

            <a href="{{ route('admin.torrent_logs.index') }}" class="btn log-back-btn">
                <i class="bi bi-arrow-left me-1"></i> Back to Logs
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- LOG INFORMATION --}}
        <div class="col-12 col-xl-4">
            <div class="log-info-card h-100">
                <div class="log-card-header">
                    <div class="log-card-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h2 class="log-card-title">Log Information</h2>
                        <div class="log-card-subtitle">Basic record details</div>
                    </div>
                </div>

                <div class="log-info-list">

                    <div class="log-info-item">
                        <span class="log-info-label">
                            <i class="bi bi-hash"></i> ID
                        </span>
                        <span class="log-info-value">#{{ $log->id }}</span>
                    </div>

                    <div class="log-info-item">
                        <span class="log-info-label">
                            <i class="bi bi-person"></i> User
                        </span>
                        <span class="log-info-value">
                            {{ $log->user->name ?? 'Unknown User' }}
                        </span>
                    </div>

                    <div class="log-info-item">
                        <span class="log-info-label">
                            <i class="bi bi-cloud-download"></i> Torrent
                        </span>
                        <span class="log-info-value">
                            {{ $log->torrent->name ?? 'Deleted Torrent' }}
                        </span>
                    </div>

                    <div class="log-info-item">
                        <span class="log-info-label">
                            <i class="bi bi-lightning-charge"></i> Action
                        </span>
                        <span>
                            <span class="log-action-badge action-{{ $log->action }}">
                                @php
                                    $actionIcon = match ($log->action) {
                                        'uploaded' => 'bi-upload',
                                        'edited' => 'bi-pencil-square',
                                        'deleted' => 'bi-trash',
                                        'restored' => 'bi-arrow-counterclockwise',
                                        'force_deleted' => 'bi-x-octagon',
                                        default => 'bi-activity',
                                    };
                                @endphp
                                <i class="bi {{ $actionIcon }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </span>
                    </div>

                    <div class="log-info-item">
                        <span class="log-info-label">
                            <i class="bi bi-calendar3"></i> Date
                        </span>
                        <span class="log-info-value text-end">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </span>
                    </div>

                </div>
            </div>
        </div>

        {{-- CHANGES --}}
        <div class="col-12 col-xl-8">
            <div class="log-changes-card">
                <div class="log-card-header">
                    <div class="log-card-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div>
                        <h2 class="log-card-title">Changes</h2>
                        <div class="log-card-subtitle">Before and after values recorded for this action</div>
                    </div>
                </div>

                @php
                    $changes = [];

                    if (!empty($log->description)) {
                        $parts = explode(',', $log->description);

                        foreach ($parts as $part) {
                            $part = trim($part);

                            if (!str_contains($part, ':') || !str_contains($part, '→')) {
                                continue;
                            }

                            $fieldSplit = explode(':', $part, 2);

                            if (!isset($fieldSplit[0], $fieldSplit[1])) {
                                continue;
                            }

                            $valueSplit = explode('→', $fieldSplit[1], 2);

                            if (!isset($valueSplit[0], $valueSplit[1])) {
                                continue;
                            }

                            $changes[] = [
                                'field' => trim($fieldSplit[0]),
                                'old'   => trim($valueSplit[0], " '"),
                                'new'   => trim($valueSplit[1], " '"),
                            ];
                        }
                    }
                @endphp

                @if(count($changes))
                    <div class="changes-list">
                        @foreach($changes as $change)
                            @php
                                $old = $change['old'] === '1'
                                    ? 'Yes'
                                    : ($change['old'] === '0' ? 'No' : $change['old']);

                                $new = $change['new'] === '1'
                                    ? 'Yes'
                                    : ($change['new'] === '0' ? 'No' : $change['new']);

                                $field = str_replace('_', ' ', $change['field']);
                            @endphp

                            <div class="change-item">

                                <div class="change-field">
                                    <i class="bi bi-pencil-square me-1"></i>
                                    {{ $field }}
                                </div>

                                <div class="change-values">

                                    <div class="change-value-box before-value">
                                        <div class="change-label">
                                            <span class="change-dot"></span>
                                            Before
                                        </div>

                                        <div class="change-value">
                                            {!! convertCustomTagsToHtml($old) !!}
                                        </div>
                                    </div>

                                    <div class="change-arrow" aria-hidden="true">
                                        <i class="bi bi-arrow-right"></i>
                                    </div>

                                    <div class="change-value-box after-value">
                                        <div class="change-label">
                                            <span class="change-dot"></span>
                                            After
                                        </div>

                                        <div class="change-value">
                                            {!! convertCustomTagsToHtml($new) !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-changes">
                        <div class="no-changes-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <div class="no-changes-title">No parsed changes</div>
                            <div class="no-changes-text">
                                {{ $log->description ?? 'No description provided' }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<style>
    .torrent-log-details-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .log-details-header,
    .log-info-card,
    .log-changes-card {
        background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
        border: 1px solid var(--ui-border, rgba(255,255,255,.08));
        box-shadow: 0 8px 24px rgba(0,0,0,.18);
    }

    .log-details-header {
        border-radius: .75rem;
        padding: 1rem 1.15rem;
        position: relative;
        overflow: hidden;
    }

    .log-details-header::before,
    .log-info-card::before,
    .log-changes-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--ui-accent, #20c997);
        opacity: .85;
    }

    .log-details-header {
        position: relative;
    }

    .log-kicker {
        color: var(--ui-accent, #20c997);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: .2rem;
    }

    .log-details-title {
        color: #f3f8fb;
        font-size: 21px;
        font-weight: 700;
        line-height: 1.25;
    }

    .log-details-subtitle {
        color: #91a4b5;
        font-size: 12px;
    }

    .log-back-btn {
        color: #d8e6ed;
        background: rgba(255,255,255,.035);
        border: 1px solid var(--ui-border, rgba(255,255,255,.08));
        border-radius: .5rem;
        font-size: 13px;
        font-weight: 600;
        padding: .45rem .7rem;
        transition: .18s ease;
    }

    .log-back-btn:hover,
    .log-back-btn:focus {
        color: #fff;
        border-color: rgba(32,201,151,.45);
        background: rgba(32,201,151,.08);
        box-shadow: 0 0 0 .15rem rgba(32,201,151,.07);
    }

    .log-info-card,
    .log-changes-card {
        position: relative;
        border-radius: .7rem;
        overflow: hidden;
    }

    .log-card-header {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,.07);
        background: rgba(255,255,255,.018);
    }

    .log-card-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        color: var(--ui-accent, #20c997);
        background: rgba(32,201,151,.08);
        border: 1px solid rgba(32,201,151,.18);
        font-size: 15px;
    }

    .log-card-title {
        color: #edf5f8;
        font-size: 15px;
        font-weight: 700;
        margin: 0;
    }

    .log-card-subtitle {
        color: #8296a8;
        font-size: 11px;
        margin-top: 2px;
    }

    .log-info-list {
        padding: .25rem 1rem .55rem;
    }

    .log-info-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .72rem 0;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .log-info-item:last-child {
        border-bottom: 0;
    }

    .log-info-label {
        color: #8fa3b3;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .log-info-label i {
        width: 18px;
        color: var(--ui-accent, #20c997);
        margin-right: 2px;
    }

    .log-info-value {
        color: #e4edf2;
        font-size: 13px;
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }

    .log-action-badge {
        display: inline-flex;
        align-items: center;
        padding: .3rem .55rem;
        border-radius: .4rem;
        border: 1px solid rgba(255,255,255,.09);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .action-uploaded {
        color: #70e0a9;
        background: rgba(25,135,84,.12);
        border-color: rgba(25,135,84,.28);
    }

    .action-edited {
        color: #f0ca72;
        background: rgba(255,193,7,.09);
        border-color: rgba(255,193,7,.24);
    }

    .action-deleted,
    .action-force_deleted {
        color: #ff8e98;
        background: rgba(220,53,69,.10);
        border-color: rgba(220,53,69,.25);
    }

    .action-restored {
        color: #75c9f3;
        background: rgba(13,202,240,.08);
        border-color: rgba(13,202,240,.22);
    }

    .changes-list {
        padding: 1rem;
    }

    .change-item {
        background: rgba(8,15,28,.28);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: .6rem;
        padding: .8rem;
        margin-bottom: .7rem;
    }

    .change-item:last-child {
        margin-bottom: 0;
    }

    .change-field {
        color: #b8c9d4;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .055em;
        text-transform: uppercase;
        padding-bottom: .6rem;
        border-bottom: 1px solid rgba(255,255,255,.055);
        word-break: break-word;
    }

    .change-field i {
        color: var(--ui-accent, #20c997);
    }

    .change-values {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 32px minmax(0, 1fr);
        gap: .55rem;
        align-items: center;
        padding-top: .65rem;
    }

    .change-value-box {
        min-width: 0;
        background: rgba(255,255,255,.025);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: .5rem;
        padding: .6rem .65rem;
    }

    .change-label {
        color: #8397a7;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }

    .change-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-right: .25rem;
        vertical-align: 1px;
        background: #7b8d9b;
    }

    .after-value .change-dot {
        background: var(--ui-accent, #20c997);
    }

    .change-value {
        color: #dbe6ec;
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .change-value:empty::after {
        content: "Empty";
        color: #647887;
        font-style: italic;
    }

    .change-arrow {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ui-accent, #20c997);
        background: rgba(32,201,151,.07);
        border: 1px solid rgba(32,201,151,.15);
        border-radius: 50%;
        font-size: 13px;
    }

    .no-changes {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        margin: 1rem;
        padding: .85rem;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: .55rem;
        background: rgba(255,255,255,.025);
    }

    .no-changes-icon {
        color: #8397a7;
        font-size: 17px;
        line-height: 1;
        padding-top: 1px;
    }

    .no-changes-title {
        color: #d8e3e9;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: .15rem;
    }

    .no-changes-text {
        color: #8296a6;
        font-size: 12px;
        line-height: 1.5;
        word-break: break-word;
    }

    @media (max-width: 767.98px) {
        .torrent-log-details-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .log-details-header {
            padding: .85rem;
        }

        .log-details-title {
            font-size: 18px;
        }

        .log-back-btn {
            width: 100%;
            text-align: center;
        }

        .log-info-item {
            align-items: flex-start;
        }

        .log-info-value {
            max-width: 58%;
        }

        .change-values {
            grid-template-columns: 1fr;
        }

        .change-arrow {
            margin: 0 auto;
            transform: rotate(90deg);
        }
    }
</style>
@endsection
