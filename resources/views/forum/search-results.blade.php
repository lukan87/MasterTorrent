<div class="container forum-page py-4 py-lg-5">
    <nav class="forum-breadcrumb mb-4" aria-label="Breadcrumb"><a class="forum-breadcrumb-link" href="{{ route('forum.index') }}">Forum</a><span aria-hidden="true">/</span><span>Search</span></nav>
    <header class="forum-category-header mb-4"><h1 class="forum-category-title">Forum Search</h1></header>
    <form method="GET" action="{{ route('forum.search') }}" class="mb-4" role="search">
        <label for="forum-search-query" class="form-label">Search topics and posts</label>
        <div class="forum-search-bar"><input id="forum-search-query" type="search" name="q" class="form-control" minlength="2" maxlength="200" value="{{ $query }}" required><button class="btn btn-search" type="submit">Search</button></div>
    </form>
    @error('q')<p class="text-danger" role="alert">{{ $message }}</p>@enderror
    @if(mb_strlen($query) >= 2)
        <p class="forum-rank-note">{{ number_format($results->total()) }} results for “{{ $query }}” · Best matches first</p>
        <div class="forum-topic-list">
            @forelse($results as $result)
                @if($result instanceof \App\Models\ForumTopic && $result->category)
                    @include('forum.partials.topic-row', ['topic' => $result, 'category' => $result->category, 'showCategory' => true])
                @elseif($result instanceof \App\Models\ForumPost && $result->topic?->category)
                    <article class="forum-topic-row"><div class="forum-topic-icon" aria-hidden="true"><i class="bi bi-chat-left-text"></i></div><div class="forum-topic-copy">
                        <a class="forum-topic-link" href="{{ route('forum.topic', ['category' => $result->topic->category->slug, 'topic' => $result->topic->slug, 'post' => $result->id]) }}">Post in {{ $result->topic->title }}</a>
                        <p class="forum-search-snippet">{{ \App\Services\ForumService::snippet($result->body, $query) }}</p>
                        <div class="forum-topic-started">{{ $result->user?->name ?? 'Former member' }} · {{ $result->topic->category->name }} · {{ $result->created_at->diffForHumans() }}</div>
                    </div></article>
                @endif
            @empty<p class="forum-empty-state">No results found. Try another phrase.</p>@endforelse
        </div>
        @if($results->hasPages())<div class="forum-pagination mt-4">{{ $results->links('pagination::bootstrap-5') }}</div>@endif
    @else<p class="forum-empty-state">Enter at least two characters to search.</p>@endif
</div>
