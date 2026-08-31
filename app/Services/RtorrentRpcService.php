<?php

namespace App\Services;

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Encoder;

class RtorrentRpcService
{
    protected Client $client;
    protected Encoder $encoder;

    public function __construct(string $url, string $username, string $password)
    {
        $this->client = new Client($url);
        $this->client->setCredentials($username, $password);
        $this->client->setSSLVerifyPeer(false);
        $this->client->setSSLVerifyHost(0);

        $this->encoder = new Encoder();
    }

    public function getTorrents(): array
    {
        $request = new Request('d.multicall2', [
            $this->encoder->encode(''),
            $this->encoder->encode('main'),
            $this->encoder->encode('d.free_diskspace='),
            $this->encoder->encode('d.creation_date='),
            $this->encoder->encode('d.name='),
            $this->encoder->encode('d.hash='),
            $this->encoder->encode('d.completed_bytes='),
            $this->encoder->encode('d.size_bytes='),
            $this->encoder->encode('d.custom1='),
            $this->encoder->encode('d.base_path='),
            $this->encoder->encode('d.state='),
            $this->encoder->encode('d.complete='),
            $this->encoder->encode('d.connection_current='),
            $this->encoder->encode('d.hashing='),
            $this->encoder->encode('d.ratio='),
            $this->encoder->encode('d.message='),
            $this->encoder->encode('d.up.total='),
        ]);

        $response = $this->client->send($request);

        if ($response->faultCode()) {
            throw new \Exception('RPC Error: ' . $response->faultString());
        }

        return $this->encoder->decode($response->value());
    }
}
