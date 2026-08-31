@extends('layouts.app')

@section('content')

<div class="container mt-5">


    {{-- TOP BAR --}}
<div class="card glass shadow-sm border-0 mb-4">
    <div class="card-body py-3 px-4">

        <div class="d-flex justify-content-between align-items-center">

            {{-- LEFT STATS --}}
            <div class="d-flex align-items-center gap-4">

                {{-- EMAILS --}}
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope text-success fs-5"></i>
                    <div>
                        <div class="small text-muted text-uppercase">Emails</div>
                        <div class="fw-semibold fs-5">
                            {{ number_format($emails->total()) }}
                        </div>
                    </div>
                </div>

                {{-- DIVIDER --}}
                <div class="vr d-none d-md-block"></div>

                {{-- SUBSCRIBED --}}
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-people text-primary fs-5"></i>
                    <div>
                        <div class="small text-muted text-uppercase">Subscribed</div>
                        <div class="fw-semibold fs-5 text-primary">
                            {{ number_format($subscribedCount) }}
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT ACTIONS --}}
            <div class="d-flex align-items-center gap-2">

                {{-- DELETE OLD --}}
                <form method="POST" action="{{ route('admin.emails.delete-old') }}" class="d-flex align-items-center gap-1">
                    @csrf
                    @method('DELETE')

                    <select name="days" class="form-select form-select-sm">
                        <option value="7">7d</option>
                        <option value="30">30d</option>
                        <option value="90">90d</option>
                        <option value="365">1y</option>
                    </select>

                    <button class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Delete old email logs?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

                {{-- NEW EMAIL --}}
                <a href="{{ route('admin.emails.create') }}" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    New
                </a>

            </div>

        </div>

    </div>
</div>

    {{-- EMAIL LIST --}}
    <div class="card glass shadow-sm border-0">
        <div class="card-body p-0">

            {{-- HEADER --}}
            <div class="row g-0 border-bottom small text-uppercase text-muted">

                <div class="col-md-2 px-3 py-3">User</div>
                <div class="col-md-1 px-3 py-3">Class</div>
                <div class="col-md-1 px-3 py-3">Last Seen</div>
                <div class="col-md-2 px-3 py-3">Subject</div>
                <div class="col-md-3 px-3 py-3">Preview</div>
                <div class="col-md-2 text-center py-3">Sent</div>
                <div class="col-md-1 text-center py-3">Action</div>

            </div>

            {{-- ROWS --}}
            @forelse($emails as $email)

                <div class="row g-0 align-items-center border-bottom py-3 hover-row">

                    {{-- USER --}}
                    <div class="col-md-2 px-3">
                        <div class="fw-bold">
                          @if($email->user)
                          <a href="{{ route('profile.show', $email->user->id) }}" class="fw-semibold text-decoration-none">
                            {{ $email->user->name }}
                        </a>
                        @else
                        —
                        @endif
                        </div>
                        <div class="small text-muted">
                            {{ $email->user->email ?? '' }}
                        </div>
                    </div>

                    {{-- CLASS --}}
                    <div class="col-md-1 px-3">
                        <span 
                            class="badge"
                            style="background: {{ \App\Models\UserClass::getClassColor($email->user->user_class ?? 0) }}"
                        >
                            {{ \App\Models\UserClass::getClassName($email->user->user_class ?? 0) }}
                        </span>
                    </div>

                    {{-- LAST Seen --}}
                    <div class="col-md-1 px-3">
                        <div class="fw-bold">
                            {{ $email->user->last_activity ? $email->user->last_activity->diffForHumans() : '—' }}
                        </div>
                        
                    </div>



                    {{-- SUBJECT --}}
                    <div class="col-md-2 px-3 fw-semibold">
                        {{ $email->subject }}
                    </div>

                    {{-- BODY PREVIEW --}}
                    <div class="col-md-3 px-3 small text-muted">
                        {{ \Illuminate\Support\Str::limit(strip_tags($email->body), 80) }}
                    </div>

                    {{-- SENT DATE --}}
                    <div class="col-md-2 text-center small text-info fw-semibold">
                        {{ $email->sent_at->diffForHumans() }}
                        <div class="text-muted small">
                            {{ $email->sent_at->format('d M Y H:i') }}
                        </div>
                    </div>

                    {{-- DELETE --}}
<div class="col-md-1 text-center">

    <form action="{{ route('admin.emails.destroy', $email->id) }}" 
          method="POST" 
          onsubmit="return confirm('Delete this email log?')">

        @csrf
        @method('DELETE')

        <button class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i>
        </button>

    </form>

</div>

                </div>

            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-envelope-x fs-2 d-block mb-2"></i>
                    No emails sent yet.
                </div>
            @endforelse

        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $emails->links('pagination::bootstrap-5') }}
    </div>

</div>

<style>
.hover-row {
    transition: all 0.2s ease;
}
.hover-row:hover {
    background: rgba(0, 123, 255, 0.05);
}
</style>

@endsection