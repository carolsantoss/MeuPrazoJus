<?php include 'src/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Honorários | MeuPrazoJus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <div class="logo">MeuPrazoJus</div>
                <div>
                   <?php if(isset($_SESSION['user_id'])): ?>
                       <a href="subscription.php" class="btn btn-ghost">Planos</a>
                       <a href="#" class="btn btn-primary" onclick="logout()">Sair</a>
                   <?php else: ?>
                       <a href="login.php" class="btn btn-ghost">Entrar</a>
                       <a href="subscription.php" class="btn btn-primary">Assinar Agora</a>
                   <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="dashboard-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="user-info">
                    <?php 
                        $fullName = $_SESSION['user_name'] ?? 'Usuário';
                        $firstName = explode(' ', trim($fullName))[0];
                    ?>
                    <h3>Olá, <?= htmlspecialchars($firstName) ?>!</h3>
                    <p>Gerencie seus prazos.</p>
                </div>
                <nav class="side-nav">
                    <a href="index" class="nav-item">📊 Prazos</a>
                    <a href="index?section=new-deadline" class="nav-item">➕ Novo Prazo</a>
                    <a href="fees" class="nav-item active">💰 Honorários</a>
                    <a href="subscription" class="nav-item">⭐ Assinatura</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="dash-content centered">
                <header class="top-bar">
                    <h1 style="text-align: center;">Calculadora de Honorários</h1>
                    <p class="subtitle" style="text-align: center;">Organize o recebimento e divisão de valores.</p>
                </header>

                <div class="content-wrapper">
                    <div class="card">
                        <form id="fee-form">
                            <div class="form-row" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group" style="flex: 1;">
                                    <label>Valor Total</label>
                                    <input type="text" id="fee-total" required placeholder="R$ 0,00">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>Nº Parcelas</label>
                                    <input type="number" id="fee-installments" min="1" value="1" required>
                                </div>
                            </div>
                            
                            <div class="form-row" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group" style="flex: 1;">
                                    <label>Data 1ª Parcela</label>
                                    <input type="date" id="fee-start-date" required>
                                </div>
                                <div class="form-group" style="flex: 1;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="display: flex; justify-content: space-between; align-items: center;">
                                    Advogados Participantes
                                    <button type="button" class="btn btn-ghost" id="add-lawyer-btn" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">+ Adicionar Advogado</button>
                                </label>
                                <div id="lawyers-list" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                                    <div class="lawyer-input-group" style="display: flex; gap: 0.5rem;">
                                        <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 1;">
                                    </div>
                                </div>
                            </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Calcular Parcelas</button>
                        </form>
                    </div>

                    <div id="fee-results" style="display:none; margin-top: 2rem;" class="card">
                        <h3>Planejamento de Recebimento</h3>
                        <div class="table-responsive">
                            <table class="glass-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data</th>
                                        <th>Valor Total</th>
                                        <th>Por Advogado</th>
                                        <th>Agenda</th>
                                    </tr>
                                </thead>
                                <tbody id="fee-table-body">
                                </tbody>
                            </table>
                        </div>
                        <div class="fee-total" id="fee-summary" style="margin-top: 1.5rem; text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);"></div>
                    </div>

                    <!-- History Section -->
                    <div id="fee-history-container" class="card" style="margin-top: 2rem;">
                        <h3>Histórico de Cálculos</h3>
                        <div class="table-responsive">
                            <table class="glass-table">
                                <thead>
                                    <tr>
                                        <th>Data Criado</th>
                                        <th>Valor Total</th>
                                        <th>Parcelas</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body">
                                    <tr><td colspan="4" style="text-align:center">Carregando histórico...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="pagination-controls" style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fee-start-date').valueAsDate = new Date();

    const feeInput = document.getElementById('fee-total');
    feeInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, "");
        value = (value / 100).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
        e.target.value = value;
    });

    document.getElementById('add-lawyer-btn').addEventListener('click', () => {
        const container = document.getElementById('lawyers-list');
        const div = document.createElement('div');
        div.className = 'lawyer-input-group';
        div.style.display = 'flex';
        div.style.gap = '0.5rem';
        div.innerHTML = `
            <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 1;">
            <button type="button" class="btn btn-ghost remove-lawyer" style="color: #f87171;">&times;</button>
        `;
        container.appendChild(div);

        div.querySelector('.remove-lawyer').addEventListener('click', () => div.remove());
    });

    document.getElementById('fee-form').addEventListener('submit', (e) => {
        e.preventDefault();
        calculateFees();
    });

    loadHistory();
});

let currentHistoryPage = 1;

async function loadHistory(page = 1) {
    try {
        currentHistoryPage = page;
        const res = await fetch(`api/fees.php?page=${page}&limit=10&v=${Date.now()}`);
        const data = await res.json();
        
        const tbody = document.getElementById('history-table-body');
        tbody.innerHTML = '';

        if (data.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">Nenhum cálculo salvo ainda.</td></tr>';
            return;
        }

        data.items.forEach(item => {
            const tr = document.createElement('tr');
            const date = new Date(item.created_at).toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            tr.innerHTML = `
                <td>${date}</td>
                <td>${formatCurrency(item.total)}</td>
                <td>${item.installments}x</td>
                <td><button onclick='loadCalculation(${JSON.stringify(item)})' class="btn btn-ghost" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Ver</button></td>
            `;
            tbody.appendChild(tr);
        });

        renderPagination(data.page, data.total_pages);

    } catch (e) { console.error('Error loading history', e); }
}

