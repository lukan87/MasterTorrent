<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UploadApiRequestSize
{
    public function handle(Request $request, Closure $next)
    {
        $length = $request->server('CONTENT_LENGTH');
        abort_if(is_numeric($length) && (float) $length > config('upload-api.request_max_kb') * 1024, 413, 'The request exceeds the upload limit.');
        // Multipart/chunked requests may not provide Content-Length. Bound their
        // parsed fields and file sizes as well; infrastructure still limits raw bodies.
        $bytes = 0;
        $nodes = 0;
        $this->measure($request->all(), $bytes, $nodes);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    private function measure(array $values, int &$bytes, int &$nodes, int $depth = 0): void
    {
        abort_if($depth > 64, 413, 'The request exceeds the upload limit.');
        foreach ($values as $key => $value) {
            abort_if(++$nodes > 100000, 413, 'The request exceeds the upload limit.');
            $bytes += strlen((string) $key);
            if ($value instanceof UploadedFile && $value->isValid()) {
                $bytes += $value->getSize();
            } elseif (is_array($value)) {
                $this->measure($value, $bytes, $nodes, $depth + 1);
            } elseif (is_scalar($value)) {
                $bytes += strlen((string) $value);
            }
            abort_if($bytes > config('upload-api.request_max_kb') * 1024, 413, 'The request exceeds the upload limit.');
        }
    }
}
