@extends('layouts.admin')

@section('admin-content')

<div class="container-fluid py-4">

    @php
        /*
        |--------------------------------------------------------------------------
        | Current campaign counts
        |--------------------------------------------------------------------------
        */

        $queuedCount = (int) ($recipientCounts['queued'] ?? 0);
        $processingCount = (int) ($recipientCounts['processing'] ?? 0);
        $sentCount = (int) ($recipientCounts['sent'] ?? 0);
        $failedCount = (int) ($recipientCounts['failed'] ?? 0);

        $processed = $sentCount + $failedCount;

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

        /*
        |--------------------------------------------------------------------------
        | Audience labels
        |--------------------------------------------------------------------------
        */

        $targetLabels = [
            'subscribed' => 'Subscribed Users',
            'all' => 'All Users',
            'user_class' => 'User Class',
            'active' => 'Active Users',
            'inactive' => 'Inactive Users',
            'seeders' => 'Seeders',
            'leechers' => 'Leechers',
            'hit_and_run' => 'Hit & Run',
            'donors' => 'Donors',
            'warned' => 'Warned Users',
            'disabled' => 'Disabled Users',
            'test' => 'Test Campaign',
        ];

        $targetLabel = $targetLabels[$campaign->target]
            ?? Str::headline($campaign->target);

        /*
        |--------------------------------------------------------------------------
        | Campaign status
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Batch series
        |--------------------------------------------------------------------------
        */

        $hasBatchSeries = !empty($campaign->batch_group_id);

        $batchNumber = $hasBatchSeries
            ? (int) $campaign->batch_number
            : null;

        $batchSize = $hasBatchSeries
            ? (int) $campaign->batch_size
            : null;

        $seriesRemainingCount = $hasBatchSeries
            ? (int) ($seriesRemaining ?? 0)
            : 0;

        $seriesSelectedCount = $hasBatchSeries
            ? (int) ($seriesTotalRecipients ?? $campaign->total_recipients)
            : $campaign->total_recipients;

        /*
         * We only allow another batch once this batch has no queued or
         * processing recipients remaining.
         */
        $batchFinished = $hasBatchSeries
            && $queuedCount === 0
            && $processingCount === 0
            && in_array(
                $campaign->status,
                [
                    'completed',
                    'completed_with_failures',
                    'failed',
                ],
                true
            );

        $canQueueNextBatch = $batchFinished
            && $seriesRemainingCount > 0;

        $nextBatchCount = $hasBatchSeries
            ? min(
                $batchSize,
                $seriesRemainingCount
            )
            : 0;
    @endphp


    {{-- ================================================================ --}}
    {{-- Header                                                           --}}
    {{-- ================================================================ --}}

    <div class="admin-page-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">

        <div>

            <a
                href="{{ route('admin.emails.campaigns') }}"
                class="text-decoration-none small d-inline-flex align-items-center mb-2"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back to campaigns
            </a>

            <div class="d-flex align-items-start gap-3">

                <div
                    class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary flex-shrink-0"
                    style="width: 48px; height: 48px;"
                >
                    <i class="bi bi-envelope-paper fs-4"></i>
                </div>

                <div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">

                        <h1 class="h3 fw-bold mb-0">
                            {{ $campaign->subject }}
                        </h1>

                        <span class="badge {{ $statusConfig['class'] }}">
                            <i class="bi {{ $statusConfig['icon'] }} me-1"></i>
                            {{ $statusConfig['label'] }}
                        </span>

                        @if($hasBatchSeries)

                            <span class="badge text-bg-dark">
                                <i class="bi bi-collection me-1"></i>
                                Batch {{ number_format($batchNumber) }}
                            </span>

                        @endif

                    </div>

                    <div class="text-body-secondary">

                        Campaign #{{ $campaign->id }}

                        <span class="mx-1">·</span>

                        {{ $targetLabel }}

                        <span class="mx-1">·</span>

                        Created
                        {{ $campaign->created_at?->format('d M Y H:i') }}

                    </div>

                </div>

            </div>

        </div>


        {{-- Header actions --}}

        <div class="d-flex flex-wrap gap-2">


            @if($hasBatchSeries && $seriesRemainingCount > 0)

                @if($canQueueNextBatch)

                    <form
                        method="POST"
                        action="{{ route('admin.emails.campaigns.next-batch', $campaign) }}"
                        onsubmit="return confirm('Queue the next batch of up to {{ number_format($batchSize) }} users?');"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            <i class="bi bi-fast-forward-fill me-1"></i>

                            Queue Next
                            {{ number_format($nextBatchCount) }}
                        </button>

                    </form>

                @else

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        disabled
                        title="Wait for the current batch to finish"
                    >
                        <i class="bi bi-hourglass-split me-1"></i>
                        Next Batch Waiting
                    </button>

                @endif

            @endif


            <a
                href="{{ route('admin.emails.create') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-pencil-square me-1"></i>
                New Campaign
            </a>

            @if(!in_array($campaign->status, ['queued', 'sending'], true)
    && $queuedCount === 0
    && $processingCount === 0)

    <button
        type="button"
        class="btn btn-outline-danger"
        data-bs-toggle="modal"
        data-bs-target="#deleteCampaignModal"
    >
        <i class="bi bi-trash3 me-1"></i>
        Delete Campaign
    </button>

