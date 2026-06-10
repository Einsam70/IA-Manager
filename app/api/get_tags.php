<?php

require_once '../services/db.php';

$pdo = getDB();

$imageId = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT t.name
    FROM tags t
    JOIN image_tags it ON t.id = it.tag_id
    WHERE it.image_id = ?
    ORDER BY t.name
");

$stmt->execute([$imageId]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));