<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinar Documento - FCSign.</title>
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
        body { background-color: #0F172A; color: #F8FAFC; }
    </style>
</head>
<body class="font-sans antialiased h-screen flex flex-col bg-dark">

    <header class="h-16 px-6 flex flex-shrink-0 items-center justify-between border-b border-slate-700 bg-dark_card z-10">
        <div class="flex items-center gap-4">
            <div class="font-bold text-2xl tracking-tighter">
                FC<span class="text-brand">.</span>
            </div>
            <div class="h-6 w-px bg-slate-700 hidden sm:block"></div>
            <h1 class="text-lg font-medium text-slate-200 hidden sm:block">Revisão de Documento</h1>
        </div>
        <div class="text-sm text-slate-400">
            Assinante: <strong class="text-slate-200"><?php echo htmlspecialchars($nome_pre_preenchido); ?></strong>
        </div>
    </header>

    <main class="flex-1 bg-slate-900 border-b border-slate-700 relative">
        <iframe src="uploads/original_<?php echo htmlspecialchars($doc_hash); ?>.pdf" class="w-full h-full border-none" title="Documento Original"></iframe>
    </main>

    <footer class="bg-dark_card p-4 md:px-8 md:py-5 flex flex-col md:flex-row items-center justify-between gap-4 flex-shrink-0">
        <label class="flex items-center gap-3 cursor-pointer group">
            <div class="relative flex items-center justify-center">
                <input type="checkbox" id="checkConcordo" class="peer sr-only" onchange="toggleBotaoAssinar()">
                <div class="w-6 h-6 border-2 border-slate-500 rounded bg-slate-800 peer-checked:bg-brand peer-checked:border-brand transition-colors"></div>
                <svg class="w-4 h-4 text-white absolute inset-0 m-auto opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="text-slate-300 group-hover:text-white transition-colors text-sm sm:text-base font-medium select-none">
                Eu revisei e concordo com os termos do contrato acima.
            </span>
        </label>
        
        <button type="button" id="btnAssinar" disabled onclick="mostrarModal()" class="w-full md:w-auto bg-slate-700 text-slate-400 cursor-not-allowed font-medium py-3 px-8 rounded-xl transition-all flex items-center justify-center gap-2 select-none">
            Iniciar Assinatura
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </button>
    </footer>

    <div id="coletaModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex-col items-center justify-center p-4">
        <div class="bg-dark_card border border-slate-700 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-slate-700">
                <h3 class="text-lg font-bold text-white">Verificação de Identidade</h3>
                <p class="text-sm text-slate-400 mt-1">Confirme seus dados para continuar.</p>
            </div>
            
            <form action="" method="POST" class="p-6">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nome Completo</label>
                    <input type="text" value="<?php echo htmlspecialchars($nome_pre_preenchido); ?>" readonly class="w-full bg-slate-800/50 border border-slate-600 rounded-lg px-4 py-2.5 text-slate-400 cursor-not-allowed">
                    <p class="text-xs text-brand mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Solicitado pelo criador do documento.
                    </p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-300 mb-2">CPF (*)</label>
                    <input type="text" name="cpf" required placeholder="000.000.000-00" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Telefone Celular (*)</label>
                    <input type="text" name="celular" required placeholder="(00) 00000-0000" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                <div class="flex gap-4">
                    <button type="button" onclick="fecharModal()" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-2.5 rounded-xl transition-colors text-sm">
                        Voltar para a leitura
                    </button>
                    <button type="submit" class="flex-1 bg-brand hover:bg-blue-600 text-white font-medium py-2.5 rounded-xl transition-colors shadow-lg shadow-brand/20">
                        Confirmar e Assinar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleBotaoAssinar() {
            const checkbox = document.getElementById('checkConcordo');
            const btn = document.getElementById('btnAssinar');
            
            if (checkbox.checked) {
                btn.disabled = false;
                btn.className = "w-full md:w-auto bg-brand hover:bg-blue-600 text-white font-medium py-3 px-8 rounded-xl transition-all shadow-lg shadow-brand/20 flex items-center justify-center gap-2 select-none cursor-pointer";
            } else {
                btn.disabled = true;
                btn.className = "w-full md:w-auto bg-slate-700 text-slate-400 font-medium py-3 px-8 rounded-xl transition-all flex items-center justify-center gap-2 select-none cursor-not-allowed";
            }
        }

        function mostrarModal() {
            document.getElementById('coletaModal').classList.remove('hidden');
            document.getElementById('coletaModal').classList.add('flex');
        }

        function fecharModal() {
            document.getElementById('coletaModal').classList.add('hidden');
            document.getElementById('coletaModal').classList.remove('flex');
        }
    </script>
</body>
</html>
