<?php

namespace App\Helpers;

class TorrentReleaseParser
{
    /**
     * Read technical release tags without inventing values for untagged names.
     * Keep dots intact so audio channel counts and group names survive parsing.
     *
     * @return array{resolution: ?string, source: ?string, audio: ?string, service: ?string, group: ?string}
     */
    public static function parse(?string $name): array
    {
        $details = array_fill_keys(['resolution', 'source', 'audio', 'service', 'group'], null);
        $name = preg_replace('/\.(?:torrent|mkv|mp4|avi|mov|m2ts|ts)$/i', '', trim($name ?? ''));
        $boundary = '(?=$|[.\s_\[\]()\-])';
        $start = '(?<![a-z0-9])';
        $offsets = [];

        if (preg_match('/'.$start.'(4320[pi]|2160[pi]|1440[pi]|1080[pi]|720[pi]|576[pi]|540[pi]|480[pi]|360[pi]|8K|4K|UHD)'.$boundary.'/i', $name, $match, PREG_OFFSET_CAPTURE)) {
            $tag = strtolower($match[1][0]);
            $details['resolution'] = match ($tag) {
                '4k', 'uhd' => '2160p',
                '8k' => '4320p',
                default => $tag,
            };
            $offsets[] = $match[1][1];
        }

        $sources = [
            'WEB[. _-]?DL' => 'WEB-DL', 'WEB[. _-]?Rip' => 'WEBRip',
            'Blu[. _-]?Ray' => 'BluRay', 'BD[. _-]?Rip' => 'BDRip',
            'BR[. _-]?Rip' => 'BRRip', 'UHD[. _-]?BD' => 'UHD BluRay',
            'HD[. _-]?DVD' => 'HD DVD', 'HDTV' => 'HDTV', 'PDTV' => 'PDTV',
            'DVDRip' => 'DVDRip', 'DVD[. _-]?R' => 'DVD-R', 'DVD' => 'DVD',
            'HDRip' => 'HDRip', 'REMUX' => 'REMUX', 'HDCAM' => 'HDCAM',
            'HDTS' => 'HDTS', 'HDTC' => 'HDTC', 'CAMRip' => 'CAMRip',
        ];
        if (preg_match('/'.$start.'('.implode('|', array_keys($sources)).')'.$boundary.'/i', $name, $match, PREG_OFFSET_CAPTURE)) {
            foreach ($sources as $pattern => $label) {
                if (preg_match('/^(?:'.$pattern.')$/i', $match[1][0])) {
                    $details['source'] = $label;
                    break;
                }
            }
            $offsets[] = $match[1][1];
        }

        // Codec-only releases still provide a reliable start for technical tags.
        if (preg_match('/'.$start.'(?:[xh][. _-]?26[45]|HEVC|AVC|AV1)'.$boundary.'/i', $name, $match, PREG_OFFSET_CAPTURE)) {
            $offsets[] = $match[0][1];
        }
        // Music releases may have an audio codec without a video resolution.
        $audioPattern = 'DDP|DD\+|E[. _-]?AC[. _-]?3|True[. _-]?HD|DTS[. _-]?HD(?:[. _-]?(?:MA|HRA))?|DTS[. _-]?X|DTS|AC[. _-]?3|AAC|FLAC|LPCM|PCM|MP3|Opus|DD';
        if ($offsets === [] && preg_match('/'.$start.'('.$audioPattern.')(?:[. _-]?[1-9][. _][0-1])?'.$boundary.'/i', $name, $match, PREG_OFFSET_CAPTURE)) {
            $offsets[] = $match[0][1];
        }
        if ($offsets === []) {
            return $details;
        }

        $offset = min($offsets);
        // A service or audio tag can precede the resolution, but should not be
        // taken from the title. Start after a release year or episode if present.
        $prefix = substr($name, 0, $offset);
        if (preg_match_all('/'.$start.'(?:(?:19|20)\d{2}|S\d{1,2}(?:E\d{1,3})*)'.$boundary.'/i', $prefix, $markers, PREG_OFFSET_CAPTURE)) {
            $last = end($markers[0]);
            $offset = $last[1] + strlen($last[0]);
        }
        $technical = substr($name, $offset);
        if ($details['source'] === null && preg_match('/'.$start.'WEB'.$boundary.'/i', $technical)) {
            $details['source'] = 'WEB';
        }

        // Only the final hyphen introduces a group. Do not consume source/audio
        // suffixes (WEB-DL, DTS-HD, DTS-X), or hyphens within the movie title.
        $hyphen = strrpos($technical, '-');
        if ($hyphen !== false) {
            $candidate = trim(substr($technical, $hyphen + 1));
            if (preg_match('/^[a-z0-9][a-z0-9._]*$/i', $candidate)
                && ! preg_match('/^(?:DL|Rip|HD|MA|HRA|X|R|26[45])(?:$|[._])|^[\d.]+$/i', $candidate)) {
                $details['group'] = $candidate;
                $technical = substr($technical, 0, $hyphen);
            }
        }

        if (preg_match('/'.$start.'(AMZN|NF|NETFLIX|DSNP|DSNY|HMAX|HBO|MAX|ATVP|ATV|APTV|HULU|PCOK|PMTP|PARA|CR|CRUNCHYROLL|BCORE|STAN|iT|iTunes)'.$boundary.'/i', $technical, $match)) {
            $details['service'] = strtoupper($match[1]);
        }

        if (preg_match('/'.$start.'('.$audioPattern.')(?:[. _-]?([1-9][. _][0-1](?:[. _][2-6])?|[1-9]CH))?'.$boundary.'/i', $technical, $match)) {
            $codec = strtoupper(preg_replace('/[. _-]/', '', $match[1]));
            $codec = match ($codec) {
                'DD+', 'EAC3' => 'DDP', 'AC3' => 'AC-3',
                'TRUEHD' => 'TrueHD', 'DTSHD' => 'DTS-HD',
                'DTSHDMA' => 'DTS-HD MA', 'DTSHDHRA' => 'DTS-HD HRA',
                'DTSX' => 'DTS:X', 'OPUS' => 'Opus',
                default => $codec,
            };
            $details['audio'] = $codec.(isset($match[2]) && $match[2] !== '' ? ' '.str_replace([' ', '_'], '.', strtoupper($match[2])) : '');
        }
        if (preg_match('/'.$start.'Atmos'.$boundary.'/i', $technical)) {
            $details['audio'] = $details['audio'] ? $details['audio'].' Atmos' : 'Atmos';
        }

        return $details;
    }

    /** Return a codec family only when the release name contains a codec tag. */
    public static function videoCodec(?string $name): ?string
    {
        if (! preg_match('/(?<![a-z0-9])([xh][. _-]?26[45]|HEVC|AVC|AV1|XviD|DivX|VP9)(?=$|[.\s_\[\]()\-])/i', $name ?? '', $match)) {
            return null;
        }

        return match (strtoupper(preg_replace('/[. _-]/', '', $match[1]))) {
            'X264', 'H264', 'AVC' => 'AVC',
            'X265', 'H265', 'HEVC' => 'HEVC',
            'XVID' => 'XviD',
            'DIVX' => 'DivX',
            default => strtoupper($match[1]),
        };
    }

}
