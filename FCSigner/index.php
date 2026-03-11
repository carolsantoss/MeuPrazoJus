<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if (empty($_SESSION['is_subscribed'])) {
    header('Location: /subscription');
    exit;
}

$user_id = $_SESSION['user_id'];

$mes_atual = date('m');
$ano_atual = date('Y');

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM documents WHERE user_id = :user_id AND MONTH(created_at) = :mes AND YEAR(created_at) = :ano");
$stmt->execute(['user_id' => $user_id, 'mes' => $mes_atual, 'ano' => $ano_atual]);
$result = $stmt->fetch();
$total_docs_mes = $result['total'] ?? 0;

$limite_mensal = 100;
$pode_criar_novo = ($total_docs_mes < $limite_mensal);

$stmtDocs = $pdo->prepare("SELECT * FROM documents WHERE user_id = :user_id ORDER BY created_at DESC");
$stmtDocs->execute(['user_id' => $user_id]);
$documentos = $stmtDocs->fetchAll();
?>
<?php require_once __DIR__ . '/app/Views/dashboard.view.php'; ?>