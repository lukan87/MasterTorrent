@extends('layouts.app')

@section('content')
    <h1>{{ $poll->title }}</h1>
    <p>{{ $poll->description }}</p>

    <!-- Display Success Message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Display Error Message -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h3>Choose your option:</h3>

    <!-- Poll Options Form -->
    <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
        @csrf

        @foreach($poll->options as $option)
            <div class="form-check">
                <input type="radio" class="form-check-input" name="option_id" value="{{ $option->id }}" id="option_{{ $option->id }}" required>
                <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->option_text }}</label>
            </div>
        @endforeach

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Submit Vote</button>
    </form>
@endsection
