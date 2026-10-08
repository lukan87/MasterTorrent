@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="admin-page-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"
                     style="width: 44px; height: 44px;">
                    <i class="bi bi-send-check fs-4"></i>
                </div>

                <div>
                    <h1 class="h3 mb-0 fw-bold">Email Campaigns</h1>
                    <div class="text-body-secondary small">
                        Track queued, sent and failed campaign emails.
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.emails.index') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i>
                Email History
            </a>

            <a href="{{ route('admin.emails.create') }}"
               class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i>
                Compose Email
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('admin.emails.campaigns') }}"
                  class="row g-3 align-items-end">

                <div class="col-md-5 col-lg-4">
                    <label for="status" class="form-label fw-semibold">
                        Campaign Status
                    </label>

                    <select name="status"
                            id="status"
                            class="form-select">

                        <option value="">All campaigns</option>

                        <option value="queued"
                            @selected(request('status') === 'queued')>
                            Queued
                        </option>

                        <option value="sending"
                            @selected(request('status') === 'sending')>
                            Sending
                        </option>

                        <option value="completed"
                            @selected(request('status') === 'completed')>
                            Completed
                        </option>

                        <option value="completed_with_failures"
                            @selected(request('status') === 'completed_with_failures')>
                            Completed with failures
                        </option>

                        <option value="failed"
                            @selected(request('status') === 'failed')>
                            Failed
                        </option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>
                        Filter
                    </button>

                    @if(request()->filled('status'))
                        <a href="{{ route('admin.emails.campaigns') }}"
                           class="btn btn-outline-secondary ms-1">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Campaigns --}}
    <div class="card border-0 shadow-sm overflow-hidden">

        <div class="card-header bg-transparent border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h5 mb-0 fw-bold">
                        <i class="bi bi-megaphone me-2"></i>
                        Campaign History
                    </h2>
                </div>

                <span class="badge text-bg-secondary rounded-pill">
                    {{ number_format($campaigns->total()) }}
                    {{ Str::plural('campaign', $campaigns->total()) }}
                </span>
            </div>
        </div>

        @if($campaigns->count())

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Campaign</th>
                            <th>Audience</th>
                            <th>Status</th>
                            <th style="min-width: 190px;">Progress</th>
                            <th class="text-center">Queued</th>
                            <th class="text-center">Sent</th>
                            <th class="text-center">Failed</th>
                            <th class="text-center">Remaining</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($campaigns as $campaign)

                            @php
                                $processed = $campaign->sent_count + $campaign->failed_count;

                                $remaining = max(
                                    0,
                                    $campaign->total_recipients - $processed
                                );

                                $progress = $campaign->total_recipients > 0
                                    ? min(
                                        100,
                                        (int) round(
                                            ($processed / $campaign->total_recipients) * 100
                                        )
                                    )
                                    : 0;

                                $targetLabels = [
                                    'subscribed' => 'Subscribed',
                                    'all' => 'All Users',
                                    'user_class' => 'User Class',
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'seeders' => 'Seeders',
                                    'leechers' => 'Leechers',
                                    'hit_and_run' => 'Hit & Run',
                                    'donors' => 'Donors',
                                    'warned' => 'Warned',
                                    'disabled' => 'Disabled',
                                    'test' => 'Test',
                                ];

                                $targetLabel = $targetLabels[$campaign->target]
                                    ?? Str::headline($campaign->target);

                                $statusConfig = match($campaign->status) {
                                    'queued' => [
                                        'class' => 'text-bg-secondary',
                                        'icon' => 'bi-clock',
                                        'label' => 'Queued',
                                    ],

                                    'sending' => [
                                        'class' => 'text-bg-primary',
                                        'icon' => 'bi-send',
                                        'label' => 'Sending',
                                    ],

                                    'completed' => [
                                        'class' => 'text-bg-success',
                                        'icon' => 'bi-check-circle',
                                        'label' => 'Completed',
                                    ],

                                    'completed_with_failures' => [
                                        'class' => 'text-bg-warning',
                                        'icon' => 'bi-exclamation-triangle',
                                        'label' => 'Completed with failures',
                                    ],

                                    'failed' => [
                                        'class' => 'text-bg-danger',
                                        'icon' => 'bi-x-circle',
                                        'label' => 'Failed',
                                    ],

                                    default => [
                                        'class' => 'text-bg-secondary',
                                        'icon' => 'bi-question-circle',
                                        'label' => Str::headline($campaign->status),
                                    ],
                                };

                                $progressClass = match(true) {
                                    $campaign->status === 'completed' => 'bg-success',
                                    $campaign->status === 'completed_with_failures' => 'bg-warning',
                                    $campaign->status === 'failed' => 'bg-danger',
                                    default => 'bg-primary',
                                };
                            @endphp

                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('admin.emails.campaigns.show', $campaign) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $campaign->subject }}
                                    </a>

                                    <div class="small text-body-secondary mt-1">
                                        Campaign #{{ $campaign->id }}
                                        ·
                                        {{ number_format($campaign->total_recipients) }}
                                        {{ Str::plural('recipient', $campaign->total_recipients) }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge border text-body">
                                        {{ $targetLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $statusConfig['class'] }}">
                                        <i class="bi {{ $statusConfig['icon'] }} me-1"></i>
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ $progress }}%</span>

                                        <span class="text-body-secondary">
                                            {{ number_format($processed) }}
                                            /
                                            {{ number_format($campaign->total_recipients) }}
                                        </span>
                                    </div>

                                    <div class="progress"
                                         role="progressbar"
                                         aria-valuenow="{{ $progress }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         style="height: 7px;">

                                        <div class="progress-bar {{ $progressClass }}"
                                             style="width: {{ $progress }}%">
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="fw-semibold">
                                        {{ number_format($campaign->queued_count) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="text-success fw-semibold">
                                        {{ number_format($campaign->sent_count) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($campaign->failed_count > 0)
                                        <span class="text-danger fw-semibold">
                                            {{ number_format($campaign->failed_count) }}
                                        </span>
                                    @else
                                        <span class="text-body-secondary">0</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="fw-semibold">
                                        {{ number_format($remaining) }}
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    <div>
                                        {{ $campaign->created_at?->format('d M Y') }}
                                    </div>

                                    <div class="small text-body-secondary">
                                        {{ $campaign->created_at?->format('H:i') }}
                                    </div>
                                </td>

                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.emails.campaigns.show', $campaign) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>
                                        View
                                    </a>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($campaigns->hasPages())
                <div class="card-footer bg-transparent border-top py-3">
                    {{ $campaigns->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @else

            <div class="text-center py-5 px-3">
                <div class="mb-3">
                    <i class="bi bi-send display-4 text-body-secondary"></i>
                </div>

                <h3 class="h5 fw-bold">
                    No campaigns found
                </h3>

                <p class="text-body-secondary mb-4">
                    Your email campaigns will appear here after they are created.
                </p>

                <a href="{{ route('admin.emails.create') }}"
                   class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i>
                    Compose Email
                </a>
            </div>

        @endif
    </div>
</div>
@endsection
