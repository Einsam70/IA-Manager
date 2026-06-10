<?php

$dbPath = __DIR__ . '/../db/database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT,
            model TEXT,
            prompt TEXT,
            negative_prompt TEXT,
            sampler TEXT,
            schedule_type TEXT,
            steps INTEGER,
            cfg REAL,
            seed TEXT,
            path TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            width INTEGER,
            height INTEGER,
            filesize INTEGER,
            album_id INTEGER,
            original_filename TEXT,
            album_sequence INTEGER
        );
    ");

    $columns = $pdo->query("PRAGMA table_info(images)")
        ->fetchAll(PDO::FETCH_COLUMN, 1);

    $missingColumns = [
        'width' => 'INTEGER',
        'height' => 'INTEGER',
        'filesize' => 'INTEGER',
        'album_id' => 'INTEGER',
        'original_filename' => 'TEXT',
        'album_sequence' => 'INTEGER'
    ];

    foreach ($missingColumns as $column => $type) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec("ALTER TABLE images ADD COLUMN $column $type");
        }
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS keywords (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            word TEXT NOT NULL UNIQUE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS image_keywords (
            image_id INTEGER NOT NULL,
            keyword_id INTEGER NOT NULL,
            UNIQUE(image_id, keyword_id),
            FOREIGN KEY(image_id) REFERENCES images(id) ON DELETE CASCADE,
            FOREIGN KEY(keyword_id) REFERENCES keywords(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS image_tags (
            image_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            UNIQUE(image_id, tag_id),
            FOREIGN KEY(image_id) REFERENCES images(id) ON DELETE CASCADE,
            FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ai_albums (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            display_name TEXT NOT NULL UNIQUE,
            filename_prefix TEXT NOT NULL UNIQUE,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS photo_albums (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            display_name TEXT NOT NULL,
            folder_slug TEXT NOT NULL UNIQUE,
            filename_prefix TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS photos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            album_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            original_filename TEXT,
            original_extension TEXT,
            path TEXT NOT NULL,
            taken_at TEXT,
            imported_at TEXT DEFAULT CURRENT_TIMESTAMP,
            width INTEGER,
            height INTEGER,
            filesize INTEGER,
            camera_make TEXT,
            camera_model TEXT,
            lens TEXT,
            focal_length TEXT,
            exposure_time TEXT,
            aperture TEXT,
            iso TEXT,
            gps_lat REAL,
            gps_lng REAL,
            place TEXT,
            user_comment TEXT,
            FOREIGN KEY(album_id) REFERENCES photo_albums(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS photo_tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS photo_image_tags (
            photo_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            UNIQUE(photo_id, tag_id),
            FOREIGN KEY(photo_id) REFERENCES photos(id) ON DELETE CASCADE,
            FOREIGN KEY(tag_id) REFERENCES photo_tags(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_images_model ON images(model)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_images_created_at ON images(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_images_album ON images(album_id)");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_ai_albums_prefix_unique ON ai_albums(filename_prefix)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_image_keywords_image ON image_keywords(image_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_image_keywords_keyword ON image_keywords(keyword_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_image_tags_image ON image_tags(image_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_image_tags_tag ON image_tags(tag_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_photos_album ON photos(album_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_photos_taken_at ON photos(taken_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_photos_imported_at ON photos(imported_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_photo_image_tags_photo ON photo_image_tags(photo_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_photo_image_tags_tag ON photo_image_tags(tag_id)");

    echo "Base de datos inicializada correctamente.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
