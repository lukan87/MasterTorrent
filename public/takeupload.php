<?php
// Setare fișier log
$log_file = '/var/log/error_takeupload.log';

// Configurare pentru logurile PHP
ini_set('log_errors', 1);
ini_set('error_log', $log_file);

// Funcție personalizată pentru logare
function log_error($message) {
    global $log_file;
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $date = date('Y-m-d H:i:s');
    $formatted_message = "[$date] [IP: $client_ip] $message\n";
    file_put_contents($log_file, $formatted_message, FILE_APPEND);
}

// Lista de cuvinte pentru verificare
$keywords = ["Tatutu", "Chefi", "Romania", "Iubire", "Vocea", "Sef", "Fierbinti", "Sariti", "iUmor", "Scara"];

// Funcție pentru verificarea existenței cuvintelor-cheie în conținutul fișierului
function contains_keywords($file_path, $keywords) {
    $content = file_get_contents($file_path); // Citește conținutul fișierului
    foreach ($keywords as $keyword) {
        if (stripos($content, $keyword) !== false) { // Verifică dacă există oricare cuvânt
            return true;
        }
    }
    return false;
}


// Funcție pentru descărcare fișier torrent cu gestionarea caracterelor speciale
function download_torrent_file($file_name) {
    $base_url = 'http://213.202.230.226/rss/download/';
    $unique_id = '579814008ff95c33722c015994f3d7ec';

    // Codifică numele fișierului pentru caractere speciale
    $encoded_file_name = rawurlencode($file_name);

    $download_url = $base_url . $encoded_file_name . "/" . $unique_id;

    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0\r\n"
        ]
    ];

    $context = stream_context_create($options);
    $torrent_content = file_get_contents($download_url, false, $context);

    if ($torrent_content === false) {
        log_error("Eroare la descărcarea fișierului de la: $download_url");
        throw new Exception("Eroare la descărcarea fișierului de la: $download_url");
    }

    return $torrent_content;
}

// Exemplu log inițial
log_error("Scriptul takeupload.php a fost accesat.");


// Restricționează accesul doar pentru IP-ul permis
$allowed_ip = '62.210.38.52';

// Încearcă să obțină IP-ul real al clientului, ținând cont de proxie sau alte anteturi
$client_ip = $_SERVER['REMOTE_ADDR']; // IP-ul detectat de server direct
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $client_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $client_ip = $_SERVER['HTTP_CLIENT_IP'];
}

// Compară IP-ul clientului cu cel permis
#if ($client_ip !== $allowed_ip) {
 #   http_response_code(403); // Răspuns 403 Forbidden
  #  die('Acces interzis. Această acțiune este permisă doar pentru IP-ul autorizat.');
#}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexiune simplă la baza de date
$host = 'localhost';
$user = 'dan';
$password = '3]j[pobWNV@6eEnt';
$database = 'lastfiles';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    log_error("Conexiunea la baza de date a eșuat: " . $conn->connect_error);
    die("Conexiunea la baza de date a eșuat.");
}

// Funcție pentru generarea info_hash din fișierul .torrent
function bencode($data) {
    if (is_int($data)) {
        return 'i' . $data . 'e';
    } elseif (is_string($data)) {
        return strlen($data) . ':' . $data;
    } elseif (is_array($data)) {
        // Verificăm dacă e listă sau dicționar
        $isList = array_keys($data) === range(0, count($data)-1);

        if ($isList) {
            // listă
            $result = 'l';
            foreach ($data as $value) {
                $result .= bencode($value);
            }
            $result .= 'e';
            return $result;
        } else {
            // dicționar - sortăm cheile lexicografic
            $result = 'd';
            $keys = array_keys($data);
            sort($keys, SORT_STRING);
            foreach ($keys as $key) {
                $result .= bencode($key) . bencode($data[$key]);
            }
            $result .= 'e';
            return $result;
        }
    }

    return '';
}

function generate_info_hash($torrent_path) {
    $content = file_get_contents($torrent_path);
    $decoded = bdecode($content);

    if (!$decoded || !isset($decoded['info'])) {
        return null;
    }

    // Bencodăm înapoi doar secțiunea info
    $bencoded_info = bencode($decoded['info']);

    // Aplicăm sha1 pe conținutul bencode al "info"
    return sha1($bencoded_info);
}


// Funcție pentru calcularea dimensiunii totale a fișierelor din fișierul .torrent
function calculate_total_size($torrent_path) {
    $content = file_get_contents($torrent_path);
    $decoded = bdecode($content); // Decodăm fișierul .torrent
    if (!$decoded || !isset($decoded['info'])) {
        return 0;
    }
    $info = $decoded['info'];

    // Dacă este un fișier single
    if (isset($info['length'])) {
        return $info['length'];
    }

    // Dacă este un torrent multi-file
    if (isset($info['files'])) {
        $total_size = 0;
        foreach ($info['files'] as $file) {
            $total_size += $file['length'];
        }
        return $total_size;
    }

    return 0;
}

// Adăugăm funcția bdecode
function bdecode($string) {
    $position = 0;
    return bdecode_element($string, $position);
}

function bdecode_element($string, &$position) {
    if ($string[$position] === 'i') {
        $position++;
        $end = strpos($string, 'e', $position);
        $number = substr($string, $position, $end - $position);
        $position = $end + 1;
        return intval($number);
    } elseif ($string[$position] === 'l') {
        $list = [];
        $position++;
        while ($string[$position] !== 'e') {
            $list[] = bdecode_element($string, $position);
        }
        $position++;
        return $list;
    } elseif ($string[$position] === 'd') {
        $dict = [];
        $position++;
        while ($string[$position] !== 'e') {
            $key = bdecode_element($string, $position);
            $value = bdecode_element($string, $position);
            $dict[$key] = $value;
        }
        $position++;
        return $dict;
    } elseif (is_numeric($string[$position])) {
        $colon = strpos($string, ':', $position);
        $length = intval(substr($string, $position, $colon - $position));
        $position = $colon + 1 + $length;
        return substr($string, $colon + 1, $length);
    }
    return null;
}

