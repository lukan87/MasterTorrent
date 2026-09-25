@extends('layouts.app')

@section('content')

@php
    $gender = [
        0 => 'Not specified',
        1 => 'Female',
        2 => 'Male',
        3 => 'Non-binary'
    ][$person['gender'] ?? 0] ?? 'Not specified';

    $birthday = !empty($person['birthday'])
        ? \Carbon\Carbon::parse($person['birthday'])
        : null;

    $deathday = !empty($person['deathday'])
        ? \Carbon\Carbon::parse($person['deathday'])
        : null;

    $age = $birthday
        ? (int) $birthday->diffInYears($deathday ?? now())
        : null;
@endphp


<div class="actor-page">

    {{-- =====================================================
        BREADCRUMB
    ====================================================== --}}
    <nav class="actor-breadcrumb" aria-label="Breadcrumb">

        <a href="{{ route('library.movies.index') }}">
            <i class="bi bi-film me-1"></i>
            Movie library
        </a>

        <i class="bi bi-chevron-right"></i>

        <span>
            {{ $person['name'] }}
        </span>

    </nav>


    {{-- =====================================================
        HERO
    ====================================================== --}}
    <section class="actor-hero">

        <div class="actor-hero-content">

            <span class="actor-eyebrow">
                THE PEOPLE BEHIND THE STORIES
            </span>

            <h1>
                {{ $person['name'] }}
            </h1>


            <div class="actor-stats">

                <a href="#actor-movies" class="actor-stat">

                    <strong>
                        {{ count($movies) }}
                    </strong>

                    <span>
                        Movies
                    </span>

                </a>


                <a href="#actor-series" class="actor-stat">

                    <strong>
                        {{ count($series) }}
                    </strong>

                    <span>
                        TV series
                    </span>

                </a>


                <a href="#actor-crew" class="actor-stat">

                    <strong>
                        {{ count($crew) }}
                    </strong>

                    <span>
                        Behind the scenes
                    </span>

                </a>

            </div>

        </div>

    </section>


    {{-- =====================================================
        MAIN ROW
    ====================================================== --}}
    <div class="row g-4 align-items-start">


        {{-- =================================================
            LEFT SIDEBAR
        ================================================== --}}
        <div class="col-12 col-md-4 col-lg-3">

            <aside class="actor-sidebar">


                {{-- PORTRAIT --}}
                <div class="actor-portrait">

                    @if($portrait)

                        <img
                            src="{{ $portrait }}"
                            alt="{{ $person['name'] }}"
                            width="342"
                            height="513"
                            fetchpriority="high"
                        >

                    @else

                        <div class="actor-no-photo">

                            <i class="bi bi-person"></i>

                            <span>
                                No portrait available
                            </span>

                        </div>

                    @endif

                </div>


                {{-- PERSONAL INFORMATION --}}
                <section class="actor-panel actor-details">

                    <div class="actor-panel-title">

                        <i class="bi bi-person-lines-fill"></i>

                        <h2>
                            Personal information
                        </h2>

                    </div>


                    <dl>

                        <div class="actor-detail">

                            <dt>
                                Known for
                            </dt>

                            <dd>
                                {{ $person['known_for_department'] ?? 'Not listed' }}
                            </dd>

                        </div>


                        <div class="actor-detail">

                            <dt>
                                Gender
                            </dt>

                            <dd>
                                {{ $gender }}
                            </dd>

                        </div>


                        <div class="actor-detail">

                            <dt>
                                Born
                            </dt>

                            <dd>

                                {{ $birthday?->format('F j, Y') ?? 'Not listed' }}

                                @if($birthday && !$deathday)

                                    <span class="actor-muted">
                                        ({{ $age }} years old)
                                    </span>

                                @endif

                            </dd>

                        </div>


                        @if($deathday)

                            <div class="actor-detail">

                                <dt>
                                    Died
                                </dt>

                                <dd>

                                    {{ $deathday->format('F j, Y') }}

                                    @if($birthday)

                                        <span class="actor-muted">
                                            (aged {{ $age }})
                                        </span>

                                    @endif

                                </dd>

                            </div>

                        @endif


                        <div class="actor-detail">

                            <dt>
                                Place of birth
                            </dt>

                            <dd>
                                {{ ($person['place_of_birth'] ?? null) ?: 'Not listed' }}
                            </dd>

                        </div>


                        <div class="actor-detail">

                            <dt>
                                Departments
                            </dt>

                            <dd>

                                <div class="actor-tags">

                                    @forelse($departments as $department)

                                        <span>
                                            {{ $department }}
                                        </span>

                                    @empty

                                        <span class="actor-muted">
                                            Not listed
                                        </span>

                                    @endforelse

                                </div>

                            </dd>

                        </div>


                        @if(!empty($person['also_known_as']))

                            <div class="actor-detail">

                                <dt>
                                    Also known as
                                </dt>

                                <dd>

                                    <ul class="actor-aliases">

                                        @foreach($person['also_known_as'] as $alias)

                                            <li>
                                                {{ $alias }}
                                            </li>

                                        @endforeach

                                    </ul>

                                </dd>

                            </div>

                        @endif

                    </dl>

                </section>


                {{-- WEB LINKS --}}
                <section class="actor-panel">

                    <div class="actor-panel-title">

                        <i class="bi bi-globe2"></i>

                        <h2>
                            Around the web
                        </h2>

                    </div>


                    <div class="actor-socials">

                        @foreach($links as $label => $url)

                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <span>
                                    {{ $label }}
                                </span>

                                <i class="bi bi-box-arrow-up-right"></i>

                            </a>

                        @endforeach


                        <a
                            href="https://www.themoviedb.org/person/{{ $person['id'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            <span>
                                TMDB profile
                            </span>

                            <i class="bi bi-box-arrow-up-right"></i>

                        </a>

                    </div>


                    @if(!$links)

                        <p class="actor-muted small mt-3 mb-0">
                            No social accounts are listed on TMDB.
                        </p>

                    @endif

                </section>

            </aside>

        </div>


        {{-- =================================================
            RIGHT CONTENT
        ================================================== --}}
        <div class="col-12 col-md-8 col-lg-9">

            <main class="actor-main">


                {{-- =========================================
                    BIOGRAPHY
                ========================================== --}}
                <section class="actor-panel actor-biography">

                    <div class="actor-panel-title">

                        <i class="bi bi-journal-text"></i>

                        <h2>
                            Biography
                        </h2>

                    </div>


                    <div class="actor-bio-text">

                        {{ trim($person['biography'] ?? '') ?: 'A biography is not available on TMDB yet.' }}

                    </div>

                </section>


                {{-- =========================================
                    KNOWN FOR
                ========================================== --}}
                <section class="actor-panel">

                    <div class="actor-section-heading">

                        <div>

                            <div class="actor-panel-title">

                                <i class="bi bi-star-fill"></i>

                                <h2>
                                    Known for
                                </h2>

                            </div>

                            <p>
                                Popular credits, ranked by TMDB vote count
                            </p>

                        </div>


                        <div class="actor-scroll-hint">

                            <i class="bi bi-arrow-left-right"></i>

                            Scroll

                        </div>

                    </div>


                    <div
                        class="actor-known-list"
                        tabindex="0"
                        role="region"
                        aria-label="Known for titles"
                    >

                        @forelse($knownFor as $credit)

                            <a
                                class="actor-known-card"
                                href="{{ route(
                                    $credit['type'] === 'movie'
                                        ? 'library.movies.show'
                                        : 'library.series.show',
                                    ['tmdbid' => $credit['id']]
                                ) }}"
                            >

                                <div class="actor-known-poster">

                                    @if($credit['poster'])

                                        <img
                                            src="{{ $credit['poster'] }}"
                                            alt="{{ $credit['title'] }}"
                                            loading="lazy"
                                            width="154"
                                            height="231"
                                        >

                                    @else

                                        <div class="actor-poster-placeholder">

                                            <i class="bi bi-film"></i>

                                        </div>

                                    @endif

                                </div>


                                <div class="actor-known-info">

                                    <h3>
                                        {{ $credit['title'] }}
                                    </h3>

                                    <span>

                                        {{ $credit['year'] }}

                                        <span class="actor-dot">•</span>

                                        {{ $credit['type'] === 'movie' ? 'Movie' : 'TV' }}

                                    </span>

                                </div>

                            </a>

                        @empty

                            <p class="actor-empty">
                                No credits are available yet.
                            </p>

                        @endforelse

                    </div>

                </section>


                {{-- =========================================
                    MOVIES
                ========================================== --}}
                @include(
                    'actors.partials.credits',
                    [
                        'credits' => $movies,
                        'heading' => 'Movies',
                        'sectionId' => 'actor-movies'
                    ]
                )


                {{-- =========================================
                    TV SERIES
                ========================================== --}}
                @include(
                    'actors.partials.credits',
                    [
                        'credits' => $series,
                        'heading' => 'TV series',
                        'sectionId' => 'actor-series'
                    ]
                )


                {{-- =========================================
                    CREW
                ========================================== --}}
                @include(
                    'actors.partials.credits',
                    [
                        'credits' => $crew,
                        'heading' => 'Behind the scenes',
                        'sectionId' => 'actor-crew'
                    ]
                )


                {{-- =========================================
                    PHOTOS
                ========================================== --}}
                @if($photos)

                    <section class="actor-panel">

                        <div class="actor-section-heading">

                            <div class="actor-panel-title">

                                <i class="bi bi-images"></i>

                                <h2>

                                    Photos

                                    <span class="actor-count">
                                        {{ count($photos) }}
                                    </span>

                                </h2>

                            </div>


                            <div class="actor-scroll-hint">

                                <i class="bi bi-arrow-left-right"></i>

                                Scroll

                            </div>

                        </div>


                        <div
                            class="actor-photo-list"
                            tabindex="0"
                            role="region"
                            aria-label="Profile photos"
                        >

                            @foreach($photos as $photo)

                            

                                    <img
                                        src="{{ $photo }}"
                                        alt="{{ $person['name'] }} — photo {{ $loop->iteration }}"
                                        loading="lazy"
                                        width="120"
                                        height="180"
                                    >

                                

                            @endforeach

                        </div>

                    </section>

                @endif


                {{-- =========================================
                    ATTRIBUTION
                ========================================== --}}
                <p class="actor-attribution">

                    Information and images from

                    <a
                        href="https://www.themoviedb.org/person/{{ $person['id'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        TMDB
                    </a>.

                    This product uses the TMDB API but is not endorsed or certified by TMDB.

                </p>

            </main>

        </div>

    </div>

</div>


@include('actors.partials.styles')

@endsection