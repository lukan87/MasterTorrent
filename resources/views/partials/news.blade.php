<div class="mt-5">
    @if($latestNews->isEmpty())
        <div class="alert alert-info text-center">
            <strong>No news at the moment.</strong>
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('news.create') }}" class="btn btn-primary btn-sm ms-2">Create News</a>
            @endif
        </div>
    @else
        @foreach($latestNews as $news)
            <div class="card mb-4 shadow-sm rounded-3 border-light">
                @if($news->image)
                    <img src="{{ asset('storage/' . $news->image) }}" class="card-img-top" alt="News Image" style="object-fit: cover; height: 200px;">
                @endif

                <div class="card-header">
                    <h5 class="mb-0">{{ $news->title }}</h5>
                </div>

                <div class="card-body">
                    <p class="card-text">
                        @php
                            $fullContent = convertCustomTagsToHtml($news->content);
                            $truncatedContent = Str::words(strip_tags($fullContent), 50, '...');
                            $isTruncated = str_word_count(strip_tags($fullContent)) > 50;
                        @endphp
                        {!! $truncatedContent !!}
                        @if($isTruncated)
                            <a href="{{ route('news.show', $news->id) }}" class="btn btn-outline-primary btn-sm">Read More</a>
                        @endif
                    </p>

                    <div class="d-flex justify-content-between align-items-center">
                        <p class="small text-muted mb-0">
                            Posted by <strong>{{ $news->user->name }}</strong>
                            <i>on {{ $news->created_at->format('F j, Y') }}</i>
                        </p>
                    </div>
                </div>

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <div class="card-footer text-end">
                        <a href="{{ route('news.create') }}" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Create News">
                            <i class="bi bi-file-earmark-plus"></i> Create
                        </a>
                        <a href="{{ route('news.edit', $news) }}" class="btn btn-info btn-sm ms-2" data-bs-toggle="tooltip" title="Edit News">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
