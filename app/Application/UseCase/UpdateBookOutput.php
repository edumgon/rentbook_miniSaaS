<?php

namespace App\Application\UseCase;

/**
 * Update Book Use Case Output DTO
 */
class UpdateBookOutput
{
    public function __construct(
        public readonly ?int $bookId,
        public readonly string $title
    ) {
    }
}