@endif

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- Flash messages                                                   --}}
    {{-- ================================================================ --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- ================================================================ --}}
    {{-- Current campaign progress                                        --}}
    {{-- ================================================================ --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-end mb-2">

                <div>

                    <div class="small text-body-secondary mb-1">
                        Campaign Progress
                    </div>

                    <div class="h4 fw-bold mb-0">
                        {{ $progress }}%
                    </div>

                </div>

                <div class="text-end">

                    <div class="fw-semibold">

                        {{ number_format($processed) }}
                        of
                        {{ number_format($campaign->total_recipients) }}

                    </div>

                    <div class="small text-body-secondary">
                        recipients processed
                    </div>

                </div>

            </div>

            <div
                class="progress"
                role="progressbar"
                aria-valuenow="{{ $progress }}"
                aria-valuemin="0"
                aria-valuemax="100"
                style="height: 10px;"
            >

                <div
                    class="progress-bar {{ $progressClass }}"
                    style="width: {{ $progress }}%"
                ></div>

            </div>

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- Mailing series                                                   --}}
    {{-- ================================================================ --}}

    @if($hasBatchSeries)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-bottom py-3">

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">

                    <div>

                        <h2 class="h5 fw-bold mb-1">

                            <i class="bi bi-collection me-2"></i>
                            Mailing Series

                        </h2>

                        <div class="small text-body-secondary">
                            Progress across all batches in this mailing.
                        </div>

                    </div>

                    <span class="badge text-bg-primary fs-6">
                        Batch {{ number_format($batchNumber) }}
                    </span>

                </div>

            </div>


            <div class="card-body">

                <div class="row g-3">

                    {{-- Current batch --}}

                    <div class="col-6 col-lg-3">

                        <div class="border rounded-3 p-3 h-100">

                            <div class="small text-body-secondary mb-1">
                                Current Batch
                            </div>

                            <div class="h4 fw-bold mb-0">
                                #{{ number_format($batchNumber) }}
                            </div>

                        </div>

                    </div>


                    {{-- Batch size --}}

                    <div class="col-6 col-lg-3">

                        <div class="border rounded-3 p-3 h-100">

                            <div class="small text-body-secondary mb-1">
                                Batch Size
                            </div>

                            <div class="h4 fw-bold mb-0">
                                {{ number_format($batchSize) }}
                            </div>

                        </div>

                    </div>


                    {{-- Selected so far --}}

                    <div class="col-6 col-lg-3">

                        <div class="border rounded-3 p-3 h-100">

                            <div class="small text-body-secondary mb-1">
                                Selected So Far
                            </div>

                            <div class="h4 fw-bold mb-0">
                                {{ number_format($seriesSelectedCount) }}
                            </div>

                        </div>

                    </div>


                    {{-- Still available --}}

                    <div class="col-6 col-lg-3">

                        <div class="border rounded-3 p-3 h-100">

                            <div class="small text-body-secondary mb-1">
                                Still Available
                            </div>

                            <div class="h4 fw-bold mb-0 {{ $seriesRemainingCount > 0 ? 'text-primary' : 'text-success' }}">
                                {{ number_format($seriesRemainingCount) }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Series state --}}

                @if($seriesRemainingCount === 0)

                    <div class="alert alert-success mb-0 mt-3">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        All currently matching users have been included in this
                        mailing series.

                    </div>

                @elseif($canQueueNextBatch)

                    <div class="alert alert-info mb-0 mt-3">

                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                            <div>

                                <div class="fw-semibold">
                                    Ready for the next batch
                                </div>

                                <div class="small">

                                    {{ number_format($seriesRemainingCount) }}
                                    matching users have not yet been included.

                                </div>

                            </div>


                            <form
                                method="POST"
                                action="{{ route('admin.emails.campaigns.next-batch', $campaign) }}"
                                onsubmit="return confirm('Queue the next batch of up to {{ number_format($batchSize) }} users?');"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-primary text-nowrap"
                                >
                                    <i class="bi bi-fast-forward-fill me-1"></i>

                                    Queue Next
                                    {{ number_format($nextBatchCount) }}

                                </button>

                            </form>

                        </div>

                    </div>

                @else

                    <div class="alert alert-secondary mb-0 mt-3">

                        <i class="bi bi-hourglass-split me-2"></i>

                        Finish the current batch before starting the next one.

                    </div>

                @endif


                {{-- Existing batches --}}

                @if(isset($seriesCampaigns) && $seriesCampaigns->count() > 1)

                    <hr>

                    <div class="small text-body-secondary mb-2">
                        Batches in this mailing
                    </div>

                    <div class="d-flex flex-wrap gap-2">

                        @foreach($seriesCampaigns as $seriesCampaign)

                            <a
                                href="{{ route(
                                    'admin.emails.campaigns.show',
                                    $seriesCampaign
                                ) }}"
                                class="btn btn-sm {{ $seriesCampaign->id === $campaign->id ? 'btn-primary' : 'btn-outline-secondary' }}"
                            >

                                Batch
                                {{ number_format($seriesCampaign->batch_number) }}

                                <span
                                    class="badge {{ $seriesCampaign->id === $campaign->id ? 'text-bg-light' : 'text-bg-secondary' }} ms-1"
                                >
                                    {{ number_format($seriesCampaign->total_recipients) }}
                                </span>

                            </a>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>

    @endif


    {{-- ================================================================ --}}
    {{-- Current campaign statistics                                      --}}
    {{-- ================================================================ --}}

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-body-secondary mb-2">
                        <i class="bi bi-people me-1"></i>
                        Total
                    </div>

                    <div class="h3 fw-bold mb-0">
                        {{ number_format($campaign->total_recipients) }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-body-secondary mb-2">
                        <i class="bi bi-clock me-1"></i>
                        Queued
                    </div>

                    <div class="h3 fw-bold mb-0">
                        {{ number_format($queuedCount) }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-body-secondary mb-2">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Processing
                    </div>

                    <div class="h3 fw-bold mb-0 text-primary">
                        {{ number_format($processingCount) }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-body-secondary mb-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Sent
                    </div>

                    <div class="h3 fw-bold mb-0 text-success">
                        {{ number_format($sentCount) }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-body-secondary mb-2">
                        <i class="bi bi-x-circle me-1"></i>
                        Failed
                    </div>

                    <div class="h3 fw-bold mb-0 {{ $failedCount > 0 ? 'text-danger' : '' }}">
                        {{ number_format($failedCount) }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-xl">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-body-secondary mb-2">
                        <i class="bi bi-hourglass-split me-1"></i>
                        Remaining
                    </div>

                    <div class="h3 fw-bold mb-0">
                        {{ number_format($remaining) }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- Campaign details + message                                       --}}
    {{-- ================================================================ --}}

    <div class="row g-4 mb-4">

        {{-- Campaign information --}}

        <div class="col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-transparent border-bottom py-3">

                    <h2 class="h5 fw-bold mb-0">

                        <i class="bi bi-info-circle me-2"></i>
                        Campaign Details

                    </h2>

                </div>


                <div class="card-body">

                    @if($hasBatchSeries)

                        <div class="mb-3">

                            <div class="small text-body-secondary">
                                Mailing Batch
                            </div>

                            <div class="fw-semibold">

                                Batch {{ number_format($batchNumber) }}

                                <span class="text-body-secondary fw-normal">
                                    · up to
                                    {{ number_format($batchSize) }}
                                    users
                                </span>

                            </div>

                        </div>

                    @endif


                    <div class="mb-3">

                        <div class="small text-body-secondary">
                            Audience
                        </div>

                        <div class="fw-semibold">
                            {{ $targetLabel }}
                        </div>

                    </div>


                    @if(!empty($campaign->filters))

                        <div class="mb-3">

                            <div class="small text-body-secondary mb-1">
                                Filters
                            </div>

                            <div class="d-flex flex-wrap gap-1">

                                @if(!empty($campaign->filters['user_class']))

                                    <span class="badge border text-body">

                                        User classes:
                                        {{ implode(
                                            ', ',
                                            $campaign->filters['user_class']
                                        ) }}

                                    </span>

                                @endif


                                @if(isset($campaign->filters['send_limit']))

                                    <span class="badge border text-body">

                                        Send limit:

                                        {{ $campaign->filters['send_limit'] === 'all'
                                            ? 'All'
                                            : number_format(
                                                (int) $campaign->filters['send_limit']
                                            )
                                        }}

                                    </span>

                                @endif


                                @if(!empty($campaign->filters['test_user_id']))

                                    <span class="badge border text-body">

                                        Test user:
                                        #{{ $campaign->filters['test_user_id'] }}

                                    </span>

                                @endif

                            </div>

                        </div>

                    @endif


                    <hr>


                    <div class="mb-3">

                        <div class="small text-body-secondary">
                            Created
                        </div>

                        <div>
                            {{ $campaign->created_at?->format('d M Y H:i:s') }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <div class="small text-body-secondary">
                            Started
                        </div>

                        <div>
                            {{ $campaign->started_at?->format('d M Y H:i:s') ?? 'Not started' }}
                        </div>

                    </div>


                    <div>

                        <div class="small text-body-secondary">
                            Completed
                        </div>

                        <div>
                            {{ $campaign->completed_at?->format('d M Y H:i:s') ?? 'Not completed' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Message --}}

        <div class="col-xl-8">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-transparent border-bottom py-3">

                    <h2 class="h5 fw-bold mb-0">

                        <i class="bi bi-envelope-paper me-2"></i>
                        Message

                    </h2>

                </div>


                <div class="card-body">

                    <div class="mb-3">

                        <div class="small text-body-secondary mb-1">
                            Subject
                        </div>

                        <div class="fw-semibold">
                            {{ $campaign->subject }}
                        </div>

                    </div>


                    <div>

                        <div class="small text-body-secondary mb-2">
                            Email Body
                        </div>

                        <div
                            class="border rounded-3 p-3 bg-body-tertiary"
                            style="white-space: pre-wrap; word-break: break-word;"
                        >{{ $campaign->body }}</div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- Recipients                                                       --}}
    {{-- ================================================================ --}}

    <div class="card border-0 shadow-sm overflow-hidden">

        <div class="card-header bg-transparent border-bottom py-3">

            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">

                <div>

                    <h2 class="h5 fw-bold mb-1">

                        <i class="bi bi-people me-2"></i>
                        Recipients

                    </h2>

                    <div class="small text-body-secondary">
                        Individual delivery status for this campaign.
                    </div>

                </div>


                {{-- Recipient search/filter --}}

                <form
                    method="GET"
                    action="{{ route('admin.emails.campaigns.show', $campaign) }}"
                    class="d-flex flex-wrap gap-2"
                >

                    <div>

                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Name or email..."
                        >

                    </div>


                    <div>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All statuses
                            </option>

                            <option
                                value="queued"
                                @selected(request('status') === 'queued')
                            >
                                Queued
                            </option>

                            <option
                                value="processing"
                                @selected(request('status') === 'processing')
                            >
                                Processing
                            </option>

                            <option
                                value="sent"
                                @selected(request('status') === 'sent')
                            >
                                Sent
                            </option>

                            <option
                                value="failed"
                                @selected(request('status') === 'failed')
                            >
                                Failed
                            </option>

                        </select>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-outline-primary"
                    >
                        <i class="bi bi-search"></i>
                    </button>


                    @if(
                        request()->filled('search') ||
                        request()->filled('status')
                    )

                        <a
                            href="{{ route('admin.emails.campaigns.show', $campaign) }}"
                            class="btn btn-outline-secondary"
                        >
                            Clear
                        </a>

                    @endif

                </form>

            </div>

        </div>


        @if($recipients->count())

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>

                        <tr>

                            <th class="ps-4">
                                Recipient
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-center">
                                Attempts
                            </th>

                            <th>
                                Queued
                            </th>

                            <th>
                                Processed
                            </th>

                            <th>
                                Bounce
                            </th>

                            <th class="pe-4">
                                Error
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($recipients as $recipient)

                            @php
                                $recipientStatus = match($recipient->status) {
                                    'queued' => [
                                        'class' => 'text-bg-secondary',
                                        'icon' => 'bi-clock',
                                        'label' => 'Queued',
                                    ],

                                    'processing' => [
                                        'class' => 'text-bg-primary',
                                        'icon' => 'bi-arrow-repeat',
                                        'label' => 'Processing',
                                    ],

                                    'sent' => [
                                        'class' => 'text-bg-success',
                                        'icon' => 'bi-check-circle',
                                        'label' => 'Sent',
                                    ],

                                    'failed' => [
                                        'class' => 'text-bg-danger',
                                        'icon' => 'bi-x-circle',
                                        'label' => 'Failed',
                                    ],

                                    default => [
                                        'class' => 'text-bg-secondary',
                                        'icon' => 'bi-question-circle',
                                        'label' => Str::headline($recipient->status),
                                    ],
                                };

                                $processedAt = $recipient->sent_at
                                    ?? $recipient->failed_at
                                    ?? $recipient->processing_at;
                            @endphp


                            <tr>

                                {{-- Recipient --}}

                                <td class="ps-4">

                                    <div class="fw-semibold">
                                        {{ $recipient->name ?: 'Unknown user' }}
                                    </div>

                                    <div class="small text-body-secondary">
                                        {{ $recipient->email }}
                                    </div>

                                    @if($recipient->user_id)

                                        <div class="small text-body-secondary">
                                            User #{{ $recipient->user_id }}
                                        </div>

                                    @endif

                                </td>


                                {{-- Status --}}

                                <td>

                                    <span class="badge {{ $recipientStatus['class'] }}">

                                        <i class="bi {{ $recipientStatus['icon'] }} me-1"></i>

                                        {{ $recipientStatus['label'] }}

                                    </span>

                                </td>


                                {{-- Attempts --}}

                                <td class="text-center">
                                    {{ $recipient->attempts }}
                                </td>


                                {{-- Queued --}}

                                <td class="text-nowrap">

                                    @if($recipient->queued_at)

                                        <div>
                                            {{ $recipient->queued_at->format('d M Y') }}
                                        </div>

                                        <div class="small text-body-secondary">
                                            {{ $recipient->queued_at->format('H:i:s') }}
                                        </div>

                                    @else

                                        <span class="text-body-secondary">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- Processed --}}

                                <td class="text-nowrap">

                                    @if($processedAt)

                                        <div>
                                            {{ $processedAt->format('d M Y') }}
                                        </div>

                                        <div class="small text-body-secondary">
                                            {{ $processedAt->format('H:i:s') }}
                                        </div>

                                    @else

                                        <span class="text-body-secondary">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- Bounce --}}

                                <td>

                                    @if($recipient->bounced_at)

                                        <span
                                            class="badge text-bg-danger"
                                            title="{{ $recipient->bounce_message }}"
                                        >
                                            <i class="bi bi-exclamation-octagon me-1"></i>

                                            {{ $recipient->bounce_type
                                                ? Str::headline($recipient->bounce_type)
                                                : 'Bounced'
                                            }}

                                            @if($recipient->bounce_code)
                                                · {{ $recipient->bounce_code }}
                                            @endif

                                        </span>

                                        <div class="small text-body-secondary mt-1">
                                            {{ $recipient->bounced_at->format('d M Y H:i') }}
                                        </div>

                                    @else

                                        <span class="text-body-secondary">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- Error --}}

                                <td
                                    class="pe-4"
                                    style="max-width: 350px;"
                                >

                                    @if($recipient->error)

                                        <span
                                            class="text-danger small"
                                            title="{{ $recipient->error }}"
                                        >
                                            {{ Str::limit(
                                                $recipient->error,
                                                120
                                            ) }}
                                        </span>

                                    @elseif($recipient->bounce_message)

                                        <span
                                            class="text-danger small"
                                            title="{{ $recipient->bounce_message }}"
                                        >
                                            {{ Str::limit(
                                                $recipient->bounce_message,
                                                120
                                            ) }}
                                        </span>

                                    @else

                                        <span class="text-body-secondary">
                                            —
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            @if($recipients->hasPages())

                <div class="card-footer bg-transparent border-top py-3">
                    {{ $recipients->links('pagination::bootstrap-5') }}
                </div>

            @endif

        @else

            <div class="text-center py-5">

                <i class="bi bi-search display-5 text-body-secondary"></i>

                <h3 class="h5 fw-bold mt-3">
                    No recipients found
                </h3>

                <p class="text-body-secondary mb-0">
                    No recipients match the selected filters.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- ==================================================================== --}}
{{-- Auto-refresh while campaign is active                                --}}
{{-- ==================================================================== --}}

