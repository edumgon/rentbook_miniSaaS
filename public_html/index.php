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

// Autoload classes - PSR-4 compatible
spl_autoload_register(function ($class) {
    // PSR-4 namespace support (App\Domain\Entity\Book -> app/Domain/Entity/Book.php)
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Legacy autoload for non-namespaced classes (Auth, Controller base, Model, User)
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php'
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
$router->get('', 'App\InterfaceAdapter\Controller\DashboardController', 'index');
$router->get('login', 'AuthController', 'login');
$router->get('auth/callback', 'AuthController', 'callback');
$router->post('logout', 'AuthController', 'logout');

// Dashboard
$router->get('dashboard', 'App\InterfaceAdapter\Controller\DashboardController', 'index');

// Books - Clean Architecture Controllers
$router->get('books', 'App\InterfaceAdapter\Controller\BookController', 'index');
$router->get('books/add', 'App\InterfaceAdapter\Controller\BookController', 'add');
$router->post('books/store', 'App\InterfaceAdapter\Controller\BookController', 'store');
$router->get('books/{id}/edit', 'App\InterfaceAdapter\Controller\BookController', 'edit');
$router->post('books/{id}/update', 'App\InterfaceAdapter\Controller\BookController', 'update');
$router->post('books/{id}/delete', 'App\InterfaceAdapter\Controller\BookController', 'delete');

// Borrowers - Clean Architecture Controllers
$router->get('borrowers', 'App\InterfaceAdapter\Controller\BorrowerController', 'index');
$router->get('borrowers/list', 'App\InterfaceAdapter\Controller\BorrowerController', 'list');
$router->post('borrowers/store', 'App\InterfaceAdapter\Controller\BorrowerController', 'store');
$router->get('borrowers/{id}/edit', 'App\InterfaceAdapter\Controller\BorrowerController', 'edit');
$router->post('borrowers/{id}/update', 'App\InterfaceAdapter\Controller\BorrowerController', 'update');
$router->post('borrowers/{id}/delete', 'App\InterfaceAdapter\Controller\BorrowerController', 'delete');

// Loans - Clean Architecture Controllers
$router->get('loans', 'App\InterfaceAdapter\Controller\LoanController', 'index');
$router->get('loans/create', 'App\InterfaceAdapter\Controller\LoanController', 'create');
$router->post('loans/store', 'App\InterfaceAdapter\Controller\LoanController', 'store');
$router->post('loans/{id}/return', 'App\InterfaceAdapter\Controller\LoanController', 'return');
$router->get('loans/history/{bookId}', 'App\InterfaceAdapter\Controller\LoanController', 'history');

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
