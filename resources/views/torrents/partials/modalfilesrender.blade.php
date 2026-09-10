@php

function renderTree($tree, $level = 0) {

    echo '<ul class="' . ($level === 0 ? 'file-tree' : 'file-tree nested-tree') . '">';

    foreach ($tree as $name => $subtree) {

        if ($name === '_size') {

            continue;

        }

        /* =====================================================

           📁 FOLDER

           ===================================================== */

        if (is_array($subtree) && count($subtree) > 0 && !isset($subtree['_size'])) {

            $id = uniqid('folder_');

            echo '

            <li class="file-tree-item">

                <div class="folder-row toggle-folder"

                     data-target="' . $id . '">

                    <span class="folder-chevron">

                        <i class="bi bi-chevron-right"></i>

                    </span>

                    <span class="folder-icon">

                        <i class="bi bi-folder-fill"></i>

                    </span>

                    <span class="folder-name">

                        ' . e($name) . '

                    </span>

                </div>

                <div id="' . $id . '" class="folder-children d-none">

            ';

            renderTree($subtree, $level + 1);

            echo '

                </div>

            </li>

            ';

        }

        /* =====================================================

           📄 FILE

           ===================================================== */

        else {

            $size      = $subtree['_size'] ?? '';

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $filename  = strtolower($name);

            /* =================================================

               FILE ICONS

               ================================================= */

            $icons = [

                // Video

                'mp4'  => 'bi bi-file-earmark-play-fill',

                'mkv'  => 'bi bi-file-earmark-play-fill',

                'avi'  => 'bi bi-file-earmark-play-fill',

                'mov'  => 'bi bi-file-earmark-play-fill',

                'wmv'  => 'bi bi-file-earmark-play-fill',

                'flv'  => 'bi bi-file-earmark-play-fill',

                'webm' => 'bi bi-file-earmark-play-fill',

                // Audio

                'mp3'  => 'bi bi-file-earmark-music-fill',

                'flac' => 'bi bi-file-earmark-music-fill',

                'wav'  => 'bi bi-file-earmark-music-fill',

                'aac'  => 'bi bi-file-earmark-music-fill',

                'ogg'  => 'bi bi-file-earmark-music-fill',

                // Subtitles

                'srt' => 'bi bi-file-earmark-text-fill',

                'sub' => 'bi bi-file-earmark-text-fill',

                'ass' => 'bi bi-file-earmark-text-fill',

                // Images

                'jpg'  => 'bi bi-file-earmark-image-fill',

                'jpeg' => 'bi bi-file-earmark-image-fill',

                'png'  => 'bi bi-file-earmark-image-fill',

                'gif'  => 'bi bi-file-earmark-image-fill',

                'webp' => 'bi bi-file-earmark-image-fill',

                // Archives

                'zip' => 'bi bi-file-earmark-zip-fill',

                'rar' => 'bi bi-file-earmark-zip-fill',

                '7z'  => 'bi bi-file-earmark-zip-fill',

                'tar' => 'bi bi-file-earmark-zip-fill',

                'gz'  => 'bi bi-file-earmark-zip-fill',

                // Disc images

                'iso' => 'bi bi-disc-fill',

                'bin' => 'bi bi-disc-fill',

                'img' => 'bi bi-disc-fill',

                // Documents

                'pdf'  => 'bi bi-file-earmark-pdf-fill',

                'txt'  => 'bi bi-file-earmark-text-fill',

                'nfo'  => 'bi bi-file-earmark-text-fill',

                'doc'  => 'bi bi-file-earmark-word-fill',

                'docx' => 'bi bi-file-earmark-word-fill',

                // Executables

                'exe' => 'bi bi-window-desktop',

                'msi' => 'bi bi-window-desktop',

                'apk' => 'bi bi-android2',

            ];

            $icon = $icons[$extension] ?? 'bi bi-file-earmark-fill';



            /* =================================================

               VIDEO DETECTION

               ================================================= */

            $videoExtensions = [

                'mp4',

                'mkv',

                'avi',

                'mov',

                'wmv',

                'flv',

                'webm'

            ];

            $isVideo = in_array($extension, $videoExtensions);



            /* =================================================

               RESOLUTION

               ================================================= */

            $resolution = null;

            if ($isVideo) {

                if (

                    str_contains($filename, '2160p') ||

                    str_contains($filename, '4k') ||

                    str_contains($filename, 'uhd')

                ) {

                    $resolution = '4K';

                } elseif (str_contains($filename, '1080p')) {

                    $resolution = '1080p';

                } elseif (str_contains($filename, '720p')) {

                    $resolution = '720p';

                } elseif (str_contains($filename, '480p')) {

                    $resolution = '480p';

                }

            }



            /* =================================================

               CODEC

               ================================================= */

            $codec = null;

            if ($isVideo) {

                if (

                    str_contains($filename, 'x265') ||

                    str_contains($filename, 'h265') ||

                    str_contains($filename, 'hevc')

                ) {

                    $codec = 'x265';

                } elseif (

                    str_contains($filename, 'x264') ||

                    str_contains($filename, 'h264') ||

                    str_contains($filename, 'avc')

                ) {

                    $codec = 'x264';

                }

            }



            /* =================================================

               HDR

               ================================================= */

            $hdr = null;

            if ($isVideo) {

                if (

                    str_contains($filename, 'dolby.vision') ||

                    str_contains($filename, 'dolbyvision') ||

                    str_contains($filename, ' dv ')

                ) {

                    if (str_contains($filename, 'fel')) {

                        $hdr = 'DV FEL';

                    } elseif (str_contains($filename, 'mel')) {

                        $hdr = 'DV MEL';

                    } elseif (str_contains($filename, 'hdr')) {

                        $hdr = 'DV+HDR';

                    } else {

                        $hdr = 'DV';

                    }

                } elseif (str_contains($filename, 'hdr10+')) {

                    $hdr = 'HDR10';

                } elseif (str_contains($filename, 'hdr10')) {

                    $hdr = 'HDR10';

                } elseif (str_contains($filename, 'hlg')) {

                    $hdr = 'HLG';

                } elseif (str_contains($filename, ' hdr ')) {

                    $hdr = 'HDR';

                }

            }



            /* =================================================

               AUDIO

               ================================================= */

            $audio = null;

            $audioChannels = null;

            if ($isVideo) {

                if (str_contains($filename, 'truehd')) {

                    $audio = 'TrueHD';

                } elseif (str_contains($filename, 'atmos')) {

                    $audio = 'Atmos';

                } elseif (

                    str_contains($filename, 'dts-hd') ||

                    str_contains($filename, 'dtshd')

                ) {

                    $audio = 'DTS-HD';

                } elseif (str_contains($filename, 'dts')) {

                    $audio = 'DTS';

                } elseif (

                    str_contains($filename, 'eac3') ||

                    str_contains($filename, 'ddp')

                ) {

                    $audio = 'DD+';

                } elseif (str_contains($filename, 'ac3')) {

                    $audio = 'AC3';

                } elseif (str_contains($filename, 'aac')) {

                    $audio = 'AAC';

                } elseif (str_contains($filename, 'flac')) {

                    $audio = 'FLAC';

                } elseif (str_contains($filename, 'mp3')) {

                    $audio = 'MP3';

                }



                if (str_contains($filename, '7.1')) {

                    $audioChannels = '7.1';

                } elseif (str_contains($filename, '5.1')) {

                    $audioChannels = '5.1';

                } elseif (str_contains($filename, '2.0')) {

                    $audioChannels = '2.0';

                }

            }



            /* =================================================

               FILE ROW

               ================================================= */

            echo '

            <li class="file-tree-item">

                <div class="file-row">

                    <div class="file-main">

                        <span class="file-type-icon">

                            <i class="' . $icon . '"></i>

                        </span>

                        <span class="file-name"

                              title="' . e($name) . '">

                            ' . e($name) . '

                        </span>

                        <div class="file-badges">

            ';

            if ($resolution) {

                echo '<span class="file-badge badge-res">' . $resolution . '</span>';

            }

            if ($codec) {

                echo '<span class="file-badge badge-codec">' . $codec . '</span>';

            }

            if ($hdr) {

                echo '<span class="file-badge badge-hdr">' . $hdr . '</span>';

            }

            if ($audio) {

                echo '<span class="file-badge badge-audio">' . $audio . '</span>';

            }

            if ($audioChannels) {

                echo '<span class="file-badge badge-audio-ch">' . $audioChannels . '</span>';

            }

            echo '

                        </div>

                    </div>



                    <div class="file-size">

                        ' . e($size) . '

                    </div>



                    <div class="file-type">

                        ' . strtoupper($extension) . '

                    </div>



                    <div class="file-kind">

                        <i class="' . ($isVideo ? 'bi bi-camera-video-fill' : 'bi bi-file-earmark-fill') . '"></i>

                        <span>' . ($isVideo ? 'Video' : 'File') . '</span>

                    </div>

                </div>

            </li>

            ';

        }

    }

    echo '</ul>';

}

