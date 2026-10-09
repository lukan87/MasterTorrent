<?php

namespace App\Services;

use App\Services\Torrent\TorrentParser;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Client\PendingRequest;

/** Uses ruTorrent HTTPRPC/source capabilities; never executes remote commands. */
class SeedboxPublishingClient extends SeedboxService
{
    private array $pinnedAddresses = [];

    protected function publicAddresses(string $host): array
    {
        $host = trim($host, '[]');
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $addresses = [$host];
        } else {
            $addresses = [];
            foreach (dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
                if (isset($record['ip'])) {
                    $addresses[] = $record['ip'];
                }
                if (isset($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }
        if (! $addresses) {
            throw new \DomainException('Seedbox address could not be verified.');
        }
        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                throw new \DomainException('Automatic publishing requires a public seedbox address.');
            }
        }

        return $addresses;
    }

    protected function validateUrl(string $url): Uri
    {
        $uri = parent::validateUrl($url);
        if ($uri->getScheme() !== 'https' || ($url === $this->url && $uri->getQuery() !== '')) {
            throw new \DomainException('Automatic publishing requires an HTTPS endpoint without a query.');
        }
        $this->pinnedAddresses[$uri->getHost()] ??= $this->publicAddresses($uri->getHost());

        return $uri;
    }

    protected function http(bool $multipart = false): PendingRequest
    {
        $uri = $this->validateUrl($this->url);
        $addresses = $this->pinnedAddresses[$uri->getHost()];
        $address = $addresses[0];
        $address = str_contains($address, ':') ? '['.$address.']' : $address;

        return parent::http($multipart)->withOptions([
            'allow_redirects' => false, 'proxy' => '',
            'curl' => [CURLOPT_RESOLVE => [$uri->getHost().':'.($uri->getPort() ?? 443).':'.$address]],
        ]);
    }

    protected function loginSession(PendingRequest $http): void
    {
        $uri = $this->validateUrl($this->url);
        $address = $this->pinnedAddresses[$uri->getHost()][0];
        $address = str_contains($address, ':') ? '['.$address.']' : $address;
        parent::loginSession($http->withOptions(['proxy' => '', 'curl' => [
            CURLOPT_RESOLVE => [$uri->getHost().':'.($uri->getPort() ?? 443).':'.$address],
        ]]));
    }

    public function candidates(): array
    {
        $result = $this->getTorrents();
        if (isset($result['error'])) {
            throw new \DomainException('Seedbox listing is unavailable.');
        }
        $candidates = [];
        foreach ($result['t'] ?? [] as $hash => $row) {
            if (! preg_match('/^[a-f0-9]{40}$/i', (string) $hash) || ! is_array($row)) {
                continue;
            }
            // HTTPRPC removes the hash from the documented multicall columns.
            if (! isset($row[5],$row[8],$row[19]) || ! is_numeric($row[5]) || (int) $row[5] <= 0
                || ! is_numeric($row[8]) || (int) $row[8] < (int) $row[5] || (int) $row[19] !== 0
                || (int) ($row[2] ?? 0) !== 1 || (int) ($row[1] ?? 1) !== 0 || (int) ($row[23] ?? 1) !== 0) {
                continue;
            }
            $candidates[strtolower($hash)] = ['hash' => strtolower($hash), 'name' => is_string($row[4] ?? null) ? mb_substr($row[4], 0, 255) : 'Torrent',
                'active' => (int) ($row[28] ?? 0) === 1];
        }

        return $candidates;
    }

    public function source(string $hash): string
    {
        if (! preg_match('/^[a-f0-9]{40}$/D', $hash)) {
            throw new \DomainException('Invalid source hash.');
        }
        $uri = $this->validateUrl($this->url);
        $suffix = '/plugins/httprpc/action.php';
        if (! str_ends_with($uri->getPath(), $suffix)) {
            throw new \DomainException('Source export is unsupported.');
        }
        $url = (string) $uri->withPath(substr($uri->getPath(), 0, -strlen($suffix)).'/plugins/source/action.php');
        $response = $this->http()->withOptions(['stream' => true])->asForm()->post($url, ['hash' => strtoupper($hash)]);
        if (! $response->successful()) {
            throw new \DomainException('Source export is unavailable.');
        }
        $stream = $response->toPsrResponse()->getBody();
        $raw = '';
        $limit = config('upload-api.torrent_max_kb') * 1024;
        try {
            while (! $stream->eof() && strlen($raw) <= $limit) {
                $part = $stream->read(min(65536, $limit + 1 - strlen($raw)));
                if ($part === '' && ! $stream->eof()) {
                    throw new \DomainException('Source export stalled.');
                }
                $raw .= $part;
            }
        } finally {
            $stream->close();
        }
        $parser = app(TorrentParser::class);
        $parser->decode($raw);
        if ($parser->infoHash !== $hash) {
            throw new \DomainException('Exported source hash does not match.');
        }

        return $raw;
    }

    public function verifyPublished(string $hash): void
    {
        $result = $this->request(['mode' => 'recheck', 'hash' => strtoupper($hash)]);
        if (isset($result['error'])) {
            throw new \DomainException('Provider cannot verify the new torrent.');
        }
    }

    public function registerPublished(string $path, string $sourceHash): bool
    {
        // Only use the verified source torrent directory supplied by the client.
        $properties = $this->request(['mode' => 'prp', 'hash' => strtoupper($sourceHash), 'cmd' => 'd.directory']);
        $directory = $properties[7] ?? null;
        if (! is_string($directory) || ! str_starts_with($directory, '/') || strlen($directory) > 4096
            || preg_match('~[\x00-\x1f\x7f]~', $directory) || in_array('..', explode('/', $directory), true)) {
            throw new \DomainException('The provider cannot verify the existing download directory.');
        }
        $uri = $this->validateUrl($this->url);
        $suffix = '/plugins/httprpc/action.php';
        $url = (string) $uri->withPath(substr($uri->getPath(), 0, -strlen($suffix)).'/php/addtorrent.php');
        $file = fopen($path, 'rb');
        if (! $file) {
            throw new \DomainException('Published torrent file is unavailable.');
        }
        try {
            // Register a NEW hash stopped, retaining the original torrent and data.
            $response = $this->http(true)->attach('torrent_file', $file, 'fileiplay.torrent')->post($url,
                ['dir_edit' => $directory, 'torrents_start_stopped' => '1', 'not_add_path' => '1']);
            $result = $this->uploadRedirectResult($response, $url);
            if (! $result || isset($result['error'])) {
                throw new \DomainException('Provider registration was not confirmed.');
            }
            $location = $response->header('Location');
            parse_str((new Uri($this->sameOriginUrl($url, $location)))->getQuery(), $query);

            return ($query['result'][0] ?? $query['result'] ?? null) === 'Success';
        } finally {
            fclose($file);
        }
    }
}
