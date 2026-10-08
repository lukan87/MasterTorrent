@extends('layouts.app')

@section('title', $torrent->name)

@if($torrent->tmdbid && !$display)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/torrent-media-hero.css') }}?v={{ filemtime(public_path('css/torrent-media-hero.css')) }}">
@endpush
@endif

@push('scripts')
    @vite('resources/js/torrent-detail.js')
@endpush

@section('content')

    <div class="container-fluid">

        {{-- Deleted Info Box --}}
        @include('torrents.partials.deleted-info')


        {{-- =========================
        HEADER (Movie/TV/Game)
    ========================== --}}
        @if ($torrent->tmdb_type === 'movie' && $display)
            @include('torrents.partials.movie')
        @elseif($torrent->tmdb_type === 'tv' && $display)
            @include('torrents.partials.tv')
        @elseif($torrent->steamid && $steamData)
            @include('torrents.partials.game')
        @elseif($torrent->tmdbid || $torrent->steamid)
            <div data-torrent-metadata data-url="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}" aria-busy="true">
                <div class="d-flex justify-content-center align-items-center py-5 mb-4" role="status">
                    <span class="spinner-border text-info" aria-hidden="true"></span>
                    <span class="visually-hidden">Loading title details…</span>
                </div>
            </div>
        @endif


        @if (Auth::user()->user_class < \App\Models\UserClass::VIP)
        @endif


        {{-- =========================
        SHOW BAR
        Hidden if deleted
    ========================== --}}
        @if (!$torrent->trashed())
            @include('torrents.partials.showbar')
            @include('torrents.partials.sticky-toolbar')
        @endif


        {{-- =========================================
        DETAILS SECTION
    ========================================= --}}
        @if (!$torrent->trashed())

            <div class="modern-tabs-card mb-4">


                {{-- =====================================
                TABS HEADER
            ====================================== --}}
                <div class="modern-tabs-header">

                    <ul class="nav modern-tabs-nav" role="tablist">

                        {{-- DESCRIPTION --}}
                        <li class="nav-item">

                            <a class="nav-link active" data-bs-toggle="tab" id="description-tab" role="tab"
                                aria-controls="description" aria-selected="true"
                                title="Release notes, details and additional information" href="#description">
                                <i class="bi bi-card-text me-2"></i>

                                Description
                            </a>

                        </li>


                        {{-- =====================================
                        SUBTITLES
                    ====================================== --}}

                    @if ($torrent->subtitles->isNotEmpty() || !empty($externalSubtitles['items']))

                        <li class="nav-item">

                            <a
                                class="nav-link"
                                data-bs-toggle="tab"
                                id="subtitles-tab"
                                role="tab"
                                aria-controls="subtitles"
                                aria-selected="false"
                                href="#subtitles"
                            >
                                <i class="bi bi-badge-cc-fill me-2"></i>

                                Subtitles
                            </a>

                        </li>

                    @endif


                        {{-- SNATCHED --}}
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                            <li class="nav-item">

                                <a class="nav-link" href="{{ route('torrents.snatched', ['id' => $torrent->id]) }}">
                                    <i class="bi bi-person-check-fill me-2"></i>

                                    Snatched
                                </a>

                            </li>
                        @endif

                    </ul>

                </div>



                {{-- =====================================
                TABS BODY
            ====================================== --}}
                <div class="modern-tabs-body">

                    <div class="tab-content">


                        {{-- =====================================
                        DESCRIPTION
                    ====================================== --}}
                        <div class="tab-pane fade show active" id="description" role="tabpanel"
                            aria-labelledby="description-tab">


                            @php

                                $descriptionHtml = convertCustomTagsToHtml($torrent->description);

                                /*
                                 * Plain text is only used to
                                 * determine whether the description
                                 * needs expanding.
                                 */
                                $descriptionText = trim(preg_replace('/\s+/', ' ', strip_tags($descriptionHtml)));

                                /*
                                 * Long description threshold.
                                 *
                                 * Change 500 to 1000 if you want
                                 * more content visible before the
                                 * expand system is enabled.
                                 */
                                $hasLongDescription = mb_strlen($descriptionText) > 500;

                            @endphp


                            <div class="modern-description-card">


                                {{-- =====================================
                                DESCRIPTION BODY
                            ====================================== --}}
                                <div class="modern-description-body">

                                    <div class="description-expand-wrapper
                                    {{ $hasLongDescription ? 'is-collapsed' : '' }}"
                                        id="torrentDescriptionWrapper">


                                        {{-- DESCRIPTION CONTENT --}}
                                        <div class="scrollable-content
                                               modern-scrollable-content
                                               description-expand-content"
                                            id="torrentDescriptionContent">

                                            {!! $descriptionHtml !!}

                                        </div>


                                        @if ($hasLongDescription)
                                            {{-- =====================================
                                            BOTTOM FADE
                                        ====================================== --}}
                                            <div class="description-fade" id="torrentDescriptionFade" aria-hidden="true">
                                            </div>


                                            {{-- =====================================
                                            OPEN BUTTON
                                        ====================================== --}}
                                            <button type="button" class="description-open-btn"
                                                id="torrentDescriptionToggle" aria-expanded="false"
                                                aria-controls="torrentDescriptionContent" title="Show full description"
                                                aria-label="Show full description">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                        @endif

                                    </div>

                                    @if ($hasLongDescription)
                                        <button type="button" class="description-header-close ms-auto mt-2"
                                            id="torrentDescriptionClose" title="Collapse description"
                                            aria-label="Collapse description" aria-controls="torrentDescriptionContent">
                                            <i class="bi bi-chevron-up"></i>
                                        </button>
                                    @endif

                                    @if ($torrent->images->isNotEmpty())
                                        <div class="mt-3">
                                            @include('torrents.partials.screens')
                                        </div>
                                    @endif

                                    @if (!empty($torrent->mediainfo) && !empty($mediainfo))
                                        <div class="mt-3">
                                            @include('torrents.partials.mediainfo')
                                        </div>
                                    @endif

                                </div>

                            </div>

                        </div>



                        {{-- =====================================
                        SUBTITLES
                    ====================================== --}}

                    @if ($torrent->subtitles->isNotEmpty() || !empty($externalSubtitles['items']))

                        <div
                            class="tab-pane fade"
                            id="subtitles"
                            role="tabpanel"
                            aria-labelledby="subtitles-tab"
                            tabindex="0"
                        >

                            <div class="modern-subtitles-card mt-3">


                                <div class="modern-subtitles-header">

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="subtitle-icon-box">

                                            <i class="bi bi-badge-cc-fill"></i>

                                        </div>


                                        <div>

                                            <h4 class="modern-subtitle-title mb-1">

                                                Available Subtitles

                                            </h4>


                                            <div class="modern-subtitle-subtitle">

                                                Local uploads and external subtitle sources

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <div class="modern-subtitles-body">


                                    @foreach ($torrent->subtitles as $sub)

                                        @php

                                            $flags = [
                                                'english'  => '🇬🇧',
                                                'romanian' => '🇷🇴',
                                                'italian'  => '🇮🇹',
                                                'french'   => '🇫🇷',
                                                'spanish'  => '🇪🇸',
                                            ];

                                            $flag =
                                                $flags[
                                                    strtolower(
                                                        $sub->language ?? ''
                                                    )
                                                ]
                                                ?? '🏳️';

                                        @endphp


                                        <div class="subtitle-item">

                                            <div class="subtitle-left">

                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                                    <span class="modern-source-badge local-badge">

                                                        LOCAL

                                                    </span>


                                                    <strong>

                                                        {{ $flag }}

                                                        {{ strtoupper($sub->language ?? 'N/A') }}

                                                    </strong>

                                                </div>


                                                <div class="subtitle-file-name">

                                                    {{ $sub->original_name }}

                                                </div>


                                                <div class="subtitle-meta">

                                                    Uploaded by

                                                    <a
                                                        href="{{ route('profile.show', [
                                                            'id' => $sub->uploaded_by,
                                                            'name' => $sub->uploader->name ?? 'Unknown'
                                                        ]) }}"
                                                    >

                                                        {{ $sub->uploader->name ?? 'Unknown' }}

                                                    </a>

                                                    • {{ $sub->created_at->diffForHumans() }}

                                                </div>

                                            </div>


                                            <div class="subtitle-actions">

                                                <a
                                                    href="{{ route('subtitles.download', $sub) }}"
                                                    class="btn modern-subtitle-btn"
                                                >

                                                    <i class="bi bi-download me-1"></i>

                                                    Download

                                                </a>


                                                @if (auth()->id() === $sub->uploaded_by || auth()->user()->user_class >= \App\Models\UserClass::MODERATOR)

                                                    <form
                                                        method="POST"
                                                        action="{{ route('subtitles.destroy', $sub) }}"
                                                        onsubmit="return confirm('Delete this subtitle?')"
                                                    >

                                                        @csrf

                                                        @method('DELETE')


                                                        <button
                                                            type="submit"
                                                            class="btn modern-delete-btn"
                                                        >

                                                            <i class="bi bi-trash"></i>

                                                        </button>

                                                    </form>

                                                @endif

                                            </div>

                                        </div>

                                    @endforeach



                                    @if (!empty($externalSubtitles['items']))

                                        @foreach ($externalSubtitles['items'] as $sub)

                                            <div class="subtitle-item external-subtitle">

                                                <div class="subtitle-left">

                                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                                        <span class="modern-source-badge external-badge">

                                                            SUBS.RO

                                                        </span>


                                                        <strong>

                                                            {{ strtoupper($sub['language'] ?? 'N/A') }}

                                                        </strong>


                                                        @if (!empty($sub['year']))

                                                            <span class="subtitle-year">

                                                                {{ $sub['year'] }}

                                                            </span>

                                                        @endif

                                                    </div>


                                                    <div class="subtitle-file-name">

                                                        {{ $sub['title'] ?? 'Unknown Subtitle' }}

                                                    </div>


                                                    @if (!empty($sub['description']))

                                                        <div class="external-description">

                                                            {!! $sub['description'] !!}

                                                        </div>

                                                    @endif


                                                    <div class="subtitle-meta">

                                                        @if (!empty($sub['translator']))

                                                            By {{ $sub['translator'] }}

                                                        @endif


                                                        @if (!empty($sub['type']))

                                                            • {{ ucfirst($sub['type']) }}

                                                        @endif

                                                    </div>

                                                </div>


                                                <div class="subtitle-actions">

                                                    @if (!empty($sub['downloadPage']))

                                                        <a
                                                            href="{{ $sub['downloadPage'] }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="btn modern-subtitle-btn external-download-btn"
                                                        >

                                                            <i class="bi bi-download me-1"></i>

                                                            Download

                                                        </a>

                                                    @endif

                                                </div>

                                            </div>

                                        @endforeach

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endif





                    </div>

                </div>

            </div>

        @endif



        {{-- =========================
        COMMENTS
    ========================== --}}
        @if (!$torrent->trashed())
            @include('torrents.partials.comments')
        @endif



        {{-- =========================
        SIMILAR TORRENTS
    ========================== --}}
        @if (!$torrent->trashed())
            @include('torrents.partials.similar')
        @endif


    </div>



    {{-- =========================
    FILES MODAL
========================== --}}
    @include('torrents.partials.modalfilesrender')



    {{-- =========================
    EXISTING SHOW CSS
========================== --}}
    @include('torrents.partials.css.show-css')



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionTab = document.getElementById('description-tab');
            if (descriptionTab) {
                bootstrap.Tooltip.getOrCreateInstance(descriptionTab, {
                    container: 'body',
                    placement: 'top',
                    trigger: 'hover focus'
                });
            }



            const wrapper =
                document.getElementById(
                    'torrentDescriptionWrapper'
                );


            const openButton =
                document.getElementById(
                    'torrentDescriptionToggle'
                );


            const closeButton =
                document.getElementById(
                    'torrentDescriptionClose'
                );


            /*
             * Short descriptions will not have
             * expand/collapse buttons.
             */
            if (
                !wrapper ||
                !openButton ||
                !closeButton
            ) {
                return;
            }



            /* =====================================================
               OPEN DESCRIPTION
               ===================================================== */

            openButton.addEventListener(
                'click',
                function() {


                    /*
                     * Expand the description.
                     */
                    wrapper.classList.remove(
                        'is-collapsed'
                    );


                    /*
                     * Update accessibility state.
                     */
                    openButton.setAttribute(
                        'aria-expanded',
                        'true'
                    );


                    /*
                     * Show UP arrow immediately
                     * below the expanded description.
                     */
                    closeButton.classList.add(
                        'show'
                    );


                }
            );



            /* =====================================================
               CLOSE DESCRIPTION
               ===================================================== */

            closeButton.addEventListener(
                'click',
                function() {


                    /*
                     * Collapse description.
                     */
                    wrapper.classList.add(
                        'is-collapsed'
                    );


                    /*
                     * Update accessibility state.
                     */
                    openButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );


                    /*
                     * Hide the collapse arrow.
                     */
                    closeButton.classList.remove(
                        'show'
                    );


                    /*
                     * Bring description back into view.
                     */
                    wrapper.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });


                }
            );


        });
    </script>

@endsection
