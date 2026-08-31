@extends('layouts.app')

@section('content')

<div class="container">

<h2 class="mb-4">Uploader Applications</h2>

{{-- Flash Messages --}}
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


@php

$user = auth()->user();

/*
|--------------------------------------------------------------------------
| ACCOUNT AGE
|--------------------------------------------------------------------------
*/

$accountAgeDays = $user->created_at->diffInDays(now());
$ageOk = $accountAgeDays >= 30;

$diff = $user->created_at->diff(now());

$years = $diff->y;
$months = $diff->m;
$weeks = intdiv($diff->d, 7);
$remainingDays = $diff->d % 7;


/*
|--------------------------------------------------------------------------
| UPLOAD
|--------------------------------------------------------------------------
*/

$uploadedGB = $user->uploaded;
$uploadRequirement = 300;

$uploadOk = $uploadedGB >= $uploadRequirement;


/*
|--------------------------------------------------------------------------
| RATIO
|--------------------------------------------------------------------------
*/

$ratio = $user->downloaded > 0
    ? $user->uploaded / $user->downloaded
    : 0;

$ratioRequirement = 1.05;

$ratioOk = $ratio >= $ratioRequirement;


/*
|--------------------------------------------------------------------------
| ELIGIBILITY
|--------------------------------------------------------------------------
*/

$eligible = $ageOk && $uploadOk && $ratioOk;


/*
|--------------------------------------------------------------------------
| COOLDOWN
|--------------------------------------------------------------------------
*/

$cooldown = $user->uploaderApplicationCooldown();

@endphp


{{-- REQUIREMENTS CARD --}}
@if($user->user_class < 5)

<div class="card glass mb-3">

<div class="card-header">
Uploader Requirements
</div>

<div class="card-body">

<ul class="list-unstyled mb-0">

<li class="mb-2">

@if($ageOk)
<span class="text-success">✔</span>
@else
<span class="text-danger">✖</span>
@endif

<strong>Account Age</strong>

<span class="text-muted">
{{ $years }}y {{ $months }}m {{ $weeks }}w {{ $remainingDays }}d
</span>

</li>


<li class="mb-2">

@if($uploadOk)
<span class="text-success">✔</span>
@else
<span class="text-danger">✖</span>
@endif

<strong>Uploaded</strong>

<span class="text-muted">
 {{ App\Helpers\FormatHelper::formatSize($uploadedGB) }} / {{ $uploadRequirement }} GB

</span>

</li>


<li>

@if($ratioOk)
<span class="text-success">✔</span>
@else
<span class="text-danger">✖</span>
@endif

<strong>Ratio</strong>

<span class="text-muted">
{{ round($ratio,2) }} / {{ $ratioRequirement }}
</span>

</li>

</ul>

</div>

</div>


{{-- APPLY BUTTON / MESSAGES --}}

@if(!$eligible)

<div class="alert alert-warning">
You do not meet the uploader requirements yet.
</div>

@elseif($cooldown['blocked'])

<div class="alert alert-warning">
You cannot apply yet.<br>
You may apply again in <strong>{{ $cooldown['days_remaining'] }} days</strong>.
</div>

@else

<div class="alert alert-success">
✔ You meet the requirements and can apply for uploader.
</div>

<a href="{{ route('uploadapps.create') }}" class="btn btn-primary mb-3">
Apply for Uploader
</a>

@endif

@endif


{{-- APPLICATION LIST --}}

@if($applications->isEmpty())

<div class="alert alert-info">
No applications found.
</div>

@else

<div class="card">

<div class="card-header">
Applications
</div>

<div class="card-body p-0">

<table class="table table-striped mb-0">

<thead>
<tr>

<th>ID</th>
<th>Applicant</th>
<th>Ratio</th>
<th>Uploaded</th>
<th>Status</th>
<th>Votes</th>
<th>Submitted</th>
<th>Actions</th>

</tr>
</thead>

<tbody>

@foreach($applications as $app)

<tr>

<td>{{ $app->id }}</td>

<td>
<a href="{{ route('profile.show',$app->applicant->id) }}">
{{ $app->applicant->name }}
</a>
</td>


<td>

@if($app->applicant->downloaded > 0)
{{ round($app->applicant->uploaded / $app->applicant->downloaded,2) }}
@else
∞
@endif

</td>


<td>
 {{ App\Helpers\FormatHelper::formatSize($app->applicant->uploaded) }}
</td>


<td>

@if($app->status === 'pending')
<span class="badge bg-warning">Pending</span>

@elseif($app->status === 'discussion')
<span class="badge bg-info">Discussion</span>

@elseif($app->status === 'voting')
<span class="badge bg-primary">Voting</span>

@elseif($app->status === 'accepted')
<span class="badge bg-success">Accepted</span>

@elseif($app->status === 'rejected')
<span class="badge bg-danger">Rejected</span>
@endif

</td>


<td>

👍 {{ $app->votes_for }} <br>
👎 {{ $app->votes_against }}

</td>


<td>
{{ $app->created_at->diffForHumans() }}
</td>


<td>

<a
href="{{ route('uploadapps.show',$app->id) }}"
class="btn btn-sm btn-info">

View

</a>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

@endif


{{-- PAGINATION (STAFF ONLY) --}}

@if($user->user_class >= 7)

<div class="mt-3">
{{ $applications->links() }}
</div>

@endif

</div>

<style>


    
</style>

@endsection