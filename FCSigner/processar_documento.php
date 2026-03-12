<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Formato inválido.");
}

$contratante = $_POST['nome_contratante'] ?? 'Contratante';
$contratado = $_POST['nome_contratado'] ?? 'Contratado';

if (!isset($_FILES['documento_pdf']) || $_FILES['documento_pdf']['error'] !== UPLOAD_ERR_OK) {
    die("Erro no upload do PDF. (Código: " . ($_FILES['documento_pdf']['error'] ?? 'desconhecido') . ")");
}

$tmpPath = $_FILES['documento_pdf']['tmp_name'];
$originalHash = hash_file('sha256', $tmpPath);
$docHash = bin2hex(random_bytes(16));
$urlValidacao = "meuprazojus.com.br/validar/" . $docHash;

if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0777, true);
}
@chmod(__DIR__ . '/uploads', 0777);

$nomeArquivoOriginal = "uploads/original_" . $docHash . ".pdf";
$destinoCompleto = __DIR__ . '/uploads/original_' . $docHash . '.pdf';

if (!@move_uploaded_file($tmpPath, $destinoCompleto)) {
    $content = @file_get_contents($tmpPath);
    if ($content !== false) {
        $putResult = @file_put_contents($destinoCompleto, $content);
        if ($putResult === false) {
            $e = error_get_last();
            die("Erro ao salvar arquivo PDF na pasta. Erro interno: " . print_r($e, true) . " | Destino: " . $destinoCompleto);
        }
        @unlink($tmpPath);
    }
    else {
        die("Falha extrema ao acessar o arquivo do upload temporário. Verifique o PHP temp_dir.");
    }
}

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $pdo->prepare("INSERT INTO documents (user_id, document_hash, title, status, original_hash, file_path) VALUES (?, ?, ?, 'Pendente', ?, ?)");
$stmt->execute([
    $user_id,
    $docHash,
    $_FILES['documento_pdf']['name'] ?? 'Documento sem título',
    $originalHash,
    '/' . $nomeArquivoOriginal
]);

$document_id = $pdo->lastInsertId();

$stmtLog = $pdo->prepare("INSERT INTO audit_logs (document_id, action_type, actor_name, ip_address, geolocation) VALUES (?, 'Criou', ?, ?, 'Sistema')");
$ip_cli = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$stmtLog->execute([$document_id, $contratante, $ip_cli]);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_dir = dirname($_SERVER['REQUEST_URI']);

$linkSimulado = $protocol . "://" . $host . $base_dir . "/assinar_link.php?hash=" . $docHash;

header("Location: index.php?link_assinatura=" . urlencode($linkSimulado));
exit();
