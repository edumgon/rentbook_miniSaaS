<?php

/**
 * BorrowerController - Borrower/Friend Management
 * 
 * Handles CRUD operations for borrowers.
 */
class BorrowerController extends Controller
{
    /**
     * List all borrowers
     */
    public function index(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $borrowerModel = new Borrower();
        $borrowers = $borrowerModel->all($userId);
        
        $this->view('borrowers/list', [
            'title' => 'My Friends',
            'borrowers' => $borrowers,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Get borrowers list as JSON (for AJAX)
     */
    public function list(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $borrowerModel = new Borrower();
        $borrowers = $borrowerModel->all($userId);
        
        $this->json(['borrowers' => $borrowers]);
    }
    
    /**
     * Create new borrower (AJAX)
     */
    public function store(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }
        
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
        
        $userId = Auth::id();
        
        $data = [
            'user_id' => $userId,
            'name' => $this->sanitize($_POST['name'] ?? ''),
            'phone' => $this->sanitize($_POST['phone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'location' => $this->sanitize($_POST['location'] ?? '')
        ];
        
        if (empty($data['name'])) {
            $this->json(['error' => 'Name is required'], 400);
        }
        
        $borrowerModel = new Borrower();
        $borrowerId = $borrowerModel->create($data);
        $borrower = $borrowerModel->find($borrowerId);
        
        $this->json(['success' => true, 'borrower' => $borrower]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        $borrowerModel = new Borrower();
        $borrower = $borrowerModel->findByUserAndId($userId, $id);
        
        if (!$borrower) {
            $this->setFlash('error', 'Friend not found');
            $this->redirect('/borrowers');
        }
        
        $this->view('borrowers/edit', [
            'title' => 'Edit Friend',
            'borrower' => $borrower,
            'csrfToken' => Auth::generateCsrfToken()
        ]);
    }
    
    /**
     * Update borrower
     */
    public function update(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/borrowers/' . $id . '/edit');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        $borrowerModel = new Borrower();
        $borrower = $borrowerModel->findByUserAndId($userId, $id);
        
        if (!$borrower) {
            $this->setFlash('error', 'Friend not found');
            $this->redirect('/borrowers');
        }
        
        $data = [
            'name' => $this->sanitize($_POST['name'] ?? ''),
            'phone' => $this->sanitize($_POST['phone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'location' => $this->sanitize($_POST['location'] ?? '')
        ];
        
        if (empty($data['name'])) {
            $this->setFlash('error', 'Name is required');
            $this->redirect('/borrowers/' . $id . '/edit');
        }
        
        $borrowerModel->update($id, $data);
        
        $this->setFlash('success', 'Friend updated successfully');
        $this->redirect('/borrowers');
    }
    
    /**
     * Delete borrower
     */
    public function delete(int $id): void
    {
        Auth::init();
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/borrowers');
        }
        
        $this->validateCsrf();
        
        $userId = Auth::id();
        $borrowerModel = new Borrower();
        
        $borrower = $borrowerModel->findByUserAndId($userId, $id);
        
        if (!$borrower) {
            $this->setFlash('error', 'Friend not found');
            $this->redirect('/borrowers');
        }
        
        if ($borrowerModel->hasActiveLoans($id)) {
            $this->setFlash('error', 'Cannot delete friend with active loans');
            $this->redirect('/borrowers');
        }
        
        $borrowerModel->delete($id);
        
        $this->setFlash('success', 'Friend deleted successfully');
        $this->redirect('/borrowers');
    }
}
