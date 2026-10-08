@php

if (! function_exists('renderTorrentFileTree')) {
function renderTorrentFileTree($tree, $level = 0) {

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

            renderTorrentFileTree($subtree, $level + 1);

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

}
@endphp
@php(renderTorrentFileTree($fileTree))
