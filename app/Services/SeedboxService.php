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
            $this->rpcClient->setCredentials($this->username, $this->password, $this->authType === 'digest' ? CURLAUTH_DIGEST : CURLAUTH_BASIC);
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
        $response = $this->rpcClient->send($request, 30);

        if ($response->faultCode()) {
            Log::error('XML-RPC Error', [
                'method' => $method,
                'faultCode' => $response->faultCode(),
                'faultString' => $response->faultString(),
            ]);
            return ['error' => $response->faultString()];
        }

        return $encoder->decode($response->value());
    }

    /**
     * Standard HTTP POST request (existing)
     */
    protected function request(array $params): array
    {
        $http = Http::withOptions(['verify' => false])->connectTimeout(10)->timeout(30);

        $http = ($this->authType === 'basic') ? $http->withBasicAuth($this->username, $this->password) : $http->withDigestAuth($this->username, $this->password);

        try {
            $response = $http->asForm()->post($this->url, $params);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return ['error' => 'Could not reach the seedbox. Check its address and try again.'];
        }

        if ($response->failed()) {
            return ['error' => 'Request failed: ' . $response->status()];
        }

        $data = $response->json();
        return is_array($data) ? $data : ['error' => 'Invalid response'];
    }

    /**
     * List torrents
     */
   // App\Services\SeedboxService.php

protected function decodeValue($value)
{
    if ($value instanceof Value) {
        $type = $value->kindOf();

        switch ($type) {
            case 'array':
                $arr = [];
                foreach ($value->scalarval() as $item) {
                    $arr[] = $this->decodeValue($item);
                }
                return $arr;

            case 'struct':
                $arr = [];
                foreach ($value->structMembers() as $k => $v) { // correct method
                    $arr[$k] = $this->decodeValue($v);
                }
                return $arr;

            default:
                return $value->scalarval();
        }
    }

    return $value;
}



