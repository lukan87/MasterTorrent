@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum-topic.css') }}?v={{ filemtime(public_path('css/forum-topic.css')) }}">
@endpush
