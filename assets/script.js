// Basic Navigation
function showSection(id) {
    document.querySelectorAll('.dash-section').forEach(el => el.style.display = 'none');
    document.getElementById('section-' + id).style.display = 'block';

    // Update active nav
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    // Simplification for demo: assuming order
    if (id === 'dashboard') document.getElementById('nav-dash')?.classList.add('active'); // Needs ID in HTML or querySelector update

    if (id === 'dashboard') loadDeadlines();
}

// ----- JURISDICTION LOGIC -----
let jurisdictionsData = null;

async function loadJurisdictions() {
    try {
        const res = await fetch('api/jurisdictions.php');
        jurisdictionsData = await res.json();

        populateJurisdictions('');      // Landing
        populateJurisdictions('-dash'); // Dashboard

    } catch (e) { console.error('Error loading jurisdictions', e); }
}

function populateJurisdictions(suffix) {
    // Populate States
    const stateSelect = document.getElementById('state' + suffix);
    if (!stateSelect) return;

    // Clear first to avoid duplicates if called multiple times (though not here)
    stateSelect.innerHTML = '<option value="">Selecione...</option>';

    jurisdictionsData.states.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.innerText = s.name;
        stateSelect.appendChild(opt);
    });

    // Populate Matters
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

    // Event Listeners
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
                    citySelect.appendChild(opt);
                });
            } else {
                cityGroup.style.display = 'none';
            }
        });
    }

    if (matterSelect) {
        matterSelect.addEventListener('change', () => {
            const selected = matterSelect.options[matterSelect.selectedIndex];
            const type = selected.dataset.type;
            if (type) {
                if (type === 'working') {
                    const el = document.getElementById('type-working' + suffix);
                    if (el) el.checked = true;
                }
                if (type === 'calendar') {
                    const el = document.getElementById('type-calendar' + suffix);
                    if (el) el.checked = true;
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadJurisdictions();
    setupCalculator('calc-form', '');
    setupCalculator('calc-form-dash', '-dash');
});

function setupCalculator(formId, suffix) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const startDate = document.getElementById('start_date' + suffix).value;
        const days = document.getElementById('days' + suffix).value;
        // Actually radio names must be unique per group if on same page, OR scoped by form.
        // Since we conditionally render (if/else), only ONE form exists in DOM usually?
        // NO. PHP has if(!isset) -> Landing ELSE Dashboard.
        // So they never coexist on the same page load.
        // Wait, 'type' radios on dashboard form need unique IDs but same Name?
        // If they don't coexist, same IDs is fine.
        // BUT my replace logic added suffix IDs.
        // Let's rely on form scoping: form.querySelector

        const typeInput = form.querySelector('input[name="type' + suffix + '"]:checked');
        const typeVal = typeInput ? typeInput.value : 'working';

        const state = document.getElementById('state' + suffix).value;
        const city = document.getElementById('city' + suffix).value;

        const btn = form.querySelector('button[type="submit"]');

        btn.innerHTML = 'Calculando...';
        btn.disabled = true;

        try {
            const response = await fetch('api/calculate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ startDate, days, type: typeVal, state, city })
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
                logContainer.innerHTML = '';
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


// Load Deadlines (Dashboard)
async function loadDeadlines() {
    try {
        const res = await fetch('api/deadlines.php');
        const data = await res.json();

        if (data.error) return; // Not logged in or error

        // Counters
        const pCount = document.getElementById('count-pending');
        if (pCount) pCount.innerText = data.stats.pending;

        const fCount = document.getElementById('count-finalized');
        if (fCount) fCount.innerText = data.stats.finalized;

        // Lists
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

// Initial Load if dashboard exists
if (document.querySelector('.dashboard-container')) {
    loadDeadlines();
}


function setupGoogleCalendar(data, suffix = '') {
    // Construct Google Calendar Link
    const title = encodeURIComponent("Fim de Prazo Legal");
    const endDateStr = data.end_date.replace(/-/g, '');
    const startDateTime = endDateStr + 'T090000';
    const endDateTime = endDateStr + 'T100000';
    const details = encodeURIComponent("Prazo processual calculado. " + data.description);

    const link = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startDateTime}/${endDateTime}&details=${details}`;

    const linkEl = document.getElementById('gcal-link' + suffix);
    if (linkEl) linkEl.href = link;
}
