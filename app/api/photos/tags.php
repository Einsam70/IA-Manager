<?php

require_once '../../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT
        t.id,
        t.name,
        COUNT(pit.photo_id) AS total
    FROM photo_tags t
    LEFT JOIN photo_image_tags pit ON pit.tag_id = t.id
    GROUP BY t.id, t.name
    ORDER BY t.name ASC
");

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_INVALID_UTF8_SUBSTITUTE);
