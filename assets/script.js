function showSection(id) {
    document.querySelectorAll('.dash-section').forEach(el => el.style.display = 'none');
    const target = document.getElementById('section-' + id);
    if (target) target.style.display = 'block';

    document.querySelectorAll('.nav-item').forEach(el => {
        el.classList.remove('active');
        if (el.getAttribute('onclick')?.includes(`'${id}'`)) {
            el.classList.add('active');
        }
    });

    if (id === 'dashboard') loadDeadlines();
}

let jurisdictionsData = null;

async function loadJurisdictions() {
    try {
        // Cache bypass for API data
        const res = await fetch('api/jurisdictions.php?v=' + Date.now());
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
        matterSelect.innerHTML = '<option value="">Geral</option>';
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
        varaSelect.innerHTML = '<option value="">Geral</option>';
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

            // Filter Varas based on Matter
            const varaSelect = document.getElementById('vara' + suffix);
            if (varaSelect && jurisdictionsData.varas) {
                const currentVara = varaSelect.value;
                varaSelect.innerHTML = '<option value="">Geral</option>';
                jurisdictionsData.varas.forEach(v => {
                    // Show if matter matches OR if no matter is selected (show all)
                    if (!matterId || v.matter === matterId) {
                        const opt = document.createElement('option');
                        opt.value = v.name;
                        opt.innerText = v.name;
                        opt.dataset.matter = v.matter || '';
                        varaSelect.appendChild(opt);
                    }
                });
                // Restore vara if still in list
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

                    // Try to restore previous selection if it exists in new list
                    if (currentVal) dtSelect.value = currentVal;

                    dtGroup.style.display = 'block';
                } else {
                    dtGroup.style.display = 'none';
                    dtSelect.innerHTML = '<option value="">Selecione...</option>';
                }
            } else {
                if (dtGroup) dtGroup.style.display = 'none';
                if (dtSelect) dtSelect.innerHTML = '<option value="">Selecione...</option>';
            }

            // Only hide disclaimer if we actually changed matter or it was empty
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
        const deadlineType = document.getElementById('deadline-type' + suffix)?.value;

        const btn = form.querySelector('button[type="submit"]');

        btn.innerHTML = 'Calculando...';
        btn.disabled = true;

        try {
            const response = await fetch('api/calculate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ startDate, days, type: typeVal, state, city, cityName, matter, vara, deadlineType })
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

                data.log.forEach(line => {
                    const div = document.createElement('div');
                    div.className = 'log-item';
                    div.innerText = line;
                    logContainer.appendChild(div);
                });

                setupGoogleCalendar(data, suffix);

                if (document.querySelector('.dashboard-container')) {
                    loadDeadlines();
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
        const res = await fetch('api/deadlines.php');
        const data = await res.json();

        if (data.error) return;

        const pCount = document.getElementById('count-pending');
        if (pCount) pCount.innerText = data.stats.pending;

        const fCount = document.getElementById('count-finalized');
        if (fCount) fCount.innerText = data.stats.finalized;

        renderList('list-pending', data.pending);
        renderList('list-finalized', data.finalized);

    } catch (e) {
        console.error(e);
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
        // Simple format: 10/02/2026 - Vence xxxx
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
        const res = await fetch('api/auth.php?action=logout');
        const data = await res.json();
        if (data.success) {
            window.location.href = 'login.php';
        }
    } catch (e) {
        console.error('Logout error', e);
    }
}

