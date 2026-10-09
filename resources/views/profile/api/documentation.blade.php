@extends('layouts.app')
@section('title', 'Upload API Guide')
@include('profile.api.partials.styles')
@section('content')
<div class="api-settings-page">
    @include('profile.api.partials.navigation')
    <div class="card"><div class="card-body p-4 p-md-5">
        <article class="upload-api-guide">{!! $documentation !!}</article>
    </div></div>
</div>
@endsection
