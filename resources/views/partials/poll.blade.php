<div class="mt-5">
    @if($polls->isEmpty())
        <div class="alert alert-info text-center">
            <p>No poll created.</p>
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('polls.create') }}" class="btn btn-primary btn-sm">Create a Poll</a>
            @endif
        </div>
    @else
        @foreach($polls as $poll)
            <div class="card mb-4 shadow-sm rounded-3 border-light">
                <div class="card-header">
                    <h5 class="mb-0">{{ $poll->title }}</h5>
                </div>

                <div class="card-body">
                    <p>{{ $poll->description }}</p>

                    <!-- Display total votes -->
                    <p class="text-muted">Total Votes: {{ $poll->votes->count() }}</p>

                    <!-- Voting Form -->
                    @if(auth()->check() && !$poll->votes()->where('user_id', auth()->id())->exists())
                        <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
                            @csrf
                            <h6>Options:</h6>
                            <ul class="list-unstyled">
                                @foreach($poll->options as $option)
                                    <li>
                                        <input type="radio" id="option{{ $option->id }}" name="option_id" value="{{ $option->id }}" required>
                                        <label for="option{{ $option->id }}">{{ $option->option_text }}</label>
                                    </li>
                                @endforeach
                            </ul>
                            <button type="submit" class="btn btn-primary mt-2">Vote</button>
                        </form>
                    @else
                        <h6>Options:</h6>
                        <ul class="list-unstyled">
                            @foreach($poll->options as $option)
                                <li>
                                    {{ $option->option_text }}
                                    <span class="badge bg-secondary">
                                        {{ $option->votes->count() }} votes
                                        ({{ $poll->votes->count() > 0 ? number_format(($option->votes->count() / $poll->votes->count()) * 100, 2) : 0 }}%)
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-success">You have already voted on this poll.</p>
                    @endif
                </div>

                @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <div class="card-footer text-end">
                        <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-outline-secondary btn-sm">View Poll</a>
                        <a href="{{ route('polls.edit', $poll->id) }}" class="btn btn-info btn-sm ms-2" data-bs-toggle="tooltip" title="Edit Poll">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <form action="{{ route('polls.destroy', $poll->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm ms-2" data-bs-toggle="tooltip" title="Delete Poll"
                                onclick="return confirm('Are you sure you want to delete this poll?');">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                        <a href="{{ route('polls.create') }}" class="btn btn-secondary btn-sm ms-2" data-bs-toggle="tooltip" title="Create Poll">
                            <i class="bi bi-plus-circle"></i> Create
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
