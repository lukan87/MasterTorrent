<?php

namespace App\Services\Torrent;

use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\Exception;
use Laravel\Nightwatch\Records\OutgoingRequest;
use Laravel\Nightwatch\Records\Query;
use Laravel\Nightwatch\Records\Request;

class UploadTelemetryRedactor
{
    public function register(): void
    {
        Nightwatch::redactQueries([$this, 'query']);
        Nightwatch::redactRequests([$this, 'request']);
        Nightwatch::redactOutgoingRequests([$this, 'outgoing']);
        Nightwatch::redactExceptions([$this, 'exception']);
    }

    public function query(Query $record): void
    {
        if (preg_match('/\b(?:announce|passkey|rsskey|token|password|recovery_code|api_token_secrets|encrypted_token)\b/i', $record->sql)) {
            $record->sql = '[credential-bearing query redacted]';
        }
    }

    public function request(Request $record): void
    {
        $record->url = $this->url($record->url);
        $path = parse_url($record->url, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/api/v1/') || str_starts_with($path, '/settings/api') || ($record->method === 'POST' && $path === '/torrents')) {
            $record->payload->replace([]);
            $record->files->replace([]);
            $record->headers->remove('Authorization');
        }
    }

    public function outgoing(OutgoingRequest $record): void
    {
        $record->url = $this->url($record->url);
    }

    public function exception(Exception $record): void
    {
        if (preg_match('/\b(?:announce|passkey|password|token|apikey|api_key)\b/i', $record->message)) {
            $record->message = '[credential-bearing exception redacted]';
        }
    }

    private function url(string $url): string
    {
        $url = preg_replace('/([?&](?:api_key|apikey|token|passkey)=)[^&#]*/i', '$1[redacted]', $url);
        $url = preg_replace('~(/announce/)[^/?#]+~i', '$1[redacted]', $url);

        return preg_replace('~(/rss/download/[^/?#]+/)[^/?#]+~i', '$1[redacted]', $url);
    }
}
