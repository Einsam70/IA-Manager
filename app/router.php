<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicDir = realpath(__DIR__ . '/public');

if ($publicDir === false) {
    http_response_code(500);
    return true;
}

if ($path === '/' || $path === '/index.php') {
    require $publicDir . '/index.php';
    return true;
}

if (str_starts_with($path, '/api/')) {
    $apiFile = realpath(__DIR__ . $path);
    $apiDir = realpath(__DIR__ . '/api');

    if ($apiFile === false || $apiDir === false || !str_starts_with($apiFile, $apiDir . DIRECTORY_SEPARATOR)) {
        http_response_code(404);
        return true;
    }

    chdir(dirname($apiFile));
    require $apiFile;
    return true;
}

$staticFile = realpath($publicDir . $path);

if ($staticFile !== false && str_starts_with($staticFile, $publicDir . DIRECTORY_SEPARATOR) && is_file($staticFile)) {
    return false;
}

http_response_code(404);
return true;
