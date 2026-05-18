<?php

namespace App\Application\UseCase;

/**
 * Return Book Use Case Output DTO
 */
class ReturnBookOutput
{
    public function __construct(
        public readonly ?int $loanId,
        public readonly ?int $bookId,
        public readonly string $bookTitle,
        public readonly ?\DateTimeImmutable $returnDate
    ) {
    }
}
