@if($latestNews->isEmpty())
    <div class="col-12 mb-4 mt-5">
        <div class="alert alert-info text-center">
            <strong>No new news at the moment.</strong>
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('news.create') }}" class="btn btn-primary ms-3">Create News</a>
            @endif
        </div>
    </div>
@else
    @foreach($latestNews as $news)
    <div class="col-md-12 col-lg-12 mb-4 mt-5">
        <div class="card h-100 shadow-sm rounded-3 border-light">
            @if($news->image)
                <img src="{{ asset('storage/' . $news->image) }}" class="card-img-top" alt="News Image" style="object-fit: cover; height: 200px;">
            @endif
            <!-- Card Header with Title -->
            <div class="card-header font-weight-bold">
                {{ $news->title }}
                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('news.create') }}" class="btn btn-secondary btn-sm ms-3" data-bs-toggle="tooltip" title="Create news"><i class="bi bi-file-earmark-plus"></i></a>
            @endif
                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <a href="{{ route('news.edit', $news) }}" class="btn btn-info btn-sm ms-3" data-bs-toggle="tooltip" title="Edit news"><i class="bi bi-pencil-square"></i></a>
                @endif
            </div>
            <!-- Card Body with Content -->
            <div class="card-body">
            <p class="card-text text-muted">
    {!! nl2br(e(Str::words(strip_tags(convertCustomTagsToHtml($news->content)), 150, '...'))) !!}
    <a href="{{ route('news.show', $news->id) }}" class="btn btn-outline-secondary btn-sm">Read More</a>
</p>


                <div class="d-flex justify-content-between align-items-center mt-3">
                    <p class="card-text small text-muted">Posted by {{ $news->user->name }}
                        <i> on {{ $news->created_at->format('F j, Y') }} </i>
                    </p>
                    <!-- <a href="{{ route('news.show', $news) }}" class="btn btn-outline-primary btn-sm">Read More</a> -->
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif
