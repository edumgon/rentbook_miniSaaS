<?php

namespace App\Application\UseCase;

/**
 * Return Book Use Case Input DTO
 */
class ReturnBookInput
{
    public function __construct(
        public readonly int $userId,
        public readonly int $loanId
    ) {
    }
}
