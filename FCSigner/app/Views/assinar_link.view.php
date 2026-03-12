<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinar Documento - FCSign.</title>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&display=swap" rel="stylesheet">
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

        /* Correção de scroll do modal apenas no mobile */
        @media (max-width: 768px) {
            #coletaModal {
                overflow-y: scroll !important;
                -webkit-overflow-scrolling: touch;
                align-items: flex-start !important;
                justify-content: flex-start !important;
            }
            #coletaModal > div {
                margin: 1rem auto;
            }
        }
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
            Assinando documento seguro
        </div>
    </header>

    <main class="flex-1 bg-slate-900 border-b border-slate-700 relative flex items-center justify-center">
        <?php if (!empty($pdf_base64)): ?>
            <object data="data:application/pdf;base64,<?php echo $pdf_base64; ?>" type="application/pdf" class="w-full h-full border-none" title="Documento Original">
                <iframe src="data:application/pdf;base64,<?php echo $pdf_base64; ?>" class="w-full h-full border-none" title="Documento Original"></iframe>
            </object>
        <?php else: ?>
            <div class="text-slate-400 flex flex-col items-center gap-3">
                <p class="text-red-400 text-sm">Não foi possível carregar o documento fisico no servidor.</p>
            </div>
        <?php endif; ?>
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

    <div id="coletaModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex-col items-center justify-center p-4 overflow-y-auto">
        <div class="bg-dark_card border border-slate-700 w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl my-8">
            <div class="p-6 border-b border-slate-700">
                <h3 class="text-lg font-bold text-white">Verificação de Identidade</h3>
                <p class="text-sm text-slate-400 mt-1">Confirme seus dados para continuar.</p>
            </div>
            
            <form action="" method="POST" class="p-6">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nome Completo do Signatário (*)</label>
                    <input type="text" name="nome_signatario" required placeholder="Digite seu nome completo" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-300 mb-2">CPF (*)</label>
                    <input type="text" name="cpf" required placeholder="000.000.000-00" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Telefone Celular (*)</label>
                    <input type="text" name="celular" required placeholder="(00) 00000-0000" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Sua Assinatura (*)</label>
                    <div class="border border-slate-600 rounded-lg overflow-hidden bg-slate-800">
                        <div class="flex border-b border-slate-700 bg-slate-900">
                            <button type="button" id="tab-draw" class="flex-1 py-2 text-sm font-medium text-brand border-b-2 border-brand" onclick="switchTab('draw')">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                DESENHAR
                            </button>
                            <button type="button" id="tab-type" class="flex-1 py-2 text-sm font-medium text-slate-400 hover:text-slate-200" onclick="switchTab('type')">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                ESCREVER
                            </button>
                        </div>
                        
                        <div id="area-draw" class="relative bg-white h-40">
                            <canvas id="signature-pad" class="w-full h-full touch-none cursor-crosshair"></canvas>
                            <button type="button" onclick="clearSignature()" class="absolute bottom-2 right-4 text-xs font-semibold text-slate-500 hover:text-red-500 transition-colors bg-white/80 px-2 py-1 rounded">Limpar</button>
                        </div>
                        
                        <div id="area-type" class="hidden bg-white h-40 flex items-center justify-center p-6 text-center overflow-x-hidden">
                            <div id="typed-signature" class="text-black whitespace-nowrap" style="font-family: 'Caveat', cursive; font-size: 3rem; line-height: 1;">Seu Nome</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="signature_type" id="signature_type" value="draw">
                    <input type="hidden" name="signature_image" id="signature_image" required>
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
            if (document.getElementById('signature_type').value === 'draw') {
                setTimeout(resizeCanvas, 50);
            }
        }

        function fecharModal() {
            document.getElementById('coletaModal').classList.add('hidden');
            document.getElementById('coletaModal').classList.remove('flex');
        }

        const inputCpf = document.querySelector('input[name="cpf"]');
        if (inputCpf) {
            inputCpf.addEventListener('input', function (e) {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2})/);
                e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' : '') + x[3] + (x[4] ? '-' + x[4] : '');
            });
        }

        const inputTel = document.querySelector('input[name="celular"]');
        if (inputTel) {
            inputTel.addEventListener('input', function (e) {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
            });
        }
        
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let hasSignature = false;
        
        function resizeCanvas() {
            const rect = canvas.parentNode.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#0f172a';
        }
        
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const evt = e.touches ? e.touches[0] : e;
            return {
                x: evt.clientX - rect.left,
                y: evt.clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasSignature = true;
            ctx.beginPath();
            const pos = getPos(e);
            ctx.moveTo(pos.x, pos.y);
            if(e.cancelable) e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            if(e.cancelable) e.preventDefault();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, {passive: false});
        canvas.addEventListener('touchmove', draw, {passive: false});
        canvas.addEventListener('touchend', stopDrawing);

        function clearSignature() {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            hasSignature = false;
        }

        const nameInput = document.querySelector('input[name="nome_signatario"]');
        const typedPreview = document.getElementById('typed-signature');
        if (nameInput) {
            nameInput.addEventListener('input', (e) => {
                typedPreview.textContent = e.target.value || 'Seu Nome';
            });
        }

        function switchTab(tab) {
            document.getElementById('signature_type').value = tab;
            const tabDrawInfo = document.getElementById('tab-draw');
            const tabTypeInfo = document.getElementById('tab-type');
            
            if (tab === 'draw') {
                tabDrawInfo.classList.add('text-brand', 'border-b-2', 'border-brand');
                tabDrawInfo.classList.remove('text-slate-400', 'hover:text-slate-200');
                
                tabTypeInfo.classList.remove('text-brand', 'border-b-2', 'border-brand');
                tabTypeInfo.classList.add('text-slate-400', 'hover:text-slate-200');
                
                document.getElementById('area-draw').classList.remove('hidden');
                document.getElementById('area-type').classList.add('hidden');
                resizeCanvas();
            } else {
                tabTypeInfo.classList.add('text-brand', 'border-b-2', 'border-brand');
                tabTypeInfo.classList.remove('text-slate-400', 'hover:text-slate-200');
                
                tabDrawInfo.classList.remove('text-brand', 'border-b-2', 'border-brand');
                tabDrawInfo.classList.add('text-slate-400', 'hover:text-slate-200');
                
                document.getElementById('area-type').classList.remove('hidden');
                document.getElementById('area-type').classList.add('flex');
                document.getElementById('area-draw').classList.add('hidden');
                
                typedPreview.textContent = nameInput.value || 'Seu Nome';
            }
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const sigType = document.getElementById('signature_type').value;
            const sigInput = document.getElementById('signature_image');
            
            if (sigType === 'draw') {
                if (!hasSignature) {
                    e.preventDefault();
                    alert('Por favor, desenhe sua assinatura para continuar.');
                    return;
                }
                sigInput.value = canvas.toDataURL('image/jpeg', 0.2).split(',')[1];
            } else {
                const nameVal = nameInput.value.trim();
                if (!nameVal) {
                    e.preventDefault();
                    alert('Por favor, preencha seu nome para gerar a assinatura.');
                    return;
                }
                
                const offCanvas = document.createElement('canvas');
                offCanvas.width = 700;
                offCanvas.height = 160;
                const octx = offCanvas.getContext('2d');

                octx.fillStyle = "#ffffff";
                octx.fillRect(0, 0, offCanvas.width, offCanvas.height);

                octx.fillStyle = "#0f172a";
                octx.textAlign = "center";
                octx.textBaseline = "middle";

                let fontSize = 52;
                octx.font = `400 ${fontSize}px 'Caveat', cursive`;
                while (octx.measureText(nameVal).width > offCanvas.width - 40 && fontSize > 20) {
                    fontSize -= 2;
                    octx.font = `400 ${fontSize}px 'Caveat', cursive`;
                }
                octx.fillText(nameVal, offCanvas.width / 2, offCanvas.height / 2);

                sigInput.value = offCanvas.toDataURL('image/jpeg', 0.2).split(',')[1];
            }
        });
    </script>
</body>
</html>
