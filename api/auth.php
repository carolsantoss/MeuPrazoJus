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
        ob_clean();
        echo json_encode(['error' => 'Preencha todos os campos']);
        exit;
    }

    $user = $userManager->register($email, $password, $name, $phone);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['is_subscribed'] = (($user['subscription_status'] ?? 'free') === 'premium');
        $_SESSION['calculations'] = 0;
        ob_clean();
        echo json_encode([
            'success' => true,
            'is_subscribed' => $_SESSION['is_subscribed'],
            'calculations_count' => 0
        ]);
    } else {
        ob_clean();
        echo json_encode(['error' => 'Email já cadastrado']);
    }

} elseif ($action === 'login') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $user = $userManager->login($email, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['is_subscribed'] = (($user['subscription_status'] ?? 'free') === 'premium');
        $_SESSION['subscription_end'] = $user['subscription_end'] ?? null;
        ob_clean();
        echo json_encode([
            'success' => true,
            'is_subscribed' => $_SESSION['is_subscribed'],
            'calculations_count' => $_SESSION['calculations']
        ]);
    } else {
        ob_clean();
        echo json_encode(['error' => 'Credenciais inválidas']);
    }

} elseif ($action === 'logout') {
    session_destroy();
    ob_clean();
    echo json_encode(['success' => true]);
} elseif ($action === 'subscribe') {
    // Mock subscription
    if (isset($_SESSION['user_id'])) {
        $date = new DateTime('+1 year');
        $dateStr = $date->format('Y-m-d');
        $userManager->setSubscription($_SESSION['user_id'], 'premium', $dateStr);
        $_SESSION['is_subscribed'] = true;
        $_SESSION['subscription_end'] = $dateStr;
        ob_clean();
        echo json_encode(['success' => true]);
    } else {
        ob_clean();
        echo json_encode(['error' => 'Não autenticado']);
    }
} else {
    ob_clean();
    echo json_encode(['error' => 'Ação inválida']);
}
