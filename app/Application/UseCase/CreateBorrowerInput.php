<?php

namespace App\Application\UseCase;

/**
 * Create Borrower Use Case Input DTO
 */
class CreateBorrowerInput
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $email = null,
        public readonly ?string $phone = null
    ) {
    }
}
