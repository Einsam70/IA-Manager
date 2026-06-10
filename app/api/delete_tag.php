<?php

require_once '../services/db.php';

$pdo = getDB();

$imageId = intval($_POST['image_id'] ?? 0);
$tag = trim($_POST['tag'] ?? '');

if ($imageId <= 0 || $tag === '') {
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM image_tags
    WHERE image_id = ?
    AND tag_id = (
        SELECT id
        FROM tags
        WHERE name = ?
    )
");

$stmt->execute([$imageId, $tag]);

echo 'ok';