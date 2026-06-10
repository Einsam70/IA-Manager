<?php

require_once '../services/db.php';
$pdo = getDB();

$q = trim($_GET['q'] ?? '');
$model = trim($_GET['model'] ?? '');
$albumId = intval($_GET['album_id'] ?? 0);
$keywords = trim($_GET['keywords'] ?? '');
$tags = trim($_GET['tags'] ?? '');

$page = max(1, intval($_GET['page'] ?? 1));
$limit = max(1, intval($_GET['limit'] ?? 20));
$offset = ($page - 1) * $limit;
$sort = $_GET['sort'] ?? 'id_desc';

$params = [];

$baseSql = "
FROM images i
WHERE 1=1
";

// 🔍 texto
if ($q !== '') {
    $baseSql .= " AND i.prompt LIKE ?";
    $params[] = '%' . $q . '%';
}

// 🧠 modelo
if ($model !== '') {
    $baseSql .= " AND i.model = ?";
    $params[] = $model;
}

if ($albumId > 0) {
    $baseSql .= " AND i.album_id = ?";
    $params[] = $albumId;
}

// 🏷️ keywords (AND)
if ($keywords !== '') {
    $kwArray = array_filter(array_map('trim', explode(',', $keywords)));

    if (count($kwArray) > 0) {

        $conditions = [];

        foreach ($kwArray as $kw) {
            $conditions[] = "EXISTS (
                SELECT 1
                FROM image_keywords ik2
                JOIN keywords k2 ON k2.id = ik2.keyword_id
                WHERE ik2.image_id = i.id
                AND TRIM(LOWER(k2.word)) = TRIM(LOWER(?))
            )";

            $params[] = $kw;
        }

        $baseSql .= " AND " . implode(" AND ", $conditions);
    }
}

if ($tags !== '') {
    $tagArray = array_filter(array_map('trim', explode(',', $tags)));

    if (count($tagArray) > 0) {

        $conditions = [];

        foreach ($tagArray as $tag) {
            $conditions[] = "EXISTS (
                SELECT 1
                FROM image_tags it2
                JOIN tags t2 ON t2.id = it2.tag_id
                WHERE it2.image_id = i.id
                AND TRIM(LOWER(t2.name)) = TRIM(LOWER(?))
            )";

            $params[] = $tag;
        }

        $baseSql .= " AND " . implode(" AND ", $conditions);
    }
}

// ==============================
// TOTAL (con filtros)
// ==============================

$countSql = "SELECT COUNT(DISTINCT i.id) " . $baseSql;

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$total = (int)$countStmt->fetchColumn();

// Modificacion hecha para navegar por todas las imagenes

$orderBy = "i.id DESC";

switch ($sort) {
    case 'date_desc':
        $orderBy = "i.created_at DESC, i.id DESC";
        break;

    case 'date_asc':
        $orderBy = "i.created_at ASC, i.id ASC";
        break;

    case 'size_desc':
        $orderBy = "i.filesize DESC, i.id DESC";
        break;

    case 'size_asc':
        $orderBy = "i.filesize ASC, i.id ASC";
        break;

    case 'resolution_desc':
        $orderBy = "(i.width * i.height) DESC, i.id DESC";
        break;

    case 'resolution_asc':
        $orderBy = "(i.width * i.height) ASC, i.id ASC";
        break;

    case 'id_asc':
        $orderBy = "i.id ASC";
        break;

    case 'id_desc':
    default:
        $orderBy = "i.id DESC";
        break;
}

$allIdsSql = "
SELECT DISTINCT i.id
" . $baseSql . "
ORDER BY $orderBy
";

$allIdsStmt = $pdo->prepare($allIdsSql);
$allIdsStmt->execute($params);

$allIds = $allIdsStmt->fetchAll(PDO::FETCH_COLUMN);

// ==============================
// RESULTADOS PAGINADOS
// ==============================

$dataSql = "
SELECT DISTINCT i.id, i.filename, i.model, i.path
" . $baseSql . "
ORDER BY $orderBy
LIMIT ? OFFSET ?
";

$dataParams = $params;
$dataParams[] = $limit;
$dataParams[] = $offset;

$stmt = $pdo->prepare($dataSql);
$stmt->execute($dataParams);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==============================

header('Content-Type: application/json');

echo json_encode([
    'items' => $items,
    'total' => $total,
    'allIds' => $allIds
]);
