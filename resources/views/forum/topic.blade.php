@extends('layouts.app')
@push('scripts') @vite(['resources/js/page-browser.js', 'resources/js/forum-topic.js']) @endpush
@section('content')
<div class="container forum-page py-4 py-lg-5">
    <nav class="forum-breadcrumb mb-4" aria-label="Breadcrumb"><a class="forum-breadcrumb-link" href="{{ route('forum.index') }}">Forum</a><span aria-hidden="true">/</span><a class="forum-breadcrumb-link" href="{{ route('forum.category', $category->slug) }}">{{ $category->name }}</a></nav>
    <header class="forum-topic-header mb-4">
        <div class="forum-topic-category">{{ $category->name }}</div>
        <h1 class="forum-topic-title">{{ $topic->title }}</h1>
        <div class="forum-topic-meta">
            <span>{{ $topic->user?->name ?? 'Former member' }} · {{ $topic->created_at->diffForHumans() }}</span>
            <span><span data-reply-count>{{ number_format($replies->total()) }}</span> replies · {{ number_format($topic->views) }} views</span>
            @if($topic->is_pinned)<span class="forum-topic-badge pinned">Pinned</span>@endif
            @if($topic->is_locked)<span class="forum-topic-badge locked">Locked</span>@endif
            @if($category->is_private)<span class="forum-topic-badge locked">Staff only</span>@endif
        </div>
        <div class="forum-topic-actions mt-3">
            @if(\App\Services\ForumAccess::canParticipate(auth()->user()))
                @if(!$topic->is_locked)<a class="forum-follow-btn" href="#forum-reply-body">Reply</a>@endif
                <form method="POST" action="{{ route($isFollowing ? 'forum.topic.unfollow' : 'forum.topic.follow', ['category' => $category->slug, 'topic' => $topic->slug]) }}">
                    @csrf @if($isFollowing) @method('DELETE') @endif
                    <button class="forum-follow-btn" type="submit">{{ $isFollowing ? 'Unfollow' : 'Follow topic' }}</button>
                </form>
            @endif
            @auth<a class="forum-latest-btn" href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'unread' => 1]) }}">First unread</a>@endauth
            @if($topic->last_post_id)<a class="forum-latest-btn" href="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug, 'post' => $topic->last_post_id]) }}">Latest post</a>@endif
            @if(\App\Services\ForumAccess::allows(auth()->user(), 'manage_topics') || \App\Services\ForumAccess::allows(auth()->user(), 'delete_topics'))
                <details class="forum-staff-menu"><summary>Topic tools</summary><div>
                    @if(\App\Services\ForumAccess::allows(auth()->user(), 'manage_topics'))
                        @foreach(['lock' => ($topic->is_locked ? 'Unlock' : 'Lock'), 'pin' => ($topic->is_pinned ? 'Unpin' : 'Pin')] as $action => $label)
                            <form method="POST" action="{{ route('forum.topic.'.$action, ['category' => $category->slug, 'topic' => $topic->slug]) }}">@csrf<button type="submit">{{ $label }} topic</button></form>
                        @endforeach
                    @endif
                    @if(\App\Services\ForumAccess::allows(auth()->user(), 'delete_topics'))
                        <form method="POST" action="{{ route('forum.topic.delete', ['category' => $category->slug, 'topic' => $topic->slug]) }}" onsubmit="return confirm('Permanently delete this topic and all replies?')">@csrf @method('DELETE')<button type="submit">Delete topic</button></form>
                    @endif
                </div></details>
            @endif
        </div>
    </header>
    <div data-page-browser data-browse-url="{{ route('forum.topic', ['category' => $category->slug, 'topic' => $topic->slug]) }}">
        @include('forum.topic-results')
    </div>
    @if(\App\Services\ForumAccess::canParticipate(auth()->user()) && !$topic->is_locked)
        <section class="forum-reply-form mt-4" aria-labelledby="forum-reply-heading">
            <h2 id="forum-reply-heading">Join the conversation</h2>
            <form data-forum-reply method="POST" action="{{ route('forum.topic.reply', ['category' => $category->slug, 'topic' => $topic->slug]) }}">
                @csrf
                <label for="forum-reply-body" class="form-label">Your reply</label>
                @include('forum.partials.bbcode-toolbar')
                <textarea id="forum-reply-body" name="body" class="form-control forum-textarea @error('body') is-invalid @enderror" rows="7" minlength="3" maxlength="10000" required>{{ old('body') }}</textarea>
                @error('body')<p class="invalid-feedback" role="alert">{{ $message }}</p>@enderror
                <div class="forum-post-preview-pane mt-3" id="forum-reply-preview" style="display:none"></div>
                <div class="multiquote-bar mt-3" id="forum-multiquote-bar" style="display:none"><span id="mq-count">0</span> posts selected <button type="button" id="mq-insert-btn">Insert all</button><button type="button" id="mq-clear-btn">Clear</button></div>
                <div class="d-flex gap-2 mt-3"><button class="forum-submit-btn" type="submit">Post reply</button><button class="forum-preview-btn" id="forum-preview-btn" type="button">Preview</button></div>
                <p class="forum-rank-note mt-2">Up to 10,000 characters. Your draft is saved in this browser.</p>
            </form>
        </section>
    @elseif($topic->is_locked)<p class="forum-empty-state">This topic is locked. You can still read the conversation.</p>
    @elseif(auth()->check())<p class="forum-empty-state">Your forum posting access is blocked.</p>
    @else<p class="forum-empty-state"><a href="{{ route('login') }}">Sign in</a> to join the conversation.</p>@endif
</div>
@include('forum.partials.topic-css')
@include('forum.partials.common-css')
@include('forum.partials.scripts', ['draftKind' => 'reply', 'draftId' => $topic->id])
@include('forum.partials.back-to-top')
@endsection
