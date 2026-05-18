<?php

namespace App\InterfaceAdapter\Controller;

use App\Application\UseCase\CreateBorrowerUseCase;
use App\Application\UseCase\CreateBorrowerInput;
use App\Application\UseCase\UpdateBorrowerUseCase;
use App\Application\UseCase\UpdateBorrowerInput;
use App\Application\UseCase\DeleteBorrowerUseCase;
use App\Application\UseCase\DeleteBorrowerInput;
use App\Domain\Repository\BorrowerRepositoryInterface;
use App\Infrastructure\Container\ServiceContainer;

/**
 * Borrower Controller - Clean Architecture Version
 */
class BorrowerController extends \Controller
{
    private BorrowerRepositoryInterface $borrowerRepository;
    private CreateBorrowerUseCase $createBorrowerUseCase;
    private UpdateBorrowerUseCase $updateBorrowerUseCase;
    private DeleteBorrowerUseCase $deleteBorrowerUseCase;

    public function __construct()
    {
        $container = ServiceContainer::getInstance();
        $container->initialize();

        $this->borrowerRepository = $container->get(BorrowerRepositoryInterface::class);
        $this->createBorrowerUseCase = new CreateBorrowerUseCase($this->borrowerRepository);
        $this->updateBorrowerUseCase = new UpdateBorrowerUseCase($this->borrowerRepository);
        $this->deleteBorrowerUseCase = new DeleteBorrowerUseCase($this->borrowerRepository);
    }

    /**
     * List all borrowers (index)
     */
    public function index(): void
    {
        $this->list();
    }

    /**
     * List all borrowers
     */
    public function list(): void
    {
        \Auth::init();
        \Auth::requireAuth();

        $userId = \Auth::id();
        $borrowers = $this->borrowerRepository->findByUser($userId);

        $this->view('borrowers/list', [
            'title' => 'My Friends',
            'borrowers' => array_map([$this, 'borrowerToArray'], $borrowers),
            'flash' => $this->getFlash()
        ]);
    }

    /**
     * Create new borrower using Use Case
     */
    public function store(): void
    {
        \Auth::init();
        \Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/borrowers');
        }

        $this->validateCsrf();

        $userId = \Auth::id();

        $name = $this->sanitize($_POST['name'] ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Name is required');
            $this->redirect('/borrowers');
        }

        try {
            $input = new CreateBorrowerInput(
                userId: $userId,
                name: $name,
                email: $this->sanitize($_POST['email'] ?? ''),
                phone: $this->sanitize($_POST['phone'] ?? '')
            );

            $output = $this->createBorrowerUseCase->execute($input);

            $this->setFlash('success', "Friend '{$output->name}' added successfully");
            $this->redirect('/borrowers');

        } catch (\Exception $e) {
            error_log('Borrower creation failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while adding the friend');
            $this->redirect('/borrowers');
        }
    }

    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        \Auth::init();
        \Auth::requireAuth();

        $userId = \Auth::id();
        $borrower = $this->borrowerRepository->findByIdAndUser($id, $userId);

        if (!$borrower) {
            $this->setFlash('error', 'Friend not found');
            $this->redirect('/borrowers');
        }

        $this->view('borrowers/edit', [
            'title' => 'Edit Friend',
            'borrower' => $this->borrowerToArray($borrower),
            'csrfToken' => \Auth::generateCsrfToken()
        ]);
    }

    /**
     * Update borrower using Use Case
     */
    public function update(int $id): void
    {
        \Auth::init();
        \Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/borrowers/' . $id . '/edit');
        }

        $this->validateCsrf();

        $userId = \Auth::id();

        $name = $this->sanitize($_POST['name'] ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Name is required');
            $this->redirect('/borrowers/' . $id . '/edit');
        }

        try {
            $input = new UpdateBorrowerInput(
                userId: $userId,
                borrowerId: $id,
                name: $name,
                email: $this->sanitize($_POST['email'] ?? ''),
                phone: $this->sanitize($_POST['phone'] ?? '')
            );

            $output = $this->updateBorrowerUseCase->execute($input);

            $this->setFlash('success', "Friend '{$output->name}' updated successfully");
            $this->redirect('/borrowers');

        } catch (\DomainException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/borrowers/' . $id . '/edit');
        } catch (\Exception $e) {
            error_log('Borrower update failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while updating the friend');
            $this->redirect('/borrowers/' . $id . '/edit');
        }
    }

    /**
     * Delete borrower using Use Case
     */
    public function delete(int $id): void
    {
        \Auth::init();
        \Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/borrowers');
        }

        $this->validateCsrf();

        $userId = \Auth::id();

        try {
            $input = new DeleteBorrowerInput(
                userId: $userId,
                borrowerId: $id
            );

            $output = $this->deleteBorrowerUseCase->execute($input);

            $this->setFlash('success', "Friend '{$output->name}' deleted successfully");
            $this->redirect('/borrowers');

        } catch (\DomainException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/borrowers');
        } catch (\Exception $e) {
            error_log('Borrower deletion failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while deleting the friend');
            $this->redirect('/borrowers');
        }
    }

    /**
     * Convert Borrower entity to array for view compatibility
     */
    private function borrowerToArray(\App\Domain\Entity\Borrower $borrower): array
    {
        return [
            'id' => $borrower->getId(),
            'user_id' => $borrower->getUserId(),
            'name' => $borrower->getName(),
            'email' => $borrower->getEmail(),
            'phone' => $borrower->getPhone(),
            'created_at' => $borrower->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $borrower->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
