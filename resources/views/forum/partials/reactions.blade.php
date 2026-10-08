@auth
@php
    $likes = $post->likes;
    $selected = $likes->firstWhere('user_id', auth()->id())?->reaction;
    $counts = $likes->groupBy('reaction')->map->count();
    $emojis = ['like' => '👍', 'love' => '❤️', 'laugh' => '😂', 'wow' => '😮', 'sad' => '😢'];
@endphp
<div class="forum-like-section" data-reactions="{{ $post->id }}">
    @if($post->user_id !== auth()->id() && \App\Services\ForumAccess::canParticipate(auth()->user()))
        <div class="forum-reactions" role="group" aria-label="React to this post">
            @foreach($emojis as $reaction => $emoji)
                <form method="POST" action="{{ route('forum.post.like', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $post->id]) }}">
                    @csrf
                    <input type="hidden" name="reaction" value="{{ $reaction }}">
                    <button type="submit" class="forum-reaction-btn {{ $selected === $reaction ? 'active' : '' }}" aria-label="{{ ucfirst($reaction) }}" aria-pressed="{{ $selected === $reaction ? 'true' : 'false' }}" title="{{ ucfirst($reaction) }}">
                        <span aria-hidden="true">{{ $emoji }}</span><span class="forum-reaction-count">{{ $counts[$reaction] ?? 0 }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    @endif
    <span class="reaction-total" aria-live="polite">{{ $likes->count() }} {{ $likes->count() === 1 ? 'reaction' : 'reactions' }}</span>
    @if($likes->isNotEmpty())
        <details class="forum-reactors">
            <summary>Who reacted</summary>
            <ul>
                @foreach($likes as $like)
                    @if($like->user)
                        <li><span aria-hidden="true">{{ $emojis[$like->reaction] ?? '👍' }}</span> <a href="{{ route('profile.show', ['id' => $like->user->id, 'name' => $like->user->name]) }}">{{ $like->user->name }}</a></li>
                    @endif
                @endforeach
            </ul>
        </details>
    @endif
    <span class="forum-reaction-status" role="status" aria-live="polite"></span>
</div>
@endauth
