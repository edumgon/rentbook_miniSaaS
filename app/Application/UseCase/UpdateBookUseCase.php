<?php

namespace App\Application\UseCase;

use App\Domain\Repository\BookRepositoryInterface;

/**
 * Update Book Use Case
 */
class UpdateBookUseCase
{
    public function __construct(
        private BookRepositoryInterface $bookRepository
    ) {
    }

    public function execute(UpdateBookInput $input): UpdateBookOutput
    {
        $book = $this->bookRepository->findByIdAndUser($input->bookId, $input->userId);
        
        if (!$book) {
            throw new \DomainException('Book not found');
        }

        // Create updated book entity
        $updatedBook = new \App\Domain\Entity\Book(
            userId: $book->getUserId(),
            title: $input->title,
            author: $input->author,
            publisher: $input->publisher,
            isbn: $input->isbn,
            coverUrl: $input->coverUrl,
            id: $book->getId(),
            status: $book->getStatus(),
            createdAt: $book->getCreatedAt()
        );

        $savedBook = $this->bookRepository->save($updatedBook);

        return new UpdateBookOutput(
            bookId: $savedBook->getId(),
            title: $savedBook->getTitle()
        );
    }
}
