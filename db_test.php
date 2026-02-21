<?php
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
} catch (PDOException $e) {
    echo "❌ ERRO DE CONEXÃO: " . $e->getMessage() . "\n";
    echo "\nDICA:\n";
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "- O usuário ou senha estão incorretos para o host atual.\n";
        echo "- O banco de dados MySQL de produção pode estar configurado para exigir que o acesso seja feito no host 'localhost'. Tente mudar DB_HOST no config.php de '" . DB_HOST . "' para 'localhost'.\n";
    } elseif (strpos($e->getMessage(), 'could not find driver') !== false) {
        echo "- A extensão pdo_mysql não está ativa no servidor de Produção (PHP.ini).\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "- O banco '" . DB_NAME . "' não foi criado no servidor de produção.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'No connection could be made') !== false) {
        echo "- O servidor MySQL pode estar desligado ou bloqueando a porta 3306.\n";
    }
}
