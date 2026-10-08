<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use PhpXmlRpc\Encoder;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class SeedboxService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected string $authType;
    protected ?Client $rpcClient = null;
    protected bool $useRpc = false;
    protected ?CookieJar $cookies = null;
    protected bool $sessionAuthenticated = false;
    protected bool $remoteCommandsUnavailable = false;

    public function __construct(string $url, string $username, string $password, string $authType = 'basic', bool $useRpc = false)
    {
        $this->url = rtrim($url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->authType = strtolower($authType);
        $this->useRpc = $useRpc;

        if ($useRpc && $this->authType === 'session') {
            $this->rpcClient = new SessionXmlRpcClient($this->url, function (string $body, int $timeout): Response {
                $response = $this->http()->timeout($timeout)->withBody($body, 'text/xml')->post($this->url);
                if ($error = $this->responseError($response)) {
                    throw new \DomainException($error);
                }
                return $response;
            });
        } elseif ($useRpc) {
            $this->rpcClient = new Client($this->url);
            $this->rpcClient->setSSLVerifyPeer(true);
            $this->rpcClient->setSSLVerifyHost(2);
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
            ]);
            return ['error' => 'XML-RPC request failed (fault code ' . $response->faultCode() . ').'];
        }

        return $encoder->decode($response->value());
    }

    /** HTTPRPC uses form fields, not an XML-RPC body. */
    protected function request(array $params): array
    {
        try {
            $response = $this->http()->asForm()->post($this->url, $params);
            if ($error = $this->responseError($response)) {
                return ['error' => $error];
            }
            $data = $response->json();
            if (!is_array($data)) {
                return ['error' => 'HTTP ' . $response->status() . ': expected JSON data from the HTTPRPC endpoint.'];
            }
            // Remote error strings may contain secrets or server diagnostics.
            if (isset($data['error'])) {
                return ['error' => 'HTTP ' . $response->status() . ': the seedbox rejected the HTTPRPC request.'];
            }
            return $data;
        } catch (\DomainException $e) {
            return ['error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['error' => 'Seedbox transport failed. Check DNS, connectivity, timeouts, and the TLS certificate.'];
        }
    }

    protected function validateUrl(string $url): Uri
    {
        $uri = new Uri($url);
        if (!in_array($uri->getScheme(), ['http', 'https'], true) || $uri->getHost() === '' || $uri->getUserInfo() !== '' || $uri->getFragment() !== '') {
            throw new \DomainException('Invalid seedbox URL. Use an HTTP(S) address without embedded credentials or fragments.');
        }
        if ($this->authType === 'session' && $uri->getScheme() !== 'https') {
            throw new \DomainException('Session authentication requires HTTPS.');
        }
        return $uri;
    }

    protected function sameOriginUrl(string $base, string $location): string
    {
        $target = UriResolver::resolve(new Uri($base), new Uri($location));
        $origin = $this->validateUrl($this->url);
        $this->validateUrl((string) $target);
        if ($target->getScheme() !== $origin->getScheme() || $target->getHost() !== $origin->getHost() || $target->getPort() !== $origin->getPort()) {
            throw new \DomainException('Seedbox redirect blocked: destination must have the same scheme, host, and port.');
        }
        return (string) $target;
    }

    protected function http(bool $negotiateAuth = false): PendingRequest
    {
        $this->validateUrl($this->url);
        $http = Http::withOptions(['verify' => true])->connectTimeout(10)->timeout(30);
        if ($this->authType === 'session') {
            $this->cookies ??= new CookieJar();
            $http = $http->withOptions(['cookies' => $this->cookies])->withoutRedirecting();
            if (!$this->sessionAuthenticated) {
                $this->loginSession($http);
            }
            return $http;
        }
        if (!in_array($this->authType, ['basic', 'digest'], true)) {
            throw new \DomainException('Unsupported seedbox authentication type.');
        }
        // Keep existing Basic/Digest request behavior, including cURL negotiation
        // for downloads/uploads, but never forward requests to another origin.
        $http = $http->withOptions(['allow_redirects' => [
            'max' => 5,
            'on_redirect' => function ($request, $response, $target) {
                $this->sameOriginUrl((string) $request->getUri(), (string) $target);
            },
        ]]);
        if ($negotiateAuth) {
            return $http->withOptions(['curl' => [
                CURLOPT_HTTPAUTH => CURLAUTH_ANY,
                CURLOPT_USERPWD => $this->username . ':' . $this->password,
            ]])->withoutRedirecting();
        }
        return $this->authType === 'basic'
            ? $http->withBasicAuth($this->username, $this->password)
            : $http->withDigestAuth($this->username, $this->password);
    }

    /** Cookies and CSRF values exist only in this service instance's memory. */
    protected function loginSession(PendingRequest $http): void
    {
        $origin = $this->validateUrl($this->url)->withPath('/login')->withQuery('');
        $loginUrl = (string) $origin;
        $response = $http->get($loginUrl);
        if ($response->status() !== 200) {
            throw new \DomainException('Session login form unavailable (HTTP ' . $response->status() . ').');
        }
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML($response->body(), LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new \DOMXPath($dom);
        $form = $xpath->query('//form[.//input[@name="csrf_token"] and .//input[@name="username"] and .//input[@name="password"]]')->item(0);
        if (!$form || strtolower($form->getAttribute('method')) !== 'post') {
            throw new \DomainException('Session login requires a supported CSRF-protected username/password form.');
        }
        $csrf = $xpath->query('.//input[@name="csrf_token"]', $form)->item(0)->getAttribute('value');
        if ($csrf === '') {
            throw new \DomainException('Session login form is missing its CSRF token.');
        }
        $action = $this->sameOriginUrl($loginUrl, $form->getAttribute('action'));
        $response = (clone $http)->asForm()->post($action, [
            'username' => $this->username,
            'password' => $this->password,
            'csrf_token' => $csrf,
        ]);
        $currentUrl = $action;
        for ($redirects = 0; $response->redirect(); $redirects++) {
            if ($redirects >= 5 || !in_array($response->status(), [301, 302, 303], true) || !$response->header('Location')) {
                throw new \DomainException('Session login returned an unsupported or excessive redirect.');
            }
            $currentUrl = $this->sameOriginUrl($currentUrl, $response->header('Location'));
            $response = $http->get($currentUrl);
        }
        if ($response->status() !== 200 || str_contains($response->body(), 'name="csrf_token"') || str_contains($response->body(), "name='csrf_token'")) {
            throw new \DomainException('Session login was not accepted (HTTP ' . $response->status() . '). Check the saved credentials.');
        }
        $this->sessionAuthenticated = true;
    }

    protected function responseError(Response $response): ?string
    {
        $status = $response->status();
        if (in_array($status, [401, 403], true)) {
            return "HTTP {$status}: authentication rejected or access denied. Check the saved credentials and authentication type.";
        }
        if ($response->redirect()) {
            $this->sessionAuthenticated = false;
            // Do not return Location: it may contain session tokens.
            return "HTTP {$status}: seedbox redirected the request; a session login or corrected endpoint may be required.";
        }
        if (!$response->successful()) {
            return "HTTP {$status}: seedbox request failed.";
        }
        if ($this->authType === 'session' && preg_match('/<form\b/i', $response->body()) && (str_contains($response->body(), 'name="password"') || str_contains($response->body(), "name='password'"))) {
            $this->sessionAuthenticated = false;
            return "HTTP {$status}: seedbox session expired or login was rejected.";
        }
        return null;
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

        $result = $this->request(['mode' => 'trkall', 'hash' => $hash]);
        return isset($result['error']) ? [] : $result;
    }

    public function downloadTorrentFile(string $hash): string
    {
        if ($this->useRpc) {
            $result = $this->rpcCall('d.get_base64', [$hash]);
            if (isset($result['error'])) return '';
            return base64_decode((string)$result);
        }
        try {
            $response = $this->http(true)->get($this->url, ['mode' => 'download', 'id' => $hash]);
            return $this->responseError($response) === null ? $response->body() : '';
        } catch (\Throwable $e) {
            return '';
        }
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
     * Multipart upload, preserving the installation path and authentication.
     */
    protected function uploadTorrentCurl(string $filePath): array
    {
        $file = null;
        try {
            $uri = $this->validateUrl($this->url);
            $suffix = '/plugins/httprpc/action.php';
            if (!str_ends_with($uri->getPath(), $suffix)) {
                return ['error' => 'Upload requires an HTTPRPC endpoint ending in /plugins/httprpc/action.php.'];
            }
            $prefix = substr($uri->getPath(), 0, -strlen($suffix));
            $uploadUrl = (string) $uri->withPath($prefix . '/php/addtorrent.php')->withQuery('');
            $file = fopen($filePath, 'rb');
            if ($file === false) return ['error' => 'Unable to read torrent file.'];
            $response = $this->http(true)->attach('torrent_file', $file, basename($filePath))->post($uploadUrl);
            if ($result = $this->uploadRedirectResult($response, $uploadUrl)) return $result;
            if ($error = $this->responseError($response)) return ['error' => $error];
            if (trim($response->body()) === 'false') {
                return ['error' => 'HTTP ' . $response->status() . ': seedbox rejected the torrent upload.'];
            }
            return ['success' => true];
        } catch (\DomainException $e) {
            return ['error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['error' => 'Torrent upload transport failed. Check connectivity and the TLS certificate.'];
        } finally {
            if (is_resource($file)) fclose($file);
        }
    }

    /** Read ruTorrent's upload result without following or repeating the POST. */
    protected function uploadRedirectResult(Response $response, string $uploadUrl): ?array
    {
        if (!in_array($response->status(), [302, 303], true) || !$response->header('Location')) {
            return null;
        }
        $target = new Uri($this->sameOriginUrl($uploadUrl, $response->header('Location')));
        if ($target->getPath() !== (new Uri($uploadUrl))->getPath()) {
            return null;
        }
        parse_str($target->getQuery(), $query);
        $results = $query['result'] ?? null;
        if (is_string($results)) $results = [$results];
        if (!is_array($results) || count($results) !== 1) {
            return null;
        }
        $result = reset($results);
        if ($result === 'Success' || $result === 'Duplicate') {
            return ['success' => true];
        }
        // Never return query strings, filenames, or unknown provider messages.
        return ['error' => 'HTTP ' . $response->status() . ': ruTorrent rejected the torrent upload.'];
    }

    /** ruTorrent's authenticated Get source action, without shell execution. */
    protected function exportTorrentSource(string $hash): array
    {
        if (!preg_match('/^[a-f0-9]{40}$/i', $hash)) {
            return ['error' => 'Invalid torrent hash for source export.'];
        }
        try {
            $uri = $this->validateUrl($this->url);
            $suffix = '/plugins/httprpc/action.php';
            if (!str_ends_with($uri->getPath(), $suffix)) {
                return ['error' => 'Torrent source export requires a ruTorrent HTTPRPC endpoint.'];
            }
            $prefix = substr($uri->getPath(), 0, -strlen($suffix));
            $url = (string) $uri->withPath($prefix . '/plugins/source/action.php')->withQuery('');
            $response = $this->http()->asForm()->post($url, ['hash' => $hash]);
            if ($error = $this->responseError($response)) return ['error' => $error];
            $body = $response->body();
            if (!$this->validBencode($body)) {
                return ['error' => 'HTTP ' . $response->status() . ': ruTorrent source export did not return a valid torrent file.'];
            }
            $decoded = \App\Helpers\Bencode::bdecode($body);
            if (!is_array($decoded) || !isset($decoded['info']) || !is_array($decoded['info']) || strtolower(\App\Helpers\Bencode::get_infohash_raw($body)) !== strtolower($hash)) {
                return ['error' => 'The exported torrent does not match the requested torrent hash.'];
            }
            return ['torrentContent' => $body];
        } catch (\DomainException $e) {
            return ['error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['error' => 'Torrent source export failed. Check connectivity and the TLS certificate.'];
        }
    }

    /** Check bounds before calling the existing decoder on an external response. */
    protected function validBencode(string $body): bool
    {
        $position = 0;
        $length = strlen($body);
        $parse = function (int $depth = 0) use (&$parse, &$position, $length, $body): bool {
            if ($depth > 64 || $position >= $length) return false;
            $type = $body[$position];
            if ($type === 'i') {
                $end = strpos($body, 'e', ++$position);
                if ($end === false || !preg_match('/^-?(0|[1-9][0-9]*)$/D', substr($body, $position, $end - $position))) return false;
                $position = $end + 1;
                return true;
            }
            if ($type === 'l' || $type === 'd') {
                $position++;
                while ($position < $length && $body[$position] !== 'e') {
                    if ($type === 'd' && (!ctype_digit($body[$position]) || !$parse($depth + 1))) return false;
                    if (!$parse($depth + 1)) return false;
                }
                if ($position >= $length) return false;
                $position++;
                return true;
            }
            if (!ctype_digit($type)) return false;
            $colon = strpos($body, ':', $position);
            if ($colon === false) return false;
            $size = substr($body, $position, $colon - $position);
            if (!preg_match('/^(0|[1-9][0-9]*)$/D', $size) || strlen($size) > strlen((string) $length)) return false;
            $position = $colon + 1;
            if ((int) $size > $length - $position) return false;
            $position += (int) $size;
            return true;
        };
        return $length > 0 && $body[0] === 'd' && $parse() && $position === $length;
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
            return ['error' => $this->rpcClient instanceof SessionXmlRpcClient ? $dirRes->faultString() : 'XML-RPC directory lookup failed.'];
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

        if (!$torrentContent && $this->authType === 'session') {
            $export = $this->exportTorrentSource($hash);
            if (isset($export['error'])) return $export;
            $torrentContent = $export['torrentContent'];
        }
        if (!$torrentContent) {
            return ['error' => 'Failed to retrieve torrent file'];
        }

        return [
            'basePath'       => $basePath,        // ✅ ALWAYS VALID
            'torrentContent' => $torrentContent,  // ✅ ALWAYS VALID
            'torrentFile'    => $torrentFile,
        ];

    } catch (\Throwable $e) {
        return ['error' => 'XML-RPC session lookup failed.'];
    }
}

/**
 * Execute a command on the remote server using XML-RPC 'execute.capture'
 */
public function executeRemoteCommand(Client $client, string $command): ?string
{
    if ($this->remoteCommandsUnavailable) return null;
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
        if ($client instanceof SessionXmlRpcClient) $this->remoteCommandsUnavailable = true;
        return null;
    }

    return base64_decode($encoder->decode($response->value()), true) ?: null;
}



   public function getMediaInfo(string $filePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient || $this->remoteCommandsUnavailable) {
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


/** Capture one of six evenly spread frames without creating remote files. */
public function generateScreenshot(string $filePath, int $index): ?string
{
    if (!$this->useRpc || !$this->rpcClient || $index < 0 || $index >= 6) {
        return null;
    }

    $duration = $this->getDuration($filePath);
    if (!$duration || $duration < 1) return null;

    return $this->captureScreenshot($filePath, (float) ($duration * (($index + 1) / 7)));
}

private function captureScreenshot(string $filePath, float $seconds): ?string
{
    $cmd = sprintf(
        'ffmpeg -nostdin -hide_banner -loglevel error -ss %.3F -i %s -frames:v 1 -vf %s -q:v 2 -f image2pipe pipe:1 | base64',
        $seconds,
        escapeshellarg($filePath),
        escapeshellarg("scale='min(1920,iw)':'min(1080,ih)':force_original_aspect_ratio=decrease")
    );
    $base64 = trim($this->executeRemoteCommand($this->rpcClient, $cmd) ?? '');

    return $base64 !== '' ? $base64 : null;
}

public function generateScreenshots(string $filePath): array
{
    $shots = [];
    for ($index = 0; $index < 6; $index++) {
        $shot = $this->generateScreenshot($filePath, $index);
        if ($shot !== null) $shots[] = $shot;
    }
    return $shots;
}


/**
 * Download a remote file from the seedbox and return its base64-encoded content - Screenshots
 */
public function downloadRemoteFile(string $remotePath): ?string
{
    if (!$this->useRpc || !$this->rpcClient || $this->remoteCommandsUnavailable) {
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
    if (!$this->useRpc || !$this->rpcClient || $this->remoteCommandsUnavailable) {
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
    if (!$this->useRpc || !$this->rpcClient || $this->remoteCommandsUnavailable) {
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
    if (!$this->useRpc || !$this->rpcClient || $this->remoteCommandsUnavailable) {
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
    if (!$this->useRpc || !$this->rpcClient || $this->remoteCommandsUnavailable) {
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
