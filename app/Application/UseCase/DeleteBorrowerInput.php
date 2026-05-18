<?php

namespace App\Application\UseCase;

/**
 * Delete Borrower Use Case Input DTO
 */
class DeleteBorrowerInput
{
    public function __construct(
        public readonly int $userId,
        public readonly int $borrowerId
    ) {
    }
}
