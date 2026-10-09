@php($original = $original ?? false)
<article id="post-{{ $post->id }}" class="{{ $original ? 'forum-post-card forum-main-post' : 'forum-reply-box forum-reply-post' }} mb-4">
    <div class="forum-post-layout">
        <aside class="forum-user-panel">
            <div class="forum-avatar">
                @if($post->user?->profile_image)
                    <img width="70" height="70" loading="lazy" decoding="async" src="{{ $post->user->profile_image }}" alt="{{ $post->user->name }}">
                @else
                    <div class="forum-avatar-placeholder"><i class="bi bi-person-fill" aria-hidden="true"></i></div>
                @endif
            </div>
            <div class="forum-author-copy">
                @if($post->user)
                    <a class="forum-username" href="{{ route('profile.show', ['id' => $post->user->id, 'name' => $post->user->name]) }}">{{ $post->user->name }}</a>
                @else
                    <span class="forum-username">Former member</span>
                @endif
                <div class="forum-user-rank" style="--user-class-color: {{ \App\Models\UserClass::getClassColor($post->user?->user_class) }}">{{ \App\Models\UserClass::getClassName($post->user?->user_class) }}</div>
                @if($post->user?->title)<div class="forum-user-title">{{ $post->user->title }}</div>@endif
                <div class="forum-user-info">Joined {{ $post->user?->created_at?->format('M Y') ?? '—' }} · {{ number_format($post->user?->forum_posts_count ?? 0) }} posts</div>
            </div>
        </aside>
        <div class="forum-post-content">
            <header class="forum-post-header">
                <div class="forum-post-meta-left">
                    @if($original)<span class="forum-topic-badge pinned">Original post</span>@endif
                    <time datetime="{{ $post->created_at->toIso8601String() }}" title="{{ $post->created_at->format('d M Y H:i') }}">{{ $post->created_at->diffForHumans() }}</time>
                    @if($post->edited_at)<span title="{{ $post->edited_at->format('d M Y H:i') }}">Edited</span>@endif
                </div>
                <div class="forum-post-actions">
                    <a class="post-anchor" aria-label="Link to post {{ $post->id }}" href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $post->id]) }}">#{{ $post->id }}</a>
                    @if(\App\Services\ForumAccess::canParticipate(auth()->user()))
                        @if(!$topic->is_locked)
                            <button type="button" class="forum-post-action quote-post-btn" data-post-id="{{ $post->id }}" data-username="{{ $post->user?->name ?? 'Former member' }}" data-body="{{ json_encode($post->body) }}"><i class="bi bi-quote" aria-hidden="true"></i> Quote</button>
                        @endif
                        @if($post->user_id === auth()->id() || \App\Services\ForumAccess::allows(auth()->user(), 'edit_posts'))
                            <a data-post-edit class="forum-post-action" href="{{ route('forum.post.edit', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $post->id]) }}">Edit</a>
                        @endif
                    @endif
                    @if(!$original && \App\Services\ForumAccess::allows(auth()->user(), 'delete_posts'))
                        <form method="POST" action="{{ route('forum.post.delete', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $post->id]) }}" onsubmit="return confirm('Delete this post permanently?')">
                            @csrf @method('DELETE')
                            <button class="forum-post-action delete-action" type="submit">Delete</button>
                        </form>
                    @endif
                </div>
            </header>
            <div class="forum-post-body">{!! $renderer->render($post->body) !!}</div>
            @include('forum.partials.reactions')
        </div>
    </div>
</article>
