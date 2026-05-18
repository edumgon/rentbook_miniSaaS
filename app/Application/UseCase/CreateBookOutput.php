<?php

namespace App\Application\UseCase;

/**
 * Create Book Use Case Output DTO
 */
class CreateBookOutput
{
    public function __construct(
        public readonly ?int $bookId,
        public readonly string $title
    ) {
    }
}
