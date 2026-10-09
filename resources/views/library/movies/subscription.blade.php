{{-- Subscribe control --}}
<div class="container px-xl-5 px-lg-4 px-3">
    <div class="library-subscribe-row">

        @if(Auth::check() && ($libraryEntry || $torrents->isNotEmpty()))

            @if($isSubscribed)

                <form data-library-action action="{{ route('library.movies.unsubscribe', $tmdbid) }}" method="POST">
                    @csrf

                    <button type="submit" class="btn subscribe-btn">
                        <i class="bi bi-bell-fill me-1"></i>
                        Unsubscribe
                    </button>
                </form>

            @else

                <form data-library-action action="{{ route('library.movies.subscribe', $tmdbid) }}" method="POST">
                    @csrf

                    <button type="submit" class="btn subscribe-btn">
                        <i class="bi bi-bell me-1"></i>
                        Subscribe
                    </button>
                </form>

            @endif

        @endif

        {{-- Subscribers (count + names) beside the subscribe button --}}
        @include('torrents.partials._subscribers-label', [
            'subscribers' => $subscribers ?? collect()
        ])

    </div>
</div>