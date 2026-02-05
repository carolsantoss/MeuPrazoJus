<?php
session_start();
header('Content-Type: application/json');

require_once '../config.php';
require_once '../src/UserManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
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

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$result = json_decode($response, true);
curl_close($ch);

if ($http_code === 201 || $http_code === 200) {
    if ($result['status'] === 'approved') {
        $userManager = new UserManager();
        $userManager->setSubscription($user_id, 'premium');
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
    echo json_encode([
        'error' => 'Erro ao processar pagamento',
        'details' => $result
    ]);
}
