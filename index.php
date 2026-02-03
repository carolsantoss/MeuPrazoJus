<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrazoLegal - Calculadora de Prazos Processuais</title>
    <link rel="stylesheet" href="assets/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <div class="logo">PrazoLegal</div>
                <div>
                   <?php session_start(); if(isset($_SESSION['user_id'])): ?>
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
    
    <script>
     async function logout() {
        await fetch('api/auth.php?action=logout');
        window.location.reload();
     }
    </script>

    <main>
        <h1>Domine seus Prazos</h1>
        <p class="subtitle">Calculadora de prazos processuais atualizada conforme o Novo CPC e recesso forense.</p>

        <div class="calculator-card">
            <form id="calc-form">
                <div class="form-group">
                    <label for="start_date">Data da Publicação / Intimação</label>
                    <input type="date" id="start_date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="days">Prazo (em dias)</label>
                    <input type="number" id="days" name="days" placeholder="Ex: 5, 10, 15" required min="1">
                </div>

                <div class="form-group">
                    <label>Tipo de Contagem</label>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <label style="color: white; cursor: pointer;">
                            <input type="radio" name="type" value="working" checked> 
                            Dias Úteis (Novo CPC)
                        </label>
                        <label style="color: white; cursor: pointer;">
                            <input type="radio" name="type" value="calendar"> 
                            Dias Corridos
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Calcular Prazo</button>
            </form>

            <div class="limit-alert"></div>

            <div id="results-area">
                <div class="result-main">
                    <div class="result-label">Prazo Final</div>
                    <div class="result-date" id="result-date">...</div>
                </div>
                
                <div class="log-container" id="log-details">
                    <!-- Steps go here -->
                </div>

                <a href="#" target="_blank" id="gcal-link" class="btn gcal-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Adicionar ao Google Agenda
                </a>
            </div>
        </div>
    </main>

    <script src="assets/script.js"></script>
</body>
</html>
