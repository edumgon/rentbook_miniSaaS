<?php

/**
 * Auth - Authentication Manager
 * 
 * Handles Google OAuth 2.0 and session management.
 * Pure PHP implementation, no external SDKs.
 */
class Auth
{
    private static ?array $user = null;
    
    /**
     * Initialize session
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user'])) {
            self::$user = $_SESSION['user'];
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        return self::$user !== null;
    }
    
    /**
     * Get current user
     */
    public static function user(): ?array
    {
        return self::$user;
    }
    
    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        return self::$user['id'] ?? null;
    }
    
    /**
     * Login user (set session)
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        self::$user = $user;
    }
    
    /**
     * Logout user
     */
    public static function logout(): void
    {
        session_destroy();
        self::$user = null;
    }
    
    /**
     * Redirect if not authenticated
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Redirect if authenticated
     */
    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Generate random state for OAuth CSRF protection
     */
    public static function generateState(): string
    {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        return $state;
    }
    
    /**
     * Validate OAuth state parameter
     */
    public static function validateState(string $state): bool
    {
        if (empty($_SESSION['oauth_state'])) {
            return false;
        }
        
        $valid = hash_equals($_SESSION['oauth_state'], $state);
        unset($_SESSION['oauth_state']);
        return $valid;
    }
    
    /**
     * Get Google OAuth URL
     */
    public static function getGoogleAuthUrl(): string
    {
        $config = require __DIR__ . '/../../config/oauth.php';
        
        $params = [
            'client_id' => $config['google']['client_id'],
            'redirect_uri' => $config['google']['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => self::generateState()
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    /**
     * Exchange OAuth code for access token
     */
    public static function exchangeCode(string $code): ?string
    {
        $config = require __DIR__ . '/../../config/oauth.php';
        
        $data = [
            'code' => $code,
            'client_id' => $config['google']['client_id'],
            'client_secret' => $config['google']['client_secret'],
            'redirect_uri' => $config['google']['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('OAuth request failed: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            error_log('OAuth token exchange failed: ' . $response);
            return null;
        }
        
        return $tokenData['access_token'];
    }
    
    /**
     * Get user info from Google
     */
    public static function getGoogleUser(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('Google userinfo request failed: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        $userData = json_decode($response, true);
        
        if (!isset($userData['id'])) {
            error_log('Google userinfo fetch failed: ' . $response);
            return null;
        }
        
        return $userData;
    }
    
    /**
     * Generate CSRF token for forms
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
