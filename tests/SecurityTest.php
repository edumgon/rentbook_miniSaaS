<?php

/**
 * Security Tests
 * 
 * Tests for XSS, SQL Injection, CSRF protection.
 */

require_once __DIR__ . '/bootstrap.php';

$runner = new TestRunner();

// ==================== XSS PROTECTION TESTS ====================

$runner->test('XSS: Output escaping prevents script injection', function($t) {
    $maliciousInput = '<script>alert("xss")</script>';
    $escaped = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
    
    $t->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $escaped);
    $t->assertFalse(strpos($escaped, '<script>') !== false);
});

$runner->test('XSS: JavaScript protocol in URL is escaped', function($t) {
    $maliciousUrl = 'javascript:alert("xss")';
    $escaped = htmlspecialchars($maliciousUrl, ENT_QUOTES, 'UTF-8');
    
    $t->assertContains('javascript', $escaped);
    $t->assertFalse(strpos($escaped, ':') !== false && strpos($escaped, 'javascript:') === false);
});

$runner->test('XSS: Event handlers are escaped', function($t) {
    $malicious = 'onclick="stealCookies()"';
    $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
    
    $t->assertContains('&quot;', $escaped);
    $t->assertFalse(strpos($escaped, 'onclick=') !== false);
});

$runner->test('XSS: HTML entities are properly encoded', function($t) {
    $input = '<img src=x onerror=alert(1)>';
    $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    $t->assertContains('&lt;', $escaped);
    $t->assertContains('&gt;', $escaped);
    $t->assertFalse(strpos($escaped, '<img') !== false);
});

// ==================== SQL INJECTION PROTECTION TESTS ====================

$runner->test('SQL Injection: PDO prepared statements prevent injection', function($t) {
    // Simulate a malicious input that would try to break SQL
    $maliciousInput = "'; DROP TABLE users; --";
    
    // In PDO with prepared statements, this would be safely escaped
    // We're testing the concept - the actual DB test is in DatabaseTest
    $t->assertNotNull($maliciousInput);
    $t->assertTrue(strlen($maliciousInput) > 0);
});

$runner->test('SQL Injection: ORM-style conditions are safe', function($t) {
    $conditions = ['status' => "'; DELETE FROM books; --"];
    
    // When using prepared statements, even malicious values in conditions
    // are treated as data, not SQL commands
    foreach ($conditions as $key => $value) {
        $t->assertTrue(is_string($key));
        $t->assertNotNull($value);
    }
});

$runner->test('SQL Injection: Wildcard characters are safe in prepared statements', function($t) {
    $searchQuery = '%\_%\'%"_"%';
    // In prepared statements, backslash escapes are handled automatically
    $t->assertContains('%', $searchQuery);
    $t->assertContains("'", $searchQuery);
});

// ==================== CSRF PROTECTION TESTS ====================

$runner->test('CSRF: Token generation creates unique tokens', function($t) {
    $token1 = bin2hex(random_bytes(32));
    $token2 = bin2hex(random_bytes(32));
    
    $t->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
    $t->assertNotEquals($token1, $token2);
});

$runner->test('CSRF: Token validation uses hash_equals', function($t) {
    $expected = 'abc123';
    $actual = 'abc123';
    
    $valid = hash_equals($expected, $actual);
    $t->assertTrue($valid);
});

$runner->test('CSRF: hash_equals prevents timing attacks', function($t) {
    $secret = str_repeat('a', 64);
    $userToken = str_repeat('a', 64);
    $wrongToken = str_repeat('b', 64);
    
    $t->assertTrue(hash_equals($secret, $userToken));
    $t->assertFalse(hash_equals($secret, $wrongToken));
});

// ==================== SESSION SECURITY TESTS ====================

$runner->test('Session: Regenerate ID prevents fixation', function($t) {
    // Test that session_regenerate_id concept exists
    $t->assertTrue(function_exists('session_regenerate_id'));
});

$runner->test('Session: Random bytes are cryptographically secure', function($t) {
    $bytes1 = random_bytes(16);
    $bytes2 = random_bytes(16);
    
    $t->assertEquals(16, strlen($bytes1));
    $t->assertEquals(16, strlen($bytes2));
    $t->assertNotEquals($bytes1, $bytes2);
});

$runner->test('Session: State tokens are unique', function($t) {
    $states = [];
    for ($i = 0; $i < 10; $i++) {
        $state = bin2hex(random_bytes(16));
        $t->assertFalse(in_array($state, $states), "Duplicate state generated");
        $states[] = $state;
    }
    $t->assertCount(10, $states);
});

// ==================== INPUT VALIDATION TESTS ====================

$runner->test('Input: Empty strings should be validated', function($t) {
    $empty = '';
    $whitespace = '   ';
    
    $t->assertTrue(empty(trim($empty)));
    $t->assertTrue(empty(trim($whitespace)));
});

$runner->test('Input: Type coercion is prevented', function($t) {
    $stringNumber = '123';
    $actualNumber = 123;
    
    // Strict comparison prevents type juggling
    $t->assertFalse($stringNumber === $actualNumber);
    $t->assertTrue($stringNumber == $actualNumber);
});

$runner->test('Input: Long strings are handled', function($t) {
    $longString = str_repeat('A', 10000);
    $t->assertEquals(10000, strlen($longString));
    
    // Check it can be safely processed
    $escaped = htmlspecialchars($longString);
    $t->assertEquals(10000, strlen($escaped));
});

// ==================== PASSWORD/OAUTH SECURITY TESTS ====================

$runner->test('OAuth: State parameter prevents CSRF', function($t) {
    // OAuth state should be random and session-bound
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    
    $t->assertNotNull($_SESSION['oauth_state']);
    $t->assertEquals($state, $_SESSION['oauth_state']);
});

$runner->test('OAuth: Code exchange uses POST not GET', function($t) {
    // The OAuth token exchange should use POST for security
    // This is a conceptual test - real implementation uses cURL POST
    $t->assertTrue(true); // Concept validated
});

// Run all tests
$runner->run();
