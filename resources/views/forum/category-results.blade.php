<div class="container forum-page py-4 py-lg-5">
    <nav class="forum-breadcrumb mb-4" aria-label="Breadcrumb"><a class="forum-breadcrumb-link" href="{{ route('forum.index') }}">Forum</a><span aria-hidden="true">/</span><span>{{ $category->name }}</span></nav>
    <header class="forum-category-header mb-4">
        <div class="flex-grow-1"><h1 class="forum-category-title">{{ $category->name }}</h1><p class="forum-category-description">{{ $category->description }}</p>@if($category->is_private)<span class="forum-topic-badge locked">Staff only</span>@endif</div>
        <div class="d-flex flex-wrap gap-2">
            @if(\App\Services\ForumAccess::canParticipate(auth()->user()) && \App\Services\ForumAccess::allows(auth()->user(), 'create_topics'))
                <a class="btn forum-new-topic-btn" href="{{ route('forum.topic.create', $category->slug) }}">New Topic</a>
            @elseif(auth()->check() && !auth()->user()->forumblock)
                <span class="forum-rank-note">Elite User rank required to start topics.</span>
            @endif
            @if(\App\Services\ForumAccess::allows(auth()->user(), 'edit_categories') || \App\Services\ForumAccess::allows(auth()->user(), 'delete_categories'))
                <details class="forum-staff-menu"><summary>Category tools</summary><div>
                    @if(\App\Services\ForumAccess::allows(auth()->user(), 'edit_categories'))<a href="{{ route('forum.category.edit', $category->id) }}">Edit category</a>@endif
                    @if(\App\Services\ForumAccess::allows(auth()->user(), 'delete_categories'))
                        <form method="POST" action="{{ route('forum.category.destroy', $category->id) }}" onsubmit="return confirm('Delete this category? It can be restored later.')">@csrf @method('DELETE')<button type="submit">Delete category</button></form>
                    @endif
                </div></details>
            @endif
        </div>
    </header>
    <nav class="forum-sort-bar mb-4" aria-label="Sort topics">
        @foreach(['latest' => 'Latest activity', 'created' => 'Newest topics', 'replies' => 'Most replies', 'views' => 'Most viewed'] as $value => $label)
            <a class="forum-sort-btn {{ $sort === $value ? 'active' : '' }}" @if($sort === $value) aria-current="page" @endif href="{{ route('forum.category', ['category' => $category->slug, 'sort' => $value]) }}">{{ $label }}</a>
        @endforeach
    </nav>
    <div class="forum-topic-list">
        @forelse($topics as $topic)
            @include('forum.partials.topic-row')
        @empty
            <div class="forum-empty-state"><h2>No topics yet</h2><p>Start a discussion when you have a question or something to share.</p></div>
        @endforelse
    </div>
    @if($topics->hasPages())<div class="forum-pagination mt-4">{{ $topics->links('pagination::bootstrap-5') }}</div>@endif
</div>
