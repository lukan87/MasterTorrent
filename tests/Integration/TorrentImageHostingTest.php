<?php

namespace Tests\Integration;

use App\Models\Torrent;
use App\Models\TorrentImage;
use App\Services\Torrent\TorrentImageService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TorrentImageHostingTest extends TestCase
{
    private string $diskRoot;
    private string $source;

    protected function setUp(): void
    {
        if (getenv('TORRENT_IMAGE_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set TORRENT_IMAGE_MYSQL_TEST=1 for isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        $this->diskRoot = sys_get_temp_dir().'/torrent-image-test-'.bin2hex(random_bytes(6));
        mkdir($this->diskRoot, 0700);
        mkdir($this->diskRoot.'/views', 0700);
        $this->source = $this->diskRoot.'/source.png';
        $image = imagecreatetruecolor(2000, 1200);
        imagepng($image, $this->source);
        imagedestroy($image);
        config([
            'database.connections.image_hosting_test' => $connection, 'cache.default' => 'array',
            'filesystems.disks.public.root' => $this->diskRoot.'/public', 'view.compiled' => $this->diskRoot.'/views',
        ]);
        Storage::forgetDisk('public');
        DB::setDefaultConnection('image_hosting_test');
        DB::statement('CREATE TEMPORARY TABLE torrent_images (id BIGINT AUTO_INCREMENT PRIMARY KEY, torrent_id BIGINT, path VARCHAR(255), fallback VARCHAR(255) NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        if (getenv('TORRENT_IMAGE_MYSQL_TEST') === '1') {
            DB::purge('image_hosting_test');
            (new \Illuminate\Filesystem\Filesystem)->deleteDirectory($this->diskRoot);
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    private function torrent(): Torrent
    {
        $torrent = new Torrent;
        $torrent->id = 123;
        return $torrent;
    }

    public function test_screenshot_is_resized_and_stored_locally_without_http(): void
    {
        $image = (new TorrentImageService)->storeWebp($this->torrent(), $this->source);
        $this->assertStringStartsWith('torrent_images/', $image->path);
        $size = getimagesizefromstring(Storage::disk('public')->get($image->path));
        $this->assertLessThanOrEqual(1920, $size[0]);
        $this->assertLessThanOrEqual(1080, $size[1]);
        $this->assertSame('image/webp', $size['mime']);
        Http::assertNothingSent();
    }

    public function test_seedbox_binary_is_saved_as_a_local_webp(): void
    {
        $image = (new TorrentImageService)->storeWebp($this->torrent(), file_get_contents($this->source));
        $this->assertTrue(Storage::disk('public')->exists($image->path));
        Http::assertNothingSent();
    }

    public function test_prepare_uploads_before_attaching_database_rows(): void
    {
        $request = Request::create('/torrents', 'POST', [], [], ['images' => [new UploadedFile($this->source, 'screen.png', 'image/png', null, true)]]);
        $service = new TorrentImageService;
        $paths = $service->prepare($request);
        $this->assertSame(0, TorrentImage::count());
        $service->attach($this->torrent(), $paths);
        $this->assertSame(1, TorrentImage::count());
    }

    public function test_local_driver_and_legacy_image_urls_continue_to_work(): void
    {
        $image = (new TorrentImageService)->storeWebp($this->torrent(), $this->source);
        $this->assertTrue(Storage::disk('public')->exists($image->path));
        $this->assertStringEndsWith('/storage/'.$image->path, $image->url);
        Http::assertNothingSent();
        (new TorrentImageService)->deleteMany($this->torrent(), [$image->id]);
        $this->assertFalse(Storage::disk('public')->exists($image->path));
        $this->assertSame(0, TorrentImage::count());
    }

    public function test_failed_database_attach_cleans_up_local_files(): void
    {
        $service = new TorrentImageService;
        $request = Request::create('/torrents', 'POST', [], [], ['images' => [new UploadedFile($this->source, 'screen.png', 'image/png', null, true)]]);
        $paths = $service->prepare($request);
        DB::statement('ALTER TABLE torrent_images MODIFY torrent_id BIGINT NOT NULL');
        $torrent = $this->torrent();
        $torrent->id = null;
        try {
            $service->attach($torrent, $paths);
            $this->fail('Expected database failure.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertSame([], Storage::disk('public')->allFiles());
        }
    }

    public function test_too_many_screenshots_fail_before_storing_files(): void
    {
        $files = [];
        for ($i = 0; $i < 11; $i++) {
            $files[] = new UploadedFile($this->source, 'screen.png', 'image/png', null, true);
        }
        $request = Request::create('/torrents', 'POST', [], [], ['images' => $files]);
        $this->expectException(ValidationException::class);
        try { (new TorrentImageService)->prepare($request); }
        finally { Http::assertNothingSent(); $this->assertSame(0, TorrentImage::count()); }
    }

    public function test_gallery_displays_hosted_and_legacy_images_together(): void
    {
        $hosted = new TorrentImage(['path' => 'https://i.ibb.co/fixture/screenshot.webp']);
        $local = new TorrentImage(['path' => 'torrent_images/legacy.webp']);
        $local->fallback = 'torrent_images/legacy.jpg';
        $torrent = $this->torrent()->setRelation('images', collect([$hosted, $local]));
        $html = view('torrents.partials.screens', compact('torrent'))->render();
        $this->assertStringContainsString('data-image-src="https://i.ibb.co/fixture/screenshot.webp"', $html);
        $this->assertStringContainsString('src="https://i.ibb.co/fixture/screenshot.webp"', $html);
        $this->assertStringContainsString('/storage/torrent_images/legacy.webp', $html);
        $this->assertStringContainsString('/storage/torrent_images/legacy.jpg', $html);
        $this->assertStringNotContainsString('/storage/https:', $html);
    }

}
