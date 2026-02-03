<?php
$id = $_GET['id'] ?? '';
$dataFile = 'data/requests.json';
$requests = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

if (!isset($requests[$id])) {
    die("Solicitação não encontrada.");
}

$req = $requests[$id];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envio de Documentos | MeuPrazoJus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: #f1f5f9; }
        .upload-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .header { text-align: center; margin-bottom: 30px; }
        .doc-item {
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .doc-item.done {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .btn-upload {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            border: none;
        }
        .btn-upload:disabled { background: #94a3b8; }
        input[type="file"] { display: none; }
    </style>
</head>
<body>

<div class="upload-container">
    <div class="header">
        <h2>Olá, <?= htmlspecialchars($req['client_name']) ?></h2>
        <p class="text-muted">Por favor, envie os documentos solicitados abaixo.</p>
    </div>

    <div class="docs-list">
        <?php foreach ($req['docs'] as $docName): ?>
            <?php 
                // Check if already uploaded
                $isUploaded = false;
                if (isset($req['uploads']) && is_array($req['uploads'])) {
                    foreach($req['uploads'] as $up) {
                        if ($up['doc_type'] === $docName) $isUploaded = true;
                    }
                }
            ?>
            <div class="doc-item <?= $isUploaded ? 'done' : '' ?>" id="item-<?= md5($docName) ?>">
                <div>
                    <strong><?= htmlspecialchars($docName) ?></strong>
                    <div class="status-text" style="font-size:0.8rem; color:#64748b;">
                        <?= $isUploaded ? '✅ Recebido' : 'Pendente' ?>
                    </div>
                </div>
                <div>
                    <?php if (!$isUploaded): ?>
                        <label class="btn-upload">
                            Enviar Arquivo
                            <input type="file" onchange="uploadFile('<?= $id ?>', '<?= $docName ?>', this)">
                        </label>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 30px; text-align: center; color: #64748b; font-size: 0.9rem;">
        <p>MeuPrazoJus - Sistema Seguro de Advocacia</p>
    </div>
</div>

<script>
async function uploadFile(reqId, docType, input) {
    if (input.files.length === 0) return;
    
    const file = input.files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('req_id', reqId);
    formData.append('doc_type', docType);

    const label = input.parentElement;
    const originalText = label.innerText;
    label.childNodes[0].textContent = 'Enviando...';
    
    try {
        const res = await fetch('api/upload_file.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            // Update UI
            const item = document.getElementById('item-' + md5(docType));
            item.classList.add('done');
            item.querySelector('.status-text').innerText = '✅ Recebido';
            label.style.display = 'none'; // Hide button after success
        } else {
            alert('Erro: ' + (data.error || 'Upload falhou'));
            label.childNodes[0].textContent = originalText;
        }
    } catch (e) {
        console.error(e);
        alert('Erro na conexão');
        label.childNodes[0].textContent = originalText;
    }
}

// Simple md5 for ID generation consistency with PHP if needed, 
// or just use a simple clean string replacer for the ID selector.
// Actually, simple string replacement is safer than implementing md5 in JS.
// Let's change the PHP ID generation to be simpler or just use a data attribute.
function md5(str) {
    // Placeholder, actually let's redo the ID logic in the loop above to not rely on this function
    // For now, I'll rely on the PHP executing before this script creates the ID.
    // Wait, the PHP sets the ID="item-md5...". So I need to match that.
    // I can't easily match PHP's md5 in JS without a library.
    // Better strategy: Pass the ID in the function call.
    return str; // This won't work matching PHP md5.
}
// Fix: We need to update the UploadFile call to find the element by a safer way.
</script>

<script>
// Re-implement the visual update logic to not rely on MD5
// We will look for the parent .doc-item of the input
</script>

<script>
// Overriding the previous script block with correct logic
async function uploadFile(reqId, docType, input) {
    if (input.files.length === 0) return;
    
    const file = input.files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('req_id', reqId);
    formData.append('doc_type', docType);

    const label = input.parentElement;
    const docItem = label.closest('.doc-item');
    
    label.childNodes[0].textContent = 'Enviando...';
    
    try {
        const res = await fetch('api/upload_file.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            docItem.classList.add('done');
            docItem.querySelector('.status-text').innerText = '✅ Recebido';
            label.style.display = 'none'; 
        } else {
            alert('Erro: ' + (data.error || 'Upload falhou'));
            label.childNodes[0].textContent = 'Enviar Arquivo';
        }
    } catch (e) {
        console.error(e);
        alert('Erro na conexão');
        label.childNodes[0].textContent = 'Enviar Arquivo';
    }
}
</script>

</body>
</html>
