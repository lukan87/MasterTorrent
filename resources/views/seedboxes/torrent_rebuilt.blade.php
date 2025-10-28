@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <h3 class="mb-3">Torrent File Rebuilt Successfully</h3>
    <a href="{{ $downloadLink }}" class="btn btn-success btn-lg">
        <i class="bi bi-download"></i> Download Torrent File
    </a>
</div>
@endsection
