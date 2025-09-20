@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-gradient-primary text-white d-flex align-items-center">
                    <i class="bi bi-envelope-fill me-2 fs-4"></i>
                    <h5 class="mb-0 fw-bold">Send Message to {{ $recipient->name }}</h5>
                </div>

                <div class="card-body p-4">

                    {{-- Success/Error Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $recipient->id }}">

                        {{-- Subject --}}
                        <div class="mb-4">
                            <label for="subject" class="form-label">Subject</label>
                            <input 
                                type="text" 
                                name="subject" 
                                id="subject" 
                                class="form-control @error('subject') is-invalid @enderror" 
                                placeholder="Subject"
                                value="{{ old('subject') }}"
                            >
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div class="mb-4">
                            <label for="body" class="form-label">Message</label>
                            <textarea 
                                name="body" 
                                id="body" 
                                class="form-control @error('body') is-invalid @enderror" 
                                placeholder="Type your message here..." 
                                required
                                rows="6"
                            >{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-gradient btn-lg text-white fw-bold shadow-sm">
                                <i class="bi bi-send-fill me-2"></i>Send Message
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-gradient-primary { background: linear-gradient(135deg,#4e54c8,#8f94fb); }
    .btn-gradient { background: linear-gradient(135deg,#6a11cb,#2575fc); border: none; transition:0.2s; }
    .btn-gradient:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,0.2); }

    .form-control { border-radius:0.5rem; transition:all 0.3s; }
    .form-control:focus { box-shadow:0 0 10px rgba(102,126,234,0.5); border-color:#667eea; }
</style>
@endpush
@endsection
