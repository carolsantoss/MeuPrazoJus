<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/UserManager.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    exit;
}

$event = $data['event'] ?? '';

if ($event === 'PAYMENT_CONFIRMED' || $event === 'PAYMENT_RECEIVED') {
    $payment = $data['payment'] ?? [];

    if (!empty($payment['externalReference'])) {
        $ref_parts = explode('-', $payment['externalReference']);
        $user_id = $ref_parts[0];
        $plano = $ref_parts[1] ?? 'anual';

        $userManager = new UserManager();

        $endDate = ($plano === 'mensal') ? date('Y-m-d', strtotime('+1 month')) : date('Y-m-d', strtotime('+1 year'));
        $userManager->setSubscription($user_id, 'premium', $endDate, $plano);

        error_log("Webhook Processado [PIX/Boleto, Plano: {$plano}]: Assinatura renovada para User_ID: " . $user_id);
    }
}

http_response_code(200);
echo json_encode(['status' => 'received']);
