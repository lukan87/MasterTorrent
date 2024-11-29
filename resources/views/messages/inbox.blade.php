@extends('layouts.app')

@section('content')
<div class="container">

<h1>Inbox</h1>
@if($messages->isEmpty())
        <div class="alert alert-info">
            No messages have been received yet.
        </div>
    @else
    <div class="list-group">
@foreach($messages as $message)
    <div>
        <a href="{{ route('messages.show', $message) }}">
            {{ $message->subject ?? '(No Subject)' }} - {{ $message->sender->name }}
        </a>
        @if(!$message->is_read) <strong>New</strong> @endif
    </div>
@endforeach
</div>
@endif
</div>
@endsection
