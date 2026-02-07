<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Assinatura - MeuPrazoJus</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <style>
        .checkout-container {
            max-width: 600px;
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
        #wallet_container {
            margin-top: 20px;
        }
        .loading-overlay {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 40px;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--glass-border);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index.php" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div>
                   <a href="subscription.php" class="btn btn-ghost">Alterar Plano</a>
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

            <div id="payment-section">
                <div id="loading-payment" class="loading-overlay">
                    <div class="spinner"></div>
                    <p style="color: var(--text-muted);">Preparando pagamento seguro...</p>
                </div>
                <div id="wallet_container"></div>
                
                <!-- New Result Area for Pix/Boleto -->
                <div id="payment-result-area" style="display: none; margin-top: 20px; text-align: center; background: rgba(255,255,255,0.05); padding: 25px; border-radius: 15px; border: 1px solid var(--glass-border);">
                    <div id="pix-result" style="display: none;">
                        <h3 style="color: white; margin-bottom: 15px;">Pague com Pix</h3>
                        <div id="pix-qr-container" style="margin-bottom: 15px; background: white; padding: 10px; display: inline-block; border-radius: 10px;">
                            <img id="pix-qr-img" src="" alt="QR Code Pix" style="width: 200px; height: 200px;">
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 10px;">Escaneie o QR Code ou copie a chave abaixo:</p>
                        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <input type="text" id="pix-code" readonly style="flex: 1; font-size: 0.8rem;">
                            <button onclick="copyPix()" class="btn btn-primary" style="white-space: nowrap;">Copiar</button>
                        </div>
                    </div>

                    <div id="boleto-result" style="display: none;">
                        <h3 style="color: white; margin-bottom: 15px;">Boleto Gerado</h3>
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Seu boleto foi gerado com sucesso. Clique no botão abaixo para visualizar o PDF.</p>
                        <a id="boleto-link" href="#" target="_blank" class="btn btn-primary btn-block">Visualizar Boleto</a>
                    </div>
                    
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 20px;">
                        💡 Sua assinatura será ativada automaticamente assim que o pagamento for confirmado.
                    </p>
                </div>
            </div>

            <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 2rem;">
                Pagamento processado com segurança pelo <strong>Mercado Pago</strong>.
            </p>
        </div>
    </main>

    <script>
        const mp = new MercadoPago('<?php echo MP_PUBLIC_KEY; ?>', {
            locale: 'pt-BR'
        });

        const bricksBuilder = mp.bricks();

        const renderPaymentBrick = async (bricksBuilder) => {
            const settings = {
                initialization: {
                    amount: 50.00,
                    payer: {
                        email: "<?php echo $user_email; ?>",
                    },
                },
                customization: {
                    paymentMethods: {
                        ticket: "all",
                        bankTransfer: "all",
                        creditCard: "all",
                        debitCard: "all",
                        mercadoPago: "all",
                    },
                },
                callbacks: {
                    onReady: () => {
                        console.log('Brick pronto (onReady)');
                        document.getElementById('loading-payment').style.display = 'none';
                    },
                    onSubmit: ({ selectedPaymentMethod, formData }) => {
                        console.log('Botão Pagar clicado (onSubmit)');
                        return new Promise((resolve, reject) => {
                            fetch("api/process_payment.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify(formData),
                            })
                            .then((response) => {
                                return response.json();
                            })
                            .then((data) => {
                                console.log('Dados processados:', data);
                                if (data.status === 'approved') {
                                    window.location.href = 'api/payment_callback.php?status=success&payment_id=' + data.id;
                                } else if (data.pix) {
                                    document.getElementById('wallet_container').style.display = 'none';
                                    document.getElementById('payment-result-area').style.display = 'block';
                                    document.getElementById('pix-result').style.display = 'block';
                                    document.getElementById('pix-qr-img').src = `data:image/png;base64, ${data.pix.qr_code_base64}`;
                                    document.getElementById('pix-code').value = data.pix.qr_code;
                                    resolve();
                                } else if (data.boleto_url) {
                                    document.getElementById('wallet_container').style.display = 'none';
                                    document.getElementById('payment-result-area').style.display = 'block';
                                    document.getElementById('boleto-result').style.display = 'block';
                                    document.getElementById('boleto-link').href = data.boleto_url;
                                    resolve();
                                } else if (data.status === 'in_process' || data.status === 'pending') {
                                    window.location.href = 'api/payment_callback.php?status=pending&payment_id=' + data.id;
                                } else if (data.status === 'rejected') {
                                    let msg = 'Pagamento recusado.';
                                    if (data.status_detail === 'cc_rejected_other_reason') {
                                        msg = 'Pagamento recusado. Se estiver em modo de teste, use um cartão de teste válido. Caso contrário, contate seu banco.';
                                    } else if (data.status_detail === 'cc_rejected_bad_filled_card_number') {
                                        msg = 'Verifique o número do cartão.';
                                    } else if (data.status_detail === 'cc_rejected_bad_filled_date') {
                                        msg = 'Verifique a data de validade.';
                                    } else if (data.status_detail === 'cc_rejected_bad_filled_security_code') {
                                        msg = 'Verifique o código de segurança.';
                                    } else if (data.status_detail === 'cc_rejected_bad_filled_other') {
                                        msg = 'Verifique os dados do cartão.';
                                    } else if (data.status_detail === 'cc_rejected_insufficient_amount') {
                                        msg = 'Saldo insuficiente.';
                                    } else {
                                        msg = 'Motivo: ' + data.status_detail;
                                    }
                                    alert(msg);
                                    resolve();
                                } else {
                                    console.error('Erro:', data);
                                    alert('Pagamento não processado: ' + (data.status_detail || 'Erro inesperado'));
                                    resolve();
                                }
                            })
                            .catch((error) => {
                                console.error("Process error:", error);
                                alert('Erro de conexão ao processar pagamento.');
                                reject();
                            });
                        });
                    },
                    onError: (error) => {
                        console.error("Payment Brick Error:", error);
                        document.getElementById('loading-payment').innerHTML = 
                            '<p style="color: #ef4444;">Erro ao carregar o formulário de pagamento. Tente recarregar a página.</p>';
                    },
                },
            };
            try {
                window.paymentBrickController = await bricksBuilder.create(
                    "payment",
                    "wallet_container",
                    settings
                );
            } catch (e) {
                console.error("Brick Creation Failed:", e);
            }
        };

        function copyPix() {
            const copyText = document.getElementById("pix-code");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Código Pix copiado!");
        }

        renderPaymentBrick(bricksBuilder);
    </script>

    <?php include 'src/footer.php'; ?>
</body>
</html>
