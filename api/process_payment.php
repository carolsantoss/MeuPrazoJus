<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

ob_start();

function json_response($data, $code = 200) {
    $output = ob_get_clean();
    if (!empty($output)) {
        file_put_contents('../payment_errors.log', date('Y-m-d H:i:s') . " - Unexpected Output: " . $output . PHP_EOL, FILE_APPEND);
    }
    http_response_code($code);
    echo json_encode($data);
    exit;
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        ob_clean();
        $msg = "Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}";
        file_put_contents('../payment_errors.log', date('Y-m-d H:i:s') . " - " . $msg . PHP_EOL, FILE_APPEND);
        echo json_encode(['error' => 'Erro interno crítico. Consulte o log.', 'debug_msg' => $msg]);
    }
});

try {
    require_once '../config.php';
    require_once '../src/UserManager.php';
    
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Usuário não autenticado'], 401);
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        json_response(['error' => 'Dados inválidos'], 400);
    }

$user_id = $_SESSION['user_id'];

$payment_data = [
    "transaction_amount" => (float)$data['transaction_amount'],
    "token" => $data['token'] ?? null,
    "description" => $data['description'] ?? "Assinatura Anual MeuPrazoJus",
    "installments" => (int)($data['installments'] ?? 1),
    "payment_method_id" => $data['payment_method_id'],
    "payer" => [
        "email" => $data['payer']['email'],
        "first_name" => $data['payer']['first_name'] ?? '',
        "last_name" => $data['payer']['last_name'] ?? '',
        "identification" => [
            "type" => $data['payer']['identification']['type'] ?? 'CPF',
            "number" => $data['payer']['identification']['number'] ?? ''
        ]
    ],
    "external_reference" => $user_id,
    "notification_url" => "https://your-domain.com/api/webhook_mercadopago.php"
];

$ch = curl_init("https://api.mercadopago.com/v1/payments");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . MP_ACCESS_TOKEN,
    "Content-Type: application/json",
    "X-Idempotency-Key: " . uniqid('mp_')
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));

// Disable SSL verification for local dev
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$result = json_decode($response, true);
curl_close($ch);

$log_file = '../payment.log';
$log_entry = date('Y-m-d H:i:s') . " - User: $user_id - HTTP Code: $http_code - Curl Error: $curl_error - Response: " . $response . PHP_EOL;
file_put_contents($log_file, $log_entry, FILE_APPEND);

if ($http_code === 201 || $http_code === 200) {
    if ($result['status'] === 'approved') {
        $userManager = new UserManager();
        $expiryDate = date('Y-m-d H:i:s', strtotime('+1 year'));
        $userManager->setSubscription($user_id, 'premium', $expiryDate);
        $_SESSION['is_subscribed'] = true;
    }
    
    $response_data = [
        'status' => $result['status'],
        'status_detail' => $result['status_detail'],
        'id' => $result['id'],
    ];

    if (isset($result['point_of_interaction']['transaction_data'])) {
        $response_data['pix'] = [
            'qr_code' => $result['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'qr_code_base64' => $result['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
            'ticket_url' => $result['point_of_interaction']['transaction_data']['ticket_url'] ?? null
        ];
    }

    if (isset($result['transaction_details']['external_resource_url'])) {
        $response_data['boleto_url'] = $result['transaction_details']['external_resource_url'];
    }

    echo json_encode($response_data);
} else {
    json_response([
        'error' => 'Erro ao processar pagamento',
        'details' => $result
    ], 500);
}
} catch (Throwable $e) {
    ob_clean();
    $msg = "Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    file_put_contents('../payment_errors.log', date('Y-m-d H:i:s') . " - " . $msg . PHP_EOL, FILE_APPEND);
    json_response(['error' => 'Erro interno no servidor', 'debug_msg' => $msg], 500);
}
