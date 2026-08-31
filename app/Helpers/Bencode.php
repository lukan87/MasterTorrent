<?php

namespace App\Helpers;

class Bencode
{
    /* ======================================================
       INTEGER
    ====================================================== */

    private static function parseInteger($s, &$pos)
    {
        $pos++; // skip 'i'
        $end = strpos($s, 'e', $pos);

        if ($end === false) {
            return null;
        }

        $number = substr($s, $pos, $end - $pos);
        $pos = $end + 1;

        if (!preg_match('/^-?\d+$/', $number)) {
            return null;
        }

        return (int)$number;
    }

    /* ======================================================
       STRING
    ====================================================== */

    private static function parseString($s, &$pos)
    {
        $colon = strpos($s, ':', $pos);

        if ($colon === false) {
            return null;
        }

        $length = substr($s, $pos, $colon - $pos);

        if (!ctype_digit($length)) {
            return null;
        }

        $length = (int)$length;

        $pos = $colon + 1;

        $result = substr($s, $pos, $length);

        $pos += $length;

        return $result;
    }

    /* ======================================================
       BDECODE
    ====================================================== */

    public static function bdecode($s, &$pos = 0)
    {
        $c = $s[$pos] ?? null;

        if ($c === 'i') {
            return self::parseInteger($s, $pos);
        }

        if ($c === 'l') {
            $pos++;
            $list = [];

            while ($s[$pos] !== 'e') {
                $list[] = self::bdecode($s, $pos);
            }

            $pos++;
            return $list;
        }

        if ($c === 'd') {
            $pos++;
            $dict = [];

            while ($s[$pos] !== 'e') {
                $key = self::bdecode($s, $pos);
                $value = self::bdecode($s, $pos);

                $dict[$key] = $value;
            }

            $pos++;
            return $dict;
        }

        if (ctype_digit($c)) {
            return self::parseString($s, $pos);
        }

        return null;
    }

    /* ======================================================
       BENCODE
    ====================================================== */

    public static function bencode($data)
    {
        if (is_int($data)) {
            return "i{$data}e";
        }

        if (is_string($data)) {
            return strlen($data) . ':' . $data;
        }

        if (is_array($data)) {

            $isDict = array_keys($data) !== range(0, count($data) - 1);

            if ($isDict) {

                ksort($data, SORT_STRING);

                $result = 'd';

                foreach ($data as $key => $value) {
                    $result .= strlen($key) . ':' . $key;
                    $result .= self::bencode($value);
                }

                return $result . 'e';
            }

            $result = 'l';

            foreach ($data as $value) {
                $result .= self::bencode($value);
            }

            return $result . 'e';
        }

        return null;
    }

    /* ======================================================
       LOAD FILE
    ====================================================== */

    public static function bdecodeFile($filename)
    {
        return self::bdecode(file_get_contents($filename));
    }

    /* ======================================================
       SAFE INFOHASH (RAW BYTES)
    ====================================================== */

    public static function get_infohash_raw(string $torrent)
    {
        $start = strpos($torrent, '4:info');

        if ($start === false) {
            return null;
        }

        $start += 6;

        $pos = $start;

        self::bdecode($torrent, $pos);

        $infoBytes = substr($torrent, $start, $pos - $start);

        return sha1($infoBytes);
    }

    /* ======================================================
       META INFO
    ====================================================== */

    public static function get_meta($torrent)
    {
        $result = [
            'size' => 0,
            'count' => 0
        ];

        if (isset($torrent['info']['files'])) {

            foreach ($torrent['info']['files'] as $file) {
                $result['size'] += $file['length'];
                $result['count']++;
            }

        } else {

            $result['size'] = $torrent['info']['length'] ?? 0;
            $result['count'] = 1;

        }

        return $result;
    }
}