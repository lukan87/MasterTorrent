

@error('torrent_file')

    <div role="alert" class="alert modern-alert modern-alert-danger mb-4">

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        {{ $message }}

    </div>

@enderror

<div class="seedbox-page">

    <div class="container-fluid py-5">

        {{-- =========================================

            HERO HEADER

        ========================================= --}}

        @if(auth()->user()->user_class >= \App\Models\UserClass::UPLOADER || auth()->user()->uploadpos === 'yes')
        <div class="mb-3"><a class="btn btn-outline-info btn-sm" href="{{ route('profile.api.publishing',['seedbox_id'=>$seedbox->id]) }}">Publish completed content</a></div>
        @endif
        <div class="seedbox-hero mb-4">

            <div class="hero-content">

                <div class="hero-kicker">

                    REMOTE CLIENT • TORRENT CONTROL

                </div>

                <h1 class="hero-title">

                    <i class="bi bi-hdd-network-fill me-2"></i>

                    {{ $seedbox->name }}

                </h1>

                <div class="hero-subtitle">

                    Manage torrents, monitor activity and control downloads.

                </div>

            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">



                <span class="connection-badge {{ $isConnected ? 'online' : 'offline' }}">

                    <i class="bi {{ $isConnected

                        ? 'bi-check-circle-fill'

                        : 'bi-x-circle-fill' }}"></i>

                    {{ $isConnected ? 'Connected' : 'Offline' }}

                </span>

                <a href="{{ route('seedboxes.index') }}"

                   class="btn modern-back-btn">

                    <i class="bi bi-arrow-left-circle me-1"></i>

                    Back

                </a>

            </div>

        </div>

        {{-- =========================================

            SESSION ALERTS

        ========================================= --}}

        @if(session('success'))

            <div role="alert" class="alert modern-alert modern-alert-success">

                <div>

                    <i class="bi bi-check-circle-fill me-2"></i>

                    {{ session('success') }}

                </div>

                <button class="btn-close btn-close-white"

                        data-bs-dismiss="alert"></button>

            </div>

        @endif

        @if(session('error'))

            <div role="alert" class="alert modern-alert modern-alert-danger">

                <div>

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    {{ session('error') }}

                </div>

                <button class="btn-close btn-close-white"

                        data-bs-dismiss="alert"></button>

            </div>

        @endif

        {{-- =========================================

            STATS

        ========================================= --}}

        @if($connectionError)
            <div class="alert modern-alert modern-alert-danger" role="alert">{{ $connectionError }}</div>
        @endif
        <div class="stats-grid mb-4">

            <div class="modern-stat-card">

                <i class="bi bi-hdd-network text-info"></i>

                <div class="stat-value">

                    {{ $stats['total'] }}

                </div>

                <div class="stat-label">

                    Total Torrents

                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-arrow-up-circle text-success"></i>

                <div class="stat-value">

                    {{ $stats['seeding'] }}

                </div>

                <div class="stat-label">

                    Seeding

                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-arrow-down-circle text-primary"></i>

                <div class="stat-value">

                    {{ $stats['downloading'] }}

                </div>

                <div class="stat-label">

                    Downloading

                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-pause-circle text-secondary"></i>

                <div class="stat-value">

                    {{ $stats['paused'] }}

                </div>

                <div class="stat-label">

                    Paused

                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-hdd-stack text-warning"></i>

                <div class="stat-value">

                    {{ App\Helpers\FormatHelper::formatSize($stats['totalSize']) }}

                </div>

                <div class="stat-label">

                    Total Size

                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-cloud-arrow-up text-info"></i>

                <div class="stat-value">

                    {{ App\Helpers\FormatHelper::formatSize($stats['totalUploaded']) }}

                </div>

                <div class="stat-label">

                    Uploaded

                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-cloud-arrow-down theme-text"></i>

                <div class="stat-value">

                    {{ App\Helpers\FormatHelper::formatSize($stats['totalDownloaded']) }}

                </div>

                <div class="stat-label">

                    Downloaded

                </div>

            </div>

        </div>

        {{-- =========================================

            ADD TORRENT

        ========================================= --}}

        <div class="modern-card mb-4">

            <div class="modern-card-header">

                <h5>

                    <i class="bi bi-cloud-arrow-up-fill text-info me-2"></i>

                    Add Torrent

                </h5>

            </div>

            <div class="modern-card-body">

                <form action="{{ route('seedboxes.addTorrent', $seedbox) }}"

                      method="POST"

                      enctype="multipart/form-data">

                    @csrf

                    <div class="row g-3 align-items-end">

                        <div class="col-md-9">

                            <label class="modern-label mb-2">

                                Select .torrent file

                            </label>

                            <input class="form-control modern-input"

                                   type="file"

                                   name="torrent_file"

                                   id="torrent_file"

                                   accept=".torrent"

                                   required>

                        </div>

                        <div class="col-md-3">

                            <button type="submit"

                                    class="btn modern-upload-btn w-100">

                                <i class="bi bi-upload me-1"></i>

                                Upload

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- =========================================

            SEARCH

        ========================================= --}}

        <div class="modern-card mb-4">

            <div class="modern-card-body">

                <form method="GET"

                      action="{{ route('seedboxes.torrents', $seedbox) }}">

                    <div class="modern-search-wrap">

                        <i class="bi bi-search search-icon"></i>

                        <input type="text"

                               name="search" aria-label="Search torrents by name"

                               class="modern-search-input"

                               placeholder="Search torrents by name..."

                               value="{{ request('search') }}">

                        <select name="status" class="form-select" aria-label="Torrent status">
                            @foreach(['all' => 'All statuses', 'seeding' => 'Seeding', 'downloading' => 'Downloading', 'paused' => 'Paused'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status', 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn modern-search-btn"

                                type="submit">

                            Search

                        </button>

                    </div>

                </form>

            </div>

        </div>

        @php

            $torrentItems = $torrents->items();

        @endphp

        {{-- =========================================

            TORRENT LIST

        ========================================= --}}

        <div class="modern-card">

            <div class="modern-card-header">

                <h5>

                    <i class="bi bi-list-ul text-info me-2"></i>

                    Torrents

                </h5>

                <span class="torrent-count">

                    {{ count($torrentItems) }}

                </span>

            </div>

            <div class="modern-card-body p-0">

                <div class="table-responsive">

                    <table class="table modern-table align-middle mb-0">

                        <tbody>

                        @forelse($torrentItems as $hash => $torrent)

                            @php

                                $name = $torrent[4] ?? 'Unknown';

                                $size = $torrent[5] ?? 0;

                                $path = $torrent[25] ?? 'Unknown';

                                $downloaded = $torrent[8] ?? 0;

                                $uploaded = $torrent[9] ?? 0;

                                $progress = $size > 0 ? min(100, max(0, round(($downloaded / $size) * 100, 2))) : 0;

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

                                $canUpload = Auth::check() && (

                                    Auth::user()->user_class >= \App\Models\UserClass::UPLOADER ||

                                    Auth::user()->uploadpos === 'yes'

                                );

                            @endphp

                            <tr data-seedbox-row="{{ $hash }}" data-name="{{ $name }}">

                                {{-- PROGRESS --}}

                                <td class="progress-column">

                                    <div class="modern-progress" role="progressbar" aria-label="Download progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">

                                        <div class="progress-bar {{ $color }}"

                                             style="width: {{ $progress }}%"></div>

                                        <span class="progress-text">

                                            {{ $progress }}%

                                        </span>

                                    </div>

                                </td>

                                {{-- INFO --}}

                                <td>

                                    <div class="torrent-name">

                                        <i class="bi bi-file-earmark-text text-info me-2"></i>

                                        {{ $name }}

                                    </div>

                                    <div class="torrent-meta">

                                        <span class="torrent-badge status-badge">

                                            <i class="bi {{ $statusIcon }}"></i>

                                            {{ $statusText }}

                                        </span>

                                        <span class="torrent-badge ratio-badge">

                                            <i class="bi bi-arrow-up-down"></i>

                                            Ratio {{ $ratio }}

                                        </span>

                                        <span class="torrent-badge upload-badge">

                                            <i class="bi bi-arrow-up-circle"></i>

                                            {{ App\Helpers\FormatHelper::formatSize($uploaded) }}

                                        </span>

                                        <span class="torrent-badge download-badge">

                                            <i class="bi bi-arrow-down-circle"></i>

                                            {{ App\Helpers\FormatHelper::formatSize($downloaded) }}

                                        </span>

                                    </div>

                                    <div class="torrent-extra">

                                        <div>

                                            <i class="bi bi-calendar3 me-1"></i>

                                            Added: {{ $addedDate }}

                                        </div>

                                        <div>

                                            <span class="tracker-badge">

                                                <b class="torrent-trackers"

                                                   data-hash="{{ $hash }}">

                                                    Loading trackers...

                                                </b>

                                            </span>

                                        </div>

                                    </div>

                                </td>

                                {{-- ACTIONS --}}

                                <td class="text-end">

                                    <div class="torrent-actions">

                                        <form method="POST"

                                              action="{{ route('seedboxes.'.$actionIcon['type'], [$seedbox, $hash]) }}">

                                            @csrf

                                            <button class="action-btn action-btn-dark"

                                                    title="{{ $actionIcon['title'] }}">

                                                <i class="bi {{ $actionIcon['icon'] }} {{ $actionIcon['class'] }}"></i>

                                            </button>

                                        </form>

                                        <form method="POST"

                                              action="{{ route('seedboxes.delete', [$seedbox, $hash]) }}">

                                            @csrf

                                            @method('DELETE')

                                            <button class="action-btn action-btn-danger"

                                                    title="Delete torrent"

                                                    onclick="return confirm('Are you sure you want to delete this torrent? Only the torrent will be removed, not the data.');">

                                                <i class="bi bi-trash-fill"></i>

                                            </button>

                                        </form>

                                        @if($canUpload)

                                            <span class="upload-button"

                                                  data-hash="{{ $hash }}"

                                                  style="display:none;">

                                                <form method="POST" action="{{ route('seedboxes.downloadRebuiltTorrent', [$seedbox, $hash]) }}">
                                                    @csrf
                                                    <button class="action-btn action-btn-info" aria-label="Upload to {{ config('app.name') }}" title="Upload to {{ config('app.name') }}"><i class="bi bi-cloud-arrow-up-fill"></i></button>
                                                </form>

                                            </span>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty
                            <tr><td colspan="3" class="text-center py-5">
                                <i class="bi bi-inbox d-block fs-3 mb-2 text-info"></i>
                                {{ !$isConnected ? 'Unable to load torrents. Check your connection and try again.' : (request('search') ? 'No torrents match your search.' : 'No torrents yet. Add a torrent file to get started.') }}
                            </td></tr>
                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="mt-4">

            <div data-seedbox-pagination>{{ $torrents->links('pagination::bootstrap-5') }}</div>

        </div>

    </div>

</div>

{{-- =========================================

    AUTO REFRESH

========================================= --}}
