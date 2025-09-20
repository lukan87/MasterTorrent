@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-light">
        
        <!-- Card Header (Subject) -->
        <div class="card-header p-3 bg-gradient-primary d-flex align-items-center">
            <i class="bi bi-envelope-fill me-2 fs-4"></i>
            <h5 class="fw-bold mb-0">{{ $message->subject ?? '(No Subject)' }}</h5>
        </div>

        <!-- Card Body (Sender & Message) -->
        <div class="card-body p-4">

            <!-- Sender Info -->
            <div class="d-flex align-items-center mb-4">
                <img src="{{ $message->sender->profile_image ?? asset('default-avatar.png') }}" alt="Avatar" 
                    class="rounded-circle border border-2 border-light shadow-sm me-3" width="60" height="60">
                <div>
                    <h6 class="fw-bold mb-0">
                        <a href="{{ route('profile.show', $message->sender->id) }}" class="text-decoration-none text-white">
                            {{ $message->sender->name }}
                        </a>
                    </h6>
                    <small class="text-muted">{{ $message->created_at->format('d M Y, H:i') }}</small>
                </div>
            </div>

            <!-- Message Body -->
            <div class="p-4 rounded-4 bg-secondary bg-opacity-25 shadow-sm">
                <p class="mb-0" style="line-height:1.6; font-size:1rem;">
                    {!! convertCustomTagsToHtml($message->body) !!}
                </p>
            </div>
        </div>

        <!-- Card Footer (Reply & Delete Buttons) -->
        <div class="card-footer border-0 d-flex gap-3 p-3 bg-dark">
            @if ($message->receiver_id === Auth::id())
                <a href="{{ route('messages.reply', $message) }}" class="btn btn-gradient fw-bold px-4 text-white">
                    <i class="bi bi-reply-fill me-2"></i> Reply
                </a>
            @endif
            <form action="{{ route('messages.destroy', $message) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger fw-bold px-4">
                    <i class="bi bi-trash-fill me-2"></i> Delete
                </button>
            </form>
        </div>

    </div>
</div>

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg,#4e54c8,#8f94fb);
    }
    .btn-gradient {
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    }
    .card-body img {
        object-fit: cover;
    }
    .card-body p {
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .bg-secondary.bg-opacity-25 {
        background: rgba(255,255,255,0.1);
    }
</style>
@endpush
@endsection
