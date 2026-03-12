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
        $_SESSION['subscription_plan'] = $user['subscription_plan'] ?? null;
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
        $userManager->setSubscription($_SESSION['user_id'], 'free', null, null);
        $_SESSION['is_subscribed'] = false;
        $_SESSION['subscription_end'] = null;
        $_SESSION['subscription_plan'] = null;
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
        require_once __DIR__ . '/../config.php';
        require_once __DIR__ . '/../src/PHPMailer/Exception.php';
        require_once __DIR__ . '/../src/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/../src/PHPMailer/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_USER, 'MeuPrazoJus');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Código de Recuperação - MeuPrazoJus';
            $mail->Body    = "Você solicitou a recuperação de senha.<br><br>Seu código de 6 dígitos é: <b>" . $token . "</b><br><br>Se não foi você, ignore este e-mail.";
            $mail->AltBody = "Você solicitou a recuperação de senha. Seu código de 6 dígitos é: " . $token . "\n\nSe não foi você, ignore este e-mail.";

            $mail->send();

            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => true]);
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            if (ob_get_length()) ob_clean();
            echo json_encode(['error' => 'Falha ao enviar o e-mail verifique a Senha de Aplicativo no .env. Erro interno: ' . $mail->ErrorInfo]);
        }
    } else {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Se o e-mail estiver cadastrado, um código de recuperação será enviado.']); // Mensagem genérica por segurança
    }
} elseif ($action === 'reset_password') {
    $email = $data['email'] ?? '';
    $token = strtoupper(trim($data['token'] ?? ''));
    $password = $data['password'] ?? '';

    if (!$email || !$token || !$password) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'E-mail, código e senha são obrigatórios']);
        exit;
    }

    $result = $userManager->resetPassword($email, $token, $password);
    if (ob_get_length()) ob_clean();
    echo json_encode($result);
} else {
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => 'Ação inválida']);
}
