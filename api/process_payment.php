<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

ob_start();

function json_response($data, $code = 200) {
    ob_clean();
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
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../src/UserManager.php';
    
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Usuário não autenticado'], 401);
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || empty($data['cpfCnpj'])) {
        json_response(['error' => 'CPF/CNPJ inválido'], 400);
    }

    $cpfCnpj = preg_replace('/[^0-9]/', '', $data['cpfCnpj']);
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];

    $headers = [
        "access_token: " . ASAAS_API_KEY,
        "Content-Type: application/json",
        "User-Agent: MeuPrazoJus/1.0"
    ];

    // 1. Create or Find Customer
    $ch = curl_init(ASAAS_URL . "/customers?cpfCnpj=" . $cpfCnpj);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $customers = json_decode($response, true);
    curl_close($ch);

    $customer_id = null;
    if (isset($customers['data']) && count($customers['data']) > 0) {
        $customer_id = $customers['data'][0]['id'];
    } else {
        // Create customer
        $customer_data = [
            "name" => $user_name,
            "email" => $user_email,
            "cpfCnpj" => $cpfCnpj,
            "externalReference" => $user_id
        ];

        $ch = curl_init(ASAAS_URL . "/customers");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customer_data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        if (isset($result['id'])) {
            $customer_id = $result['id'];
        } else {
            file_put_contents('../payment_errors.log', date('Y-m-d H:i:s') . " - Erro ao criar cliente no Asaas: " . $response . PHP_EOL, FILE_APPEND);
            $msg = isset($result['errors'][0]['description']) ? $result['errors'][0]['description'] : 'Erro ao cadastrar cliente no Asaas.';
            json_response(['error' => $msg], 400);
        }
    }

    // 2. Create Charge
    $dueDate = date('Y-m-d');
    
    // Determine the base URL for the callback
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $callbackUrl = $protocol . "://" . $host . "/api/payment_callback.php?status=success";
    
    $payment_data = [
        "customer" => $customer_id,
        "billingType" => "UNDEFINED", // Let the user choose Pix, Boleto or CC on Asaas checkout page
        "value" => 50.00,
        "dueDate" => $dueDate,
        "description" => "Assinatura Anual MeuPrazoJus",
        "externalReference" => $user_id,
        "callback" => [
            "successUrl" => $callbackUrl,
            "autoRedirect" => true
        ]
    ];

    $ch = curl_init(ASAAS_URL . "/payments");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result = json_decode($response, true);
    curl_close($ch);

    $log_file = '../payment.log';
    $log_entry = date('Y-m-d H:i:s') . " - User: $user_id - HTTP Code: $http_code - Response: " . $response . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    if (isset($result['invoiceUrl'])) {
        json_response([
            'success' => true,
            'invoiceUrl' => $result['invoiceUrl']
        ]);
    } else {
        $msg = isset($result['errors'][0]['description']) ? $result['errors'][0]['description'] : 'Erro ao processar pagamento.';
        json_response([
            'error' => $msg,
            'details' => $result
        ], 500);
    }

} catch (Throwable $e) {
    ob_clean();
    $msg = "Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    file_put_contents('../payment_errors.log', date('Y-m-d H:i:s') . " - " . $msg . PHP_EOL, FILE_APPEND);
    json_response(['error' => 'Erro interno no servidor', 'debug_msg' => $msg], 500);
}
