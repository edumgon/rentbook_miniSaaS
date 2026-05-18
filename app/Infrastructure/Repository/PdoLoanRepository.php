<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Loan;
use App\Domain\Repository\LoanRepositoryInterface;
use App\Domain\ValueObject\LoanStatus;
use PDO;

/**
 * PDO Loan Repository Implementation
 */
class PdoLoanRepository implements LoanRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?Loan
    {
        $stmt = $this->pdo->prepare("SELECT * FROM loans WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Loan::fromArray($data) : null;
    }

    public function findByIdAndUser(int $id, int $userId): ?Loan
    {
        $stmt = $this->pdo->prepare("SELECT * FROM loans WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Loan::fromArray($data) : null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM loans WHERE user_id = :user_id ORDER BY loan_date DESC");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Loan::class, 'fromArray'], $rows);
    }

    public function findByUserAndStatus(int $userId, LoanStatus $status): array
    {
        $stmt = $this->pdo->prepare("SELECT l.* FROM loans l
            JOIN books b ON l.book_id = b.id
            WHERE l.user_id = :user_id AND l.status = :status
            ORDER BY l.loan_date DESC");
        $stmt->execute(['user_id' => $userId, 'status' => $status->value]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Loan::class, 'fromArray'], $rows);
    }

    public function save(Loan $loan): Loan
    {
        $data = $loan->toArray();

        if ($loan->getId()) {
            // Update
            $stmt = $this->pdo->prepare("UPDATE loans SET 
                return_date = :return_date, notes = :notes, status = :status, updated_at = :updated_at 
                WHERE id = :id");
            $stmt->execute([
                'id' => $data['id'],
                'return_date' => $data['return_date'],
                'notes' => $data['notes'],
                'status' => $data['status'],
                'updated_at' => $data['updated_at']
            ]);
        } else {
            // Insert
            $stmt = $this->pdo->prepare("INSERT INTO loans 
                (user_id, book_id, borrower_id, loan_date, return_date, notes, status, created_at, updated_at) 
                VALUES (:user_id, :book_id, :borrower_id, :loan_date, :return_date, :notes, :status, :created_at, :updated_at)");
            $stmt->execute([
                'user_id' => $data['user_id'],
                'book_id' => $data['book_id'],
                'borrower_id' => $data['borrower_id'],
                'loan_date' => $data['loan_date'],
                'return_date' => $data['return_date'],
                'notes' => $data['notes'],
                'status' => $data['status'],
                'created_at' => $data['updated_at'],
                'updated_at' => $data['updated_at']
            ]);
            
            $reflection = new \ReflectionClass($loan);
            $prop = $reflection->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($loan, (int) $this->pdo->lastInsertId());
        }

        return $loan;
    }

    public function findActiveLoanForBook(int $bookId): ?Loan
    {
        $stmt = $this->pdo->prepare("SELECT * FROM loans 
            WHERE book_id = :book_id AND status = 'active' 
            LIMIT 1");
        $stmt->execute(['book_id' => $bookId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Loan::fromArray($data) : null;
    }

    public function findByBookId(int $bookId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM loans 
            WHERE book_id = :book_id 
            ORDER BY loan_date DESC");
        $stmt->execute(['book_id' => $bookId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Loan::class, 'fromArray'], $rows);
    }

    public function findOverdueLoans(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM loans 
            WHERE user_id = :user_id 
            AND status = 'active' 
            AND loan_date < DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            ORDER BY loan_date ASC");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Loan::class, 'fromArray'], $rows);
    }
}
