@extends('layouts.app')

@section('content')
<h2 class="mb-3">
    <i class="bi bi-hdd-network text-info"></i> {{ $seedbox->name }} Torrents
</h2>
<a href="{{ route('seedboxes.index') }}" class="btn btn-secondary mb-3">
    <i class="bi bi-arrow-left-circle"></i> Back
</a>

{{-- ✅ Status section --}}
<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    @php
        $isConnected = isset($torrents) && $torrents->count() > 0;
    @endphp
    <span class="badge {{ $isConnected ? 'bg-success' : 'bg-danger' }}">
        <i class="bi {{ $isConnected ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
        {{ $isConnected ? 'Connected' : 'Offline' }}
    </span>


    {{-- 🔁 Auto-refresh toggle --}}
    <button id="toggle-refresh" class="btn btn-sm btn-outline-info ms-auto">
        <i class="bi bi-arrow-repeat"></i> Auto Refresh <span id="refresh-status">(off)</span>
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {!! session('success') !!}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> {!! session('error') !!}</div>
@endif

<!-- Add Torrent Box -->
<div class="card mb-4 shadow-sm bg-dark text-white border-secondary">
    <div class="card-header">
        <i class="bi bi-plus-circle-fill text-info"></i> Add Torrent
    </div>
    <div class="card-body">
        <form action="{{ route('seedboxes.addTorrent', $seedbox) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="torrent_file" class="form-label">Select .torrent file</label>
                <input class="form-control form-control-dark" type="file" name="torrent_file" id="torrent_file" accept=".torrent" required>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-upload"></i> Upload
            </button>
        </form>
    </div>
</div>

<!-- Search -->
<form method="GET" action="{{ route('seedboxes.torrents', $seedbox) }}" class="mb-4">
    <div class="input-group">
        <span class="input-group-text bg-dark text-white"><i class="bi bi-search"></i></span>
        <input 
            type="text" 
            name="search" 
            class="form-control form-control-dark" 
            placeholder="Search torrents by name..." 
            value="{{ request('search') }}"
        >
        <button class="btn btn-info" type="submit">
            <i class="bi bi-arrow-right-circle"></i> Search
        </button>
    </div>
</form>

@php
    $torrentItems = $torrents->items();
@endphp

