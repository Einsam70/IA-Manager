<?php

require_once '../services/db.php';

$pdo = getDB();

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT name
        FROM tags
        WHERE LOWER(name) LIKE LOWER(?)
        ORDER BY name
        LIMIT 10
    ");
    $stmt->execute(['%' . $q . '%']);
} else {
    $stmt = $pdo->query("
        SELECT name
        FROM tags
        ORDER BY name
        LIMIT 10
    ");
}

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));