<div class="mt-5">

    @foreach($polls as $poll)
    <div class="card col-md-12 col-lg-12 mb-4 mt-5 shadow-sm rounded-3 border-light">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $poll->title }}</h4>
                @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('polls.create') }}">
                    <i class="bi bi-plus-circle" data-bs-toggle="tooltip" title="Add new poll"></i>
                </a>
                  @endif
            </div>

            <div class="card-body">
                <p>{{ $poll->description }}</p>

                <!-- Display total votes -->
                <p class="text-muted">Total Votes: {{ $poll->votes->count() }}</p>

                <!-- Voting Form -->
                @if(auth()->check() && !$poll->votes()->where('user_id', auth()->id())->exists())
                    <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
                        @csrf
                        <h5>Options:</h5>
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
                    <h5>Options:</h5>
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
                <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-outline-secondary">View Poll Details</a>
            </div>
            @endif
        </div>
    @endforeach
</div>