@endphp



@if(!empty($fileTree))

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

                @php

                    renderTree($fileTree);

                @endphp

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
        rgba(22, 32, 51, .98),
        rgba(15, 23, 42, .96)
    );
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    box-shadow: 0 20px 50px rgba(0,0,0,.45);
    color: #e5e7eb;
}

.torrent-files-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--ui-border);
    background: rgba(255,255,255,.02);
}

.torrent-files-header .modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff;
    font-size: 14px;
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
    background: rgba(45,212,191,.10);
    border: 1px solid rgba(45,212,191,.20);
    font-size: 16px;
}

.files-subtitle {
    display: block;
    margin-top: 3px;
    margin-left: 44px;
    color: rgba(255,255,255,.58);
    font-size: 12px;
}

.files-column-header {
    grid-template-columns: minmax(0, 1fr) 100px 90px 90px;
    gap: 10px;
    padding: 9px 14px;
    color: rgba(255,255,255,.48);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .5px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

.torrent-files-body {
    padding: 10px 12px;
    background: rgba(0,0,0,.08);
}

.file-tree {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nested-tree {
    margin-left: 20px;
    padding-left: 10px;
    border-left: 1px solid rgba(45,212,191,.14);
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
    color: #dbe4e8;
    transition: background .15s ease, color .15s ease;
}

.folder-row:hover {
    background: rgba(45,212,191,.07);
    color: #fff;
}

.folder-chevron {
    width: 16px;
    display: inline-flex;
    justify-content: center;
    color: rgba(255,255,255,.42);
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
    background: rgba(45,212,191,.10);
    font-size: 13px;
}

.folder-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 14px;
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
    background: rgba(45,212,191,.045);
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
    background: rgba(255,255,255,.045);
    font-size: 13px;
}

.file-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #d8e0e4;
    font-size: 14px;
    font-weight: 500;
}

