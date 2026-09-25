@extends('layouts.app')

@section('content')

@php
    $inviteOnly = config('app.invite_only');
    $userClass = Auth::user()->user_class ?? 0;

    $usedInvites = $invites->where('is_used', true)->count();
    $activeInvites = $invites->filter(fn ($invite) => !$invite->is_used && !$invite->expired)->count();
@endphp

<div class="container mt-4">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <p class="text-muted">Codes expire after 14 days. Revoking an active, unused code returns one invite to your balance. You will receive a private message when someone joins using your code.</p>

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-semibold mb-0">
            <i class="bi bi-person-plus-fill text-primary me-2"></i>
            Your Invites
        </h3>

        <span class="badge bg-primary fs-6 px-3 py-2">
            Available: {{ $inviteCount }}
        </span>
    </div>


    {{-- Invite stats --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card glass shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="text-muted small">Active Invites</div>
                    <div class="fs-4 fw-bold text-success">{{ $activeInvites }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card glass shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="text-muted small">Used Invites</div>
                    <div class="fs-4 fw-bold text-primary">{{ $usedInvites }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card glass shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="text-muted small">Total Invites</div>
                    <div class="fs-4 fw-bold">{{ $invites->count() }}</div>
                </div>
            </div>
        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="card-body">

            {{-- Signup status --}}
            @if(!$inviteOnly)
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        Signups are currently <strong>open</strong>. Invite codes are optional.
                    </div>
                </div>
            @endif


            {{-- Create Invite Button Logic --}}
            <div class="mb-4">

                @if(!$inviteOnly && $userClass >= 7)

                    <form action="{{ route('invites.create') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success" @disabled($inviteCount <= 0)>
                            <i class="bi bi-plus-circle me-1"></i>
                            Create New Invite
                        </button>
                    </form>

                @elseif($inviteOnly)

                    @if($userClass >= \App\Models\UserClass::ELITE_USER)

                        <form action="{{ route('invites.create') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" @disabled($inviteCount <= 0)>
                                <i class="bi bi-plus-circle me-1"></i>
                                Create New Invite
                            </button>
                        </form>

                    @else

                        <span data-bs-toggle="tooltip"
                              title="You need to be an Elite user to create invite codes">

                            <button class="btn btn-secondary" disabled>
                                <i class="bi bi-lock me-1"></i>
                                Create New Invite
                            </button>

                        </span>

                    @endif

                @endif

            </div>


            {{-- Invite list --}}
            @if($invites->isEmpty())

                <div class="text-center py-5">

                    <i class="bi bi-envelope-open text-muted" style="font-size:50px;"></i>

                    <p class="text-muted mt-3 mb-0">
                        You haven't created any invites yet.
                    </p>

                </div>

            @else

<div class="invite-list">

@foreach($invites as $invite)

<div class="invite-row py-3 px-3 mb-2 {{ $invite->is_used ? 'used' : '' }}">

<div class="row align-items-center">

{{-- Invite Code --}}
<div class="col-md-3">

<div class="invite-code d-flex align-items-center gap-2">

@if($invite->is_used || $invite->expired)

    <code class="px-2 py-1 rounded text-decoration-line-through text-muted">
        {{ $invite->invite_code }}
    </code>

@else

    <code class="px-2 py-1 bg-warning bg-opacity-10 rounded">
        {{ $invite->invite_code }}
    </code>

    <button class="btn btn-sm btn-outline-secondary"
            onclick="navigator.clipboard.writeText('{{ $invite->invite_code }}')"
            data-bs-toggle="tooltip"
            title="Copy code">

        <i class="bi bi-clipboard"></i>

    </button>

@endif

</div>

<div class="small text-muted">
Created {{ $invite->created_at->diffForHumans() }}
@if(!$invite->is_used)
    <br>{{ $invite->expired ? 'Expired' : 'Expires' }} {{ $invite->expires_at->utc()->format('M j, Y H:i') }} UTC
@endif
@if(!$invite->is_used && !$invite->expired)
    <br><a href="{{ route('register', ['invite_code' => $invite->invite_code]) }}">Registration link</a>
@endif
</div>

</div>


{{-- Status --}}
<div class="col-md-2">

@if($invite->is_used)

<span class="badge bg-success">
<i class="bi bi-check-circle me-1"></i>
Used
</span>

@elseif(!$invite->expired)

<span class="badge bg-warning text-dark">
Pending
</span>

@endif

@if(!$invite->is_used && $invite->expired)

<span class="badge bg-danger ms-1">
Expired
</span>

@endif

</div>


{{-- User --}}
<div class="col-md-3">

@if($invite->is_used && $invite->usedBy)

<a href="{{ route('profile.show', $invite->usedBy->id) }}"
class="fw-semibold text-decoration-none">

<i class="bi bi-person-circle text-primary me-1"></i>
{{ $invite->usedBy->name }}

</a>

@if($invite->usedBy && $invite->usedBy->trashed())
<span class="badge bg-danger ms-1">Deleted</span>
@endif

<div class="small text-muted">
    <div class="invite-stats small">
        <span class="text-success fs-6">
Joined {{ $invite->usedBy->created_at->diffForHumans() }} 
</span>
<br>

<span class="text-danger fs-6">Last Seen {{ $invite->usedBy->updated_at->diffForHumans() }}</span>
</div>
</div>

@else

<span class="text-muted">Not used yet</span>

@endif

</div>


{{-- Activity --}}
<div class="col-md-3">

@if($invite->is_used && $invite->usedBy)

<div class="invite-stats d-inline-flex align-items-center gap-3 px-3 py-1 rounded">

<span class="text-success fs-5">
<i class="bi bi-arrow-up-circle me-1"></i>
{{ \App\Helpers\FormatHelper::formatSize($invite->usedBy->uploaded) }}
</span>

<span class="text-danger fs-5">
<i class="bi bi-arrow-down-circle me-1"></i>
{{ \App\Helpers\FormatHelper::formatSize($invite->usedBy->downloaded) }}
</span>

<span class="text-info fs-5">
<i class="bi bi-activity me-1"></i>
@if(($invite->usedBy->downloaded ?? 0) > 0)
{{ number_format($invite->usedBy->uploaded / $invite->usedBy->downloaded,2) }}
@else
∞
@endif
</span>

</div>

@endif

</div>


{{-- Action --}}
<div class="col-md-1 text-end">

@if(!$invite->is_used && !$invite->expired)

<form action="{{ route('invites.delete', $invite->id) }}"
method="POST"
onsubmit="return confirm('Revoke this invite and return one invite to your balance?');">

@csrf
@method('DELETE')

<button class="btn btn-sm btn-outline-danger"
data-bs-toggle="tooltip"
title="Revoke invite">

<i class="bi bi-trash"></i>

</button>

</form>

@endif

</div>

</div>

</div>

@endforeach

</div>

            @endif

        </div>
    </div>
</div>


<style>

.invite-row{
background:#3a3838;
border:1px solid #1c202472;
border-radius:8px;
transition:all .15s ease;
}

.invite-row:hover{
background:#313436;
border-color:#437db8;
}

.invite-code code{
font-size:14px;
}

.invite-stats span{
font-weight:500;
margin-right:10px;
}

.invite-row.used{
opacity:0.7;
}

</style>
@endsection