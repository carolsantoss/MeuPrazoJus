<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Formato inválido.");
}

$contratante = $_POST['nome_contratante'] ?? 'Contratante';
$cpf_contratante = $_POST['cpf_contratante'] ?? null;
$contratado = $_POST['nome_contratado'] ?? 'Contratado';

if (!isset($_FILES['documento_pdf']) || empty($_FILES['documento_pdf']['tmp_name'][0])) {
    die("Erro no upload do PDF.");
}

$docHash = bin2hex(random_bytes(16));
$urlValidacao = "meuprazojus.com.br/validar/" . $docHash;

if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0777, true);
}
@chmod(__DIR__ . '/uploads', 0777);

$nomeArquivoOriginal = "uploads/original_" . $docHash . ".pdf";
$destinoCompleto = __DIR__ . '/' . $nomeArquivoOriginal;

require_once __DIR__ . '/vendor/autoload.php';
$pdf = new \setasign\Fpdi\Fpdi();
$metadata = [];
$startPage = 1;

$firstContentHash = '';

// Reorganiza array $_FILES caso seja múltiplo
$filesData = $_FILES['documento_pdf'];
$fileCount = is_array($filesData['tmp_name']) ? count($filesData['tmp_name']) : 1;

for ($i = 0; $i < $fileCount; $i++) {
    $tmpName = is_array($filesData['tmp_name']) ? $filesData['tmp_name'][$i] : $filesData['tmp_name'];
    $err = is_array($filesData['error']) ? $filesData['error'][$i] : $filesData['error'];
    $fName = is_array($filesData['name']) ? $filesData['name'][$i] : $filesData['name'];

    if ($err !== UPLOAD_ERR_OK || !$tmpName) continue;

    if ($i === 0) {
        $firstContentHash = hash_file('sha256', $tmpName);
    }

    try {
        $pageCount = $pdf->setSourceFile($tmpName);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }

        $metadata[] = [
            'name' => htmlspecialchars($fName),
            'startPage' => $startPage,
            'pageCount' => $pageCount
        ];
        $startPage += $pageCount;
    } catch (\Exception $e) {
        // Ignora PDFs que não puderam ser lidos, mas idealmente você pode logar isso
    }
}

if (empty($metadata)) {
    die("Nenhum PDF válido foi enviado ou ocorreu um erro na leitura dos arquivos.");
}

$pdf->Output($destinoCompleto, 'F');
@chmod($destinoCompleto, 0666);

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

try {
    $pdo->exec("ALTER TABLE documents ADD COLUMN contratante_cpf VARCHAR(20) NULL AFTER user_id");
} catch (\Throwable $t) {}

$user_id = $_SESSION['user_id'] ?? 1;
$metadataJson = json_encode($metadata);
$mainTitle = $fileCount > 1 ? $fileCount . ' Documentos' : $metadata[0]['name'];

$stmt = $pdo->prepare("INSERT INTO documents (user_id, contratante_cpf, document_hash, title, status, original_hash, file_path, metadata) VALUES (?, ?, ?, ?, 'Pendente', ?, ?, ?)");
$stmt->execute([
    $user_id,
    $cpf_contratante,
    $docHash,
    $mainTitle,
    $firstContentHash,
    '/' . $nomeArquivoOriginal,
    $metadataJson
]);

function getGeolocationFromIP($ip) {
    if ($ip == '127.0.0.1' || $ip == '::1') return 'Localhost';
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $res = @file_get_contents("http://ip-api.com/json/{$ip}?lang=pt-BR", false, $ctx);
    if ($res) {
        $data = @json_decode($res, true);
        if ($data && isset($data['status']) && $data['status'] === 'success') {
            return $data['city'] . ' - ' . $data['region'];
        }
    }
    return 'Sistema';
}

$document_id = $pdo->lastInsertId();

$ip_cli = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$geo = getGeolocationFromIP($ip_cli);

$stmtLog = $pdo->prepare("INSERT INTO audit_logs (document_id, action_type, actor_name, ip_address, geolocation) VALUES (?, 'Criou', ?, ?, ?)");
$stmtLog->execute([$document_id, $contratante, $ip_cli, $geo]);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname($_SERVER['REQUEST_URI']), '/');

header("Location: preparar_documento.php?hash=" . $docHash);
exit();
