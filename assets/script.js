function showSection(id) {
    document.querySelectorAll('.dash-section').forEach(el => el.style.display = 'none');

    const target = document.getElementById('section-' + id);
    if (!target) return;

    if (id === 'converter') {
        target.style.display = 'flex';
    } else {
        target.style.display = 'block';
    }

    document.querySelectorAll('.nav-item').forEach(el => {
        el.classList.remove('active');
        if (el.getAttribute('onclick')?.includes(`'${id}'`)) {
            el.classList.add('active');
        }
    });

    if (id === 'dashboard') loadDeadlines();

    const url = new URL(window.location);
    if (id === 'dashboard') {
        url.searchParams.delete('section');
    } else {
        url.searchParams.set('section', id);
    }
    window.history.pushState({}, '', url);
}

let jurisdictionsData = null;

async function loadJurisdictions() {
    try {
        const res = await fetch('/api/jurisdictions?v=' + Date.now());
        jurisdictionsData = await res.json();
        console.log('Jurisdictions Loaded:', jurisdictionsData);

        populateJurisdictions('');
        populateJurisdictions('-dash');

    } catch (e) { console.error('Error loading jurisdictions', e); }
}

function populateJurisdictions(suffix) {
    const stateSelect = document.getElementById('state' + suffix);
    if (!stateSelect) return;

    stateSelect.innerHTML = '<option value="">Selecione...</option>';

    jurisdictionsData.states.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.innerText = s.name;
        stateSelect.appendChild(opt);
    });

    const matterSelect = document.getElementById('matter' + suffix);
    if (matterSelect) {
        matterSelect.innerHTML = '<option value="">Selecione...</option>';
        jurisdictionsData.matters.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.innerText = m.name;
            opt.dataset.type = m.default_type;
            matterSelect.appendChild(opt);
        });
    }

    const varaSelect = document.getElementById('vara' + suffix);
    if (varaSelect && jurisdictionsData.varas) {
        varaSelect.innerHTML = '<option value="">Selecione...</option>';
        jurisdictionsData.varas.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.name;
            opt.innerText = v.name;
            opt.dataset.matter = v.matter || '';
            varaSelect.appendChild(opt);
        });
    }

    setupJurisdictionListeners(suffix);
}

function setupJurisdictionListeners(suffix) {
    const stateSelect = document.getElementById('state' + suffix);
    const citySelect = document.getElementById('city' + suffix);
    const matterSelect = document.getElementById('matter' + suffix);
    const cityGroup = document.getElementById('city-group' + suffix);

    if (stateSelect) {
        stateSelect.addEventListener('change', () => {
            const uf = stateSelect.value;
            citySelect.innerHTML = '<option value="">Selecione...</option>';

            if (uf && jurisdictionsData.cities[uf]) {
                cityGroup.style.display = 'block';
                jurisdictionsData.cities[uf].forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.innerText = c.name;
                    opt.dataset.name = c.name;
                    citySelect.appendChild(opt);
                });
            } else {
                cityGroup.style.display = 'none';
            }

            const courtSelect = document.getElementById('court' + suffix);
            const courtGroup = document.getElementById('court-group' + suffix);

            if (courtSelect && jurisdictionsData.courts && jurisdictionsData.courts[uf]) {
                courtSelect.innerHTML = '<option value="">Selecione...</option>';
                courtGroup.style.display = 'block';

                jurisdictionsData.courts[uf].forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.innerText = c.name || c.id;
                    courtSelect.appendChild(opt);
                });
            } else if (courtGroup) {
                courtGroup.style.display = 'none';
                if (courtSelect) courtSelect.innerHTML = '<option value="">Selecione o Estado</option>';
            }
        });
    }

    if (matterSelect) {
        let lastValue = matterSelect.value;
        matterSelect.addEventListener('change', () => {
            const matterId = matterSelect.value;
            const selected = matterSelect.options[matterSelect.selectedIndex];
            const type = selected ? selected.dataset.type : null;

            if (type) {
                const elW = document.getElementById('type-working' + suffix);
                if (elW) elW.checked = (type === 'working');
                const elC = document.getElementById('type-calendar' + suffix);
                if (elC) elC.checked = (type === 'calendar');
            }

            const varaSelect = document.getElementById('vara' + suffix);
            if (varaSelect && jurisdictionsData.varas) {
                const currentVara = varaSelect.value;
                varaSelect.innerHTML = '<option value="">Selecione...</option>';
                jurisdictionsData.varas.forEach(v => {
                    if (!matterId || v.matter === matterId) {
                        const opt = document.createElement('option');
                        opt.value = v.name;
                        opt.innerText = v.name;
                        opt.dataset.matter = v.matter || '';
                        varaSelect.appendChild(opt);
                    }
                });
                varaSelect.value = currentVara;
                if (varaSelect.selectedIndex === -1) varaSelect.value = "";
            }



            const dtGroup = document.getElementById('deadline-type-group' + suffix);
            const dtSelect = document.getElementById('deadline-type' + suffix);
            const disclaimer = document.getElementById('deadline-disclaimer' + suffix);

            if (matterId && dtSelect) {
                const matter = jurisdictionsData.matters.find(m => m.id === matterId);
                if (matter && matter.deadlines) {
                    const currentVal = dtSelect.value;
                    dtSelect.innerHTML = '<option value="">Selecione...</option>';
                    matter.deadlines.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.name;
                        opt.innerText = d.name;
                        opt.dataset.days = d.days;
                        opt.dataset.type = d.type;
                        opt.dataset.ref = d.ref || '';
                        dtSelect.appendChild(opt);
                    });

                    if (currentVal) dtSelect.value = currentVal;
                    dtGroup.style.display = 'block';
                } else {
                    if (dtGroup) dtGroup.style.display = 'none';
                    if (dtSelect) dtSelect.innerHTML = '<option value="">Selecione...</option>';
                }
            } else {
                if (dtGroup) dtGroup.style.display = 'none';
                if (dtSelect) dtSelect.innerHTML = '<option value="">Selecione...</option>';
            }

            if (matterId !== lastValue) {
                if (disclaimer) disclaimer.style.display = 'none';
                lastValue = matterId;
            }
        });
    }

    const dtSelect = document.getElementById('deadline-type' + suffix);
    if (dtSelect) {
        dtSelect.addEventListener('change', () => {
            const selected = dtSelect.options[dtSelect.selectedIndex];
            if (!selected) return;
            const days = selected.dataset.days;
            const type = selected.dataset.type;
            const disclaimer = document.getElementById('deadline-disclaimer' + suffix);

            if (days) {
                const daysInput = document.getElementById('days' + suffix);
                if (daysInput) daysInput.value = days;
            }
            if (type) {
                const elW = document.getElementById('type-working' + suffix);
                if (elW) elW.checked = (type === 'working');
                const elC = document.getElementById('type-calendar' + suffix);
                if (elC) elC.checked = (type === 'calendar');
            }
            if (disclaimer) {
                if (dtSelect.value) {
                    const ref = selected.dataset.ref;
                    disclaimer.style.display = 'block';
                    disclaimer.innerHTML = `⚠️ <strong>Aviso:</strong> Confira se o prazo está correto. Estimativa baseada na lei federal${ref ? ' (' + ref + ')' : ''}.`;
                } else {
                    disclaimer.style.display = 'none';
                }
            }
        });
    }

}

