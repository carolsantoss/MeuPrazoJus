<?php
$posts = [
    [
        'id' => 1,
        'title' => 'Como calcular prazo no Novo CPC usando nossa calculadora gratuita',
        'excerpt' => 'Aprenda o passo a passo para contar prazos processuais e dias úteis segundo o Código de Processo Civil e evite dores de cabeça.',
        'content' => '
            <p>O advento do <strong>Novo Código de Processo Civil (CPC/2015)</strong> trouxe uma das mudanças mais aguardadas pelos advogados brasileiros: a contagem de prazos em dias úteis. Contudo, essa facilidade trouxe também o desafio de mapear corretamente os feriados, suspensões e recessos de diferentes tribunais e comarcas espalhados pelo Brasil.</p>

            <h2>A Importância da Contagem Precisa</h2>
            <p>A perda de um prazo processual é, indiscutivelmente, um dos maiores pesadelos para a atuação jurídica. Além de precluir o direito do cliente, expõe o profissional a responsabilizações civis e perdas de credibilidade.</p>
            <p>O artigo 219 do CPC determina de forma translúcida: <em>"Na contagem de prazo em dias, estabelecido por lei ou pelo juiz, computar-se-ão somente os dias úteis."</em></p>
            
            <h2>Como usar o MeuPrazoJus para isso?</h2>
            <p>A ferramenta do <strong>MeuPrazoJus</strong> foi desenvolvida exatamente para mitigar esse risco e reduzir o tempo gasto na conferência de calendários estaduais. Para simular seu prazo, basta seguir estes passos:</p>
            <ol>
                <li>Na página inicial, informe o tribunal que está julgando seu processo.</li>
                <li>Insira a data em que a publicação foi disponibilizada no Diário Oficial. O sistema considerará automaticamente o início do prazo no dia útil subsequente.</li>
                <li>Selecione o tipo de rito ou a quantidade de dias úteis estabelecida (15 dias para Apelação e Agravo de Instrumento, 5 dias para Embargos de Declaração, etc.).</li>
            </ol>

            <div style="background: rgba(197, 160, 89, 0.1); border-left: 4px solid var(--primary); padding: 1rem; border-radius: 4px; margin: 2rem 0;">
                💡 <strong>Dica Prática:</strong> Não confie apenas em estimativas mentais! Feriados estaduais e municipais muitas vezes caem em dias atípicos ou são transferidos por portarias dos tribunais que você pode desconhecer.
            </div>

            <h2>Lembrete de Suspensão de Prazos</h2>
            <p>Verifique sempre a suspensão no período de 20 de dezembro a 20 de janeiro (recesso forense e férias da advocacia), um recurso que a nossa calculadora também processa automaticamente de acordo com as diretrizes do CNJ.</p>
            <p>Portanto, economize tempo e ganhe segurança. Utilize diariamente a <strong>nossa Calculadora de Prazos Processuais</strong> como primeiro filtro na estruturação da sua agenda jurídica!</p>
        ',
        'date' => date('d/m/Y', strtotime('-2 days')),
        'category' => 'Processo Civil'
    ],
    [
        'id' => 2,
        'title' => 'O que é a suspensão de prazos no fim do ano (Recesso Forense)?',
        'excerpt' => 'O recesso do judiciário impacta a contagem dos prazos. Entenda como funciona a suspensão e como não perder dias vitais...',
        'content' => '
            <p>Seja você um recém-aprovado na OAB ou um profissional sênior, o fim do ano sempre traz uma preocupação em escritórios de advocacia de todo o país: a gestão da <strong>suspensão dos prazos processuais</strong>.</p>

            <h2>Qual é o período do recesso forense?</h2>
            <p>A Lei nº 13.105/2015 (CPC) unificou uma demanda antiga da classe advocatícia: ter férias. Conforme prevê expressamente o art. 220:</p>
            <blockquote style="font-style: italic; border-left: 2px solid #555; padding-left: 1rem; color: #bbb;">
                "Suspende-se o curso do prazo processual nos dias compreendidos entre 20 de dezembro e 20 de janeiro, inclusive."
            </blockquote>

            <h2>Como a suspensão afeta os meus prazos diários?</h2>
            <p>O principal efeito dessa norma é que, mesmo que o cartório judiciário continue realizando publicações eletrônicas ou expedientes internos, os <strong>prazos processuais param de fluir a partir de 20 de dezembro</strong> e só voltam a contar (os dias restantes) a partir de <strong>21 de janeiro</strong>.</p>
            <ul>
                <li><strong>Exemplo:</strong> Se um prazo de 15 dias úteis se iniciou no dia 15 de dezembro, contam-se os dias úteis até 19 de dezembro. Aí há uma "pausa", e os dias restantes da contagem começam a rodar novamente apenas em 21 de janeiro.</li>
            </ul>

            <h2>Tribunais Superiores e Justiça Especializada</h2>
            <p>Um cuidado essencial é com os Tribunais Superiores (STF, STJ, TST), que seguem o mesmo calendário garantido pelo CPC, e com os cartórios estaduais e plantões. As audiências e sessões de julgamento também ficam proibidas de ocorrer nesse intervalo. Na <strong>Justiça do Trabalho</strong>, essa mesma regra foi absorvida no art. 775, §1º, da CLT moderna.</p>

            <h2>Automatização contra falhas</h2>
            <p>Como fazer essa contabilidade se o seu artigo cai no meio da suspensão? Ao invés de usar calendários impressos e contar dedo a dedo, garantindo que "pulou" os dias certos, insira a data da Intimação na <strong>calculadora disponibilizada no MeuPrazoJus</strong> e o sistema excluirá esse intervalo para você, apontando o exato dia fatal em janeiro ou fevereiro.</p>
        ',
        'date' => date('d/m/Y', strtotime('-5 days')),
        'category' => 'Dicas Práticas'
    ]
];
