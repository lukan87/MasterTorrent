@extends('layouts.admin')
@section('admin-content')
<div class="container">
    <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary mb-3">Back to messages</a>
    <h1>{{ $message->subject ?: 'Message details' }}</h1>
    <dl><dt>From</dt><dd>{{ $message->sender?->name ?? 'Deleted user' }}</dd>
        <dt>To</dt><dd>{{ $message->receiver?->name ?? 'Deleted user' }}</dd>
        <dt>Sent</dt><dd>{{ $message->created_at }}</dd></dl>
    <div class="card p-3 mb-3" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $message->body }}</div>
    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Permanently delete this message?');">
        @csrf @method('DELETE')
        <button class="btn btn-danger" type="submit">Delete message</button>
    </form>
</div>
@endsection
