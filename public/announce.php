<?php

if (isset($_GET['torrent_pass'])) {
    // Verificare pentru hash de 32 caractere
    if (preg_match('/^[0-9a-fA-F]{32}$/', $_GET['torrent_pass'])) {
        $torrent_pass = $_GET['torrent_pass'];
    }
    // Verificare pentru hash de lungime variabilă între 32 și 40 caractere
    elseif (preg_match('/^[0-9a-fA-F]{32,40}$/', $_GET['torrent_pass'])) {
        $torrent_pass = $_GET['torrent_pass'];
    } else {
        // Hash invalid
        header('Content-Type: text/plain');
        echo bencode(['failure reason' => 'Invalid torrent_pass']);
        exit();
    }

    // Construim URL complet
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST']; // Detectăm automat dacă este cu www sau fără
    $tracker_url = "{$protocol}://{$host}/announce/" . $torrent_pass;

    // Procesăm parametrii
    $params = $_GET;
    unset($params['torrent_pass']); // Eliminăm 'torrent_pass'

    // Setăm numărul de peers dorit
    $params['numwant'] = min(1, max(0, (int)($params['numwant'] ?? 11500)));
    $params['ip'] = filter_var($params['ip'] ?? $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: $_SERVER['REMOTE_ADDR'];
    $params['port'] = max(1, min(65535, (int)($params['port'] ?? 1))); // Evităm port 0

    // Adăugăm event-ul (implicit sau definit)
    $params['event'] = $params['event'] ?? 'started'; // Default 'started'

    $params['compact'] = 1;
    $params['support_utp'] = 1;

    // Redirecționare
    header("Location: $tracker_url?" . http_build_query($params), true, 302);
    exit();
} else {
    // Răspuns pentru eroare
    header('Content-Type: text/plain');
    echo bencode(['failure reason' => 'Missing torrent_pass']);
    exit();
}


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
