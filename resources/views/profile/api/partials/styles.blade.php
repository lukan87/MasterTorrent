@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/upload-api.css') }}?v={{ filemtime(public_path('css/upload-api.css')) }}">
@endpush
@endonce