document.addEventListener('DOMContentLoaded', () => {
    loadJurisdictions();
    setupCalculator('calc-form', '');
    setupCalculator('calc-form-dash', '-dash');

    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) {
        showSection(section);
    }
});

function setupCalculator(formId, suffix) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const startDate = document.getElementById('start_date' + suffix).value;
        const days = document.getElementById('days' + suffix).value;

        const typeInput = form.querySelector('input[name="type' + suffix + '"]:checked');
        const typeVal = typeInput ? typeInput.value : 'working';

        const state = document.getElementById('state' + suffix).value;
        const citySelect = document.getElementById('city' + suffix);
        const city = citySelect.value;
        const cityName = citySelect.options[citySelect.selectedIndex]?.dataset.name || '';
        const matter = document.getElementById('matter' + suffix).value;
        const vara = document.getElementById('vara' + suffix).value;
        const court = document.getElementById('court' + suffix)?.value;
        const processType = document.getElementById('process-type' + suffix)?.value || 'electronic';
        const deadlineType = document.getElementById('deadline-type' + suffix)?.value;


        const btn = form.querySelector('button[type="submit"]');

        btn.innerHTML = 'Calculando...';
        btn.disabled = true;

        try {
            const response = await fetch('/api/calculate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ startDate, days, type: typeVal, state, city, cityName, matter, vara, court, processType, deadlineType })
            });

            const data = await response.json();

            if (data.error === 'upgrade_required') {
                const alertEl = document.querySelector('.limit-alert');
                if (alertEl) {
                    alertEl.style.display = 'block';
                    alertEl.innerText = data.message;
                } else {
                    alert(data.message);
                }
                const resArea = document.getElementById('results-area' + suffix);
                if (resArea) resArea.style.display = 'none';
            } else if (data.error) {
                alert('Erro: ' + data.error);
            } else {
                const alertEl = document.querySelector('.limit-alert');
                if (alertEl) alertEl.style.display = 'none';

                document.getElementById('results-area' + suffix).style.display = 'block';
                document.getElementById('result-date' + suffix).innerText = data.description;

                const logContainer = document.getElementById('log-details' + suffix);
                logContainer.innerHTML = `<div class='log-item' style='color:#4ade80; border-bottom:1px solid rgba(255,255,255,0.1); margin-bottom:0.5rem; padding-bottom:0.5rem;'>📍 Localidade: ${data.location}</div>`;

                if (data.usage) {
                    const counterText = document.querySelector('.usage-counter span:nth-child(2)');
                    const counterBar = document.querySelector('.usage-counter div div');
                    if (counterText && counterBar) {
                        counterText.innerText = `${data.usage.count}/${data.usage.limit}`;
                        const pct = Math.min(100, (data.usage.count / data.usage.limit) * 100);
                        counterBar.style.width = `${pct}%`;
                    }
                }

                logContainer.innerHTML = '';

                setupGoogleCalendar(data, suffix);
                setupPDF(data, suffix);

                if (document.querySelector('.dashboard-container')) {
                    loadDeadlines();
                }

                if (suffix === '') {
                    const guestWarning = document.getElementById('guest-warning');
                    if (guestWarning) guestWarning.style.display = 'block';
                }
            }

        } catch (err) {
            alert("Ocorreu um erro na requisição.");
            console.error(err);
        } finally {
            btn.innerHTML = 'Calcular Prazo';
            btn.disabled = false;
        }
    });
}

