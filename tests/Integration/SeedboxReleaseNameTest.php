<?php

namespace Tests\Integration;

use App\Services\TorrentMetadataService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SeedboxReleaseNameTest extends TestCase
{
    public static function releases(): array
    {
        return [
            ['Practical.Magic.2.2026.1080p.TELESYNC.x264-DKS.mkv', 'Practical Magic 2', '2026', 'movie', null, null],
            ['1917.2019.1080p.BluRay.x264-GROUP', '1917', '2019', 'movie', null, null],
            ['Blade.Runner.2049.2017.2160p.BluRay.x265-GROUP', 'Blade Runner 2049', '2017', 'movie', null, null],
            ['2001.A.Space.Odyssey.1968.1080p.BluRay-GROUP', '2001 A Space Odyssey', '1968', 'movie', null, null],
            ['The.Web.2013.1080p.WEB-DL-GROUP', 'The Web', '2013', 'movie', null, null],
            ['The.English.2022.S01E01.1080p.WEB-DL', 'The English', '2022', 'tv', 1, 1],
            ['The.Last.of.Us_S01E01_1080p_WEB-DL_DDP5.1_H.264-GROUP', 'The Last of Us', null, 'tv', 1, 1],
            ['Show.Season.2.COMPLETE.1080p.WEB-DL-GROUP', 'Show', null, 'tv', 2, null],
            ['Show.S01.E02.720p.HDTV.mkv', 'Show', null, 'tv', 1, 2],
            ['Show.1x03.720p.HDTV.avi', 'Show', null, 'tv', 1, 3],
            ['Show.Episode.4.1080p.WEBRip.mp4', 'Show', null, 'tv', null, 4],
            ['Show.S01E01E02.1080p.WEBRip', 'Show', null, 'tv', 1, 1],
            ['[YTS.MX] Se7en.1995.1080p.BluRay.x264', 'Se7en', '1995', 'movie', null, null],
            ['[REC].2007.1080p.BluRay', 'REC', '2007', 'movie', null, null],
            ['Spider-Man.1080p.WEB-DL-GROUP', 'Spider-Man', null, 'movie', null, null],
            ['The.Complete.Unknown.2020.1080p.WEB-DL', 'The Complete Unknown', '2020', 'movie', null, null],
            ['Léon.1994.1080p.BluRay', 'Léon', '1994', 'movie', null, null],
            ['Movie.Title', 'Movie Title', null, 'movie', null, null],
        ];
    }

    #[DataProvider('releases')]
    public function test_release_names_preserve_titles_and_extract_years_and_episodes(string $release, string $title, ?string $year, string $type, ?int $season, ?int $episode): void
    {
        $service = new TorrentMetadataService;
        self::assertSame($type, $service->detectTypeFromName($release));
        self::assertSame([
            'clean' => $title, 'year' => $year, 'season' => $season, 'episode' => $episode,
        ], $service->cleanNameAndExtractData($release));
    }

    public function test_torrent_names_prefer_nonempty_utf8_values_and_reject_missing_names(): void
    {
        $service = new TorrentMetadataService;
        self::assertSame('Léon.1994', $service->torrentName(['info' => ['name.utf-8' => 'Léon.1994', 'name' => 'Leon.1994']]));
        self::assertSame('Original.Name', $service->torrentName(['info' => ['name.utf-8' => ' ', 'name' => 'Original.Name']]));
        self::assertSame('Original.Name', $service->torrentName(['info' => ['name' => ' Original.Name ']]));
        self::assertNull($service->torrentName(['info' => ['name' => '']]));
        self::assertNull($service->torrentName(['info' => ['name' => []]]));
        self::assertNull($service->torrentName([]));
    }
}
