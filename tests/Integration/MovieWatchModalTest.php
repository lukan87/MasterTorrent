<?php

namespace Tests\Integration;

use App\Models\Movie;
use PHPUnit\Framework\TestCase;

class MovieWatchModalTest extends TestCase
{
    protected function setUp(): void
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $cache = sys_get_temp_dir().'/movie-player-views-'.getmypid();
        if (! is_dir($cache)) {
            mkdir($cache, 0700, true);
        }
        config(['view.compiled' => $cache]);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    public function test_modal_has_an_accessible_blank_player_and_a_direct_link_to_the_series_provider(): void
    {
        $movie = new Movie;
        $movie->forceFill(['name' => 'Movie <script>alert(1)</script>', 'imdb_id' => 'tt1300854']);
        $html = view('movies.partials.watch-modal', compact('movie'))->render();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $modal = $xpath->query('//*[@id="movieWatchModal"]')->item(0);
        self::assertSame('movieWatchTitle', $modal->getAttribute('aria-labelledby'));
        self::assertSame('movieWatchHelp', $modal->getAttribute('aria-describedby'));
        $frame = $xpath->query('//iframe')->item(0);
        self::assertSame('about:blank', $frame->getAttribute('src'));
        self::assertSame('Watch Movie <script>alert(1)</script>', $frame->getAttribute('title'));
        self::assertTrue($frame->hasAttribute('allowfullscreen'));
        self::assertStringContainsString('picture-in-picture', $frame->getAttribute('allow'));
        self::assertStringContainsString('fullscreen *', $frame->getAttribute('allow'));
        self::assertSame(1, $xpath->query('//button[@data-movie-watch-fullscreen and @aria-pressed="false"]')->length);
        self::assertSame(1, $xpath->query('//a[@href="https://v2.vidsrc.me/embed/tt1300854"]')->length);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertSame(1, $xpath->query('//button[@data-bs-dismiss="modal"]')->length);
    }
}
