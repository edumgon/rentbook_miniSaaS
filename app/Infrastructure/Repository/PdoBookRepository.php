<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Book;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\ValueObject\BookStatus;
use PDO;

/**
 * PDO Book Repository Implementation
 * 
 * Implements the domain repository interface using PDO.
 * This is part of the infrastructure layer.
 */
class PdoBookRepository implements BookRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?Book
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Book::fromArray($data) : null;
    }

    public function findByIdAndUser(int $id, int $userId): ?Book
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Book::fromArray($data) : null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Book::class, 'fromArray'], $rows);
    }

    public function findByUserAndStatus(int $userId, BookStatus $status): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE user_id = :user_id AND status = :status ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId, 'status' => $status->value]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Book::class, 'fromArray'], $rows);
    }

    public function save(Book $book): Book
    {
        $data = $book->toArray();

        if ($book->getId()) {
            // Update
            $stmt = $this->pdo->prepare("UPDATE books SET 
                title = :title, author = :author, publisher = :publisher, 
                isbn = :isbn, cover_url = :cover_url, status = :status, updated_at = :updated_at 
                WHERE id = :id");
            $stmt->execute([
                'id' => $data['id'],
                'title' => $data['title'],
                'author' => $data['author'],
                'publisher' => $data['publisher'],
                'isbn' => $data['isbn'],
                'cover_url' => $data['cover_url'],
                'status' => $data['status'],
                'updated_at' => $data['updated_at']
            ]);
        } else {
            // Insert
            $stmt = $this->pdo->prepare("INSERT INTO books 
                (user_id, title, author, publisher, isbn, cover_url, status, created_at, updated_at) 
                VALUES (:user_id, :title, :author, :publisher, :isbn, :cover_url, :status, :created_at, :updated_at)");
            $stmt->execute([
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'author' => $data['author'],
                'publisher' => $data['publisher'],
                'isbn' => $data['isbn'],
                'cover_url' => $data['cover_url'],
                'status' => $data['status'],
                'created_at' => $data['updated_at'], // For new books, created = updated
                'updated_at' => $data['updated_at']
            ]);
            
            // Set the ID on the entity
            $reflection = new \ReflectionClass($book);
            $prop = $reflection->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($book, (int) $this->pdo->lastInsertId());
        }

        return $book;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hasActiveLoans(int $bookId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM loans WHERE book_id = :book_id AND status = 'active'");
        $stmt->execute(['book_id' => $bookId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countByStatus(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM books WHERE user_id = :user_id GROUP BY status");
        $stmt->execute(['user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    public function search(int $userId, string $query): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books 
            WHERE user_id = :user_id 
            AND (title LIKE :query OR author LIKE :query) 
            ORDER BY title ASC");
        $stmt->execute([
            'user_id' => $userId,
            'query' => '%' . $query . '%'
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Book::class, 'fromArray'], $rows);
    }
}
