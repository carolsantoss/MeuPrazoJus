<?php
// api/calculate.php
session_start();
header('Content-Type: application/json');

require_once '../config.php';
require_once '../src/DeadlineCalculator.php';
require_once '../src/UserManager.php';

// Simple file-based "DB" for users/trials if SQLite fails or just for simplicity here
// In a real app, use the PDO connection from config.php.
// Here we simulate the logic.

// Get inputs
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$startDate = $data['startDate'] ?? date('Y-m-d');
$days = intval($data['days'] ?? 0);
$type = $data['type'] ?? 'working';

// --- Trial Logic ---
$userManager = new UserManager();
$user = null;
$isSubscribed = false;
$usageCount = 0;

if (isset($_SESSION['user_id'])) {
    // Authenticated User
    $user = $userManager->getUser($_SESSION['user_id']);
    $usageCount = $user['calculations_count'] ?? 0;
    $isSubscribed = ($user['subscription_status'] === 'premium');
} else {
    // Guest User
    if (!isset($_SESSION['calculations'])) $_SESSION['calculations'] = 0;
    $usageCount = $_SESSION['calculations'];
}

// Check limit (5 for free)
if (!$isSubscribed && $usageCount >= 5) {
    echo json_encode([
        'error' => 'upgrade_required',
        'message' => 'Seu período de teste gratuito expirou (limite de 5 cálculos). Assine para continuar.'
    ]);
    exit;
}

try {
    $result = DeadlineCalculator::calculate($startDate, $days, $type);
    
    // Increment counter
    if ($user) {
        $usageCount = $userManager->incrementUsage($user['id']);
    } else {
        $_SESSION['calculations']++;
        $usageCount = $_SESSION['calculations'];
    }
    
    // add usage info
    $result['usage'] = [
        'count' => $usageCount,
        'limit' => 5,
        'is_subscribed' => $isSubscribed
    ];

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
