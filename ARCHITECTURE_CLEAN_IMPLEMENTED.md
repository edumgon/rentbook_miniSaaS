# Arquitetura Clean Architecture - Implementação

## Visão Geral

Este documento descreve a implementação dos princípios Clean Architecture no projeto RentBook MiniSaaS.

## Estrutura de Camadas

```
app/
├── Domain/                    # Enterprise Business Rules (núcleo)
│   ├── Entity/               # Entidades de domínio (Book, Borrower, Loan)
│   ├── ValueObject/          # Objetos de valor (BookStatus, LoanStatus)
│   ├── Repository/           # Interfaces de repositório (contratos)
│   └── Exception/            # Exceções de domínio
│
├── Application/              # Application Business Rules
│   └── UseCase/             # Casos de uso (LendBook, ReturnBook)
│       ├── LendBookUseCase.php
│       ├── LendBookInput.php      # DTO Input
│       ├── LendBookOutput.php     # DTO Output
│       └── ...
│
├── Infrastructure/           # Frameworks & Drivers
│   ├── Repository/        # Implementações PDO dos repositórios
│   │   ├── PdoBookRepository.php
│   │   ├── PdoBorrowerRepository.php
│   │   └── PdoLoanRepository.php
│   └── Container/         # Container de DI
│       └── ServiceContainer.php
│
├── InterfaceAdapter/       # Interface Adapters
│   └── Controller/        # Controllers Clean Arch
│       └── LoanController.php
│
├── core/                  # Código legado (mantido para compatibilidade)
├── models/               # Models Active Record legados
├── controllers/          # Controllers MVC legados
└── views/               # Templates (compartilhados)
```

## Princípios Aplicados

### 1. Independência de Frameworks
- ✅ Domain não depende de PDO, controllers ou frameworks externos
- ✅ Entities são Plain PHP Objects (sem herança de framework)

### 2. Testabilidade
- ✅ Use Cases podem ser testados sem banco de dados (usar mocks dos repositories)
- ✅ Domain Entities têm lógica pura, sem dependências externas

### 3. Independência de UI
- ✅ Use Cases retornam DTOs, não views
- ✅ Controllers convertem DTOs para arrays compatíveis com views

### 4. Independência de Banco de Dados
- ✅ Repository Interface define contratos
- ✅ Implementação PDO pode ser trocada por outra (MongoDB, API, etc)

### 5. Independência de Agentes Externos
- ✅ Regras de negócio isoladas em Entities e Use Cases
- ✅ Camadas externas (Controllers, Repositories) dependem de camadas internas

## Exemplo de Fluxo - Lend Book

```
HTTP Request
     ↓
[LoanController] (Interface Adapter)
     ↓ converte POST → LendBookInput DTO
[LendBookUseCase] (Application)
     ↓ orquestra
[BookRepository] + [BorrowerRepository] + [LoanRepository]
     ↓ carregam
[Book Entity] + [Borrower Entity]
     ↓ Book::lendTo() → [Loan Entity] (Domain Logic)
     ↓
[Repositories persistem mudanças]
     ↓
[LendBookOutput DTO]
     ↓
[Controller converte para view]
     ↓
HTTP Response
```

## Comparação: Antes vs Depois

### Antes (MVC Tradicional)
```php
class LoanController extends Controller
{
    public function store(): void
    {
        $book = $bookModel->findByUserAndId($userId, $bookId);
        if (!$book) { /* erro */ }
        
        if ($book['status'] !== 'available') {
            $this->setFlash('error', 'Book is not available');
            $this->redirect('/loans/create');
        }
        
        $loanId = $loanModel->create($data);
        $bookModel->updateStatus($bookId, 'lent'); // ← Lógica de negócio no controller!
        
        $this->setFlash('success', 'Book lent successfully');
        $this->redirect('/dashboard');
    }
}
```

### Depois (Clean Architecture)
```php
class LoanController extends Controller
{
    public function store(): void
    {
        $input = new LendBookInput(
            userId: $userId,
            bookId: $bookId,
            borrowerId: $borrowerId,
            loanDate: $loanDate,
            notes: $notes
        );
        
        $output = $this->lendBookUseCase->execute($input);
        
        $this->setFlash('success', "Book '{$output->bookTitle}' lent successfully");
        $this->redirect('/dashboard');
    }
}

// Domain/Entity/Book.php
class Book
{
    public function lendTo(Borrower $borrower): Loan
    {
        if (!$this->isAvailable()) {
            throw new BookNotAvailableException();
        }
        $this->status = BookStatus::LENT;
        return new Loan($this->id, $borrower->getId(), new DateTimeImmutable());
    }
}
```

## Benefícios Obtidos

1. **Separação Clara**: Cada camada tem responsabilidade única
2. **Testabilidade**: Domain e Use Cases testáveis sem infraestrutura
3. **Flexibilidade**: Pode trocar PDO por outra implementação sem mudar domain
4. **Manutenibilidade**: Regras de negócio centralizadas em Entities
5. **Backward Compatibility**: Código legado continua funcionando

## Rotas Migradas

| Rota | Controller Antigo | Controller Novo |
|------|------------------|-----------------|
| GET /loans | LoanController | App\InterfaceAdapter\Controller\LoanController |
| GET /loans/create | LoanController | App\InterfaceAdapter\Controller\LoanController |
| POST /loans/store | LoanController | App\InterfaceAdapter\Controller\LoanController |
| POST /loans/{id}/return | LoanController | App\InterfaceAdapter\Controller\LoanController |
| GET /loans/history/{bookId} | LoanController | App\InterfaceAdapter\Controller\LoanController |

## Próximos Passos Sugeridos

1. **Migrar BookController**: Criar Use Cases para cadastro/edição de livros
2. **Migrar BorrowerController**: Criar Use Cases para gestão de contatos
3. **Testes Unitários**: Aproveitar a arquitetura para testar domain sem banco
4. **Validação**: Extrair validação de input para objetos de validação dedicados

## Notas

- O código legado em `app/controllers/`, `app/models/`, `app/core/` continua funcionando
- A migração pode ser feita gradualmente, controller por controller
- Views são compartilhadas entre controllers antigos e novos (usam arrays compatíveis)
