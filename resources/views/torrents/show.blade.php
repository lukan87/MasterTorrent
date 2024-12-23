@extends('layouts.app')

@section('title',  $torrent->name )

@section('content')

<div class="container-fluid">



 @if($torrent->tmdb_type === 'movie')
    @include('torrents.partials.movie')
@elseif($torrent->tmdb_type === 'tv')
    @include('torrents.partials.tv')
@elseif($torrent->steamid)
    @include('torrents.partials.game')
@else
    @include('torrents.partials.default')
@endif


            <div class="card card-blur mb-3 mt-3">
    <div class="card-header">
        <h6 class="card-title">{{ $torrent->name }}</h6>
    </div>
    <div class="card-body d-flex justify-content-between align-items-center">

<!-- Left: Download and Edit Links -->
<div>
    <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-primary btn-sm">
        Download
    </a>
    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
    <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-secondary btn-sm">
        Edit
    </a>
    @endif
</div>

<!-- Right: Seeders, Leechers, and Times Completed -->
<div class="d-flex ms-auto">


<p class="mb-0 mx-2">
        <strong><i class="bi bi-tags" data-bs-toggle="tooltip" title="Category"></i></strong>
        {{ $torrent->category->name }}
    </p>

@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
<a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?seeders">
    <p class="mb-0 mx-2">
        <strong><i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i></strong>
        {{ $torrent->seeders }}
    </p>
</a>
@else
    <p class="mb-0 mx-2">
        <strong><i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i></strong>
        {{ $torrent->seeders }}
    </p>
@endif

@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
<a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?leechers">
    <p class="mb-0 mx-2"> <strong><i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i></strong>
        {{ $torrent->leechers }}
    </p>
</a>
@else
<p class="mb-0 mx-2">
    <strong><i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i></strong>
     {{ $torrent->leechers }}</p>
@endif




    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
    <p class="mb-0 mx-2">
    <strong>
        <i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i>
    </strong>
    <a href="{{ route('torrent.history', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-decoration-none">
        {{ $torrent->times_completed }}
    </a>
</p>

    @else
    <p class="mb-0 mx-2"><strong><i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i></strong> {{ $torrent->times_completed }}</p>
    @endif
    <p class="mb-0 mx-2"><strong><i class="bi bi-pie-chart-fill" data-bs-toggle="tooltip" title="Size"></i></strong> {{ \App\Helpers\FormatHelper::formatSize($torrent->size) }}</p>
</div>

</div>


</div>



<!-- Bootstrap Tabs -->
<div class="card card-blur">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">Description</a>
            </li>

            <!-- Media Info Tab - Only display if $mediainfo is set and not null -->
            @if(isset($mediainfo) && $mediainfo !== null)
                <li class="nav-item">
                    <a class="nav-link" id="mediainfo-tab" data-bs-toggle="tab" href="#mediainfo" role="tab" aria-controls="mediainfo" aria-selected="false">Media Info</a>
                </li>
            @endif

            <!-- Files Tab - Only display if files are available -->
            @if($torrent->files && $torrent->files->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link" id="files-tab" data-bs-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">Files</a>
                </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" id="comments-tab" data-bs-toggle="tab" href="#comments" role="tab" aria-controls="comments" aria-selected="false">Comments</a>
            </li>

            <!-- Snatched Tab -->
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
            <li class="nav-item">
                <a class="nav-link" id="snatched-tab" data-bs-toggle="tab" href="#snatched" role="tab" aria-controls="snatched" aria-selected="false">Snatched</a>
            </li>
            @endif
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content" id="myTabContent">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                <div class="scrollable-content">
                    {!! convertCustomTagsToHtml($torrent->description) !!}
                </div>
            </div>

            <!-- Media Info Tab -->
            @if(isset($mediainfo) && $mediainfo !== null)
                <div class="tab-pane fade" id="mediainfo" role="tabpanel" aria-labelledby="mediainfo-tab">
                    @include('torrents.partials.mediainfo')
                </div>
            @endif

            <!-- Files Tab -->
            @if($torrent->files && $torrent->files->isNotEmpty())
                <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                    <h5>Files in Torrent</h5>
                    <ul class="list-group">
                        @foreach($torrent->files as $file)
                            <li class="list-group-item">
                            <strong>{{ $file->filename }}</strong> - {{ \App\Helpers\FormatHelper::formatSize($file->size) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Comments Tab -->
            <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                <!-- Comment Form -->
                <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="commentable_id" value="{{ $torrent->id }}">
                    <input type="hidden" name="commentable_type" value="torrent">
                    <input type="hidden" name="torrent_id" value="{{ $torrent->id }}">
                    <div class="mb-3">
                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Comment</button>
                </form>
                <hr>

                <!-- Comments Section -->
                <div class="tt_block rounded">
    <h5>Comments for {{ $torrent->name }}</h5>
    @if($comments->isEmpty())
        <p>No comments yet</p>
    @else
        @foreach($comments as $comment)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-subtitle mb-2 text-muted">
                        {{ $comment->user->name ?? 'Unknown' }} <b>@ {{ $comment->created_at }}</b>
                    </h5>
                    <p class="card-text">{{ $comment->comment }}</p>
                    @if ($comment->user_id == Auth::id())
                        <!-- Add edit or delete options here if needed -->
                    @endif
                </div>
            </div>
        @endforeach
        {{ $comments->links() }} <!-- Pagination links -->
    @endif
</div>


            </div>

            <!-- Snatched Tab -->
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
            <div class="tab-pane fade" id="snatched" role="tabpanel" aria-labelledby="snatched-tab">
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Users That Snatched The Torrent</h5>
                    </div>
                    <div class="card-body">
                        @if($snatched->isEmpty())
                            <p>No users have snatched this torrent yet.</p>
                        @else
                            <ul class="list-group">
                            @foreach($snatched as $history)
    <li class="list-group-item">
        <strong>
            <a href="{{ route('profile.show', ['id' => $history->user_id, 'name' => $history->user_name]) }}" data-bs-toggle="tooltip" data-bs-title="See {{$history->user_name}}'s Profile">
                {{ $history->user_name }}
            </a>
            - Downloaded: {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) }}
            / Uploaded: {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) }}
            / Seedtime: {{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}
        </strong>
        <br>
        <small>Snatched on: {{ $history->created_at->diffForHumans() }} / Seeder:
            <span class="{{ $history->seeder ? 'text-success' : 'text-danger' }}">
    {{ $history->seeder ? 'Yes' : 'No' }}
            </span>
        </small>
    </li>
@endforeach

                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@include('torrents.partials.similar')
@include('torrents.partials.recommended')



<!-- Scrollable Content Style -->
<style>
.scrollable-content {
    max-height: 750px;
    overflow-y: auto;
}


</style>


</div>


@endsection