async function loadDeadlines() {
    try {
        const res = await fetch('/api/deadlines');
        const data = await res.json();

        if (data.error) return;

        const pCount = document.getElementById('count-pending');
        if (pCount) pCount.innerText = data.stats.pending;

        const fCount = document.getElementById('count-finalized');
        if (fCount) fCount.innerText = data.stats.finalized;

        renderList('list-pending', data.pending);
        renderList('list-finalized', data.finalized);

        const allItems = [...data.pending, ...data.finalized].sort((a, b) => new Date(b.end_date) - new Date(a.end_date));
        renderHistory(allItems);

    } catch (e) {
        console.error(e);
    }
}

function renderHistory(items) {
    const tbody = document.getElementById('history-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 2rem;">Nenhum histórico encontrado.</td></tr>';
        return;
    }

    items.forEach(item => {
        const tr = document.createElement('tr');

        const dateParts = item.end_date.split('-');
        const dateFmt = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;

        const today = new Date().toISOString().split('T')[0];
        const status = item.end_date < today ?
            '<span class="req-status status-completed">Finalizado</span>' :
            '<span class="req-status status-pending">Pendente</span>';

        // Assuming item.id exists in the returned items
        const idParam = item.id ? `'${item.id}'` : 'null';

        tr.innerHTML = `
            <td>${dateFmt}</td>
            <td>${item.description || '-'}</td>
            <td>${item.days}</td>
            <td>${item.cityName || item.location || '-'}</td>
            <td>${status}</td>
            <td style="text-align:center;">
                <button onclick="deleteDeadline(${idParam})" class="btn" style="background: none; border: none; color: #ef4444; font-size: 1.25rem; cursor: pointer; padding: 0.25rem 0.5rem;" title="Excluir">
                    🗑️
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function deleteDeadline(id) {
    if (!id) return;
    if (!confirm("Tem certeza que deseja excluir este cálculo do histórico?")) return;
    
    try {
        const res = await fetch('/api/deadlines', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            loadDeadlines();
        } else {
            alert('Erro ao excluir: ' + (data.error || 'Desconhecido'));
        }
    } catch (e) {
        console.error(e);
        alert('Ocorreu um erro ao excluir.');
    }
}

function renderList(elementId, items) {
    const list = document.getElementById(elementId);
    if (!list) return;
    list.innerHTML = '';

    if (items.length === 0) {
        list.innerHTML = '<li>Nenhum prazo encontrado.</li>';
        return;
    }

    items.forEach(item => {
        const li = document.createElement('li');
        const dateParts = item.end_date.split('-');
        const dateFmt = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
        const loc = item.location ? `<br><small style='color:#666'>${item.location}</small>` : '';

        li.innerHTML = `
            <span>
                <strong>${dateFmt}</strong> ${item.description || ''}
                ${loc}
            </span>
            <small>(${item.days} dias)</small>
        `;
        list.appendChild(li);
    });
}

if (document.querySelector('.dashboard-container')) {
    loadDeadlines();
}


function setupGoogleCalendar(data, suffix = '') {
    const title = encodeURIComponent("Fim de Prazo Legal");
    const endDateStr = data.end_date.replace(/-/g, '');
    const startDateTime = endDateStr + 'T090000';
    const endDateTime = endDateStr + 'T100000';
    const details = encodeURIComponent("Prazo processual calculado. " + data.description);

    const link = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startDateTime}/${endDateTime}&details=${details}`;

    const linkEl = document.getElementById('gcal-link' + suffix);
    if (linkEl) linkEl.href = link;
}

async function logout() {
    try {
        const res = await fetch('/api/auth?action=logout');
        const data = await res.json();
        if (data.success) {
            window.location.href = 'login';
        }
    } catch (e) {
        console.error('Logout error', e);
    }
}


function setupPDF(data, suffix) {
    const btn = document.getElementById('btn-pdf' + suffix);
    if (!btn) return;

    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);

    newBtn.addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        function formatDateBR(isoDate) {
            if (!isoDate) return '-';
            const parts = isoDate.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        const rows = [];
        data.log.forEach(item => {
            if (typeof item === 'object') {
                let countStr = '';
                if (item.count && item.count !== 'X') countStr = item.count.toString();
                else if (item.count === 'X') countStr = 'X';

                const d = new Date(item.date + 'T12:00:00');
                const days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                const dayName = days[d.getDay()];
                const dateFmt = formatDateBR(item.date);

                rows.push([countStr, `${dateFmt} - ${dayName}`, item.description || '-']);
            } else {
                rows.push(['-', item, '-']);
            }
        });

        const pageWidth = doc.internal.pageSize.getWidth();

        doc.setFillColor(245, 247, 250);
        doc.rect(0, 0, pageWidth, 40, 'F');

        doc.setFontSize(22);
        doc.setTextColor(28, 40, 75);
        doc.setFont("helvetica", "bold");
        doc.text("MeuPrazoJus", 14, 25);

        let y = 50;
        doc.setFontSize(10);
        doc.setTextColor(50, 50, 50);
        doc.setFont("helvetica", "normal");

        const stateName = document.getElementById('state' + suffix)?.options[document.getElementById('state' + suffix).selectedIndex]?.text || '-';
        const cityName = document.getElementById('city' + suffix)?.options[document.getElementById('city' + suffix).selectedIndex]?.text || '-';
        const courtName = document.getElementById('court' + suffix)?.options[document.getElementById('court' + suffix).selectedIndex]?.text || '-';
        const matterName = document.getElementById('matter' + suffix)?.options[document.getElementById('matter' + suffix).selectedIndex]?.text || '-';
        const processName = document.getElementById('process-type' + suffix)?.options[document.getElementById('process-type' + suffix).selectedIndex]?.text || '-';

        doc.text(`Estado: ${stateName}`, 14, y); y += 6;
        doc.text(`Município: ${cityName}`, 14, y); y += 6;
        doc.text(`Matéria: ${matterName}`, 14, y); y += 6;
        doc.text(`Processo: ${processName}`, 14, y); y += 6;
        doc.text(`Tribunal: ${courtName}`, 14, y); y += 12; // Gap

        doc.setFontSize(12);
        doc.text(`Prazo de ${data.days} dias (${data.type === 'working' ? 'úteis' : 'corridos'}), iniciando em ${formatDateBR(data.term_start)}.`, 14, y);
        y += 10;

        doc.setFontSize(16);
        doc.setTextColor(220, 53, 69);
        doc.setFont("helvetica", "bold");
        const finalDateText = `Data final: ${formatDateBR(data.end_date)}`;
        const textWidth = doc.getTextWidth(finalDateText);
        doc.text(finalDateText, (pageWidth - textWidth) / 2, y);
        y += 10;

        doc.autoTable({
            startY: y,
            head: [['Contagem', 'Data', 'Descrição']],
            body: rows,
            theme: 'striped',
            headStyles: {
                fillColor: [230, 230, 230],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
                halign: 'center'
            },
            styles: {
                fontSize: 10,
                cellPadding: 4,
                valign: 'middle'
            },
            columnStyles: {
                0: { halign: 'center', cellWidth: 25, fontStyle: 'bold', textColor: [100, 100, 100] },
                1: { cellWidth: 80 },
                2: { cellWidth: 'auto' }
            },
            didParseCell: function (data) {
                if (data.column.index === 0) {
                    const val = data.cell.raw;
                    if (val !== 'X' && val !== '' && !isNaN(parseInt(val))) {
                        data.cell.styles.textColor = [0, 128, 0];
                        data.cell.styles.fontStyle = 'bold';
                    } else if (val === 'X') {
                        data.cell.styles.textColor = [200, 50, 50];
                    }
                }
            }
        });

        const finalY = doc.lastAutoTable.finalY + 15;
        doc.setFontSize(8);
        doc.setTextColor(150, 150, 150);
        const disclaimer = "O MeuPrazoJus disponibiliza este cálculo como simples referência. Este serviço não substitui a consulta a um advogado ou contador profissional. O usuário é responsável pelas informações inseridas.";
        const splitDisclaimer = doc.splitTextToSize(disclaimer, pageWidth - 28);
        doc.text(splitDisclaimer, pageWidth / 2, finalY, { align: 'center' });

        doc.setTextColor(28, 40, 75);
        doc.text("www.meuprazojus.com.br", pageWidth / 2, finalY + 15, { align: 'center' });

        doc.save(`Prazo-${data.days}dias-${data.end_date}.pdf`);
    });
}


