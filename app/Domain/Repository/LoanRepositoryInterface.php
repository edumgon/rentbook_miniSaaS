<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Loan;
use App\Domain\ValueObject\LoanStatus;

/**
 * Loan Repository Interface
 */
interface LoanRepositoryInterface
{
    /**
     * Find loan by ID
     */
    public function findById(int $id): ?Loan;

    /**
     * Find loan by ID and user
     */
    public function findByIdAndUser(int $id, int $userId): ?Loan;

    /**
     * Find all loans for a user
     * @return Loan[]
     */
    public function findByUser(int $userId): array;

    /**
     * Find loans by user and status
     * @return Loan[]
     */
    public function findByUserAndStatus(int $userId, LoanStatus $status): array;

    /**
     * Save a loan
     */
    public function save(Loan $loan): Loan;

    /**
     * Get active loan for a specific book
     */
    public function findActiveLoanForBook(int $bookId): ?Loan;

    /**
     * Get loan history for a book
     * @return Loan[]
     */
    public function findByBookId(int $bookId): array;

    /**
     * Get overdue loans for user
     * @return Loan[]
     */
    public function findOverdueLoans(int $userId): array;
}
