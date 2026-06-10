<?php

require_once '../services/db.php';

$pdo = getDB();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID requerido']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?");
$stmt->execute([$id]);

$image = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($image, JSON_INVALID_UTF8_SUBSTITUTE);
