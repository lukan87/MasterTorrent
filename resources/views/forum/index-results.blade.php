<div class="container forum-index py-4 py-lg-5">
    <header class="forum-index-header">
        <div class="forum-header-copy">
            <div class="forum-eyebrow"><i class="bi bi-chat-square-dots" aria-hidden="true"></i> FileIplay community</div>
            <h1 class="forum-page-title">Good conversations<br>start here.</h1>
            <p class="forum-page-subtitle">Share what you love, ask a question, or find your next great discovery. There’s a conversation for everyone.</p>
            <div class="forum-header-actions">
                <a href="#forum-categories" class="forum-primary-btn">Explore categories <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
                @auth
                    @if(\App\Services\ForumAccess::allows(auth()->user(), 'create_categories'))
                        <a href="{{ route('forum.category.create') }}" class="forum-secondary-btn"><i class="bi bi-folder-plus" aria-hidden="true"></i> Add Category</a>
                    @endif
                @endauth
            </div>
        </div>
        <div class="forum-overview" aria-label="Forum overview">
            <div class="forum-overview-symbol" aria-hidden="true"><i class="bi bi-chat-square-heart"></i></div>
            <p>A place to connect.</p>
            <dl class="forum-stats">
                <div><dt>Categories</dt><dd>{{ number_format($categories->count()) }}</dd></div>
                <div><dt>Topics</dt><dd>{{ number_format($categories->sum('topics_count')) }}</dd></div>
            </dl>
        </div>
    </header>

    <div class="forum-toolbar">
        <form method="GET" action="{{ route('forum.search') }}" class="forum-search-bar" role="search">
            <label for="forum-search" class="visually-hidden">Search the forum</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input id="forum-search" type="search" name="q" placeholder="Search topics and conversations…" minlength="2" maxlength="200" required>
            <button type="submit" class="forum-primary-btn">Search</button>
        </form>
        @auth
            <a href="{{ route('forum.my-topics') }}" class="forum-secondary-btn"><i class="bi bi-person-circle" aria-hidden="true"></i> My Topics</a>
        @endauth
    </div>

    <form method="GET" action="{{ route('forum.index') }}" class="forum-search-bar mb-4"><label for="category-filter">Filter categories</label><input id="category-filter" type="search" name="category_q" maxlength="100" value="{{ request('category_q') }}"><button type="submit" class="forum-primary-btn">Filter</button></form>
    <section id="forum-categories" aria-labelledby="forum-categories-title">
        <div class="forum-section-heading">
            <div>
                <div class="forum-eyebrow">Find your conversation</div>
                <h2 id="forum-categories-title">Browse categories</h2>
            </div>
            <span class="forum-category-total">{{ number_format($categories->count()) }} {{ $categories->count() === 1 ? 'category' : 'categories' }}</span>
        </div>
        <div class="forum-category-list">
            @forelse($categories as $category)
                <div class="forum-category-row">
                <a href="{{ route('forum.category', $category->slug) }}" class="forum-category-link">
                    <div class="forum-category-icon"><i class="bi {{ $category->icon ?: 'bi-chat-square-text' }}" aria-hidden="true"></i></div>
                    <div class="forum-category-content">
                        <h3 class="forum-category-title">{{ $category->name }}</h3>
                        @if($category->is_private)<span class="forum-private-label">Staff only</span>@endif
                        @if($category->description)
                            <p class="forum-category-description">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="forum-topic-count">
                        <strong>{{ number_format($category->topics_count) }}</strong>
                        <span>{{ (int) $category->topics_count === 1 ? 'Topic' : 'Topics' }}</span>
                    </div>
                    <span class="forum-category-arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                </a>
                @include('forum.partials.category-activity')
                </div>
            @empty
                <div class="forum-empty-state">
                    <div class="forum-empty-icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></div>
                    <h3>A new conversation is on its way</h3>
                    <p>No categories are available yet. Check back soon to join the discussion.</p>
                </div>
            @endforelse
        </div>
        <p class="forum-community-note"><i class="bi bi-heart" aria-hidden="true"></i> A great community starts with a little kindness. Keep it friendly and helpful.</p>
    </section>

{{-- =========================================================
     DELETED CATEGORIES
========================================================== --}}

@if(
    auth()->check() &&
    \App\Services\ForumAccess::allows(auth()->user(), 'create_categories') &&
    $deletedCategories->isNotEmpty()
)

    <section class="forum-deleted-categories mt-5">

        {{-- Header --}}
        <div class="forum-deleted-header">

            <div class="forum-deleted-heading">

                <div class="forum-deleted-icon">
                    <i class="bi bi-trash3-fill"></i>
                </div>

                <div>

                    <h3>
                        Deleted Categories
                    </h3>

                    <p>
                        Restore previously deleted categories or permanently remove them.
                    </p>

                </div>

            </div>

            <span class="forum-deleted-count">
                {{ $deletedCategories->count() }}
                {{ $deletedCategories->count() === 1 ? 'Category' : 'Categories' }}
            </span>

        </div>


        {{-- Deleted category list --}}
        <div class="forum-deleted-category-list">

            @foreach($deletedCategories as $category)

                <div class="forum-deleted-category-card">

                    <div class="forum-deleted-category-main">

                        <div class="forum-deleted-category-icon">
                            <i class="bi {{ $category->icon ?? 'bi-chat' }}"></i>
                        </div>

                        <div class="forum-deleted-category-info">

                            <h4>
                                {{ $category->name }}
                            </h4>

                            @if($category->description)

                                <p>
                                    {{ $category->description }}
                                </p>

                            @endif

                            <span class="forum-deleted-date">
                                <i class="bi bi-clock me-1"></i>
                                Deleted {{ $category->deleted_at->format('d/m/Y H:i') }}
                            </span>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="forum-deleted-actions">

                        <form method="POST"
                              action="{{ route('forum.category.restore', $category->id) }}"
                              onsubmit="return confirm('Restore this category?');">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="forum-restore-category-btn">

                                <i class="bi bi-arrow-counterclockwise me-1"></i>

                                Restore

                            </button>

                        </form>


                        <form method="POST"
                              action="{{ route('forum.category.force-delete', $category->id) }}"
                              onsubmit="return confirm('{{ $category->topics_count > 0 ? 'WARNING: This category contains ' . $category->topics_count . ' ' . ($category->topics_count === 1 ? 'topic' : 'topics') . '. Permanently deleting it will also permanently delete the category and all of its topics, posts and reactions. This cannot be undone. Are you sure?' : 'WARNING: This will permanently delete this category. This cannot be undone. Are you sure?' }}');">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="forum-permanent-delete-category-btn">

                                <i class="bi bi-trash3-fill me-1"></i>

                                Permanently Delete

                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    </section>

@endif

</div>
