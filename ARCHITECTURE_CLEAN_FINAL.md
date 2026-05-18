# Arquitetura Clean Architecture - Versão Final

## Status: ✅ Implementação Completa

Todas as funcionalidades foram migradas para Clean Architecture.

---

## Estrutura Final do Projeto

```
app/
├── Domain/                           # ⭐ Enterprise Business Rules (núcleo)
│   ├── Entity/                      # Entidades de domínio
│   │   ├── Book.php                # Regra: lendTo(), isAvailable()
│   │   ├── Borrower.php            # Regra: update()
│   │   └── Loan.php                # Regra: markAsReturned(), isOverdue()
│   ├── ValueObject/                 # Objetos de valor
│   │   ├── BookStatus.php          # Enum: available, lent
│   │   └── LoanStatus.php          # Enum: active, returned
│   ├── Repository/                  # Interfaces (contratos)
│   │   ├── BookRepositoryInterface.php
│   │   ├── BorrowerRepositoryInterface.php
│   │   └── LoanRepositoryInterface.php
│   └── Exception/                   # Exceções de domínio
│       └── BookNotAvailableException.php
│
├── Application/                      # ⭐ Application Business Rules
│   └── UseCase/                    # Casos de uso
│       ├── Book/
│       │   ├── CreateBookUseCase.php
│       │   ├── CreateBookInput.php
│       │   ├── CreateBookOutput.php
│       │   ├── UpdateBookUseCase.php
│       │   ├── UpdateBookInput.php
│       │   ├── UpdateBookOutput.php
│       │   ├── DeleteBookUseCase.php
│       │   ├── DeleteBookInput.php
│       │   └── DeleteBookOutput.php
│       ├── Borrower/
│       │   └── (mesma estrutura)
│       └── Loan/
│           ├── LendBookUseCase.php      # ⭐ Orquestra empréstimo
│           ├── LendBookInput.php
│           ├── LendBookOutput.php
│           ├── ReturnBookUseCase.php    # ⭐ Orquestra devolução
│           ├── ReturnBookInput.php
│           └── ReturnBookOutput.php
│
├── Infrastructure/                   # ⭐ Frameworks & Drivers
│   ├── Repository/                 # Implementações concretas
│   │   ├── PdoBookRepository.php
│   │   ├── PdoBorrowerRepository.php
│   │   └── PdoLoanRepository.php
│   └── Container/                  # Dependency Injection
│       └── ServiceContainer.php
│
├── InterfaceAdapter/               # ⭐ Interface Adapters
│   └── Controller/                 # Controllers (adaptam HTTP)
│       ├── BookController.php
│       ├── BorrowerController.php
│       ├── LoanController.php
│       └── DashboardController.php
│
├── controllers/                     # ⚠️ Código legado mínimo
│   ├── AuthController.php          # OAuth (mantido - específico)
│   └── Controller.php              # Base class
│
├── core/                           # ⚠️ Infraestrutura legada
│   ├── Auth.php                    # Session + OAuth
│   ├── Database.php                # PDO Singleton (não usado mais)
│   ├── Env.php                     # Environment
│   └── Router.php                  # Roteamento
│
├── models/                         # ⚠️ Mantido apenas:
│   └── User.php                    # Para OAuth callback
│
└── views/                          # 🎨 Templates (compartilhados)
    ├── layout.php
    ├── dashboard.php
    ├── books/
    ├── borrowers/
    ├── loans/
    └── auth/
```

---

## Comparação: Antes vs Depois

### ❌ ANTES (MVC Tradicional - Arquivos Removidos)

```
app/controllers/BookController.php       ❌ REMOVIDO
app/controllers/BorrowerController.php   ❌ REMOVIDO
app/controllers/LoanController.php       ❌ REMOVIDO
app/controllers/DashboardController.php    ❌ REMOVIDO
app/models/Book.php                        ❌ REMOVIDO
app/models/Borrower.php                    ❌ REMOVIDO
app/models/Loan.php                        ❌ REMOVIDO
app/models/Model.php                       ❌ REMOVIDO
```

### ✅ DEPOIS (Clean Architecture)

