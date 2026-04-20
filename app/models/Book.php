<?php

/**
 * Book Model
 * 
 * Handles book data for the user's personal library.
 */
class Book extends Model
{
    protected string $table = 'books';
    
    /**
     * Get books with optional status filter
     */
    public function getBooks(int $userId, ?string $status = null): array
    {
        $conditions = [];
        if ($status !== null) {
            $conditions['status'] = $status;
        }
        return $this->findByUser($userId, $conditions);
    }
    
    /**
     * Find book by user and ID (ensures ownership)
     */
    public function findByUserAndId(int $userId, int $bookId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $bookId, 'user_id' => $userId]);
    }
    
    /**
     * Update book status
     */
    public function updateStatus(int $bookId, string $status): bool
    {
        return $this->update($bookId, ['status' => $status]);
    }
    
    /**
     * Count books by status
     */
    public function countByStatus(int $userId): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id GROUP BY status";
        $results = Database::fetchAll($sql, ['user_id' => $userId]);
        
        $counts = [
            'available' => 0,
            'lent' => 0,
            'total' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Search books by title or author
     */
    public function search(int $userId, string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND (title LIKE :query OR author LIKE :query) 
                ORDER BY title ASC";
        
        return Database::fetchAll($sql, [
            'user_id' => $userId,
            'query' => '%' . $query . '%'
        ]);
    }
}
