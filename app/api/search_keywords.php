<?php

require_once '../services/db.php';

$pdo = getDB();

$keywords = $_GET['keywords'] ?? [];

if (!is_array($keywords)) {
    $keywords = explode(',', $keywords);
}

$placeholders = implode(',', array_fill(0, count($keywords), '?'));

$sql = "
SELECT DISTINCT i.id, i.filename, i.model, i.path
FROM images i
JOIN image_keywords ik ON i.id = ik.image_id
JOIN keywords k ON k.id = ik.keyword_id
WHERE k.word IN ($placeholders)
";

$stmt = $pdo->prepare($sql);
$stmt->execute($keywords);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));