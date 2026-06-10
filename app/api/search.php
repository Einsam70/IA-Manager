<?php

require_once '../services/db.php';

$pdo = getDB();

$keyword = $_GET['q'] ?? '';
$model = $_GET['model'] ?? '';

$sql = "SELECT id, filename, model, path FROM images WHERE 1=1";
$params = [];

// búsqueda por prompt
if (!empty($keyword)) {
    $sql .= " AND prompt LIKE ?";
    $params[] = "%" . $keyword . "%";
}

// filtro por modelo
if (!empty($model)) {
    $sql .= " AND model = ?";
    $params[] = $model;
}

$sql .= " ORDER BY id DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);