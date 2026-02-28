<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($path, '/');

// If the path is empty, serve index.php
if (empty($path)) {
    require 'index.php';
    return true;
}

// If it's a real file (like assets, images), let the built-in server serve it
if (file_exists(__DIR__ . '/' . $path)) {
    return false;
}

// If the file with .php exists, serve it (for clean URLs like /login -> login.php)
if (file_exists(__DIR__ . '/' . $path . '.php')) {
    require __DIR__ . '/' . $path . '.php';
    return true;
}

// Otherwise return 404 header and let it handle naturally
header("HTTP/1.0 404 Not Found");
echo "404 Not Found.";
return true;
