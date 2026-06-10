<?php

require_once '../services/db.php';

$pdo = getDB();

$imageId = intval($_POST['image_id'] ?? 0);

$tag = trim($_POST['tag'] ?? '');
$tag = preg_replace('/\s+/', ' ', $tag);
$tag = mb_strtolower($tag, 'UTF-8');

if ($imageId <= 0 || $tag === '') {
    exit;
}

// insertar tag si no existe
$stmt = $pdo->prepare("INSERT OR IGNORE INTO tags(name) VALUES(?)");
$stmt->execute([$tag]);

// obtener id del tag
$stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
$stmt->execute([$tag]);

$tagId = (int)$stmt->fetchColumn();

if ($tagId <= 0) {
    exit;
}

// crear relación imagen-tag sin duplicar
$stmt = $pdo->prepare("
    INSERT OR IGNORE INTO image_tags(image_id, tag_id)
    VALUES(?, ?)
");

$stmt->execute([$imageId, $tagId]);

echo 'ok';