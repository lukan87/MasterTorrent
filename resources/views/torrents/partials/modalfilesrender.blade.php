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
   TORRENT FILE MODAL
   ========================================================= */

.torrent-files-modal {
    overflow: hidden;

    background:
        linear-gradient(
            145deg,
            rgba(28,30,35,.98),
            rgba(18,20,24,.98)
        );

    border: 1px solid rgba(255,255,255,.09);

    border-radius: 16px;

    box-shadow:
        0 25px 70px rgba(0,0,0,.55);
}


/* =========================================================
   HEADER
   ========================================================= */

.torrent-files-header {
    padding: 16px 20px;

    border-bottom: 1px solid rgba(255,255,255,.07);
}


.torrent-files-header .modal-title {
    display: flex;

    align-items: center;

    gap: 10px;

    color: #f1f1f1;

    font-size: 16px;

    font-weight: 700;
}


.files-title-icon {
    width: 34px;
    height: 34px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    color: #9ab0ff;

    background: rgba(100,130,255,.10);

    border: 1px solid rgba(140,165,255,.14);

    font-size: 16px;
}


.files-subtitle {
    display: block;

    margin-top: 3px;
    margin-left: 44px;

    color: #6f737b;

    font-size: 10px;
}


/* =========================================================
   COLUMN HEADER
   ========================================================= */

.files-column-header {
    grid-template-columns: minmax(0, 1fr) 100px 90px 90px;

    gap: 10px;

    padding: 8px 14px;

    color: #666b73;

    font-size: 9px;

    font-weight: 700;

    letter-spacing: .6px;

    border-bottom: 1px solid rgba(255,255,255,.045);
}


/* =========================================================
   BODY
   ========================================================= */

.torrent-files-body {
    padding: 10px 12px;

    background:
        radial-gradient(
            circle at top,
            rgba(100,120,255,.025),
            transparent 45%
        );
}


/* =========================================================
   TREE
   ========================================================= */

.file-tree {
    list-style: none;

    margin: 0;

    padding: 0;
}


.nested-tree {
    margin-left: 20px;

    padding-left: 10px;

    border-left: 1px solid rgba(255,255,255,.06);
}


.file-tree-item {
    list-style: none;

    margin: 1px 0;
}


/* =========================================================
   FOLDER
   ========================================================= */

.folder-row {
    display: flex;

    align-items: center;

    gap: 8px;

    min-height: 34px;

    padding: 5px 8px;

    border-radius: 8px;

    cursor: pointer;

    color: #d2d5da;

    transition:
        background .15s ease,
        color .15s ease;
}


.folder-row:hover {
    background: rgba(255,255,255,.055);

    color: #fff;
}


.folder-chevron {
    width: 16px;

    display: inline-flex;

    justify-content: center;

    color: #666d78;

    font-size: 9px;

    transition:
        transform .18s ease,
        color .18s ease;
}


.folder-open .folder-chevron {
    transform: rotate(90deg);

    color: #9ab0ff;
}


.folder-icon {
    width: 25px;
    height: 25px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 6px;

    color: #e3b85c;

    background: rgba(227,184,92,.08);

    font-size: 13px;
}


.folder-name {
    min-width: 0;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    font-size: 15px;

    font-weight: 600;
}


/* =========================================================
   FILE ROW
   ========================================================= */

.file-row {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        100px
        90px
        90px;

    align-items: center;

    gap: 10px;

    min-height: 39px;

    padding: 5px 8px;

    border-radius: 8px;

    transition:
        background .15s ease;
}


.file-row:hover {
    background: rgba(255,255,255,.035);
}


/* =========================================================
   FILE MAIN
   ========================================================= */

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

    border-radius: 7px;

    color: #8e96a5;

    background: rgba(255,255,255,.045);

    font-size: 13px;
}


.file-name {
    min-width: 0;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #cfd2d7;

    font-size: 13px;

    font-weight: 500;
}


.file-row:hover .file-name {
    color: #fff;
}


/* =========================================================
   BADGES
   ========================================================= */

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

    min-height: 17px;

    padding: 2px 5px;

    border-radius: 5px;

    font-size: 10px;

    line-height: 1;

    font-weight: 700;

    letter-spacing: .15px;
}


.badge-res {
    color: #9ab0ff;

    background: rgba(100,130,255,.10);

    border: 1px solid rgba(130,155,255,.12);
}


.badge-codec {
    color: #8de0b0;

    background: rgba(70,190,120,.08);

    border: 1px solid rgba(100,210,145,.11);
}


.badge-hdr {
    color: #d9a7ff;

    background: rgba(170,100,230,.09);

    border: 1px solid rgba(190,125,245,.12);
}


.badge-audio {
    color: #ffb875;

    background: rgba(230,140,60,.08);

    border: 1px solid rgba(240,160,90,.11);
}


.badge-audio-ch {
    color: #82d8e8;

    background: rgba(50,180,200,.08);

    border: 1px solid rgba(80,200,220,.11);
}


/* =========================================================
   SIZE / TYPE / KIND
   ========================================================= */

.file-size,
.file-type,
.file-kind {
    color: #858a93;

    font-size: 10px;

    text-align: center;
}


.file-size {
    color: #83b7d8;

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
    font-size: 10px;

    opacity: .65;
}


/* =========================================================
   FOOTER
   ========================================================= */

.torrent-files-footer {
    padding: 10px 16px;

    border-top: 1px solid rgba(255,255,255,.06);
}


.files-close-btn {
    display: inline-flex;

    align-items: center;

    padding: 6px 12px;

    border-radius: 8px;

    color: #aaa;

    background: rgba(255,255,255,.045);

    border: 1px solid rgba(255,255,255,.08);

    font-size: 10px;

    transition:
        background .15s ease,
        color .15s ease;
}


.files-close-btn:hover {
    color: #fff;

    background: rgba(255,255,255,.09);
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767.98px) {

    .torrent-files-modal {
        border-radius: 12px;
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
        font-size: 13px;
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
        font-size: 12px;
    }


    .file-badges {
        display: none;
    }


    .file-size {
        display: none;
    }


    .file-type {
        display: none;
    }


    .file-kind {
        width: 45px;

        flex-shrink: 0;

        font-size: 8px;
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

}

    </style>