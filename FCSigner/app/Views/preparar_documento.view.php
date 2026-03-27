<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preparar Documento - FCSign.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: '#3B82F6', dark: '#0F172A', dark_card: '#1E293B', light: '#F8FAFC' } } }
        }
    </script>
    <style>
        body { background-color: #0F172A; color: #F8FAFC; overflow: hidden; }
        .pdf-page-container { position: relative; margin: 20px auto; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #1e293b; background: white; }
        .marker { position: absolute; border: 2px dashed #ef4444; background: rgba(239, 68, 68, 0.2); cursor: move; display: flex; align-items: stretch; justify-content: stretch; user-select: none; }
        .marker-title { position: absolute; top: 0; left: 0; right: 0; background: #ef4444; color: white; font-size: 10px; font-weight: bold; text-align: center; padding: 2px; }
        .marker-remove { position: absolute; top: -10px; right: -10px; background: #fff; border-radius: 50%; color: #dc2626; width: 20px; height: 20px; text-align: center; line-height: 20px; cursor: pointer; border: 1px solid #dc2626; font-size: 14px; z-index: 10; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.5); }
        .marker-resize { position: absolute; bottom: 0; right: 0; width: 15px; height: 15px; background: #ef4444; cursor: se-resize; }
    </style>
</head>
<body class="font-sans antialiased h-screen flex flex-col">
    <header class="h-16 px-6 flex items-center justify-between border-b border-slate-700 bg-dark_card z-10 flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold">Posicionar Assinatura</h1>
            <p class="text-sm text-slate-400 hidden md:block">Clique no documento para adicionar onde o signatário deverá assinar.</p>
        </div>
        <button id="btnSalvar" class="bg-brand hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition-colors shadow-lg shadow-brand/20">
            Concluir e Gerar Link
        </button>
    </header>

    <div class="flex-1 overflow-hidden bg-slate-900 flex relative">
        <?php if (!empty($metadataDocs) && count($metadataDocs) > 1): ?>
        <aside class="w-64 bg-dark_card border-r border-slate-700 flex flex-col flex-shrink-0 z-10 overflow-y-auto hidden md:flex">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold text-slate-200">Arquivos do Envelope</h3>
                <p class="text-xs text-slate-400 mt-1">Navegue pelas páginas correspondentes.</p>
            </div>
            <ul class="flex-1 p-2 space-y-1">
                <?php foreach($metadataDocs as $index => $meta): ?>
                    <li>
                        <button onclick="mudarPaginaScroll(<?php echo $meta['startPage']; ?>, this)" class="w-full text-left px-3 py-3 rounded-lg flex items-center gap-3 transition-colors pdf-item <?php echo $index === 0 ? 'bg-brand/10 text-brand' : 'text-slate-400 hover:bg-slate-800'; ?>">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="text-sm truncate" title="<?php echo htmlspecialchars($meta['name']); ?>"><?php echo htmlspecialchars($meta['name']); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php endif; ?>

        <main class="flex-1 overflow-auto p-4 w-full" id="pdf-viewer">
            <div class="flex flex-col items-center gap-4" id="pages-container"></div>
        </main>

        <!-- Right sidebar for Signer selection -->
        <aside class="w-64 bg-dark_card border-l border-slate-700 flex flex-col flex-shrink-0 z-10 overflow-y-auto hidden md:flex">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold text-slate-200">Signatários</h3>
                <p class="text-xs text-slate-400 mt-1">Selecione de quem é a assinatura que deseja posicionar.</p>
            </div>
            <div class="flex-1 p-4 space-y-3" id="signer-list-container">
                <label class="block cursor-pointer">
                    <input type="radio" name="active_signer" value="owner" class="peer sr-only" checked>
                    <div class="w-full text-left px-3 py-3 rounded-lg border border-slate-700 text-slate-300 peer-checked:bg-blue-600/20 peer-checked:border-blue-500 peer-checked:text-blue-400 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="text-sm font-medium">Assinante Real (Dono)</span>
                        </div>
                    </div>
                </label>
                <label class="block cursor-pointer mt-3" id="label_signer_1">
                    <input type="radio" name="active_signer" value="signer_1" class="peer sr-only">
                    <div class="w-full text-left px-3 py-3 rounded-lg border border-slate-700 text-slate-300 peer-checked:bg-red-600/20 peer-checked:border-red-500 peer-checked:text-red-400 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2 w-full mr-2">
                            <div class="w-3 h-3 rounded-full bg-red-500 shrink-0"></div>
                            <input type="text" id="name_signer_1" value="Signatário 1" class="bg-transparent border-none text-sm font-medium focus:ring-0 w-full text-slate-200" onclick="this.closest('label').querySelector('input[type=radio]').checked = true" onfocus="this.closest('label').querySelector('input[type=radio]').checked = true" onkeydown="event.stopPropagation()">
                        </div>
                        <button type="button" onclick="removerSignatario('signer_1', event)" class="text-slate-500 hover:text-red-500 transition-colors shrink-0 flex items-center justify-center p-1" title="Remover Signatário">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </label>
            </div>
            <div class="p-4 border-t border-slate-700">
                <button type="button" onclick="adicionarSignatario()" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Adicionar Signatário
                </button>
            </div>
        </aside>
    </div>

    <script>
        const pdfData = atob('<?php echo $pdf_base64; ?>');
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        
        const container = document.getElementById('pages-container');
        let markers = [];
        let isDragging = false, isResizing = false, currentMarker = null;
        let startX, startY, startLeft, startTop, startW, startH;

        function renderPDF() {
            const loadingTask = pdfjsLib.getDocument({data: pdfData});
            loadingTask.promise.then(async (pdf) => {
                const scale = 1.5;

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({ scale });
                    
                    const wrapper = document.createElement('div');
                    wrapper.className = 'pdf-page-container';
                    wrapper.id = 'page-container-' + i;
                    wrapper.style.width = viewport.width + 'px';
                    wrapper.style.height = viewport.height + 'px';
                    wrapper.dataset.page = i;
                    wrapper.dataset.width = viewport.width;
                    wrapper.dataset.height = viewport.height;

                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    canvas.style.display = 'block';

                    const ctx = canvas.getContext('2d');
                    await page.render({ canvasContext: ctx, viewport }).promise;

                    wrapper.appendChild(canvas);
                    
                    wrapper.addEventListener('click', (e) => {
                        if(e.target === canvas) addMarker(e, wrapper, i);
                    });

                    container.appendChild(wrapper);
                }
            });
        }
        
        function mudarPaginaScroll(pagina, btn) {
            document.querySelectorAll('.pdf-item').forEach(el => {
                el.classList.remove('bg-brand/10', 'text-brand');
                el.classList.add('text-slate-400', 'hover:bg-slate-800');
            });
            btn.classList.add('bg-brand/10', 'text-brand');
            btn.classList.remove('text-slate-400', 'hover:bg-slate-800');

            const targetPage = document.getElementById('page-container-' + pagina);
            if (targetPage) {
                targetPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        let signerCount = 1;
        const colorPalette = [
            { val: 'red', hex: '#ef4444' },
            { val: 'green', hex: '#22c55e' },
            { val: 'yellow', hex: '#eab308' },
            { val: 'purple', hex: '#a855f7' },
            { val: 'orange', hex: '#f97316' },
            { val: 'pink', hex: '#ec4899' },
            { val: 'teal', hex: '#14b8a6' },
            { val: 'rose', hex: '#f43f5e' }
        ];

        function adicionarSignatario() {
            signerCount++;
            const id = 'signer_' + signerCount;
            const container = document.getElementById('signer-list-container');
            const colorObj = colorPalette[(signerCount - 1) % colorPalette.length];
            const c = colorObj.val;
            
            const html = `
                <label class="block cursor-pointer mt-3" id="label_${id}">
                    <input type="radio" name="active_signer" value="${id}" class="peer sr-only" checked>
                    <div class="w-full text-left px-3 py-3 rounded-lg border border-slate-700 text-slate-300 peer-checked:bg-${c}-600/20 peer-checked:border-${c}-500 peer-checked:text-${c}-400 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2 w-full mr-2">
                            <div class="w-3 h-3 rounded-full bg-${c}-500 shrink-0"></div>
                            <input type="text" id="name_${id}" value="Signatário ${signerCount}" class="bg-transparent border-none text-sm font-medium focus:ring-0 w-full text-slate-200" onclick="this.closest('label').querySelector('input[type=radio]').checked = true" onfocus="this.closest('label').querySelector('input[type=radio]').checked = true" onkeydown="event.stopPropagation()">
                        </div>
                        <button type="button" onclick="removerSignatario('${id}', event)" class="text-slate-500 hover:text-red-500 transition-colors shrink-0 flex items-center justify-center p-1" title="Remover Signatário">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </label>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removerSignatario(id, event) {
            event.preventDefault();
            event.stopPropagation();
            
            const el = document.getElementById('label_' + id);
            if (el) el.remove();
            
            markers.forEach(m => {
                if (m.signer === id) {
                    m.markerEl.remove();
                }
            });
            markers = markers.filter(m => m.signer !== id);
            
            const checked = document.querySelector('input[name="active_signer"]:checked');
            if (!checked) {
                document.querySelector('input[value="owner"]').checked = true;
            }
        }

        function getActiveSigner() {
            const checked = document.querySelector('input[name="active_signer"]:checked');
            if (!checked) return { value: 'owner', label: 'Assinante Real', color: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' };
            
            const value = checked.value;
            if (value === 'owner') { 
                return { value: 'owner', label: 'Assinante Real', color: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' }; 
            }
            
            const index = parseInt(value.split('_')[1], 10) || 1;
            const c = colorPalette[(index - 1) % colorPalette.length];
            
            const hex = c.hex;
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            
            const nameInput = document.getElementById('name_' + value);
            const label = nameInput ? nameInput.value : ('Signatário ' + index);

            return {
                value: value,
                label: label,
                color: hex,
                bg: `rgba(${r}, ${g}, ${b}, 0.2)`
            };
        }

        function addMarker(e, wrapper, pageIndex) {
            const rect = wrapper.getBoundingClientRect();
            // width ~150px, height ~50px default
            const w = 150, h = 60;
            let left = e.clientX - rect.left - (w/2);
            let top = e.clientY - rect.top - (h/2);
            
            if (left < 0) left = 0;
            if (top < 0) top = 0;
            if (left + w > rect.width) left = rect.width - w;
            if (top + h > rect.height) top = rect.height - h;

            const signerInfo = getActiveSigner();

            const id = 'marker_' + Date.now();
            const markerDiv = document.createElement('div');
            markerDiv.className = 'marker';
            markerDiv.id = id;
            markerDiv.style.left = left + 'px';
            markerDiv.style.top = top + 'px';
            markerDiv.style.width = w + 'px';
            markerDiv.style.height = h + 'px';
            markerDiv.style.borderColor = signerInfo.color;
            markerDiv.style.backgroundColor = signerInfo.bg;

            const title = document.createElement('div');
            title.className = 'marker-title';
            title.style.backgroundColor = signerInfo.color;
            title.textContent = 'Assinatura: ' + signerInfo.label;
            
            const btnClose = document.createElement('div');
            btnClose.className = 'marker-remove';
            btnClose.textContent = '×';
            btnClose.onclick = (ev) => {
                ev.stopPropagation();
                markerDiv.remove();
                markers = markers.filter(m => m.id !== id);
            };

            const btnResize = document.createElement('div');
            btnResize.className = 'marker-resize';
            btnResize.onmousedown = (ev) => initResize(ev, markerDiv);

            markerDiv.appendChild(title);
            markerDiv.appendChild(btnClose);
            markerDiv.appendChild(btnResize);
            
            wrapper.appendChild(markerDiv);
            
            markerDiv.onmousedown = (ev) => {
                if (ev.target === btnClose || ev.target === btnResize) return;
                initDrag(ev, markerDiv, wrapper);
            };

            markers.push({ id, page: pageIndex, markerEl: markerDiv, wrapper, signer: signerInfo.value, signerName: signerInfo.label });
        }

        function initDrag(e, el, wrapper) {
            isDragging = true;
            currentMarker = el;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(el.style.left, 10);
            startTop = parseInt(el.style.top, 10);
            
            document.onmousemove = (ev) => doDrag(ev, wrapper);
            document.onmouseup = stopDrag;
        }

        function doDrag(e, wrapper) {
            if (!isDragging || !currentMarker) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            let newL = startLeft + dx;
            let newT = startTop + dy;
            
            const wRect = wrapper.getBoundingClientRect();
            const mw = parseInt(currentMarker.style.width, 10);
            const mh = parseInt(currentMarker.style.height, 10);

            if (newL < 0) newL = 0;
            if (newT < 0) newT = 0;
            if (newL + mw > wRect.width) newL = wRect.width - mw;
            if (newT + mh > wRect.height) newT = wRect.height - mh;

            currentMarker.style.left = newL + 'px';
            currentMarker.style.top = newT + 'px';
        }

        function initResize(e, el) {
            e.stopPropagation();
            isResizing = true;
            currentMarker = el;
            startX = e.clientX;
            startY = e.clientY;
            startW = parseInt(el.style.width, 10);
            startH = parseInt(el.style.height, 10);
            
            document.onmousemove = doResize;
            document.onmouseup = stopDrag;
        }

        function doResize(e) {
            if (!isResizing || !currentMarker) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            
            let nw = startW + dx;
            let nh = startH + dy;
            if (nw < 50) nw = 50;
            if (nh < 30) nh = 30;

            const wrapper = currentMarker.parentElement;
            const wRect = wrapper.getBoundingClientRect();
            const left = parseInt(currentMarker.style.left, 10);
            const top = parseInt(currentMarker.style.top, 10);

            if (left + nw > wRect.width) nw = wRect.width - left;
            if (top + nh > wRect.height) nh = wRect.height - top;

            currentMarker.style.width = nw + 'px';
            currentMarker.style.height = nh + 'px';
        }

        function stopDrag() {
            isDragging = false;
            isResizing = false;
            currentMarker = null;
            document.onmousemove = null;
            document.onmouseup = null;
        }

        document.getElementById('btnSalvar').addEventListener('click', async () => {
            const results = markers.map(m => {
                const wrapperW = parseFloat(m.wrapper.dataset.width);
                const wrapperH = parseFloat(m.wrapper.dataset.height);
                const l = parseFloat(m.markerEl.style.left);
                const t = parseFloat(m.markerEl.style.top);
                const w = parseFloat(m.markerEl.style.width);
                const h = parseFloat(m.markerEl.style.height);

                return {
                    page: m.page,
                    x: l / wrapperW,
                    y: t / wrapperH,
                    w: w / wrapperW,
                    h: h / wrapperH,
                    signer: m.signer,
                    signer_name: m.signerName
                };
            });

            if(results.length === 0) {
                if(!confirm('Nenhuma posição de assinatura para o signatário definida. O sistema usará as rubricas padrão no rodapé. Deseja continuar?')) {
                    return;
                }
            }
            
            document.getElementById('btnSalvar').innerText = "Salvando...";
            document.getElementById('btnSalvar').disabled = true;

            const res = await fetch('salvar_posicoes.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ hash: '<?php echo $doc_hash; ?>', positions: results })
            });

            const data = await res.json();
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert('Erro: ' + (data.error || 'Desconhecido'));
                document.getElementById('btnSalvar').innerText = "Concluir e Gerar Link";
                document.getElementById('btnSalvar').disabled = false;
            }
        });

        renderPDF();
    </script>
</body>
</html>
