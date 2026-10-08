<?php

namespace Tests\Integration;

use App\Helpers\Bencode;
use App\Http\Controllers\TorznabController;
use App\Models\User;
use App\Services\TorznabService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TorznabTest extends TestCase
{
    private ?string $torrentFile = null;

    protected function setUp(): void
    {
        if (getenv('TORZNAB_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set TORZNAB_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.torznab_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array', 'services.tmdb.key' => 'test-provider-key']);
        DB::setDefaultConnection('torznab_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, passkey VARCHAR(100), enabled VARCHAR(10), activation_pending BOOLEAN DEFAULT 0, banned_until TIMESTAMP NULL, deleted_at TIMESTAMP NULL, downloadpos VARCHAR(10), hit_and_run_count INT DEFAULT 0',
            'categories' => 'id BIGINT PRIMARY KEY, name VARCHAR(100)',
            'torrents' => 'id BIGINT PRIMARY KEY, name VARCHAR(255), slug VARCHAR(255), size BIGINT DEFAULT 1024, category_id BIGINT, seeders INT DEFAULT 10, leechers INT DEFAULT 2, times_completed INT DEFAULT 5, created_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, imdbid VARCHAR(30), tmdbid BIGINT, free BOOLEAN DEFAULT 0, `double` BOOLEAN DEFAULT 0, file_name VARCHAR(255)',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns})");
        }
        DB::table('users')->insert(['id' => 10, 'passkey' => 'fixture-member-passkey', 'enabled' => 'yes', 'downloadpos' => 'yes']);
        DB::table('categories')->insert([
            ['id' => 11, 'name' => 'Movies HD'], ['id' => 20, 'name' => 'TV Episodes'], ['id' => 27, 'name' => 'Adult'],
        ]);
        foreach ([
            [1, 'Example.Movie.2026.1080p & Special', 11, null],
            [2, 'Show.S01E01.1080p', 20, null],
            [3, 'Show.S01E02E03.1080p', 20, null],
            [4, 'Show.S01.1080p', 20, null],
            [5, 'Show.S02E03.1080p', 20, null],
            [6, 'Adult.Movie', 27, null],
            [7, 'Deleted.Movie', 11, '2026-10-04 00:00:00'],
            [8, 'Daily.Show.2026.10.04.1080p', 20, null],
        ] as [$id, $name, $category, $deleted]) {
            DB::table('torrents')->insert(['id' => $id, 'name' => $name, 'slug' => 'release-'.$id, 'category_id' => $category, 'created_at' => '2026-10-04 12:00:00', 'deleted_at' => $deleted, 'imdbid' => $category === 11 ? 'tt12345' : 'tt54321', 'tmdbid' => $category === 11 ? 100 : 200]);
        }
        $path = sys_get_temp_dir().'/torznab-test-views-'.getmypid();
        @mkdir($path, 0700, true);
        config(['view.compiled' => $path]);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        if ($this->torrentFile) {
            @unlink($this->torrentFile);
        }
        if (getenv('TORZNAB_MYSQL_TEST') === '1') {
            DB::purge('torznab_test');
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    private function call(array $params = [])
    {
        $key = (new TorznabService)->key(User::findOrFail(10));

        return (new TorznabController)->api(Request::create('/torznab/api', 'GET', $params + ['t' => 'search', 'apikey' => $key]), new TorznabService);
    }

    private function xml($response): \SimpleXMLElement
    {
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $xml = simplexml_load_string($response->getContent());
        self::assertNotFalse($xml);

        return $xml;
    }

    private function ids(array $params): array
    {
        $xml = $this->xml($this->call($params));

        return array_map(fn ($item) => (string) $item->guid, iterator_to_array($xml->channel->item, false));
    }

    public function test_private_caps_and_account_keys_work_and_passkey_rotation_revokes_access(): void
    {
        $xml = $this->xml($this->call(['t' => 'caps']));
        self::assertSame('yes', (string) $xml->searching->{'movie-search'}['available']);
        self::assertSame('yes', (string) $xml->searching->{'tv-search'}['available']);
        self::assertSame(401, $this->call(['apikey' => 'invalid'])->getStatusCode());
        self::assertSame(401, $this->call(['apikey' => ['bad']])->getStatusCode());
        $service = new TorznabService;
        $old = $service->key(User::findOrFail(10));
        DB::table('users')->where('id', 10)->update(['passkey' => 'new-passkey']);
        self::assertNull($service->authenticate($old));
        DB::table('users')->where('id', 10)->update(['enabled' => 'no']);
        self::assertSame(401, $this->call()->getStatusCode());
    }

    public function test_http_route_accepts_an_api_key_without_browser_sessions(): void
    {
        Http::fake(['www.cloudflare.com/*' => Http::response("192.0.2.0/24\n", 200)]);
        $request = Request::create('/torznab/api', 'GET', [
            't' => 'caps', 'apikey' => (new TorznabService)->key(User::findOrFail(10)),
        ]);
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        $xml = $this->xml($response);
        self::assertSame('FileIplay', (string) $xml->server['title']);
        self::assertCount(0, $response->headers->getCookies());
        $kernel->terminate($request, $response);
    }

    public function test_http_rate_limits_return_xml_errors(): void
    {
        Http::fake(['www.cloudflare.com/*' => Http::response("192.0.2.0/24\n", 200)]);
        $key = (new TorznabService)->key(User::findOrFail(10));
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        for ($i = 0; $i < 121; $i++) {
            $response = $kernel->handle(Request::create('/torznab/api', 'GET', ['t' => 'caps', 'apikey' => $key]));
        }
        self::assertSame(429, $response->getStatusCode());
        self::assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        self::assertSame('error', simplexml_load_string($response->getContent())->getName());
        self::assertTrue($response->headers->has('Retry-After'));
    }

    public function test_disabled_members_cannot_open_the_setup_page(): void
    {
        $user = User::findOrFail(10);
        $user->enabled = 'no';
        $request = Request::create('/integrations');
        $request->setUserResolver(fn () => $user);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Your account is disabled.');
        (new TorznabController)->setup($request, new TorznabService);
    }

    public function test_movie_categories_identifiers_and_xml_escaping(): void
    {
        self::assertSame(['fileiplay-1'], $this->ids(['t' => 'movie', 'imdbid' => '12345']));
        self::assertSame(['fileiplay-1'], $this->ids(['cat' => '2000', 'tmdbid' => '100']));
        self::assertSame(['fileiplay-1'], $this->ids(['cat' => '2040', 'q' => 'example movie']));
        self::assertSame([], $this->ids(['cat' => '9999']));
        $xml = $this->xml($this->call(['t' => 'movie']));
        self::assertSame('Example.Movie.2026.1080p & Special', (string) $xml->channel->item->title);
        self::assertSame('application/x-bittorrent', (string) $xml->channel->item->enclosure['type']);
        parse_str(parse_url((string) $xml->channel->item->link, PHP_URL_QUERY), $download);
        self::assertSame('get', $download['t']);
        self::assertSame('1', $download['id']);
        self::assertNotNull((new TorznabService)->authenticate($download['apikey']));
    }

    public function test_tv_episode_season_daily_and_tvdb_searches(): void
    {
        self::assertSame(['fileiplay-3'], $this->ids(['t' => 'tvsearch', 'season' => '1', 'ep' => '3']));
        self::assertSame(['fileiplay-4', 'fileiplay-3', 'fileiplay-2'], $this->ids(['t' => 'tvsearch', 'season' => '1']));
        self::assertSame(['fileiplay-8'], $this->ids(['t' => 'tvsearch', 'season' => '2026', 'ep' => '10/04']));
        Http::fake(['api.themoviedb.org/3/find/999*' => Http::response(['tv_results' => [['id' => 200]]])]);
        self::assertSame(['fileiplay-3'], $this->ids(['t' => 'tvsearch', 'tvdbid' => '999', 'season' => '1', 'ep' => '3']));
        Http::assertSentCount(1);
        $this->ids(['t' => 'tvsearch', 'tvdbid' => '999', 'season' => '1', 'ep' => '3']);
        Http::assertSentCount(1);
    }

    public function test_paging_empty_queries_validation_and_provider_failure(): void
    {
        $xml = $this->xml($this->call(['limit' => '2', 'offset' => '1']));
        $response = $xml->channel->children('http://www.newznab.com/DTD/2010/feeds/attributes/')->response;
        self::assertSame('6', (string) $response->attributes()['total']);
        self::assertSame('1', (string) $response->attributes()['offset']);
        self::assertCount(2, $xml->channel->item);
        self::assertSame([], $this->ids(['offset' => '99']));
        self::assertSame(400, $this->call(['limit' => '101'])->getStatusCode());
        self::assertSame(400, $this->call(['q' => ['bad']])->getStatusCode());
        Http::fake(['api.themoviedb.org/3/find/999*' => Http::response([], 503)]);
        $error = $this->call(['t' => 'tvsearch', 'tvdbid' => '999']);
        self::assertSame(503, $error->getStatusCode());
        self::assertStringNotContainsString('test-provider-key', $error->getContent());
    }

    public function test_download_permissions_and_personalised_torrent_preserve_the_info_hash(): void
    {
        self::assertSame(404, $this->call(['t' => 'get', 'id' => '6'])->getStatusCode());
        self::assertSame(404, $this->call(['t' => 'get', 'id' => '7'])->getStatusCode());
        DB::table('users')->where('id', 10)->update(['downloadpos' => 'no']);
        self::assertSame(403, $this->call(['t' => 'get', 'id' => '1'])->getStatusCode());
        DB::table('users')->where('id', 10)->update(['downloadpos' => 'yes', 'hit_and_run_count' => 21]);
        self::assertSame(403, $this->call(['t' => 'get', 'id' => '1'])->getStatusCode());
        DB::table('users')->where('id', 10)->update(['hit_and_run_count' => 0]);
        $info = ['name' => 'fixture.bin', 'length' => 1024, 'piece length' => 1024, 'pieces' => str_repeat('x', 20)];
        $filename = '.torznab-test-'.bin2hex(random_bytes(8)).'.torrent';
        $this->torrentFile = public_path('files/torrents/'.$filename);
        file_put_contents($this->torrentFile, Bencode::bencode(['announce' => 'https://old.test/announce', 'info' => $info]));
        DB::table('torrents')->where('id', 1)->update(['file_name' => $filename]);
        $response = $this->call(['t' => 'get', 'id' => '1', 'free' => '1']);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/x-bittorrent', $response->headers->get('Content-Type'));
        ob_start();
        $response->sendContent();
        $decoded = Bencode::bdecode(ob_get_clean());
        self::assertSame(sha1(Bencode::bencode($info)), sha1(Bencode::bencode($decoded['info'])));
        self::assertStringContainsString('/announce/fixture-member-passkey', $decoded['announce']);
        self::assertStringContainsString('/announce/fixture-member-passkey', $decoded['announce-list'][0][0]);
    }
}
