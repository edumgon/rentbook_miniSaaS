<?php

/**
 * Test Bootstrap
 * 
 * Sets up environment for testing.
 */

// Define test environment
define('TEST_MODE', true);
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');

// Autoload classes
spl_autoload_register(function ($class) {
    // PSR-4 for the Clean Architecture namespace (App\Domain\Entity\Book -> app/Domain/Entity/Book.php)
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $relative = substr($class, strlen($prefix));
        $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
        return;
    }

    // Legacy non-namespaced classes
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        __DIR__ . '/' . $class . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load test configuration
require_once __DIR__ . '/TestRunner.php';

// Mock session for testing
if (!isset($_SESSION)) {
    $_SESSION = [];
}

// Suppress error output during tests
ini_set('display_errors', '0');
error_reporting(E_ALL);
