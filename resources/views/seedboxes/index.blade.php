@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h2 class="text-light mb-2 mb-md-0">Seedboxes</h2>
        <a href="{{ route('seedboxes.create') }}" class="btn btn-primary btn-hover">Add New Seedbox</a>
    </div>

    {{-- Alerts --}}
    @foreach (['success', 'error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible fade show rounded-3" role="alert">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

   @php
    $authUserId = auth()->id();
    $isDev = auth()->user()->user_class == \App\Models\UserClass::WEB_DEVELOPER;

    // Auth user's seedboxes
    $userSeedboxes = $seedboxes->where('user_id', $authUserId);

    // Other seedboxes are only visible to WEB_DEVELOPER
    $otherSeedboxes = $isDev 
        ? $seedboxes->where('user_id', '!=', $authUserId)
        : collect();
@endphp

{{-- User Seedboxes --}}
<h4 class="text-info mt-4">Your Seedboxes</h4>
@if($userSeedboxes->isEmpty())
    <div class="alert alert-secondary text-center my-3 rounded-3">
        <i class="bi bi-info-circle"></i> You haven't added any seedboxes yet.
    </div>
@else
    @include('seedboxes.partials.seedbox-table', ['boxes' => $userSeedboxes, 'highlightOwner' => true])
@endif

{{-- Other Seedboxes (only for WEB_DEVELOPER) --}}
@if($isDev && $otherSeedboxes->isNotEmpty())
    <h4 class="text-warning mt-5">Other Seedboxes</h4>
    @include('seedboxes.partials.seedbox-table', ['boxes' => $otherSeedboxes, 'highlightOwner' => false])
@endif


</div>

<script>
function togglePassword(id) {
    const input = document.getElementById('password-' + id);
    const button = input.nextElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'Hide';
    } else {
        input.type = 'password';
        button.textContent = 'Show';
    }
}
</script>

@endsection
