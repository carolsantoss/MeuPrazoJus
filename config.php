<?php

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, '"\'');
            $value = trim($value, '"\''); 
            
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) $value = $_ENV[$key];
    if ($value === false && isset($_SERVER[$key])) $value = $_SERVER[$key];
    return $value !== false ? $value : $default;
}

$isLocal = (env('APP_ENV', 'production') === 'local');

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'meuprazojus'));

define('BASE_PATH', __DIR__);

date_default_timezone_set('America/Sao_Paulo');

// Asaas Credentials
define('ASAAS_API_KEY', env('ASAAS_API_KEY', ''));
define('ASAAS_URL', env('ASAAS_URL', 'https://sandbox.asaas.com/api/v3'));
