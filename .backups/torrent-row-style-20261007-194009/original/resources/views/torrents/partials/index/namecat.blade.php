<a href="{{ route($browseRoute ?? 'torrents.index', [
        'keyword' => '',
        'categories' => [$torrent->category->id],
        'genre' => '',
        'torrent_status' => 'active'
    ]) }}"
   class="category-pill me-2"
   aria-label="{{ $torrent->category->name }}"
   data-bs-toggle="tooltip"
   data-bs-placement="top"
   title="{{ $torrent->category->name }}">

    <span class="category-icon">
        <i class="{{ $categoryIcon ?? $torrent->category->icon }}" aria-hidden="true"></i>
    </span>

    <span class="category-name">
        {{ $torrent->category->name }}
    </span>

</a>

<div class="overflow-hidden w-100">

    @include('torrents.partials.title-status')

    {{-- GENRES + TAGS --}}
<div class="mt-1 d-flex flex-wrap align-items-center gap-1">

    {{-- Genres --}}
    @foreach($torrent->genres as $genre)

        <a href="{{ route($browseRoute ?? 'torrents.index', ['genre' => $genre->id]) }}"
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

@once
<style>

    
.genre-badge {
    display: inline-flex;
    align-items: center;

    padding: 2px 7px;

    border-radius: 6px;

    font-size: var(--site-font-small, 13px);
    font-weight: 600;

    line-height: 1.1;

    color: var(--theme-text, #cfcfcf);

    background: var(--theme-surface-alt, rgba(255,255,255,0.042));

    border: 1px solid var(--theme-border, rgba(255,255,255,.06));

    transition:
        background .15s ease,
        color .15s ease,
        border-color .15s ease;
}

.genre-badge:hover {
    color: var(--theme-text, #fff);

    background: var(--theme-surface-alt, rgba(255,255,255,0.084));

    border-color: var(--theme-border, rgba(255,255,255,.12));
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
        var(--theme-surface-alt, rgba(255,255,255,0.0525)),
        var(--theme-surface-alt, rgba(255,255,255,0.0245))
    );

    border: 1px solid var(--theme-border, rgba(255,255,255,.10));

    color: var(--theme-text, #d8d8d8);
    text-decoration: none;

    font-size: var(--site-font-small, 13px);
    font-weight: 600;

    white-space: nowrap;

    box-shadow:
        inset 0 1px 0 var(--theme-shadow, rgba(255,255,255,.04)),
        0 2px 8px var(--theme-shadow, rgba(0,0,0,.08));

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
    background: var(--theme-surface-alt, rgba(108, 117, 125, 0.18));
    border-color: var(--theme-border, rgba(108, 117, 125, 0.30));

    color: inherit;
    text-decoration: none;

    transform: translateY(-1px);

    box-shadow: 0 3px 10px var(--theme-shadow, rgba(0, 0, 0, 0.08));
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
@endonce
