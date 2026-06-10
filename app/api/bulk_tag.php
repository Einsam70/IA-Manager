<?php

require_once '../services/db.php';

$pdo = getDB();

$data = json_decode(file_get_contents('php://input'), true);

$ids = $data['ids'] ?? [];
$rawTags = $data['tag'] ?? '';

if (empty($ids) || trim((string)$rawTags) === '') {
    exit;
}

function parseTags($value) {
    $items = preg_split('/[,;\n]+/', (string)$value);
    $tags = [];

    foreach ($items as $item) {
        $tag = trim($item);
        $tag = preg_replace('/\s+/', ' ', $tag);
        $tag = mb_strtolower($tag, 'UTF-8');

        if ($tag !== '' && !in_array($tag, $tags, true)) {
            $tags[] = $tag;
        }
    }

    return $tags;
}

$tags = parseTags($rawTags);

if (!$tags) {
    exit;
}

$insertTagStmt = $pdo->prepare("INSERT OR IGNORE INTO tags(name) VALUES(?)");
$selectTagStmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
$insertRelationStmt = $pdo->prepare("
    INSERT OR IGNORE INTO image_tags(image_id, tag_id)
    VALUES(?, ?)
");

foreach ($tags as $tag) {
    $insertTagStmt->execute([$tag]);
    $selectTagStmt->execute([$tag]);
    $tagId = (int)$selectTagStmt->fetchColumn();

    if ($tagId <= 0) {
        continue;
    }

    foreach ($ids as $imageId) {
        $insertRelationStmt->execute([(int)$imageId, $tagId]);
    }
}

echo 'ok';
