@php
    $author = $comment->user;
    $canManage = auth()->check() && ((int) auth()->id() === (int) $comment->user_id || auth()->user()->user_class > 5);
    $canParticipate = auth()->check() && !auth()->user()->commentblock;
    $canReact = $canParticipate && (int) auth()->id() !== (int) $comment->user_id;
    $selectedReaction = $comment->reactions->firstWhere('user_id', auth()->id())?->reaction;
@endphp
<article class="discussion-card {{ $isReply ? 'discussion-reply' : '' }}" id="comment-{{ $comment->id }}">
    <header class="discussion-author">
        <div class="discussion-avatar">
            @if($author?->profile_image)<img src="{{ $author->profile_image }}" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($author?->name ?? '?', 0, 1)) }}@endif
        </div>
        <div class="discussion-identity">
            <div class="discussion-name">
                @if($author)<a href="{{ route('profile.show', ['id' => $author->id, 'name' => $author->name]) }}" style="--member-color: {{ \App\Models\UserClass::getClassColor($author->user_class) }}; color: var(--member-color)">{{ $author->name }}</a>@else<strong>Deleted user</strong>@endif
                @if($author)<span class="discussion-badge">{{ \App\Models\UserClass::getClassName($author->user_class) }}</span>@endif
                @if($author?->created_at)<span class="discussion-joined">Joined {{ $author->created_at->format('M Y') }}</span>@endif
                @if($commentType === 'torrent' && $author && (int) $commentTarget->owner === (int) $author->id)<span class="discussion-badge">Uploader</span>@endif
            </div>
            <div class="discussion-meta">
                @if($commentType === 'torrent' && isset($seedingUserIds) && $seedingUserIds->contains($comment->user_id))<span class="discussion-seeding" title="Currently seeding this torrent"><i class="bi bi-arrow-up-circle-fill" aria-hidden="true"></i> Seeding</span>@endif
                <a href="#comment-{{ $comment->id }}"><time datetime="{{ $comment->created_at->toIso8601String() }}" title="{{ $comment->created_at->format('M j, Y H:i') }} UTC">{{ $comment->created_at->diffForHumans() }}</time></a>
                @if($comment->updated_at->gt($comment->created_at))<span>· Edited</span>@endif
            </div>
            @if($author?->title)<div class="discussion-member-title">{{ $author->title }}</div>@endif
        </div>
    </header>
    <div class="discussion-content">{!! convertCustomTagsToHtml($comment->comment) !!}</div>
    <footer class="discussion-card-footer">
        <div class="discussion-reaction-area">
            @if($canReact)
                <div class="discussion-reaction-picker">
                    <form action="{{ route('comments.react', $comment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="reaction" value="{{ $selectedReaction ?? 'like' }}">
                        <button class="discussion-like" type="submit" aria-pressed="{{ $selectedReaction ? 'true' : 'false' }}" aria-label="{{ $selectedReaction ? 'Remove your reaction' : 'Like this comment' }}"><span aria-hidden="true">{{ \App\Models\CommentReaction::TYPES[$selectedReaction ?? 'like'] }}</span> {{ $selectedReaction ? ucfirst($selectedReaction) : 'Like' }}</button>
                    </form>
                    <button class="discussion-reaction-toggle" type="button" aria-label="More reactions" aria-expanded="false" aria-controls="reaction-options-{{ $comment->id }}"><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                    <div class="discussion-reaction-options" id="reaction-options-{{ $comment->id }}" role="group" aria-label="Choose a reaction" hidden>
                        @foreach(\App\Models\CommentReaction::TYPES as $type => $emoji)
                            <form action="{{ route('comments.react', $comment->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $type }}">
                                <button type="submit" aria-pressed="{{ $selectedReaction === $type ? 'true' : 'false' }}" aria-label="{{ ucfirst($type) }}" data-bs-toggle="tooltip" data-bs-html="false" data-bs-title="{{ ucfirst($type) }}: {{ $comment->reactions->where('reaction', $type)->map(fn ($reaction) => $reaction->user?->name ?? 'Deleted user')->implode(', ') ?: 'No reactions yet' }}"><span aria-hidden="true">{{ $emoji }}</span></button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($comment->reactions->isNotEmpty())
                <span class="discussion-reaction-totals" aria-label="{{ $comment->reactions->count() }} reactions">
                    @foreach(\App\Models\CommentReaction::TYPES as $type => $emoji)
                        @if($count = $comment->reactions->where('reaction', $type)->count())
                            <span tabindex="0" data-bs-toggle="tooltip" data-bs-html="false" data-bs-title="{{ ucfirst($type) }}: {{ $comment->reactions->where('reaction', $type)->map(fn ($reaction) => $reaction->user?->name ?? 'Deleted user')->implode(', ') }}"><span aria-hidden="true">{{ $emoji }}</span> {{ $count }}</span>
                        @endif
                    @endforeach
                </span>
            @else
                <span class="discussion-reaction-totals">{{ $canReact ? '' : 'No reactions yet' }}</span>
            @endif
            <span class="discussion-reaction-status visually-hidden" role="status" aria-live="polite"></span>
        </div>
        <div class="discussion-controls">
            @if($canParticipate && !$isReply)
                <button type="button" data-bs-toggle="collapse" data-bs-target="#reply-panel-{{ $comment->id }}" aria-controls="reply-panel-{{ $comment->id }}" aria-expanded="{{ (int) old('parent_id') === (int) $comment->id ? 'true' : 'false' }}"><i class="bi bi-reply" aria-hidden="true"></i> Reply @if($comment->replies_count)<span>{{ $comment->replies_count }}</span>@endif</button>
            @elseif(!$isReply && $comment->replies_count)
                <span class="discussion-reply-count">{{ $comment->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $comment->replies_count) }}</span>
            @endif
            @if($canManage)
                @if($canParticipate)
                    <button type="button" data-bs-toggle="collapse" data-bs-target="#edit-panel-{{ $comment->id }}" aria-controls="edit-panel-{{ $comment->id }}" aria-expanded="false"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</button>
                @endif
                <form method="POST" action="{{ route('comments.destroy', $comment->id) }}" onsubmit="return confirm('Delete this comment? Replies will be kept.');">
                    @csrf @method('DELETE')
                    <button class="discussion-delete" type="submit" aria-label="Delete comment by {{ $author?->name ?? 'deleted user' }}"><i class="bi bi-trash" aria-hidden="true"></i> Delete</button>
                </form>
            @endif
        </div>
    </footer>
    @if($canParticipate && !$isReply)
        <div class="collapse discussion-form-panel {{ (int) old('parent_id') === (int) $comment->id ? 'show' : '' }}" id="reply-panel-{{ $comment->id }}">
            @include('comments.form', ['formId' => 'reply-'.$comment->id, 'parentId' => $comment->id])
        </div>
    @endif
    @if($canManage && $canParticipate)
        <div class="collapse discussion-form-panel" id="edit-panel-{{ $comment->id }}">
            <form method="POST" action="{{ route('comments.update', $comment->id) }}" class="discussion-composer">
                @csrf @method('PUT')
                <label class="visually-hidden" for="edit-{{ $comment->id }}">Edit comment</label>
                @include('comments.toolbar')
                <textarea id="edit-{{ $comment->id }}" name="comment" rows="3" maxlength="10000" required>{{ $comment->comment }}</textarea>
                <footer><button type="submit" class="discussion-submit">Save changes</button></footer>
            </form>
        </div>
    @endif
    @if(!$isReply && $comment->replies->isNotEmpty())
        <div class="discussion-thread" aria-label="Replies">
            @foreach($comment->replies as $reply)
                @include('comments.card', ['comment' => $reply, 'isReply' => true])
            @endforeach
        </div>
    @endif
</article>
