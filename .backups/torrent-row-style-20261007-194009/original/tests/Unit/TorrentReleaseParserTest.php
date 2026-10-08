<?php

namespace Tests\Unit;

use App\Helpers\TorrentReleaseParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TorrentReleaseParserTest extends TestCase
{
    #[DataProvider('releaseNames')]
    public function test_release_details(?string $name, array $expected): void
    {
        self::assertSame(array_combine(['resolution', 'source', 'audio', 'service', 'group'], $expected), TorrentReleaseParser::parse($name));
    }

    public static function releaseNames(): array
    {
        return [
            'requested example' => ['Mutiny.2026.1080p.AMZN.WEB-DL.DDP5.1.H.265-Fr334ALL', ['1080p', 'WEB-DL', 'DDP 5.1', 'AMZN', 'Fr334ALL']],
            'TV underscores' => ['Show_S01E02_2160p_DSNP_WEB-DL_DDP5_1_Atmos_H265-FLUX.mkv', ['2160p', 'WEB-DL', 'DDP 5.1 Atmos', 'DSNP', 'FLUX']],
            'spaces and case' => ['Movie 2025 720P nf webrip aac2.0 x264-Group', ['720p', 'WEBRip', 'AAC 2.0', 'NF', 'Group']],
            'service before resolution' => ['Movie.2025.NF.1080p.WEB-DL.DD+5.1-GROUP', ['1080p', 'WEB-DL', 'DDP 5.1', 'NF', 'GROUP']],
            'interlaced' => ['Movie.2020.1080i.HDTV.AC3.2.0-Team', ['1080i', 'HDTV', 'AC-3 2.0', null, 'Team']],
            'lossless audio' => ['Movie.2020.2160p.BluRay.TrueHD.7.1.Atmos-NTb', ['2160p', 'BluRay', 'TrueHD 7.1 Atmos', null, 'NTb']],
            'DTS HD MA' => ['Movie.2020.1080p.BluRay.DTS-HD.MA.5.1.x264-DON', ['1080p', 'BluRay', 'DTS-HD MA 5.1', null, 'DON']],
            'EAC3' => ['Movie.2020.1080p.AMZN.WEB-DL.E-AC-3.5.1-GROUP', ['1080p', 'WEB-DL', 'DDP 5.1', 'AMZN', 'GROUP']],
            '4K' => ['Movie.2020.4K.WEBRip.FLAC.2.0-GROUP.torrent', ['2160p', 'WEBRip', 'FLAC 2.0', null, 'GROUP']],
            '8K' => ['Movie.2020.8K.WEBRip-GROUP', ['4320p', 'WEBRip', null, null, 'GROUP']],
            'DVD' => ['Movie.1999.576p.DVDRip.MP3-GROUP.avi', ['576p', 'DVDRip', 'MP3', null, 'GROUP']],
            'no resolution' => ['Movie.2020.AMZN.WEB-DL.DDP5.1-GROUP', [null, 'WEB-DL', 'DDP 5.1', 'AMZN', 'GROUP']],
            'bare WEB' => ['Movie.2020.1080p.NF.WEB.H264-GROUP', ['1080p', 'WEB', null, 'NF', 'GROUP']],
            'WEB-DL without group' => ['Movie.2020.1080p.WEB-DL', ['1080p', 'WEB-DL', null, null, null]],
            'WEB-DL audio without group' => ['Movie.2020.1080p.WEB-DL.DDP5.1', ['1080p', 'WEB-DL', 'DDP 5.1', null, null]],
            'DTS-HD without group' => ['Movie.2020.1080p.BluRay.DTS-HD.MA.5.1', ['1080p', 'BluRay', 'DTS-HD MA 5.1', null, null]],
            'title hyphen' => ['Spider-Man.2020.1080p.BluRay', ['1080p', 'BluRay', null, null, null]],
            'plain title hyphen' => ['Spider-Man', [null, null, null, null, null]],
            'title service word' => ['The.MAX.2020.1080p.WEBRip-GROUP', ['1080p', 'WEBRip', null, null, 'GROUP']],
            'group service word' => ['Movie.2020.1080p.WEBRip-MAX', ['1080p', 'WEBRip', null, null, 'MAX']],
            'non video' => ['Software.Collection.2026', [null, null, null, null, null]],
            'missing name' => [null, [null, null, null, null, null]],
            'empty name' => ['', [null, null, null, null, null]],
        ];
    }
}
