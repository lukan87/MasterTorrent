
@extends('layouts.app')

@section('content')

<div class="container py-5">



{{-- =========================================================
     BREADCRUMB
     ========================================================= --}}

<div class="forum-breadcrumb mb-4">

    <a href="{{ route('forum.index') }}"
       class="forum-breadcrumb-link">

        <i class="bi bi-house-door-fill"></i>

        <span>Forum</span>

    </a>

    <i class="bi bi-chevron-right forum-breadcrumb-separator"></i>

    <a href="{{ route('forum.category', $category->slug) }}"
       class="forum-breadcrumb-link forum-breadcrumb-current">

        <i class="bi bi-folder-fill"></i>

        <span>{{ $category->name }}</span>

    </a>

</div>




    {{-- =========================================================
         TOPIC HEADER
         ========================================================= --}}

    <div class="forum-topic-header mb-4">

        <div class="d-flex align-items-start justify-content-between gap-3">

            <div>

                <div class="forum-topic-category mb-2">

                    <i class="bi bi-chat-square-text-fill me-1"></i>

                    {{ $category->name }}

                </div>


                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">

    <h1 class="forum-topic-title mb-0">

        {{ $topic->title }}

    </h1>


    @auth

        @if(!auth()->user()->forumblock)

            @if($isFollowing)

                <form method="POST"
                      action="{{ route('forum.topic.unfollow', [
                          'category' => $category->slug,
                          'topic' => $topic->slug,
                      ]) }}">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="forum-follow-btn following">

                        <i class="bi bi-bell-slash me-1"></i>

                        Unfollow

                    </button>

                </form>

            @else

                <form method="POST"
                      action="{{ route('forum.topic.follow', [
                          'category' => $category->slug,
                          'topic' => $topic->slug,
                      ]) }}">

                    @csrf

                    <button type="submit"
                            class="forum-follow-btn">

                        <i class="bi bi-bell me-1"></i>

                        Follow Topic

                    </button>

                </form>

            @endif

        @endif

        @if($replies->count() > 0)

    <a href="{{ route('forum.topic', [
        'category' => $category->slug,
        'topic' => $topic->slug,
        'page' => $latestReplyPage,
    ]) }}#post-{{ $topic->last_post_id }}"
       class="forum-latest-btn">

        <i class="bi bi-arrow-down-circle me-1"></i>

        Latest Reply

    </a>

@endif

    @endauth

</div>


                <div class="forum-topic-meta">

                    <span>

                        <i class="bi bi-person-circle me-1"></i>

                        Started by

                        <strong>
                            {{ $topic->user->name ?? 'Unknown' }}
                        </strong>

                    </span>


                    <span>

                        <i class="bi bi-clock me-1"></i>

                        {{ $topic->created_at->diffForHumans() }}

                    </span>


                    <span>

                        <i class="bi bi-eye me-1"></i>

                        {{ $topic->views }} views

                    </span>


                    @if($topic->is_locked)

                        <span class="forum-locked-badge">

                            <i class="bi bi-lock-fill me-1"></i>

                            Locked

                        </span>

                    @endif

                </div>

            </div>

            @auth

   @if(auth()->user()->user_class > \App\Models\UserClass::MODERATOR)

    <div class="forum-topic-actions">

        {{-- PIN / UNPIN --}}

        <form method="POST"
              action="{{ route('forum.topic.pin', [
                  'category' => $category->slug,
                  'topic' => $topic->slug,
              ]) }}"
              onsubmit="return confirm('{{ $topic->is_pinned ? 'Unpin this topic?' : 'Pin this topic?' }}');">

            @csrf

            @if($topic->is_pinned)

                <button type="submit"
                        class="forum-topic-action unpin">

                    <i class="bi bi-pin-angle-fill me-1"></i>

                    Unpin Topic

                </button>

            @else

                <button type="submit"
                        class="forum-topic-action pin">

                    <i class="bi bi-pin-angle-fill me-1"></i>

                    Pin Topic

                </button>

            @endif

        </form>


        {{-- LOCK / UNLOCK --}}

        <form method="POST"
              action="{{ route('forum.topic.lock', [
                  'category' => $category->slug,
                  'topic' => $topic->slug,
              ]) }}"
              onsubmit="return confirm('{{ $topic->is_locked ? 'Unlock this topic?' : 'Lock this topic?' }}');">

            @csrf

            @if($topic->is_locked)

                <button type="submit"
                        class="forum-topic-action unlock">

                    <i class="bi bi-unlock-fill me-1"></i>

                    Unlock Topic

                </button>

            @else

                <button type="submit"
                        class="forum-topic-action lock">

                    <i class="bi bi-lock-fill me-1"></i>

                    Lock Topic

                </button>

            @endif

        </form>


        {{-- DELETE TOPIC --}}

        <form method="POST"
      action="{{ route('forum.topic.delete', [
          'category' => $category->slug,
          'topic' => $topic->slug,
      ]) }}"
      onsubmit="return confirm('Are you sure you want to delete this topic? This will permanently delete the topic and all of its replies.');">

    @csrf

    @method('DELETE')

    <button type="submit"
            class="forum-topic-action delete-topic">

        <i class="bi bi-trash3-fill me-1"></i>

        Delete Topic

    </button>

