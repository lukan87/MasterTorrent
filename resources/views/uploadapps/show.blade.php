@extends('layouts.app')

@section('content')

<div class="container">

<h2 class="mb-4">Uploader Application #{{ $application->id }}</h2>

{{-- SUCCESS / ERROR --}}
@if(session('success'))
<div class="alert alert-success">
{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
{{ session('error') }}
</div>
@endif

<div class="card mb-4">
<div class="card-header">
<strong>Applicant Information</strong>
</div>

<div class="card-body">

<p><strong>User:</strong> {{ $application->applicant->name }}</p>

<p><strong>Account Age:</strong>
{{ $application->applicant->created_at->diffForHumans() }}
</p>

<p><strong>Uploaded:</strong>
{{ App\Helpers\FormatHelper::formatSize($application->applicant->uploaded) }}
</p>

<p><strong>Downloaded:</strong>
{{ App\Helpers\FormatHelper::formatSize($application->applicant->downloaded) }}
</p>

<p><strong>Ratio:</strong>

@if($application->applicant->downloaded > 0)
{{ round($application->applicant->uploaded / $application->applicant->downloaded,2) }}
@else
∞
@endif

</p>

</div>
</div>


{{-- APPLICATION ANSWERS --}}

<div class="card mb-4">

<div class="card-header">
<strong>Application Answers</strong>
</div>

<div class="card-body">

<p><strong>Why should you be promoted?</strong></p>
<p>{{ $application->why_promoted }}</p>

<hr>

<p><strong>Torrent Experience</strong></p>
<p>{{ $application->experience }}</p>

<hr>

<p><strong>What will you upload?</strong></p>
<p>{{ $application->content_plan }}</p>

<hr>

<p><strong>Internal Speed</strong></p>

@if($application->internal_speed)
<a href="{{ $application->internal_speed }}" target="_blank">View Speedtest</a>
@else
Not provided
@endif

<hr>

<p><strong>External Speed</strong></p>

@if($application->external_speed)
<a href="{{ $application->external_speed }}" target="_blank">View Speedtest</a>
@else
Not provided
@endif

<hr>

<p><strong>External Sites</strong></p>
<p>{{ $application->external_sites ?? 'None provided' }}</p>

<hr>

<p><strong>Scene Access:</strong>
{{ $application->scene_access ? 'Yes' : 'No' }}
</p>

<p><strong>Knows How To Create Torrents:</strong>
{{ $application->know_torrents ? 'Yes' : 'No' }}
</p>

<p><strong>Understands Seeding:</strong>
{{ $application->understand_seeding ? 'Yes' : 'No' }}
</p>

</div>
</div>


{{-- STAFF DISCUSSION --}}

@if(auth()->user()->user_class >= 7)

<div class="card mb-4">

<div class="card-header">
<strong>Staff Discussion</strong>
</div>

<div class="card-body">

@forelse($application->comments as $comment)

<div class="border p-2 mb-2">

<strong>{{ $comment->user->name }}</strong>
<small class="text-muted">
{{ $comment->created_at->diffForHumans() }}
</small>

<p class="mt-2">{{ $comment->comment }}</p>

</div>

@empty

<p>No comments yet.</p>

@endforelse


<form method="POST"
action="{{ route('uploadapps.comment',$application->id) }}">

@csrf

<textarea
name="comment"
class="form-control"
rows="3"
placeholder="Staff discussion..."
required
></textarea>

<button class="btn btn-primary mt-2">
Post Comment
</button>

</form>

</div>
</div>

@endif



{{-- STAFF VOTING --}}

@if(auth()->user()->user_class >= 7)

<div class="card mb-4">

<div class="card-header">
<strong>Staff Voting</strong>
</div>

<div class="card-body">

<p>

<strong>Votes For:</strong> {{ $application->votes_for }}

<br>

<strong>Votes Against:</strong> {{ $application->votes_against }}

</p>


<form method="POST"
action="{{ route('uploadapps.vote',$application->id) }}">

@csrf

<button
name="vote"
value="approve"
class="btn btn-success">

Approve

</button>


<button
name="vote"
value="reject"
class="btn btn-danger">

Reject

</button>

</form>

</div>
</div>

@endif


<hr>

<h5>Vote Details</h5>

@foreach($application->votes as $vote)

<div>
<strong>{{ $vote->user->name }}</strong>

@if($vote->vote === 'approve')
<span class="badge bg-success">Approve</span>
@else
<span class="badge bg-danger">Reject</span>
@endif

</div>

@endforeach

@php
$total = $application->votes_for + $application->votes_against;
$percent = $total > 0 ? ($application->votes_for / $total) * 100 : 0;
@endphp

<div class="progress mt-3 mb-3">

<div
class="progress-bar bg-success"
role="progressbar"
style="width: {{ $percent }}%">

{{ round($percent) }}% Approval

</div>

</div>


{{-- FINAL DECISION --}}

@if(auth()->user()->user_class >= 7 && !in_array($application->status, ['accepted','rejected']))

<div class="card">

<div class="card-header">
<strong>Final Decision</strong>
</div>

<div class="card-body">

<form method="POST"
action="{{ route('uploadapps.accept',$application->id) }}"
style="display:inline">

@csrf

<button class="btn btn-success">
Accept Application
</button>

</form>


<form method="POST"
action="{{ route('uploadapps.reject',$application->id) }}"
style="display:inline">

@csrf

<button class="btn btn-danger">
Reject Application
</button>

</form>

</div>

</div>

@endif


</div>

@endsection