@if(in_array($campaign->status, ['queued', 'sending'], true))

    <script>
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    </script>

@endif


@if(!in_array($campaign->status, ['queued', 'sending'], true)
    && $queuedCount === 0
    && $processingCount === 0)

    <div
        class="modal fade"
        id="deleteCampaignModal"
        tabindex="-1"
        aria-labelledby="deleteCampaignModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger"
                            style="width: 48px; height: 48px;"
                        >
                            <i class="bi bi-trash3 fs-4"></i>
                        </div>

                        <div>
                            <h5
                                class="modal-title fw-bold mb-1"
                                id="deleteCampaignModalLabel"
                            >
                                Delete Campaign?
                            </h5>

                            <div class="small text-body-secondary">
                                Campaign #{{ $campaign->id }}
                                @if($campaign->batch_number)
                                    · Batch {{ number_format($campaign->batch_number) }}
                                @endif
                            </div>
                        </div>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body py-4">

                    <p class="mb-3">
                        Are you sure you want to permanently delete
                        <strong>{{ $campaign->subject }}</strong>?
                    </p>

                    <div class="alert alert-danger mb-0">
                        <div class="d-flex gap-2">

                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>

                            <div>
                                <strong>This action cannot be undone.</strong>

                                <div class="small mt-1">
                                    The campaign and its recipient history will
                                    be permanently deleted.
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 pt-0">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <form
                        method="POST"
                        action="{{ route('admin.emails.campaigns.destroy', $campaign) }}"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="btn btn-danger"
                        >
                            <i class="bi bi-trash3 me-1"></i>
                            Delete Campaign
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>

@endif

@endsection
