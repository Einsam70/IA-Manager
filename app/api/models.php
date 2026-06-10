<?php

require_once '../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("SELECT DISTINCT model FROM images ORDER BY model ASC");
$models = $stmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode($models);