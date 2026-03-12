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
            require_once __DIR__ . '/../src/Database.php';
            die("<div style='padding:20px; font-family:sans-serif; max-width:600px; margin: 40px auto; border: 1px solid #ccc; border-radius:10px;'>
                 <h2 style='color:#b91c1c;'>Acesso Negado à Pasta de Uploads</h2>
                 <p>O servidor Linux (BunkerWeb / Ubuntu) em que este sistema está hospedado <strong>cortou as permissões do PHP ({$_SERVER['USER']})</strong> para guardar imagens ou PDFs na pasta <code>/FCSigner/uploads</code>.</p>
                 <p>Como resolver definitivamente:<br>Peça ao administrador para acessar o terminal (SSH) do servidor e digitar os seguintes comandos como Root:</p>
                 <div style='background:#1e293b; color:#fff; padding:15px; border-radius:5px; font-family:monospace; margin: 10px 0;'>
                 sudo chown -R www-data:www-data " . __DIR__ . "/uploads<br>
                 sudo chmod -R 777 " . __DIR__ . "/uploads
                 </div>
                 <p style='font-size:12px; color:#666;'>Detalhes Técnicos: " . htmlspecialchars(print_r($e, true)) . "</p>
                 </div>");
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
