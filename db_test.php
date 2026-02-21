<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

header('Content-Type: text/plain');

echo "Testando conexão com o Banco de Dados...\n";
echo "Ambiente Local? " . ($isLocal ? 'SIM' : 'NÃO (Produção)') . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_NAME: " . DB_NAME . "\n\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $conn = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ SUCESSO: Conexão estabelecida com sucesso!\n";
} catch (Throwable $e) {
    echo "❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . " no arquivo " . $e->getFile() . "\n";
    echo "\nDICA:\n";
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "- O usuário ou senha estão incorretos para o host atual.\n";
        echo "- O banco de dados MySQL de produção pode estar configurado para exigir que o acesso seja feito no host 'localhost'. Tente mudar DB_HOST no config.php de '" . DB_HOST . "' para 'localhost'.\n";
    } elseif (strpos($e->getMessage(), 'could not find driver') !== false) {
        echo "- A extensão pdo_mysql não está instalada ou não está ativa no seu servidor de Produção (verifique seu painel de controle ou arquivo php.ini).\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "- O banco '" . DB_NAME . "' não foi criado no servidor de produção.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'No connection could be made') !== false) {
        echo "- O servidor MySQL pode estar desligado ou bloqueando a porta 3306.\n";
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        echo "- Extensão PDO do PHP não está habilitada.\n";
    }
}
