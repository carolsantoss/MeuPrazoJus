<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

ob_start();

function json_response($data, $code = 200) {
    if (ob_get_length()) ob_clean();
    http_response_code($code); 
    echo json_encode($data);
    exit;
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        if (ob_get_length()) ob_clean();
        $msg = "Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}";
        error_log($msg);
        echo json_encode(['error' => 'Erro interno crítico. Consulte o log.', 'debug_msg' => $msg]);
    }
});

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../src/UserManager.php';
    $userManager = new UserManager();
    
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Usuário não autenticado'], 401);
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $billingType = $data['billingType'] ?? 'CREDIT_CARD';

    if (!$data || empty($data['cpfCnpj'])) {
        json_response(['error' => 'Dados de pagamento incompletos'], 400);
    }
    
    if ($billingType === 'CREDIT_CARD' && empty($data['creditCard'])) {
        json_response(['error' => 'Dados de cartão incompletos'], 400);
    }

    $cpfCnpj = preg_replace('/[^0-9]/', '', $data['cpfCnpj']);
    $cc_data = $data['creditCard'] ?? [];
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '10.0.0.1';
    if ($remoteIp === '::1' || $remoteIp === '127.0.0.1') { $remoteIp = '10.0.0.1'; /* mockup pra testes local */ }
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];

    $headers = [
        "access_token: " . ASAAS_API_KEY,
        "Content-Type: application/json",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    ];

    $ch = curl_init(ASAAS_URL . "/customers?cpfCnpj=" . $cpfCnpj);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        error_log("cURL Error on Customer API: " . $curl_error);
        json_response(['error' => 'Erro de conexão (cURL). ' . $curl_error], 500);
    }
    
    $customers = json_decode($response, true);
    
    $customer_id = null;
    if (isset($customers['data']) && count($customers['data']) > 0) {
        $customer_id = $customers['data'][0]['id'];
    } else {
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
            error_log("Erro ao criar cliente no Asaas: " . $response);
            $msg = isset($result['errors'][0]['description']) ? $result['errors'][0]['description'] : 'Erro ao cadastrar cliente no Asaas.';
            json_response(['error' => $msg], 400);
        }
    }

    $dueDate = date('Y-m-d');
    
    $protocol = "https";
    $host = $_SERVER['HTTP_HOST'];
    $callbackUrl = $protocol . "://" . $host . "/api/payment_callback.php?status=success";
    
    $payment_data = [
        "customer" => $customer_id,
        "billingType" => $billingType,
        "value" => 50.00,
        "dueDate" => $dueDate,
        "description" => "Assinatura Anual MeuPrazoJus - " . $billingType,
        "externalReference" => $user_id
    ];

    if ($billingType === 'CREDIT_CARD') {
        $payment_data["creditCard"] = [
            "holderName" => $cc_data['holderName'],
            "number" => $cc_data['number'],
            "expiryMonth" => $cc_data['expiryMonth'],
            "expiryYear" => $cc_data['expiryYear'],
            "ccv" => $cc_data['ccv']
        ];
        $payment_data["creditCardHolderInfo"] = [
            "name" => $user_name,
            "email" => $user_email,
            "cpfCnpj" => $cpfCnpj,
            "postalCode" => "01001000",
            "addressNumber" => "1",
            "phone" => "11999999999"
        ];
        $payment_data["remoteIp"] = $remoteIp;
    }

    $ch = curl_init(ASAAS_URL . "/payments");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        error_log("cURL Error on Payment API: " . $curl_error);
        json_response(['error' => 'Erro de conexão (cURL). ' . $curl_error], 500);
    }
    
    $result = json_decode($response, true);
    
    error_log("Payment Processed - User: $user_id - HTTP: $http_code");

    if (isset($result['status']) && in_array($result['status'], ['CONFIRMED', 'RECEIVED', 'PENDING', 'AWAITING_RISK_ANALYSIS'])) {
        
        $response_data = [
            'success' => true,
            'status' => $result['status'],
            'payment_id' => $result['id'] ?? '',
            'billingType' => $billingType
        ];
        
        if ($billingType === 'CREDIT_CARD' && ($result['status'] === 'CONFIRMED' || $result['status'] === 'RECEIVED')) {
            // Aprove internally immediately since it is credit card
            $userManager->setSubscription($user_id, 'premium', date('Y-m-d', strtotime('+1 year')));
            $_SESSION['is_subscribed'] = true;
        } elseif ($billingType === 'PIX') {
            // we need to get Pix QR CODE
            $chQr = curl_init(ASAAS_URL . "/payments/" . $result['id'] . "/pixQrCode");
            curl_setopt($chQr, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chQr, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chQr, CURLOPT_SSL_VERIFYPEER, false);
            $pixResponse = curl_exec($chQr);
            curl_close($chQr);
            
            $pixData = json_decode($pixResponse, true);
            $response_data['pix'] = [
                'encodedImage' => $pixData['encodedImage'] ?? '',
                'payload' => $pixData['payload'] ?? '',
                'expirationDate' => $pixData['expirationDate'] ?? ''
            ];
        } elseif ($billingType === 'BOLETO') {
            $response_data['boletoUrl'] = $result['bankSlipUrl'] ?? '';
            $response_data['invoiceUrl'] = $result['invoiceUrl'] ?? '';
        }
        
        json_response($response_data);
    } else {
        $msg = isset($result['errors'][0]['description']) ? $result['errors'][0]['description'] : 'Transação não aprovada pela operadora.';
        json_response([
            'error' => $msg,
            'details' => $result
        ], 500);
    }
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    $msg = "Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    error_log($msg);
    json_response(['error' => 'Erro interno no servidor', 'debug_msg' => $msg], 500);
}
