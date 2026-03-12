<?php
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$doc_hash = '';

if (!empty($_GET['hash'])) {
    $doc_hash = trim($_GET['hash']);
}

if (!$doc_hash && !empty($_SERVER['PATH_INFO'])) {
    $doc_hash = trim($_SERVER['PATH_INFO'], '/');
}

if (!$doc_hash) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $uri = strtok($uri, '?');
    if (preg_match('#/validar/([^/]+)$#', $uri, $m)) {
        $doc_hash = trim($m[1]);
    }
}

$doc_hash = preg_replace('/[^a-zA-Z0-9_\-]/', '', $doc_hash);


$stmtDoc = $pdo->prepare("
    SELECT d.id, d.document_hash, d.status, d.file_path, d.created_at, d.updated_at,
           u.name AS contratante_nome, d.contratante_cpf
    FROM documents d
    JOIN users u ON d.user_id = u.id
    WHERE d.document_hash = ?
");
$stmtDoc->execute([$doc_hash]);
$documento = $stmtDoc->fetch(PDO::FETCH_ASSOC);


$logs = [];
$signatarios = [];

if ($documento) {
    $stmtLogs = $pdo->prepare("
        SELECT action_type, actor_name, actor_cpf, actor_phone, ip_address, created_at
        FROM audit_logs
        WHERE document_id = ?
        ORDER BY created_at ASC
    ");
    $stmtLogs->execute([$documento['id']]);
    $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

    $stmtSign = $pdo->prepare("
        SELECT DISTINCT actor_name AS name, actor_cpf AS cpf, actor_phone AS phone
        FROM audit_logs
        WHERE document_id = ? AND action_type = 'Assinou'
    ");
    $stmtSign->execute([$documento['id']]);
    $signatarios = $stmtSign->fetchAll(PDO::FETCH_ASSOC);
}


function formatCpf($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    return $cpf ?: '—';
}

function formatPhone($phone) {
    if (!$phone) return '—';
    if (strpos($phone, '+') === false) {
        $phone = '+' . preg_replace('/\D/', '', $phone);
    }
    return $phone;
}

function formatDate($dt) {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return date('d/m/Y \à\s H:i', $ts);
}

function actionLabel($action) {
    $map = [
        'Criou'       => 'criou o documento',
        'Visualizou'  => 'visualizou o documento',
        'Assinou'     => 'assinou eletronicamente',
    ];
    return $map[$action] ?? $action;
}

function actionColor($action) {
    switch ($action) {
        case 'Criou':      return '#94a3b8';
        case 'Visualizou': return '#3b82f6';
        case 'Assinou':    return '#22c55e';
        default:           return '#94a3b8';
    }
}

function actionIcon($action) {
    switch ($action) {
        case 'Criou':
            return '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>';
        case 'Visualizou':
            return '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        case 'Assinou':
            return '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>';
        default:
            return '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação de Documento – FCSign.</title>
    <meta name="description" content="Verifique a autenticidade e validade de documentos assinados eletronicamente pelo FCSign.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0f172a;
            --card:      #1e293b;
            --card2:     #263045;
            --border:    rgba(255,255,255,.08);
            --brand:     #3b82f6;
            --brand-dim: rgba(59,130,246,.15);
            --green:     #22c55e;
            --green-dim: rgba(34,197,94,.12);
            --red:       #ef4444;
            --red-dim:   rgba(239,68,68,.12);
            --text:      #f8fafc;
            --muted:     #94a3b8;
            --radius:    14px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }


        header {
            height: 64px;
            background: var(--card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-weight: 700;
            font-size: 22px;
            letter-spacing: -.5px;
            color: var(--text);
        }
        .logo span { color: var(--brand); }

        .header-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--brand-dim);
            border: 1px solid rgba(59,130,246,.3);
            border-radius: 100px;
            padding: 6px 14px;
            font-size: 13px;
            color: var(--brand);
            font-weight: 500;
        }
        .header-badge svg { width: 15px; height: 15px; }


        main {
            flex: 1;
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 16px 60px;
        }


        .section-title {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 20px;
        }


        .status-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            border-radius: var(--radius);
            margin-bottom: 24px;
        }
        .status-banner.valid {
            background: var(--green-dim);
            border: 1px solid rgba(34,197,94,.3);
        }
        .status-banner.invalid {
            background: var(--red-dim);
            border: 1px solid rgba(239,68,68,.3);
        }
        .status-banner.notfound {
            background: rgba(148,163,184,.08);
            border: 1px solid var(--border);
        }

        .status-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .valid .status-icon   { background: rgba(34,197,94,.2); color: var(--green); }
        .invalid .status-icon { background: rgba(239,68,68,.2); color: var(--red); }
        .notfound .status-icon { background: rgba(148,163,184,.1); color: var(--muted); }

        .status-text h2 { font-size: 18px; font-weight: 700; }
        .valid .status-text h2   { color: var(--green); }
        .invalid .status-text h2 { color: var(--red); }
        .notfound .status-text h2 { color: var(--muted); }
        .status-text p  { font-size: 13px; color: var(--muted); margin-top: 4px; }


        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-item label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .info-item .value {
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            word-break: break-all;
        }
        .info-item .value.hash {
            font-size: 12px;
            font-family: 'Courier New', monospace;
            color: var(--brand);
            background: var(--brand-dim);
            padding: 6px 10px;
            border-radius: 8px;
            display: inline-block;
            word-break: break-all;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-status.signed   { background: var(--green-dim); color: var(--green); border: 1px solid rgba(34,197,94,.3); }
        .badge-status.pending  { background: rgba(234,179,8,.1); color: #eab308; border: 1px solid rgba(234,179,8,.3); }


        .signer-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }
        .signer-card:last-child { border-bottom: none; padding-bottom: 0; }
        .signer-card:first-child { padding-top: 0; }

        .signer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--green-dim);
            border: 1px solid rgba(34,197,94,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            color: var(--green);
            flex-shrink: 0;
        }

        .signer-info { flex: 1; }
        .signer-info .signer-name { font-weight: 600; font-size: 14px; }
        .signer-info .signer-cpf  { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .signer-check { color: var(--green); }


        .timeline { position: relative; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 23px;
            top: 4px;
            bottom: 4px;
            width: 1px;
            background: var(--border);
        }

        .timeline-item {
            display: flex;
            gap: 16px;
            padding: 0 0 20px 0;
            position: relative;
        }
        .timeline-item:last-child { padding-bottom: 0; }

        .tl-dot {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            border: 2px solid var(--bg);
        }

        .tl-body { flex: 1; padding-top: 10px; }
        .tl-title { font-size: 14px; font-weight: 500; line-height: 1.4; }
        .tl-title strong { font-weight: 700; }
        .tl-meta  { font-size: 12px; color: var(--muted); margin-top: 4px; }


        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--brand);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            transition: background .2s, transform .1s;
            border: none;
            cursor: pointer;
        }
        .btn-download:hover { background: #2563eb; transform: translateY(-1px); }


        .nf-box {
            text-align: center;
            padding: 60px 20px;
        }
        .nf-box svg { color: var(--muted); margin: 0 auto 20px; display: block; }
        .nf-box h2 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .nf-box p  { color: var(--muted); font-size: 14px; max-width: 380px; margin: 0 auto; }


        footer {
            border-top: 1px solid var(--border);
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }
        footer a { color: var(--brand); text-decoration: none; }


        @media (max-width: 540px) {
            .status-banner { flex-direction: column; text-align: center; }
            .header-badge span { display: none; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">FC<span>.</span></div>
    <div class="header-badge">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <span>Verificação de Autenticidade</span>
    </div>
</header>

<main>
<?php if (!$doc_hash): ?>


    <div class="nf-box">
        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" opacity=".4">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h2>Código de validação não informado</h2>
        <p>Acesse o link completo presente no rodapé do documento assinado:<br>
           <code style="color:#3b82f6">meuprazojus.com.br/validar/<em>código</em></code>
        </p>
    </div>

<?php elseif (!$documento): ?>


    <div class="status-banner notfound">
        <div class="status-icon">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div class="status-text">
            <h2>Documento não encontrado</h2>
            <p>Nenhum documento corresponde ao código <strong><?= htmlspecialchars($doc_hash) ?></strong>.</p>
        </div>
    </div>

<?php else:
    $assinado = ($documento['status'] === 'Assinado');
    $pdfUrl   = !empty($documento['file_path'])
                    ? rtrim('https://meuprazojus.com.br', '/') . '/' . ltrim($documento['file_path'], '/')
                    : null;
?>


    <div class="status-banner <?= $assinado ? 'valid' : 'invalid' ?>">
        <div class="status-icon">
            <?php if ($assinado): ?>
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            <?php else: ?>
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
                </svg>
            <?php endif; ?>
        </div>
        <div class="status-text">
            <h2><?= $assinado ? 'Documento válido e autenticado' : 'Documento pendente de assinatura' ?></h2>
            <p><?= $assinado
                    ? 'Este documento foi assinado eletronicamente e sua autenticidade é confirmada.'
                    : 'Este documento existe mas ainda não foi assinado por todas as partes.' ?>
            </p>
        </div>
    </div>


    <p class="section-title">Informações do documento</p>
    <div class="card">
        <div class="info-grid">
            <div class="info-item">
                <label>Identificador único</label>
                <div class="value hash"><?= htmlspecialchars($doc_hash) ?></div>
            </div>
            <div class="info-item">
                <label>Status</label>
                <div class="value">
                    <?php if ($assinado): ?>
                        <span class="badge-status signed">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                            Assinado
                        </span>
                    <?php else: ?>
                        <span class="badge-status pending">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                            Pendente
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item">
                <label>Criado em</label>
                <div class="value"><?= htmlspecialchars(formatDate($documento['created_at'])) ?></div>
            </div>
            <?php if ($assinado): ?>
            <div class="info-item">
                <label>Assinado em</label>
                <div class="value"><?= htmlspecialchars(formatDate($documento['updated_at'])) ?></div>
            </div>
            <?php endif; ?>
            <div class="info-item">
                <label>Titular da conta</label>
                <div class="value"><?= htmlspecialchars($documento['contratante_nome'] ?? '—') ?></div>
            </div>
        </div>
    </div>

    <?php if (!empty($signatarios)): ?>

    <p class="section-title">Signatários</p>
    <div class="card">
        <?php foreach ($signatarios as $s): ?>
            <div class="signer-card">
                <div class="signer-avatar">
                    <?= mb_strtoupper(mb_substr(trim($s['name']), 0, 1)) ?>
                </div>
                <div class="signer-info">
                    <div class="signer-name"><?= htmlspecialchars($s['name']) ?></div>
                    <div class="signer-cpf">CPF: <?= htmlspecialchars(formatCpf($s['cpf'])) ?>
                        <?php if (!empty($s['phone'])): ?>
                            &nbsp;·&nbsp; <?= htmlspecialchars(formatPhone($s['phone'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="signer-check">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($logs)): ?>

    <p class="section-title">Trilha de auditoria</p>
    <div class="card">
        <div class="timeline">
            <?php foreach ($logs as $log):
                $color = actionColor($log['action_type']);
            ?>
            <div class="timeline-item">
                <div class="tl-dot" style="background: <?= $color ?>22; color: <?= $color ?>;">
                    <?= actionIcon($log['action_type']) ?>
                </div>
                <div class="tl-body">
                    <div class="tl-title">
                        <strong><?= htmlspecialchars($log['actor_name']) ?></strong>
                        <?= htmlspecialchars(actionLabel($log['action_type'])) ?>
                    </div>
                    <div class="tl-meta">
                        <?= htmlspecialchars(formatDate($log['created_at'])) ?>
                        <?php if (!empty($log['ip_address'])): ?>
                            &nbsp;·&nbsp; IP: <?= htmlspecialchars($log['ip_address']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($assinado && $pdfUrl): ?>

    <p class="section-title">Documento assinado</p>
    <div class="card" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="font-weight:600; margin-bottom:4px;">documento_<?= htmlspecialchars($doc_hash) ?>.pdf</div>
            <div style="font-size:13px; color:var(--muted);">Versão final com página de assinaturas e QR code de validação.</div>
        </div>
        <a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank" class="btn-download">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Baixar PDF
        </a>
    </div>
    <?php endif; ?>

<?php endif; ?>
</main>

<footer>
    Validação de documento eletrônico &mdash;
    <a href="https://meuprazojus.com.br">MeuPrazoJus</a> &middot; FCSign.
    &nbsp;|&nbsp; As informações exibidas refletem o estado atual do banco de dados.
</footer>

</body>
</html>
