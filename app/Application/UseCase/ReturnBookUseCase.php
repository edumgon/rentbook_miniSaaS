<?php

namespace App\Application\UseCase;

use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\Repository\LoanRepositoryInterface;

/**
 * Return Book Use Case
 */
class ReturnBookUseCase
{
    public function __construct(
        private LoanRepositoryInterface $loanRepository,
        private BookRepositoryInterface $bookRepository
    ) {
    }

    /**
     * Execute the use case
     * 
     * @throws \DomainException
     */
    public function execute(ReturnBookInput $input): ReturnBookOutput
    {
        $loan = $this->loanRepository->findByIdAndUser($input->loanId, $input->userId);
        if (!$loan) {
            throw new \DomainException('Loan not found');
        }

        if (!$loan->isActive()) {
            throw new \DomainException('Loan is already returned');
        }

        $book = $this->bookRepository->findById($loan->getBookId());
        if (!$book) {
            throw new \DomainException('Book not found');
        }

        // Domain logic: mark as returned
        $loan->markAsReturned();
        $book->markAsReturned();

        // Persist
        $this->loanRepository->save($loan);
        $this->bookRepository->save($book);

        return new ReturnBookOutput(
            loanId: $loan->getId(),
            bookId: $book->getId(),
            bookTitle: $book->getTitle(),
            returnDate: $loan->getReturnDate()
        );
    }
}
