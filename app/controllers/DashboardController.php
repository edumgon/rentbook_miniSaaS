<?php

/**
 * DashboardController - Main Dashboard
 * 
 * Shows overview of books, loans, and statistics.
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index(): void
    {
        Auth::init();
        Auth::requireAuth();
        
        $userId = Auth::id();
        
        $bookModel = new Book();
        $loanModel = new Loan();
        
        $bookStats = $bookModel->countByStatus($userId);
        $activeLoans = $loanModel->getLoansWithDetails($userId, 'active');
        $overdueLoans = $loanModel->getOverdueLoans($userId);
        
        $this->view('dashboard', [
            'title' => 'Dashboard',
            'bookStats' => $bookStats,
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'flash' => $this->getFlash()
        ]);
    }
}