let converterFiles = [];

function switchConverterTab(type) {
    document.getElementById('converter-pdf-panel').style.display = 'none';
    document.getElementById('converter-audio-panel').style.display = 'none';
    document.getElementById('converter-video-panel').style.display = 'none';

    ['pdf', 'audio', 'video'].forEach(t => {
        document.getElementById(`btn-tab-${t}`).classList.remove('btn-primary');
        document.getElementById(`btn-tab-${t}`).classList.add('btn-ghost');
    });

    document.getElementById(`converter-${type}-panel`).style.display = 'block';

    const activeBtn = document.getElementById(`btn-tab-${type}`);
    activeBtn.classList.remove('btn-ghost');
    activeBtn.classList.add('btn-primary');
}

const dropZonePdf = document.getElementById('drop-zone-pdf');
const inputImages = document.getElementById('input-images');

if (dropZonePdf) {
    dropZonePdf.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZonePdf.classList.add('dragover');
    });

    dropZonePdf.addEventListener('dragleave', () => dropZonePdf.classList.remove('dragover'));

    dropZonePdf.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZonePdf.classList.remove('dragover');
        handlePdfFiles(e.dataTransfer.files);
    });

    inputImages.addEventListener('change', (e) => handlePdfFiles(e.target.files));
}

