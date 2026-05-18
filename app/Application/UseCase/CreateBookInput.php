<?php

namespace App\Application\UseCase;

/**
 * Create Book Use Case Input DTO
 */
class CreateBookInput
{
    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $author = null,
        public readonly ?string $publisher = null,
        public readonly ?string $isbn = null,
        public readonly ?string $coverUrl = null
    ) {
    }
}
