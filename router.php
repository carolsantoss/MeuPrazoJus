<?php
/**
 * Router for PHP Built-in Server
 * Usage: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

$phpFile = $file . '.php';
if (file_exists($phpFile) && !is_dir($phpFile)) {
    include $phpFile;
    exit;
}

if (is_dir($file)) {
    $indexFile = rtrim($file, '/') . '/index.php';
    if (file_exists($indexFile)) {
        include $indexFile;
        exit;
    }
}

return false;