function handlePdfFiles(files) {
    const list = document.getElementById('preview-list');
    const btn = document.getElementById('btn-convert-pdf');

    Array.from(files).forEach(file => {
        if (file.type === 'image/jpeg' || file.type === 'image/png') {
            converterFiles.push(file);

            const item = document.createElement('div');
            item.className = 'upload-item';

            item.innerHTML = `
                <span>${file.name}</span>
                <button class="remove-btn" onclick="removeFile(this, '${file.name}')">&times;</button>
            `;
            list.appendChild(item);
        }
    });

    btn.disabled = converterFiles.length === 0;
}

function removeFile(el, name) {
    converterFiles = converterFiles.filter(f => f.name !== name);
    el.parentElement.remove();
    document.getElementById('btn-convert-pdf').disabled = converterFiles.length === 0;
}

const btnConvertPdf = document.getElementById('btn-convert-pdf');
if (btnConvertPdf) {
    btnConvertPdf.addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        for (let i = 0; i < converterFiles.length; i++) {
            const file = converterFiles[i];
            const imgData = await readFileAsDataURL(file);

            const imgProps = doc.getImageProperties(imgData);
            const pdfWidth = doc.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            if (i > 0) doc.addPage();
            doc.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
        }

        doc.save("MeusDocumentos.pdf");

        converterFiles = [];
        document.getElementById('preview-list').innerHTML = '';
        btnConvertPdf.disabled = true;
        alert("PDF Gerado com sucesso!");
    });
}

function readFileAsDataURL(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

let ffmpeg = null;
let fetchFile = null;

async function loadFFmpeg() {
    if (ffmpeg) return;

    if (!window.FFmpeg) {
        throw new Error("FFmpeg script did not load correctly.");
    }

    const { createFFmpeg, fetchFile: ff } = window.FFmpeg;
    fetchFile = ff;

    ffmpeg = createFFmpeg({ log: true });

    if (!ffmpeg.isLoaded()) {
        await ffmpeg.load();
    }
}

const btnConvertAudio = document.getElementById('btn-convert-audio');
if (btnConvertAudio) {
    btnConvertAudio.addEventListener('click', async () => {
        const fileInput = document.getElementById('input-audio');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert("Selecione um arquivo de áudio primeiro.");
            return;
        }

        const file = fileInput.files[0];
        const btn = btnConvertAudio;
        const originalText = btn.innerText;

        try {
            btn.disabled = true;
            btn.innerText = "Carregando FFmpeg...";

            await loadFFmpeg();

            btn.innerText = "Convertendo...";

            const fileName = file.name;
            const outputName = 'audio_convertido.mp3';

            ffmpeg.FS('writeFile', fileName, await fetchFile(file));

            await ffmpeg.run('-i', fileName, outputName);

            const data = ffmpeg.FS('readFile', outputName);

            const url = URL.createObjectURL(new Blob([data.buffer], { type: 'audio/mp3' }));

            const a = document.createElement('a');
            a.href = url;
            a.download = outputName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            ffmpeg.FS('unlink', fileName);
            ffmpeg.FS('unlink', outputName);

            alert("Conversão de áudio concluída com sucesso!");

        } catch (err) {
            console.error(err);
            alert("Erro na conversão: " + err.message + ". Verifique se seu navegador suporta SharedArrayBuffer (HTTPS ou localhost com headers corretos).");
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    });
}

const inputAudio = document.getElementById('input-audio');
const dropZoneAudio = document.getElementById('drop-zone-audio');
if (dropZoneAudio) {
    inputAudio.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            document.getElementById('audio-file-info').innerHTML = `
                <div class="upload-item">
                    <span>${e.target.files[0].name}</span>
                    <button class="remove-btn" onclick="removeSingleFile('input-audio', 'audio-file-info', 'btn-convert-audio')">&times;</button>
                </div>
            `;
            document.getElementById('btn-convert-audio').disabled = false;
        }
    });
}

