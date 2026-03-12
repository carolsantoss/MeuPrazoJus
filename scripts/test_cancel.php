<?php
session_start();
$_SESSION['user_id'] = 'usr_6986a4398aaac'; // Caroline

require_once __DIR__ . '/../src/UserManager.php';
$userManager = new UserManager();

echo "Tentando cancelar assinatura para usr_6986a4398aaac...\n";
$res = $userManager->setSubscription($_SESSION['user_id'], 'free', null, null);

if ($res) {
    echo "Sucesso no Banco de Dados!\n";
} else {
    echo "Falha no Banco de Dados.\n";
}

$stmt = Database::getInstance()->getConnection()->prepare("SELECT subscription_status, subscription_plan FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
print_r($user);
