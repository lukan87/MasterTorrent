



@if(!empty($fileTree) || ($torrent->files_count ?? 0) > 0)

<div class="modal fade" id="torrentFilesModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content torrent-files-modal">



            {{-- HEADER --}}

            <div class="modal-header torrent-files-header">

                <div>

                    <h5 class="modal-title">

                        <span class="files-title-icon">

                            <i class="bi bi-folder2-open"></i>

                        </span>

                        Torrent Files

                    </h5>

                    <small class="files-subtitle">

                        Browse files and folders

                    </small>

                </div>

                <button

                    type="button"

                    class="btn-close"

                    data-bs-dismiss="modal">

                </button>

            </div>



            {{-- COLUMN HEADERS --}}

            <div class="files-column-header d-none d-md-grid">

                <div>FILE</div>

                <div>SIZE</div>

                <div>TYPE</div>

                <div>CONTENT</div>

            </div>



            {{-- FILE TREE --}}

            <div class="modal-body torrent-files-body">

                @if(!empty($fileTree))
                    @include('torrents.partials.file-tree')
                @else
                    <div data-torrent-files data-url="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}">
                        <p class="text-muted mb-0" role="status">Open this panel to load the file list.</p>
                    </div>
                @endif

            </div>



            {{-- FOOTER --}}

            <div class="modal-footer torrent-files-footer">

                <button

                    class="files-close-btn"

                    data-bs-dismiss="modal">

                    <i class="bi bi-x-lg me-1"></i>

                    Close

                </button>

            </div>

        </div>

    </div>

</div>

@endif



@push('scripts')

<script>

document.addEventListener('click', function (e) {

    const toggle = e.target.closest('.toggle-folder');

    if (!toggle) {

        return;

    }

    const target = document.getElementById(toggle.dataset.target);

    if (!target) {

        return;

    }

    const isOpen = !target.classList.contains('d-none');

    target.classList.toggle('d-none');

    toggle.classList.toggle('folder-open', !isOpen);

});

</script>

@endpush

<style>
/* =========================================================
   FILEIPLAY TORRENT FILES MODAL
   Forum-style dark glass / teal accent
   ========================================================= */

.torrent-files-modal {
    overflow: hidden;
    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.98)),
        var(--theme-surface, rgba(10,15,27,.96))
    );
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    box-shadow: 0 20px 50px var(--theme-shadow, rgba(0,0,0,.45));
    color: var(--theme-text, #e5e7eb);
}

.torrent-files-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--ui-border);
    background: var(--theme-surface-alt, rgba(255,255,255,0.014));
}

.torrent-files-header .modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--theme-text, #fff);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.files-title-icon {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .65rem;
    color: var(--ui-accent);
    background: var(--theme-teal-soft, rgba(45,212,191,.10));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.20));
    font-size: 16px;
}

.files-subtitle {
    display: block;
    margin-top: 3px;
    margin-left: 44px;
    color: var(--theme-muted, rgba(255,255,255,.58));
    font-size: var(--site-font-small, 13px);
}

.files-column-header {
    grid-template-columns: minmax(0, 1fr) 100px 90px 90px;
    gap: 10px;
    padding: 9px 14px;
    color: var(--theme-muted, rgba(255,255,255,.48));
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
    letter-spacing: .5px;
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.06));
}

.torrent-files-body {
    padding: 10px 12px;
    background: var(--theme-surface-alt, rgba(0,0,0,.08));
}

.file-tree {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nested-tree {
    margin-left: 20px;
    padding-left: 10px;
    border-left: 1px solid var(--theme-teal-border, rgba(45,212,191,.14));
}

.file-tree-item {
    list-style: none;
    margin: 1px 0;
}

.folder-row {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 34px;
    padding: 5px 8px;
    border-radius: .55rem;
    cursor: pointer;
    color: var(--theme-text, #dbe4e8);
    transition: background .15s ease, color .15s ease;
}

.folder-row:hover {
    background: var(--theme-teal-soft, rgba(45,212,191,.07));
    color:  var(--theme-text, #fff);
}

.folder-chevron {
    width: 16px;
    display: inline-flex;
    justify-content: center;
    color: var(--theme-muted, rgba(255,255,255,.42));
    font-size: 9px;
    transition: transform .18s ease, color .18s ease;
}

.folder-open .folder-chevron {
    transform: rotate(90deg);
    color: var(--ui-accent);
}

.folder-icon {
    width: 25px;
    height: 25px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .5rem;
    color: var(--ui-accent);
    background: var(--theme-teal-soft, rgba(45,212,191,.10));
    font-size: 13px;
}

.folder-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
}

.file-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 100px 90px 90px;
    align-items: center;
    gap: 10px;
    min-height: 39px;
    padding: 5px 8px;
    border-radius: .55rem;
    transition: background .15s ease;
}

.file-row:hover {
    background: var(--theme-teal-soft, rgba(45,212,191,.045));
}

.file-main {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-type-icon {
    width: 27px;
    height: 27px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .5rem;
    color: var(--ui-accent);
    background: var(--theme-surface-alt, rgba(255,255,255,0.0315));
    font-size: 13px;
}

.file-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--theme-text, #d8e0e4);
    font-size: var(--site-font-body, 13px);
    font-weight: 500;
}

.file-row:hover .file-name {
    color: var(--theme-text, #fff);
}

.file-badges {
    display: flex;
    align-items: center;
    gap: 3px;
    flex-shrink: 0;
}

.file-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 18px;
    padding: 2px 6px;
    border-radius: .35rem;
    font-size: var(--site-font-small, 13px);
    line-height: 1;
    font-weight: 700;
    letter-spacing: .15px;
}

.badge-res {
    color: var(--ui-accent);
    background: var(--theme-teal-soft, rgba(45,212,191,.10));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.18));
}

