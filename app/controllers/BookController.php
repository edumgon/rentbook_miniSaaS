<?php

/**
 * BookController - Book Management
 * 
 * Handles CRUD operations for books.
 */
class BookController extends Controller
{
    /**
     * List all books
     */
    public function index(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $bookModel = new Book();
        
        $status = $_GET['status'] ?? null;
        $books = $bookModel->getBooks($userId, $status);
        
        $this->view('books/list', [
            'title' => 'My Books',
            'books' => $books,
            'status' => $status,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Show add book form
     */
    public function add(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $this->view('books/add', [
            'title' => 'Add Book',
            'csrfToken' => Auth::generateCsrfToken()
        ]);
    }
    
    /**
     * Create new book
     */
    public function store(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/books/add');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        
        $data = [
            'user_id' => $userId,
            'title' => $this->sanitize($_POST['title'] ?? ''),
            'author' => $this->sanitize($_POST['author'] ?? ''),
            'publisher' => $this->sanitize($_POST['publisher'] ?? ''),
            'isbn' => $this->sanitize($_POST['isbn'] ?? ''),
            'cover_url' => $this->sanitize($_POST['cover_url'] ?? ''),
            'status' => 'available'
        ];
        
        if (empty($data['title'])) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/books/add');
        }
        
        $bookModel = new Book();
        $bookId = $bookModel->create($data);
        
        $this->setFlash('success', 'Book added successfully');
        $this->redirect('/books');
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $bookModel = new Book();
        $book = $bookModel->findByUserAndId($userId, $id);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/books');
        }
        
        $this->view('books/edit', [
            'title' => 'Edit Book',
            'book' => $book,
            'csrfToken' => Auth::generateCsrfToken()
        ]);
    }
    
    /**
     * Update book
     */
    public function update(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/books/' . $id . '/edit');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        $bookModel = new Book();
        $book = $bookModel->findByUserAndId($userId, $id);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/books');
        }
        
        $data = [
            'title' => $this->sanitize($_POST['title'] ?? ''),
            'author' => $this->sanitize($_POST['author'] ?? ''),
            'publisher' => $this->sanitize($_POST['publisher'] ?? ''),
            'isbn' => $this->sanitize($_POST['isbn'] ?? ''),
            'cover_url' => $this->sanitize($_POST['cover_url'] ?? '')
        ];
        
        if (empty($data['title'])) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/books/' . $id . '/edit');
        }
        
        $bookModel->update($id, $data);
        
        $this->setFlash('success', 'Book updated successfully');
        $this->redirect('/books');
    }
    
    /**
     * Delete book
     */
    public function delete(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/books');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        $bookModel = new Book();
        $loanModel = new Loan();
        
        $book = $bookModel->findByUserAndId($userId, $id);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/books');
        }
        
        $activeLoan = $loanModel->getActiveLoanForBook($id);
        if ($activeLoan) {
            $this->setFlash('error', 'Cannot delete book with active loan');
            $this->redirect('/books');
        }
        
        $bookModel->delete($id);
        
        $this->setFlash('success', 'Book deleted successfully');
        $this->redirect('/books');
    }
}
