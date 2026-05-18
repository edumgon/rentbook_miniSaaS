<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Borrower;

/**
 * Borrower Repository Interface
 */
interface BorrowerRepositoryInterface
{
    /**
     * Find borrower by ID
     */
    public function findById(int $id): ?Borrower;

    /**
     * Find borrower by ID and user
     */
    public function findByIdAndUser(int $id, int $userId): ?Borrower;

    /**
     * Find all borrowers for a user
     * @return Borrower[]
     */
    public function findByUser(int $userId): array;

    /**
     * Save a borrower
     */
    public function save(Borrower $borrower): Borrower;

    /**
     * Delete a borrower
     */
    public function delete(int $id): bool;
}
