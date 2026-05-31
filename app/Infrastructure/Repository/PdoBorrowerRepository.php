<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Borrower;
use App\Domain\Repository\BorrowerRepositoryInterface;
use PDO;

/**
 * PDO Borrower Repository Implementation
 */
class PdoBorrowerRepository implements BorrowerRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?Borrower
    {
        $stmt = $this->pdo->prepare("SELECT * FROM borrowers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Borrower::fromArray($data) : null;
    }

    public function findByIdAndUser(int $id, int $userId): ?Borrower
    {
        $stmt = $this->pdo->prepare("SELECT * FROM borrowers WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Borrower::fromArray($data) : null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM borrowers WHERE user_id = :user_id ORDER BY name ASC");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Borrower::class, 'fromArray'], $rows);
    }

    public function save(Borrower $borrower): Borrower
    {
        $data = $borrower->toArray();

        if ($borrower->getId()) {
            // Update
            $stmt = $this->pdo->prepare("UPDATE borrowers SET
                name = :name, email = :email, phone = :phone, location = :location, updated_at = :updated_at
                WHERE id = :id");
            $stmt->execute([
                'id' => $data['id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'location' => $data['location'],
                'updated_at' => $data['updated_at']
            ]);
        } else {
            // Insert
            $stmt = $this->pdo->prepare("INSERT INTO borrowers
                (user_id, name, email, phone, location, created_at, updated_at)
                VALUES (:user_id, :name, :email, :phone, :location, :created_at, :updated_at)");
            $stmt->execute([
                'user_id' => $data['user_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'location' => $data['location'],
                'created_at' => $data['updated_at'],
                'updated_at' => $data['updated_at']
            ]);
            
            $reflection = new \ReflectionClass($borrower);
            $prop = $reflection->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($borrower, (int) $this->pdo->lastInsertId());
        }

        return $borrower;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM borrowers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
