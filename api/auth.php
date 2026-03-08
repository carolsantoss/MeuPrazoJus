<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../src/UserManager.php';

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
        if (ob_get_length()) ob_clean();
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
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => true,
            'is_subscribed' => $_SESSION['is_subscribed'],
            'calculations_count' => 0
        ]);
    } else {
        if (ob_get_length()) ob_clean();
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
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => true,
            'is_subscribed' => $_SESSION['is_subscribed'],
            'calculations_count' => $_SESSION['calculations'] ?? 0
        ]);
    } else {
        if (ob_get_length()) ob_clean();
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
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true]);
    } else {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Não autenticado']);
    }
} elseif ($action === 'cancel_subscription') {
    if (isset($_SESSION['user_id'])) {
        $userManager->setSubscription($_SESSION['user_id'], 'free', null);
        $_SESSION['is_subscribed'] = false;
        $_SESSION['subscription_end'] = null;
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true]);
    } else {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Não autenticado']);
    }
} elseif ($action === 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Não autenticado']);
        exit;
    }

    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$name || !$phone) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Nome e telefone são obrigatórios']);
        exit;
    }

    $result = $userManager->updateProfile($_SESSION['user_id'], $name, $phone, $email, $password);
    if ($result['success']) {
        $_SESSION['user_name'] = $name;
        if ($email) {
            $_SESSION['user_email'] = $email;
        }
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true]);
    } else {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => $result['error']]);
    }
} elseif ($action === 'forgot_password') {
    $email = $data['email'] ?? '';
    if (!$email) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Email é obrigatório']);
        exit;
    }

    $token = $userManager->createPasswordReset($email);
    if ($token) {
        // Enviar e-mail com o link de recuperação
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset?token=" . $token;
        $subject = "Recuperacao de Senha - MeuPrazoJus";
        $message = "Você solicitou a recuperação de senha. Clique no link para redefinir: " . $resetLink;
        $headers = "From: suporte@meuprazojus.com.br\r\n";
        
        // Vamos usar a função mail(), se houver servidor SMTP configurado ela funcionará
        // Se não houver, no ambiente de desenvolvimento iremos logar ou assumir apenas como teste
        @mail($email, $subject, $message, $headers);

        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true]);
    } else {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Se o e-mail estiver cadastrado, um link de recuperação será enviado.']); // Mensagem genérica por segurança
    }
} elseif ($action === 'reset_password') {
    $token = $data['token'] ?? '';
    $password = $data['password'] ?? '';

    if (!$token || !$password) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Token e senha são obrigatórios']);
        exit;
    }

    $result = $userManager->resetPassword($token, $password);
    if (ob_get_length()) ob_clean();
    echo json_encode($result);
} else {
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => 'Ação inválida']);
}
