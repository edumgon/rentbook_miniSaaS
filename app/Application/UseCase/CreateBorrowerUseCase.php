<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Borrower;
use App\Domain\Repository\BorrowerRepositoryInterface;

/**
 * Create Borrower Use Case
 */
class CreateBorrowerUseCase
{
    public function __construct(
        private BorrowerRepositoryInterface $borrowerRepository
    ) {
    }

    public function execute(CreateBorrowerInput $input): CreateBorrowerOutput
    {
        $borrower = new Borrower(
            userId: $input->userId,
            name: $input->name,
            email: $input->email,
            phone: $input->phone,
            location: $input->location
        );

        $savedBorrower = $this->borrowerRepository->save($borrower);

        return new CreateBorrowerOutput(
            borrowerId: $savedBorrower->getId(),
            name: $savedBorrower->getName()
        );
    }
}
