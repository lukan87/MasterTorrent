{{-- Subscriber count + names label shown next to the subscribe button.
     Expects: $subscribers (Collection of User models with id + name).      --}}
@php
    if (!isset($subscribers) || $subscribers === null) {
        $subscribers = collect();
    }

    $authId         = Auth::id();
    $authSubscribed = $authId !== null && $subscribers->contains('id', $authId);

    $others = $authSubscribed
        ? $subscribers->reject(fn ($u) => (int) $u->id === (int) $authId)->values()
        : $subscribers;

    $names = $others->pluck('name')
        ->map(fn ($n) => trim((string) $n))
        ->reject(fn ($n) => $n === '')
        ->values();

    $list    = $authSubscribed ? collect(['You'])->concat($names) : $names;
    $total   = $list->count();
    $visible = $list->take(5)->values();
    $extra   = max(0, $total - $visible->count());
@endphp

<span class="subscribers-label" title="{{ $list->implode(', ') }}">
    <i class="bi bi-people-fill"></i>
    <span class="subscribers-count">{{ $total }}</span>
    <span class="subscribers-names">{{ $visible->implode(', ') }}</span>
    @if($extra > 0)
        <span class="subscribers-more">+{{ $extra }}</span>
    @endif
</span>