public function listTorrents(): array
{
    if ($this->useRpc) {
        $fields = [
            '', 'main',
            'd.free_diskspace=', 'd.creation_date=', 'd.name=',
            'd.hash=', 'd.completed_bytes=', 'd.size_bytes=',
            'd.custom1=', 'd.base_path=', 'd.state=',
            'd.complete=', 'd.connection_current=', 'd.hashing=',
            'd.ratio=', 'd.message=', 'd.up.total='
        ];

        $encoder = new Encoder();
        $encodedFields = array_map(fn($f) => $encoder->encode($f), $fields);

        $result = $this->rpcCall('d.multicall2', $encodedFields);

        return $this->decodeValue($result);
    }

    $result = $this->request(['mode' => 'list']);
    if (!isset($result['error']) && (!isset($result['t']) || !is_array($result['t']))) {
        return ['error' => 'The seedbox returned an invalid torrent list. Check the endpoint address.'];
    }
    return $result;
}


    public function getTorrents(): array
    {
        
        return $this->listTorrents();
    }

    public function startTorrent(string $hash): array
    {
        if ($this->useRpc) {
            $result = $this->rpcCall('d.start', [$hash]);
            return is_array($result) && isset($result['error']) ? $result : ['success' => true];
        }
        return $this->request(['mode' => 'start', 'hash' => $hash]);
    }

    public function pauseTorrent(string $hash): array
    {
        if ($this->useRpc) {
            $result = $this->rpcCall('d.stop', [$hash]);
            return is_array($result) && isset($result['error']) ? $result : ['success' => true];
        }
        return $this->request(['mode' => 'pause', 'hash' => $hash]);
    }

    public function deleteTorrent(string $hash, bool $deleteData = false)
    {
        if ($this->useRpc) {
            if ($deleteData) return ['error' => 'Deleting files is not supported over XML-RPC.'];
            $result = $this->rpcCall('d.erase', [$hash]);
            return is_array($result) && isset($result['error']) ? $result : ['success' => true];
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
            $http = Http::withOptions(['verify' => false])->connectTimeout(10)->timeout(30);
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
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($curl);
        curl_close($curl);

        return is_string($content) ? $content : '';
    }

    public function addTorrentFile(string $filePath): array
    {
        if ($this->useRpc) {
            $torrent = file_get_contents($filePath);
            if ($torrent === false) return ['error' => 'Unable to read torrent file'];
            $result = $this->rpcCall('load.raw_start', ['', new Value($torrent, 'base64')]);
            return is_array($result) && isset($result['error']) ? $result : ['success' => true];
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
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
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


    // In App\Services\SeedboxService.php
public function testRpcSessionPath(string $hash): array
{
    try {
        if (!$this->rpcClient) {
            return ['error' => 'RPC client not initialized'];
        }

        /* ---------- 1. DOWNLOAD DIRECTORY ---------- */

        $dirReq = new Request('d.directory', [
            new Value($hash, 'string'),
        ]);
        $dirRes = $this->rpcClient->send($dirReq, 30);

        if ($dirRes->faultCode()) {
            return ['error' => $dirRes->faultString()];
        }

        $basePath = rtrim($dirRes->value()->scalarval(), '/');

        /* ---------- 2. TRY TIED TORRENT FILE ---------- */

        $torrentFile = '';

        $tiedReq = new Request('d.tied_to_file', [
            new Value($hash, 'string'),
        ]);
        $tiedRes = $this->rpcClient->send($tiedReq, 30);

        if (!$tiedRes->faultCode()) {
            $torrentFile = trim($tiedRes->value()->scalarval());
        }

        /* ---------- 3. FALLBACK TO SESSION PATH ---------- */

        if ($torrentFile === '') {

            $sessReq = new Request('session.path', []);
            $sessRes = $this->rpcClient->send($sessReq, 30);

            if ($sessRes->faultCode()) {
                return ['error' => 'Unable to resolve torrent file location'];
            }

            $sessionPath = rtrim($sessRes->value()->scalarval(), '/');

            $torrentFile = sprintf(
                '%s/%s.torrent',
                $sessionPath,
                strtoupper($hash)
            );
        }

        /* ---------- 4. READ TORRENT FILE ---------- */

        $torrentContent = $this->executeRemoteCommand(
            $this->rpcClient,
            'cat -- ' . escapeshellarg($torrentFile)
        );

        if (!$torrentContent) {
            return ['error' => 'Failed to retrieve torrent file'];
        }

        return [
            'basePath'       => $basePath,        // ✅ ALWAYS VALID
            'torrentContent' => $torrentContent,  // ✅ ALWAYS VALID
            'torrentFile'    => $torrentFile,
        ];

    } catch (\Throwable $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Execute a command on the remote server using XML-RPC 'execute.capture'
 */
public function executeRemoteCommand(Client $client, string $command): ?string
{
    $encoder = new Encoder();
    $args = [
        '',
        'bash',
        '-c',
        sprintf('%s | base64', $command),
    ];
    $args = array_map(fn($item) => $encoder->encode($item), $args);

    $req = new Request('execute.capture', $args);
    $response = $client->send($req, 30);

    if ($response->faultCode() !== 0) {
        return null;
    }

    return base64_decode($encoder->decode($response->value()), true) ?: null;
}



   public function getMediaInfo(string $filePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient) {
        return null;
    }

    // Execute mediainfo command on remote
    $command = 'mediainfo ' . escapeshellarg($filePath);
    return $this->executeRemoteCommand($this->rpcClient, $command);
}



public function extractMediaInfoFromTorrent(array $torrentDecoded, string $basePath, string $torrentName): string
{
    $mediainfo = '';

    if (!isset($torrentDecoded['info']['files'])) {
        return $mediainfo;
    }

    foreach ($torrentDecoded['info']['files'] as $file) {
        $filePath = $basePath . '/' . $torrentName . '/' . $file['path'][0];
        $ext = strtolower(pathinfo($file['path'][0], PATHINFO_EXTENSION));

        if (in_array($ext, ['mkv', 'mp4', 'avi', 'mov'])) {
            $mediainfo = $this->getMediaInfo($filePath);
            if ($mediainfo) break;
        }
    }

    return $mediainfo;
}

//NEW

public function getDuration(string $filePath): ?float
{
    if (!$this->useRpc || !$this->rpcClient) return null;

    $cmd = sprintf(
        'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
        escapeshellarg($filePath)
    );

    $out = trim($this->executeRemoteCommand($this->rpcClient, $cmd) ?? '');

    return is_numeric($out) ? (float)$out : null;
}


public function generateScreenshots(string $filePath): array
{
    if (!$this->useRpc || !$this->rpcClient) {
        return [];
    }

    $duration = $this->getDuration($filePath);
    if (!$duration || $duration < 60) {
        return [];
    }

    $positions = [
        (int)($duration * 0.10),
        (int)($duration * 0.30),
        (int)($duration * 0.50),
        (int)($duration * 0.70),
        (int)($duration * 0.80),
        (int)($duration * 0.90),
    ];

    $shots = [];

    foreach ($positions as $seconds) {
        // Generate screenshot via ffmpeg and output to stdout, then base64
        $cmd = sprintf(
            'ffmpeg -y -ss %d -i %s -frames:v 1 -q:v 2 -f image2pipe pipe:1 | base64',
            $seconds,
            escapeshellarg($filePath)
        );

        $base64 = trim($this->executeRemoteCommand($this->rpcClient, $cmd) ?? '');

        if (!empty($base64)) {
            $shots[] = $base64; // store base64 string
        }
    }

    return $shots;
}


/**
 * Download a remote file from the seedbox and return its base64-encoded content - Screenshots
 */
public function downloadRemoteFile(string $remotePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient) {
        return null;
    }

    // Use `base64 -w 0` to avoid line breaks (important!)
    $cmd = sprintf('base64 -w 0 %s', escapeshellarg($remotePath));
    $base64 = $this->executeRemoteCommand($this->rpcClient, $cmd);

    if (!$base64) return null;

    return trim($base64);
}

public function findPrimaryVideoFile(string $basePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient) {
        return null;
    }

    $cmd = 'find ' . escapeshellarg($basePath)
        . ' -type f \( -iname "*.mkv" -o -iname "*.mp4" -o -iname "*.avi" -o -iname "*.mov" \)'
        . ' ! -iname "*sample*" ! -path "*/extras/*" ! -path "*/subs/*" ! -path "*/subtitles/*"'
        . ' -printf "%s|%p\n" 2>/dev/null | sort -nr | head -n 1 | cut -d"|" -f2-';

    $out = trim($this->executeRemoteCommand($this->rpcClient, $cmd) ?? '');

    return $out !== '' ? $out : null;
}

public function guessPrimaryVideoFile(string $basePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient) {
        return null;
    }

    $extensions = ['mkv', 'mp4', 'avi', 'mov'];

    /* ---------- 1️⃣ SCENE-STYLE (folder/file) ---------- */

    $folderName = basename($basePath);

    foreach ($extensions as $ext) {
        $candidate = $basePath . '/' . $folderName . '.' . $ext;

        $ok = $this->executeRemoteCommand(
            $this->rpcClient,
            'test -f ' . escapeshellarg($candidate) . ' && echo OK'
        );

        if (trim($ok ?? '') === 'OK') {
            return $candidate;
        }
    }

    /* ---------- 2️⃣ SINGLE-FILE TORRENT ---------- */

    foreach ($extensions as $ext) {
        $candidate = $basePath . '.' . $ext;

        $ok = $this->executeRemoteCommand(
            $this->rpcClient,
            'test -f ' . escapeshellarg($candidate) . ' && echo OK'
        );

        if (trim($ok ?? '') === 'OK') {
            return $candidate;
        }
    }

    return null;
}

public function findTvEpisodeFile(string $basePath): ?array
{
    if (!$this->useRpc || !$this->rpcClient) {
        return null;
    }

    $escaped = escapeshellarg($basePath);

    $cmd = 'find ' . $escaped . ' -type f -iname "*.mkv" ! -iname "*sample*"';
    $output = $this->executeRemoteCommand($this->rpcClient, $cmd);

    if (!$output) return null;

    $files = array_filter(array_map('trim', explode("\n", $output)));
    if (empty($files)) return null;

    $episodes = [];
    $season = null;

    foreach ($files as $file) {
        if (preg_match('/S(\d{2})E(\d{2})/i', basename($file), $m)) {
            $season = $m[1];
            $episodes[(int)$m[2]] = $file; // episode number as key
        }
    }

    if (empty($episodes)) {
        return null;
    }

    ksort($episodes); // sort by episode number

    $episodeNumbers = array_keys($episodes);
    $min = min($episodeNumbers);
    $max = max($episodeNumbers);

    $expected = range($min, $max);
    $missing = array_diff($expected, $episodeNumbers);

    $isPack = count($episodes) > 1;
    $isComplete = empty($missing);

    // Pick largest episode for MediaInfo
    $largestFile = null;
    $largestSize = 0;

    foreach ($episodes as $epFile) {
        $sizeCmd = 'stat -c %s -- ' . escapeshellarg($epFile);
        $size = $this->executeRemoteCommand($this->rpcClient, $sizeCmd);

        if (is_numeric($size) && (int)$size > $largestSize) {
            $largestSize = (int)$size;
            $largestFile = $epFile;
        }
    }

    return [
        'file' => $largestFile,
        'season' => $season,
        'episode_count' => count($episodes),
        'is_pack' => $isPack,
        'is_complete' => $isComplete,
        'missing_episodes' => array_values($missing),
        'episode_range' => "{$min}-{$max}",
    ];
}





public function findPrimaryVideoRecursive(string $basePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient) {
        return null;
    }

    return $this->findPrimaryVideoFile($basePath);
}






public function rpcEchoTest(): ?string
{
    if (!$this->rpcClient) return null;

    return $this->executeRemoteCommand(
        $this->rpcClient,
        'echo hello'
    );
}

public function getRpcClient(): ?\PhpXmlRpc\Client
{
    return $this->rpcClient;
}

           

}
