@php
    $voters = $request->votes->sortBy(fn ($vote) => (int) $vote->user_id === (int) auth()->id() ? 0 : 1)->values();
    $names = $voters->map(fn ($vote) => (int) $vote->user_id === (int) auth()->id() ? 'You' : ($vote->user?->name ?? 'Former member'));
    $allNames = $voters->map(fn ($vote) => $vote->user?->name ?? 'Former member')->implode(', ');
    $remaining = max(0, $voters->count() - 3);
@endphp
@if($voters->isNotEmpty())
    <p class="rq-voters rq-muted small mt-2 mb-0">
        <span tabindex="0" data-bs-toggle="tooltip" data-bs-html="false" data-bs-title="{{ $allNames }}">{{ $names->take(3)->implode(', ') }}@if($remaining) and {{ $remaining }} {{ Str::plural('other', $remaining) }}@endif voted</span>
    </p>
@else
    <p class="rq-muted small mt-2 mb-0">No votes yet.</p>
@endif
