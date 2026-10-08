<article class="forum-topic-row {{ $topic->is_pinned ? 'topic-pinned' : '' }} {{ $topic->is_locked ? 'topic-locked' : '' }}">
    <div class="forum-topic-icon" aria-hidden="true"><i class="bi {{ $topic->is_locked ? 'bi-lock-fill' : ($topic->is_pinned ? 'bi-pin-fill' : 'bi-chat-left-text') }}"></i></div>
    <div class="forum-topic-copy">
        <a class="forum-topic-link" href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug]) }}">{{ $topic->title }}</a>
        <div class="forum-topic-badges">
            @if($topic->is_pinned)<span class="forum-topic-badge pinned">Pinned</span>@endif
            @if($topic->is_locked)<span class="forum-topic-badge locked">Locked</span>@endif
            @if($category->is_private)<span class="forum-topic-badge locked">Staff only</span>@endif
            @if(($topic->new_replies_count ?? 0) > 0)
                <a class="forum-topic-badge new" href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'unread' => 1]) }}">{{ $topic->new_replies_count }} unread · First unread</a>
            @endif
        </div>
        <div class="forum-topic-started">{{ $topic->user?->name ?? 'Former member' }} · <time datetime="{{ $topic->created_at->toIso8601String() }}" title="{{ $topic->created_at->format('d M Y H:i') }}">{{ $topic->created_at->diffForHumans() }}</time>@if($showCategory ?? false) · {{ $category->name }}@endif</div>
    </div>
    <div class="forum-topic-stats">
        <div class="forum-topic-stat"><strong>{{ number_format(max(0, $topic->posts_count - 1)) }}</strong><span>Replies</span></div>
        <div class="forum-topic-stat"><strong>{{ number_format($topic->views) }}</strong><span>Views</span></div>
    </div>
    <div class="forum-last-post">
        @if($topic->lastPost)
            <a class="forum-last-post-link" href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $topic->lastPost->id]) }}">
                <span class="forum-last-post-label">Last post · {{ $topic->lastPost->user?->name ?? 'Former member' }}</span>
                <time class="forum-last-post-time" datetime="{{ $topic->lastPost->created_at->toIso8601String() }}" title="{{ $topic->lastPost->created_at->format('d M Y H:i') }}">{{ $topic->lastPost->created_at->diffForHumans() }}</time>
            </a>
        @endif
    </div>
</article>
