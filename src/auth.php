<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

require_once __DIR__ . '/UserManager.php';
$um = new UserManager();
$u = $um->getUserById($_SESSION['user_id']);
if ($u) {
    $_SESSION['calculations'] = $u['calculations_count'];
    $_SESSION['is_subscribed'] = ($u['subscription_status'] === 'premium');
    $_SESSION['subscription_end'] = $u['subscription_end'] ?? null;
}
?>
