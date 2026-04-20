<?php

/**
 * User Model
 * 
 * Handles user data and Google OAuth integration.
 */
class User extends Model
{
    protected string $table = 'users';
    
    /**
     * Find user by Google ID
     */
    public function findByGoogleId(string $googleId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE google_id = :google_id LIMIT 1";
        return Database::fetchOne($sql, ['google_id' => $googleId]);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        return Database::fetchOne($sql, ['email' => $email]);
    }
    
    /**
     * Create or update user from Google data
     */
    public function createOrUpdateFromGoogle(array $googleData): array
    {
        $existing = $this->findByGoogleId($googleData['id']);
        
        $data = [
            'google_id' => $googleData['id'],
            'email' => $googleData['email'],
            'name' => $googleData['name'] ?? $googleData['email'],
            'avatar_url' => $googleData['picture'] ?? null
        ];
        
        if ($existing) {
            $this->update($existing['id'], $data);
            return array_merge($existing, $data);
        }
        
        $id = $this->create($data);
        return $this->find($id);
    }
    
    /**
     * Update user's last login time
     */
    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login' => date('Y-m-d H:i:s')]);
    }
}