</form>

    </div>

@endif

@endauth

        </div>

    </div>


    {{-- =========================================================
         ORIGINAL POST
         ========================================================= --}}

    @if($firstPost)

        <article id="post-{{ $firstPost->id }}"
                 class="forum-post-card forum-main-post mb-4">


            {{-- ORIGINAL POST HEADER --}}

            <div class="original-post-label">

                <span>

                    <i class="bi bi-pin-angle-fill me-1"></i>

                    MAIN POST

                </span>


                <div class="forum-post-actions">

                    @auth

                        {{-- EDIT --}}

                        @if(
                            $firstPost->user_id === auth()->id()
                            || auth()->user()->user_class > \App\Models\UserClass::MODERATOR
                        )

                            <a href="{{ route('forum.post.edit', [
                                'category' => $category->slug,
                                'topic' => $topic->slug,
                                'post' => $firstPost->id,
                            ]) }}"
                               class="forum-post-action edit-action">

                                <i class="bi bi-pencil-square me-1"></i>

                                Edit

                            </a>

                        @endif




                    @endauth


                    {{-- POST ANCHOR --}}

                    <a href="#post-{{ $firstPost->id }}"
                       class="post-anchor">

                        #{{ $firstPost->id }}

                    </a>

                </div>

            </div>


            <div class="row g-0">


                {{-- =================================================
                     USER PANEL
                     ================================================= --}}

                <div class="col-md-3 col-lg-2">

                    <div class="forum-user-panel">


                        {{-- AVATAR --}}

                        <div class="forum-avatar">

                            @if(
                                $firstPost->user &&
                                $firstPost->user->profile_image
                            )

                                <img
                                    src="{{ $firstPost->user->profile_image }}"
                                    alt="{{ $firstPost->user->name }}"
                                >

                            @else

                                <div class="forum-avatar-placeholder">

                                    <i class="bi bi-person-fill"></i>

                                </div>

                            @endif

                        </div>


                        {{-- USERNAME --}}

                        @if($firstPost->user)

                            <a href="{{ route('profile.show', [
                                'id' => $firstPost->user->id,
                                'username' => $firstPost->user->name
                            ]) }}"
                               class="forum-username">

                                {{ $firstPost->user->name }}

                            </a>

                        @else

                            <span class="forum-username">

                                Unknown

                            </span>

                        @endif


                        {{-- TITLE --}}

                        @if($firstPost->user?->title)

                            <div class="forum-user-title">

                                {{ $firstPost->user->title }}

                            </div>

                        @endif


{{-- USER CLASS --}}
@php
    $userClassId = $firstPost->user?->user_class;
    $userClassName = \App\Models\UserClass::getClasses()[$userClassId] ?? 'User';
    $userClassColor = \App\Models\UserClass::getClassColor($userClassId);
