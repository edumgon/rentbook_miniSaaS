<?php

/**
 * Router - Simple URL Router
 * 
 * Routes URLs to controller actions based on pattern matching.
 * Pure PHP, no external dependencies.
 */
class Router
{
    private array $routes = [];
    private array $params = [];
    
    /**
     * Add a route
     */
    public function add(string $route, string $controller, string $action, string $method = 'GET'): void
    {
        $this->routes[$method][$route] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Add GET route
     */
    public function get(string $route, string $controller, string $action): void
    {
        $this->add($route, $controller, $action, 'GET');
    }
    
    /**
     * Add POST route
     */
    public function post(string $route, string $controller, string $action): void
    {
        $this->add($route, $controller, $action, 'POST');
    }
    
    /**
     * Match URL to a route
     */
    public function match(string $url, string $method): bool
    {
        $url = trim($url, '/');
        $method = strtoupper($method);
        
        if (!isset($this->routes[$method])) {
            return false;
        }
        
        foreach ($this->routes[$method] as $route => $target) {
            $routePattern = $this->convertToRegex($route);
            
            if (preg_match($routePattern, $url, $matches)) {
                $this->params = $this->extractParams($matches);
                $this->currentRoute = $target;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Convert route pattern to regex
     */
    private function convertToRegex(string $route): string
    {
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^\/]+)', $route);
        return '/^' . $route . '$/';
    }
    
    /**
     * Extract named parameters from matches
     */
    private function extractParams(array $matches): array
    {
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    
    /**
     * Dispatch matched route
     */
    public function dispatch(): void
    {
        if (!isset($this->currentRoute)) {
            http_response_code(404);
            $this->handle404();
            return;
        }
        
        $controllerName = $this->currentRoute['controller'];
        $action = $this->currentRoute['action'];
        
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller not found: {$controllerName}");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            throw new Exception("Controller class not found: {$controllerName}");
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $action)) {
            throw new Exception("Action not found: {$action} in {$controllerName}");
        }
        
        call_user_func_array([$controller, $action], $this->params);
    }
    
    /**
     * Handle 404 error
     */
    private function handle404(): void
    {
        $title = 'Page Not Found';
        $content = '<h1>404 - Page Not Found</h1><p>The requested page does not exist.</p>';
        require __DIR__ . '/../views/layout.php';
    }
    
    /**
     * Get current parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
