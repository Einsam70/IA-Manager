<?php

require_once '../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT
        a.id,
        a.display_name,
        a.filename_prefix,
        COUNT(i.id) AS total,
        COALESCE(MAX(i.album_sequence), 0) + 1 AS next_sequence
    FROM ai_albums a
    LEFT JOIN images i ON i.album_id = a.id
    GROUP BY a.id
    ORDER BY a.display_name ASC
");

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_INVALID_UTF8_SUBSTITUTE);
