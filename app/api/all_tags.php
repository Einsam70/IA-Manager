<?php

require_once '../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT name
    FROM tags
    ORDER BY name ASC
");

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));