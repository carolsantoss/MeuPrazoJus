<?php
date_default_timezone_set('America/Sao_Paulo');

$doc_id = isset($_GET['doc_id']) ? (int) $_GET['doc_id'] : 0;

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$stmtDoc = $pdo->prepare("SELECT document_hash, original_hash, updated_at FROM documents WHERE id = ?");
$stmtDoc->execute([$doc_id]);
$documento = $stmtDoc->fetch();

if (!$documento) {
    die("Documento não encontrado na trilha de auditoria.");
}

$stmtLogs = $pdo->prepare("SELECT * FROM audit_logs WHERE document_id = ? ORDER BY created_at ASC");
$stmtLogs->execute([$doc_id]);
$logs = $stmtLogs->fetchAll();

$stmtSigners = $pdo->prepare("SELECT DISTINCT actor_name as name, actor_cpf as cpf, 'Assinado' as status FROM audit_logs WHERE document_id = ? AND action_type = 'Assinou'");
$stmtSigners->execute([$doc_id]);
$signatarios = $stmtSigners->fetchAll();


function formatCpf($cpf)
{
    return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", preg_replace("/\D/", "", $cpf));
}

function formatPhone($phone)
{
    if (strpos($phone, '+') === false) {
        $phone = '+' . preg_replace("/\D/", "", $phone);
    }
    return $phone;
}

function getIconForAction($action)
{
    switch ($action) {
        case 'Criou':
            return '<svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>';
        case 'Visualizou':
            return '<svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
        case 'Assinou':
            return '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>';
        default:
            return '';
    }
}
?>
<?php require_once __DIR__ . '/app/Views/trilha_auditoria.view.php'; ?>