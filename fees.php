<?php include 'src/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Honorários | MeuPrazoJus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <main>
        <div class="dashboard-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="user-info">
                    <h3>Bem-vindo!</h3>
                    <p>Gerencie seus prazos.</p>
                </div>
                <nav class="side-nav">
                    <a href="index.php" class="nav-item">📊 Prazos</a>
                    <a href="fees.php" class="nav-item active">💰 Honorários</a>
                    <a href="doc_requests.php" class="nav-item">📂 Documentos</a>
                    <a href="subscription.php" class="nav-item">⭐ Assinatura</a>
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
                                    <label>Valor Total (R$)</label>
                                    <input type="number" id="fee-total" step="0.01" required placeholder="Ex: 5000,00">
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
                                    <label>Dividir entre (Advogados)</label>
                                    <input type="number" id="fee-split" min="1" value="1" required>
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
                                    <!-- JS Generated -->
                                </tbody>
                            </table>
                        </div>
                        <div class="fee-total" id="fee-summary" style="margin-top: 1.5rem; text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Set default date to today
    document.getElementById('fee-start-date').valueAsDate = new Date();

    document.getElementById('fee-form').addEventListener('submit', (e) => {
        e.preventDefault();
        calculateFees();
    });
});

function calculateFees() {
    const total = parseFloat(document.getElementById('fee-total').value);
    const installments = parseInt(document.getElementById('fee-installments').value);
    const startDateStr = document.getElementById('fee-start-date').value;
    const splitCount = parseInt(document.getElementById('fee-split').value);

    if (isNaN(total) || total <= 0) {
        alert("Digite um valor válido.");
        return;
    }

    const tbody = document.getElementById('fee-table-body');
    tbody.innerHTML = '';

    const installValue = total / installments;
    const perPerson = installValue / splitCount;
    
    // Date handling
    const [y, m, d] = startDateStr.split('-').map(Number);
    // Create date focusing on noon to avoid timezone issues on simple addition
    let currentDate = new Date(y, m - 1, d, 12, 0, 0);

    for (let i = 1; i <= installments; i++) {
        const row = document.createElement('tr');
        
        // Format Date
        const dateFmt = currentDate.toLocaleDateString('pt-BR');
        
        // Google Calendar Link
        const gcalLink = generateGCalLink(currentDate, installValue, i, installments);

        row.innerHTML = `
            <td>${i}x</td>
            <td>${dateFmt}</td>
            <td>${formatCurrency(installValue)}</td>
            <td>${formatCurrency(perPerson)} ${splitCount > 1 ? '<small class="text-muted">(cada)</small>' : ''}</td>
            <td><a href="${gcalLink}" target="_blank" class="gcal-icon">📅 Agendar</a></td>
        `;
        tbody.appendChild(row);

        // Next Month
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

</body>
</html>
