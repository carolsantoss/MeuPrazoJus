<?php
session_start();
require_once __DIR__ . '/../src/UserManager.php';

$status = $_GET['status'] ?? 'pending';
$payment_id = $_GET['payment_id'] ?? '';

$userManager = new UserManager();

if ($status === 'success') {
    if (isset($_SESSION['user_id'])) {
        $userManager->setSubscription($_SESSION['user_id'], 'premium');
        $_SESSION['is_subscribed'] = true;
    }
    $message = "Pagamento aprovado! Sua conta agora é Premium.";
    $color = "green";
    $icon = "✅";
} elseif ($status === 'pending') {
    $message = "Aguardando confirmação do pagamento (Pix ou Boleto).";
    $color = "#f59e0b";
    $icon = "⏳";
} else {
    $message = "O pagamento não foi concluído ou foi cancelado.";
    $color = "red";
    $icon = "❌";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Status do Pagamento - MeuPrazoJus</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .callback-card {
            max-width: 500px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .status-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="callback-card">
        <div class="status-icon" style="color: <?php echo $color; ?>">
            <?php echo $icon; ?>
        </div>
        <h2 style="color: white;"><?php echo $message; ?></h2>
        <p style="color: var(--text-muted); margin: 20px 0;">
            ID do Pagamento: <?php echo $payment_id; ?>
        </p>
        <a href="../index.php" class="btn btn-primary">Voltar para a Calculadora</a>
    </div>
</body>
</html>