@endphp

<div
    class="forum-user-rank"
    style="--user-class-color: {{ $userClassColor }};"
>
    {{ $userClassName }}
</div>


                        {{-- JOIN DATE / POSTS --}}

                        @if($firstPost->user)

                            <div class="forum-user-info">

                                <i class="bi bi-calendar3 me-1"></i>

                                Joined

                                {{ $firstPost->user->created_at?->format('M Y') }}

                            </div>


                            <div class="forum-user-info">

                                <i class="bi bi-chat-left-text me-1"></i>

                                {{ $firstPost->user->forum_posts_count ?? 0 }}

                                posts

                            </div>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     POST CONTENT
                     ================================================= --}}

                <div class="col-md-9 col-lg-10">

                    <div class="forum-post-content">


                        {{-- POST HEADER --}}

                        <div class="forum-post-header">

                            <span>

                                <i class="bi bi-clock me-1"></i>

                                Posted

                                {{ $firstPost->created_at->format('d M Y H:i') }}

                            </span>


                            @if($firstPost->edited_at)

                                <span class="post-edited">

                                    <i class="bi bi-pencil me-1"></i>

                                    Edited

                                    {{ $firstPost->edited_at->diffForHumans() }}

                                </span>

                            @endif

                        </div>


                        {{-- BODY --}}

                        <div class="forum-post-body">

                            {!! convertCustomTagsToHtml($firstPost->body) !!}

                        </div>

                        {{-- LIKES --}}

@auth

   @php
    $postLikes = $firstPost->likes;
    $postLikeCount = $postLikes->count();
    $postReaction = $postLikes
        ->firstWhere('user_id', auth()->id())
        ?->reaction;
        $reactionCounts = $postLikes->groupBy('reaction')->map->count();
@endphp

    <div class="forum-like-section">

        @if($firstPost->user_id !== auth()->id())
    <div class="forum-reactions">

        @foreach([
            'like' => '👍',
            'love' => '❤️',
            'laugh' => '😂',
            'wow' => '😮',
            'sad' => '😢',
        ] as $reaction => $emoji)

            <form method="POST"
                  action="{{ route('forum.post.like', [
                      'category' => $category->slug,
                      'topic' => $topic->slug,
                      'post' => $firstPost->id,
                  ]) }}"
                  class="d-inline">

                @csrf

                <input type="hidden" name="reaction" value="{{ $reaction }}">

                <button type="submit"
                        class="forum-reaction-btn {{ $postReaction === $reaction ? 'active' : '' }}"
                        title="{{ ucfirst($reaction) }}">
                    <span class="forum-reaction-emoji">{{ $emoji }}</span>
                </button>

            </form>

        @endforeach

       @if($postLikeCount > 0)
    <div class="reaction-summary">
        @foreach([
            'like' => '👍',
            'love' => '❤️',
            'laugh' => '😂',
            'wow' => '😮',
            'sad' => '😢',
        ] as $reaction => $emoji)
            @if(($reactionCounts[$reaction] ?? 0) > 0)
                <span class="reaction-summary-item">
                    <span>{{ $emoji }}</span>
                    <span>{{ $reactionCounts[$reaction] }}</span>
                </span>
            @endif
        @endforeach
    </div>
@endif

    </div>

@elseif($postLikeCount > 0)
    <span class="reaction-total">
        {{ $postLikeCount }}
    </span>
@endif


        @if($postLikeCount > 0)

          <div class="forum-liked-by">

    <i class="bi bi-heart-fill liked-by-heart"></i>

    <span class="liked-by-label">
    Reacted by
</span>

    @php
        $likedUsers = $postLikes
            ->filter(fn ($like) => $like->user)
            ->values();

        $visibleLikedUsers = $likedUsers->take(2);
        $remainingLikes = max(0, $likedUsers->count() - 2);
    @endphp

    @foreach($visibleLikedUsers as $like)

       <a href="{{ route('profile.show', [
    'id' => $like->user->id,
    'username' => $like->user->name
]) }}"
   class="liked-by-user">
    @switch($like->reaction)
        @case('like')
            👍
            @break
        @case('love')
            ❤️
            @break
        @case('laugh')
            😂
            @break
        @case('wow')
            😮
            @break
        @case('sad')
            😢
            @break
        @default
            👍
    @endswitch
    {{ $like->user->name }}
