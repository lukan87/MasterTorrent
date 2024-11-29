@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-3 text-white">{{ $CollectionDetails['name'] }}</h1>
        <h3 class="mb-4 text-white">{{ $CollectionDetails['overview'] }}</h3>

        @if ($collection->isEmpty())
            <p class="text-white">No movies found in this collection.</p>
        @else
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 g-4">
                @foreach ($collection as $movie)
                    <div class="col">
                    <a href="{{ route('movies.show', $movie->slug) }}"><div class="card shadow-sm border-0 rounded-3 hover-shadow">
                            <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}" class="card-img-top rounded-3" alt="{{ $movie->name }}">
                            <div class="card-body bg-dark text-white rounded-bottom">
                                <h5 class="card-title">{{ $movie->name }}</h5>

                            </div>
                        </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Pagination links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $collection->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection

<style type="text/css">
    body {
        position: relative;
        margin: 0;
        color: white;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: url('https://www.themoviedb.org/t/p/original{{ $CollectionDetails["backdrop_path"] }}');
        background-size: cover;
        background-attachment: fixed;
        background-repeat: no-repeat;
        background-position: center;
        opacity: 0.2;
        z-index: -1;
    }

    /* Hover effect on card */
    .card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    /* Custom shadow on hover */
    .hover-shadow {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .hover-shadow:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }

    /* Button styling */
    .btn-light {
        background-color: #f8f9fa;
        color: #333;
        font-weight: bold;
    }

    .btn-light:hover {
        background-color: #e2e6ea;
    }

    /* Responsive card grid */
    @media (max-width: 768px) {
        .row-cols-md-2 {
            row-gap: 20px;
        }
    }

    /* Headings styling */
    h1, h3 {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }
</style>
