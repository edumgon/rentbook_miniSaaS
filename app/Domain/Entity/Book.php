<?php

namespace App\Domain\Entity;

use App\Domain\Exception\BookNotAvailableException;
use App\Domain\ValueObject\BookStatus;

/**
 * Book Entity - Core Domain Object
 * 
 * Encapsulates business rules for book management.
 * Independent of database and framework concerns.
 */
class Book
{
    private ?int $id;
    private int $userId;
    private string $title;
    private ?string $author;
    private ?string $publisher;
    private ?string $isbn;
    private ?string $coverUrl;
    private BookStatus $status;
    private ?\DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        int $userId,
        string $title,
        ?string $author = null,
        ?string $publisher = null,
        ?string $isbn = null,
        ?string $coverUrl = null,
        ?int $id = null,
        ?BookStatus $status = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->author = $author;
        $this->publisher = $publisher;
        $this->isbn = $isbn;
        $this->coverUrl = $coverUrl;
        $this->id = $id;
        $this->status = $status ?? BookStatus::AVAILABLE;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Lend this book to a borrower
     * @throws BookNotAvailableException
     */
    public function lendTo(Borrower $borrower, ?\DateTimeImmutable $loanDate = null): Loan
    {
        if (!$this->isAvailable()) {
            throw new BookNotAvailableException(
                sprintf('Book "%s" is not available for lending (current status: %s)', 
                    $this->title, 
                    $this->status->value)
            );
        }

        $this->status = BookStatus::LENT;
        $this->updatedAt = new \DateTimeImmutable();

        return new Loan(
            $this->userId,
            $this->id ?? throw new \RuntimeException('Book must be persisted before lending'),
            $borrower->getId() ?? throw new \RuntimeException('Borrower must be persisted'),
            $loanDate ?? new \DateTimeImmutable()
        );
    }

    /**
     * Mark book as returned
     */
    public function markAsReturned(): void
    {
        $this->status = BookStatus::AVAILABLE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Check if book is available for lending
     */
    public function isAvailable(): bool
    {
        return $this->status === BookStatus::AVAILABLE;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): ?string { return $this->author; }
    public function getPublisher(): ?string { return $this->publisher; }
    public function getIsbn(): ?string { return $this->isbn; }
    public function getCoverUrl(): ?string { return $this->coverUrl; }
    public function getStatus(): BookStatus { return $this->status; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    /**
     * Create from array data (for repository mapping)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            title: $data['title'],
            author: $data['author'] ?? null,
            publisher: $data['publisher'] ?? null,
            isbn: $data['isbn'] ?? null,
            coverUrl: $data['cover_url'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            status: isset($data['status']) ? BookStatus::from($data['status']) : BookStatus::AVAILABLE,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }

    /**
     * Convert to array (for repository persistence)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'cover_url' => $this->coverUrl,
            'status' => $this->status->value,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }
}
