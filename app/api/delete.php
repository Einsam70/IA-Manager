<?php

require_once '../services/db.php';

$pdo = getDB();

$id = $_POST['id'] ?? null;

if (!$id) {
    echo "ID requerido";
    exit;
}

// obtener datos
$stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?");
$stmt->execute([$id]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
    echo "No encontrado";
    exit;
}

// borrar archivo físico
$fullPath = __DIR__ . '/../public' . $image['path'];
if (file_exists($fullPath)) {
    unlink($fullPath);
}

// borrar de DB
$stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
$stmt->execute([$id]);

echo "Eliminado";