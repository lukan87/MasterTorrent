
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

                        <div class="forum-user-rank">

                            @switch($firstPost->user?->user_class)

                                @case(1)

                                    User

                                    @break

                                @case(2)

                                    Power User

                                    @break

                                @case(3)

                                    VIP

                                    @break

                                @case(4)

                                    Elite User

                                    @break

                                @case(5)

                                    Moderator

                                    @break

                                @case(6)

                                    Administrator

                                    @break

                                @default

                                    Member

                            @endswitch

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

                                {{ $firstPost->user->forumPosts()->count() }}

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

                            {!! nl2br(e($firstPost->body)) !!}

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

    <article id="post-{{ $post->id }}"
             class="forum-reply-box forum-reply-post mb-4">

             <div class="row g-0">

    <div class="col-md-3 col-lg-2">

        <div class="forum-user-panel">



                        {{-- AVATAR --}}

                        <div class="forum-avatar">

                            @if(
                                $post->user &&
                                $post->user->profile_image
                            )

                                <img
                                    src="{{ $post->user->profile_image }}"
                                    alt="{{ $post->user->name }}"
                                >

                            @else

                                <div class="forum-avatar-placeholder">

                                    <i class="bi bi-person-fill"></i>

                                </div>

                            @endif

                        </div>


                        {{-- USERNAME --}}

                        @if($post->user)

                            <a href="{{ route('profile.show', [
                                'id' => $post->user->id,
                                'username' => $post->user->name
                            ]) }}"
                               class="forum-username">

                                {{ $post->user->name }}

                            </a>

                        @else

                            <span class="forum-username">

                                Unknown

                            </span>

                        @endif


                        {{-- TITLE --}}

                        @if($post->user?->title)

                            <div class="forum-user-title">

                                {{ $post->user->title }}

                            </div>

                        @endif


                        {{-- USER CLASS --}}

                        <div class="forum-user-rank">

                            @switch($post->user?->user_class)

                                @case(1)

                                    User

                                    @break

                                @case(2)

                                    Power User

                                    @break

                                @case(3)

                                    VIP

                                    @break

                                @case(4)

                                    Elite User

                                    @break

                                @case(5)

                                    Moderator

                                    @break

                                @case(6)

                                    Administrator

                                    @break

                                @default

                                    Member

                            @endswitch

                        </div>


                        {{-- JOIN DATE / POSTS --}}

                        @if($post->user)

                            <div class="forum-user-info">

                                <i class="bi bi-calendar3 me-1"></i>

                                Joined

                                {{ $post->user->created_at?->format('M Y') }}

                            </div>


                            <div class="forum-user-info">

                                <i class="bi bi-chat-left-text me-1"></i>

                                {{ $post->user->forumPosts()->count() }}

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

                            <div class="forum-post-meta-left">

                                <span>

                                    <i class="bi bi-reply-fill me-1"></i>

                                    Reply

                                </span>


                                <span>

                                    <i class="bi bi-clock me-1"></i>

                                    {{ $post->created_at->format('d M Y H:i') }}

                                </span>


                                @if($post->edited_at)

                                    <span class="post-edited">

                                        <i class="bi bi-pencil me-1"></i>

                                        Edited

                                        {{ $post->edited_at->diffForHumans() }}

                                    </span>

                                @endif

                            </div>




                            {{-- ACTIONS --}}

                            <div class="forum-post-actions">

                                    {{-- QUOTE --}}

        @auth

            @if(
                !$topic->is_locked &&
                !auth()->user()->forumblock
            )

               <button
    type="button"
    class="forum-post-action quote-post-btn"
    data-post-id="{{ $post->id }}"
    data-username="{{ $post->user->name ?? 'Unknown' }}"
    data-body='@json($post->body)'
>
    <i class="bi bi-quote me-1"></i>
    Quote
</button>
            @endif

        @endauth

                                @auth

                                    {{-- EDIT --}}

                                    @if(
                                        $post->user_id === auth()->id()
                                        || auth()->user()->user_class > \App\Models\UserClass::MODERATOR
                                    )

                                        <a href="{{ route('forum.post.edit', [
                                            'category' => $category->slug,
                                            'topic' => $topic->slug,
                                            'post' => $post->id,
                                        ]) }}"
                                           class="forum-post-action edit-action">

                                            <i class="bi bi-pencil-square me-1"></i>

                                            Edit

                                        </a>

                                    @endif


                                    {{-- DELETE - STAFF ONLY --}}

                                   @if(auth()->user()->user_class > \App\Models\UserClass::MODERATOR)

    <form method="POST"
          action="{{ route('forum.post.delete', [
              'category' => $category->slug,
              'topic' => $topic->slug,
              'post' => $post->id,
          ]) }}"
          class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this post?');">

        @csrf

        @method('DELETE')

        <button type="submit"
                class="forum-post-action delete-action">

            <i class="bi bi-trash3 me-1"></i>

            Delete

        </button>

    </form>

@endif

                                @endauth


                                {{-- POST ANCHOR --}}

                                <a href="#post-{{ $post->id }}"
                                   class="post-anchor">

                                    #{{ $post->id }}

                                </a>

                            </div>

                        </div>


                        {{-- BODY --}}

                        <div class="forum-post-body">

    {!! convertCustomTagsToHtml($post->body) !!}

</div>

{{-- LIKES --}}

@auth

   @php
    $postLikes = $post->likes;
    $postLikeCount = $postLikes->count();
    $postReaction = $postLikes
        ->firstWhere('user_id', auth()->id())
        ?->reaction;
        $reactionCounts = $postLikes->groupBy('reaction')->map->count();
@endphp

    <div class="forum-like-section">

   @if($post->user_id !== auth()->id())
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
                      'post' => $post->id,
                  ]) }}"
                  class="d-inline">

                @csrf

                <input type="hidden"
                       name="reaction"
                       value="{{ $reaction }}">

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

@else
    @if($postLikeCount > 0)
        <span class="reaction-total">
            {{ $postLikeCount }}
        </span>
    @endif
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
                data-bs-target="#likeModal-{{ $post->id }}">

            {{ $remainingLikes }} {{ $remainingLikes === 1 ? 'other' : 'others' }}

        </button>

    @endif

    @if($likedUsers->count() > 0 && $remainingLikes === 0)

        <button type="button"
                class="liked-by-more"
                data-bs-toggle="modal"
                data-bs-target="#likeModal-{{ $post->id }}">

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

                </article>


        {{-- =========================================================
             REPLY REACTIONS MODAL
             ========================================================= --}}

        @if($postLikeCount > 0)

            <div class="modal fade"
                 id="likeModal-{{ $post->id }}"
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
                                    data-bs-dismiss="modal"
                                    aria-label="Close">
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

</script>

@endsection

