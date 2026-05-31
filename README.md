# Book Lending Manager

Sistema de gestão de empréstimo de livros pessoal. Desenvolvido em PHP 8.x puro, sem dependências externas, pronto para deploy via cPanel.

## Funcionalidades

- ✅ Autenticação com Google OAuth
- 📚 Cadastro e gerenciamento de livros
- 👥 Cadastro de amigos/contatos
- 📤 Registro de empréstimos
- 📊 Dashboard com estatísticas
- 🔍 Busca de livros na Open Library API
- 📱 Interface responsiva

## Requisitos

- PHP 8.0 ou superior
- MySQL 5.7 ou superior (ou MariaDB)
- Extensões PHP: PDO, PDO_MySQL, cURL, session
- Conta Google para OAuth (opcional para desenvolvimento local)

## Instalação no cPanel

### 1. Banco de Dados

1. Acesse o phpMyAdmin do cPanel
2. Crie um novo banco de dados
3. Importe o arquivo `database/schema.sql`

### 2. Configuração

1. Copie `.env.example` para `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edite `.env` com suas credenciais do banco de dados

3. Configure o Google OAuth:
   - Acesse https://console.cloud.google.com/apis/credentials
   - Crie um projeto e habilite a Google+ API
   - Crie credenciais OAuth 2.0 (Web application)
   - Adicione o redirect URI: `https://seudominio.com/auth/callback`
   - Copie o Client ID e Client Secret para o `.env`

4. (Opcional) Configure a Google Books API:
   - No mesmo projeto do OAuth, habilite "Books API"
   - Crie credenciais tipo "API Key"
   - Configure restrições da API Key (recomendado):
     * Application restrictions: HTTP referrers (seudominio.com)
     * API restrictions: Books API apenas
   - Copie a API Key para o `.env` em `GOOGLE_BOOKS_API_KEY`
   - Se deixar em branco, o sistema usará apenas Open Library

### 3. Deploy via Git (cPanel)

1. No cPanel, acesse "Git Version Control"
2. Clone este repositório
3. Configure o deploy automático para copiar arquivos para `public_html/`

### 4. Estrutura de Arquivos

Certifique-se de que a estrutura no servidor fique assim:

```
public_html/
  ├── .htaccess
  ├── index.php
  ├── css/
  │   └── style.css
  └── js/
      └── app.js
app/           # (pode estar fora de public_html ou protegido)
config/
database/
```

## Configuração Manual (sem Git)

1. Faça upload de todos os arquivos via FTP/File Manager
2. Coloque o conteúdo de `public_html/` na pasta `public_html` do cPanel
3. A pasta `app/` pode ficar fora de `public_html` para maior segurança
4. Ajuste o `BASE_PATH` em `public_html/index.php` se necessário

## Estrutura do Projeto

```
├── app/
│   ├── core/           # Classes base (Database, Router, Auth)
│   ├── models/         # Modelos (User, Book, Borrower, Loan)
│   ├── controllers/    # Controladores
│   └── views/          # Templates
├── config/             # Arquivos de configuração
├── database/
│   └── schema.sql      # Estrutura do banco
├── public_html/        # Document root
│   ├── index.php       # Entry point
│   ├── css/
│   └── js/
├── .env.example        # Template de configuração
└── README.md
```

## Tecnologias

- **Backend**: PHP 8.x vanilla (sem frameworks)
- **Database**: MySQL com PDO
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Auth**: Google OAuth 2.0 (implementação manual)
- **API**: Open Library API

## Segurança

- Prepared statements PDO (proteção contra SQL Injection)
- Escape de output HTML (proteção contra XSS)
- CSRF tokens em todos os formulários
- Session security com regenerate ID
- OAuth state parameter para prevenir CSRF

## Desenvolvimento Local

1. Clone o repositório
2. Copie `.env.example` para `.env` e configure
3. Crie um banco de dados local
4. Importe `database/schema.sql`
5. Configure um virtual host apontando para `public_html/`
6. Acesse `http://localhost` (ou seu virtual host)

## Testes

A suíte é escrita em PHP puro (sem dependências) e fica em `tests/`:

- `ConfigTest` — extensões, arquivos e configs presentes
- `SecurityTest` — escape XSS, prepared statements
- `UnitTest` — Env, Router, Auth
- `IntegrationTest` — fluxos (criação de usuário, empréstimo, busca)
- `ViewContractTest` — garante que toda chave `$loan/$book/$borrower['x']` usada nas views é produzida pelo `*ToArray()` do controller correspondente (protege contra descompasso controller↔view)

### Rodar com PHP local

```bash
php tests/run.php
```

### Rodar via Docker (PHP 8.1, igual ao servidor)

Não precisa de PHP instalado na máquina. O `pdo_mysql` é instalado no container só para o teste de extensão refletir o ambiente de produção:

```bash
docker run --rm -v "$PWD":/app -w /app php:8.1-cli \
  sh -c "docker-php-ext-install pdo_mysql >/dev/null 2>&1; php tests/run.php"
```

A suíte completa deve terminar com `ALL TESTS PASSED` (82 testes, 0 falhas). O `run.php` marca como erro qualquer suíte que aborte sem imprimir o resumo, evitando falso-verde.

## Solução de Problemas

### Erro de conexão com banco de dados
- Verifique as credenciais no arquivo `.env`
- Confirme que o usuário MySQL tem permissões no banco

### OAuth não funciona
- Verifique se o redirect URI está configurado corretamente no Google Cloud Console
- Certifique-se de que a URL corresponde exatamente (incluindo https/http)

### Erro 500
- Verifique os logs de erro do Apache/cPanel
- Confirme que todas as extensões PHP necessárias estão ativadas

## Licença

MIT License - Sinta-se livre para usar e modificar.

## Contato

Para dúvidas ou sugestões, abra uma issue no repositório.
