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

            <div class="order-summary" id="checkout-panel">
                <form id="checkout-form">
                    <div style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: center;">
                        <button type="button" class="btn btn-primary" id="tab-cc" style="flex:1" onclick="selectPayment('CREDIT_CARD')">Cartão</button>
                        <button type="button" class="btn btn-ghost" id="tab-pix" style="flex:1; border: 1px solid var(--glass-border);" onclick="selectPayment('PIX')">PIX</button>
                        <button type="button" class="btn btn-ghost" id="tab-boleto" style="flex:1; border: 1px solid var(--glass-border);" onclick="selectPayment('BOLETO')">Boleto</button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div class="form-group">
                            <label id="lbl_cpf" for="cpf_cnpj">CPF do Pagador</label>
                            <input type="text" id="cpf_cnpj" placeholder="000.000.000-00" required autocomplete="off">
                        </div>

                        <div id="credit-card-form" style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="creditCardNumber">Número do Cartão</label>
                                <input type="text" id="creditCardNumber" placeholder="0000 0000 0000 0000" maxlength="19" required autocomplete="cc-number">
                            </div>
                            
                            <div class="form-group">
                                <label for="creditCardHolderName">Nome Impresso no Cartão</label>
                                <input type="text" id="creditCardHolderName" placeholder="Ex: JOAO M SILVA" required autocomplete="cc-name" style="text-transform: uppercase;">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                                <div class="form-group">
                                    <label for="expiryMonth">Mês (MM)</label>
                                    <input type="text" id="expiryMonth" placeholder="Ex: 12" maxlength="2" required>
                                </div>
                                <div class="form-group">
                                    <label for="expiryYear">Ano (AAAA)</label>
                                    <input type="text" id="expiryYear" placeholder="Ex: 2029" maxlength="4" required>
                                </div>
                                <div class="form-group">
                                    <label for="ccv">CVV</label>
                                    <input type="text" id="ccv" placeholder="123" maxlength="4" required autocomplete="cc-csc">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="btn-pay" class="btn btn-primary btn-block" style="margin-top: 15px; font-size: 1.1rem; padding: 15px;">Confirmar Pagamento</button>
                </form>
                <div id="error-msg" style="color: #f87171; text-align: center; margin-top: 15px; display: none;"></div>
            </div>

            <div class="order-summary" id="pix-panel" style="display: none; text-align: center;">
                <h3 style="margin-bottom: 15px; color: #10b981;">PIX Gerado com Sucesso!</h3>
                <p style="margin-bottom: 20px;">Use o aplicativo do seu banco para ler o QR Code ou copie o código Pix abaixo:</p>
                <img id="pix-qrcode" src="" alt="QR Code Pix" style="max-width: 250px; margin: 0 auto; border-radius: 10px; border: 2px solid white;">
                
                <div class="form-group" style="margin-top: 20px;">
                    <label>Pix Copia e Cola:</label>
                    <input type="text" id="pix-copy-text" readonly style="text-align: center;">
                </div>
                <button type="button" class="btn btn-secondary btn-block" onclick="copyPix()" style="margin-top: 10px;"> Copiar Código PIX</button>
                
                <div style="margin-top: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border-radius: 10px;">
                    <p style="font-size: 0.9rem; color: #10b981;">⏱️ Após o pagamento, sua assinatura será liberada automaticamente em instantes.</p>
                </div>
            </div>

            <div class="order-summary" id="boleto-panel" style="display: none; text-align: center;">
                <h3 style="margin-bottom: 15px; color: #3b82f6;">Boleto Gerado com Sucesso!</h3>
                <p style="margin-bottom: 20px;">Clique no botão abaixo para visualizar e imprimir seu boleto:</p>
                
                <a id="boleto-link" href="#" target="_blank" class="btn btn-primary btn-block" style="margin-top: 10px; background-color: #3b82f6;">Visualizar Boleto</a>
                
                <div style="margin-top: 20px; padding: 15px; background: rgba(59, 130, 246, 0.1); border-radius: 10px;">
                    <p style="font-size: 0.9rem; color: #60a5fa;">⏱️ Boletos podem levar até 2 dias úteis para compensar após o pagamento.</p>
                </div>
            </div>

            <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 2rem;">
                🔒 Pagamento processado com criptografia 256-bits direto na provedora <strong>Asaas</strong>. Seus dados não são armazenados.
            </p>
        </div>
    </main>

    <script>
        // Formatações e Máscaras Reais
        const cpfInput = document.getElementById('cpf_cnpj');
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            }
            e.target.value = value;
        });

        const ccInput = document.getElementById('creditCardNumber');
        ccInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            value = value.replace(/(\d{4})(\d)/, "$1 $2");
            value = value.replace(/(\d{4}) (\d{4})(\d)/, "$1 $2 $3");
            value = value.replace(/(\d{4}) (\d{4}) (\d{4})(\d)/, "$1 $2 $3 $4");
            e.target.value = value;
        });

        const mInput = document.getElementById('expiryMonth');
        mInput.addEventListener('input', function(e) { e.target.value = e.target.value.replace(/\D/g, ""); });
        
        const yInput = document.getElementById('expiryYear');
        yInput.addEventListener('input', function(e) { e.target.value = e.target.value.replace(/\D/g, ""); });

        const cvvInput = document.getElementById('ccv');
        cvvInput.addEventListener('input', function(e) { e.target.value = e.target.value.replace(/\D/g, ""); });

        let currentPaymentMethod = 'CREDIT_CARD';
        function selectPayment(method) {
            currentPaymentMethod = method;
            document.getElementById('tab-cc').className = method === 'CREDIT_CARD' ? 'btn btn-primary' : 'btn btn-ghost';
            document.getElementById('tab-pix').className = method === 'PIX' ? 'btn btn-primary' : 'btn btn-ghost';
            document.getElementById('tab-boleto').className = method === 'BOLETO' ? 'btn btn-primary' : 'btn btn-ghost';
            
            ['tab-cc','tab-pix','tab-boleto'].forEach(id => {
               if(document.getElementById(id).classList.contains('btn-ghost')){
                   document.getElementById(id).style.border = '1px solid var(--glass-border)';
               } else {
                   document.getElementById(id).style.border = 'none';
               }
            });

            document.getElementById('credit-card-form').style.display = method === 'CREDIT_CARD' ? 'grid' : 'none';
            document.getElementById('lbl_cpf').innerText = method === 'CREDIT_CARD' ? 'CPF do Titular do Cartão' : 'CPF do Pagador';
            
            // Toggle required attributes for CC inputs
            const ccInputs = document.querySelectorAll('#credit-card-form input');
            ccInputs.forEach(input => {
                input.required = (method === 'CREDIT_CARD');
            });
            
            const btnPay = document.getElementById('btn-pay');
            if(method === 'CREDIT_CARD') btnPay.innerText = 'Confirmar Pagamento';
            if(method === 'PIX') btnPay.innerText = 'Gerar PIX';
            if(method === 'BOLETO') btnPay.innerText = 'Gerar Boleto';
        }

        function copyPix() {
            const copyText = document.getElementById("pix-copy-text");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Código Pix copiado!");
        }

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

            const payload_data = {
                billingType: currentPaymentMethod,
                cpfCnpj: cpfCnpj
            };

            if (currentPaymentMethod === 'CREDIT_CARD') {
                payload_data.creditCard = {
                    holderName: document.getElementById('creditCardHolderName').value.trim(),
                    number: ccInput.value.replace(/\D/g, ""),
                    expiryMonth: document.getElementById('expiryMonth').value,
                    expiryYear: document.getElementById('expiryYear').value,
                    ccv: cvvInput.value
                };
            }

            btn.innerText = "Aguarde...";
            btn.disabled = true;
            errorMsg.style.display = "none";

            try {
                const res = await fetch("api/process_payment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload_data)
                });

                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Resposta bruta do servidor:", text);
                    errorMsg.innerText = "Erro gravíssimo: A resposta do Asaas não pôde ser lida.";
                    errorMsg.style.display = "block";
                    btn.disabled = false;
                    btn.innerText = originalText;
                    return;
                }
                
                if (data.success && data.status === 'CONFIRMED') {
                    // Magia acontecendo! Foi instantâneo. Sem sair da página para cartão.
                    btn.innerText = "Pagamento Aprovado! ✓";
                    btn.style.backgroundColor = "#10b981"; // Verdinho
                    setTimeout(() => {
                        window.location.href = "api/payment_callback.php?status=success&payment_id=" + (data.payment_id || '');
                    }, 1500);
                } else if (data.success && data.billingType === 'PIX' && data.pix) {
                    // Mostrar QR Code do PIX
                    document.getElementById('checkout-panel').style.display = 'none';
                    document.getElementById('pix-panel').style.display = 'block';
                    document.getElementById('pix-qrcode').src = `data:image/png;base64,${data.pix.encodedImage}`;
                    document.getElementById('pix-copy-text').value = data.pix.payload;
                } else if (data.success && data.billingType === 'BOLETO' && data.boletoUrl) {
                    // Mostrar Link do Boleto
                    document.getElementById('checkout-panel').style.display = 'none';
                    document.getElementById('boleto-panel').style.display = 'block';
                    document.getElementById('boleto-link').href = data.boletoUrl;
                } else if (data.error) {
                    errorMsg.innerText = data.error;
                    errorMsg.style.display = "block";
                    btn.disabled = false;
                    btn.innerText = originalText;
                } else {
                    errorMsg.innerText = "Ação não pôde ser completada.";
                    errorMsg.style.display = "block";
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            } catch (err) {
                errorMsg.innerText = "Erro ao conectar com nosso servidor de pagamentos.";
                errorMsg.style.display = "block";
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    </script>
</body>
</html>
