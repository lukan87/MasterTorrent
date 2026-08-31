<?php

namespace App\Helpers;

class MediaInfoParser
{
    public static function parse(string $raw): array
    {
        $d = [
            'width' => null,
            'height' => null,
            'resolution' => null,
            'video' => null,
            'profile' => null,
            'bitrate' => null,
            'fps' => null,
            'hdr' => false,
            'dolby_vision' => false,
            'audio' => [],
            'text' => [],
            'chapters' => [],
        ];

        $raw = str_replace("\r", '', $raw);

        /* ---------------- VIDEO ---------------- */

        if (preg_match('/Width\s+:\s+(\d+)/i', $raw, $m)) {
            $d['width'] = (int) $m[1];
        }

        if (preg_match('/Height\s+:\s+(\d+)/i', $raw, $m)) {
            $d['height'] = (int) $m[1];
        }

        if ($d['height']) {
            $d['resolution'] = match (true) {
                $d['height'] >= 2160 => '2160p',
                $d['height'] >= 1440 => '1440p',
                $d['height'] >= 1080 => '1080p',
                $d['height'] >= 720  => '720p',
                default => $d['height'] . 'p',
            };
        }

        if (preg_match('/Format\s+:\s+(.+)/i', $raw, $m)) {
            $d['video'] = self::normalizeVideo(trim($m[1]));
        }

        if (preg_match('/Format profile\s+:\s+(.+)/i', $raw, $m)) {
            $d['profile'] = trim($m[1]);
        }

        if (preg_match('/Bit rate\s+:\s+(.+)/i', $raw, $m)) {
            $d['bitrate'] = trim($m[1]);
        }

        if (preg_match('/Frame rate\s+:\s+([\d\.]+)/i', $raw, $m)) {
            $d['fps'] = trim($m[1]) . ' fps';
        }

        /* ---------------- HDR / DV ---------------- */

        if (preg_match('/Dolby Vision/i', $raw)) {
            $d['dolby_vision'] = true;
            $d['hdr'] = true;
        } elseif (preg_match('/HDR|BT\.2020|SMPTE ST 2086|PQ/i', $raw)) {
            $d['hdr'] = true;
        }

        /* ---------------- AUDIO ---------------- */

        preg_match_all('/Audio\s*\n(.*?)(?=\n\n|Video|\z)/is', $raw, $tracks);
        foreach ($tracks[1] as $block) {
            $codec = null;
            $channels = null;
            $lang = null;

            if (preg_match('/Format\s+:\s+(.+)/i', $block, $m)) {
                $codec = trim($m[1]);
            }
            if (preg_match('/Channel\(s\)\s+:\s+([\d\.]+)/i', $block, $m)) {
                $channels = trim($m[1]);
            }
            if (preg_match('/Language\s+:\s+(.+)/i', $block, $m)) {
                $lang = trim($m[1]);
            }

            if ($codec) {
                $d['audio'][] = self::normalizeAudio($codec, $channels, $lang);
            }
        }

        /* ---------------- TEXT / SUBTITLES ---------------- */

        preg_match_all('/Text\s*\n(.*?)(?=\n\n|Video|Audio|\z)/is', $raw, $texts);
        foreach ($texts[1] as $block) {
            $lang = null;
            $title = null;
            if (preg_match('/Language\s+:\s+(.+)/i', $block, $m)) {
                $lang = trim($m[1]);
            }
            if (preg_match('/Title\s+:\s+(.+)/i', $block, $m)) {
                $title = trim($m[1]);
            }
            if ($lang || $title) {
                $d['text'][] = trim(($title ? $title . " " : "") . ($lang ?? ""));
            }
        }

        /* ---------------- CHAPTERS / MENU ---------------- */

        preg_match_all('/Menu\s*\n(.*)/is', $raw, $menus);
        if (!empty($menus[1])) {
            foreach ($menus[1] as $line) {
                $line = trim($line);
                if ($line) {
                    $d['chapters'][] = $line;
                }
            }
        }

        return $d;
    }

    /* ---------------- NORMALIZERS ---------------- */

    protected static function normalizeVideo(string $raw): string
    {
        return match (true) {
            stripos($raw, 'HEVC') !== false => 'x265',
            stripos($raw, 'AVC') !== false => 'x264',
            stripos($raw, 'MPEG-4') !== false => 'x264',
            default => $raw,
        };
    }

    protected static function normalizeAudio(string $codec, ?string $channels, ?string $lang): string
    {
        $codec = match (true) {
            stripos($codec, 'E-AC-3') !== false => 'DDP',
            stripos($codec, 'AC-3') !== false => 'DD',
            stripos($codec, 'TrueHD') !== false => 'TrueHD',
            stripos($codec, 'DTS-HD') !== false => 'DTS-HD MA',
            stripos($codec, 'DTS') !== false => 'DTS',
            stripos($codec, 'AAC') !== false => 'AAC',
            default => $codec,
        };

        $out = $codec;
        if ($channels) $out .= " {$channels}";
        if ($lang) $out .= " ({$lang})";

        return $out;
    }

    /* ---------------- DESCRIPTION ---------------- */

    public static function toDescription(array $d): string
    {
        if (
            !$d['video'] &&
            !$d['resolution'] &&
            empty($d['audio']) &&
            empty($d['text'])
        ) {
            return '';
        }

        $out = [];
        $out[] = "Generated automatically.";
        $out[] = "";
        $out[] = "[b]MediaInfo[/b]";
        $out[] = "━━━━━━━━━━━━━━━━━━";

        // Video
        if ($d['resolution']) {
            $out[] = "📐 Resolution: {$d['resolution']}";
        }

        if ($d['video']) {
            $videoLine = "🎞 Video: {$d['video']}";

            if ($d['profile']) {
                $videoLine .= " ({$d['profile']})";
            }

            if ($d['hdr']) {
                $videoLine .= $d['dolby_vision']
                    ? " • Dolby Vision"
                    : " • HDR";
            }

            $out[] = $videoLine;
        }

        if ($d['fps']) {
            $out[] = "⏱ Frame rate: {$d['fps']}";
        }

        if ($d['bitrate']) {
            $out[] = "📊 Bitrate: {$d['bitrate']}";
        }

        // Audio
        if (!empty($d['audio'])) {
            $out[] = "🔊 Audio:";
            foreach (array_unique($d['audio']) as $a) {
                $out[] = "  • {$a}";
            }
        }

        // Text / Subtitles
        if (!empty($d['text'])) {
            $out[] = "💬 Subtitles / Text tracks:";
            foreach (array_unique($d['text']) as $t) {
                $out[] = "  • {$t}";
            }
        }

        // Chapters / Menu
        if (!empty($d['chapters'])) {
            $out[] = "📀 Chapters / Menu:";
            foreach ($d['chapters'] as $c) {
                $out[] = "  • {$c}";
            }
        }

        return "\n" . implode("\n", $out);
    }
}
