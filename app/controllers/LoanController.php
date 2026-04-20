<?php

/**
 * LoanController - Loan Management
 * 
 * Handles lending and returning books.
 */
class LoanController extends Controller
{
    /**
     * List all loans
     */
    public function index(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $loanModel = new Loan();
        
        $status = $_GET['status'] ?? 'active';
        $loans = $loanModel->getLoansWithDetails($userId, $status);
        
        $this->view('loans/list', [
            'title' => $status === 'active' ? 'Active Loans' : 'Returned Books',
            'loans' => $loans,
            'status' => $status,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Show create loan form
     */
    public function create(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $bookModel = new Book();
        $borrowerModel = new Borrower();
        
        $availableBooks = $bookModel->getBooks($userId, 'available');
        $borrowers = $borrowerModel->all($userId);
        
        if (empty($availableBooks)) {
            $this->setFlash('error', 'No available books to lend. Add books first.');
            $this->redirect('/books');
        }
        
        if (empty($borrowers)) {
            $this->setFlash('error', 'No friends to lend to. Add friends first.');
            $this->redirect('/borrowers');
        }
        
        $this->view('loans/create', [
            'title' => 'Lend a Book',
            'books' => $availableBooks,
            'borrowers' => $borrowers,
            'csrfToken' => Auth::generateCsrfToken()
        ]);
    }
    
    /**
     * Store new loan
     */
    public function store(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loans/create');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $borrowerId = (int) ($_POST['borrower_id'] ?? 0);
        
        if (!$bookId || !$borrowerId) {
            $this->setFlash('error', 'Please select both a book and a friend');
            $this->redirect('/loans/create');
        }
        
        $bookModel = new Book();
        $borrowerModel = new Borrower();
        $loanModel = new Loan();
        
        $book = $bookModel->findByUserAndId($userId, $bookId);
        $borrower = $borrowerModel->findByUserAndId($userId, $borrowerId);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/loans/create');
        }
        
        if (!$borrower) {
            $this->setFlash('error', 'Friend not found');
            $this->redirect('/loans/create');
        }
        
        if ($book['status'] !== 'available') {
            $this->setFlash('error', 'Book is not available for lending');
            $this->redirect('/loans/create');
        }
        
        $data = [
            'user_id' => $userId,
            'book_id' => $bookId,
            'borrower_id' => $borrowerId,
            'loan_date' => $_POST['loan_date'] ?? date('Y-m-d'),
            'notes' => $this->sanitize($_POST['notes'] ?? ''),
            'status' => 'active'
        ];
        
        $loanId = $loanModel->create($data);
        $bookModel->updateStatus($bookId, 'lent');
        
        $this->setFlash('success', 'Book lent successfully');
        $this->redirect('/dashboard');
    }
    
    /**
     * Mark loan as returned
     */
    public function return(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        $loanModel = new Loan();
        $bookModel = new Book();
        
        $loan = $loanModel->findByUserAndId($userId, $id);
        
        if (!$loan) {
            $this->setFlash('error', 'Loan not found');
            $this->redirect('/dashboard');
        }
        
        if ($loan['status'] !== 'active') {
            $this->setFlash('error', 'Loan is already returned');
            $this->redirect('/dashboard');
        }
        
        $loanModel->markReturned($id);
        $bookModel->updateStatus($loan['book_id'], 'available');
        
        $this->setFlash('success', 'Book marked as returned');
        $this->redirect('/dashboard');
    }
    
    /**
     * Show loan history for a book
     */
    public function history(int $bookId): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $bookModel = new Book();
        $loanModel = new Loan();
        
        $book = $bookModel->findByUserAndId($userId, $bookId);
        
        if (!$book) {
            $this->setFlash('error', 'Book not found');
            $this->redirect('/books');
        }
        
        $history = $loanModel->getBookHistory($bookId);
        
        $this->view('loans/history', [
            'title' => 'Loan History: ' . $book['title'],
            'book' => $book,
            'history' => $history,
            'flash' => $this->getFlash()
        ]);
    }
}
