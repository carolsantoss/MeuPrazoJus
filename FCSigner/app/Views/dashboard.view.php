<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCSign. - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#3B82F6',   
                        dark: '#0F172A',    
                        dark_card: '#1E293B',
                        light: '#F8FAFC'    
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0F172A;
            color: #F8FAFC;
        }
    </style>
</head>

<body class="font-sans antialiased flex h-screen overflow-hidden">

    <aside class="w-64 bg-dark_card border-r border-slate-700 flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-slate-700 font-bold text-2xl tracking-tighter">
            <span>FC<span class="text-brand">.</span></span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="index.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-brand/10 text-brand">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                Dashboard
            </a>
            <a href="/index" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Voltar ao MeuPrazoJus
            </a>
        </nav>
        

    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="h-16 px-8 flex items-center justify-between border-b border-slate-700 bg-dark_card">
            <h1 class="text-xl font-semibold">Meus Documentos</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-400">Olá,
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
            </div>
        </header>

        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold">Gerenciar Assinaturas</h2>
                    <p class="text-slate-400 text-sm mt-1">Acompanhe o status dos seus envelopes.</p>
                </div>
                <button onclick="tentarCriarDocumento(<?php echo $pode_criar_novo ? 'true' : 'false'; ?>)"
                    class="bg-brand hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors shadow-lg shadow-brand/20">
                    + Novo Documento
                </button>
            </div>

            <?php if (isset($_GET['link_assinatura'])): ?>
                <div class="mb-8 p-4 bg-brand/10 border border-brand/20 text-brand rounded-xl shadow-lg">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-3 font-semibold text-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            Link de Assinatura Criado!
                        </div>
                        <p class="text-sm text-slate-300">Envie este link abaixo para o contratado (a testar) iniciar a assinatura.</p>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="text" value="<?php echo htmlspecialchars($_GET['link_assinatura']); ?>" readonly class="flex-1 bg-slate-800/50 border border-brand/30 rounded-lg px-4 py-2.5 text-sm font-mono text-slate-400">
                            <a href="<?php echo htmlspecialchars($_GET['link_assinatura']); ?>" target="_blank" class="bg-brand hover:bg-brand/80 text-white px-4 py-2.5 rounded-lg font-medium transition-colors text-sm whitespace-nowrap">
                                Abrir Link
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['novo_doc'])): ?>
                <div class="mb-8 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <strong>Sucesso!</strong> Todas as partes assinaram e a trilha de auditoria foi anexada!
                    </div>
                    <a href="<?php echo htmlspecialchars($_GET['novo_doc']); ?>" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Baixar PDF Final
                    </a>
                </div>
            <?php endif; ?>

            <div class="bg-dark_card rounded-xl border border-slate-700 overflow-hidden">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-800/50 text-slate-400 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-4 font-medium">Nome do Documento</th>
                            <th class="px-6 py-4 font-medium">Data de Criação</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php if (empty($documentos)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">Nenhum documento encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documentos as $doc): ?>
                                <tr class="hover:bg-slate-800/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-200">
                                        <?php if ($doc['status'] === 'Pendente'): ?>
                                            <div class="cursor-pointer group flex items-center gap-2 hover:text-brand transition-colors" onclick="copyLink('<?php echo $doc['document_hash']; ?>')" title="Clique para copiar o link de assinatura">
                                                <?php echo htmlspecialchars($doc['title']); ?>
                                                <svg class="w-4 h-4 text-slate-500 group-hover:text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </div>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($doc['title']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusClass = 'bg-yellow-500/10 text-yellow-500';
                                        if ($doc['status'] == 'Assinado')
                                            $statusClass = 'bg-green-500/10 text-green-500';
                                        if ($doc['status'] == 'Cancelado')
                                            $statusClass = 'bg-red-500/10 text-red-500';
                                        ?>
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $statusClass; ?> border-current">
                                            <?php echo htmlspecialchars($doc['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-3">
                                        <?php if ($doc['status'] === 'Assinado' && !empty($doc['file_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" download class="text-green-500 hover:text-green-400 hover:underline">Download</a>
                                        <?php endif; ?>
                                        <a href="trilha_auditoria.php?doc_id=<?php echo $doc['id']; ?>"
                                            class="text-brand hover:text-blue-400 hover:underline">Auditoria</a>
                                        <a href="excluir_documento.php?id=<?php echo $doc['id']; ?>"
                                            onclick="return confirm('Tem certeza que deseja apagar este documento permanentemente? Isso removerá o arquivo do servidor imediatamente.');"
                                            class="text-red-500 hover:text-red-400 hover:underline">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="upgradeModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex-col items-center justify-center p-4">
        <div class="bg-dark_card border border-slate-700 w-full max-w-md rounded-2xl p-8 shadow-2xl relative">
            <button onclick="fecharModal()" class="absolute top-4 right-4 text-slate-500 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="text-center mb-6">
                <div
                    class="w-16 h-16 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Limite Mensal Atingido</h3>
                <p class="text-slate-400">Você atingiu o limite de envio de 5 documentos do plano grátis este mês.
                    Deseja enviar mais?</p>
            </div>
            <button class="w-full bg-brand hover:bg-blue-600 text-white font-medium py-3 rounded-xl transition-colors">
                Fazer Upgrade Agora
            </button>
        </div>
    </div>

    <script>
        function tentarCriarDocumento(podeCriar) {
            if (podeCriar) {
                window.location.href = 'novo_documento.php'; 
            } else {
                document.getElementById('upgradeModal').classList.remove('hidden');
                document.getElementById('upgradeModal').classList.add('flex');
            }
        }

        function copyLink(hash) {
            const path = window.location.pathname.replace(/\/index\.php$/, '');
            const url = window.location.origin + path + '/assinar_link.php?hash=' + hash;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link de assinatura copiado com sucesso!');
            }).catch(err => {
                alert('Erro ao copiar link, copie manualmente: ' + url);
            });
        }
    </script>
</body>

</html>