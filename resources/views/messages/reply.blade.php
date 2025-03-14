@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    <div class="card border-0 shadow rounded-4">
        <!-- Card Header (Subject) -->
        <div class="card-header border-bottom p-3">
            <h5 class="fw-bold text-primary mb-0">Reply to: {{ $message->subject ?? '(No Subject)' }}</h5>
        </div>

        <!-- Card Body (Original Message & Reply Form) -->
        <div class="card-body">
            <!-- Sender Info -->
            <div class="d-flex align-items-center mb-3">
                <img src="{{ $message->sender->profile_image ?? asset('default-avatar.png') }}" alt="Avatar" 
                    class="rounded-circle border shadow-sm me-3" width="50" height="50">
                <div>
                    <h6 class="fw-bold mb-0">
                        <a href="{{ route('profile.show', $message->sender->id) }}" class="text-decoration-none">
                            {{ $message->sender->name }}
                        </a>
                    </h6>
                    <small class="text-muted">Sent on {{ $message->created_at->format('d M Y, H:i') }}</small>
                </div>
            </div>

            <!-- Original Message -->
            <div class="p-4 rounded-3 mb-4">
                <p class="mb-0 fw-bold">{!! convertCustomTagsToHtml($message->body) !!}</p>
            </div>

            <!-- Reply Form -->
            <form action="{{ route('messages.storeReply', $message) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="body" class="form-label fw-bold">Your Reply:</label>
                    <textarea name="body" id="body" rows="4" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-outline-primary fw-bold px-4">
                    <i class="bi bi-send-fill"></i> Send Reply
                </button>
            </form>
        </div>       
    </div>
</div>
@endsection
