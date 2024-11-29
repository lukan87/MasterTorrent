@extends('layouts.app')

@section('content')

<!-- Poll Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h3 class="mb-0">{{ $poll->title }}</h3>
    </div>

    <div class="card-body">
        <!-- Show Success Message -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Show Error Message -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <p>{{ $poll->description }}</p>

        <!-- Poll Options -->
        <h4>Poll Options:</h4>
        <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
            @csrf
            @foreach($poll->options as $option)
                <div class="form-check">
                    <input type="radio" class="form-check-input" id="option{{ $option->id }}" name="option_id" value="{{ $option->id }}"
                    @if($userVote && $userVote->option_id == $option->id) disabled @endif
                    required>
                    <label class="form-check-label" for="option{{ $option->id }}"
                    @if($userVote && $userVote->option_id == $option->id) style="color: green;" @endif>
                        {{ $option->option_text }}
                    </label>
                    <!-- Show percentage if user has voted -->
                    @if($userVote)
                        <span class="badge bg-secondary">{{ number_format(($option->votes->count() / $poll->votes->count()) * 100, 2) }}%</span>
                    @endif
                </div>
            @endforeach

            @if(!$userVote)
                <button type="submit" class="btn btn-primary mt-3">Vote</button>
            @else
                <p class="mt-3">You have already voted for this poll.</p>
            @endif
        </form>
    </div>

    <!-- Poll Results (Optional) -->
    <div class="card-footer">
    <h5>Results:</h5>
    <ul class="list-group">
        @foreach($poll->options as $option)
            <li class="list-group-item">
                <strong>{{ $option->option_text }}:</strong>

                <!-- Check if there are votes -->
                @if($option->votes->count() > 0)
                    {{ $option->votes->count() }} votes
                    @if($poll->votes->count() > 0)
                        ({{ number_format(($option->votes->count() / $poll->votes->count()) * 100, 2) }}%)
                    @endif

                    <!-- Display names of users who voted for this option -->
                    <div class="mt-2">
                        <small class="text-muted">Voted by:
                            @foreach($option->votes as $vote)
                            {{ $vote->user?->name ?? 'Unknown' }}@if(!$loop->last),@endif
                            @endforeach
                        </small>
                    </div>
                @else
                    <span class="text-muted">No votes yet</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>


</div>

<!-- Back to Polls List Button -->
<div class="mt-3">
    <a href="{{ route('polls.index') }}" class="btn btn-secondary">Back to Polls List</a>
</div>

@endsection
