<?php

require_once '../services/db.php';

$pdo = getDB();

if (!isset($_GET['ids'])) {
    die("No IDs");
}

$ids = explode(',', $_GET['ids']);

// preparar consulta
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM images WHERE id IN ($placeholders)");
$stmt->execute($ids);

$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// crear zip
$zip = new ZipArchive();
$zipName = "export_" . time() . ".zip";
$zipPath = sys_get_temp_dir() . '/' . $zipName;

if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    die("No se pudo crear ZIP");
}

foreach ($images as $img) {

    // ruta física imagen
    $basePath = realpath(__DIR__ . '/../public');
    $filePath = $basePath . $img['path'];

    if (file_exists($filePath)) {
        $zip->addFile($filePath, basename($filePath));
    } else {
        error_log("No existe: " . $filePath);
    }

    // generar metadata en texto
    $metaContent = "
    Model: {$img['model']}
    Prompt: {$img['prompt']}
    Negative: {$img['negative_prompt']}
    Sampler: {$img['sampler']}
    Schedule type: {$img['schedule_type']}
    Steps: {$img['steps']}
    CFG: {$img['cfg']}
    Seed: {$img['seed']}
    ";

    $metaName = pathinfo($img['filename'], PATHINFO_FILENAME) . ".txt";
    $zip->addFromString($metaName, $metaContent);
}

$zip->close();

// descargar
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
readfile($zipPath);

// borrar temporal
unlink($zipPath);
exit;
