<?php

namespace App\Application\UseCase;

/**
 * Delete Book Use Case Output DTO
 */
class DeleteBookOutput
{
    public function __construct(
        public readonly ?int $bookId,
        public readonly string $title
    ) {
    }
}
