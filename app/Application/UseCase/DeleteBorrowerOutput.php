<?php

namespace App\Application\UseCase;

/**
 * Delete Borrower Use Case Output DTO
 */
class DeleteBorrowerOutput
{
    public function __construct(
        public readonly ?int $borrowerId,
        public readonly string $name
    ) {
    }
}
