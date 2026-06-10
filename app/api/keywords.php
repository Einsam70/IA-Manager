<?php

// TODAS LAS PALABRAS POR ORDEN ALFABETICO
/* require_once '../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("SELECT word FROM keywords ORDER BY word ASC LIMIT 500");

echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN)); */

//TODAS LAS PALABRAS ORDENADAS POR FRECUENCIA
/* require_once '../services/db.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT 
        k.word,
        COUNT(ik.image_id) AS total
    FROM keywords k
    LEFT JOIN image_keywords ik ON k.id = ik.keyword_id
    GROUP BY k.id
    ORDER BY total DESC, k.word ASC
    LIMIT 500
");

$keywords = $stmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode($keywords); */

//ORDENADAS POR FRECUENCIA PERO SIN PALABRAS RUIDO
/* require_once '../services/db.php';

$pdo = getDB();

// keywords que NO queremos mostrar en filtros
$excluded = [
    'masterpiece',
    'best quality',
    'high quality',
    'ultra detailed',
    'photorealistic',
    'high resolution',
    'sharp focus',
    'amazing depth'
];

// placeholders para PDO
$placeholders = implode(',', array_fill(0, count($excluded), '?'));

$sql = "
    SELECT 
        k.word,
        COUNT(ik.image_id) AS total
    FROM keywords k
    LEFT JOIN image_keywords ik ON k.id = ik.keyword_id
    WHERE TRIM(LOWER(k.word)) NOT IN ($placeholders)
    GROUP BY k.id
    ORDER BY total DESC, k.word ASC
    LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_map('strtolower', $excluded));

$keywords = $stmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode($keywords); */

//ORDENADAS ALFABETICAMENTE MOSTRANDO EL TOTAL
require_once '../services/db.php';

$pdo = getDB();

$excluded = [
    'masterpiece',
    'best quality',
    'high quality',
    'ultra detailed',
    'photorealistic',
    'high resolution',
    'sharp focus',
    'amazing depth'
];

$placeholders = implode(',', array_fill(0, count($excluded), '?'));

$sql = "
    SELECT 
        k.word,
        COUNT(ik.image_id) AS total
    FROM keywords k
    LEFT JOIN image_keywords ik ON k.id = ik.keyword_id
    WHERE TRIM(LOWER(k.word)) NOT IN ($placeholders)
    GROUP BY k.id
    ORDER BY total DESC, k.word ASC
    LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_map('strtolower', $excluded));

$keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($keywords);