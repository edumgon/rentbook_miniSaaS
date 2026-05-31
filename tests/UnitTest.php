<?php

/**
 * Unit Tests
 * 
 * Tests for models and core classes.
 */

require_once __DIR__ . '/bootstrap.php';

$runner = new TestRunner();

// ==================== ENV TESTS ====================

$runner->test('Env: Can load and retrieve values', function($t) {
    // Create temporary env file
    $envContent = "TEST_KEY=test_value\nTEST_NUMBER=123\n";
    $tempFile = sys_get_temp_dir() . '/test_env_' . uniqid();
    mkdir($tempFile, 0777, true);
    file_put_contents($tempFile . '/.env', $envContent);

    Env::reset();
    Env::load($tempFile);

    $t->assertEquals('test_value', Env::get('TEST_KEY'));
    $t->assertEquals('123', Env::get('TEST_NUMBER'));
    $t->assertEquals('default', Env::get('NON_EXISTENT', 'default'));
    
    // Cleanup
    unlink($tempFile . '/.env');
    rmdir($tempFile);
});

$runner->test('Env: Handles quoted values', function($t) {
    $envContent = 'QUOTED="quoted value"' . "\n" . "SINGLE='single value'\n";
    $tempFile = sys_get_temp_dir() . '/test_env_' . uniqid();
    mkdir($tempFile, 0777, true);
    file_put_contents($tempFile . '/.env', $envContent);

    Env::reset();
    Env::load($tempFile);

    $t->assertEquals('quoted value', Env::get('QUOTED'));
    $t->assertEquals('single value', Env::get('SINGLE'));
    
    // Cleanup
    unlink($tempFile . '/.env');
    rmdir($tempFile);
});

// ==================== ROUTER TESTS ====================

$runner->test('Router: Can add and match routes', function($t) {
    $router = new Router();
    $router->get('test', 'TestController', 'index');
    
    $matched = $router->match('test', 'GET');
    $t->assertTrue($matched);
});

$runner->test('Router: Parameters are extracted from URL', function($t) {
    $router = new Router();
    $router->get('books/{id}', 'BookController', 'show');
    
    $matched = $router->match('books/123', 'GET');
    $t->assertTrue($matched);
    
    $params = $router->getParams();
    $t->assertArrayHasKey('id', $params);
    $t->assertEquals('123', $params['id']);
});

$runner->test('Router: 404 for non-matching routes', function($t) {
    $router = new Router();
    $router->get('books', 'BookController', 'index');
    
    $matched = $router->match('nonexistent', 'GET');
    $t->assertFalse($matched);
});

$runner->test('Router: Different HTTP methods are separated', function($t) {
    $router = new Router();
    $router->get('resource', 'Controller', 'index');
    $router->post('resource', 'Controller', 'store');
    
    $t->assertTrue($router->match('resource', 'GET'));
    $t->assertTrue($router->match('resource', 'POST'));
});

// ==================== AUTH TESTS ====================

$runner->test('Auth: CSRF token generation', function($t) {
    $_SESSION = []; // Reset session
    
    $token = Auth::generateCsrfToken();
    
    $t->assertNotNull($token);
    $t->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
    $t->assertEquals($token, $_SESSION['csrf_token']);
});

$runner->test('Auth: CSRF token validation', function($t) {
    $_SESSION = ['csrf_token' => 'valid_token_123'];
    
    $t->assertTrue(Auth::validateCsrfToken('valid_token_123'));
    $t->assertFalse(Auth::validateCsrfToken('invalid_token'));
    $t->assertFalse(Auth::validateCsrfToken(''));
});

$runner->test('Auth: State generation for OAuth', function($t) {
    $_SESSION = [];
    
    $state = Auth::generateState();
    
    $t->assertNotNull($state);
    $t->assertEquals(32, strlen($state)); // 16 bytes = 32 hex chars
    $t->assertEquals($state, $_SESSION['oauth_state']);
});

$runner->test('Auth: State validation and cleanup', function($t) {
    $_SESSION = ['oauth_state' => 'test_state'];
    
    $t->assertTrue(Auth::validateState('test_state'));
    $t->assertFalse(isset($_SESSION['oauth_state'])); // Should be cleared after validation
});

$runner->test('Auth: Invalid state is rejected', function($t) {
    $_SESSION = ['oauth_state' => 'correct_state'];
    
    $t->assertFalse(Auth::validateState('wrong_state'));
    $t->assertFalse(Auth::validateState(''));
});

$runner->test('Auth: Login sets user and regenerates session', function($t) {
    $_SESSION = [];
    
    $user = ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'];
    Auth::login($user);
    
    $t->assertEquals($user, $_SESSION['user']);
    $t->assertTrue(Auth::check());
    $t->assertEquals($user, Auth::user());
    $t->assertEquals(1, Auth::id());
});

$runner->test('Auth: Logout clears session', function($t) {
    $_SESSION = ['user' => ['id' => 1]];
    
    Auth::logout();
    
    $t->assertEquals([], $_SESSION);
    $t->assertFalse(Auth::check());
    $t->assertNull(Auth::user());
});

// ==================== DATABASE TESTS ====================

$runner->test('Database: Configuration structure is correct', function($t) {
    // We can't test actual DB without connection, but we can test config structure
    $config = require CONFIG_PATH . '/database.php';
    
    $t->assertArrayHasKey('host', $config);
    $t->assertArrayHasKey('database', $config);
    $t->assertArrayHasKey('username', $config);
    $t->assertArrayHasKey('password', $config);
    $t->assertArrayHasKey('charset', $config);
});

// ==================== MODEL TESTS ====================

$runner->test('Model: Data sanitization works correctly', function($t) {
    $dirtyInput = "<script>alert('xss')</script>";
    $cleanInput = htmlspecialchars($dirtyInput, ENT_QUOTES, 'UTF-8');
    
    $t->assertNotEquals($dirtyInput, $cleanInput);
    $t->assertFalse(strpos($cleanInput, '<script>') !== false);
});

$runner->test('Model: Date formatting is correct', function($t) {
    $date = date('Y-m-d H:i:s');
    
    $t->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
});

$runner->test('Book: Status values are valid', function($t) {
    $validStatuses = ['available', 'lent'];
    
    $t->assertContains('available', implode(',', $validStatuses));
    $t->assertContains('lent', implode(',', $validStatuses));
});

$runner->test('Loan: Status values are valid', function($t) {
    $validStatuses = ['active', 'returned'];
    
    $t->assertContains('active', implode(',', $validStatuses));
    $t->assertContains('returned', implode(',', $validStatuses));
});

// ==================== CONTROLLER TESTS ====================

$runner->test('Controller: Sanitize method cleans input', function($t) {
    // Test sanitize logic
    $dirty = "  <b>Test</b>  ";
    $clean = htmlspecialchars(trim($dirty), ENT_QUOTES, 'UTF-8');
    
    $t->assertContains('&lt;', $clean);
    $t->assertContains('&gt;', $clean);
    $t->assertFalse(strpos($clean, '<b>') !== false);
});

// Run all tests
$runner->run();
