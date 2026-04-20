<?php

/**
 * AuthController - Authentication Controller
 * 
 * Handles Google OAuth login/logout.
 */
class AuthController extends Controller
{
    /**
     * Show login page
     */
    public function login(): void
    {
        Auth::init();
        Auth::requireGuest();
        
        $googleAuthUrl = Auth::getGoogleAuthUrl();
        $this->view('auth/login', [
            'title' => 'Login',
            'googleAuthUrl' => $googleAuthUrl
        ]);
    }
    
    /**
     * Handle Google OAuth callback
     */
    public function callback(): void
    {
        Auth::init();
        
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;
        $error = $_GET['error'] ?? null;
        
        if ($error) {
            $this->setFlash('error', 'Google authentication failed: ' . $error);
            $this->redirect('/login');
        }
        
        if (!$code || !$state) {
            $this->setFlash('error', 'Invalid authentication response');
            $this->redirect('/login');
        }
        
        if (!Auth::validateState($state)) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/login');
        }
        
        $accessToken = Auth::exchangeCode($code);
        
        if (!$accessToken) {
            $this->setFlash('error', 'Failed to authenticate with Google');
            $this->redirect('/login');
        }
        
        $googleUser = Auth::getGoogleUser($accessToken);
        
        if (!$googleUser) {
            $this->setFlash('error', 'Failed to get user information');
            $this->redirect('/login');
        }
        
        $userModel = new User();
        $user = $userModel->createOrUpdateFromGoogle($googleUser);
        
        Auth::login($user);
        
        $userModel->updateLastLogin($user['id']);
        
        $this->setFlash('success', 'Welcome, ' . $user['name'] . '!');
        $this->redirect('/dashboard');
    }
    
    /**
     * Logout user
     */
    public function logout(): void
    {
        Auth::init();
        Auth::logout();
        $this->redirect('/');
    }
}
