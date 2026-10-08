@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum-index.css') }}?v={{ filemtime(public_path('css/forum-index.css')) }}">
@endpush
