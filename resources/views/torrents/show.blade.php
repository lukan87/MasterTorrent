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


@include('torrents.partials.showbar')


@if ($torrent->images->isNotEmpty())

@include('torrents.partials.screens')

@endif


{{-- Details section --}}
<div class="card card-blur">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">Description</a>
            </li>

            @if(!empty($torrent->mediainfo))
                <li class="nav-item">
                    <a class="nav-link" id="mediainfo-tab" data-bs-toggle="tab" href="#mediainfo" role="tab" aria-controls="mediainfo" aria-selected="false">Media Info</a>
                </li>
            @endif

            @if($torrent->files && $torrent->files->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link" id="files-tab" data-bs-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">Files</a>
                </li>
            @endif

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <li class="nav-item">
                    <a class="nav-link" id="snatched-tab" data-bs-toggle="tab" href="#snatched" role="tab" aria-controls="snatched" aria-selected="false">Snatched</a>
                </li>
            @endif
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                <div class="scrollable-content">
                    {!! convertCustomTagsToHtml($torrent->description) !!}
                </div>
            </div>

            @if(!empty($torrent->mediainfo))
                <div class="tab-pane fade" id="mediainfo" role="tabpanel" aria-labelledby="mediainfo-tab">
                    @include('torrents.partials.mediainfo')
                </div>
            @endif

            @if(!empty($fileTree))
                <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                    <h5>Files in Torrent</h5>
                    <ul class="list-group">
                        @php renderTree($fileTree); @endphp
                    </ul>
                </div>
            @endif

        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
<div class="tab-pane fade" id="snatched" role="tabpanel" aria-labelledby="snatched-tab">
    <div class="glass-card snatched-card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person-check-fill me-2"></i>Users That Snatched The Torrent</h5>
        </div>
        <div class="card-body">
            @if($snatched->isEmpty())
                <p class="text-muted">No users have snatched this torrent yet.</p>
            @else
                <ul class="list-unstyled snatched-list mb-0">
                    @foreach($snatched as $history)
                    <li class="snatched-item">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <a href="{{ route('profile.show', ['id'=>$history->user_id,'name'=>$history->user_name]) }}" 
                                   class="snatched-user-link" 
                                   data-bs-toggle="tooltip" 
                                   title="See {{$history->user_name}}'s Profile">
                                    {{ $history->user_name }}
                                </a>
                                <div class="stats mt-1">
                                    <span class="badge bg-primary"><i class="bi bi-download me-1"></i> {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) }}</span>
                                    <span class="badge bg-success"><i class="bi bi-upload me-1"></i> {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) }}</span>
                                    <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> {{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}</span>
                                </div>
                            </div>
                            <div class="seeder-info mt-2 mt-md-0">
                                <span class="badge {{ $history->seeder ? 'bg-success' : 'bg-danger' }}">
                                    {{ $history->seeder ? 'Seeding' : 'Not Seeding' }}
                                </span>
                                <small class="text-muted d-block mt-1">Snatched: {{ $history->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
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


@include('torrents.partials.comments')
@include('torrents.partials.similar')
{{-- @include('torrents.partials.recommended') --}}



<!-- Scrollable Content Style -->
<style>
.scrollable-content {
    max-height: 750px;
    overflow-y: auto;
}

.snatched-card {
    border-radius: 16px;
    overflow: hidden;
    background: rgba(20,20,20,0.6);
    backdrop-filter: blur(4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.08);
}

.snatched-card .card-header {
    font-weight: 600;
    background: rgba(0,123,255,0.15);
    border-bottom: none;
}

.snatched-list {
    padding: 0;
    margin: 0;
}

.snatched-item {
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 12px;
    background: rgba(255,255,255,0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.snatched-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.35);
}

.snatched-user-link {
    font-weight: 600;
    color: #00BFFF;
    text-decoration: none;
    transition: color 0.2s ease;
}

.snatched-user-link:hover {
    color: #1E90FF;
    text-decoration: underline;
}

.stats .badge {
    font-size: 0.75rem;
    margin-right: 0.25rem;
}

.seeder-info .badge {
    font-weight: 600;
    font-size: 0.8rem;
}

@media (max-width: 575px) {
    .snatched-item {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .seeder-info {
        margin-top: 0.5rem;
    }
}


</style>


</div>

@php
function renderTree($tree, $level = 0) {
    echo '<ul class="list-group" style="margin-left:' . ($level * 15) . 'px;">';
    foreach ($tree as $name => $subtree) {
        if ($name === '_size') continue; // Skip size placeholder
        echo '<li class="list-group-item">';

        if (is_array($subtree) && count($subtree) > 0 && !isset($subtree['_size'])) {
            // Folder
            $id = uniqid('folder_');
            echo '<span class="toggle-folder d-block p-2" data-toggle="#' . $id . '" style="cursor:pointer;" data-bs-toggle="tooltip" title="Click to see folder content">📂 ' . $name . '</span>';
            echo '<ul id="' . $id . '" class="list-group ms-3" style="display: none;">';
            renderTree($subtree, $level + 1);
            echo '</ul>';
        } else {
            // File with icons based on extension
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $size = isset($subtree['_size']) ? $subtree['_size'] : '';

            // File type icons
            $icons = [
                'mp4' => '🎬', 'mkv' => '🎬', 'avi' => '🎬', 'mov' => '🎬', 'wmv' => '🎬', // Videos
                'mp3' => '🎵', 'flac' => '🎵', 'wav' => '🎵', 'aac' => '🎵', // Audio
                'srt' => '📜', 'sub' => '📜', 'ass' => '📜', // Subtitles
                'jpg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'bmp' => '🖼️', // Images
                'zip' => '📦', 'rar' => '📦', '7z' => '📦', // Compressed files
                'txt' => '📄', 'nfo' => '📄', 'pdf' => '📄', 'doc' => '📄', 'docx' => '📄', // Documents
                'exe' => '🖥️', 'msi' => '🖥️', // Executables
                'iso' => '💿', 'img' => '💿', 'bin' => '💿', // Disc Images
            ];
            $icon = $icons[strtolower($extension)] ?? '📄'; // Default to document icon

            echo '<span class="d-block p-2">' . $icon . ' ' . $name . ' - <small>' . $size . '</small></span>';
        }

        echo '</li>';
    }
    echo '</ul>';
}
@endphp

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Bootstrap tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Toggle folder visibility
        document.querySelectorAll('.toggle-folder').forEach(function(folder) {
            folder.addEventListener('click', function() {
                const target = document.querySelector(folder.getAttribute('data-toggle'));
                if (target) {
                    target.style.display = target.style.display === 'none' ? 'block' : 'none';
                }
            });
        });
    });
</script>
@endpush





<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-folder').forEach(folder => {
            folder.addEventListener('click', function() {
                let target = document.querySelector(this.dataset.toggle);
                if (target.style.display === 'none') {
                    target.style.display = 'block';
                } else {
                    target.style.display = 'none';
                }
            });
        });
    });
</script>



@endsection
