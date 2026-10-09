@php
    $commentTarget = $libraryEntry ?? $onlineMedia;
    $commentType = $commentTarget ? get_class($commentTarget) : null;
    $discussionComments = null;
    if ($commentTarget) {
        $discussionComments = \App\Models\Comment::query()->where(function ($query) use ($libraryEntry, $onlineMedia) {
            foreach ([$libraryEntry, $onlineMedia] as $target) {
                if ($target) {
                    $query->orWhere(fn ($part) => $part->where('commentable_type', get_class($target))->where('commentable_id', $target->id));
                }
            }
        })->discussion()->paginate(10, ['*'], 'comments_page')->withQueryString()->fragment('discussion');
    }
@endphp
@if($commentTarget)
<div class="container px-xl-5 px-lg-4 px-3 mb-4">
    @include('comments.discussion', compact('commentTarget', 'commentType', 'discussionComments'))
</div>
@endif
