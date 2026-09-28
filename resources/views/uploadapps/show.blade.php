@extends('layouts.app')
@section('content')
<div class="container-fluid py-4 ua-page">
    <a class="ua-link d-inline-block mb-3" href="{{ route('uploadapps.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>All applications</a>
    <header class="ua-hero mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div><span class="ua-eyebrow">Uploader application #{{ $application->id }}</span><h1>{{ $application->applicant->name }}</h1><p class="ua-muted mb-0">Submitted {{ $application->created_at->format('d M Y, H:i') }}</p></div>
            @include('uploadapps.partials.status', ['status' => $application->status])
        </div>
        <p class="ua-muted mt-3 mb-0">
            @if($application->isActive())
                This application is {{ $application->status === 'pending' ? 'waiting for review' : 'under review' }}. A final decision will arrive in your private inbox.
            @else
                {{ $application->status === 'accepted' ? 'This application was approved.' : 'This application was rejected.' }}
                @if($application->decision_at) Decided {{ $application->decision_at->format('d M Y, H:i') }}.@endif
                @if($canReview && $application->reviewer) Reviewed by {{ $application->reviewer->name }}.@endif
                Check your private inbox for the confirmation and any reviewer feedback.
            @endif
        </p>
    </header>
    @include('uploadapps.partials.feedback')
    <div class="row g-4">
        <div class="col-lg-8"><section class="ua-card">
            <h2 class="mb-4">Application answers</h2>
            @foreach(['why_promoted' => 'Motivation', 'experience' => 'Torrent experience', 'content_plan' => 'Upload plans', 'external_sites' => 'Other trackers'] as $field => $label)
                <div class="ua-answer-section"><h3 class="h6 ua-muted">{{ $label }}</h3><div class="ua-answer">{{ $application->$field ?: 'Not provided' }}</div></div>
            @endforeach
        </section></div>
        <div class="col-lg-4"><section class="ua-card">
            <div class="ua-card-header"><h2>Applicant overview</h2><a class="ua-link small" href="{{ route('profile.show', ['id' => $application->applicant->id, 'name' => $application->applicant->name]) }}">View profile</a></div>
            @if($application->applicant->trashed())<p class="text-warning">This member’s account has been deleted.</p>@endif
            <dl class="ua-details">
                <div><dt>Member since</dt><dd>{{ $application->applicant->created_at->format('d M Y') }}</dd></div>
                <div><dt>Current class</dt><dd>{{ $application->applicant->role_name }}</dd></div>
                <div><dt>Uploaded</dt><dd>{{ \App\Helpers\FormatHelper::formatSize($application->applicant->uploaded) }}</dd></div>
                <div><dt>Ratio</dt><dd>{{ $application->applicant->downloaded > 0 ? number_format($application->applicant->uploaded / $application->applicant->downloaded, 2) : '∞' }}</dd></div>
            </dl>
            <hr>
            @foreach(['scene_access' => 'Scene access', 'know_torrents' => 'Creates torrents', 'understand_seeding' => 'Understands seeding'] as $field => $label)
                <div class="d-flex justify-content-between gap-3 my-2 small"><span class="ua-muted">{{ $label }}</span><strong>{{ $application->$field ? 'Yes' : 'No' }}</strong></div>
            @endforeach
            <hr>
            @foreach(['internal_speed' => 'Internal speedtest', 'external_speed' => 'External speedtest'] as $field => $label)
                <div class="my-2 small">
                    @if($application->$field && in_array(strtolower(parse_url($application->$field, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true))
                        <a class="ua-link" href="{{ $application->$field }}" target="_blank" rel="noopener noreferrer">{{ $label }} <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i><span class="visually-hidden"> (opens in a new tab)</span></a>
                    @else<span class="ua-muted">{{ $label }}: not provided</span>@endif
                </div>
            @endforeach
        </section></div>
        @if($canReview)
            <div class="col-lg-8"><section class="ua-card">
                <div class="ua-card-header"><h2>Staff discussion</h2><span class="ua-muted">Internal · {{ $comments->total() }} comments</span></div>
                @forelse($comments as $comment)
                    <article class="ua-comment"><div class="d-flex justify-content-between flex-wrap gap-2 mb-2"><strong class="small">{{ $comment->user?->name ?? 'Deleted member' }}</strong><time class="ua-muted" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time></div><div class="ua-answer">{{ $comment->comment }}</div></article>
                @empty<p class="ua-muted">No staff comments yet. Use this space to discuss the application before voting.</p>@endforelse
                {{ $comments->links() }}
                @if($canAct)
                    <form method="POST" action="{{ route('uploadapps.comment', $application->id) }}" class="mt-4">@csrf<label for="comment" class="form-label">Add a staff comment</label><textarea id="comment" name="comment" maxlength="5000" rows="4" required class="form-control @error('comment') is-invalid @enderror" aria-describedby="comment-help">{{ old('comment') }}</textarea><p id="comment-help" class="form-text">Visible to reviewing administrators. Feedback for the applicant belongs in the final decision.</p><button class="btn btn-outline-info" type="submit">Post comment</button></form>
                @endif
            </section></div>
            <div class="col-lg-4"><section class="ua-card">
                <div class="ua-card-header"><h2>Staff votes</h2><span class="ua-muted">{{ \App\Services\UploadApplicationService::REQUIRED_VOTES }} matching votes decide</span></div>
                <div class="d-flex justify-content-between gap-3 mb-3"><span class="text-success">{{ $application->votes_for }} approve</span><span class="text-danger">{{ $application->votes_against }} reject</span></div>
                @forelse($application->votes as $vote)
                    <div class="d-flex justify-content-between gap-3 py-2 small border-bottom"><span>{{ $vote->user?->name ?? 'Deleted member' }}{{ (int) $vote->user_id === (int) auth()->id() ? ' (you)' : '' }}</span><strong>{{ ucfirst($vote->vote) }}</strong></div>
                @empty<p class="ua-muted">No votes recorded yet.</p>@endforelse
                @if($canAct)
                    <form method="POST" action="{{ route('uploadapps.vote', $application->id) }}" class="mt-4">@csrf<div class="d-flex flex-wrap gap-2"><button type="submit" name="vote" value="approve" class="btn btn-outline-success">Vote to approve</button><button type="submit" name="vote" value="reject" class="btn btn-outline-danger">Vote to reject</button></div><p class="form-text mb-0">You can change your vote until a decision is made. Reaching either threshold sends the applicant a private confirmation.</p></form>
                @endif
            </section></div>
        @endif
        @if($canAct)
            <div class="col-12"><section class="ua-card">
                <h2>Final decision</h2><p class="ua-muted">Decide now without waiting for the voting threshold. A final decision closes voting and sends a private message to the applicant.</p>
                <div class="row g-4">
                    <div class="col-md-6"><form method="POST" action="{{ route('uploadapps.accept', $application->id) }}">@csrf<p class="small">Approve this application and grant uploader access. Existing higher account classes are preserved.</p><button type="submit" class="btn btn-success">Approve application</button></form></div>
                    <div class="col-md-6"><form method="POST" action="{{ route('uploadapps.reject', $application->id) }}">@csrf<label for="reason" class="form-label">Reason for rejection</label><textarea id="reason" name="reason" rows="3" maxlength="2000" required class="form-control @error('reason') is-invalid @enderror" aria-describedby="reason-help">{{ old('reason') }}</textarea><p id="reason-help" class="form-text">Sent to the applicant along with the {{ \App\Models\User::UPLOADER_COOLDOWN_DAYS }}-day reapplication cooldown.</p><button type="submit" class="btn btn-outline-danger">Reject &amp; send feedback</button></form></div>
                </div>
            </section></div>
        @endif
    </div>
</div>
@include('uploadapps.partials.style')
@endsection
