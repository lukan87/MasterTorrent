<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use PhpXmlRpc\Encoder;

class SeedboxService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected string $authType;
    protected ?Client $rpcClient = null;
    protected bool $useRpc = false;

    public function __construct(string $url, string $username, string $password, string $authType = 'basic', bool $useRpc = false)
    {
        $this->url = rtrim($url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->authType = strtolower($authType);
        $this->useRpc = $useRpc;

        if ($useRpc) {
            $this->rpcClient = new Client($this->url);
            $this->rpcClient->setSSLVerifyPeer(false);
            $this->rpcClient->setSSLVerifyHost(0);
            $this->rpcClient->setCredentials($this->username, $this->password);
        }
    }

    /**
     * Make XML-RPC call
     */
    protected function rpcCall(string $method, array $params = [])
    {
        if (!$this->rpcClient) {
            return ['error' => 'XML-RPC not initialized'];
        }

        $encoder = new Encoder();
        $xmlParams = [];
        foreach ($params as $p) {
            $xmlParams[] = $encoder->encode($p);
        }

        $request = new Request($method, $xmlParams);
        $response = $this->rpcClient->send($request);

        if ($response->faultCode()) {
            Log::error('XML-RPC Error', [
                'method' => $method,
                'faultCode' => $response->faultCode(),
                'faultString' => $response->faultString(),
            ]);
            return ['error' => $response->faultString()];
        }

        return $response->value();
    }

    /**
     * Standard HTTP POST request (existing)
     */
    protected function request(array $params): array
    {
        $http = Http::withOptions(['verify' => false]);

        $http = ($this->authType === 'basic') ? $http->withBasicAuth($this->username, $this->password) : $http->withDigestAuth($this->username, $this->password);

        $response = $http->asForm()->post($this->url, $params);

        if ($response->failed()) {
            return ['error' => 'Request failed: ' . $response->status()];
        }

        return $response->json() ?? ['error' => 'Invalid response'];
    }

    /**
     * List torrents
     */
    public function listTorrents(): array
    {
        if ($this->useRpc) {
            $encoder = new Encoder();

            $fields = [
                '', 'main',
                'd.free_diskspace=', 'd.creation_date=', 'd.name=',
                'd.hash=', 'd.completed_bytes=', 'd.size_bytes=',
                'd.custom1=', 'd.base_path=', 'd.state=',
                'd.complete=', 'd.connection_current=', 'd.hashing=',
                'd.ratio=', 'd.message=', 'd.up.total='
            ];

            $encodedFields = array_map(fn($f) => $encoder->encode($f), $fields);

            return $this->rpcCall('d.multicall2', $encodedFields);
        }

        return $this->request(['mode' => 'list']);
    }

    public function getTorrents(): array
    {
        
        return $this->listTorrents();
    }

    public function startTorrent(string $hash): array
    {
        if ($this->useRpc) {
            return $this->rpcCall('d.start', [$hash]);
        }
        return $this->request(['mode' => 'start', 'hash' => $hash]);
    }

    public function pauseTorrent(string $hash): array
    {
        if ($this->useRpc) {
            return $this->rpcCall('d.stop', [$hash]);
        }
        return $this->request(['mode' => 'pause', 'hash' => $hash]);
    }

    public function deleteTorrent(string $hash, bool $deleteData = false)
    {
        if ($this->useRpc) {
            return $this->rpcCall('d.erase', [$hash]);
        }

        return $this->request([
            'mode' => $deleteData ? 'remove_and_data' : 'remove',
            'hash' => $hash,
        ]);
    }

    public function torrentTrackers(string $hash): array
    {
        if ($this->useRpc) {
            return $this->rpcCall('d.get_trackers', [$hash]);
        }

        try {
            $http = Http::withOptions(['verify' => false]);
            $http = $this->authType === 'basic'
                ? $http->withBasicAuth($this->username, $this->password)
                : $http->withDigestAuth($this->username, $this->password);

            $response = $http->asForm()->post($this->url, [
                'mode' => 'trkall',
                'hash' => $hash,
            ]);

            return $response->successful() ? ($response->json() ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function downloadTorrentFile(string $hash): string
    {
        if ($this->useRpc) {
            $result = $this->rpcCall('d.get_base64', [$hash]);
            if (isset($result['error'])) return '';
            return base64_decode((string)$result);
        }

        $url = "{$this->url}?mode=download&id=$hash";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($curl);
        curl_close($curl);

        return $content;
    }

    public function addTorrentFile(string $filePath): array
    {
        if ($this->useRpc) {
            $torrent = file_get_contents($filePath);
            $torrentBase64 = base64_encode($torrent);
            return $this->rpcCall('load.raw_start', [$torrentBase64]);
        }

        return $this->uploadTorrentCurl($filePath);
    }

    public function addTorrentFileSeedBox(string $filePath): array
    {
        return $this->addTorrentFile($filePath);
    }

    /**
     * Curl-based upload
     */
    protected function uploadTorrentCurl(string $filePath): array
    {
        $parsedUrl = parse_url($this->url);
        $host = $parsedUrl['host'] ?? null;
        if (!$host) return ['error' => 'Invalid seedbox URL'];

        $uploadUrl = str_contains($this->url, '/rutorrent/')
            ? "https://{$host}/rutorrent/php/addtorrent.php"
            : "https://{$host}/php/addtorrent.php";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $uploadUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            'torrent_file' => curl_file_create($filePath),
        ]);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error || $status >= 400 || $response === 'false') {
            return ['error' => $error ?: "Upload failed, response: $response"];
        }

        return ['success' => true];
    }

   

}