const btnConvertVideo = document.getElementById('btn-convert-video');
if (btnConvertVideo) {
    btnConvertVideo.addEventListener('click', async () => {
        const fileInput = document.getElementById('input-video');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert("Selecione um arquivo de vídeo primeiro.");
            return;
        }

        const file = fileInput.files[0];
        const btn = btnConvertVideo;
        const originalText = btn.innerText;

        try {
            btn.disabled = true;
            btn.innerText = "Carregando FFmpeg...";

            await loadFFmpeg();

            btn.innerText = "Convertendo...";

            const fileName = file.name;
            const outputName = 'video_convertido.mp4';

            ffmpeg.FS('writeFile', fileName, await fetchFile(file));

            await ffmpeg.run('-i', fileName, '-c:v', 'libx264', '-preset', 'ultrafast', '-c:a', 'aac', outputName);

            const data = ffmpeg.FS('readFile', outputName);

            const url = URL.createObjectURL(new Blob([data.buffer], { type: 'video/mp4' }));

            const a = document.createElement('a');
            a.href = url;
            a.download = outputName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            ffmpeg.FS('unlink', fileName);
            ffmpeg.FS('unlink', outputName);

            alert("Conversão de vídeo concluída com sucesso!");

        } catch (err) {
            console.error(err);
            alert("Erro na conversão: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    });
}

const inputVideo = document.getElementById('input-video');
const dropZoneVideo = document.getElementById('drop-zone-video');
if (dropZoneVideo) {
    inputVideo.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            document.getElementById('video-file-info').innerHTML = `
                <div class="upload-item">
                    <span>${e.target.files[0].name}</span>
                    <button class="remove-btn" onclick="removeSingleFile('input-video', 'video-file-info', 'btn-convert-video')">&times;</button>
                </div>
            `;
            document.getElementById('btn-convert-video').disabled = false;
        }
    });
}

function removeSingleFile(inputId, infoId, btnId) {
    document.getElementById(inputId).value = '';
    document.getElementById(infoId).innerHTML = '';
    document.getElementById(btnId).disabled = true;
}

const profileForm = document.getElementById('profile-form');
if (profileForm) {
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = profileForm.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.innerText = 'Salvando...';
        btn.disabled = true;

        try {
            const formData = new FormData(profileForm);
            const data = Object.fromEntries(formData.entries());

            const res = await fetch('/api/user_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if (result.success) {
                alert('Perfil atualizado com sucesso!');
                window.location.reload();
            } else {
                alert(result.error || 'Erro ao atualizar perfil.');
            }
        } catch (err) {
            console.error(err);
            alert('Erro de conexão ou sistema.');
        } finally {
            btn.innerText = originalText;
            btn.disabled = false;
        }
    });

    const profilePhone = document.getElementById('profile-phone');
    if (profilePhone) {
        profilePhone.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 10) value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
            else if (value.length > 6) value = value.replace(/^(\d{2})(\d{4,5})(\d{0,4}).*/, "($1) $2-$3");
            else if (value.length > 2) value = value.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
            else value = value.replace(/^(\d*)/, "($1");
            e.target.value = value;
        });
    }
}

function setupFeesEvents() {
    const feeStart = document.getElementById('fee-start-date');
    if (feeStart) {
        feeStart.valueAsDate = new Date();
    }

    const feeInput = document.getElementById('fee-total');
    if (feeInput) {
        feeInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, "");
            value = (value / 100).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            e.target.value = value;
        });
    }

    const addLawyerBtn = document.getElementById('add-lawyer-btn');
    if (addLawyerBtn) {
        addLawyerBtn.addEventListener('click', () => {
            const container = document.getElementById('lawyers-list');
            const div = document.createElement('div');
            div.className = 'lawyer-input-group';
            div.style.display = 'flex';
            div.style.gap = '0.5rem';
            div.innerHTML = `
                <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 2;">
                <input type="number" class="lawyer-percent" placeholder="%" min="0" max="100" required style="width: 80px;">
                <button type="button" class="btn btn-ghost remove-lawyer" style="color: #f87171;">&times;</button>
            `;
            container.appendChild(div);

            div.querySelector('.remove-lawyer').addEventListener('click', () => div.remove());
        });
    }

    const feeForm = document.getElementById('fee-form');
    if (feeForm) {
        feeForm.addEventListener('submit', (e) => {
            e.preventDefault();
            calculateFees();
        });
        loadFeeHistory();
    }
}

let currentFeeHistoryPage = 1;

