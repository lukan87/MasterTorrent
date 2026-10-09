    @if($firstPost) @include('forum.partials.post', ['post' => $firstPost, 'original' => true]) @endif
    <div class="forum-replies-heading"><h2>Replies</h2><span>Newest replies first</span></div>
    @forelse($replies as $post)
        @include('forum.partials.post', ['original' => false])
    @empty
        <p class="forum-empty-state">No replies yet.</p>
    @endforelse
    @if($replies->hasPages())<div class="forum-pagination my-4">{{ $replies->links('pagination::bootstrap-5') }}</div>@endif
