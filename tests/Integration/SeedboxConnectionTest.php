<?php

namespace Tests\Integration;

use App\Services\SeedboxService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class SeedboxConnectionTest extends TestCase
{
    private const ORIGIN = 'https://seedbox.test';
    private const ENDPOINT = self::ORIGIN.'/custom-rutorrent/plugins/httprpc/action.php';
    private const FORM = '<form method="post" action="/login"><input name="csrf_token" value="test-csrf"><input name="username"><input name="password" type="password"></form>';

    protected function setUp(): void
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        Http::preventStrayRequests();
        Log::spy();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        restore_error_handler();
        restore_exception_handler();
    }

    private function resetHttp(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
    }

    private function service(string $auth = 'basic', string $url = self::ENDPOINT): SeedboxService
    {
        return new SeedboxService($url, 'test-user', 'test-password', $auth);
    }

    public function test_basic_and_digest_keep_form_post_and_authentication(): void
    {
        foreach (['basic', 'digest'] as $auth) {
            $this->resetHttp();
            Http::fake([self::ENDPOINT => function ($request, $options) use ($auth) {
                self::assertSame('POST', $request->method());
                self::assertSame('list', $request['mode']);
                self::assertStringContainsString('application/x-www-form-urlencoded', $request->header('Content-Type')[0]);
                self::assertSame($auth === 'basic' ? ['test-user', 'test-password'] : ['test-user', 'test-password', 'digest'], $options['auth']);
                self::assertTrue($options['verify']);
                return Http::response(['t' => [], 'cid' => 1]);
            }]);
            self::assertSame(['t' => [], 'cid' => 1], $this->service($auth)->getTorrents());
        }
    }

    private function sessionLogin(): void
    {
        Http::fake([
            self::ORIGIN.'/login' => function ($request, $options) {
                self::assertTrue($options['verify']);
                self::assertFalse($options['allow_redirects']);
                self::assertArrayNotHasKey('auth', $options);
                if ($request->method() === 'GET') {
                    return Http::response(self::FORM, 200, ['Set-Cookie' => 'prelogin=test-prelogin; Path=/; Secure; HttpOnly']);
                }
                self::assertSame('test-csrf', $request['csrf_token']);
                self::assertSame('test-user', $request['username']);
                self::assertSame('test-password', $request['password']);
                self::assertStringContainsString('prelogin=test-prelogin', implode(';', $request->header('Cookie')));
                return Http::response('', 303, ['Location' => '/custom-rutorrent/', 'Set-Cookie' => 'session=test-session; Path=/; Secure; HttpOnly']);
            },
            self::ORIGIN.'/custom-rutorrent/' => Http::response('<html>ruTorrent</html>'),
        ]);
    }

    public function test_session_posts_csrf_and_cookies_then_reuses_authenticated_session(): void
    {
        $this->sessionLogin();
        Http::fake([self::ENDPOINT => function ($request, $options) {
            self::assertSame('POST', $request->method());
            self::assertSame('list', $request['mode']);
            self::assertStringContainsString('session=test-session', implode(';', $request->header('Cookie')));
            self::assertSame([], $request->header('Authorization'));
            return Http::response(['t' => []]);
        }]);
        $service = $this->service('session');
        self::assertSame(['t' => []], $service->getTorrents());
        self::assertSame(['t' => []], $service->getTorrents());
        Http::assertSentCount(5);
        Log::shouldNotHaveReceived('error');
        Log::shouldNotHaveReceived('warning');
    }

    public function test_invalid_credentials_stop_before_torrent_request(): void
    {
        Http::fake([self::ORIGIN.'/login' => Http::sequence()->push(self::FORM)->push(self::FORM)]);
        self::assertStringContainsString('not accepted', $this->service('session')->getTorrents()['error']);
        Http::assertSentCount(2);
    }

    public function test_missing_csrf_stops_before_sending_credentials(): void
    {
        Http::fake([self::ORIGIN.'/login' => Http::response(str_replace('test-csrf', '', self::FORM))]);
        self::assertStringContainsString('CSRF token', $this->service('session')->getTorrents()['error']);
        Http::assertSentCount(1);
    }

    public function test_session_rejects_cross_origin_form_and_login_redirects(): void
    {
        foreach (['https://evil.test/login', 'http://seedbox.test/login', 'https://seedbox.test:8443/login', 'https://user:secret@seedbox.test/login'] as $target) {
            $this->resetHttp();
            Http::fake([self::ORIGIN.'/login' => Http::response(str_replace('action="/login"', 'action="'.$target.'"', self::FORM))]);
            self::assertArrayHasKey('error', $this->service('session')->getTorrents());
            Http::assertSentCount(1);
        }
        $this->resetHttp();
        Http::fake([self::ORIGIN.'/login' => Http::sequence()->push(self::FORM)->push('', 303, ['Location' => 'https://evil.test/?token=secret'])]);
        $error = $this->service('session')->getTorrents()['error'];
        self::assertStringContainsString('blocked', $error);
        self::assertStringNotContainsString('secret', $error);
        Http::assertSentCount(2);
    }

    public function test_basic_redirect_cannot_forward_credentials_to_another_origin(): void
    {
        Http::fake([self::ENDPOINT => Http::response('', 302, ['Location' => 'https://evil.test/'])]);
        self::assertStringContainsString('blocked', $this->service()->getTorrents()['error']);
        Http::assertSentCount(1);
    }

    public function test_http_and_invalid_response_errors_never_include_remote_secrets(): void
    {
        foreach ([401, 403, 404, 500] as $status) {
            $this->resetHttp();
            Http::fake([self::ENDPOINT => Http::response('test-password cookie=secret-token', $status)]);
            $error = $this->service()->getTorrents()['error'];
            self::assertStringContainsString((string) $status, $error);
            self::assertStringNotContainsString('test-password', $error);
            self::assertStringNotContainsString('secret-token', $error);
        }
        foreach (['false', '<html>login secret-token</html>', '{broken', '{"error":"test-password"}'] as $body) {
            $this->resetHttp();
            Http::fake([self::ENDPOINT => Http::response($body)]);
            $error = $this->service()->getTorrents()['error'];
            self::assertStringContainsString('200', $error);
            self::assertStringNotContainsString('secret-token', $error);
            self::assertStringNotContainsString('test-password', $error);
        }
        $this->resetHttp();
        Http::fake([self::ENDPOINT => Http::response(['unexpected' => []])]);
        self::assertStringContainsString('invalid torrent list', $this->service()->getTorrents()['error']);
        Log::shouldNotHaveReceived('error');
    }

    public function test_transport_errors_are_sanitized(): void
    {
        Http::fake([self::ENDPOINT => Http::failedConnection('password=test-password cookie=secret-token')]);
        $error = $this->service()->getTorrents()['error'];
        self::assertStringContainsString('TLS', $error);
        self::assertStringNotContainsString('test-password', $error);
        self::assertStringNotContainsString('secret-token', $error);
    }

    public function test_session_requires_https_for_httprpc_and_xmlrpc(): void
    {
        self::assertStringContainsString('requires HTTPS', $this->service('session', str_replace('https:', 'http:', self::ENDPOINT))->getTorrents()['error']);
        $rpc = new SeedboxService(str_replace('https:', 'http:', self::ENDPOINT), 'test-user', 'test-password', 'session', true);
        self::assertStringContainsString('XML-RPC request failed', $rpc->startTorrent('hash')['error']);
        Http::assertNothingSent();
    }

    public function test_upload_preserves_root_standard_custom_path_and_port(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'seedbox-fixture-');
        file_put_contents($file, 'torrent fixture');
        try {
            foreach (['', '/rutorrent', '/custom-rutorrent', '/nested/rutorrent'] as $prefix) {
                $this->resetHttp();
                $origin = 'https://seedbox.test:8443';
                $url = $origin.$prefix.'/plugins/httprpc/action.php';
                Http::fake([$origin.$prefix.'/php/addtorrent.php' => function ($request, $options) {
                    self::assertTrue($options['verify']);
                    self::assertSame(CURLAUTH_ANY, $options['curl'][CURLOPT_HTTPAUTH]);
                    self::assertStringContainsString('torrent fixture', $request->body());
                    return Http::response('true');
                }]);
                self::assertSame(['success' => true], $this->service('basic', $url)->addTorrentFile($file));
            }
        } finally {
            unlink($file);
        }
    }

    public function test_session_upload_and_download_share_authenticated_cookies(): void
    {
        $this->sessionLogin();
        Http::fake([
            self::ORIGIN.'/custom-rutorrent/php/addtorrent.php' => function ($request) {
                self::assertStringContainsString('session=test-session', implode(';', $request->header('Cookie')));
                return Http::response('true');
            },
            self::ENDPOINT.'*' => function ($request) {
                self::assertSame('GET', $request->method());
                self::assertSame('download', $request['mode']);
                self::assertStringContainsString('session=test-session', implode(';', $request->header('Cookie')));
                return Http::response('torrent fixture');
            },
        ]);
        $file = tempnam(sys_get_temp_dir(), 'seedbox-fixture-');
        file_put_contents($file, 'torrent fixture');
        try {
            $service = $this->service('session');
            self::assertSame(['success' => true], $service->addTorrentFile($file));
            self::assertSame('torrent fixture', $service->downloadTorrentFile('safe-hash'));
            Http::assertSentCount(5);
        } finally {
            unlink($file);
        }
    }

    public function test_session_endpoint_redirect_is_reported_without_following_or_exposing_token(): void
    {
        $this->sessionLogin();
        Http::fake([self::ENDPOINT => Http::response('', 303, ['Location' => '/login?token=secret-token'])]);
        $error = $this->service('session')->getTorrents()['error'];
        self::assertStringContainsString('303', $error);
        self::assertStringNotContainsString('secret-token', $error);
        Http::assertSentCount(4);
    }
    public function test_session_rejects_redirects_that_would_repost_credentials_and_redirect_loops(): void
    {
        foreach ([307, 308] as $status) {
            $this->resetHttp();
            Http::fake([self::ORIGIN.'/login' => Http::sequence()->push(self::FORM)->push('', $status, ['Location' => '/other'])]);
            self::assertStringContainsString('unsupported', $this->service('session')->getTorrents()['error']);
            Http::assertSentCount(2);
        }
        $this->resetHttp();
        Http::fake([
            self::ORIGIN.'/login' => Http::sequence()->push(self::FORM)->push('', 303, ['Location' => '/loop']),
            self::ORIGIN.'/loop' => Http::response('', 303, ['Location' => '/loop']),
        ]);
        self::assertStringContainsString('excessive', $this->service('session')->getTorrents()['error']);
        Http::assertSentCount(7);
    }

    public function test_expired_session_html_is_not_accepted_as_a_download(): void
    {
        $this->sessionLogin();
        Http::fake([self::ENDPOINT.'*' => Http::response(self::FORM)]);
        self::assertSame('', $this->service('session')->downloadTorrentFile('safe-hash'));
    }

    public function test_upload_failure_is_sanitized_and_does_not_follow_redirects(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'seedbox-fixture-');
        file_put_contents($file, 'torrent fixture');
        try {
            foreach ([Http::response('secret-token', 403), Http::response('false'), Http::response('', 303, ['Location' => '/login?token=secret-token'])] as $response) {
                $this->resetHttp();
                Http::fake([self::ORIGIN.'/custom-rutorrent/php/addtorrent.php' => $response]);
                $error = $this->service()->addTorrentFile($file)['error'];
                self::assertStringNotContainsString('secret-token', $error);
                Http::assertSentCount(1);
            }
        } finally {
            unlink($file);
        }
    }

    public function test_xmlrpc_fault_logs_only_code_and_method(): void
    {
        $client = \Mockery::mock(\PhpXmlRpc\Client::class);
        $client->shouldReceive('send')->once()->andReturn(new \PhpXmlRpc\Response(0, 123, 'test-password session=secret-token'));
        $service = new class($client) extends SeedboxService {
            public function __construct($client)
            {
                parent::__construct('https://seedbox.test/RPC2', 'test-user', 'test-password');
                $this->useRpc = true;
                $this->rpcClient = $client;
            }
        };
        self::assertSame(['error' => 'XML-RPC request failed (fault code 123).'], $service->startTorrent('hash'));
        Log::shouldHaveReceived('error')->once()->with('XML-RPC Error', ['method' => 'd.start', 'faultCode' => 123]);
    }

    public function test_session_xmlrpc_retrieves_torrent_with_session_path_fallback(): void
    {
        $this->sessionLogin();
        $torrent = \App\Helpers\Bencode::bencode(['info' => ['name' => 'Fixture.mkv', 'length' => 1]]);
        $methods = [];
        Http::fake([self::ENDPOINT => function ($request, $options) use ($torrent, &$methods) {
            self::assertTrue($options['verify']);
            self::assertFalse($options['allow_redirects']);
            self::assertSame('text/xml', $request->header('Content-Type')[0]);
            self::assertStringContainsString('session=test-session', implode(';', $request->header('Cookie')));
            self::assertSame([], $request->header('Authorization'));
            $xml = simplexml_load_string($request->body());
            $method = (string) $xml->methodName;
            $methods[] = $method;
            $encoder = new \PhpXmlRpc\Encoder();
            $response = match ($method) {
                'd.directory' => new \PhpXmlRpc\Response($encoder->encode('/downloads/Fixture')),
                'd.tied_to_file' => new \PhpXmlRpc\Response(0, -507, 'secret-token'),
                'session.path' => new \PhpXmlRpc\Response($encoder->encode('/session')),
                'execute.capture' => new \PhpXmlRpc\Response($encoder->encode(base64_encode($torrent))),
            };
            if ($method === 'execute.capture') {
                self::assertStringContainsString("cat -- '/session/ABC.torrent'", (string) $xml->params->param[3]->value->string);
            }
            return Http::response($response->serialize(), 200, ['Content-Type' => 'text/xml']);
        }]);
        $service = new SeedboxService(self::ENDPOINT, 'test-user', 'test-password', 'session', true);
        $result = $service->testRpcSessionPath('abc');
        self::assertSame($torrent, $result['torrentContent']);
        self::assertSame('/downloads/Fixture', $result['basePath']);
        self::assertSame(['d.directory', 'd.tied_to_file', 'session.path', 'execute.capture'], $methods);
        Http::assertSentCount(7);
    }

    public function test_session_xmlrpc_rejects_auth_redirects_and_unsafe_xml_without_leaking_secrets(): void
    {
        foreach ([Http::response('', 303, ['Location' => 'https://evil.test/?token=secret-token']), Http::response('test-password', 403), Http::response('<!DOCTYPE x [<!ENTITY secret SYSTEM "file:///etc/passwd">]><methodResponse/>'), Http::response('not XML secret-token')] as $response) {
            $this->resetHttp();
            $this->sessionLogin();
            Http::fake([self::ENDPOINT => $response]);
            $service = new SeedboxService(self::ENDPOINT, 'test-user', 'test-password', 'session', true);
            $result = $service->testRpcSessionPath('abc');
            self::assertArrayHasKey('error', $result);
            self::assertStringNotContainsString('secret-token', $result['error']);
            self::assertStringNotContainsString('test-password', $result['error']);
            Http::assertSentCount(4);
        }
    }

    public function test_session_source_export_handles_restricted_commands_and_skips_optional_shell_features(): void
    {
        $this->sessionLogin();
        $torrent = \App\Helpers\Bencode::bencode(['info' => ['name' => 'Fixture.mkv', 'length' => 1]]);
        $hash = \App\Helpers\Bencode::get_infohash_raw($torrent);
        $encoder = new \PhpXmlRpc\Encoder();
        Http::fake([
            self::ENDPOINT => Http::sequence()
                ->push((new \PhpXmlRpc\Response($encoder->encode('/downloads')))->serialize())
                ->push((new \PhpXmlRpc\Response($encoder->encode('/tied.torrent')))->serialize())
                ->push((new \PhpXmlRpc\Response(0, -507, 'secret-token'))->serialize()),
            self::ORIGIN.'/custom-rutorrent/plugins/source/action.php' => function ($request, $options) use ($torrent, $hash) {
                self::assertSame($hash, $request['hash']);
                self::assertTrue($options['verify']);
                self::assertFalse($options['allow_redirects']);
                self::assertStringContainsString('session=test-session', implode(';', $request->header('Cookie')));
                return Http::response($torrent, 200, ['Content-Type' => 'application/x-bittorrent']);
            },
        ]);
        $service = new SeedboxService(self::ENDPOINT, 'test-user', 'test-password', 'session', true);
        self::assertSame($torrent, $service->testRpcSessionPath($hash)['torrentContent']);
        self::assertNull($service->findPrimaryVideoFile('/downloads'));
        self::assertNull($service->guessPrimaryVideoFile('/downloads'));
        self::assertNull($service->getMediaInfo('/downloads'));
        Http::assertSentCount(7);
    }

    public function test_source_export_rejects_malformed_and_mismatched_torrents_without_following_redirects(): void
    {
        $valid = \App\Helpers\Bencode::bencode(['info' => ['name' => 'Fixture.mkv', 'length' => 1]]);
        $hash = \App\Helpers\Bencode::get_infohash_raw($valid);
        foreach (['di1ei1ee', 'd4:infod4:name9999:xe', 'd4:infol', '<html>secret-token</html>', \App\Helpers\Bencode::bencode(['info' => ['name' => 'Other.mkv', 'length' => 1]])] as $body) {
            $this->resetHttp();
            $this->sessionLogin();
            Http::fake([self::ORIGIN.'/custom-rutorrent/plugins/source/action.php' => Http::response($body)]);
            $service = new class(self::ENDPOINT, 'test-user', 'test-password', 'session') extends SeedboxService {
                public function export(string $hash): array { return $this->exportTorrentSource($hash); }
            };
            $result = $service->export($hash);
            self::assertArrayHasKey('error', $result);
            self::assertStringNotContainsString('secret-token', $result['error']);
        }
    }

    public function test_upload_accepts_same_endpoint_result_redirects_for_all_auth_types(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'seedbox-fixture-');
        file_put_contents($file, 'torrent fixture');
        $uploadUrl = self::ORIGIN.'/custom-rutorrent/php/addtorrent.php';
        try {
            foreach (['basic', 'digest', 'session'] as $auth) {
                foreach ([
                    [302, '//seedbox.test/custom-rutorrent/php/addtorrent.php?result[]=Success&name[]=private.torrent'],
                    [303, 'addtorrent.php?result=Success'],
                    [302, $uploadUrl.'?result[]=Duplicate'],
                ] as [$status, $location]) {
                    $this->resetHttp();
                    if ($auth === 'session') $this->sessionLogin();
                    Http::fake([$uploadUrl => function ($request, $options) use ($status, $location) {
                        self::assertTrue($options['verify']);
                        self::assertFalse($options['allow_redirects']);
                        return Http::response('', $status, ['Location' => $location]);
                    }]);
                    self::assertSame(['success' => true], $this->service($auth)->addTorrentFile($file));
                    Http::assertSentCount($auth === 'session' ? 4 : 1);
                }
            }
        } finally {
            unlink($file);
        }
    }

    public function test_upload_never_accepts_unsafe_or_failed_result_redirects(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'seedbox-fixture-');
        file_put_contents($file, 'torrent fixture');
        $uploadUrl = self::ORIGIN.'/custom-rutorrent/php/addtorrent.php';
        try {
            foreach ([
                'https://evil.test/custom-rutorrent/php/addtorrent.php?result[]=Success',
                'http://seedbox.test/custom-rutorrent/php/addtorrent.php?result[]=Success',
                'https://seedbox.test:8443/custom-rutorrent/php/addtorrent.php?result[]=Success',
                'https://user:secret-token@seedbox.test/custom-rutorrent/php/addtorrent.php?result[]=Success',
                '/other/addtorrent.php?result[]=Success',
                '/login?result[]=Success&token=secret-token',
                'addtorrent.php?result[]=Success&result[]=Failed',
                'addtorrent.php?result[][]=Success',
                'addtorrent.php?result[]=Failed&name[]=secret-token',
                'addtorrent.php?result[]=FailedFile',
                'addtorrent.php?result[]=FailedDirectory',
                'addtorrent.php?result[]=secret-token',
                'addtorrent.php?token=secret-token',
            ] as $location) {
                $this->resetHttp();
                Http::fake([$uploadUrl => Http::response('', 302, ['Location' => $location])]);
                $result = $this->service()->addTorrentFile($file);
                self::assertArrayHasKey('error', $result);
                self::assertStringNotContainsString('secret-token', $result['error']);
                Http::assertSentCount(1);
            }
            foreach ([301, 307, 308] as $status) {
                $this->resetHttp();
                Http::fake([$uploadUrl => Http::response('', $status, ['Location' => 'addtorrent.php?result[]=Success'])]);
                self::assertArrayHasKey('error', $this->service()->addTorrentFile($file));
                Http::assertSentCount(1);
            }
        } finally {
            unlink($file);
        }
    }

}
