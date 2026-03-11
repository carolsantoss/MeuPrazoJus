<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (empty($_SESSION['is_subscribed'])) {
    header('Location: ../subscription.php');
    exit;
}
?>
<?php require_once __DIR__ . '/app/Views/novo_documento.view.php'; ?>