async function loadFeeHistory(page = 1) {
    try {
        currentFeeHistoryPage = page;
        const res = await fetch(`api/h_calc?page=${page}&limit=10&v=${Date.now()}`);
        const text = await res.text();

        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            throw new Error(`Parse failed: ${text.substring(0, 50)}`);
        }

        const tbody = document.getElementById('fee-history-table-body');

        if (data.error || !data.items) {
            console.error('API Error:', data.error || 'No items');
            if (tbody) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">Erro ao carregar histórico.</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        if (data.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">Nenhum cálculo salvo ainda.</td></tr>';
            return;
        }

        data.items.forEach(item => {
            const tr = document.createElement('tr');

            const safeDateStr = item.created_at ? item.created_at.replace(' ', 'T') : '';
            const date = safeDateStr ? new Date(safeDateStr).toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : '-';

            tr.innerHTML = `
                <td>${date}</td>
                <td>${formatCurrency(item.total)}</td>
                <td>${item.installments}x</td>
                <td style="display: flex; gap: 0.5rem; justify-content: center;">
                    <button class="btn btn-ghost view-btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Ver</button>
                    <button onclick="deleteFee(${item.id})" class="btn" style="background: none; border: none; color: #ef4444; font-size: 1.25rem; cursor: pointer; padding: 0.25rem 0.5rem;" title="Excluir">🗑️</button>
                </td>
            `;
            tr.querySelector('.view-btn').onclick = () => loadFeeCalculation(item);
            tbody.appendChild(tr);
        });

        renderFeePagination(data.page, data.total_pages);

    } catch (e) {
        console.error('Error loading history', e);
        const tbody = document.getElementById('fee-history-table-body');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: #f87171;">Erro ao carregar: ${e.message}</td></tr>`;
        }
    }
}

async function deleteFee(id) {
    if (!id) return;
    if (!confirm("Tem certeza que deseja excluir este cálculo de honorário do histórico?")) return;
    
    try {
        const res = await fetch('/api/h_calc', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            loadFeeHistory(1);
        } else {
            alert('Erro ao excluir: ' + (data.error || 'Desconhecido'));
        }
    } catch (e) {
        console.error(e);
        alert('Ocorreu um erro ao excluir.');
    }
}

function loadFeeCalculation(item) {
    document.getElementById('fee-total').value = parseFloat(item.total).toFixed(2);
    document.getElementById('fee-installments').value = item.installments;
    document.getElementById('fee-start-date').value = item.startDate.split('T')[0];

    const list = document.getElementById('lawyers-list');
    list.innerHTML = '';
    if (item.lawyers && Array.isArray(item.lawyers)) {
        item.lawyers.forEach(l => {
            const row = document.createElement('div');
            row.className = 'lawyer-input-group';
            row.style = 'display: flex; gap: 0.5rem;';
            row.innerHTML = `
                <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 2;" value="${l.name}">
                <input type="number" class="lawyer-percent" placeholder="%" min="0" max="100" required style="width: 80px;" value="${l.percent}">
                <button type="button" class="btn btn-ghost remove-lawyer-btn" style="padding: 0 0.5rem;">🗑️</button>
            `;
            row.querySelector('.remove-lawyer-btn').onclick = () => row.remove();
            list.appendChild(row);
        });
    }

    // Trigger calculation
    calculateFees();
    document.getElementById('fee-results').scrollIntoView({ behavior: 'smooth' });
}

function renderFeePagination(current, total) {
    const container = document.getElementById('pagination-controls');
    if (!container) return;
    container.innerHTML = '';

    if (total <= 1) return;

    const prev = document.createElement('button');
    prev.className = 'btn btn-ghost';
    prev.innerText = '←';
    prev.disabled = current === 1;
    prev.onclick = () => loadFeeHistory(current - 1);
    container.appendChild(prev);

    const span = document.createElement('span');
    span.innerText = `${current} / ${total}`;
    span.style.color = 'var(--text-muted)';
    container.appendChild(span);

    const next = document.createElement('button');
    next.className = 'btn btn-ghost';
    next.innerText = '→';
    next.disabled = current === total;
    next.onclick = () => loadFeeHistory(current + 1);
    container.appendChild(next);
}

