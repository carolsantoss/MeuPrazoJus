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

    <main class="flex-1 overflow-auto bg-slate-900 p-4" id="pdf-viewer">
        <div class="flex flex-col items-center gap-4" id="pages-container"></div>
    </main>

    <script>
        const pdfData = atob('<?php echo $pdf_base64; ?>');
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        
        const container = document.getElementById('pages-container');
        let markers = []; // { id, page, left, top, width, height }
        let isDragging = false, isResizing = false, currentMarker = null;
        let startX, startY, startLeft, startTop, startW, startH;

        async function renderPDF() {
            const loadingTask = pdfjsLib.getDocument({data: pdfData});
            const pdf = await loadingTask.promise;
            const scale = 1.5;

            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const viewport = page.getViewport({ scale });
                
                const wrapper = document.createElement('div');
                wrapper.className = 'pdf-page-container';
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

            const id = 'marker_' + Date.now();
            const markerDiv = document.createElement('div');
            markerDiv.className = 'marker';
            markerDiv.id = id;
            markerDiv.style.left = left + 'px';
            markerDiv.style.top = top + 'px';
            markerDiv.style.width = w + 'px';
            markerDiv.style.height = h + 'px';

            const title = document.createElement('div');
            title.className = 'marker-title';
            title.textContent = 'Assinatura Signatário';
            
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

            markers.push({ id, page: pageIndex, markerEl: markerDiv, wrapper });
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
                    h: h / wrapperH
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
