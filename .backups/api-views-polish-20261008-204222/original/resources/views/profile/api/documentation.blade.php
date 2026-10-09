@extends('layouts.app')
@section('title', 'Upload API Guide')
@section('content')
<div class="card my-4 mx-auto" style="max-width: 1080px"><div class="card-body p-4 p-md-5">
    <a href="{{ route('profile.api.index') }}" class="btn btn-outline-info btn-sm mb-4">← API settings</a>
    <article class="upload-api-guide">{!! $documentation !!}</article>
</div></div>
@endsection
@push('styles')
<style>
.upload-api-guide{line-height:1.7;overflow-wrap:anywhere}.upload-api-guide h1{font-size:1.8rem}.upload-api-guide h2{font-size:1.25rem;margin:2rem 0 1rem}.upload-api-guide pre{padding:1rem;background:var(--theme-surface-secondary,rgba(128,128,128,.08));border-radius:.5rem;overflow:auto}.upload-api-guide table{display:block;max-width:100%;overflow:auto;border-collapse:collapse;margin:1rem 0}.upload-api-guide th,.upload-api-guide td{padding:.65rem;border:1px solid var(--bs-border-color,rgba(128,128,128,.3));vertical-align:top}.upload-api-guide code{font-size:.9em}
</style>
@endpush
