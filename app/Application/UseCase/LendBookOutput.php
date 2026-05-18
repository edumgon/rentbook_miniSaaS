<?php

namespace App\Application\UseCase;

/**
 * Lend Book Use Case Output DTO
 */
class LendBookOutput
{
    public function __construct(
        public readonly ?int $loanId,
        public readonly ?int $bookId,
        public readonly int $borrowerId,
        public readonly string $bookTitle,
        public readonly string $borrowerName
    ) {
    }
}
