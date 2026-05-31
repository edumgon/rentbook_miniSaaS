<?php

namespace App\InterfaceAdapter\Controller;

use App\Application\UseCase\LendBookInput;
use App\Application\UseCase\LendBookUseCase;
use App\Application\UseCase\ReturnBookInput;
use App\Application\UseCase\ReturnBookUseCase;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\Repository\BorrowerRepositoryInterface;
use App\Domain\Repository\LoanRepositoryInterface;
use App\Domain\ValueObject\LoanStatus;
use App\Infrastructure\Container\ServiceContainer;

/**
 * Loan Controller - Clean Architecture Version
 * 
 * Acts as an Interface Adapter, converting HTTP requests to Use Case inputs
 * and Use Case outputs to HTTP responses.
 */
class LoanController extends \Controller
{
    private LendBookUseCase $lendBookUseCase;
    private ReturnBookUseCase $returnBookUseCase;
    private BookRepositoryInterface $bookRepository;
    private BorrowerRepositoryInterface $borrowerRepository;
    private LoanRepositoryInterface $loanRepository;

    public function __construct()
    {
        $container = ServiceContainer::getInstance();
        $container->initialize();
        
        $this->bookRepository = $container->get(BookRepositoryInterface::class);
        $this->borrowerRepository = $container->get(BorrowerRepositoryInterface::class);
        $this->loanRepository = $container->get(LoanRepositoryInterface::class);
        
        $this->lendBookUseCase = new LendBookUseCase(
            $this->bookRepository,
            $this->borrowerRepository,
            $this->loanRepository
        );
        
        $this->returnBookUseCase = new ReturnBookUseCase(
            $this->loanRepository,
            $this->bookRepository
        );
    }

    /**
     * List all loans
     */
    public function index(): void
    {
        \Auth::init();
        \Auth::requireAuth();
        
        $userId = \Auth::id();
        $status = $_GET['status'] ?? 'active';
        
        $loans = $this->loanRepository->findByUserAndStatus(
            $userId, 
            LoanStatus::from($status)
        );
        
        // Convert entities to arrays for view compatibility
        $loanArrays = array_map(fn($loan) => $this->loanToArray($loan, $userId), $loans);
        
        $this->view('loans/list', [
            'title' => $status === 'active' ? 'Active Loans' : 'Returned Books',
            'loans' => $loanArrays,
            'status' => $status,
            'flash' => $this->getFlash()
        ]);
    }

    /**
     * Show create loan form
     */
    public function create(): void
    {
        \Auth::init();
        \Auth::requireAuth();
        
        $userId = \Auth::id();
        
        $books = $this->bookRepository->findByUserAndStatus(
            $userId, 
            \App\Domain\ValueObject\BookStatus::AVAILABLE
        );
        $borrowers = $this->borrowerRepository->findByUser($userId);
        
        if (empty($books)) {
            $this->setFlash('error', 'No available books to lend. Add books first.');
            $this->redirect('/books');
        }
        
        if (empty($borrowers)) {
            $this->setFlash('error', 'No friends to lend to. Add friends first.');
            $this->redirect('/borrowers');
        }
        
        $this->view('loans/create', [
            'title' => 'Lend a Book',
            'books' => array_map([$this, 'bookToArray'], $books),
            'borrowers' => array_map([$this, 'borrowerToArray'], $borrowers),
            'csrfToken' => \Auth::generateCsrfToken()
        ]);
    }

    /**
     * Store new loan using Use Case
     */
    public function store(): void
    {
        \Auth::init();
        \Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loans/create');
        }
        
        $this->validateCsrf();
        
        $userId = \Auth::id();
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $borrowerId = (int) ($_POST['borrower_id'] ?? 0);
        
        if (!$bookId || !$borrowerId) {
            $this->setFlash('error', 'Please select both a book and a friend');
            $this->redirect('/loans/create');
        }
        
