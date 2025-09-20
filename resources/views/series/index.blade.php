@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold">📺 Series</h1>
    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
        <a href="{{ route('series.create') }}" class="btn btn-primary shadow-sm">
            ➕ Add Series
        </a>
    @endif
</div>

{{-- Search Bar --}}
<div class="card shadow-sm mb-5 border-0">
    <div class="card-body">
        <form action="{{ route('series.search-series') }}" method="POST" class="row g-2 align-items-center">
            @csrf
            <div class="col-md-10">
                <input type="text" name="name" id="name" class="form-control form-control-lg" 
                       required placeholder="🔍 Search for a Series...">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-lg btn-primary">Search</button>
            </div>
        </form>
    </div>
</div>

{{-- Pagination (Top) --}}
<div class="d-flex justify-content-center mb-4">
    {{ $series->links('pagination::bootstrap-5') }}
</div>

{{-- Series Grid --}}
<div class="row g-4">
    @forelse ($series as $serie)
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm border-0 series-card position-relative overflow-hidden">
                <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}" 
                     class="card-img-top w-100" 
                     alt="{{ $serie->name }}">

                {{-- Overlay --}}
                <div class="overlay d-flex flex-column justify-content-end text-white p-3">
                    <h6 class="fw-bold text-truncate mb-3">{{ $serie->name }}</h6>

                    <div class="d-flex flex-wrap gap-2">
                        {{-- View Details Button (opens modal) --}}
                        <button type="button" 
                                class="btn btn-sm btn-light" 
                                data-bs-toggle="modal" 
                                data-bs-target="#seriesModal{{ $serie->id }}">
                            <i class="bi bi-info-circle"></i> View Details
                        </button>

                        {{-- Delete Button for Admins --}}
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)
                            <form action="{{ route('series.destroy', $serie->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete {{ $serie->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div class="modal fade" id="seriesModal{{ $serie->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body p-0">
                        <div class="row g-0">
                            {{-- Poster --}}
                            <div class="col-md-4">
                                <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}" 
                                     class="img-fluid rounded-start" 
                                     alt="{{ $serie->name }}">
                            </div>
                            {{-- Info --}}
                            <div class="col-md-8 p-4">
                                <h4 class="fw-bold">{{ $serie->name }}</h4>
                                
                                <p class="text-secondary">
                                    {{ Str::limit($serie->overview ?? 'No description available.', 350) }}
                                </p>
                                <div class="mt-3 d-flex gap-2">
                                    <a href="{{ route('series.show', ['id' => $serie->id, 'slug' => $serie->slug]) }}" 
                                       class="btn btn-primary">
                                        Open Full Page
                                    </a>
                                    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)
                                        <form action="{{ route('series.destroy', $serie->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete {{ $serie->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @empty
        <div class="col-12">
            <div class="alert alert-danger text-center shadow-sm">🚨 No series found.</div>
        </div>
    @endforelse
</div>

{{-- Pagination (Bottom) --}}
<div class="d-flex justify-content-center mt-4">
    {{ $series->links('pagination::bootstrap-5') }}
</div>

{{-- Custom CSS --}}
<style>
    .series-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        border-radius: 10px;
    }
    .series-card:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 30px rgba(0,0,0,0.3);
    }
    .series-card .overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.2));
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }
    .series-card:hover .overlay {
        opacity: 1;
    }
    .overlay h6 {
        color: #fff;
    }
</style>

@endsection
