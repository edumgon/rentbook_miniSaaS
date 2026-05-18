<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Loan;
use App\Domain\Exception\BookNotAvailableException;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\Repository\BorrowerRepositoryInterface;
use App\Domain\Repository\LoanRepositoryInterface;

/**
 * Lend Book Use Case
 * 
 * Orchestrates the business logic for lending a book.
 * This is an Application Service in Clean Architecture terms.
 */
class LendBookUseCase
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private BorrowerRepositoryInterface $borrowerRepository,
        private LoanRepositoryInterface $loanRepository
    ) {
    }

    /**
     * Execute the use case
     * 
     * @throws BookNotAvailableException
     * @throws \DomainException
     */
    public function execute(LendBookInput $input): LendBookOutput
    {
        // Find entities
        $book = $this->bookRepository->findByIdAndUser($input->bookId, $input->userId);
        if (!$book) {
            throw new \DomainException('Book not found');
        }

        $borrower = $this->borrowerRepository->findByIdAndUser($input->borrowerId, $input->userId);
        if (!$borrower) {
            throw new \DomainException('Borrower not found');
        }

        // Domain logic: Book lends itself to borrower
        // This encapsulates the business rule and state change
        $loan = $book->lendTo($borrower, $input->loanDate);

        if ($input->notes) {
            // Create new loan with notes using reflection or a factory
            // For simplicity, we'll use the loan from book->lendTo and update notes
            $loanData = $loan->toArray();
            $loanData['notes'] = $input->notes;
            $loan = Loan::fromArray($loanData);
        }

        // Persist changes
        $this->bookRepository->save($book);
        $savedLoan = $this->loanRepository->save($loan);

        return new LendBookOutput(
            loanId: $savedLoan->getId(),
            bookId: $book->getId(),
            borrowerId: $borrower->getId(),
            bookTitle: $book->getTitle(),
            borrowerName: $borrower->getName()
        );
    }
}