.badge-codec {
    color: var(--theme-green-text, #86efac);
    background: var(--theme-green-soft, rgba(34,197,94,.09));
    border: 1px solid var(--theme-green-border, rgba(34,197,94,.15));
}

.badge-hdr {
    color: var(--theme-amber-text, #fbbf24);
    background: var(--theme-amber-soft, rgba(245,158,11,.09));
    border: 1px solid var(--theme-amber-border, rgba(245,158,11,.15));
}

.badge-audio {
    color: var(--theme-amber-text, #fdba74);
    background: var(--theme-amber-soft, rgba(249,115,22,.09));
    border: 1px solid var(--theme-amber-border, rgba(249,115,22,.15));
}

.badge-audio-ch {
    color: var(--theme-teal-text, #67e8f9);
    background: var(--theme-teal-soft, rgba(34,211,238,.08));
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.14));
}

.file-size,
.file-type,
.file-kind {
    color: var(--theme-muted, rgba(255,255,255,.55));
    font-size: var(--site-font-small, 13px);
    text-align: center;
}

.file-size {
    color: var(--theme-text, #9bd8d1);
    font-weight: 600;
}

.file-type {
    font-weight: 600;
    letter-spacing: .3px;
}

.file-kind {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.file-kind i {
    color: var(--ui-accent);
    font-size: 11px;
    opacity: .8;
}

.torrent-files-footer {
    padding: 10px 16px;
    border-top: 1px solid var(--ui-border);
    background: var(--theme-surface-alt, rgba(255,255,255,0.014));
}

.files-close-btn {
    display: inline-flex;
    align-items: center;
    padding: 7px 13px;
    border-radius: .55rem;
    color: var(--theme-muted, rgba(255,255,255,.72));
    background: var(--theme-surface-alt, rgba(255,255,255,0.0315));
    border: 1px solid var(--ui-border);
    font-size: var(--site-font-body, 13px);
    transition: background .15s ease, color .15s ease, border-color .15s ease;
}

.files-close-btn:hover {
    color:  var(--theme-text, #fff);
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    border-color: var(--theme-teal-border, rgba(45,212,191,.28));
}

.torrent-files-modal .btn-close {
    filter: invert(1) grayscale(1);
    opacity: .65;
}

.torrent-files-modal .btn-close:hover {
    opacity: 1;
}

@media (max-width: 767.98px) {
    .torrent-files-modal {
        border-radius: .7rem;
    }

    .torrent-files-body {
        padding: 7px;
    }

    .nested-tree {
        margin-left: 12px;
        padding-left: 7px;
    }

    .folder-row {
        min-height: 32px;
        padding: 4px 6px;
    }

    .folder-name {
        font-size: var(--site-font-body, 13px);
    }

    .file-row {
        display: flex;
        min-height: 37px;
        padding: 4px 6px;
    }

    .file-main {
        flex: 1;
        min-width: 0;
    }

    .file-name {
        font-size: var(--site-font-body, 13px);
    }

    .file-badges,
    .file-size,
    .file-type {
        display: none;
    }

    .file-kind {
        width: 45px;
        flex-shrink: 0;
        font-size: var(--site-font-small, 13px);
    }

    .file-kind span {
        display: none;
    }

    .file-type-icon {
        width: 25px;
        height: 25px;
        font-size: 12px;
    }

    .torrent-files-header {
        padding: 13px 14px;
    }

    .files-subtitle {
        font-size: var(--site-font-small, 13px);
    }
}
</style>
