<?php

namespace Tests\Integration;

use App\Models\Torrent;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\TestCase;

class CollectionPageRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $cache = sys_get_temp_dir().'/collection-page-views-'.getmypid();
        if (! is_dir($cache)) {
            mkdir($cache, 0700, true);
        }
        config(['view.compiled' => $cache, 'cache.default' => 'array', 'session.driver' => 'array']);
        \Illuminate\View\Component::flushCache();
        \Illuminate\View\Component::forgetFactory();
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    private function films(): array
    {
        $torrent = new Torrent;
        $torrent->forceFill([
            'id' => 55, 'slug' => 'example-release', 'name' => 'Example <script>release</script>',
            'size' => 1073741824, 'seeders' => 1200, 'leechers' => 3, 'created_at' => '2026-10-07 10:00:00',
        ]);
        return [
            ['tmdb_id' => 1, 'title' => 'First <script>film</script>', 'year' => '2020', 'overview' => 'The first chapter.', 'poster' => null, 'is_online' => false, 'movie_id' => null, 'torrents' => collect([$torrent])],
            ['tmdb_id' => 2, 'title' => 'Second film', 'year' => '2026', 'overview' => null, 'poster' => null, 'is_online' => true, 'movie_id' => 22, 'torrents' => collect()],
        ];
    }

    private function render(array $films): string
    {
        $template = file_get_contents(__DIR__.'/../../resources/views/collections/show.blade.php');
        $template = str_replace([
            "@extends('layouts.app')", "@section('title', 'Collection: ' . \$collection['name'])",
            "@section('content')", '@endsection',
        ], '', $template);
        return Blade::render($template, [
            'collection' => ['name' => 'Example <b>collection</b>', 'overview' => 'A complete story.', 'poster' => null, 'backdrop_path' => null, 'uploaded' => 0],
            'movies' => $films,
        ]);
    }

    public function test_collection_counts_links_and_releases_use_available_films(): void
    {
        Auth::guard('web')->forgetUser();
        $html = $this->render($this->films());
        self::assertStringContainsString('1 of 2 films have torrents', $html);
        self::assertStringContainsString('50%', $html);
        self::assertStringContainsString('2020–2026', $html);
        self::assertStringContainsString('First &lt;script&gt;film&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>film</script>', $html);
        self::assertStringContainsString('Example &lt;script&gt;release&lt;/script&gt;', $html);
        self::assertStringContainsString(route('torrents.show', [55, 'example-release']), $html);
        self::assertStringContainsString(asset('images/not-found.jpg'), $html);
        self::assertStringContainsString('1,200 seeders', $html);
        self::assertStringContainsString('Online entry exists', $html);
        self::assertStringNotContainsString('Upload torrent', $html);
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        self::assertSame(2, $xpath->query('//article')->length);
        self::assertSame(1, $xpath->query('//details')->length);
        self::assertSame(1, $xpath->query('//details[@open]')->length);
    }

    public function test_upload_and_online_management_controls_follow_permissions(): void
    {
        foreach ([
            [UserClass::USER, 'no', false, false],
            [UserClass::USER, 'yes', true, false],
            [UserClass::UPLOADER, 'no', true, false],
            [UserClass::MODERATOR, 'no', true, true],
        ] as [$rank, $uploadPermission, $upload, $staff]) {
            $viewer = new User;
            $viewer->forceFill(['id' => 10, 'user_class' => $rank, 'uploadpos' => $uploadPermission]);
            Auth::guard('web')->setUser($viewer);
            $html = $this->render($this->films());
            self::assertSame($upload, str_contains($html, 'Upload torrent'));
            self::assertSame($staff, str_contains($html, 'Add online'));
            if ($staff) {
                self::assertStringContainsString(route('movies.show', 22), $html);
            }
        }
    }

    public function test_empty_collection_renders_zero_coverage_and_an_empty_state(): void
    {
        Auth::guard('web')->forgetUser();
        $html = $this->render([]);
        self::assertStringContainsString('0 of 0 films have torrents', $html);
        self::assertStringContainsString('value="0" max="1"', $html);
        self::assertStringContainsString('No films listed yet', $html);
        self::assertStringNotContainsString('cx-film-art', $html);
    }
}
