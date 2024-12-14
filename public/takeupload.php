<?php
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
    die("Conexiunea la baza de date a eșuat: " . $conn->connect_error);
}

// Funcție pentru generarea info_hash din fișierul .torrent
function generate_info_hash($torrent_path) {
    $content = file_get_contents($torrent_path);
    $start = strpos($content, "4:info");
    if ($start === false) {
        return null;
    }
    $info = substr($content, $start + 6);
    $info_end = strrpos($info, "e");
    if ($info_end === false) {
        return null;
    }
    $info = substr($info, 0, $info_end + 1);
    return sha1($info);
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
    $file_path = $upload_dir . basename($file_name);
    if (!move_uploaded_file($file_tmp, $file_path)) {
        die("Eroare la salvarea fișierului uploadat.");
    }

    // Generăm info_hash
    $info_hash = generate_info_hash($file_path);
    if (!$info_hash) {
        die("Eroare la generarea info_hash din fișierul torrent.");
    }

    // Calculăm dimensiunea totală
    $total_size = calculate_total_size($file_path);
    if ($total_size === 0) {
        die("Eroare: nu s-a putut calcula dimensiunea fișierelor din torrent.");
    }

    // Pregătirea interogării SQL
    $stmt = $conn->prepare("INSERT INTO torrents (name, poster, description, imdb_url, mediainfo, file_name, category_id, slug, info_hash, size, owner, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
    if (!$stmt) {
        die("Eroare la pregătirea interogării: " . $conn->error);
    }

    $stmt->bind_param('ssssssissi', $name, $poster, $descr, $imdb_url, $mediainfo, $file_name, $category_id, $slug, $info_hash, $total_size);

    if ($stmt->execute()) {
        echo "Torrentul a fost încărcat cu succes! Dimensiunea totală: " . round($total_size / (1024 ** 3), 2) . " GB.";
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

        <button type="submit">Încarcă</button>
    </form>
</body>
</html>
