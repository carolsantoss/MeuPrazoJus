<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Assinaturas -
        <?php echo htmlspecialchars($documento['document_hash']); ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { brand: '#3B82F6', dark: '#0F172A', dark_card: '#1E293B' } }
            }
        }
    </script>
    <style>
        body {
            background-color: #0F172A;
            color: #F8FAFC;
        }

        .page-container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #ffffff;
            color: #1a1a1a;
            padding: 40px;
            border-radius: 8px;
        }

        @media print {
            body {
                background: white;
                margin: 0;
            }

            .page-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>

<body class="bg-dark antialiased px-4">

    <div class="page-container shadow-2xl">

        <header class="flex justify-between items-start mb-12 border-b-2 border-slate-200 pb-4">
            <div>
                <div class="font-bold text-3xl tracking-tighter text-slate-800">
                    FC<span class="text-brand">.</span>
                </div>
            </div>
            <div class="text-right text-xs text-slate-600">
                <p>Data e horários em GMT -3:00</p>
                <p>Última atualização em
                    <?php echo date('d M Y \à\s H:i', strtotime($documento['updated_at'])); ?>
                </p>
                <p>Identificador:
                    <?php echo htmlspecialchars($documento['document_hash']); ?>
                </p>
            </div>
        </header>

        <div class="text-center mb-16">
            <h1 class="text-2xl font-bold text-slate-800">Página de assinaturas</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-12 mb-16 justify-items-center">
            <?php foreach ($signatarios as $sig): ?>
                <?php if ($sig['status'] == 'Assinado'): ?>
                    <div class="text-center w-full max-w-[250px]">
                        <div class="border-b border-black mb-1 pb-1 relative h-12 flex items-end justify-center">
                            <span style="font-family: 'Brush Script MT', cursive;" class="text-3xl text-slate-700 opacity-80">
                                <?php echo htmlspecialchars(explode(' ', $sig['name'])[0]); ?>
                            </span>
                        </div>
                        <div class="font-bold text-sm text-slate-800">
                            <?php echo htmlspecialchars($sig['name']); ?>
                        </div>
                        <div class="text-sm font-semibold text-slate-800">
                            <?php echo formatCpf($sig['cpf']); ?>
                        </div>
                        <div class="text-xs text-slate-600">Signatário</div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="mb-12">
            <h3 class="font-bold text-sm tracking-widest text-slate-500 uppercase border-b border-slate-300 pb-2 mb-4">
                Histórico</h3>
            <div class="space-y-4">
                <?php foreach ($logs as $log): ?>
                    <?php
                    $dt = new DateTime($log['created_at']);
                    $dataStr = $dt->format('d M Y'); 
                    $horaStr = $dt->format('H:i:s');
                    ?>
                    <div class="flex gap-4 text-sm">
                        <div class="w-24 flex-shrink-0 text-right text-slate-600 font-medium">
                            <div>
                                <?php echo $dataStr; ?>
                            </div>
                            <div>
                                <?php echo $horaStr; ?>
                            </div>
                        </div>
                        <div class="w-6 flex items-center justify-center flex-shrink-0 mt-1">
                            <?php echo getIconForAction($log['action_type']); ?>
                        </div>
                        <div class="flex-1 text-slate-700">
                            <span class="font-bold">
                                <?php echo htmlspecialchars($log['actor_name']); ?>
                            </span>

                            <?php if ($log['actor_cpf'] && $log['actor_phone']): ?>
                                <span class="italic text-slate-500">(Celular:
                                    <?php echo formatPhone($log['actor_phone']); ?>, CPF:
                                    <?php echo formatCpf($log['actor_cpf']); ?>)
                                </span>
                            <?php endif; ?>

                            <?php
                            if ($log['action_type'] == 'Criou') {
                                echo "criou este documento.";
                            } else {
                                echo strtolower($log['action_type']) . " este documento por meio do IP <span class='font-mono'>{$log['ip_address']}</span> localizado em " . htmlspecialchars($log['geolocation']);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($logs)): ?>
                    <p class="text-slate-500 text-sm">Nenhum evento registrado até o momento.</p>
                <?php endif; ?>
            </div>
        </div>

        <footer
            class="mt-20 pt-8 border-t border-slate-200 text-center text-xs text-slate-500 flex flex-col items-center">
            <div class="w-12 h-12 text-brand mb-2 opacity-50">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                </svg>
            </div>
            <p>Escaneie a imagem para verificar a autenticidade do documento</p>
            <p>Hash SHA256 do PDF original
                <?php echo htmlspecialchars($documento['original_hash']); ?>
            </p>
            <p class="font-bold mt-1 text-slate-600">https://meuprazojus.com.br/validar/
                <?php echo htmlspecialchars($documento['document_hash']); ?>
            </p>

            <div class="absolute bottom-10 right-10">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=https://meuprazojus.com.br/validar/<?php echo htmlspecialchars($documento['document_hash']); ?>"
                    alt="QR Code" class="w-16 h-16">
            </div>
        </footer>

    </div>

</body>

</html>