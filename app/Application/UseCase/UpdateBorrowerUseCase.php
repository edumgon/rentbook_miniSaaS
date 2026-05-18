<?php

namespace App\Application\UseCase;

use App\Domain\Repository\BorrowerRepositoryInterface;

/**
 * Update Borrower Use Case
 */
class UpdateBorrowerUseCase
{
    public function __construct(
        private BorrowerRepositoryInterface $borrowerRepository
    ) {
    }

    public function execute(UpdateBorrowerInput $input): UpdateBorrowerOutput
    {
        $borrower = $this->borrowerRepository->findByIdAndUser($input->borrowerId, $input->userId);
        
        if (!$borrower) {
            throw new \DomainException('Borrower not found');
        }

        $borrower->update($input->name, $input->email, $input->phone);
        $savedBorrower = $this->borrowerRepository->save($borrower);

        return new UpdateBorrowerOutput(
            borrowerId: $savedBorrower->getId(),
            name: $savedBorrower->getName()
        );
    }
}
