<?php

/**
 * Book Lending Manager - Entry Point
 * 
 * Front controller that handles all incoming requests.
 * Pure PHP implementation - no external dependencies.
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering
ob_start();

// Define base path
$defaultBasePath = dirname(__DIR__);
$repoBasePath = '/home2/infoegco/repositories/rentbook_miniSaaS';

define('BASE_PATH', is_dir($defaultBasePath . '/app') && is_dir($defaultBasePath . '/config') ? $defaultBasePath : $repoBasePath);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load environment variables
Env::load(BASE_PATH);

// Create Router and define routes
$router = new Router();

// Public routes
$router->get('', 'DashboardController', 'index');
$router->get('login', 'AuthController', 'login');
$router->get('auth/callback', 'AuthController', 'callback');
$router->post('logout', 'AuthController', 'logout');

// Dashboard
$router->get('dashboard', 'DashboardController', 'index');

// Books
$router->get('books', 'BookController', 'index');
$router->get('books/add', 'BookController', 'add');
$router->post('books/store', 'BookController', 'store');
$router->get('books/{id}/edit', 'BookController', 'edit');
$router->post('books/{id}/update', 'BookController', 'update');
$router->post('books/{id}/delete', 'BookController', 'delete');

// Borrowers
$router->get('borrowers', 'BorrowerController', 'index');
$router->get('borrowers/list', 'BorrowerController', 'list');
$router->post('borrowers/store', 'BorrowerController', 'store');
$router->get('borrowers/{id}/edit', 'BorrowerController', 'edit');
$router->post('borrowers/{id}/update', 'BorrowerController', 'update');
$router->post('borrowers/{id}/delete', 'BorrowerController', 'delete');

// Loans
$router->get('loans', 'LoanController', 'index');
$router->get('loans/create', 'LoanController', 'create');
$router->post('loans/store', 'LoanController', 'store');
$router->post('loans/{id}/return', 'LoanController', 'return');
$router->get('loans/history/{bookId}', 'LoanController', 'history');

// Get request URL
$url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Remove subdirectory from URL if present
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($url, ltrim($scriptName, '/')) === 0) {
    $url = substr($url, strlen(ltrim($scriptName, '/')));
    $url = ltrim($url, '/');
}

$method = $_SERVER['REQUEST_METHOD'];

// Match and dispatch route
try {
    if ($router->match($url, $method)) {
        $router->dispatch();
    } else {
        // 404 - Route not found
        http_response_code(404);
        $title = 'Page Not Found';
        $content = '<h1>404 - Page Not Found</h1><p>The requested page does not exist.</p>';
        require APP_PATH . '/views/layout.php';
    }
} catch (Exception $e) {
    // 500 - Server error
    http_response_code(500);
    error_log($e->getMessage());
    $title = 'Server Error';
    $content = '<h1>500 - Server Error</h1><p>Something went wrong. Please try again later.</p>';
    if (ini_get('display_errors')) {
        $content .= '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>';
    }
    require APP_PATH . '/views/layout.php';
}

// Flush output
ob_end_flush();
