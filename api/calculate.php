<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/DeadlineCalculator.php';
require_once __DIR__ . '/../src/UserManager.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$startDate = $data['startDate'] ?? null;
$days = intval($data['days'] ?? 0);
$type = $data['type'] ?? 'working';
$state = $data['state'] ?? null;
$city = $data['city'] ?? null;
$cityName = $data['cityName'] ?? null;
$matter = $data['matter'] ?? null;
$processType = $data['processType'] ?? 'electronic';
$court = $data['court'] ?? null;

if (!$startDate || $days <= 0) {
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

$userManager = new UserManager();
$user = null;
$isSubscribed = false;
$usageCount = 0;

if (isset($_SESSION['user_id'])) {
    $user = $userManager->getUserById($_SESSION['user_id']);
    $usageCount = $user['calculations_count'] ?? 0;
    $isSubscribed = ($user['subscription_status'] === 'premium');
} else {
    if (!isset($_SESSION['calculations'])) $_SESSION['calculations'] = 0;
    $usageCount = $_SESSION['calculations'];
}


try {
    $result = DeadlineCalculator::calculate($startDate, $days, $type, $state, $city, $matter, $cityName);
    
    if ($user) {
        $usageCount = $userManager->incrementUsage($user['id']);
        $_SESSION['calculations'] = $usageCount; // Sync session
        
        require_once __DIR__ . '/../src/DeadlineManager.php';
        $dlManager = new DeadlineManager();
        $dlManager->save($user['id'], [
            'start_date' => $startDate,
            'days' => $days,
            'type' => $type,
            'matter' => $matter,
            'end_date' => $result['end_date'],
            'description' => $result['description'],
            'location' => $result['location'] ?? ''
        ]);

    } else {
        $_SESSION['calculations']++;
        $usageCount = $_SESSION['calculations'];
    }
    
    $result['usage'] = [
        'count' => $usageCount,
        'limit' => 5,
        'is_subscribed' => $isSubscribed
    ];

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
