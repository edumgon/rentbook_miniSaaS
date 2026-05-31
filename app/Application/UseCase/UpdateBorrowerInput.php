<?php

namespace App\Application\UseCase;

/**
 * Update Borrower Use Case Input DTO
 */
class UpdateBorrowerInput
{
    public function __construct(
        public readonly int $userId,
        public readonly int $borrowerId,
        public readonly string $name,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $location = null
    ) {
    }
}
