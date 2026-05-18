<?php

namespace App\Application\UseCase;

use App\Domain\Repository\BorrowerRepositoryInterface;

/**
 * Delete Borrower Use Case
 */
class DeleteBorrowerUseCase
{
    public function __construct(
        private BorrowerRepositoryInterface $borrowerRepository
    ) {
    }

    public function execute(DeleteBorrowerInput $input): DeleteBorrowerOutput
    {
        $borrower = $this->borrowerRepository->findByIdAndUser($input->borrowerId, $input->userId);
        
        if (!$borrower) {
            throw new \DomainException('Borrower not found');
        }

        $this->borrowerRepository->delete($input->borrowerId);

        return new DeleteBorrowerOutput(
            borrowerId: $input->borrowerId,
            name: $borrower->getName()
        );
    }
}
