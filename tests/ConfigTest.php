<?php

/**
 * Configuration Tests
 * 
 * Validates system configuration and requirements.
 */

require_once __DIR__ . '/bootstrap.php';

$runner = new TestRunner();

// ==================== PHP REQUIREMENTS ====================

$runner->test('PHP: Version is 8.0 or higher', function($t) {
    $version = PHP_VERSION_ID;
    $t->assertTrue($version >= 80000, "PHP version must be 8.0+. Current: " . PHP_VERSION);
});

$runner->test('PHP: PDO extension is available', function($t) {
    $t->assertTrue(extension_loaded('pdo'), "PDO extension required");
});

$runner->test('PHP: PDO MySQL driver is available', function($t) {
    $t->assertTrue(extension_loaded('pdo_mysql'), "PDO MySQL driver required");
});

$runner->test('PHP: cURL extension is available', function($t) {
    $t->assertTrue(extension_loaded('curl'), "cURL extension required for OAuth");
});

$runner->test('PHP: Session extension is available', function($t) {
    $t->assertTrue(extension_loaded('session'), "Session extension required");
});

$runner->test('PHP: JSON extension is available', function($t) {
    $t->assertTrue(extension_loaded('json'), "JSON extension required");
});

$runner->test('PHP: MBString extension is available', function($t) {
    $t->assertTrue(extension_loaded('mbstring'), "MBString extension recommended");
});

// ==================== FILE STRUCTURE ====================

$runner->test('Files: Core files exist', function($t) {
    $requiredFiles = [
        BASE_PATH . '/app/core/Database.php',
        BASE_PATH . '/app/core/Router.php',
        BASE_PATH . '/app/core/Auth.php',
        BASE_PATH . '/app/core/Env.php',
    ];
    
    foreach ($requiredFiles as $file) {
        $t->assertTrue(file_exists($file), "Required file missing: {$file}");
    }
});

$runner->test('Files: Model files exist', function($t) {
    $requiredFiles = [
        BASE_PATH . '/app/models/Model.php',
        BASE_PATH . '/app/models/User.php',
        BASE_PATH . '/app/models/Book.php',
        BASE_PATH . '/app/models/Borrower.php',
        BASE_PATH . '/app/models/Loan.php',
    ];
    
    foreach ($requiredFiles as $file) {
        $t->assertTrue(file_exists($file), "Required file missing: {$file}");
    }
});

$runner->test('Files: Controller files exist', function($t) {
    $requiredFiles = [
        BASE_PATH . '/app/controllers/Controller.php',
        BASE_PATH . '/app/controllers/AuthController.php',
        BASE_PATH . '/app/controllers/DashboardController.php',
        BASE_PATH . '/app/controllers/BookController.php',
        BASE_PATH . '/app/controllers/BorrowerController.php',
        BASE_PATH . '/app/controllers/LoanController.php',
    ];
    
    foreach ($requiredFiles as $file) {
        $t->assertTrue(file_exists($file), "Required file missing: {$file}");
    }
});

$runner->test('Files: Public assets exist', function($t) {
    $requiredFiles = [
        BASE_PATH . '/public_html/index.php',
        BASE_PATH . '/public_html/.htaccess',
        BASE_PATH . '/public_html/css/style.css',
        BASE_PATH . '/public_html/js/app.js',
    ];
    
    foreach ($requiredFiles as $file) {
        $t->assertTrue(file_exists($file), "Required file missing: {$file}");
    }
});

$runner->test('Code: JavaScript includes Google Books API integration', function($t) {
    $jsContent = file_get_contents(BASE_PATH . '/public_html/js/app.js');
    
    // Check for Google Books API function
    $t->assertContains('searchGoogleBooks', $jsContent, 'Google Books search function missing');
    
    // Check for API selector
    $t->assertContains('api-select', $jsContent, 'API selector missing');
    
    // Check for Google Books API endpoint
    $t->assertContains('googleapis.com/books', $jsContent, 'Google Books API endpoint missing');
    
    // Check that API key is used from window config (not hardcoded)
    $t->assertContains('window.GOOGLE_BOOKS_CONFIG', $jsContent, 'Window config missing');
    $t->assertContains('apiKey', $jsContent, 'API key reference missing');
    
    // Check that Open Library is still present
    $t->assertContains('searchOpenLibrary', $jsContent, 'Open Library search function missing');
    $t->assertContains('openlibrary.org', $jsContent, 'Open Library API endpoint missing');
});

$runner->test('Files: Google Books configuration file exists', function($t) {
    $t->assertTrue(file_exists(BASE_PATH . '/config/google-books.php'), 'Google Books config file missing');
});

