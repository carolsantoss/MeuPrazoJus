<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Documento - FCSign.</title>
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
<body class="font-sans antialiased h-screen flex flex-col">

    <header class="h-16 px-8 flex items-center justify-between border-b border-slate-700 bg-dark_card">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-xl font-semibold">Enviar Novo Documento</h1>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-6">
        <form action="processar_documento.php" method="POST" enctype="multipart/form-data" class="bg-dark_card border border-slate-700 p-8 rounded-2xl w-full max-w-lg shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Detalhes da Assinatura</h2>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-300 mb-2">Selecione o PDF (*)</label>
                <div class="border-2 border-dashed border-slate-600 rounded-xl p-6 text-center hover:border-brand transition-colors relative cursor-pointer group">
                    <input type="file" name="documento_pdf[]" accept="application/pdf" multiple required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="text-slate-400 group-hover:text-brand transition-colors pointer-events-none">
                        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span id="file-name">Clique ou arraste um ou mais PDFs aqui</span>
                    </div>
                </div>
            </div>

            <div class="mb-4 hidden">
                <input type="hidden" name="nome_contratante" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8 items-end">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Seu CPF (Titular)</label>
                    <input type="text" name="cpf_contratante" placeholder="000.000.000-00" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand font-medium">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Signatário / Outra Parte (opcional)</label>
                    <input type="text" name="nome_contratado" placeholder="Preenchido na assinatura" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand font-medium">
                </div>
            </div>

            <button type="submit" class="w-full bg-brand hover:bg-blue-600 text-white font-medium py-3 rounded-xl transition-colors shadow-lg shadow-brand/20 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Assinar e Gerar Documento
            </button>
        </form>
    </main>

    <script>
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            let files = e.target.files;
            if (files.length === 0) {
                document.getElementById('file-name').textContent = 'Clique ou arraste um ou mais PDFs aqui';
            } else if (files.length === 1) {
                document.getElementById('file-name').textContent = files[0].name;
            } else {
                document.getElementById('file-name').textContent = files.length + ' documentos selecionados';
            }
        });

        const inputCpf = document.querySelector('input[name="cpf_contratante"]');
        if (inputCpf) {
            inputCpf.addEventListener('input', function (e) {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2})/);
                e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' : '') + x[3] + (x[4] ? '-' + x[4] : '');
            });
        }
    </script>
</body>
</html>
