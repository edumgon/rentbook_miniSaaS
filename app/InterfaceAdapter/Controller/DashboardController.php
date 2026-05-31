<?php

namespace App\InterfaceAdapter\Controller;

use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\Repository\BorrowerRepositoryInterface;
use App\Domain\Repository\LoanRepositoryInterface;
use App\Domain\ValueObject\LoanStatus;
use App\Infrastructure\Container\ServiceContainer;

/**
 * Dashboard Controller - Clean Architecture Version
 */
class DashboardController extends \Controller
{
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
    }

    /**
     * Show dashboard
     */
    public function index(): void
    {
        \Auth::init();
        \Auth::requireAuth();

        $userId = \Auth::id();

        $bookStats = $this->bookRepository->countByStatus($userId);
        $activeLoans = $this->loanRepository->findByUserAndStatus($userId, LoanStatus::ACTIVE);
        $overdueLoans = $this->loanRepository->findOverdueLoans($userId);

        // Convert entities to arrays for views
        $activeLoanArrays = array_map([$this, 'loanToArray'], $activeLoans);
        $overdueLoanArrays = array_map([$this, 'loanToArray'], $overdueLoans);

        $this->view('dashboard', [
            'title' => 'Dashboard',
            'bookStats' => $bookStats,
            'activeLoans' => $activeLoanArrays,
            'overdueLoans' => $overdueLoanArrays,
            'flash' => $this->getFlash()
        ]);
    }

    /**
     * Convert Loan entity to array with related data
     */
    private function loanToArray(\App\Domain\Entity\Loan $loan): array
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
