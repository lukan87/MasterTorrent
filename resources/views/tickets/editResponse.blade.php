@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Response</h2>

    <form method="POST" action="{{ route('tickets.updateResponse', ['ticket' => $ticket->id, 'response' => $response->id]) }}">
        @csrf
        <div class="form-group">
            <label for="message">Message</label>
            <textarea class="form-control" id="message" name="message" rows="4" required>{{ old('message', $response->message) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Response</button>
        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
@endsection
