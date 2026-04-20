<?php

/**
 * Loan Model
 * 
 * Handles book lending records.
 */
class Loan extends Model
{
    protected string $table = 'loans';
    
    /**
     * Get all loans for a user with book and borrower details
     */
    public function getLoansWithDetails(int $userId, string $status = 'active'): array
    {
        $sql = "SELECT l.*, b.title as book_title, b.author as book_author, b.cover_url, 
                       br.name as borrower_name, br.phone as borrower_phone, br.email as borrower_email
                FROM {$this->table} l
                JOIN books b ON l.book_id = b.id
                JOIN borrowers br ON l.borrower_id = br.id
                WHERE l.user_id = :user_id AND l.status = :status
                ORDER BY l.loan_date DESC";
        
        return Database::fetchAll($sql, ['user_id' => $userId, 'status' => $status]);
    }
    
    /**
     * Find loan by ID and user (ensures ownership)
     */
    public function findByUserAndId(int $userId, int $loanId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $loanId, 'user_id' => $userId]);
    }
    
    /**
     * Mark loan as returned
     */
    public function markReturned(int $loanId): bool
    {
        return $this->update($loanId, [
            'status' => 'returned',
            'return_date' => date('Y-m-d')
        ]);
    }
    
    /**
     * Get loan history for a specific book
     */
    public function getBookHistory(int $bookId): array
    {
        $sql = "SELECT l.*, br.name as borrower_name
                FROM {$this->table} l
                JOIN borrowers br ON l.borrower_id = br.id
                WHERE l.book_id = :book_id
                ORDER BY l.loan_date DESC";
        
        return Database::fetchAll($sql, ['book_id' => $bookId]);
    }
    
    /**
     * Get active loan for a book (if any)
     */
    public function getActiveLoanForBook(int $bookId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE book_id = :book_id AND status = 'active' 
                LIMIT 1";
        return Database::fetchOne($sql, ['book_id' => $bookId]);
    }
    
    /**
     * Count active loans for user
     */
    public function countActive(int $userId): int
    {
        return $this->count($userId, ['status' => 'active']);
    }
    
    /**
     * Get overdue loans (loaned for more than 30 days)
     */
    public function getOverdueLoans(int $userId): array
    {
        $sql = "SELECT l.*, b.title as book_title, b.cover_url,
                       br.name as borrower_name, br.phone as borrower_phone,
                       DATEDIFF(CURRENT_DATE, l.loan_date) as days_loaned
                FROM {$this->table} l
                JOIN books b ON l.book_id = b.id
                JOIN borrowers br ON l.borrower_id = br.id
                WHERE l.user_id = :user_id 
                AND l.status = 'active' 
                AND l.loan_date < DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                ORDER BY l.loan_date ASC";
        
        return Database::fetchAll($sql, ['user_id' => $userId]);
    }
}
