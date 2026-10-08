@extends('layouts.app')

@section('title', $seriesDetails['name'] ?? $series->name)

@section('content')

<div class="series-page media-detail-page">

    <div class="container-fluid px-lg-5 px-3 position-relative">

    {{-- UBLOCK ORIGIN NOTICE --}}
    <div class="ublock-notice" role="alert">

        <div class="ublock-notice-icon">
            <i class="bi bi-shield-check"></i>
        </div>

        <div class="ublock-notice-content">

            <div class="ublock-notice-title">
                Watch without ads
            </div>

            <div class="ublock-notice-text">
                Use <strong>uBlock Origin</strong> to help block ads and unwanted content while watching.
            </div>

        </div>

        <a href="https://ublockorigin.com/"
           target="_blank"
           rel="noopener noreferrer"
           class="ublock-notice-button">

            <i class="bi bi-box-arrow-up-right"></i>
            Get uBlock Origin

        </a>

    </div>

        @include('media.detail-hero', ['media' => $series, 'mediaType' => 'tv', 'details' => $seriesDetails, 'metadata' => $seriesOm ?? []])

        @include('media.recommendations', ['mediaType' => 'tv'])

        {{-- SEASONS --}}
        <div id="series-seasons">@include('series.seasons')</div>

        {{-- ONLINE --}}
        @include('series.online')


         @include('comments.discussion', ['commentTarget' => $series, 'commentType' => \App\Models\Series::class])

    </div>

</div>

@include('series.partials.showstyle')

@endsection


@push('styles')
<link rel="stylesheet" href="{{ asset('css/media-details.css') }}?v={{ filemtime(public_path('css/media-details.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/media-details.js') }}?v={{ filemtime(public_path('js/media-details.js')) }}" defer></script>
@endpush