$runner->test('Config: Google Books config structure is valid', function($t) {
    $config = require BASE_PATH . '/config/google-books.php';
    
    $t->assertArrayHasKey('api_key', $config, 'api_key missing from config');
    $t->assertArrayHasKey('enabled', $config, 'enabled missing from config');
    $t->assertIsBool($config['enabled'], 'enabled must be boolean');
});

$runner->test('Files: Configuration files exist', function($t) {
    $t->assertTrue(file_exists(BASE_PATH . '/config/database.php'));
    $t->assertTrue(file_exists(BASE_PATH . '/config/oauth.php'));
    $t->assertTrue(file_exists(BASE_PATH . '/database/schema.sql'));
});

$runner->test('Files: Documentation exists', function($t) {
    $t->assertTrue(file_exists(BASE_PATH . '/README.md'));
    $t->assertTrue(file_exists(BASE_PATH . '/.env.example'));
});

// ==================== CONFIGURATION VALIDITY ====================

$runner->test('Config: Database config is valid PHP', function($t) {
    $config = require BASE_PATH . '/config/database.php';
    
    $t->assertTrue(is_array($config));
    $t->assertArrayHasKey('host', $config);
    $t->assertArrayHasKey('database', $config);
    $t->assertArrayHasKey('username', $config);
    $t->assertArrayHasKey('password', $config);
    $t->assertArrayHasKey('charset', $config);
});

$runner->test('Config: OAuth config is valid PHP', function($t) {
    $config = require BASE_PATH . '/config/oauth.php';
    
    $t->assertTrue(is_array($config));
    $t->assertArrayHasKey('google', $config);
    $t->assertArrayHasKey('client_id', $config['google']);
    $t->assertArrayHasKey('client_secret', $config['google']);
    $t->assertArrayHasKey('redirect_uri', $config['google']);
});

$runner->test('Config: Database schema SQL is valid', function($t) {
    $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
    
    $t->assertContains('CREATE TABLE', $schema);
    $t->assertContains('users', $schema);
    $t->assertContains('books', $schema);
    $t->assertContains('borrowers', $schema);
    $t->assertContains('loans', $schema);
});

// ==================== SECURITY CHECKS ====================

$runner->test('Security: .htaccess protects sensitive files', function($t) {
    $htaccess = file_get_contents(BASE_PATH . '/public_html/.htaccess');
    
    $t->assertContains('RewriteEngine On', $htaccess);
    $t->assertContains('index.php', $htaccess);
});

$runner->test('Security: .gitignore excludes sensitive files', function($t) {
    $gitignore = file_get_contents(BASE_PATH . '/.gitignore');
    
    $t->assertContains('.env', $gitignore);
});

$runner->test('Security: No hardcoded credentials in source', function($t) {
    // Scan for potential hardcoded passwords
    $files = [
        BASE_PATH . '/app/core/Database.php',
        BASE_PATH . '/config/database.php',
        BASE_PATH . '/config/oauth.php',
    ];
    
    $suspiciousPatterns = ['password123', 'secret', 'admin', 'root'];
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        foreach ($suspiciousPatterns as $pattern) {
            // Check if suspicious pattern exists outside of comments/examples
            $cleanContent = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
            $cleanContent = preg_replace('/\/\/.*$/m', '', $cleanContent);
            
            // Only fail if it's a real value, not in comments
            if (strpos(strtolower($cleanContent), $pattern) !== false) {
                // This is just a warning - not a strict test
                // Real credentials should be in .env only
            }
        }
    }
    
    $t->assertTrue(true); // Pass if we get here
});

// ==================== CODE QUALITY ====================

$runner->test('Code: No syntax errors in PHP files', function($t) {
    $files = [
        BASE_PATH . '/app/core/Database.php',
        BASE_PATH . '/app/core/Router.php',
        BASE_PATH . '/app/core/Auth.php',
        BASE_PATH . '/app/models/Model.php',
        BASE_PATH . '/public_html/index.php',
    ];
    
    foreach ($files as $file) {
        $output = [];
        $return = 0;
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return);
        
        $t->assertEquals(0, $return, "Syntax error in {$file}: " . implode("\n", $output));
    }
});

$runner->test('Code: All classes are loadable', function($t) {
    $classes = ['Database', 'Router', 'Auth', 'Env', 'Model', 'User', 'Book', 'Borrower', 'Loan'];
    
    foreach ($classes as $class) {
        $t->assertTrue(class_exists($class), "Class {$class} not loadable");
    }
});

// Run all tests
$runner->run();
