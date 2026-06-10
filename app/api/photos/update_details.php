<?php

require_once '../../services/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$id = intval($_POST['id'] ?? 0);
$field = trim((string)($_POST['field'] ?? ''));
$value = trim((string)($_POST['value'] ?? ''));

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID requerido']);
    exit;
}

if (!in_array($field, ['place', 'user_comment'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Campo no valido']);
    exit;
}

$stmt = $pdo->prepare("UPDATE photos SET $field = ? WHERE id = ?");
$stmt->execute([$value !== '' ? $value : null, $id]);

if ($stmt->rowCount() === 0) {
    $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE id = ?");
    $existsStmt->execute([$id]);

    if ((int)$existsStmt->fetchColumn() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Fotografia no encontrada']);
        exit;
    }
}

echo json_encode([
    'ok' => true,
    'field' => $field,
    'value' => $value
], JSON_INVALID_UTF8_SUBSTITUTE);
