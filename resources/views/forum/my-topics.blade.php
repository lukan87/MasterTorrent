@extends('layouts.app')
@section('content')
<div class="container forum-page py-4 py-lg-5">
    <nav class="forum-breadcrumb mb-4" aria-label="Breadcrumb"><a class="forum-breadcrumb-link" href="{{ route('forum.index') }}">Forum</a><span aria-hidden="true">/</span><span>My Topics</span></nav>
    <header class="forum-category-header mb-4"><div><h1 class="forum-category-title">My Topics</h1><p class="forum-category-description">Conversations you have participated in, ordered by last post.</p></div></header>
    <div class="forum-topic-list">
        @forelse($topics as $topic)
            @if($topic->category) @include('forum.partials.topic-row', ['category' => $topic->category, 'showCategory' => true]) @endif
        @empty<p class="forum-empty-state">You haven’t participated in any topics yet.</p>@endforelse
    </div>
    @if($topics->hasPages())<div class="forum-pagination mt-4">{{ $topics->links('pagination::bootstrap-5') }}</div>@endif
</div>
@include('forum.partials.category-css')
@include('forum.partials.common-css')
@include('forum.partials.back-to-top')
@endsection
