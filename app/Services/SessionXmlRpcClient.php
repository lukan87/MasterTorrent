<?php

namespace App\Services;

use Closure;
use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Response;
use Psr\Log\NullLogger;

/** Reuse the seedbox's verified HTTPS session for existing XML-RPC operations. */
class SessionXmlRpcClient extends Client
{
    public function __construct(string $url, private Closure $transport)
    {
        parent::__construct($url);
    }

    public function send($req, $timeout = 0, $method = '')
    {
        if (!$req instanceof Request) {
            return new Response(0, -1, 'Session XML-RPC requires a single request.');
        }

        try {
            $http = ($this->transport)($req->serialize(), $timeout ?: 30);
            $body = $http->body();
            // Reject external entities before decoding any XML-RPC values.
            if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) {
                return new Response(0, -1, 'HTTP ' . $http->status() . ': unsafe XML-RPC response rejected.');
            }
            // Use a separate parser with a silent logger; raw responses and remote
            // fault strings must never be written to logs.
            $parser = new class('') extends Request {
                public function getLogger()
                {
                    return new NullLogger();
                }
            };
            $parsed = $parser->parseResponse($body, true);
            if ($parsed->faultCode()) {
                return new Response(0, $parsed->faultCode(), 'HTTP ' . $http->status() . ': XML-RPC request failed (fault code ' . $parsed->faultCode() . ').');
            }
            return new Response($parsed->value());
        } catch (\DomainException $e) {
            // The transport supplies only fixed, sanitized diagnostic messages.
            return new Response(0, -1, $e->getMessage());
        } catch (\Throwable $e) {
            return new Response(0, -1, 'Session XML-RPC transport failed. Check connectivity and the TLS certificate.');
        }
    }
}
