@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Detailed Results</h2>
            <p class="text-muted mb-0">
                {{ $poll->votes->count() }} total {{ Str::plural('vote', $poll->votes->count()) }}
            </p>
        </div>

        <a href="{{ route('polls.index') }}" class="btn btn-sm btn-outline-secondary">
            ← Back to polls
        </a>
    </div>

    {{-- Results --}}
    <div class="vstack gap-4">

        @foreach($poll->options->sortByDesc(fn($o) => $o->votes->count()) as $index => $option)

            @php
                $votes = $option->votes->count();
                $total = max(1, $poll->votes->count());
                $percent = round(($votes / $total) * 100, 2);
            @endphp

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    {{-- Option header --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold fs-5">
                            {{ $option->option_text }}

                            @if($index === 0 && $votes > 0)
                                <span class="badge bg-success ms-2">Top choice</span>
                            @endif
                        </div>

                        <div class="text-muted">
                            {{ $votes }} votes · {{ $percent }}%
                        </div>
                    </div>

                    {{-- Progress --}}
                    @php
    $isUserChoice = $userVote && $userVote->option_id === $option->id;
@endphp

<div class="progress mb-3" style="height: 6px;">
    <div class="progress-bar
                progress-bar-striped
                progress-bar-animated
                {{ $isUserChoice ? 'bg-success' : 'bg-primary' }}"
         role="progressbar"
         style="width: {{ $percent }}%"
         aria-valuenow="{{ $percent }}"
         aria-valuemin="0"
         aria-valuemax="100">
    </div>
</div>


                 {{-- Voters --}}
@if($votes > 0)
    <div class="mt-2 small text-muted">
        <strong>Voted by:</strong>

        @foreach($option->votes->sortBy('created_at') as $vote)
            @if($vote->user)
                <a href="{{ route('profile.show', $vote->user->id) }}"
                   class="text-decoration-none fw-medium"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   title="Voted at {{ $vote->created_at->format('Y-m-d H:i') }}">
                    {{ $vote->user->name }}
                </a>
            @else
                <span class="text-muted"
                      data-bs-toggle="tooltip"
                      title="Anonymous vote at {{ $vote->created_at->format('Y-m-d H:i') }}">
                    Anonymous
                </span>
            @endif

            @if(! $loop->last)
                <span>, </span>
            @endif
        @endforeach
    </div>
@else
    <p class="text-muted mb-0">No votes yet.</p>
@endif


                </div>
            </div>

        @endforeach

    </div>
</div>
@endsection
