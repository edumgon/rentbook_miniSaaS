<?php

namespace App\Application\UseCase;

/**
 * Create Borrower Use Case Output DTO
 */
class CreateBorrowerOutput
{
    public function __construct(
        public readonly ?int $borrowerId,
        public readonly string $name
    ) {
    }
}
