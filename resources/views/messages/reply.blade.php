@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-light">
        
        <!-- Card Header -->
        <div class="card-header p-3 bg-gradient-primary d-flex align-items-center text-white">
            <i class="bi bi-chat-dots-fill me-2 fs-4"></i>
            <h5 class="fw-bold mb-0">{{ $message->subject ?? '(No Subject)' }}</h5>
        </div>

        <!-- Chat Body -->
        <div class="card-body p-4" style="max-height: 70vh; overflow-y: auto;">

            <!-- Original Message (Sender) -->
            <div class="d-flex mb-3">
                <img src="{{ $message->sender->profile_image ?? asset('default-avatar.png') }}" 
                     alt="Avatar" class="rounded-circle border border-2 border-light shadow-sm me-3" width="50" height="50">
                <div class="bg-secondary bg-opacity-25 p-3 rounded-4 shadow-sm">
                    <p class="mb-0">{!! convertCustomTagsToHtml($message->body) !!}</p>
                    <small class="text-muted d-block mt-1">{{ $message->created_at->format('d M Y, H:i') }}</small>
                </div>
            </div>

            <!-- Reply Input -->
            <form action="{{ route('messages.storeReply', $message) }}" method="POST" class="mt-4">
                @csrf
                <div class="input-group">
                    <textarea name="body" id="body" class="form-control rounded-pill bg-dark text-light border-secondary" 
                              placeholder="Type your reply..." rows="1" required></textarea>
                    <button class="btn btn-gradient text-white fw-bold ms-2" type="submit">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
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
        background: linear-gradient(135deg,#6a11cb,#2575fc);
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 50px;
        padding: 0.5rem 1.2rem;
    }
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    }
    .card-body p {
        word-wrap: break-word;
        white-space: pre-wrap;
        line-height: 1.5;
    }
    .bg-secondary.bg-opacity-25 {
        background: rgba(255,255,255,0.05);
    }
    textarea.form-control {
        resize: none;
        border-radius: 50px;
        padding: 0.75rem 1rem;
        min-height: 50px;
    }
    .form-control::placeholder {
        color: #aaa;
    }
</style>
@endpush
@endsection
