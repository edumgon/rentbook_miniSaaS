<?php

namespace App\Application\UseCase;

/**
 * Update Book Use Case Input DTO
 */
class UpdateBookInput
{
    public function __construct(
        public readonly int $userId,
        public readonly int $bookId,
        public readonly string $title,
        public readonly ?string $author = null,
        public readonly ?string $publisher = null,
        public readonly ?string $isbn = null,
        public readonly ?string $coverUrl = null
    ) {
    }
}
