<?php

namespace Tests\Unit;

use App\Services\SeedboxService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use PhpXmlRpc\Client;
use PhpXmlRpc\Response;
use PhpXmlRpc\Value;
use PHPUnit\Framework\TestCase;

class SeedboxServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Http::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_empty_seedbox_is_a_valid_connection(): void
    {
        Http::swap(new Factory());
        Http::fake(['*' => Http::response(['t' => []])]);
        $service = new SeedboxService('https://example.test/rutorrent', 'user', 'secret');
        self::assertSame(['t' => []], $service->getTorrents());
    }

    public function test_invalid_json_shapes_are_reported_as_errors(): void
    {
        foreach (['true', '"ok"', '{}', '{"t":false}'] as $body) {
            Http::swap(new Factory());
            Http::fake(['*' => Http::response($body, 200, ['Content-Type' => 'application/json'])]);
            $service = new SeedboxService('https://example.test/rutorrent', 'user', 'secret');
            self::assertArrayHasKey('error', $service->getTorrents());
        }
    }

    public function test_connection_failure_is_reported_without_throwing(): void
    {
        Http::swap(new Factory());
        Http::fake(['*' => Http::failedConnection()]);
        $service = new SeedboxService('https://example.test/rutorrent', 'user', 'secret');
        self::assertArrayHasKey('error', $service->getTorrents());
    }

    public function test_rpc_scalar_success_is_normalized_for_actions(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::exactly(3))->method('send')->willReturn(new Response(new Value(0, 'int')));
        $service = $this->rpcService($client);
        self::assertSame(['success' => true], $service->startTorrent(str_repeat('a', 40)));
        self::assertSame(['success' => true], $service->pauseTorrent(str_repeat('a', 40)));
        self::assertSame(['success' => true], $service->deleteTorrent(str_repeat('a', 40)));
    }

    public function test_rpc_upload_sends_binary_as_base64_with_an_empty_target(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('send')->willReturnCallback(function ($request) {
            self::assertSame('load.raw_start', $request->method());
            self::assertSame('', $request->getParam(0)->scalarval());
            self::assertSame('base64', $request->getParam(1)->scalartyp());
            self::assertSame("torrent\x00bytes", $request->getParam(1)->scalarval());
            return new Response(new Value(0, 'int'));
        });
        $file = tempnam(sys_get_temp_dir(), 'seedbox_test_');
        try {
            file_put_contents($file, "torrent\x00bytes");
            self::assertSame(['success' => true], $this->rpcService($client)->addTorrentFile($file));
        } finally {
            unlink($file);
        }
    }

    public function test_rpc_does_not_claim_to_delete_data_it_cannot_delete(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('send');
        self::assertArrayHasKey('error', $this->rpcService($client)->deleteTorrent(str_repeat('a', 40), true));
    }

    public function test_remote_media_path_is_quoted_as_a_literal_argument(): void
    {
        $service = new class('https://example.test/RPC2', 'user', 'secret', 'basic', true) extends SeedboxService {
            public string $command = '';
            public function executeRemoteCommand(Client $client, string $command): ?string
            {
                $this->command = $command;
                return 'media info';
            }
        };
        $path = '/media/film $(touch unwanted) `id` \'name.mkv';
        $service->getMediaInfo($path);
        self::assertSame('mediainfo ' . escapeshellarg($path), $service->command);
    }

    private function rpcService(Client $client): SeedboxService
    {
        return new class($client) extends SeedboxService {
            public function __construct(Client $client)
            {
                parent::__construct('https://example.test/RPC2', 'user', 'secret', 'basic', true);
                $this->rpcClient = $client;
            }
        };
    }
}
