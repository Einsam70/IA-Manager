<?php

require_once __DIR__ . '/../services/db.php';

$aiImageCount = 0;
$photoCount = 0;

try {
    $pdo = getDB();
    $aiImageCount = (int)$pdo->query("SELECT COUNT(*) FROM images")->fetchColumn();
    $photoCount = (int)$pdo->query("SELECT COUNT(*) FROM photos")->fetchColumn();
} catch (Throwable $e) {
    // La portada sigue disponible aunque la base no este inicializada.
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="theme.css">
    <title>IA Manager</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f8;
            color: #1f2933;
        }

        main {
            width: min(920px, calc(100% - 32px));
        }

        h1 {
            margin: 0 0 8px;
            font-size: 32px;
            font-weight: 700;
        }

        .subtitle {
            margin: 0 0 28px;
            color: #52606d;
            font-size: 16px;
        }

        .options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .option {
            display: block;
            min-height: 190px;
            padding: 22px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: white;
            color: inherit;
            text-decoration: none;
            box-sizing: border-box;
        }

        .option:hover {
            border-color: #8aa4bf;
            box-shadow: 0 8px 20px rgba(31, 41, 51, 0.08);
        }

        .option h2 {
            margin: 0 0 10px;
            font-size: 22px;
        }

        .optionHeader {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .imageCount {
            flex: 0 0 auto;
            padding: 4px 8px;
            border: 1px solid var(--border, #d9e2ec);
            border-radius: 4px;
            background: var(--surface-soft, #f8fafc);
            color: var(--text, #1f2933);
            font-size: 13px;
            font-weight: bold;
        }

        .option p {
            margin: 0;
            color: #52606d;
            line-height: 1.45;
        }
    </style>
</head>
<body>
    <main>
        <h1>IA Manager</h1>
        <p class="subtitle">Selecciona el espacio de trabajo.</p>

        <div class="options">
            <a class="option" href="ai.php">
                <div class="optionHeader">
                    <h2>Imágenes IA</h2>
                    <span class="imageCount"><?= number_format($aiImageCount, 0, ',', '.') ?></span>
                </div>
                <p>Organiza generaciones por modelo, prompt, metadatos técnicos, keywords y tags propios.</p>
            </a>

            <a class="option" href="photos.php">
                <div class="optionHeader">
                    <h2>Fotografías personales</h2>
                    <span class="imageCount"><?= number_format($photoCount, 0, ',', '.') ?></span>
                </div>
                <p>Prepara álbumes de fotos personales con fecha original, comentarios, datos EXIF y tags separados.</p>
            </a>
        </div>
    </main>
    <script src="theme.js"></script>
</body>
</html>
