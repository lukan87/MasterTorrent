@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Send Message to {{ $recipient->name }}</h3>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Send Message Form --}}
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $recipient->id }}">

        {{-- Subject Field --}}
        <div class="form-group">
            <input type="text" name="subject" class="form-control" placeholder="Subject" maxlength="100">
        </div>

        {{-- Message Body --}}
        <div class="form-group mt-3">
            <textarea name="body" class="form-control" placeholder="Type your message here..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Send Message</button>
    </form>
</div>
@endsection
