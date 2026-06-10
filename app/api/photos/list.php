<?php

require_once '../../services/db.php';

$pdo = getDB();

$page = max(1, intval($_GET['page'] ?? 1));
$limit = max(1, min(20, intval($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$albumId = intval($_GET['album_id'] ?? 0);
$tagId = intval($_GET['tag_id'] ?? 0);

$params = [];
$where = "WHERE 1=1";

if ($albumId > 0) {
    $where .= " AND p.album_id = ?";
    $params[] = $albumId;
}

if ($tagId > 0) {
    $where .= "
        AND EXISTS (
            SELECT 1
            FROM photo_image_tags pit
            WHERE pit.photo_id = p.id
              AND pit.tag_id = ?
        )
    ";
    $params[] = $tagId;
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM photos p
    JOIN photo_albums a ON a.id = p.album_id
    $where
");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$idsStmt = $pdo->prepare("
    SELECT p.id
    FROM photos p
    JOIN photo_albums a ON a.id = p.album_id
    $where
    ORDER BY COALESCE(p.taken_at, p.imported_at) DESC, p.id DESC
");
$idsStmt->execute($params);
$allIds = array_map('intval', $idsStmt->fetchAll(PDO::FETCH_COLUMN));

$stmt = $pdo->prepare("
    SELECT
        p.id,
        p.filename,
        p.path,
        p.taken_at,
        p.width,
        p.height,
        a.display_name AS album_name,
        a.folder_slug
    FROM photos p
    JOIN photo_albums a ON a.id = p.album_id
    $where
    ORDER BY COALESCE(p.taken_at, p.imported_at) DESC, p.id DESC
    LIMIT ? OFFSET ?
");

$dataParams = $params;
$dataParams[] = $limit;
$dataParams[] = $offset;

$stmt->execute($dataParams);

header('Content-Type: application/json');

echo json_encode([
    'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    'total' => $total,
    'allIds' => $allIds
], JSON_INVALID_UTF8_SUBSTITUTE);
