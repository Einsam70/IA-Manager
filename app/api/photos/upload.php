<?php

require_once '../../services/db.php';
require_once '../../services/python.php';

$pdo = getDB();

header('Content-Type: application/json');

function normalizeText($value) {
    $value = trim((string)$value);
    $value = preg_replace('/\s+/', ' ', $value);
    return $value;
}

function makeSlug($value) {
    $value = normalizeText($value);
    $value = mb_strtolower($value, 'UTF-8');
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim($value, '-');
    return $value !== '' ? $value : 'album';
}

function makePrefix($value) {
    $value = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value));
    $value = preg_replace('/[^A-Z0-9]/', '', $value);
    return substr($value !== '' ? $value : 'FOTO', 0, 6);
}

function uniqueSlug(PDO $pdo, $baseSlug) {
    $slug = $baseSlug;
    $n = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM photo_albums WHERE folder_slug = ?");

    while (true) {
        $stmt->execute([$slug]);

        if ((int)$stmt->fetchColumn() === 0) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $n;
        $n++;
    }
}

function getOrCreateAlbum(PDO $pdo, $albumName, $prefix) {
    $stmt = $pdo->prepare("SELECT * FROM photo_albums WHERE display_name = ?");
    $stmt->execute([$albumName]);
    $album = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($album) {
        return $album;
    }

    $baseSlug = makeSlug($albumName);
    $slug = uniqueSlug($pdo, $baseSlug);
    $prefix = makePrefix($prefix !== '' ? $prefix : $albumName);

    $stmt = $pdo->prepare("
        INSERT INTO photo_albums(display_name, folder_slug, filename_prefix)
        VALUES(?, ?, ?)
    ");
    $stmt->execute([$albumName, $slug, $prefix]);

    $stmt = $pdo->prepare("SELECT * FROM photo_albums WHERE id = ?");
    $stmt->execute([$pdo->lastInsertId()]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function parseTags($value) {
    $items = preg_split('/[,;\n]+/', (string)$value);
    $tags = [];

    foreach ($items as $item) {
        $tag = normalizeText($item);
        $tag = mb_strtolower($tag, 'UTF-8');

        if ($tag !== '' && !in_array($tag, $tags, true)) {
            $tags[] = $tag;
        }
    }

    return $tags;
}

function attachTags(PDO $pdo, $photoId, array $tags) {
    foreach ($tags as $tag) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO photo_tags(name) VALUES(?)");
        $stmt->execute([$tag]);

        $stmt = $pdo->prepare("SELECT id FROM photo_tags WHERE name = ?");
        $stmt->execute([$tag]);
        $tagId = (int)$stmt->fetchColumn();

        if ($tagId > 0) {
            $stmt = $pdo->prepare("
                INSERT OR IGNORE INTO photo_image_tags(photo_id, tag_id)
                VALUES(?, ?)
            ");
            $stmt->execute([$photoId, $tagId]);
        }
    }
}

function getUniqueTempName($dir, $filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $candidate = $filename;
    $n = 1;

    while (file_exists($dir . $candidate)) {
        $candidate = $base . '_' . $n . '.' . $ext;
        $n++;
    }

    return $candidate;
}

if (empty($_FILES['files']['name'])) {
    echo json_encode(['processed' => 0, 'skipped' => [['reason' => 'No files']]]);
    exit;
}

$albumName = normalizeText($_POST['albumName'] ?? '');

if ($albumName === '') {
    echo json_encode(['processed' => 0, 'skipped' => [['reason' => 'Album name required']]]);
    exit;
}

$albumPrefix = normalizeText($_POST['albumPrefix'] ?? '');
$tags = parseTags($_POST['tags'] ?? '');
$album = getOrCreateAlbum($pdo, $albumName, $albumPrefix);

$tempDir = __DIR__ . '/../../incoming/photos/';

if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

$allowed = ['jpg', 'jpeg', 'png', 'bmp', 'webp', 'tif', 'tiff'];
$total = count($_FILES['files']['name']);
$processed = 0;
$skipped = [];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE album_id = ?");
$stmt->execute([$album['id']]);
$sequence = (int)$stmt->fetchColumn() + 1;

for ($i = 0; $i < $total; $i++) {
    $originalName = basename($_FILES['files']['name'][$i]);
    $tmpName = $_FILES['files']['tmp_name'][$i];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Unsupported format'];
        continue;
    }

    $tempName = getUniqueTempName($tempDir, $originalName);
    $destination = $tempDir . $tempName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Upload move failed'];
        continue;
    }

    $pythonCommand = getPythonCommand();
    $pythonScript = realpath(__DIR__ . '/../../python/photo_processor.py');
    $imagePath = realpath($destination);

    if ($pythonCommand === null || $pythonScript === false || $imagePath === false) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Python environment not found'];
        continue;
    }

    $command = $pythonCommand . ' '
        . escapeshellarg($pythonScript) . ' '
        . escapeshellarg($imagePath) . ' '
        . escapeshellarg($album['folder_slug']) . ' '
        . escapeshellarg($album['filename_prefix']) . ' '
        . escapeshellarg((string)$sequence) . ' '
        . escapeshellarg($originalName) . ' 2>&1';

    $output = trim((string)shell_exec($command));

    if (file_exists($destination)) {
        unlink($destination);
    }

    $data = json_decode($output, true);

    if (!is_array($data)) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Processor failed', 'output' => $output];
        continue;
    }

    $stmt = $pdo->prepare("
        INSERT INTO photos (
            album_id,
            filename,
            original_filename,
            original_extension,
            path,
            taken_at,
            width,
            height,
            filesize,
            camera_make,
            camera_model,
            lens,
            focal_length,
            exposure_time,
            aperture,
            iso,
            gps_lat,
            gps_lng
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $album['id'],
        $data['filename'] ?? '',
        $data['original_filename'] ?? $originalName,
        $data['original_extension'] ?? $ext,
        $data['path'] ?? '',
        $data['taken_at'] ?? null,
        $data['width'] ?? null,
        $data['height'] ?? null,
        $data['filesize'] ?? null,
        $data['camera_make'] ?? '',
        $data['camera_model'] ?? '',
        $data['lens'] ?? '',
        $data['focal_length'] ?? '',
        $data['exposure_time'] ?? '',
        $data['aperture'] ?? '',
        $data['iso'] ?? '',
        $data['gps_lat'] ?? null,
        $data['gps_lng'] ?? null
    ]);

    $photoId = (int)$pdo->lastInsertId();
    attachTags($pdo, $photoId, $tags);

    $processed++;
    $sequence++;
}

echo json_encode([
    'album' => [
        'id' => (int)$album['id'],
        'display_name' => $album['display_name'],
        'folder_slug' => $album['folder_slug'],
        'filename_prefix' => $album['filename_prefix']
    ],
    'processed' => $processed,
    'skipped' => $skipped
]);
