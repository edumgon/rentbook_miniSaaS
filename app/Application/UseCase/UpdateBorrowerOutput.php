<?php

namespace App\Application\UseCase;

/**
 * Update Borrower Use Case Output DTO
 */
class UpdateBorrowerOutput
{
    public function __construct(
        public readonly ?int $borrowerId,
        public readonly string $name
    ) {
    }
}