</a>

        @if(!$loop->last)
            <span class="liked-by-comma">,</span>
        @endif

    @endforeach

    @if($remainingLikes > 0)

        <button type="button"
                class="liked-by-more"
                data-bs-toggle="modal"
                data-bs-target="#likeModal-{{ $firstPost->id }}">

            {{ $remainingLikes }} {{ $remainingLikes === 1 ? 'other' : 'others' }}

        </button>

    @endif

    @if($likedUsers->count() > 0 && $remainingLikes === 0)

        <button type="button"
                class="liked-by-more"
                data-bs-toggle="modal"
                data-bs-target="#likeModal-{{ $firstPost->id }}">

            <i class="bi bi-chevron-down"></i>

        </button>

    @endif

</div>

        @endif

    </div>

@endauth

                    </div>

                </div>

            </div>

            @if($postLikeCount > 0)

    <div class="modal fade"
         id="likeModal-{{ $firstPost->id }}"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content forum-like-modal">

                <div class="modal-header">

                    <h5 class="modal-title">
    <i class="bi bi-emoji-smile me-2"></i>
    Reactions {{ $postLikeCount }}
</h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    @foreach($postLikes as $like)

                        @if($like->user)

                            <div class="forum-like-user">

                                <div class="forum-like-avatar">

                                    @if($like->user->profile_image)

                                        <img src="{{ $like->user->profile_image }}"
                                             alt="{{ $like->user->name }}">

                                    @else

                                        <div class="forum-like-avatar-placeholder">
                                            <i class="bi bi-person-fill"></i>
                                        </div>

                                    @endif

                                </div>

                                <div class="flex-grow-1">

                                    <a href="{{ route('profile.show', [
                                        'id' => $like->user->id,
                                        'username' => $like->user->name
                                    ]) }}"
                                       class="forum-like-username">

                                        {{ $like->user->name }}

                                    </a>

                                    <div class="small text-muted">
                                        {{ $like->created_at->diffForHumans() }}
                                    </div>

                                </div>

                               <span class="forum-modal-reaction">
    @switch($like->reaction)
        @case('like')
            👍
            @break
        @case('love')
            ❤️
            @break
        @case('laugh')
            😂
            @break
        @case('wow')
            😮
            @break
        @case('sad')
            😢
            @break
        @default
            👍
    @endswitch
</span>

                            </div>

                        @endif

                    @endforeach

                </div>

            </div>

        </div>

    </div>

    @endif

    </article>

@endif

@foreach($replies as $post)

    @include('forum.partials.reply-article')

@endforeach



    {{-- =========================================================
         PAGINATION
         ========================================================= --}}

    @if($replies->hasPages())

        <div class="forum-pagination mt-4">

            {{ $replies->links('pagination::bootstrap-5') }}

        </div>

    @endif



    {{-- =========================================================
         REPLY FORM
         ========================================================= --}}

    @auth

        @if(!$topic->is_locked && !auth()->user()->forumblock)

            <div class="forum-reply-box mt-5">

                <div class="forum-reply-box-header">

                    <div>

                        <h5 class="mb-1">

                            <i class="bi bi-reply-fill me-1"></i>

                            Reply to this topic

                        </h5>

                        <small>

                            Share your thoughts with the community.

                        </small>

                    </div>

                </div>


                <div class="card-body">

                    <form method="POST"
                          action="{{ route('forum.topic.reply', [
                              'category' => $category->slug,
                              'topic' => $topic->slug,
                          ]) }}">

                        @csrf


                        <div class="mb-3">

