<?php

namespace App\Application\UseCase;

/**
 * Lend Book Use Case Input DTO
 */
class LendBookInput
{
    public function __construct(
        public readonly int $userId,
        public readonly int $bookId,
        public readonly int $borrowerId,
        public readonly ?\DateTimeImmutable $loanDate = null,
        public readonly ?string $notes = null
    ) {
    }
}