```
app/InterfaceAdapter/Controller/BookController.php      ✅ NOVO
app/InterfaceAdapter/Controller/BorrowerController.php  ✅ NOVO
app/InterfaceAdapter/Controller/LoanController.php       ✅ NOVO
app/InterfaceAdapter/Controller/DashboardController.php ✅ NOVO
app/Domain/Entity/Book.php                              ✅ NOVO
app/Domain/Entity/Borrower.php                           ✅ NOVO
app/Domain/Entity/Loan.php                               ✅ NOVO
app/Domain/Repository/BookRepositoryInterface.php        ✅ NOVO
app/Infrastructure/Repository/PdoBookRepository.php     ✅ NOVO
app/Application/UseCase/CreateBookUseCase.php            ✅ NOVO
(app/Domain/UseCase/LendBookUseCase.php)                ✅ NOVO
```

---

## Rotas Atualizadas

Todas as rotas agora usam controllers namespaced:

```php
// public_html/index.php

// Dashboard
$router->get('', 'App\InterfaceAdapter\Controller\DashboardController', 'index');
$router->get('dashboard', 'App\InterfaceAdapter\Controller\DashboardController', 'index');

// Books
$router->get('books', 'App\InterfaceAdapter\Controller\BookController', 'index');
$router->get('books/add', 'App\InterfaceAdapter\Controller\BookController', 'add');
$router->post('books/store', 'App\InterfaceAdapter\Controller\BookController', 'store');
$router->get('books/{id}/edit', 'App\InterfaceAdapter\Controller\BookController', 'edit');
$router->post('books/{id}/update', 'App\InterfaceAdapter\Controller\BookController', 'update');
$router->post('books/{id}/delete', 'App\InterfaceAdapter\Controller\BookController', 'delete');

// Borrowers
$router->get('borrowers', 'App\InterfaceAdapter\Controller\BorrowerController', 'index');
$router->get('borrowers/list', 'App\InterfaceAdapter\Controller\BorrowerController', 'list');
$router->post('borrowers/store', 'App\InterfaceAdapter\Controller\BorrowerController', 'store');
$router->get('borrowers/{id}/edit', 'App\InterfaceAdapter\Controller\BorrowerController', 'edit');
$router->post('borrowers/{id}/update', 'App\InterfaceAdapter\Controller\BorrowerController', 'update');
$router->post('borrowers/{id}/delete', 'App\InterfaceAdapter\Controller\BorrowerController', 'delete');

// Loans
$router->get('loans', 'App\InterfaceAdapter\Controller\LoanController', 'index');
$router->get('loans/create', 'App\InterfaceAdapter\Controller\LoanController', 'create');
$router->post('loans/store', 'App\InterfaceAdapter\Controller\LoanController', 'store');
$router->post('loans/{id}/return', 'App\InterfaceAdapter\Controller\LoanController', 'return');
$router->get('loans/history/{bookId}', 'App\InterfaceAdapter\Controller\LoanController', 'history');
```

---

## Exemplo de Fluxo - Lend Book (Completo)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           HTTP REQUEST (POST /loans/store)                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│  [InterfaceAdapter\Controller\LoanController::store()]                       │
│  • Sanitiza input                                                          │
│  • Valida CSRF                                                             │
│  • Cria LendBookInput DTO                                                  │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Application\UseCase\LendBookUseCase::execute()]                             │
│  • Orquestra a operação                                                    │
│  • Chama repositories para carregar entidades                            │
│  • Delega lógica de negócio para entidades                               │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Domain\Entity\Book::lendTo()]                                               │
│  • Verifica disponibilidade (isAvailable)                                │
│  • Lança exceção se não disponível                                         │
│  • Altera status para LENT                                                 │
│  • Retorna nova Loan entity                                                │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Infrastructure\Repository\PdoBookRepository::save()]                        │
│  [Infrastructure\Repository\PdoLoanRepository::save()]                       │
│  • Persiste mudanças no banco (PDO)                                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Application\UseCase\LendBookOutput DTO]                                     │
│  • Retorna dados estruturados para controller                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Controller converte para view array]                                      │
│  • Flash message + redirect                                                │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌─────────────────────────────────────────────────────────────────────────────┐
│                           HTTP REDIRECT (/dashboard)                         │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Princípios Clean Architecture Aplicados

### ✅ 1. Independência de Frameworks
- Domain não depende de PDO, controllers ou frameworks
- Entities são Plain PHP Objects

### ✅ 2. Testabilidade
- Use Cases podem ser testados com repositories mockados
- Domain Entities não dependem de infraestrutura

### ✅ 3. Independência de UI
- Use Cases retornam DTOs
- Controllers convertem DTOs para views

