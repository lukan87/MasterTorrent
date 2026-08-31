<?php

// Legacy wrapper: /announce.php?torrent_pass=...
// Redirecteaza catre noul endpoint: /announce/{passkey}
// IMPORTANT: nu modificam query string-ul (info_hash / peer_id sunt binare si trebuie pastrate exact)

if (!isset($_GET['torrent_pass'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo bencode(['failure reason' => 'Missing torrent_pass']);
    exit();
}

$torrent_pass = (string) $_GET['torrent_pass'];

// acceptam 32-40 hex (surse vechi)
if (!preg_match('/^[0-9a-fA-F]{32,40}$/', $torrent_pass)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo bencode(['failure reason' => 'Invalid torrent_pass']);
    exit();
}

// protocol + host
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
$protocol = $https ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';

// target: /announce/{passkey}
$tracker_url = "{$protocol}://{$host}/announce/" . $torrent_pass;

// luam query string original ca sa NU stricam info_hash / peer_id
$qs = $_SERVER['QUERY_STRING'] ?? '';

// scoatem torrent_pass din query string (din orice pozitie)
$qs = preg_replace('/(^|&)(torrent_pass=[^&]*)/i', '$1', $qs);
$qs = ltrim($qs, '&');

// daca lipsesc parametrii esentiali, raspundem bencoded (nu redirect)
parse_str($qs, $tmp);
if (!isset($tmp['info_hash']) || !isset($tmp['peer_id'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo bencode(['failure reason' => 'Missing required params']);
    exit();
}

// optional: numwant default daca nu exista (fara sa stricam info_hash)
if (!isset($tmp['numwant'])) {
    $qs .= ($qs === '' ? '' : '&') . 'numwant=50';
}

// optional: port default daca nu exista
if (!isset($tmp['port'])) {
    $qs .= ($qs === '' ? '' : '&') . 'port=6881';
}

// optional: compact + support_utp daca nu exista
if (!isset($tmp['compact'])) {
    $qs .= ($qs === '' ? '' : '&') . 'compact=1';
}
if (!isset($tmp['support_utp'])) {
    $qs .= ($qs === '' ? '' : '&') . 'support_utp=1';
}

// optional: ip default daca nu exista (REMOTE_ADDR)
if (!isset($tmp['ip'])) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '127.0.0.1';
    }
    $qs .= ($qs === '' ? '' : '&') . 'ip=' . rawurlencode($ip);
}

// redirect corect: 307
header('Location: ' . $tracker_url . '?' . $qs, true, 307);
exit();


function bencode($data)
{
    if (is_int($data)) {
        return "i{$data}e";
    }
    if (is_string($data)) {
        return strlen($data) . ':' . $data;
    }
    if (is_array($data)) {
        $isList = array_keys($data) === range(0, count($data) - 1);
        $buffer = [$isList ? 'l' : 'd'];
        foreach ($data as $key => $value) {
            if (!$isList) {
                $buffer[] = bencode((string)$key);
            }
            $buffer[] = bencode($value);
        }
        $buffer[] = 'e';
        return implode('', $buffer);
    }
    throw new InvalidArgumentException('Invalid data type for bencoding');
}
