@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Poll Details</h2>
            <a href="{{ route('polls.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Polls
            </a>
        </div>

        <!-- Poll Options Card -->
        <div class="col-md-6">
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="bi bi-list-check me-2"></i>Poll Options
                        </h3>
                        <span class="badge bg-white text-primary">
                            {{ $poll->votes->count() }} {{ Str::plural('vote', $poll->votes->count()) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">

                    @if($poll->description)
                        <div class="alert alert-light mb-4">
                            <p class="mb-0"><strong>Description:</strong> {{ $poll->description }}</p>
                        </div>
                    @endif

                    <!-- Voting Form -->
                    <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
                        @csrf
                        <div class="list-group">
                            @foreach($poll->options->sortByDesc(function($option) {
                                return $option->votes->count();
                            }) as $option)
                                <label class="list-group-item list-group-item-action rounded mb-2 border">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" 
                                               class="form-check-input me-3" 
                                               id="option{{ $option->id }}" 
                                               name="option_id" 
                                               value="{{ $option->id }}"
                                               @if($userVote && $userVote->option_id == $option->id) checked disabled @endif
                                               required>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <span class="fw-medium @if($userVote && $userVote->option_id == $option->id) text-success fw-bold @endif">
                                                {{ $option->option_text }}
                                                @if($userVote && $userVote->option_id == $option->id)
                                                    <span class="badge bg-success ms-2">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Your choice
                                                    </span>
                                                @endif
                                            </span>
                                            @if($userVote)
                                                <small class="text-muted mt-1">
                                                    {{ number_format(($option->votes->count() / $poll->votes->count()) * 100, 2) }}% 
                                                    ({{ $option->votes->count() }} votes)
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @if(!$userVote)
                            <button type="submit" class="btn btn-primary mt-3 px-4 py-2">
                                <i class="bi bi-check-circle-fill me-2"></i>Submit Vote
                            </button>
                        @else
                            <div class="alert alert-success mt-3 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                You have already voted in this poll.
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Detailed Results Card -->
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h3 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Detailed Results
                    </h3>
                </div>

                <div class="card-body">
                    <div class="list-group">
                        @foreach($poll->options->sortByDesc(function($option) {
                            return $option->votes->count();
                        }) as $option)
                            <div class="list-group-item border-0 p-0 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-medium">
                                        {{ $option->option_text }}
                                        @if($userVote && $userVote->option_id == $option->id)
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-check-circle-fill me-1"></i>Your vote
                                            </span>
                                        @endif
                                    </span>
                                    <span class="text-muted">
                                        {{ $option->votes->count() }} votes
                                        @if($poll->votes->count() > 0)
                                            ({{ number_format(($option->votes->count() / $poll->votes->count()) * 100, 2) }}%)
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar 
                                        @if($userVote && $userVote->option_id == $option->id) bg-success
                                        @else bg-primary @endif" 
                                        role="progressbar" 
                                        style="width: {{ $poll->votes->count() > 0 ? ($option->votes->count() / $poll->votes->count()) * 100 : 0 }}%" 
                                        aria-valuenow="{{ $option->votes->count() }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="{{ $poll->votes->count() }}">
                                    </div>
                                </div>
                                
                                @if($option->votes->count() > 0)
    <div class="d-flex flex-wrap gap-1">
        <small class="text-muted">Voters: </small>
        @foreach($option->votes as $vote)
            <span class="strong">
                {{ $vote->user?->name ?? 'Anonymous' }}@if(!$loop->last),@endif
            </span>
        @endforeach
    </div>
@else
    <small class="text-muted">No votes yet</small>
@endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #5d5d5e 0%, #212425 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #5d5d5e 0%, #212425 100%);
    }
    
    .card {
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .list-group-item {
        transition: all 0.2s;
    }
    
    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .badge.bg-info {
        color: #000 !important;
        background-color: rgba(13, 110, 253, 0.2) !important;
        border: 1px solid rgba(13, 110, 253, 0.5);
    }
    
    .badge.bg-success {
        background-color: rgba(25, 135, 84, 0.9) !important;
    }
</style>
@endsection