<!-- <a href="{{ route('torrents.index', [
        'keyword' => '',
        'categories' => [$torrent->category->id],
        'genre' => '',
        'torrent_status' => 'active'
    ]) }}"
   class="me-3 d-inline-block"
   data-bs-toggle="tooltip"
   title="{{ $torrent->category->name }}">

    <img src="{{ asset($torrent->category->image) }}?v={{ filemtime(public_path($torrent->category->image)) }}"
         class="rounded shadow-sm"
         style="width:74px;height:40px"
         alt="{{ $torrent->category->name }}">

</a> -->

<a href="{{ route('torrents.index', [
        'keyword' => '',
        'categories' => [$torrent->category->id],
        'genre' => '',
        'torrent_status' => 'active'
    ]) }}"
   class="category-pill me-2"
   data-bs-toggle="tooltip"
   data-bs-placement="top"
   title="{{ $torrent->category->name }}">

    <span class="category-icon">
        <i class="{{ $torrent->category->icon }}" aria-hidden="true"></i>
    </span>

    <span class="category-name">
        {{ $torrent->category->name }}
    </span>

</a>

<div class="overflow-hidden w-100">

    {{-- TITLE + STATUS ICONS --}}
    <div class="d-flex align-items-center gap-2 overflow-hidden">

        <a href="{{ route('torrents.show', [$torrent->id, urlencode($torrent->slug)]) }}"
           class="text-decoration-none flex-grow-1 overflow-hidden"
           data-bs-toggle="tooltip"
           data-bs-html="true"
           data-bs-title="<img src='{{ $torrent->poster }}' class='img-fluid rounded' style='max-width:180px'>">

            <small class="torrent-title text-truncate d-block fw-bold">
                {{ $torrent->name }}
            </small>

        </a>

        {{-- Downloaded --}}
        @if($torrent->has_downloaded)

            <span
                class="torrent-inline-icon downloaded"
                data-bs-toggle="tooltip"
                title="You already downloaded this torrent"
            >
                <i class="bi bi-check-circle-fill"></i>
            </span>

        @endif

        {{-- Seeding --}}
        @if($torrent->is_seeding)

            <span
                class="torrent-inline-icon seeding"
                data-bs-toggle="tooltip"
                title="You are currently seeding this torrent"
            >
                <i class="bi bi-broadcast-pin"></i>
            </span>

        @endif

    </div>

    {{-- MOBILE INFO --}}
    <div class="d-md-none small text-muted mt-1 fs-6">

        {{ $torrent->created_at->format('M d, Y') }} ·
        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }} ·
        {{ $torrent->times_completed }}
        {{ Str::plural('Time', $torrent->times_completed) }}

    </div>

    {{-- GENRES + TAGS --}}
<div class="mt-1 d-flex flex-wrap align-items-center gap-1">

    {{-- Genres --}}
    @foreach($torrent->genres as $genre)

        <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}"
           class="genre-badge text-decoration-none">

            {{ $genre->name }}

        </a>

    @endforeach

    {{-- Tags --}}
    <div class="d-flex flex-wrap gap-1 align-items-center">

        @include('torrents.partials.tags')

    </div>

</div>

</div>

<style>

    
.genre-badge {
    display: inline-flex;
    align-items: center;

    padding: 2px 7px;

    border-radius: 6px;

    font-size: 11px;
    font-weight: 600;

    line-height: 1.1;

    color: #cfcfcf;

    background: rgba(255,255,255,.06);

    border: 1px solid rgba(255,255,255,.06);

    transition:
        background .15s ease,
        color .15s ease,
        border-color .15s ease;
}

.genre-badge:hover {
    color: #fff;

    background: rgba(255,255,255,.12);

    border-color: rgba(255,255,255,.12);
}
.torrent-inline-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 19px;
    height: 19px;

    border-radius: 50%;

    font-size: 11px;

    flex-shrink: 0;

    cursor: help;

    transition:
        transform .15s ease,
        box-shadow .2s ease;

    backdrop-filter: blur(4px);
}

.torrent-inline-icon:hover {
    transform: scale(1.12);
}

/* Downloaded */
.torrent-inline-icon.downloaded {
    color: #72ffb0;
    background: rgba(40,167,69,.10);
    border: 1px solid rgba(109,255,156,.14);
}

/* Seeding */
.torrent-inline-icon.seeding {
    color: #9ab0ff;
    background: rgba(80,120,255,.10);
    border: 1px solid rgba(154,176,255,.14);

    box-shadow: 0 0 10px rgba(154,176,255,.08);
}


.category-pill {
    width: 150px;
    height: 34px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 0 10px;

    border-radius: 10px;

    background: linear-gradient(
        135deg,
        rgba(255,255,255,.075),
        rgba(255,255,255,.035)
    );

    border: 1px solid rgba(255,255,255,.10);

    color: #d8d8d8;
    text-decoration: none;

    font-size: 11px;
    font-weight: 600;

    white-space: nowrap;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.04),
        0 2px 8px rgba(0,0,0,.08);

    transition:
        transform .2s ease,
        background .2s ease,
        border-color .2s ease,
        box-shadow .2s ease,
        color .2s ease;
}

.category-pill i {
    font-size: 17px;
    line-height: 1;
}

.category-pill:hover {
    background: rgba(108, 117, 125, 0.18);
    border-color: rgba(108, 117, 125, 0.30);

    color: inherit;
    text-decoration: none;

    transform: translateY(-1px);

    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.category-pill:active {
    transform: translateY(0);
}

@media (max-width: 767.98px) {

    .category-pill {
        width: 32px;
        height: 32px;

        padding: 0;

        margin-right: 6px !important;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 8px;

        gap: 0;
    }

    .category-pill .category-name {
        display: none;
    }

    .category-pill .category-icon {
        width: 100%;
        height: 100%;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 7px;

        background: transparent;

        font-size: 15px;
    }

}


</style>