        try {
            $input = new LendBookInput(
                userId: $userId,
                bookId: $bookId,
                borrowerId: $borrowerId,
                loanDate: isset($_POST['loan_date']) && $_POST['loan_date'] 
                    ? new \DateTimeImmutable($_POST['loan_date']) 
                    : null,
                notes: $this->sanitize($_POST['notes'] ?? '')
            );
            
            $output = $this->lendBookUseCase->execute($input);
            
            $this->setFlash('success', "Book '{$output->bookTitle}' lent to {$output->borrowerName} successfully");
            $this->redirect('/dashboard');
            
        } catch (\App\Domain\Exception\BookNotAvailableException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/loans/create');
        } catch (\DomainException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/loans/create');
        } catch (\Exception $e) {
            error_log('Loan creation failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while creating the loan');
            $this->redirect('/loans/create');
        }
    }

    /**
     * Mark loan as returned using Use Case
     */
    public function return(int $id): void
    {
        \Auth::init();
        \Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }
        
        $this->validateCsrf();
        
        $userId = \Auth::id();
        
        try {
            $input = new ReturnBookInput(
                userId: $userId,
                loanId: $id
            );
            
            $output = $this->returnBookUseCase->execute($input);
            
            $this->setFlash('success', "Book '{$output->bookTitle}' marked as returned");
            $this->redirect('/dashboard');
            
        } catch (\DomainException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            error_log('Loan return failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while returning the book');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show loan history for a book
     */
    public function history(int $bookId): void
    {
        \Auth::init();
        \Auth::requireAuth();
        
        $userId = \Auth::id();
        
        $book = $this->bookRepository->findByIdAndUser($bookId, $userId);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/books');
        }
        
        $loans = $this->loanRepository->findByBookId($bookId);
        $loanArrays = array_map(fn($loan) => $this->loanToArray($loan, $userId), $loans);
        
        $this->view('loans/history', [
            'title' => 'Loan History: ' . $book->getTitle(),
            'book' => $this->bookToArray($book),
            'history' => $loanArrays,
            'flash' => $this->getFlash()
        ]);
    }

    /**
     * Convert Book entity to array for view compatibility
     */
    private function bookToArray(\App\Domain\Entity\Book $book): array
    {
        return [
            'id' => $book->getId(),
            'user_id' => $book->getUserId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'publisher' => $book->getPublisher(),
            'isbn' => $book->getIsbn(),
            'cover_url' => $book->getCoverUrl(),
            'status' => $book->getStatus()->value,
            'created_at' => $book->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $book->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert Borrower entity to array for view compatibility
     */
    private function borrowerToArray(\App\Domain\Entity\Borrower $borrower): array
    {
        return [
            'id' => $borrower->getId(),
            'user_id' => $borrower->getUserId(),
            'name' => $borrower->getName(),
            'email' => $borrower->getEmail(),
            'phone' => $borrower->getPhone(),
            'location' => $borrower->getLocation(),
            'created_at' => $borrower->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $borrower->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert Loan entity to array with related data for view compatibility
     */
    private function loanToArray(\App\Domain\Entity\Loan $loan, int $userId): array
    {
        $book = $this->bookRepository->findById($loan->getBookId());
        $borrower = $this->borrowerRepository->findById($loan->getBorrowerId());
        
        return [
            'id' => $loan->getId(),
            'user_id' => $loan->getUserId(),
            'book_id' => $loan->getBookId(),
            'borrower_id' => $loan->getBorrowerId(),
            'loan_date' => $loan->getLoanDate()->format('Y-m-d'),
            'return_date' => $loan->getReturnDate()?->format('Y-m-d'),
            'notes' => $loan->getNotes(),
            'status' => $loan->getStatus()->value,
            'book_title' => $book?->getTitle(),
            'book_author' => $book?->getAuthor(),
            'cover_url' => $book?->getCoverUrl(),
            'borrower_name' => $borrower?->getName(),
            'borrower_phone' => $borrower?->getPhone(),
            'borrower_email' => $borrower?->getEmail(),
            'days_loaned' => $loan->getDaysLoaned(),
            'is_overdue' => $loan->isOverdue(),
        ];
    }
}
