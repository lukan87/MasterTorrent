@php
    $commentAuthorIds = $comments->getCollection()->flatMap(fn ($comment) => $comment->replies->pluck('user_id')->push($comment->user_id))->unique();
    $seedingUserIds = \App\Models\Peer::where('torrent_id', $torrent->id)
        ->where('active', 1)->where('seeder', 1)->whereIn('user_id', $commentAuthorIds)
        ->distinct()->pluck('user_id');
@endphp
@include('comments.discussion', ['commentTarget' => $torrent, 'commentType' => 'torrent', 'discussionComments' => $comments])
