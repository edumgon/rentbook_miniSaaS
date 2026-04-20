<?php

/**
 * Model - Base Model Class
 * 
 * Provides basic CRUD operations for all models.
 * Uses PDO prepared statements for security.
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    
    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Find records by user ID (for user-scoped data)
     */
    public function findByUser(int $userId, array $conditions = []): array
    {
        $where = ['user_id = :user_id'];
        $params = ['user_id' => $userId];
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Create new record
     */
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = $data['created_at'];
        return Database::insert($this->table, $data);
    }
    
    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::update($this->table, $data, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        return Database::delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    /**
     * Get all records
     */
    public function all(int $userId): array
    {
        return $this->findByUser($userId);
    }
    
    /**
     * Count records
     */
    public function count(int $userId, array $conditions = []): int
    {
        $where = ['user_id = :user_id'];
        $params = ['user_id' => $userId];
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
        $result = Database::fetchOne($sql, $params);
        
        return (int) ($result['count'] ?? 0);
    }
}
