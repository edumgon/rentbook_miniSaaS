<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\LoanStatus;

/**
 * Loan Entity - Core Domain Object
 * 
 * Represents a book lending transaction.
 */
class Loan
{
    private ?int $id;
    private int $userId;
    private int $bookId;
    private int $borrowerId;
    private \DateTimeImmutable $loanDate;
    private ?\DateTimeImmutable $returnDate;
    private ?string $notes;
    private LoanStatus $status;
    private ?\DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        int $userId,
        int $bookId,
        int $borrowerId,
        ?\DateTimeImmutable $loanDate = null,
        ?string $notes = null,
        ?int $id = null,
        ?LoanStatus $status = null,
        ?\DateTimeImmutable $returnDate = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->userId = $userId;
        $this->bookId = $bookId;
        $this->borrowerId = $borrowerId;
        $this->loanDate = $loanDate ?? new \DateTimeImmutable();
        $this->notes = $notes;
        $this->id = $id;
        $this->status = $status ?? LoanStatus::ACTIVE;
        $this->returnDate = $returnDate;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Mark loan as returned
     */
    public function markAsReturned(): void
    {
        if ($this->status === LoanStatus::RETURNED) {
            throw new \DomainException('Loan is already returned');
        }

        $this->status = LoanStatus::RETURNED;
        $this->returnDate = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Check if loan is active
     */
    public function isActive(): bool
    {
        return $this->status === LoanStatus::ACTIVE;
    }

    /**
     * Check if loan is overdue (more than 30 days)
     */
    public function isOverdue(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $daysLoaned = (new \DateTimeImmutable())->diff($this->loanDate)->days;
        return $daysLoaned > 30;
    }

    /**
     * Get days since loan date
     */
    public function getDaysLoaned(): int
    {
        return (new \DateTimeImmutable())->diff($this->loanDate)->days;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getBookId(): int { return $this->bookId; }
    public function getBorrowerId(): int { return $this->borrowerId; }
    public function getLoanDate(): \DateTimeImmutable { return $this->loanDate; }
    public function getReturnDate(): ?\DateTimeImmutable { return $this->returnDate; }
    public function getNotes(): ?string { return $this->notes; }
    public function getStatus(): LoanStatus { return $this->status; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    /**
     * Create from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            bookId: (int) $data['book_id'],
            borrowerId: (int) $data['borrower_id'],
            loanDate: isset($data['loan_date']) ? new \DateTimeImmutable($data['loan_date']) : null,
            notes: $data['notes'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            status: isset($data['status']) ? LoanStatus::from($data['status']) : LoanStatus::ACTIVE,
            returnDate: isset($data['return_date']) ? new \DateTimeImmutable($data['return_date']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
            'borrower_id' => $this->borrowerId,
            'loan_date' => $this->loanDate->format('Y-m-d'),
            'return_date' => $this->returnDate?->format('Y-m-d'),
            'notes' => $this->notes,
            'status' => $this->status->value,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }
}
