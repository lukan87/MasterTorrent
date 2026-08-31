@php
function renderTree($tree, $level = 0) {

    echo '<ul class="' . ($level === 0 ? 'list-group' : 'ps-3 mt-1') . '">';

    foreach ($tree as $name => $subtree) {
        if ($name === '_size') continue;

        echo '<li class="list-group-item bg-transparent text-light border-0 px-2 py-1">';

        /* =========================
           📁 FOLDER
           ========================= */
        if (is_array($subtree) && count($subtree) > 0 && !isset($subtree['_size'])) {

            $id = uniqid('folder_');

            echo '
                <div class="toggle-folder d-flex align-items-center gap-2 fw-semibold"
                     data-target="' . $id . '"
                     style="cursor:pointer;">
                    <span class="folder-icon">📂</span>
                    <span class="folder-name">' . e($name) . '</span>
                </div>
            ';

            echo '<div id="' . $id . '" class="d-none">';
            renderTree($subtree, $level + 1);
            echo '</div>';

        }
        /* =========================
           📄 FILE
           ========================= */
        else {

            $size      = $subtree['_size'] ?? '';
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $filename  = strtolower($name);

            /* ---------- ICONS ---------- */
            $icons = [
                // Video
                'mp4'=>'🎬','mkv'=>'🎬','avi'=>'🎬','mov'=>'🎬','wmv'=>'🎬','flv'=>'🎬','webm'=>'🎬',

                // Audio
                'mp3'=>'🎵','flac'=>'🎵','wav'=>'🎵','aac'=>'🎵','ogg'=>'🎵',

                // Subtitles
                'srt'=>'📜','sub'=>'📜','ass'=>'📜',

                // Images
                'jpg'=>'🖼️','jpeg'=>'🖼️','png'=>'🖼️','gif'=>'🖼️','webp'=>'🖼️',

                // Archives
                'zip'=>'📦','rar'=>'📦','7z'=>'📦','tar'=>'📦','gz'=>'📦',

                // Disc images
                'iso'=>'💿','bin'=>'💿','img'=>'💿',

                // Documents
                'pdf'=>'📕','txt'=>'📄','nfo'=>'📄','doc'=>'📄','docx'=>'📄',

                // Executables
                'exe'=>'🖥️','msi'=>'🖥️','apk'=>'📱',
            ];

            $icon = $icons[$extension] ?? '📄';

            /* ---------- VIDEO DETECTION ---------- */
            $videoExtensions = ['mp4','mkv','avi','mov','wmv','flv','webm'];
            $isVideo = in_array($extension, $videoExtensions);

            /* ---------- RESOLUTION ---------- */
            $resolution = null;
            if ($isVideo) {
                if (str_contains($filename, '2160p') || str_contains($filename, '4k') || str_contains($filename, 'uhd')) {
                    $resolution = '4K';
                } elseif (str_contains($filename, '1080p')) {
                    $resolution = '1080p';
                } elseif (str_contains($filename, '720p')) {
                    $resolution = '720p';
                } elseif (str_contains($filename, '480p')) {
                    $resolution = '480p';
                }
            }

            /* ---------- CODEC ---------- */
            $codec = null;
            if ($isVideo) {
                if (str_contains($filename, 'x265') || str_contains($filename, 'h265') || str_contains($filename, 'hevc')) {
                    $codec = 'x265';
                } elseif (str_contains($filename, 'x264') || str_contains($filename, 'h264') || str_contains($filename, 'avc')) {
                    $codec = 'x264';
                }
            }
            
/* =====================================================
   🎥 HDR / VIDEO FORMAT
   ===================================================== */

$hdr = null;

if ($isVideo) {

    // Dolby Vision (profiles)
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

    // HDR10+
    } elseif (str_contains($filename, 'hdr10+')) {
        $hdr = 'HDR10+';

    // HDR10
    } elseif (str_contains($filename, 'hdr10')) {
        $hdr = 'HDR10';

    // Hybrid Log-Gamma
    } elseif (str_contains($filename, 'hlg')) {
        $hdr = 'HLG';

    // Generic HDR
    } elseif (str_contains($filename, ' hdr ')) {
        $hdr = 'HDR';
    }
}


/* =====================================================
   🔊 AUDIO CODEC + CHANNELS
   ===================================================== */

$audio = null;
$audioChannels = null;

if ($isVideo) {

    /* ---------- CODEC ---------- */
    if (str_contains($filename, 'truehd')) {
        $audio = 'TrueHD';
    } elseif (str_contains($filename, 'atmos')) {
        $audio = 'Atmos';
    } elseif (str_contains($filename, 'dts-hd') || str_contains($filename, 'dtshd')) {
        $audio = 'DTS-HD';
    } elseif (str_contains($filename, 'dts')) {
        $audio = 'DTS';
    } elseif (str_contains($filename, 'eac3') || str_contains($filename, 'ddp')) {
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

    /* ---------- CHANNELS ---------- */
    if (str_contains($filename, '7.1')) {
        $audioChannels = '7.1';
    } elseif (str_contains($filename, '5.1')) {
        $audioChannels = '5.1';
    } elseif (str_contains($filename, '2.0')) {
        $audioChannels = '2.0';
    }
}



/* ---------- RENDER FILE  ---------- */
echo '
<div class="row g-0 align-items-center border-bottom py-2 file-row">

    <!-- FILE INFO -->
    <div class="col-12 col-md-6 d-flex align-items-center gap-2 px-2 fs-6">

        <span class="file-icon">' . $icon . '</span>

        <span class="file-name fw-semibold">' . e($name) . '</span>

        <div class="d-flex gap-1 ms-2 fs-6">
';

if ($resolution) {
    echo '<span class="badge badge-res">' . $resolution . '</span>';
}

if ($codec) {
    echo '<span class="badge badge-codec">' . $codec . '</span>';
}

if ($hdr) {
    echo '<span class="badge badge-hdr">' . $hdr . '</span>';
}

if ($audio) {
    echo '<span class="badge badge-audio">' . $audio . '</span>';
}

if ($audioChannels) {
    echo '<span class="badge badge-audio-ch">' . $audioChannels . '</span>';
}


echo '
        </div>
    </div>

    <!-- SIZE -->
    <div class="d-none d-md-block col-md-2 text-center fs-6 text-info fw-bold">
        ' . e($size) . '
    </div>

    <!-- TYPE -->
    <div class="d-none d-md-block col-md-2 text-center fs-6 fw-bold text-muted">
        ' . strtoupper($extension) . '
    </div>

    <!-- ICON -->
    <div class="col-12 col-md-2 text-center fs-6 text-muted">
        ' . ($isVideo ? 'Video' : 'File') . '
    </div>

</div>
';
        }

        echo '</li>';
    }

    echo '</ul>';
}
@endphp

@push('scripts')
<script>
document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.toggle-folder');
    if (!toggle) return;

    const target = document.getElementById(toggle.dataset.target);
    if (!target) return;

    target.classList.toggle('d-none');

    toggle.textContent = toggle.textContent.includes('📂')
        ? toggle.textContent.replace('📂', '📁')
        : toggle.textContent.replace('📁', '📂');
});
</script>
@endpush




@if(!empty($fileTree))
<div class="modal fade" id="torrentFilesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-blur">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-folder2-open me-2"></i>
                    Torrent Files
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <ul class="list-group">
                    @php renderTree($fileTree); @endphp
                </ul>
            </div>

            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
@endif