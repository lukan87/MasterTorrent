@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Collections</h1>

        @if ($collections->isEmpty())
            <p>No collections found.</p>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">
                @foreach ($collections as $collection)
                <a href="{{ route('collections.show', $collection->collection_id) }}">
                    <div class="col">
                        <div class="card shadow-lg rounded-3 border-0 hover-outline">
                            <img src="https://image.tmdb.org/t/p/original{{ $collectionDetailsList[$collection->collection_id]['backdrop_path'] ?? 'default-image.jpg' }}" class="card-img-top rounded-top" alt="...">
                            <div class="card-body text-white bg-dark">
                                <h5 class="card-title">{{ $collectionDetailsList[$collection->collection_id]['name'] }}</h5><br>
                                <!-- Ensure movie count is on a new line below the name -->
                                <p class="card-text">
                                    <small class="text-muted">({{ $collection->total }} movies)</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <!-- Pagination links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $collections->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection

<style type="text/css">
    /* Hover effect: outline-colored */
    .hover-outline {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border 0.3s ease;
    }

    .hover-outline:hover {
        transform: translateY(-5px); /* Lift the card */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Soft shadow */
        border: 2px solid #ff5722; /* Colored outline on hover (can change color) */
    }

    .hover-outline img {
        transition: transform 0.3s ease;
    }

    .hover-outline:hover img {
        transform: scale(1.05); /* Slight zoom effect on the image */
    }

    /* Additional card styling */
    .card-body {
        background-color: #343a40; /* Dark background */
    }

    .card-body a {
        color: #fff;
    }

    .card-body a:hover {
        color: #ff5722; /* Highlight color on hover */
    }
</style>
