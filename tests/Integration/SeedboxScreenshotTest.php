<?php

namespace Tests\Integration;

use App\Jobs\CaptureSeedboxScreenshot;
use App\Models\Seedbox;
use App\Services\SeedboxService;
use App\Services\Torrent\TorrentImageService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use PhpXmlRpc\Client;
use PHPUnit\Framework\TestCase;

class SeedboxScreenshotTest extends TestCase
{
    protected function setUp(): void
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['cache.default' => 'array']);
    }

    protected function tearDown(): void
    {
        DB::purge('screenshot_test');
        \Mockery::close();
        restore_error_handler();
        restore_exception_handler();
    }

    public function test_six_frames_are_spread_through_the_video_and_paths_are_quoted(): void
    {
        $service = new class extends SeedboxService {
            public array $commands = [];
            public function __construct() { parent::__construct('https://seedbox.test/RPC2', 'user', 'secret', 'basic', true); }
            public function getDuration(string $filePath): ?float { return 700; }
            public function executeRemoteCommand(Client $client, string $command): ?string {
                $this->commands[] = $command;
                return base64_encode('frame');
            }
        };
        $path = "/videos/Film's name.mkv";
        self::assertCount(6, $service->generateScreenshots($path));
        foreach ($service->commands as $index => $command) {
            self::assertStringContainsString('-ss '.(($index + 1) * 100).'.000', $command);
            self::assertStringContainsString(escapeshellarg($path), $command);
            self::assertStringContainsString('-frames:v 1', $command);
        }
        self::assertNull($service->generateScreenshot($path, 6));
        self::assertCount(6, $service->commands);
    }

    public function test_background_job_saves_one_decoded_frame_and_rejects_invalid_data(): void
    {
        if (getenv('TORRENT_IMAGE_MYSQL_TEST') !== '1') self::markTestSkipped('Enable isolated MySQL temporary tables.');
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.screenshot_test' => $connection]);
        DB::setDefaultConnection('screenshot_test');
        DB::statement('CREATE TEMPORARY TABLE torrents (id BIGINT PRIMARY KEY, deleted_at TIMESTAMP NULL)');
        DB::statement('CREATE TEMPORARY TABLE seedboxes (id BIGINT PRIMARY KEY, address VARCHAR(255), username VARCHAR(255), password TEXT, auth_type VARCHAR(20))');
        DB::table('torrents')->insert(['id' => 123]);
        $seedbox = new Seedbox(['password' => 'fixture']);
        DB::table('seedboxes')->insert(['id' => 9, 'address' => 'https://seedbox.test/RPC2', 'username' => 'user', 'password' => $seedbox->getAttributes()['password'], 'auth_type' => 'basic']);
        $remote = \Mockery::mock(SeedboxService::class);
        app()->bind(SeedboxService::class, function ($app, $params) use ($remote) {
            self::assertSame('https://seedbox.test/RPC2', $params['url']);
            self::assertTrue($params['useRpc']);
            return $remote;
        });
        $remote->shouldReceive('generateScreenshot')->once()->with('/video.mkv', 2)->andReturn(base64_encode('binary-frame'));
        $remote->shouldReceive('generateScreenshot')->once()->with('/video.mkv', 3)->andReturn('invalid%');
        $images = \Mockery::mock(TorrentImageService::class);
        $images->shouldReceive('storeWebp')->once()->with(\Mockery::on(fn ($torrent) => $torrent->id === 123), 'binary-frame');
        (new CaptureSeedboxScreenshot(9, 123, '/video.mkv', 2))->handle($images);
        $this->expectException(\RuntimeException::class);
        (new CaptureSeedboxScreenshot(9, 123, '/video.mkv', 3))->handle($images);
    }
    public function test_seedbox_upload_redirects_and_schedules_six_frames_even_when_readd_fails(): void
    {
        if (getenv('TORRENT_IMAGE_MYSQL_TEST') !== '1') self::markTestSkipped('Enable isolated MySQL temporary tables.');
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.screenshot_test' => $connection]);
        DB::setDefaultConnection('screenshot_test');
        DB::statement('CREATE TEMPORARY TABLE torrents (id BIGINT PRIMARY KEY, slug VARCHAR(255), info_hash VARCHAR(40), deleted_at TIMESTAMP NULL)');
        DB::statement('CREATE TEMPORARY TABLE seedboxes (id BIGINT PRIMARY KEY)');
        DB::table('seedboxes')->insert(['id' => 9]);
        \Illuminate\Support\Facades\Queue::fake();
        $user = new \App\Models\User;
        $user->user_class = \App\Models\UserClass::UPLOADER;
        $user->passkey = 'fixture-passkey';
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($user);
        $raw = \App\Helpers\Bencode::bencode(['info' => ['name' => 'Fixture.Movie.2020.mkv', 'length' => 123]]);
        $remote = \Mockery::mock(SeedboxService::class);
        $remote->shouldReceive('testRpcSessionPath')->with('fixture-hash')->andReturn(['torrentContent' => $raw, 'basePath' => '/video.mkv']);
        $remote->shouldReceive('findPrimaryVideoFile')->with('/video.mkv')->andReturn('/video.mkv');
        $remote->shouldReceive('getMediaInfo')->with('/video.mkv')->andReturn(null);
        $metadata = \Mockery::mock(\App\Services\TorrentMetadataService::class);
        $metadata->shouldReceive('torrentName')->andReturn('Fixture.Movie.2020.mkv');
        $metadata->shouldReceive('detectTypeFromName')->andReturn('movie');
        $metadata->shouldReceive('cleanNameAndExtractData')->andReturn(['clean' => 'Fixture Movie', 'year' => '2020']);
        $metadata->shouldReceive('detectCategory')->andReturn(1);
        $metadata->shouldReceive('fetchTmdb')->andReturn([null, null, null]);
        app()->instance(\App\Services\TorrentMetadataService::class, $metadata);
        $torrent = new \App\Models\Torrent;
        $torrent->id = 123;
        $torrent->slug = 'fixture-movie';
        $auto = \Mockery::mock(\App\Services\AutoUploadService::class);
        $auto->shouldReceive('upload')->once()->andReturn(['created' => true, 'torrent' => $torrent]);
        app()->instance(\App\Services\AutoUploadService::class, $auto);
        $controller = \Mockery::mock(\App\Http\Controllers\SeedboxController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('seedboxService')->once()->with(\Mockery::type(Seedbox::class), true)->andReturn($remote);
        $controller->shouldReceive('addTorrentToSeedbox')->once()->andThrow(new \RuntimeException('Remote unavailable'));
        $response = $controller->downloadRebuiltTorrent(9, 'fixture-hash');
        self::assertSame(route('torrents.show', ['id' => 123, 'slug' => 'fixture-movie']), $response->getTargetUrl());
        self::assertStringContainsString('could not be added back', session('warning'));
        \Illuminate\Support\Facades\Queue::assertPushed(CaptureSeedboxScreenshot::class, 6);
        for ($index = 0; $index < 6; $index++) {
            \Illuminate\Support\Facades\Queue::assertPushed(CaptureSeedboxScreenshot::class, fn ($job) => $job->index === $index && $job->torrentId === 123 && $job->filePath === '/video.mkv');
        }
        $remote->shouldNotHaveReceived('generateScreenshots');
    }

}
