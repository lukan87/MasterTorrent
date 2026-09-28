@if(!auth()->user()->chatblock && $messages->where('sticky', true)->isNotEmpty())
    <section class="shoutbox-pinned-panel" aria-label="Pinned messages">
        @include('partials.shoutbox-messages', ['messages' => $messages->where('sticky', true)])
    </section>
@endif
