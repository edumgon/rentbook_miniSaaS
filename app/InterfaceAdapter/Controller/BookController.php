<?php

namespace App\InterfaceAdapter\Controller;

use App\Application\UseCase\CreateBookUseCase;
use App\Application\UseCase\CreateBookInput;
use App\Application\UseCase\UpdateBookUseCase;
use App\Application\UseCase\UpdateBookInput;
use App\Application\UseCase\DeleteBookUseCase;
use App\Application\UseCase\DeleteBookInput;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\ValueObject\BookStatus;
use App\Infrastructure\Container\ServiceContainer;

/**
 * Book Controller - Clean Architecture Version
 */
class BookController extends \Controller
{
    private BookRepositoryInterface $bookRepository;
    private CreateBookUseCase $createBookUseCase;
    private UpdateBookUseCase $updateBookUseCase;
    private DeleteBookUseCase $deleteBookUseCase;

    public function __construct()
    {
        $container = ServiceContainer::getInstance();
        $container->initialize();

        $this->bookRepository = $container->get(BookRepositoryInterface::class);
        $this->createBookUseCase = new CreateBookUseCase($this->bookRepository);
        $this->updateBookUseCase = new UpdateBookUseCase($this->bookRepository);
        $this->deleteBookUseCase = new DeleteBookUseCase($this->bookRepository);
    }

    /**
     * List all books
     */
    public function index(): void
    {
        \Auth::init();
        \Auth::requireAuth();

        $userId = \Auth::id();
        $status = $_GET['status'] ?? null;

        if ($status) {
            $books = $this->bookRepository->findByUserAndStatus($userId, BookStatus::from($status));
        } else {
            $books = $this->bookRepository->findByUser($userId);
        }

        $counts = $this->bookRepository->countByStatus($userId);

        $this->view('books/list', [
            'title' => 'My Books',
            'books' => array_map([$this, 'bookToArray'], $books),
            'status' => $status,
            'counts' => $counts,
            'flash' => $this->getFlash()
        ]);
    }

    /**
     * Show add book form
     */
    public function add(): void
    {
        \Auth::init();
        \Auth::requireAuth();

        $this->view('books/add', [
            'title' => 'Add Book',
            'csrfToken' => \Auth::generateCsrfToken()
        ]);
    }

    /**
     * Create new book using Use Case
     */
    public function store(): void
    {
        \Auth::init();
        \Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/books/add');
        }

        $this->validateCsrf();

        $userId = \Auth::id();

        $title = $this->sanitize($_POST['title'] ?? '');
        if (empty($title)) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/books/add');
        }

        try {
            $input = new CreateBookInput(
                userId: $userId,
                title: $title,
                author: $this->sanitize($_POST['author'] ?? ''),
                publisher: $this->sanitize($_POST['publisher'] ?? ''),
                isbn: $this->sanitize($_POST['isbn'] ?? ''),
                coverUrl: $this->sanitize($_POST['cover_url'] ?? '')
            );

            $output = $this->createBookUseCase->execute($input);

            $this->setFlash('success', "Book '{$output->title}' added successfully");
            $this->redirect('/books');

        } catch (\Exception $e) {
            error_log('Book creation failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while adding the book');
            $this->redirect('/books/add');
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
        $book = $this->bookRepository->findByIdAndUser($id, $userId);

        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/books');
        }

        $this->view('books/edit', [
            'title' => 'Edit Book',
            'book' => $this->bookToArray($book),
            'csrfToken' => \Auth::generateCsrfToken()
        ]);
    }

    /**
     * Update book using Use Case
     */
    public function update(int $id): void
    {
        \Auth::init();
        \Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/books/' . $id . '/edit');
        }

        $this->validateCsrf();

        $userId = \Auth::id();

        $title = $this->sanitize($_POST['title'] ?? '');
        if (empty($title)) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/books/' . $id . '/edit');
        }

        try {
            $input = new UpdateBookInput(
                userId: $userId,
                bookId: $id,
                title: $title,
                author: $this->sanitize($_POST['author'] ?? ''),
                publisher: $this->sanitize($_POST['publisher'] ?? ''),
                isbn: $this->sanitize($_POST['isbn'] ?? ''),
                coverUrl: $this->sanitize($_POST['cover_url'] ?? '')
            );

            $output = $this->updateBookUseCase->execute($input);

            $this->setFlash('success', "Book '{$output->title}' updated successfully");
            $this->redirect('/books');

        } catch (\DomainException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/books/' . $id . '/edit');
        } catch (\Exception $e) {
            error_log('Book update failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while updating the book');
            $this->redirect('/books/' . $id . '/edit');
        }
    }

    /**
     * Delete book using Use Case
     */
    public function delete(int $id): void
    {
        \Auth::init();
        \Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/books');
        }

        $this->validateCsrf();

        $userId = \Auth::id();

        try {
            $input = new DeleteBookInput(
                userId: $userId,
                bookId: $id
            );

            $output = $this->deleteBookUseCase->execute($input);

            $this->setFlash('success', "Book '{$output->title}' deleted successfully");
            $this->redirect('/books');

        } catch (\DomainException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/books');
        } catch (\Exception $e) {
            error_log('Book deletion failed: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while deleting the book');
            $this->redirect('/books');
        }
    }

    /**
     * Convert Book entity to array for view compatibility
     */
    private function bookToArray(\App\Domain\Entity\Book $book): array
    {
        return [
            'id' => $book->getId(),
            'user_id' => $book->getUserId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'publisher' => $book->getPublisher(),
            'isbn' => $book->getIsbn(),
            'cover_url' => $book->getCoverUrl(),
            'status' => $book->getStatus()->value,
            'created_at' => $book->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $book->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
