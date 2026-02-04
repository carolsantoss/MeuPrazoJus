<?php
session_start();
header('Content-Type: application/json');

require_once '../src/UserManager.php';

$action = $_GET['action'] ?? '';

$json = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($json, $_POST);

$userManager = new UserManager();

if ($action === 'register') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    
    if (!$email || !$password) {
        echo json_encode(['error' => 'Preencha todos os campos']);
        exit;
    }

    $user = $userManager->register($email, $password, $name, $phone);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['is_subscribed'] = ($user['subscription_status'] === 'premium');
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Email já cadastrado']);
    }

} elseif ($action === 'login') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $user = $userManager->login($email, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['is_subscribed'] = ($user['subscription_status'] === 'premium');
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Credenciais inválidas']);
    }

} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
} elseif ($action === 'subscribe') {
    // Mock subscription
    if (isset($_SESSION['user_id'])) {
        $userManager->setSubscription($_SESSION['user_id'], 'premium');
        $_SESSION['is_subscribed'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Não autenticado']);
    }
} else {
    echo json_encode(['error' => 'Ação inválida']);
}