<div class="card mb-3 shadow-sm bg-dark text-white border-secondary">
    <div class="card-header">
        <strong><i class="bi bi-list"></i> Torrents (Newest First)</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <tbody>
                    @foreach($torrentItems as $hash => $torrent)
                        @php
                            $name = $torrent[4] ?? 'Unknown';
                            $size = $torrent[5] ?? 0;
                            $path = $torrent[25] ?? 'Unknown';
                            $downloaded = $torrent[8] ?? 0;
                            $uploaded = $torrent[9] ?? 0;
                            $progress = $size > 0 ? round(($downloaded / $size) * 100, 2) : 0;
                            $state = $torrent[28] ?? 0; 
                            $ratio = $downloaded > 0 ? round($uploaded / $downloaded, 2) : 0;

                            if ($progress >= 80) $color = 'bg-success-gradient';
                            elseif ($progress >= 50) $color = 'bg-info-gradient';
                            elseif ($progress >= 30) $color = 'bg-warning-gradient';
                            else $color = 'bg-danger-gradient';

                            if ($state == 1) {
                                $statusText = 'Seeding';
                                $statusIcon = 'bi-arrow-up-circle text-success';
                                $actionIcon = ['type'=>'pause','icon'=>'bi-pause-circle-fill','title'=>'Pause','class'=>'text-warning'];
                            } elseif ($state == 2) {
                                $statusText = 'Downloading';
                                $statusIcon = 'bi-arrow-down-circle text-info';
                                $actionIcon = ['type'=>'pause','icon'=>'bi-pause-circle-fill','title'=>'Pause','class'=>'text-warning'];
                            } elseif ($state == 0) {
                                $statusText = 'Paused';
                                $statusIcon = 'bi-stop-circle text-secondary';
                                $actionIcon = ['type'=>'start','icon'=>'bi-play-circle-fill','title'=>'Start','class'=>'text-success'];
                            } else {
                                $statusText = 'Unknown';
                                $statusIcon = 'bi-question-circle text-secondary';
                                $actionIcon = ['type'=>'start','icon'=>'bi-play-circle-fill','title'=>'Start','class'=>'text-secondary'];
                            }

                            $addedDate = isset($torrent[21]) 
                                ? \Carbon\Carbon::createFromTimestamp($torrent[21])->format('Y-m-d H:i') 
                                : 'N/A';

                           
                        @endphp

                        <tr data-name="{{ $name }}">
                            <td style="width: 120px;">
                                <div class="progress" style="height: 25px; position: relative; background-color: #343a40; border-radius: 0.25rem;">
                                    <div class="progress-bar {{ $color }} progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         style="width: {{ $progress }}%; height: 100%;">
                                    </div>
                                    <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.85rem; font-weight: bold; color: #fff;">
                                        {{ $progress }}%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <strong><i class="bi bi-file-earmark-text"></i> {{ $name }}</strong><br>
                                <small class="text-muted fw-bold small">
                                    <i class="bi {{ $statusIcon }}"></i> {{ $statusText }} |
                                    <i class="bi bi-hdd"></i> {{ App\Helpers\FormatHelper::formatSize($size) }},
                                    <i class="bi bi-arrow-up-circle text-success"></i> {{ App\Helpers\FormatHelper::formatSize($uploaded) }},
                                    <i class="bi bi-arrow-down-circle text-info"></i> {{ App\Helpers\FormatHelper::formatSize($downloaded) }},
                                    Ratio: {{ $ratio }}
                                </small><br>
                                <small class="text-muted fw-bold small">
                                    <i class="bi bi-calendar"></i> Added: {{ $addedDate }} |
                                    <span class="badge bg-secondary">
            <b class="torrent-trackers" data-hash="{{ $hash }}">Loading trackers...</b>
        </span>
                                </small>

                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('seedboxes.'.$actionIcon['type'], [$seedbox, $hash]) }}" style="display:inline-block;">
                                    @csrf
                                    <button class="btn btn-link p-0 {{ $actionIcon['class'] }}" title="{{ $actionIcon['title'] }}">
                                        <i class="bi {{ $actionIcon['icon'] }}" style="font-size:1.5rem;"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('seedboxes.delete', [$seedbox, $hash]) }}" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link p-0 text-danger" title="Delete torrent" onclick="return confirm('Are you sure you want to delete this torrent? Only the torrent will be removed, not the data.');">
                                        <i class="bi bi-trash-fill" style="font-size:1.5rem;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $torrents->links('pagination::bootstrap-5') }}
</div>

{{-- 🔁 Auto-refresh script --}}
<script>
let refreshEnabled = false;
let intervalId = null;
document.getElementById('toggle-refresh').addEventListener('click', function () {
    refreshEnabled = !refreshEnabled;
    document.getElementById('refresh-status').textContent = refreshEnabled ? '(on)' : '(off)';
    if (refreshEnabled) {
        intervalId = setInterval(() => location.reload(), 15000);
    } else {
        clearInterval(intervalId);
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.torrent-trackers').forEach(td => {
        const hash = td.dataset.hash;
        fetch(`/seedboxes/{{ $seedbox->id }}/torrent/${hash}/trackers`)
            .then(res => res.json())
            .then(data => {
                if (data.trackers && data.trackers.length) {
                    const hosts = data.trackers.map(url => {
                        try {
                            let host = new URL(url).hostname;
                            host = host.replace(/^(tracker\.|www\.)/i, '');
                            return host;
                        } catch(e) {
                            return url;
                        }
                    });
                    td.textContent = [...new Set(hosts)].join(', ');
                } else {
                    td.textContent = 'N/A';
                }
            })
            .catch(() => td.textContent = 'Error');
    });
});
</script>


<style>
.progress-bar { transition: width 0.4s ease; }
.bg-success-gradient { background: linear-gradient(90deg, #28a745, #85e085) !important; }
.bg-info-gradient    { background: linear-gradient(90deg, #17a2b8, #6cd2e8) !important; }
.bg-warning-gradient { background: linear-gradient(90deg, #ffc107, #ffe08c) !important; }
.bg-danger-gradient  { background: linear-gradient(90deg, #dc3545, #f88a95) !important; }
</style>
@endsection