// Verificăm dacă datele au fost trimise prin formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_name = $_FILES['file']['name'] ?? null;
    if (!$file_name) {
        die("Fișierul torrent este obligatoriu.");
    }

    $name = pathinfo($file_name, PATHINFO_FILENAME);
    $slug = strtolower(str_replace(' ', '-', $name));
    $poster = trim($_POST['poster'] ?? '');
    $mediainfo = trim($_POST['mediainfo'] ?? '');
    $descr = trim($_POST['descr'] ?? '');
    $category_id = intval($_POST['type'] ?? 0);
    $imdb_url = trim($_POST['url'] ?? '');

    // Salvează fișierul încărcat
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/files/torrents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp = $_FILES['file']['tmp_name'];
    $file_path = $upload_dir . basename($file_name); // Definește $file_path corect aici
if (!move_uploaded_file($file_tmp, $file_path)) {
    log_error("Eroare la salvarea fișierului uploadat: $file_name");
    die("Eroare la salvarea fișierului uploadat.");
}

// Verifică dacă fișierul conține cuvintele-cheie
if (contains_keywords($file_path, $keywords)) {
    $category_id = 22; // Setează categoria la 22 dacă cuvintele-cheie sunt găsite
}

    // Generăm info_hash
    $info_hash = generate_info_hash($file_path);
if (!$info_hash) {
    log_error("Eroare la generarea info_hash din fișierul torrent: $file_name");
    die("Eroare la generarea info_hash din fișierul torrent.");
}


    // Calculăm dimensiunea totală
    $total_size = calculate_total_size($file_path);
if ($total_size === 0) {
    log_error("Eroare: nu s-a putut calcula dimensiunea fișierelor din torrent: $file_name");
    die("Eroare: nu s-a putut calcula dimensiunea fișierelor din torrent.");
}


    // Setăm coloana free dacă dimensiunea > 4GB
    $free = ($total_size > (5 * 1024 * 1024 * 1024)) ? 1 : 0;

    // Pregătirea interogării SQL
    $stmt = $conn->prepare("INSERT INTO torrents (name, poster, description, imdb_url, mediainfo, file_name, category_id, slug, info_hash, size, owner, created_at, free) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?)");
if (!$stmt) {
    log_error("Eroare la pregătirea interogării SQL: " . $conn->error);
    die("Eroare la pregătirea interogării SQL.");
}


    $stmt->bind_param('ssssssissii', $name, $poster, $descr, $imdb_url, $mediainfo, $file_name, $category_id, $slug, $info_hash, $total_size, $free);

    // După ce fișierul a fost uploadat cu succes:
    if ($stmt->execute()) {
      
        // Obține ID-ul torrentului inserat
        $torrent_id = $conn->insert_id;

        // Rulăm comanda php artisan pentru update IMDB
try {
    $artisan_path = '/var/www/html/lastfiles/artisan';
    $command = "php $artisan_path torrents:update-imdb --id=" . escapeshellarg($torrent_id);
    $output = [];
    $return_var = 0;

    // Executăm comanda
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        log_error("Comanda Artisan a fost executată cu succes pentru torrentul cu ID-ul: $torrent_id.");
    } else {
        $error_message = "Eroare la actualizarea IMDB pentru torrentul cu ID-ul: $torrent_id. Output: " . implode("\n", $output);
        log_error($error_message);
        echo "Eroare la actualizarea IMDB pentru torrentul cu ID-ul: $torrent_id.<br>";
    }
} catch (Exception $e) {
    $error_message = "Eroare la rularea comenzii Artisan pentru torrentul cu ID-ul: $torrent_id. Mesaj: " . $e->getMessage();
    log_error($error_message);
    echo "Eroare la rularea comenzii Artisan.<br>";
}


        // Descarcă fișierul torrent
        try {
            $torrent_content = download_torrent_file($file_name);

            // Salvează fișierul local
            $save_path = $_SERVER['DOCUMENT_ROOT'] . '/files/torrents_downloaded/';
            if (!is_dir($save_path)) {
                mkdir($save_path, 0777, true);
            }

            $save_file = $save_path . $file_name;
            file_put_contents($save_file, $torrent_content);

            // Setează anteturile pentru descărcare directă
            header('Content-Description: File Transfer');
            header('Content-Type: application/x-bittorrent');
            header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($torrent_content));

            // Trimiterea conținutului fișierului către client
           log_error("Fișierul $file_name a fost încărcat și procesat cu succes.");

            exit;
        } catch (Exception $e) {
            echo "Eroare: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Eroare la încărcarea torrentului: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Upload Torrent</title>
</head>
<body>
    <h1>Upload Torrent</h1>
    <form action="takeupload.php" method="post" enctype="multipart/form-data">
        <label for="poster">Poster:</label>
        <input type="text" name="poster" id="poster"><br><br>

        <label for="mediainfo">MediaInfo:</label>
        <textarea name="mediainfo" id="mediainfo" required></textarea><br><br>

        <label for="descr">Descriere:</label>
        <textarea name="descr" id="descr" required></textarea><br><br>

        <label for="type">Categorie:</label>
        <input type="number" name="type" id="type" required><br><br>

        <label for="url">IMDB URL:</label>
        <input type="text" name="url" id="url"><br><br>

        <label for="file">Fișier torrent:</label>
        <input type="file" name="file" id="file" required><br><br>

        <button type="submit">Incarca</button>
    </form>
</body>
</html>
