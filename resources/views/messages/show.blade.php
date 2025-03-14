@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    <div class="card border-0 shadow rounded-4">
        <!-- Card Header (Subject) -->
        <div class="card-header border-bottom p-3">
            <h5 class="fw-bold text-primary mb-0">{{ $message->subject ?? '(No Subject)' }}</h5>
        </div>

        <!-- Card Body (Sender & Message) -->
        <div class="card-body">
            <!-- Sender Info -->
            <div class="d-flex align-items-center mb-4">
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

            <!-- Message Body -->
            <div class="p-4 rounded-3">
                <p class="mb-0 fw-bold6">{!! convertCustomTagsToHtml( $message->body ) !!}</p>
            </div>
        </div>

        <!-- Card Footer (Reply & Delete Buttons) -->
        <div class="border-top d-flex gap-3 p-3">
            @if ($message->receiver_id === Auth::id())
                <a href="{{ route('messages.reply', $message) }}" class="btn btn-outline-primary fw-bold px-4">
                    <i class="bi bi-reply-fill"></i> Reply
                </a>
            @endif
            <form action="{{ route('messages.destroy', $message) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger fw-bold px-4">
                    <i class="bi bi-trash-fill"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
