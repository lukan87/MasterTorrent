@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum-common.css') }}?v={{ filemtime(public_path('css/forum-common.css')) }}">
@endpush
@endonce
