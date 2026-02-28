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

// Quando o pagamento via Pix ou Boleto compensar na conta
if ($event === 'PAYMENT_CONFIRMED' || $event === 'PAYMENT_RECEIVED') {
    $payment = $data['payment'] ?? [];
    
    // O externalReference foi preenchido com o user_id na hora de gerar o pagamento
    if (!empty($payment['externalReference'])) {
        $user_id = $payment['externalReference'];
        
        $userManager = new UserManager();
        
        // Ativa a conta premium por 1 ano
        $endDate = date('Y-m-d', strtotime('+1 year'));
        $userManager->setSubscription($user_id, 'premium', $endDate);
        
        error_log("Webhook Processado [PIX/Boleto]: Assinatura renovada para User_ID: " . $user_id);
    }
}

// Retorna 200 OK para o Asaas saber que recebemos
http_response_code(200);
echo json_encode(['status' => 'received']);
