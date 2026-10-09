<?php

namespace App\Services\Torrent;

use Illuminate\Validation\ValidationException;

/** A bounded decoder for untrusted upload bytes. Tracker/download decoding is unchanged. */
class TorrentParser
{
    private string $raw;

    private int $position = 0;

    private int $nodes = 0;

    public ?string $infoHash = null;

    public function decode(string $raw): array
    {
        if ($raw === '' || strlen($raw) > config('upload-api.torrent_max_kb', 10240) * 1024 || $raw[0] !== 'd') {
            $this->invalid();
        }
        $this->raw = $raw;
        $this->position = $this->nodes = 0;
        $this->infoHash = null;
        $value = $this->parse();
        if ($this->position !== strlen($raw) || ! is_array($value) || ! isset($value['info']) || ! is_array($value['info'])) {
            $this->invalid();
        }
        $this->validateInfo($value['info']);

        return $value;
    }

    private function parse(int $depth = 0): mixed
    {
        if ($depth > config('upload-api.max_depth', 64) || ++$this->nodes > 1000000 || $this->position >= strlen($this->raw)) {
            $this->invalid();
        }
        $type = $this->raw[$this->position];
        if ($type === 'i') {
            $end = strpos($this->raw, 'e', ++$this->position);
            if ($end === false) {
                $this->invalid();
            }
            $number = substr($this->raw, $this->position, $end - $this->position);
            if (! preg_match('/^(0|-?[1-9][0-9]*)$/D', $number) || filter_var($number, FILTER_VALIDATE_INT) === false) {
                $this->invalid();
            }
            $this->position = $end + 1;

            return (int) $number;
        }
        if (ctype_digit($type)) {
            $colon = strpos($this->raw, ':', $this->position);
            if ($colon === false) {
                $this->invalid();
            }
            $size = substr($this->raw, $this->position, $colon - $this->position);
            if (! preg_match('/^(0|[1-9][0-9]*)$/D', $size) || strlen($size) > 8) {
                $this->invalid();
            }
            $size = (int) $size;
            $this->position = $colon + 1;
            if ($size > strlen($this->raw) - $this->position) {
                $this->invalid();
            }
            $value = substr($this->raw, $this->position, $size);
            $this->position += $size;

            return $value;
        }
        if ($type !== 'd' && $type !== 'l') {
            $this->invalid();
        }
        $this->position++;
        $value = [];
        while (($this->raw[$this->position] ?? null) !== 'e') {
            if ($type === 'd') {
                if (! isset($this->raw[$this->position]) || ! ctype_digit($this->raw[$this->position])) {
                    $this->invalid();
                }
                $key = $this->parse($depth + 1);
                if (array_key_exists($key, $value)) {
                    $this->invalid();
                }
                $start = $this->position;
                $value[$key] = $this->parse($depth + 1);
                if ($depth === 0 && $key === 'info') {
                    $this->infoHash = sha1(substr($this->raw, $start, $this->position - $start));
                }
            } else {
                $value[] = $this->parse($depth + 1);
            }
        }
        $this->position++;

        return $value;
    }

    private function validateInfo(array $info): void
    {
        $this->component($info['name'] ?? null);
        if (isset($info['name.utf-8'])) {
            $this->component($info['name.utf-8']);
        }
        if (isset($info['symlink path']) || (is_string($info['attr'] ?? null) && str_contains($info['attr'], 'l'))) {
            $this->invalid();
        }
        if (! is_int($info['piece length'] ?? null) || $info['piece length'] <= 0
            || ! is_string($info['pieces'] ?? null) || strlen($info['pieces']) % 20 !== 0
            || isset($info['meta version'])) {
            $this->invalid();
        }
        $size = 0;
        if (isset($info['files'])) {
            if (isset($info['length']) || ! is_array($info['files']) || ! array_is_list($info['files'])
                || count($info['files']) === 0 || count($info['files']) > config('upload-api.max_files', 100000)) {
                $this->invalid();
            }
            $paths = [];
            foreach ($info['files'] as $file) {
                if (! is_array($file) || ! is_array($file['path'] ?? null) || ! array_is_list($file['path']) || $file['path'] === []) {
                    $this->invalid();
                }
                foreach ($file['path'] as $part) {
                    $this->component($part);
                }
                if (isset($file['symlink path']) || (is_string($file['attr'] ?? null) && str_contains($file['attr'], 'l'))) {
                    $this->invalid();
                }
                if (isset($file['path.utf-8'])) {
                    if (! is_array($file['path.utf-8']) || ! array_is_list($file['path.utf-8']) || count($file['path.utf-8']) !== count($file['path'])) {
                        $this->invalid();
                    }
                    foreach ($file['path.utf-8'] as $part) {
                        $this->component($part);
                    }
                }
                $path = implode('/', $file['path']);
                if (isset($paths[$path]) || mb_strlen($path) > 255) {
                    $this->invalid();
                }
                $paths[$path] = true;
                $size = $this->addLength($size, $file['length'] ?? null);
            }
        } else {
            $size = $this->addLength(0, $info['length'] ?? null);
        }
        $pieces = intdiv($size, $info['piece length']) + ($size % $info['piece length'] === 0 ? 0 : 1);
        if ($size <= 0 || intdiv(strlen($info['pieces']), 20) !== $pieces) {
            $this->invalid();
        }
    }

    private function addLength(int $size, mixed $length): int
    {
        if (! is_int($length) || $length < 0 || $length > PHP_INT_MAX - $size) {
            $this->invalid();
        }

        return $size + $length;
    }

    private function component(mixed $value): void
    {
        if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || $value === '' || $value === '.' || $value === '..'
            || strlen($value) > 255 || preg_match('~[\\\\/\x00-\x1f\x7f]~', $value)
            || preg_match('/^[A-Za-z]:/', $value)) {
            $this->invalid();
        }
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['torrent' => 'The file is not a valid supported BitTorrent v1 torrent.']);
    }
}
