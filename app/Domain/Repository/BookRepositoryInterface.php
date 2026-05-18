<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Book;
use App\Domain\ValueObject\BookStatus;

/**
 * Book Repository Interface
 * 
 * Defines the contract for book persistence operations.
 * This is part of the domain layer - implementation is in infrastructure.
 */
interface BookRepositoryInterface
{
    /**
     * Find book by ID
     */
    public function findById(int $id): ?Book;

    /**
     * Find book by ID ensuring it belongs to user
     */
    public function findByIdAndUser(int $id, int $userId): ?Book;

    /**
     * Find all books for a user
     * @return Book[]
     */
    public function findByUser(int $userId): array;

    /**
     * Find books by user and status
     * @return Book[]
     */
    public function findByUserAndStatus(int $userId, BookStatus $status): array;

    /**
     * Save a book (create or update)
     */
    public function save(Book $book): Book;

    /**
     * Delete a book
     */
    public function delete(int $id): bool;

    /**
     * Check if book has active loans
     */
    public function hasActiveLoans(int $bookId): bool;

    /**
     * Count books by status for user
     */
    public function countByStatus(int $userId): array;

    /**
     * Search books by title or author
     * @return Book[]
     */
    public function search(int $userId, string $query): array;
}
