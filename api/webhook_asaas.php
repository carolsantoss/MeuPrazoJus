<?php
// api/webhook_asaas.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/UserManager.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['event'])) {
    http_response_code(400);
    exit('Invalid payload');
}

$log_file = '../payment.log';
$log_entry = date('Y-m-d H:i:s') . " - Asaas Webhook Received: " . $json . PHP_EOL;
file_put_contents($log_file, $log_entry, FILE_APPEND);

$event = $data['event'];
$payment = $data['payment'];

// Se pagamento confirmado ou recebido com sucesso
if ($event === 'PAYMENT_RECEIVED' || $event === 'PAYMENT_CONFIRMED') {
    $user_id = $payment['externalReference'] ?? null;
    
    if ($user_id) {
        $userManager = new UserManager();
        
        // Verifica se usuário existe
        $user = $userManager->getUserById($user_id);
        if ($user) {
            // Renovar por 1 ano
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 year'));
            $userManager->setSubscription($user_id, 'premium', $expiryDate);
            
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Subscription updated via Webhook for user $user_id" . PHP_EOL, FILE_APPEND);
        }
    }
}

http_response_code(200);
echo "OK";
