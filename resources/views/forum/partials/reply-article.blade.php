
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
@php
    $userClassId = $post->user?->user_class;
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

                        @if($post->user)

                            <div class="forum-user-info">

                                <i class="bi bi-calendar3 me-1"></i>

                                Joined

                                {{ $post->user->created_at?->format('M Y') }}

                            </div>


                            <div class="forum-user-info">

                                <i class="bi bi-chat-left-text me-1"></i>

                                {{ $post->user->forum_posts_count ?? 0 }}

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


