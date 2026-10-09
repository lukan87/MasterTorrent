<form action="{{ route('comments.store') }}" method="POST" class="discussion-composer">
    @csrf
    <input type="hidden" name="commentable_id" value="{{ $parentId && isset($comment) ? $comment->commentable_id : $commentTarget->id }}">
    <input type="hidden" name="commentable_type" value="{{ $parentId && isset($comment) ? $comment->commentable_type : $commentType }}">
    @if($parentId)<input type="hidden" name="parent_id" value="{{ $parentId }}">@endif
    <label class="visually-hidden" for="{{ $formId }}">{{ $parentId ? 'Your reply' : 'Your comment' }}</label>
    @include('comments.toolbar')
    <textarea id="{{ $formId }}" name="comment" rows="3" maxlength="10000" required placeholder="{{ $parentId ? 'Keep the conversation going…' : 'Share a thought or a helpful detail…' }}">{{ (string) old('parent_id', '') === (string) ($parentId ?? '') && !old('_method') ? old('comment') : '' }}</textarea>
    <footer><small>5 posts per day · +1 bonus point</small><button class="discussion-submit" type="submit"><i class="bi bi-send" aria-hidden="true"></i> {{ $parentId ? 'Post reply' : 'Post comment' }}</button></footer>
</form>
