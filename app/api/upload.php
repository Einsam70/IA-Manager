<?php

require_once '../services/db.php';
require_once '../services/python.php';

$pdo = getDB();

$tempDir = __DIR__ . '/../incoming/ai/';

if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

function getUniqueFilename($dir, $filename) {
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

function normalizeText($value) {
    $value = trim((string)$value);
    $value = preg_replace('/\s+/', ' ', $value);
    return $value;
}

function makePrefix($value) {
    $value = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value));
    $value = preg_replace('/[^A-Z0-9]/', '', $value);
    return substr($value !== '' ? $value : 'IMG', 0, 8);
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

function getOrCreateAlbum(PDO $pdo, $albumId, $albumName, $albumPrefix) {
    $albumId = intval($albumId);

    if ($albumId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM ai_albums WHERE id = ?");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($album) {
            return $album;
        }
    }

    $albumName = normalizeText($albumName);

    if ($albumName === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM ai_albums WHERE display_name = ?");
    $stmt->execute([$albumName]);
    $album = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($album) {
        return $album;
    }

    $albumPrefix = makePrefix($albumPrefix !== '' ? $albumPrefix : $albumName);

    $stmt = $pdo->prepare("SELECT * FROM ai_albums WHERE filename_prefix = ?");
    $stmt->execute([$albumPrefix]);
    $prefixAlbum = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prefixAlbum) {
        throw new RuntimeException(
            'El prefijo "' . $albumPrefix . '" ya pertenece al álbum "' . $prefixAlbum['display_name'] . '".'
        );
    }

    $stmt = $pdo->prepare("
        INSERT INTO ai_albums(display_name, filename_prefix)
        VALUES(?, ?)
    ");
    $stmt->execute([$albumName, $albumPrefix]);

    $stmt = $pdo->prepare("SELECT * FROM ai_albums WHERE id = ?");
    $stmt->execute([$pdo->lastInsertId()]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function nextAlbumSequence(PDO $pdo, $albumId) {
    if (!$albumId) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT COALESCE(MAX(album_sequence), 0) + 1 FROM images WHERE album_id = ?");
    $stmt->execute([$albumId]);
    return (int)$stmt->fetchColumn();
}

function attachTags(PDO $pdo, $imageId, array $tags) {
    foreach ($tags as $tag) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO tags(name) VALUES(?)");
        $stmt->execute([$tag]);

        $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
        $stmt->execute([$tag]);
        $tagId = (int)$stmt->fetchColumn();

        if ($tagId > 0) {
            $stmt = $pdo->prepare("
                INSERT OR IGNORE INTO image_tags(image_id, tag_id)
                VALUES(?, ?)
            ");
            $stmt->execute([$imageId, $tagId]);
        }
    }
}

header('Content-Type: application/json');

if (empty($_FILES['files']['name'])) {
    echo json_encode(['processed' => 0, 'album' => null, 'skipped' => [['reason' => 'No files']]]);
    exit;
}

try {
    $album = getOrCreateAlbum(
        $pdo,
        $_POST['aiAlbumId'] ?? 0,
        $_POST['aiAlbumName'] ?? '',
        $_POST['aiAlbumPrefix'] ?? ''
    );
} catch (RuntimeException $e) {
    http_response_code(409);
    echo json_encode([
        'processed' => 0,
        'album' => null,
        'error' => $e->getMessage(),
        'skipped' => []
    ], JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
$albumSequence = $album ? nextAlbumSequence($pdo, $album['id']) : null;
$uploadTags = parseTags($_POST['uploadTags'] ?? ($_POST['uploadTag'] ?? ''));
$total = count($_FILES['files']['name']);
$processed = 0;
$skipped = [];

for ($i = 0; $i < $total; $i++) {
    $tmpName = $_FILES['files']['tmp_name'][$i];
    $originalName = basename($_FILES['files']['name'][$i]);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['png', 'jpg', 'jpeg'];

    if (!in_array($ext, $allowed, true)) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Unsupported format'];
        continue;
    }

    $tempName = getUniqueFilename($tempDir, $originalName);
    $destination = $tempDir . $tempName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Upload move failed'];
        continue;
    }

    $pythonScript = realpath(__DIR__ . '/../python/processor.py');
    $imagePath = realpath($destination);
    $pythonCommand = getPythonCommand();

    if ($pythonCommand === null || $pythonScript === false || $imagePath === false) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Python environment not found'];
        continue;
    }

    $generatedBaseName = null;

    if ($album && $albumSequence !== null) {
        $generatedBaseName = $album['filename_prefix'] . '_' . str_pad((string)$albumSequence, 6, '0', STR_PAD_LEFT);
    }

    $command = $pythonCommand . ' '
        . escapeshellarg($pythonScript) . ' '
        . escapeshellarg($imagePath) . ' '
        . escapeshellarg($originalName);

    if ($generatedBaseName !== null) {
        $command .= ' ' . escapeshellarg($generatedBaseName);
    }

    $command .= ' 2>&1';
    $output = shell_exec($command);

    if (file_exists($destination)) {
        unlink($destination);
    }

    $output = trim((string)$output);

    if ($output === '') {
        $skipped[] = ['file' => $originalName, 'reason' => 'Processor returned empty output'];
        continue;
    }

    $data = explode("|", $output);

    if (count($data) < 15) {
        $skipped[] = ['file' => $originalName, 'reason' => 'Processor failed', 'output' => $output];
        continue;
    }

    list(
        $filename,
        $model,
        $prompt,
        $negative,
        $sampler,
        $scheduleType,
        $steps,
        $cfg,
        $seed,
        $path,
        $keywordsStr,
        $createdAt,
        $width,
        $height,
        $filesize
    ) = $data;

    $stmt = $pdo->prepare("
        INSERT INTO images (
            filename,
            model,
            prompt,
            negative_prompt,
            sampler,
            schedule_type,
            steps,
            cfg,
            seed,
            path,
            created_at,
            width,
            height,
            filesize,
            album_id,
            original_filename,
            album_sequence
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $filename,
        $model,
        $prompt,
        $negative,
        $sampler,
        $scheduleType,
        $steps,
        $cfg,
        $seed,
        $path,
        $createdAt,
        $width,
        $height,
        $filesize,
        $album['id'] ?? null,
        $originalName,
        $albumSequence
    ]);

    $imageId = (int)$pdo->lastInsertId();
    attachTags($pdo, $imageId, $uploadTags);

    $keywords = explode(',', $keywordsStr);

    foreach ($keywords as $word) {
        $word = trim($word);

        if ($word === '') {
            continue;
        }

        $stmt = $pdo->prepare("INSERT OR IGNORE INTO keywords(word) VALUES(?)");
        $stmt->execute([$word]);

        $stmt = $pdo->prepare("SELECT id FROM keywords WHERE word = ?");
        $stmt->execute([$word]);
        $keywordId = (int)$stmt->fetchColumn();

        if ($keywordId > 0) {
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO image_keywords(image_id, keyword_id) VALUES(?, ?)");
            $stmt->execute([$imageId, $keywordId]);
        }
    }

    $processed++;

    if ($albumSequence !== null) {
        $albumSequence++;
    }
}

echo json_encode([
    'processed' => $processed,
    'album' => $album ? [
        'id' => (int)$album['id'],
        'display_name' => $album['display_name'],
        'filename_prefix' => $album['filename_prefix']
    ] : null,
    'skipped' => $skipped
], JSON_INVALID_UTF8_SUBSTITUTE);
