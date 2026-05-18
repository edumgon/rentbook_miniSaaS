<?php

namespace App\Application\UseCase;

/**
 * Delete Book Use Case Input DTO
 */
class DeleteBookInput
{
    public function __construct(
        public readonly int $userId,
        public readonly int $bookId
    ) {
    }
}