### ✅ 4. Independência de Banco de Dados
- Repository Interface define contratos
- Implementação PDO pode ser trocada

### ✅ 5. Dependência Aponta para Dentro
```
Infrastructure (PDO Repositories)
         ↓ depends on
InterfaceAdapter (Controllers)
         ↓ depends on
Application (Use Cases)
         ↓ depends on
Domain (Entities) ⭐ Centro
```

---

## Arquivos Criados (43 novos arquivos)

### Domain Layer (10 arquivos)
- `Domain/Entity/Book.php`
- `Domain/Entity/Borrower.php`
- `Domain/Entity/Loan.php`
- `Domain/ValueObject/BookStatus.php`
- `Domain/ValueObject/LoanStatus.php`
- `Domain/Exception/BookNotAvailableException.php`
- `Domain/Repository/BookRepositoryInterface.php`
- `Domain/Repository/BorrowerRepositoryInterface.php`
- `Domain/Repository/LoanRepositoryInterface.php`

### Application Layer (21 arquivos)
- `Application/UseCase/LendBookUseCase.php` (+ Input/Output)
- `Application/UseCase/ReturnBookUseCase.php` (+ Input/Output)
- `Application/UseCase/CreateBookUseCase.php` (+ Input/Output)
- `Application/UseCase/UpdateBookUseCase.php` (+ Input/Output)
- `Application/UseCase/DeleteBookUseCase.php` (+ Input/Output)
- `Application/UseCase/CreateBorrowerUseCase.php` (+ Input/Output)
- `Application/UseCase/UpdateBorrowerUseCase.php` (+ Input/Output)
- `Application/UseCase/DeleteBorrowerUseCase.php` (+ Input/Output)

### Infrastructure Layer (4 arquivos)
- `Infrastructure/Repository/PdoBookRepository.php`
- `Infrastructure/Repository/PdoBorrowerRepository.php`
- `Infrastructure/Repository/PdoLoanRepository.php`
- `Infrastructure/Container/ServiceContainer.php`

### Interface Adapter Layer (4 arquivos)
- `InterfaceAdapter/Controller/BookController.php`
- `InterfaceAdapter/Controller/BorrowerController.php`
- `InterfaceAdapter/Controller/LoanController.php`
- `InterfaceAdapter/Controller/DashboardController.php`

---

## Arquivos Removidos (8 arquivos)

❌ `app/controllers/BookController.php` (187 linhas)
❌ `app/controllers/BorrowerController.php`
❌ `app/controllers/LoanController.php` (192 linhas)
❌ `app/controllers/DashboardController.php`
❌ `app/models/Book.php` (79 linhas)
❌ `app/models/Borrower.php`
❌ `app/models/Loan.php` (100 linhas)
❌ `app/models/Model.php` (97 linhas)

---

## Código Mantido (Legado Mínimo)

✅ `app/controllers/AuthController.php` - OAuth específico do Google
✅ `app/controllers/Controller.php` - Base class para herança
✅ `app/core/Auth.php` - Gerenciamento de sessão + OAuth
✅ `app/core/Env.php` - Environment variables
✅ `app/core/Router.php` - Roteamento HTTP
✅ `app/models/User.php` - Para callback OAuth

---

## Próximos Passos Opcionais

1. **Mover Auth para Clean Architecture**
   - Criar Domain/User Entity
   - Criar AuthRepository
   - Criar AuthenticateUserUseCase

2. **Adicionar Testes Unitários**
   - Testar Use Cases com repositories mockados
   - Testar Domain Entities isoladamente

3. **Validação de Input**
   - Criar objetos de validação dedicados
   - Mover validação dos Controllers

4. **Eventos de Domínio**
   - Emitir eventos quando livro for emprestado
   - Possibilitar notificações, logs, etc.

---

## Resumo

| Aspecto | Antes | Depois |
|---------|-------|--------|
| **Arquitetura** | MVC Tradicional | Clean Architecture |
| **Camadas** | 3 (M-V-C) | 4 (Domain-App-Infra-Interface) |
| **Regras de Negócio** | Nos Controllers | Nas Entities |
| **Acesso ao Banco** | Active Record | Repository Pattern |
| **Testabilidade** | Requer banco | Mockable |
| **Dependências** | Apontam para fora | Apontam para dentro |
| **Total de arquivos novos** | - | 43 |
| **Arquivos removidos** | - | 8 |

---

*Projeto completamente migrado para Clean Architecture*
*Data: Maio 2026*
