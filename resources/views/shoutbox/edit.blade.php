@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="glass p-4">

                <div class="text-center mb-3">
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-pencil-square me-2"></i> Edit Message
                    </h4>
                    <div class="text-muted small">
                        Update your shoutbox message
                    </div>
                </div>

                {{-- Emoji picker --}}
              @include('shoutbox.partials.emoji')




                <form action="{{ route('shoutbox.update', $messages->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="content" class="form-label small text-muted">
                            Message
                        </label>

                        <textarea
                            class="form-control modern-textarea"
                            name="content"
                            id="content"
                            rows="4"
                            required>{{ $messages->message }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>

                        <button type="submit" class="btn btn-success btn-sm px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>

</div>

{{-- Styles --}}
<style>
body {
    background: radial-gradient(circle at top, #2a2a2a, #121212);
    color: #eaeaea;
}

.glass {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.08);
}

.modern-textarea {
    background: #111;
    color: #fff;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.15);
    resize: vertical;
}

.modern-textarea:focus {
    background: #111;
    color: #fff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.15rem rgba(13,110,253,.25);
}

.text-muted {
    color: #aaa !important;
}

.emoji-wrapper {
    margin-bottom: 10px;
}

.emoji-toggle {
    background: none;
    border: none;
    color: #ffc107;
    font-weight: 600;
    cursor: pointer;
    padding: 4px 0;
}

.emoji-picker {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transform: translateY(-6px);
    transition: all .25s ease;
}

.emoji-picker.open {
    max-height: 300px;
    opacity: 1;
    transform: translateY(0);
}

</style>

@endsection
