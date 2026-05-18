<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Book;
use App\Domain\Repository\BookRepositoryInterface;

/**
 * Create Book Use Case
 */
class CreateBookUseCase
{
    public function __construct(
        private BookRepositoryInterface $bookRepository
    ) {
    }

    public function execute(CreateBookInput $input): CreateBookOutput
    {
        $book = new Book(
            userId: $input->userId,
            title: $input->title,
            author: $input->author,
            publisher: $input->publisher,
            isbn: $input->isbn,
            coverUrl: $input->coverUrl
        );

        $savedBook = $this->bookRepository->save($book);

        return new CreateBookOutput(
            bookId: $savedBook->getId(),
            title: $savedBook->getTitle()
        );
    }
}
