<?php
session_start();

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$doc_hash = $_GET['hash'] ?? '';
if (!$doc_hash) {
    die("Link inválido.");
}

try {
    $pdo->exec("ALTER TABLE documents ADD COLUMN collected_signatures JSON NULL AFTER signature_positions");
} catch (\Throwable $t) {}

$stmtId = $pdo->prepare("SELECT d.id, d.status, d.title, d.metadata, d.signature_positions, d.collected_signatures, d.contratante_cpf, u.name as contratante FROM documents d JOIN users u ON d.user_id = u.id WHERE d.document_hash = ?");
$stmtId->execute([$doc_hash]);
$docData = $stmtId->fetch();
$metadataDocs = [];
if (!empty($docData['metadata'])) {
    $metadataDocs = json_decode($docData['metadata'], true) ?: [];
}
$signaturePositions = [];
if (!empty($docData['signature_positions'])) {
    $signaturePositions = json_decode($docData['signature_positions'], true) ?: [];
}
$collectedSignatures = [];
if (!empty($docData['collected_signatures'])) {
    $collectedSignatures = json_decode($docData['collected_signatures'], true) ?: [];
}

$externalSigners = [];
foreach ($signaturePositions as $pos) {
    if (isset($pos['signer']) && $pos['signer'] !== 'owner') {
        if (!isset($externalSigners[$pos['signer']])) {
            $externalSigners[$pos['signer']] = $pos['signer_name'] ?? ('Signatário ' . str_replace('signer_', '', $pos['signer']));
        }
    }
}

$pendingSigners = [];
foreach ($externalSigners as $sigId => $name) {
    if (!isset($collectedSignatures[$sigId])) {
        $pendingSigners[$sigId] = $name;
    }
}

if (!$docData) {
    die("Documento não encontrado.");
}
if ($docData['status'] == 'Assinado') {
    die("Este documento já foi assinado.");
}

$contratante = $docData['contratante'] ?? 'Titular da Conta';
$cpf_contratante = $docData['contratante_cpf'] ?? '';

$caminhoPdf = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
$pdf_base64 = '';
if (file_exists($caminhoPdf)) {
    $pdf_base64 = base64_encode(file_get_contents($caminhoPdf));
}
else {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'] ?? '';
    $celular = $_POST['celular'] ?? '';
    $contratado = $_POST['nome_signatario'] ?? 'Signatário';
    $assinatura_base64 = $_POST['signature_image'] ?? null;
    
    if ($assinatura_base64 && strpos($assinatura_base64, 'data:') !== 0) {
        $assinatura_base64 = 'data:image/jpeg;base64,' . $assinatura_base64;
    }

    require 'vendor/autoload.php';
    require_once 'app/Services/DocumentService.php';

    try {
        $caminhoOriginal = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
        if (!file_exists($caminhoOriginal)) {
            die("Arquivo original não encontrado para gerar a assinatura.");
        }

        $neededCount = count($externalSigners);
        if ($neededCount === 0) $neededCount = 1;

        $assignedSignerId = null;
        if (isset($_POST['signer_id']) && isset($externalSigners[$_POST['signer_id']])) {
            $assignedSignerId = $_POST['signer_id'];
            if (isset($collectedSignatures[$assignedSignerId])) {
                die("Esta assinatura já foi coletada.");
            }
        } else if (!empty($pendingSigners)) {
            $assignedSignerId = array_key_first($pendingSigners);
        } else if (empty($externalSigners)) {
            $assignedSignerId = 'signer_' . (count($collectedSignatures) + 1);
        }

        if (!$assignedSignerId || (count($collectedSignatures) >= $neededCount && count($externalSigners) > 0)) {
             die("Todas as assinaturas permitidas já foram coletadas para este documento.");
        }

        $ip_cli = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
        $ua_cli = $_POST['client_ua'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Não identificado';

        $collectedSignatures[$assignedSignerId] = [
            'name' => $contratado,
            'cpf' => $cpf,
            'phone' => $celular,
            'sig' => $assinatura_base64,
            'ip' => $ip_cli,
            'ua' => $ua_cli,
            'date' => date('Y-m-d H:i:s')
        ];

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

        $geo = getGeolocationFromIP($ip_cli);

        $stmtUpdateJson = $pdo->prepare("UPDATE documents SET collected_signatures = ? WHERE id = ?");
        $stmtUpdateJson->execute([json_encode($collectedSignatures), $docData['id']]);

        $stmtLog = $pdo->prepare("INSERT INTO audit_logs (document_id, action_type, actor_name, actor_cpf, actor_phone, ip_address, geolocation) VALUES (?, 'Assinou', ?, ?, ?, ?, ?)");
        $stmtLog->execute([$docData['id'], $contratado, $cpf, $celular, $ip_cli, $geo]);

        if (count($collectedSignatures) >= $neededCount) {
            $documentService = new \App\Services\DocumentService();
            $nomeArquivoFinal = $documentService->assinarDocumentoMulti(
                $caminhoOriginal,
                $doc_hash,
                $contratante,
                $cpf_contratante,
                $collectedSignatures,
                $docData['title'] ?? 'Documento',
                $signaturePositions,
                $metadataDocs
            );

            $stmtUpdate = $pdo->prepare("UPDATE documents SET status = 'Assinado', file_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmtUpdate->execute(['/uploads/' . $nomeArquivoFinal, $docData['id']]);

            if (file_exists($caminhoOriginal)) {
                @unlink($caminhoOriginal);
            }

            header("Location: index.php?novo_doc=" . urlencode("uploads/" . $nomeArquivoFinal));
            exit();
        } else {
            die("<div style='padding:40px; font-family:sans-serif; text-align:center; background:#f8fafc; color:#0f172a; height:100vh; width:100vw; display:flex; flex-direction:column; align-items:center; justify-content:center; box-sizing:border-box;'>
                <div style='background:white; padding:30px; border-radius:12px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); max-width:400px;'>
                    <div style='color:#10b981; margin-bottom:15px;'><svg style='width:64px; height:64px; margin:0 auto;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg></div>
                    <h2 style='margin-top:0;'>Assinatura Registrada!</h2>
                    <p style='color:#64748b;'>Sua assinatura foi processada com sucesso. O documento será concluído e gerado assim que todos os signatários finalizarem o preenchimento.</p>
                    <p style='color:#64748b; font-size:14px; margin-top:20px;'>Pode fechar esta página com segurança.</p>
                </div>
            </div>");
        }
    } catch (\Throwable $e) {
        http_response_code(200);
        die("<div style='padding:20px; background:#ffdce0; color:#900; font-family:sans-serif; border: 1px solid #900;'><strong>ERRO FATAL PHP:</strong><br><br>" . htmlspecialchars($e->getMessage()) . "<br><br><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . " <strong>na linha</strong> " . $e->getLine() . "</div>");
    }
}
?>
<?php require_once __DIR__ . '/app/Views/assinar_link.view.php'; ?>