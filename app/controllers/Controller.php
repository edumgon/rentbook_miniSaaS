<?php

/**
 * Controller - Base Controller Class
 * 
 * Provides common functionality for all controllers.
 */
abstract class Controller
{
    /**
     * Render a view with data
     */
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data);
        
        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$viewPath}");
        }
        
        $content = $this->renderView($viewFile, $data);
        require __DIR__ . '/../views/layout.php';
    }
    
    /**
     * Render view content only (for layout)
     */
    private function renderView(string $viewFile, array $data): string
    {
        ob_start();
        extract($data);
        require $viewFile;
        return ob_get_clean();
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get and clear flash message
     */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    /**
     * Validate CSRF token from POST
     */
    protected function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
    }
    
    /**
     * Sanitize input
     */
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
