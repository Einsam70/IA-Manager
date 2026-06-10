<?php

require_once '../../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT
        a.id,
        a.display_name,
        a.folder_slug,
        a.filename_prefix,
        COUNT(p.id) AS total
    FROM photo_albums a
    LEFT JOIN photos p ON p.album_id = a.id
    GROUP BY a.id
    ORDER BY a.display_name ASC
");

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
