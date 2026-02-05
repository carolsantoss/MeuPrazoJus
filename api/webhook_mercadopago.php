<?php
// api/webhook_mercadopago.php
require_once '../config.php';
require_once '../src/UserManager.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['type'])) {
    http_response_code(400);
    exit;
}

if ($data['type'] === 'payment') {
    $payment_id = $data['data']['id'];
    
    $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . MP_ACCESS_TOKEN
    ]);
    
    $response = curl_exec($ch);
    $payment_details = json_decode($response, true);
    curl_close($ch);
    
    if (isset($payment_details['status']) && ($payment_details['status'] === 'approved' || $payment_details['status'] === 'authorized')) {
        $user_id = $payment_details['external_reference'];
        
        if ($user_id) {
            $userManager = new UserManager();
            $userManager->setSubscription($user_id, 'premium');
        }
    }
}

http_response_code(200);
?>
