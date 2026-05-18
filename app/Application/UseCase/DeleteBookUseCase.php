<?php

namespace App\Application\UseCase;

use App\Domain\Repository\BookRepositoryInterface;

/**
 * Delete Book Use Case
 */
class DeleteBookUseCase
{
    public function __construct(
        private BookRepositoryInterface $bookRepository
    ) {
    }

    public function execute(DeleteBookInput $input): DeleteBookOutput
    {
        $book = $this->bookRepository->findByIdAndUser($input->bookId, $input->userId);
        
        if (!$book) {
            throw new \DomainException('Book not found');
        }

        // Check for active loans
        if ($this->bookRepository->hasActiveLoans($input->bookId)) {
            throw new \DomainException('Cannot delete book with active loan');
        }

        $this->bookRepository->delete($input->bookId);

        return new DeleteBookOutput(
            bookId: $input->bookId,
            title: $book->getTitle()
        );
    }
}
