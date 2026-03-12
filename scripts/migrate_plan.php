<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance()->getConnection();

try {
    // Add subscription_plan column if not exists
    $db->exec("ALTER TABLE users ADD COLUMN subscription_plan VARCHAR(20) DEFAULT NULL");
    echo "Coluna subscription_plan adicionada com sucesso!\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "Coluna já existe.\n";
    } else {
        echo "Erro: " . $e->getMessage() . "\n";
    }
}

// Show current users
$stmt = $db->query("SELECT id, name, subscription_status, subscription_plan, subscription_end FROM users");
$users = $stmt->fetchAll();
echo "\nUsuários:\n";
foreach ($users as $u) {
    echo "  {$u['id']} | {$u['name']} | status={$u['subscription_status']} | plan={$u['subscription_plan']} | end={$u['subscription_end']}\n";
}