@include('forum.partials.bbcode-toolbar')

                            <textarea
    id="forum-reply-body"
    name="body"
    rows="6"
    class="form-control forum-textarea @error('body') is-invalid @enderror"
    placeholder="Write your reply..."
    required
>{{ old('body') }}</textarea>


                            @error('body')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                        <div class="d-flex gap-2 mt-2 align-items-center">
                            <button type="button"
                                    class="forum-preview-btn"
                                    id="forum-preview-btn">
                                <i class="bi bi-eye me-1"></i>Preview
                            </button>

                            @auth
                            <button type="button"
                                    class="forum-preview-btn multiquote-btn"
                                    id="forum-multiquote-toggle">
                                <i class="bi bi-quote me-1"></i>Multi-quote
                            </button>
                            @endauth
                        </div>

                        <div class="forum-post-preview-pane mt-2" style="display:none;" id="forum-reply-preview"></div>

                        <div class="multiquote-bar mt-2" id="forum-multiquote-bar" style="display:none;">
                            <span class="mq-count" id="mq-count">0</span>
                            posts selected
                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    id="mq-insert-btn">
                                <i class="bi bi-quote me-1"></i>Insert all
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-secondary"
                                    id="mq-clear-btn">
                                Clear
                            </button>
                        </div>


                        <button type="submit"
                                class="btn forum-submit-btn">

                            <i class="bi bi-send-fill me-1"></i>

                            Post Reply

                        </button>

                    </form>

                </div>

            </div>

        @elseif($topic->is_locked)

            <div class="forum-locked-box mt-5">

                <i class="bi bi-lock-fill"></i>

                <div>

                    <strong>
                        This topic is locked
                    </strong>

                    <div>
                        No new replies can be posted.
                    </div>

                </div>

            </div>

        @endif

    @endauth


</div>



@include('forum.partials.topic-css')

