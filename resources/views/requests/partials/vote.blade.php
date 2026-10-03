@if($request->canBeVotedBy(auth()->user()))
<form method="POST" action="{{ route('requests.vote', $request->id) }}" class="rq-vote-form">
    @csrf
    <input type="hidden" name="voted" value="{{ $request->has_voted ? '0' : '1' }}">
    <button type="submit" class="btn rq-vote {{ $request->has_voted ? 'rq-voted' : '' }}" aria-pressed="{{ $request->has_voted ? 'true' : 'false' }}" aria-label="{{ $request->has_voted ? 'Remove your vote for' : 'Vote for' }} {{ $request->name }}">
        <i class="bi bi-arrow-up-circle{{ $request->has_voted ? '-fill' : '' }}" aria-hidden="true"></i>
        <strong>{{ number_format($request->votes_count) }}</strong>
        <span>{{ $request->has_voted ? 'Voted' : 'Vote' }}</span>
    </button>
</form>
@else
    <span class="rq-muted small"><i class="bi bi-arrow-up-circle me-1" aria-hidden="true"></i><strong>{{ number_format($request->votes_count) }}</strong> {{ Str::plural('vote', $request->votes_count) }}</span>
@endif
