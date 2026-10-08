@extends('layouts.admin')
@section('admin-content')
<div class="admin-message-page">
    @include('admin.partials.page-header', ['eyebrow' => 'MEMBER MESSAGES', 'title' => $message->subject ?: 'Message details', 'subtitle' => 'Review message content and recipient details.', 'backRoute' => 'admin.messages.index', 'backLabel' => 'Back to messages'])
    @include('messages.mass-pill', ['message' => $message, 'showMassActions' => true, 'showNormal' => true])
    <dl class="admin-message-meta">
        <div><dt>From</dt><dd>{{ $message->sender?->name ?? 'Deleted user' }}</dd></div>
        <div><dt>To</dt><dd>{{ $message->receiver?->name ?? 'Deleted user' }}</dd></div>
        <div><dt>Sent</dt><dd>{{ $message->created_at }}</dd></div>
    </dl>
    <div class="card p-4 mb-4 admin-message-body">{{ $message->body }}</div>
    @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Permanently delete this message?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-2"></i>Delete message</button>
    </form>
    @endif
</div>
@endsection