@include('forum.partials.back-to-top')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const replyBox = document.getElementById('forum-reply-body');

    if (!replyBox) {
        return;
    }

    document.querySelectorAll('.quote-post-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            const username = this.dataset.username || 'Unknown';

            let body = '';

            try {

                body = JSON.parse(this.dataset.body);

            } catch (error) {

                console.error('Quote error:', error);
                console.error('Post ID:', this.dataset.postId);
                console.error('Raw body:', this.dataset.body);

                return;
            }


            const quote =
                '[quote="' + username + '"]\n' +
                body.trim() +
                '\n[/quote]\n\n';


            /*
             * If there is already text in the reply box,
             * add the quote after it.
             */

            if (replyBox.value.trim() !== '') {

                replyBox.value =
                    replyBox.value.trimEnd() +
                    '\n\n' +
                    quote;

            } else {

                replyBox.value = quote;

            }


            /*
             * Focus the reply box.
             */

            replyBox.focus();


            /*
             * Put cursor at the end.
             */

            replyBox.setSelectionRange(
                replyBox.value.length,
                replyBox.value.length
            );


            /*
             * Scroll to reply box.
             */

            replyBox.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

        });

    });

});

    /* ============================================================
       AJAX REACTIONS
       ============================================================ */
    document.querySelectorAll('.forum-reactions form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var button = form.querySelector('.forum-reaction-btn');
            var csrfToken = form.querySelector('input[name="_token"]');
            var reactionInput = form.querySelector('input[name="reaction"]');

            if (!csrfToken || !reactionInput) return;

            var url = form.getAttribute('action');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken.value,
                    'Accept': 'application/json',
                },
                body: 'reaction=' + encodeURIComponent(reactionInput.value),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.error) return;

                // Find the reaction container for this post
                var container = form.closest('.forum-reactions');
                if (!container) return;

                // Update active states on buttons
                var allForms = container.querySelectorAll('form');
                allForms.forEach(function (f) {
                    var inp = f.querySelector('input[name="reaction"]');
                    var btn = f.querySelector('.forum-reaction-btn');
                    if (inp && btn) {
                        btn.classList.toggle('active', inp.value === data.user_reaction);
                    }
                });

                // Update summary counts
                var summary = container.querySelector('.reaction-summary');
                if (summary) {
                    var reactions = { like: '👍', love: '❤️', laugh: '😂', wow: '😮', sad: '😢' };
                    var html = '';
                    var totalCount = 0;
                    for (var r in reactions) {
                        var count = data.counts[r] || 0;
                        totalCount += count;
                        if (count > 0) {
                            html += '<span class="reaction-summary-item"><span>' + reactions[r] + '</span><span>' + count + '</span></span> ';
                        }
                    }
                    summary.innerHTML = html;
                    summary.style.display = totalCount > 0 ? '' : 'none';

                    // Update total
                    var totalEl = container.parentElement.querySelector('.reaction-total');
                    if (totalEl) {
                        totalEl.textContent = totalCount > 0 ? totalCount + ' reaction' + (totalCount !== 1 ? 's' : '') : '';
                    }
                }
            })
            .catch(function (err) {
                console.error('Reaction AJAX error:', err);
            });
        });
    });

    /* ============================================================
       POST HIGHLIGHT ON DEEP-LINK
       ============================================================ */
    const hash = window.location.hash;
    if (hash && hash.startsWith('#post-')) {
        const el = document.querySelector(hash);
        if (el) {
            el.classList.add('post-flash-highlight');
            setTimeout(() => el.classList.remove('post-flash-highlight'), 3200);
        }
    }

    /* ============================================================
       POST PREVIEW (Reply form)
       ============================================================ */
    (function () {
        var previewBtn = document.getElementById('forum-preview-btn');
        var previewPane = document.getElementById('forum-reply-preview');
        var textarea = document.getElementById('forum-reply-body');
        if (!previewBtn || !previewPane || !textarea) return;

        previewBtn.addEventListener('click', function () {
            var isHidden = previewPane.style.display === 'none';
            if (isHidden) {
                var bbcode = textarea.value;
                if (!bbcode.trim()) {
                    previewPane.innerHTML = '<em class="text-secondary">Nothing to preview.</em>';
                } else {
                    var html = bbcode
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/\[b\](.*?)\[\/b\]/gi, '<strong>$1</strong>')
                        .replace(/\[i\](.*?)\[\/i\]/gi, '<em>$1</em>')
                        .replace(/\[u\](.*?)\[\/u\]/gi, '<u>$1</u>')
                        .replace(/\[center\](.*?)\[\/center\]/gi, '<div class="text-center">$1</div>')
                        .replace(/\[quote(?:=(.*?))?\](.*?)\[\/quote\]/gi, '<blockquote>$2</blockquote>')
                        .replace(/\[code\](.*?)\[\/code\]/gi, '<pre><code>$1</code></pre>')
                        .replace(/\[spoiler\](.*?)\[\/spoiler\]/gi, '<span style="background:#333;color:#333">$1</span>')
                        .replace(/\[url\](.*?)\[\/url\]/gi, '<a href="$1" target="_blank">$1</a>')
                        .replace(/\[img\](.*?)\[\/img\]/gi, '<img src="$1" style="max-width:100%;">')
                        .replace(/\[youtube\](.*?)\[\/youtube\]/gi, '<iframe src="https://www.youtube.com/embed/$1" style="width:100%;height:315px" allowfullscreen></iframe>')
                        .replace(/\[hr\]/gi, '<hr>')
                        .replace(/\n/g, '<br>');
                    previewPane.innerHTML = html;
                }
                previewPane.style.display = '';
                previewBtn.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Edit';
            } else {
                previewPane.style.display = 'none';
                previewBtn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview';
            }
        });
    })();

    /* ============================================================
       AUTO-SAVE DRAFTS (Reply form)
       ============================================================ */
    (function () {
        var textarea = document.getElementById('forum-reply-body');
        if (!textarea) return;

        var draftKey = 'forum_draft_topic_{{ $topic->id }}';
        var debounceTimer = null;

        // Restore draft on load
        var saved = localStorage.getItem(draftKey);
        if (saved && !textarea.value.trim()) {
            textarea.value = saved;
            var toast = document.createElement('div');
            toast.className = 'draft-restore-toast';
            toast.innerHTML = '<i class="bi bi-journal-text me-1"></i>Draft restored. ' +
                '<button id="draft-keep">Keep</button>' +
                '<button id="draft-dismiss" class="draft-dismiss">Dismiss</button>';
            document.body.appendChild(toast);

            var keepBtn = document.getElementById('draft-keep');
            var dismissBtn = document.getElementById('draft-dismiss');
            if (keepBtn) keepBtn.addEventListener('click', function () { toast.remove(); });
            if (dismissBtn) dismissBtn.addEventListener('click', function () {
                textarea.value = '';
                localStorage.removeItem(draftKey);
                toast.remove();
            });
        }

        textarea.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (textarea.value.trim()) {
                    localStorage.setItem(draftKey, textarea.value);
                } else {
                    localStorage.removeItem(draftKey);
                }
            }, 2000);
        });

        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                localStorage.removeItem(draftKey);
            });
        }
    })();

    /* ============================================================
       MULTI-QUOTE
       ============================================================ */
    (function () {
        var multiQuotePosts = {};

        document.querySelectorAll('.quote-post-btn').forEach(function (btn) {
            var postId = btn.dataset.postId;
            if (!postId) return;

            var mqBtn = document.createElement('button');
            mqBtn.type = 'button';
            mqBtn.className = 'forum-preview-btn multiquote-btn ms-1';
            mqBtn.innerHTML = '<i class="bi bi-plus-circle"></i>';
            mqBtn.title = 'Add to multi-quote';
            mqBtn.dataset.postId = postId;
            mqBtn.dataset.username = btn.dataset.username || 'Unknown';
            mqBtn.dataset.body = btn.dataset.body || '';

            mqBtn.addEventListener('click', function () {
                if (multiQuotePosts[postId]) {
                    delete multiQuotePosts[postId];
                } else {
                    multiQuotePosts[postId] = {
                        username: mqBtn.dataset.username,
                        body: mqBtn.dataset.body,
                    };
                }
                mqBtn.classList.toggle('active', !!multiQuotePosts[postId]);
                updateMultiQuoteBar();
            });

            btn.parentNode.insertBefore(mqBtn, btn.nextSibling);
        });

        function updateMultiQuoteBar() {
            var count = Object.keys(multiQuotePosts).length;
            var bar = document.getElementById('forum-multiquote-bar');
            var countEl = document.getElementById('mq-count');
            if (bar && countEl) {
                countEl.textContent = count;
                bar.style.display = count > 0 ? '' : 'none';
            }
        }

        var insertBtn = document.getElementById('mq-insert-btn');
        if (insertBtn) {
            insertBtn.addEventListener('click', function () {
                var replyTextarea = document.getElementById('forum-reply-body');
                if (!replyTextarea) return;

                var keys = Object.keys(multiQuotePosts);
                if (keys.length === 0) return;

                var quotes = '';
                keys.forEach(function (pid) {
                    var p = multiQuotePosts[pid];
                    try {
                        var body = JSON.parse(p.body);
                        quotes += '[quote="' + p.username + '"]\r\n' + body.trim() + '\r\n[/quote]\r\n\r\n';
                    } catch (e) {}
                });

                if (replyTextarea.value.trim() !== '') {
                    replyTextarea.value = replyTextarea.value.trimEnd() + '\r\n\r\n' + quotes;
                } else {
                    replyTextarea.value = quotes;
                }

                replyTextarea.focus();
                replyTextarea.setSelectionRange(replyTextarea.value.length, replyTextarea.value.length);
                clearMultiQuote();
            });
        }

        function clearMultiQuote() {
            multiQuotePosts = {};
            document.querySelectorAll('.multiquote-btn').forEach(function (btn) {
                btn.classList.remove('active');
            });
            updateMultiQuoteBar();
        }

        var clearBtn = document.getElementById('mq-clear-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearMultiQuote);
        }
    })();
</script>
@endsection

