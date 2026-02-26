<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

require_once 'src/UserManager.php';
$userManager = new UserManager();

if (!isset($_SESSION['user_email'])) {
    $user = $userManager->getUserById($_SESSION['user_id']);
    if ($user) {
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
    }
}

$user_name = $_SESSION['user_name'] ?? 'Usuário';
$user_email = $_SESSION['user_email'] ?? '';

// Prevent double subscription
if (isset($_SESSION['is_subscribed']) && $_SESSION['is_subscribed']) {
    header('Location: index');
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Assinatura - MeuPrazoJus</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime('assets/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .checkout-container {
            max-width: 500px;
            margin: 40px auto;
            width: 100%;
        }
        .order-summary {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(20px);
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--glass-border);
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .item-info {
            flex: 1;
            padding-right: 20px;
        }
        .item-info h3 {
            font-size: 1.25rem;
            color: white;
            margin-bottom: 4px;
        }
        .item-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .item-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            white-space: nowrap;
        }
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            margin-top: 10px;
        }
        .total-label {
            font-size: 1.1rem;
            color: var(--text-muted);
        }
        .total-amount {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-muted);
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            margin-bottom: 15px;
        }
    </style>
    <?php include 'src/google_adsense.php'; ?>
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div>
                   <a href="subscription" class="btn btn-ghost">Alterar Plano</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="checkout-container">
            <h2 style="margin-bottom: 2rem; text-align: center;">Finalizar Assinatura</h2>
            
            <div class="order-summary">
                <div class="order-item">
                    <div class="item-info">
                        <h3>Plano Anual</h3>
                        <p>Cálculos ilimitados, Integração Google Agenda e Suporte Prioritário por 12 meses.</p>
                    </div>
                    <div class="item-price">R$ 50,00</div>
                </div>
                
                <div class="total-section">
                    <span class="total-label">Total a pagar:</span>
                    <span class="total-amount">R$ 50,00</span>
                </div>
            </div>

            <div class="order-summary">
                <form id="checkout-form">
                    <div class="form-group">
                        <label for="cpf_cnpj">CPF / CNPJ para o Faturamento</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" required placeholder="000.000.000-00" autocomplete="off">
                    </div>
                    <button type="submit" id="btn-pay" class="btn btn-primary btn-block" style="margin-top: 10px; font-size: 1.1rem; padding: 15px;">Ir para Pagamento (Seguro)</button>
                </form>
                <div id="error-msg" style="color: #f87171; text-align: center; margin-top: 15px; display: none;"></div>
            </div>

            <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 2rem;">
                Pagamento processado com segurança pelo <strong>Asaas</strong>.
            </p>
        </div>
    </main>

    <script>
        const cpfInput = document.getElementById('cpf_cnpj');
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            } else {
                value = value.replace(/^(\d{2})(\d)/, "$1.$2");
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
                value = value.replace(/\.(\d{3})(\d)/, ".$1/$2");
                value = value.replace(/(\d{4})(\d)/, "$1-$2");
            }
            e.target.value = value;
        });

        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-pay');
            const errorMsg = document.getElementById('error-msg');
            const originalText = btn.innerText;

            const cpfCnpj = cpfInput.value.replace(/\D/g, "");
            
            if (cpfCnpj.length !== 11 && cpfCnpj.length !== 14) {
                errorMsg.innerText = "Informe um CPF ou CNPJ válido.";
                errorMsg.style.display = "block";
                return;
            }

            btn.innerText = "Aguarde...";
            btn.disabled = true;
            errorMsg.style.display = "none";

            try {
                const res = await fetch("api/process_payment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ cpfCnpj: cpfCnpj })
                });

                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Resposta bruta do servidor:", text);
                    errorMsg.innerText = "Erro do servidor: A resposta não é um JSON válido. Verifique o console ou log de erros.";
                    errorMsg.style.display = "block";
                    btn.disabled = false;
                    btn.innerText = originalText;
                    return;
                }
                
                if (data.invoiceUrl) {
                    window.location.href = data.invoiceUrl;
                } else if (data.error) {
                    errorMsg.innerText = data.error;
                    errorMsg.style.display = "block";
                    btn.disabled = false;
                    btn.innerText = originalText;
                } else {
                    errorMsg.innerText = "Erro ao gerar cobrança.";
                    errorMsg.style.display = "block";
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            } catch (err) {
                errorMsg.innerText = "Erro ao conectar com o servidor.";
                errorMsg.style.display = "block";
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    </script>
</body>
</html>
