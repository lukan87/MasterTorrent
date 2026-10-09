@extends('layouts.app')

@push('scripts') @vite('resources/js/page-browser.js') @endpush
@section('content')
<div data-page-browser data-browse-url="{{ route('notifications.index') }}">
    @include('notifications.index-results')
</div>
<style>
.notification-item {
    transition: background-color .2s ease, transform .15s ease;
}

.notification-item:hover {
    background-color: var(--theme-surface-alt, rgba(255,255,255,0.028));
}

.notification-item.unread {
    background: linear-gradient(
        90deg,
        var(--theme-blue-soft, rgba(13,110,253,0.12)),
        var(--theme-blue-soft, rgba(13,110,253,0.02))
    );
}

.notification-item.unread:hover {
    background: linear-gradient(
        90deg,
        var(--theme-blue-soft, rgba(13,110,253,0.18)),
        var(--theme-blue-soft, rgba(13,110,253,0.04))
    );
}

.notification-item button {
    padding: 0;
}

.notification-item em {
    font-style: normal;
    color: var(--theme-blue-text, #9ec5fe);
}

.card {
    backdrop-filter: blur(6px);
}
</style>
@endsection
