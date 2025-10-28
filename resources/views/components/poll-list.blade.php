<div class="mt-5">
    @if($polls->isEmpty())
        <div class="alert alert-info text-center">
            <p class="mb-2">No polls available at this time.</p>
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('polls.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Create New Poll
                </a>
            @endif
        </div>
    @else
        @foreach($pollData as $data)
            @php($poll = $data['poll'])
            @php($userVote = $data['userVote'])
            @php($sortedOptions = $data['sortedOptions'])
            @php($maxVotes = $data['maxVotes'])
            @php($totalVotes = $data['totalVotes'])

            <div class="card mb-4 rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
    <!-- Title on the left -->
    <h5 class="mb-0 fw-bold d-flex align-items-center">
        <i class="bi bi-bar-chart-fill me-2"></i>{{ $poll->title }}
    </h5>

    <!-- Votes and creation date on the far right -->
    <div class="text-end">
        <small class="text-muted d-block">
            Created {{ $poll->created_at->diffForHumans() }}
        </small>
        <h6 class="text-muted fw-bold mb-0">
            {{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }}
        </h6>
    </div>
</div>



                <div class="card-body">
                    @if($poll->description)
                        <div class="alert alert-info mb-4">{{ $poll->description }}</div>
                    @endif

                    @if(auth()->check() && !$userVote)
                        <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
                            @csrf
                            <h6 class="fw-bold mb-3 text-muted">Select your choice:</h6>
                            <div class="list-group mb-4">
                                @foreach($sortedOptions as $option)
                                    <label class="list-group-item list-group-item-action rounded mb-2">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="option_id" value="{{ $option->id }}" class="form-check-input me-3" required>
                                            <span class="fw-medium">{{ $option->option_text }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-4 py-2">
                                <i class="bi bi-check-circle-fill me-2"></i>Submit Vote
                            </button>
                        </form>
                    @else
                        <h6 class="fw-bold mb-3 text-muted">Poll Results:</h6>

                        @if($totalVotes === 0)
                            <div class="alert alert-light text-center small mb-3">No votes yet. Be the first!</div>
                        @endif

                        <div class="list-group mb-3">
                            @foreach($sortedOptions as $option)
                                @php($votesCount = $option->votes->count())
                                @php($percentage = $totalVotes ? number_format(($votesCount / $totalVotes) * 100, 1) : 0)
                                @php($isUserChoice = $userVote && $userVote->option_id === $option->id)

                                <div class="list-group-item border-0 p-0 mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-medium @if($isUserChoice) text-success fw-bold @endif">
                                            {{ $option->option_text }}
                                            @if($isUserChoice)
                                                <i class="bi bi-check-circle-fill me-1" data-bs-toggle="tooltip" title="Your Choice"></i>
                                            @endif
                                        </span>
                                        <span class="text-muted">{{ $votesCount }} ({{ $percentage }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated
                                             @if($isUserChoice) bg-success
                                             @elseif($votesCount === $maxVotes && $maxVotes > 0) bg-info
                                             @else bg-primary @endif"
                                             role="progressbar"
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($userVote)
                            <div class="alert alert-success d-flex align-items-center text-muted small">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <strong>You've already voted in this poll</strong>
                            </div>
                        @endif
                    @endif
                </div>

                @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <form action="{{ route('polls.destroy', $poll->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                <a href="{{ route('polls.create') }}" class="btn btn-outline-success btn-sm" title="Create new">
                                    <i class="bi bi-plus-circle-fill"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>

<style>
.list-group-item { transition: all 0.2s; border: 1px solid #e0e0e0; }
.progress { border-radius: 5px; background-color: #e9ecef; }
.progress-bar { border-radius: 5px; transition: width 0.6s ease; }
.alert-light { background-color: #f8f9fa; border-color: #e9ecef; }
</style>