.file-row:hover .file-name {
    color: #fff;
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
    font-size: 10px;
    line-height: 1;
    font-weight: 700;
    letter-spacing: .15px;
}

.badge-res {
    color: var(--ui-accent);
    background: rgba(45,212,191,.10);
    border: 1px solid rgba(45,212,191,.18);
}

.badge-codec {
    color: #86efac;
    background: rgba(34,197,94,.09);
    border: 1px solid rgba(34,197,94,.15);
}

.badge-hdr {
    color: #fbbf24;
    background: rgba(245,158,11,.09);
    border: 1px solid rgba(245,158,11,.15);
}

.badge-audio {
    color: #fdba74;
    background: rgba(249,115,22,.09);
    border: 1px solid rgba(249,115,22,.15);
}

.badge-audio-ch {
    color: #67e8f9;
    background: rgba(34,211,238,.08);
    border: 1px solid rgba(34,211,238,.14);
}

.file-size,
.file-type,
.file-kind {
    color: rgba(255,255,255,.55);
    font-size: 12px;
    text-align: center;
}

.file-size {
    color: #9bd8d1;
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
    background: rgba(255,255,255,.02);
}

.files-close-btn {
    display: inline-flex;
    align-items: center;
    padding: 7px 13px;
    border-radius: .55rem;
    color: rgba(255,255,255,.72);
    background: rgba(255,255,255,.045);
    border: 1px solid var(--ui-border);
    font-size: 13px;
    transition: background .15s ease, color .15s ease, border-color .15s ease;
}

.files-close-btn:hover {
    color: #fff;
    background: rgba(45,212,191,.08);
    border-color: rgba(45,212,191,.28);
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
        font-size: 14px;
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
        font-size: 13px;
    }

    .file-badges,
    .file-size,
    .file-type {
        display: none;
    }

    .file-kind {
        width: 45px;
        flex-shrink: 0;
        font-size: 9px;
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
        font-size: 11px;
    }
}
</style>
