<?php

/**
 * Borrower Model
 * 
 * Handles borrower/friend data for lending.
 */
class Borrower extends Model
{
    protected string $table = 'borrowers';
    
    /**
     * Find borrower by user and ID
     */
    public function findByUserAndId(int $userId, int $borrowerId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $borrowerId, 'user_id' => $userId]);
    }
    
    /**
     * Search borrowers by name
     */
    public function search(int $userId, string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND name LIKE :query 
                ORDER BY name ASC";
        
        return Database::fetchAll($sql, [
            'user_id' => $userId,
            'query' => '%' . $query . '%'
        ]);
    }
    
    /**
     * Check if borrower has active loans
     */
    public function hasActiveLoans(int $borrowerId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM loans 
                WHERE borrower_id = :borrower_id AND status = 'active'";
        $result = Database::fetchOne($sql, ['borrower_id' => $borrowerId]);
        return ($result['count'] ?? 0) > 0;
    }
}
