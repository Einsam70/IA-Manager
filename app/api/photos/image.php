<?php

require_once '../../services/db.php';

$pdo = getDB();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID requerido']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        a.display_name AS album_name,
        a.folder_slug
    FROM photos p
    JOIN photo_albums a ON a.id = p.album_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$photo) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'Fotografia no encontrada']);
    exit;
}

$tagStmt = $pdo->prepare("
    SELECT t.name
    FROM photo_tags t
    JOIN photo_image_tags pit ON pit.tag_id = t.id
    WHERE pit.photo_id = ?
    ORDER BY t.name
");
$tagStmt->execute([$id]);
$photo['tags'] = $tagStmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode($photo, JSON_INVALID_UTF8_SUBSTITUTE);
