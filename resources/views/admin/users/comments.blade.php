@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 user-comments-page">

    {{-- HEADER --}}
    <div class="comments-header mb-3">
        <div>
            <h1 class="comments-title">
                <i class="bi bi-chat-square-text-fill me-2"></i>
                User Comments
            </h1>

            <div class="comments-subtitle">
                View comments posted by users across the site
            </div>
        </div>

        <span class="comments-badge">
            <i class="bi bi-chat-dots-fill me-1"></i>
            {{ $comments->total() }} comments
        </span>
    </div>

    {{-- COMMENTS --}}
    @if($comments->isEmpty())

        <div class="empty-comments-card">
            <i class="bi bi-chat-square"></i>
            <strong>No comments found.</strong>
            <span>There are currently no user comments to display.</span>
        </div>

    @else

        <div class="comments-list">

            @foreach($comments as $comment)

                <div class="comment-card">

                    <div class="comment-main">

                        {{-- USER --}}
                        <div class="comment-top">

                            @if($comment->user)

                                <a
                                    href="{{ route('profile.show', [
                                        'id' => $comment->user->id,
                                        'name' => $comment->user->name
                                    ]) }}"
                                    class="comment-user"
                                    style="color: {{ \App\Models\UserClass::getClassColor($comment->user->user_class) }};"
                                    data-bs-toggle="tooltip"
                                    title="{{ \App\Models\UserClass::getClassName($comment->user->user_class) }}">

                                    <i class="bi bi-person-fill me-1"></i>
                                    {{ $comment->user->name }}

                                </a>

                            @else

                                <span class="deleted-user">
                                    <i class="bi bi-person-x-fill me-1"></i>
                                    [Deleted User]
                                </span>

                            @endif

                            {{-- TORRENT --}}
                            <span class="comment-torrent">

                                <span class="torrent-label">
                                    <i class="bi bi-hdd-stack-fill me-1"></i>
                                    Commented on:
                                </span>

                                @if($comment->torrent)

                                    <a
                                        href="{{ route('torrents.show', $comment->torrent->id) }}"
                                        class="torrent-link">

                                        {{ $comment->torrent->name }}

                                    </a>

                                @else

                                    <span class="unknown-torrent">
                                        Unknown Torrent
                                    </span>

                                @endif

                            </span>

                        </div>

                        {{-- COMMENT CONTENT --}}
                        <div class="comment-content">
                            {!! convertCustomTagsToHtml($comment->comment) !!}
                        </div>

                        {{-- DATE --}}
                        <div class="comment-date">
                            <i class="bi bi-clock me-1"></i>
                            {{ $comment->created_at->diffForHumans() }}
                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        {{-- PAGINATION --}}
        <div class="pagination-wrap">
            {{ $comments->links('pagination::bootstrap-5') }}
        </div>

    @endif

</div>

<style>
    .user-comments-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .comments-header,
    .comment-card,
    .empty-comments-card {
        position: relative;
        background: linear-gradient(
            135deg,
            rgba(22, 32, 51, .96),
            rgba(15, 23, 42, .88)
        );
        border: 1px solid var(--ui-border, rgba(255, 255, 255, .08));
        border-radius: .65rem;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .16);
        overflow: hidden;
    }

    .comments-header::before,
    .comment-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--ui-accent, #20c997);
        opacity: .75;
    }

    .comments-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .9rem;
    }

    .comments-title {
        margin: 0;
        color: #f3f8fb;
        font-size: 18px;
        font-weight: 700;
    }

    .comments-title i {
        color: var(--ui-accent, #20c997);
    }

    .comments-subtitle {
        margin-top: .15rem;
        color: #718596;
        font-size: 11px;
    }

    .comments-badge {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: .3rem .55rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        border-radius: .4rem;
        color: #72e3bb;
        font-size: 10px;
        font-weight: 700;
    }

    .comments-list {
        display: flex;
        flex-direction: column;
        gap: .45rem;
    }

    .comment-card {
        padding: .65rem .8rem;
    }

    .comment-card:hover {
        background: linear-gradient(
            135deg,
            rgba(24, 38, 58, .97),
            rgba(15, 23, 42, .9)
        );
    }

    .comment-main {
        min-width: 0;
    }

    .comment-top {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .5rem;
        padding-bottom: .45rem;
        border-bottom: 1px solid rgba(255, 255, 255, .055);
    }

    .comment-user {
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .comment-user:hover {
        color: #fff !important;
    }

    .deleted-user {
        color: #ff8e98;
        font-size: 12px;
        font-weight: 600;
    }

    .comment-torrent {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .3rem;
        color: #718596;
        font-size: 11px;
    }

    .torrent-label {
        color: #718596;
    }

    .torrent-label i {
        color: var(--ui-accent, #20c997);
    }

    .torrent-link {
        max-width: 500px;
        overflow: hidden;
        color: #72e3bb;
        font-weight: 600;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .torrent-link:hover {
        color: #fff;
    }

    .unknown-torrent {
        color: #718596;
    }

    .comment-content {
        padding: .65rem 0 .45rem;
        color: #d1dde3;
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .comment-date {
        color: #647889;
        font-size: 10px;
    }

    .empty-comments-card {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .2rem;
        min-height: 110px;
        padding: 1rem;
        color: #718596;
        text-align: center;
    }

    .empty-comments-card i {
        margin-bottom: .1rem;
        color: var(--ui-accent, #20c997);
        font-size: 22px;
    }

    .empty-comments-card strong {
        color: #b9c8d0;
        font-size: 13px;
    }

    .empty-comments-card span {
        font-size: 11px;
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: .65rem;
    }

    .pagination {
        gap: 2px;
        margin-bottom: 0;
    }

    .pagination .page-link {
        background: rgba(22, 32, 51, .9);
        border-color: rgba(255, 255, 255, .075);
        color: #aabcc7;
        font-size: 11px;
        padding: .3rem .55rem;
        border-radius: .35rem !important;
    }

    .pagination .page-item.active .page-link {
        background: rgba(32, 201, 151, .13);
        border-color: rgba(32, 201, 151, .28);
        color: #73e2bb;
    }

    .pagination .page-link:hover {
        background: rgba(32, 201, 151, .07);
        border-color: rgba(32, 201, 151, .22);
        color: #fff;
    }

    @media (max-width: 767.98px) {
        .user-comments-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .comments-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .comment-top {
            align-items: flex-start;
            flex-direction: column;
            gap: .25rem;
        }

        .torrent-link {
            max-width: 100%;
        }
    }
</style>

@endsection