function loadFeeCalculation(item) {
    document.getElementById('fee-total').value = formatCurrency(item.total);
    document.getElementById('fee-installments').value = item.installments;
    document.getElementById('fee-start-date').value = item.startDate;

    const container = document.getElementById('lawyers-list');
    container.innerHTML = '';

    let lawyersData = item.lawyers;
    if (lawyersData.length > 0 && typeof lawyersData[0] === 'string') {
        const splitPercent = 100 / lawyersData.length;
        lawyersData = lawyersData.map(name => ({ name, percent: splitPercent }));
    }

    lawyersData.forEach((l, idx) => {
        const div = document.createElement('div');
        div.className = 'lawyer-input-group';
        div.style.display = 'flex';
        div.style.gap = '0.5rem';
        div.innerHTML = `
            <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 2;" value="${l.name}">
            <input type="number" class="lawyer-percent" placeholder="%" min="0" max="100" required style="width: 80px;" value="${l.percent}">
            ${idx > 0 ? '<button type="button" class="btn btn-ghost remove-lawyer" style="color: #f87171;">&times;</button>' : ''}
        `;
        container.appendChild(div);

        const btn = div.querySelector('.remove-lawyer');
        if (btn) btn.addEventListener('click', () => div.remove());
    });

    const tbody = document.getElementById('fee-table-body');
    tbody.innerHTML = '';

    const installValue = item.total / item.installments;

    const [y, m, d] = item.startDate.split('-').map(Number);
    let currentDate = new Date(y, m - 1, d, 12, 0, 0);

    for (let i = 1; i <= item.installments; i++) {
        const row = document.createElement('tr');
        const dateFmt = currentDate.toLocaleDateString('pt-BR');
        const gcalLink = generateFeeGCalLink(currentDate, installValue, i, item.installments);

        let distributionText = lawyersData.map(l => {
            const share = installValue * (l.percent / 100);
            return `${l.name} (${parseFloat(l.percent).toFixed(2)}%): ${formatCurrency(share)}`;
        }).join('<br>');

        row.innerHTML = `
            <td>${i}x</td>
            <td>${dateFmt}</td>
            <td>${formatCurrency(installValue)}</td>
            <td><small style="font-size: 0.85rem; color: var(--text-muted)">${distributionText}</small></td>
            <td><a href="${gcalLink}" target="_blank" class="gcal-icon">📅 Agendar</a></td>
        `;
        tbody.appendChild(row);
        currentDate.setMonth(currentDate.getMonth() + 1);
    }

    document.getElementById('fee-summary').innerText = `Total: ${formatCurrency(item.total)}`;
    document.getElementById('fee-results').style.display = 'block';

    document.getElementById('fee-results').scrollIntoView({ behavior: 'smooth' });
}

async function calculateFees() {
    const rawValue = document.getElementById('fee-total').value;
    const total = parseFloat(rawValue.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
    const installments = parseInt(document.getElementById('fee-installments').value);
    const startDateStr = document.getElementById('fee-start-date').value;

    const lawyerInputs = document.querySelectorAll('.lawyer-input-group');
    const lawyers = [];
    let totalPercent = 0;

    lawyerInputs.forEach(div => {
        const name = div.querySelector('.lawyer-name').value.trim();
        const percent = parseFloat(div.querySelector('.lawyer-percent').value) || 0;

        if (name) {
            lawyers.push({ name, percent });
            totalPercent += percent;
        }
    });

    if (isNaN(total) || total <= 0) {
        alert("Digite um valor válido.");
        return;
    }

    if (Math.abs(totalPercent - 100) > 0.1) {
        alert(`A soma das porcentagens deve ser 100%. Atual: ${totalPercent}%`);
        return;
    }

    try {
        await fetch('api/h_calc', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                total,
                installments,
                startDate: startDateStr,
                lawyers
            })
        });
        loadFeeHistory(1);
    } catch (e) {
        console.error('Error saving calculation', e);
    }

    const tbody = document.getElementById('fee-table-body');
    tbody.innerHTML = '';

    const installValue = total / installments;

    const [y, m, d] = startDateStr.split('-').map(Number);
    let currentDate = new Date(y, m - 1, d, 12, 0, 0);

    for (let i = 1; i <= installments; i++) {
        const row = document.createElement('tr');
        const dateFmt = currentDate.toLocaleDateString('pt-BR');
        const gcalLink = generateFeeGCalLink(currentDate, installValue, i, installments);

        let distributionText = lawyers.map(l => {
            const share = installValue * (l.percent / 100);
            return `${l.name} (${l.percent}%): ${formatCurrency(share)}`;
        }).join('<br>');

        row.innerHTML = `
            <td>${i}x</td>
            <td>${dateFmt}</td>
            <td>${formatCurrency(installValue)}</td>
            <td><small style="font-size: 0.85rem; color: var(--text-muted)">${distributionText}</small></td>
            <td><a href="${gcalLink}" target="_blank" class="gcal-icon">📅 Agendar</a></td>
        `;
        tbody.appendChild(row);

        currentDate.setMonth(currentDate.getMonth() + 1);
    }

    document.getElementById('fee-summary').innerText = `Total: ${formatCurrency(total)}`;
    document.getElementById('fee-results').style.display = 'block';
}

function formatCurrency(val) {
    return Number(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function generateFeeGCalLink(date, val, current, total) {
    const title = encodeURIComponent(`Recebimento Honorários (${current}/${total})`);

    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const dateStr = `${y}${m}${d}`;

    const details = encodeURIComponent(`Recebimento de honorários: ${formatCurrency(val)}`);

    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${dateStr}/${dateStr}&details=${details}`;
}

document.addEventListener('DOMContentLoaded', setupFeesEvents);