function renderPagination(current, total) {
    const container = document.getElementById('pagination-controls');
    container.innerHTML = '';
    
    if (total <= 1) return;

    const prev = document.createElement('button');
    prev.className = 'btn btn-ghost';
    prev.innerText = '←';
    prev.disabled = current === 1;
    prev.onclick = () => loadHistory(current - 1);
    container.appendChild(prev);

    const span = document.createElement('span');
    span.innerText = `${current} / ${total}`;
    span.style.color = 'var(--text-muted)';
    container.appendChild(span);

    const next = document.createElement('button');
    next.className = 'btn btn-ghost';
    next.innerText = '→';
    next.disabled = current === total;
    next.onclick = () => loadHistory(current + 1);
    container.appendChild(next);
}

function loadCalculation(item) {
    document.getElementById('fee-total').value = formatCurrency(item.total);
    document.getElementById('fee-installments').value = item.installments;
    document.getElementById('fee-start-date').value = item.startDate;
    
    const container = document.getElementById('lawyers-list');
    container.innerHTML = '';
    item.lawyers.forEach((name, idx) => {
        const div = document.createElement('div');
        div.className = 'lawyer-input-group';
        div.style.display = 'flex';
        div.style.gap = '0.5rem';
        div.innerHTML = `
            <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 1;" value="${name}">
            ${idx > 0 ? '<button type="button" class="btn btn-ghost remove-lawyer" style="color: #f87171;">&times;</button>' : ''}
        `;
        container.appendChild(div);
        
        const btn = div.querySelector('.remove-lawyer');
        if (btn) btn.addEventListener('click', () => div.remove());
    });

    const tbody = document.getElementById('fee-table-body');
    tbody.innerHTML = '';

    const installValue = item.total / item.installments;
    const perPerson = installValue / (item.lawyers.length || 1);
    
    const [y, m, d] = item.startDate.split('-').map(Number);
    let currentDate = new Date(y, m - 1, d, 12, 0, 0);

    for (let i = 1; i <= item.installments; i++) {
        const row = document.createElement('tr');
        const dateFmt = currentDate.toLocaleDateString('pt-BR');
        const gcalLink = generateGCalLink(currentDate, installValue, i, item.installments);

        row.innerHTML = `
            <td>${i}x</td>
            <td>${dateFmt}</td>
            <td>${formatCurrency(installValue)}</td>
            <td>${formatCurrency(perPerson)} <br><small style="font-size: 0.75rem; color: var(--text-muted)">p/ ${item.lawyers.join(', ') || 'Advogado'}</small></td>
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
    
    const lawyerInputs = document.querySelectorAll('.lawyer-name');
    const lawyers = Array.from(lawyerInputs).map(i => i.value).filter(v => v.trim() !== "");
    const splitCount = lawyers.length || 1;

    if (isNaN(total) || total <= 0) {
        alert("Digite um valor válido.");
        return;
    }

    try {
        await fetch('api/fees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                total,
                installments,
                startDate: startDateStr,
                lawyers
            })
        });
        loadHistory(1);
    } catch (e) {
        console.error('Error saving calculation', e);
    }

    const tbody = document.getElementById('fee-table-body');
    tbody.innerHTML = '';

    const installValue = total / installments;
    const perPerson = installValue / splitCount;
    
    const [y, m, d] = startDateStr.split('-').map(Number);
    let currentDate = new Date(y, m - 1, d, 12, 0, 0);

    for (let i = 1; i <= installments; i++) {
        const row = document.createElement('tr');
        
        const dateFmt = currentDate.toLocaleDateString('pt-BR');
        
        const gcalLink = generateGCalLink(currentDate, installValue, i, installments);

        row.innerHTML = `
            <td>${i}x</td>
            <td>${dateFmt}</td>
            <td>${formatCurrency(installValue)}</td>
            <td>${formatCurrency(perPerson)} <br><small style="font-size: 0.75rem; color: var(--text-muted)">p/ ${lawyers.join(', ') || 'Advogado'}</small></td>
            <td><a href="${gcalLink}" target="_blank" class="gcal-icon">📅 Agendar</a></td>
        `;
        tbody.appendChild(row);

        currentDate.setMonth(currentDate.getMonth() + 1);
    }

    document.getElementById('fee-summary').innerText = `Total: ${formatCurrency(total)}`;
    document.getElementById('fee-results').style.display = 'block';
}

function formatCurrency(val) {
    return val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function generateGCalLink(date, val, current, total) {
    const title = encodeURIComponent(`Recebimento Honorários (${current}/${total})`);
    
    // Format YYYYMMDD
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const dateStr = `${y}${m}${d}`;
    
    const details = encodeURIComponent(`Recebimento de honorários: ${formatCurrency(val)}`);
    
    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${dateStr}/${dateStr}&details=${details}`;
}
</script>

    <?php include 'src/footer.php'; ?>
    <script src="assets/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
