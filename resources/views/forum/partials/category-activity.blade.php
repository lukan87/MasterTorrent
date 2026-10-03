<div class="forum-category-activity">
    <span class="forum-category-activity-label">Latest activity</span>
    @if($category->latestTopic?->lastPost)
        @php($latestPost = $category->latestTopic->lastPost)
        <a href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $category->latestTopic->slug, 'post' => $latestPost->id]) }}#post-{{ $latestPost->id }}">{{ $category->latestTopic->title }}</a>
        <p>{{ $latestPost->user->name ?? 'Unknown' }} · <time datetime="{{ $latestPost->created_at->toIso8601String() }}" title="{{ $latestPost->created_at->format('d M Y H:i') }}">{{ $latestPost->created_at->diffForHumans() }}</time></p>
    @else
        <p>No posts yet. Start the conversation.</p>
    @endif
</div>
