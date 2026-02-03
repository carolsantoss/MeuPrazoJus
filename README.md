# MeuPrazoJus - Calculadora de Prazos Processuais

**MeuPrazoJus** é uma aplicação web moderna para o cálculo de prazos processuais e penais, desenvolvida com foco na legislação brasileira (Novo CPC) e integração com ferramentas de produtividade.

## 🚀 Funcionalidades

- **Calculadora Inteligente**:
  - Contagem em **Dias Úteis** (conforme Novo CPC).
  - Contagem em **Dias Corridos** (Direito Material/Penal).
  - Detecção automática de **Feriados Nacionais** e Móveis (Páscoa, Carnaval, Corpus Christi).
  - Consideração automática do **Recesso Forense** (20 dez a 20 jan).

- **Gestão de Assinaturas (SaaS)**:
  - Sistema de **Free Trial**: Limite de 5 cálculos gratuitos para visitantes.
  - Autenticação de Usuários (Login/Cadastro).
  - Mockup de fluxo de assinatura Premium (Anual).

- **Integração**:
  - **Google Agenda**: Crie eventos automaticamente com a data final do prazo calculado.

## 🛠️ Tecnologias

- **Backend**: PHP 8+ (Vanilla)
- **Frontend**: HTML5, CSS3 (Glassmorphism UI), JavaScript.
- **Banco de Dados**: JSON (para portabilidade e demo) / SQLite (configurável).

## 📦 Como Rodar Localmente

1. Clone o repositório:
   ```bash
   git clone https://github.com/SEU_USUARIO/meuprazojus.git
   ```

2. Inicie o servidor embutido do PHP:
   ```bash
   cd meuprazojus
   php -S localhost:8000
   ```

3. Acesse no navegador:
   [http://localhost:8000](http://localhost:8000)

## 📄 Licença

Este projeto está sob a licença MIT.
