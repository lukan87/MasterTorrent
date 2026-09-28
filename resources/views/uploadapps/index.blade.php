@extends('layouts.app')
@section('content')
<div class="container-fluid py-4 ua-page">
    <header class="ua-hero mb-4">
        <span class="ua-eyebrow">Contribute to the community</span>
        <h1>Uploader applications</h1>
        <p class="ua-muted mb-0">{{ $canReview ? 'Review applications, discuss with the team and help new uploaders get started.' : 'Share your experience, tell us what you want to upload and follow your application here.' }}</p>
    </header>
    @include('uploadapps.partials.feedback')
    @if(auth()->user()->user_class < \App\Models\UserClass::UPLOADER)
        @php
            $user = auth()->user();
            $ageDays = (int) floor($user->created_at->diffInDays(now()));
            $ratio = $user->downloaded > 0 ? $user->uploaded / $user->downloaded : ($user->uploaded > 0 ? INF : 0);
            $requirements = [
                ['Account age', number_format($ageDays).' days', 'At least '.\App\Models\User::UPLOADER_MIN_AGE_DAYS.' days', $ageDays >= \App\Models\User::UPLOADER_MIN_AGE_DAYS],
                ['Uploaded', \App\Helpers\FormatHelper::formatSize($user->uploaded), 'At least 300 GB', $user->uploaded >= \App\Models\User::UPLOADER_MIN_UPLOAD],
                ['Share ratio', is_infinite($ratio) ? '∞' : number_format($ratio, 2), 'At least '.\App\Models\User::UPLOADER_MIN_RATIO, $ratio >= \App\Models\User::UPLOADER_MIN_RATIO],
            ];
        @endphp
        <section class="ua-card mb-4">
            <div class="ua-card-header"><h2>Ready to become an uploader?</h2><span class="ua-muted">Your current eligibility</span></div>
            <div class="ua-requirements">
                @foreach($requirements as [$label, $value, $hint, $met])
                    <div class="ua-requirement">
                        <span class="ua-muted">{{ $label }}</span><strong>{{ $value }}</strong>
                        <span class="{{ $met ? 'text-success' : 'text-warning' }} small"><i class="bi {{ $met ? 'bi-check-circle' : 'bi-hourglass-split' }} me-1" aria-hidden="true"></i>{{ $met ? 'Met' : 'Not yet' }} · {{ $hint }}</span>
                    </div>
                @endforeach
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
                <p class="ua-muted mb-0">{{ $blocker ?? 'You meet the requirements. Tell us about your experience and upload plans.' }}</p>
                @unless($blocker)<a class="btn btn-success" href="{{ route('uploadapps.create') }}"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Start application</a>@endunless
            </div>
        </section>
    @endif
    <section class="ua-card">
        <div class="ua-card-header"><h2>{{ $canReview ? 'Review queue' : 'Your applications' }}</h2><span class="ua-muted">{{ $applications->total() }} {{ request('status') ? 'matching' : 'total' }}</span></div>
        <nav class="ua-filters mb-4" aria-label="Filter applications by status">
            <a href="{{ route('uploadapps.index') }}" @if(!request('status')) aria-current="page" @endif>All ({{ $statusCounts->sum() }})</a>
            @foreach(\App\Models\UploadApplication::STATUSES as $status)
                <a href="{{ route('uploadapps.index', ['status' => $status]) }}" @if(request('status') === $status) aria-current="page" @endif>{{ $status === 'accepted' ? 'Approved' : ucfirst($status) }} ({{ $statusCounts[$status] ?? 0 }})</a>
            @endforeach
        </nav>
        @if($applications->isEmpty())
            <div class="ua-empty"><i class="bi bi-inbox fs-2 d-block mb-3" aria-hidden="true"></i><strong>No applications{{ request('status') ? ' with this status' : ' yet' }}.</strong><p class="mt-2 mb-0">{{ $canReview ? 'New applications will appear here when members submit them.' : 'Your submitted applications and decisions will appear here.' }}</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover ua-table">
                    <caption class="visually-hidden">Uploader applications and their current review status</caption>
                    <thead><tr><th scope="col">Application</th><th scope="col">Applicant</th><th scope="col">Uploaded / ratio</th><th scope="col">Status</th><th scope="col">Submitted</th><th scope="col"><span class="visually-hidden">Open</span></th></tr></thead>
                    <tbody>
                        @foreach($applications as $application)
                            <tr>
                                <td>#{{ $application->id }}</td>
                                <td><a href="{{ route('profile.show', ['id' => $application->applicant->id, 'name' => $application->applicant->name]) }}">{{ $application->applicant->name }}</a>@if($application->applicant->trashed())<span class="ua-muted"> (deleted)</span>@endif</td>
                                <td>{{ \App\Helpers\FormatHelper::formatSize($application->applicant->uploaded) }}<span class="ua-muted d-block">Ratio {{ $application->applicant->downloaded > 0 ? number_format($application->applicant->uploaded / $application->applicant->downloaded, 2) : '∞' }}</span></td>
                                <td>@include('uploadapps.partials.status', ['status' => $application->status])</td>
                                <td><time datetime="{{ $application->created_at->toIso8601String() }}" title="{{ $application->created_at->format('d M Y H:i') }}">{{ $application->created_at->diffForHumans() }}</time></td>
                                <td class="text-end"><a class="btn btn-outline-info" href="{{ route('uploadapps.show', $application->id) }}" aria-label="View application {{ $application->id }}">View <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $applications->links() }}</div>
        @endif
    </section>
</div>
@include('uploadapps.partials.style')
@endsection
