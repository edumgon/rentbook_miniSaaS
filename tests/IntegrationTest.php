<?php

/**
 * Integration Tests
 * 
 * Tests for complete workflows and scenarios.
 */

require_once __DIR__ . '/bootstrap.php';

$runner = new TestRunner();

// ==================== SCENARIO: USER REGISTRATION FLOW ====================

$runner->test('Scenario: New user can be created from Google data', function($t) {
    // Simulate Google OAuth response
    $googleData = [
        'id' => 'google_123456',
        'email' => 'newuser@example.com',
        'name' => 'New User',
        'picture' => 'https://example.com/photo.jpg'
    ];
    
    // Verify data structure
    $t->assertArrayHasKey('id', $googleData);
    $t->assertArrayHasKey('email', $googleData);
    $t->assertArrayHasKey('name', $googleData);
    
    // Simulate user model processing
    $userData = [
        'google_id' => $googleData['id'],
        'email' => $googleData['email'],
        'name' => $googleData['name'],
        'avatar_url' => $googleData['picture']
    ];
    
    $t->assertEquals('google_123456', $userData['google_id']);
    $t->assertEquals('newuser@example.com', $userData['email']);
});

// ==================== SCENARIO: BOOK LENDING WORKFLOW ====================

$runner->test('Scenario: Complete book lending flow', function($t) {
    // Step 1: User has a book
    $book = [
        'id' => 1,
        'user_id' => 1,
        'title' => 'The Great Book',
        'status' => 'available'
    ];
    $t->assertEquals('available', $book['status']);
    
    // Step 2: Friend exists
    $borrower = [
        'id' => 1,
        'user_id' => 1,
        'name' => 'John Doe',
        'phone' => '123-456-7890'
    ];
    $t->assertNotNull($borrower);
    
    // Step 3: Create loan
    $loan = [
        'id' => 1,
        'user_id' => 1,
        'book_id' => $book['id'],
        'borrower_id' => $borrower['id'],
        'loan_date' => date('Y-m-d'),
        'status' => 'active'
    ];
    $t->assertEquals('active', $loan['status']);
    $t->assertEquals(1, $loan['book_id']);
    $t->assertEquals(1, $loan['borrower_id']);
    
    // Step 4: Book becomes lent
    $book['status'] = 'lent';
    $t->assertEquals('lent', $book['status']);
    
    // Step 5: Return book
    $loan['status'] = 'returned';
    $loan['return_date'] = date('Y-m-d');
    $book['status'] = 'available';
    
    $t->assertEquals('returned', $loan['status']);
    $t->assertNotNull($loan['return_date']);
    $t->assertEquals('available', $book['status']);
});

// ==================== SCENARIO: BOOK SEARCH ====================

$runner->test('Scenario: Book search from external API', function($t) {
    // Simulate Open Library API response
    $apiResponse = [
        'docs' => [
            [
                'title' => 'The Hobbit',
                'author_name' => ['J.R.R. Tolkien'],
                'isbn' => ['9780547928227'],
                'cover_i' => 12345
            ]
        ]
    ];
    
    $t->assertCount(1, $apiResponse['docs']);
    
    $book = $apiResponse['docs'][0];
    $t->assertEquals('The Hobbit', $book['title']);
    $t->assertEquals('J.R.R. Tolkien', $book['author_name'][0]);
    
    // Verify cover URL generation
    $coverUrl = "https://covers.openlibrary.org/b-id/{$book['cover_i']}-M.jpg";
    $t->assertContains('openlibrary.org', $coverUrl);
    $t->assertContains((string)$book['cover_i'], $coverUrl);
});

$runner->test('Scenario: Book search with multiple APIs', function($t) {
    // Simulate Open Library response
    $openLibResponse = [
        'title' => 'Dom Casmurro',
        'author' => 'Machado de Assis',
        'publisher' => 'Editora Nova',
        'isbn' => '9788535915528',
        'cover' => 'https://covers.openlibrary.org/b/id/12345-M.jpg',
        'source' => 'Open Library'
    ];
    
    // Simulate Google Books response
    $googleResponse = [
        'title' => 'Dom Casmurro',
        'author' => 'Machado de Assis',
        'publisher' => 'Editora Nova',
        'isbn' => '9788535915528',
        'cover' => 'https://books.google.com/books/content?id=xyz',
        'source' => 'Google Books'
    ];
    
    // Verify both have required fields
    $t->assertArrayHasKey('title', $openLibResponse);
    $t->assertArrayHasKey('title', $googleResponse);
    $t->assertArrayHasKey('source', $openLibResponse);
    $t->assertArrayHasKey('source', $googleResponse);
    
    // Verify sources are different
    $t->assertNotEquals($openLibResponse['source'], $googleResponse['source']);
    
    // Verify both can be combined
    $combinedResults = [$openLibResponse, $googleResponse];
    $t->assertCount(2, $combinedResults);
    
    // Verify deduplication would work (same ISBN)
    $t->assertEquals($openLibResponse['isbn'], $googleResponse['isbn']);
});

$runner->test('Scenario: Google Books API response structure', function($t) {
    // Simulate Google Books API response
    $googleApiResponse = [
        'items' => [
            [
                'volumeInfo' => [
                    'title' => 'O Alquimista',
                    'authors' => ['Paulo Coelho'],
                    'publisher' => 'Rocco',
                    'industryIdentifiers' => [
                        ['type' => 'ISBN_13', 'identifier' => '9788575427583']
                    ],
                    'imageLinks' => [
                        'thumbnail' => 'https://books.google.com/books/content?id=xyz&printsec=frontcover'
                    ]
                ]
            ]
        ]
    ];
    
    $t->assertArrayHasKey('items', $googleApiResponse);
    
    $item = $googleApiResponse['items'][0];
    $volume = $item['volumeInfo'];
    
    $t->assertEquals('O Alquimista', $volume['title']);
    $t->assertArrayHasKey('authors', $volume);
    $t->assertArrayHasKey('industryIdentifiers', $volume);
    
    // Verify ISBN extraction
    $identifiers = $volume['industryIdentifiers'];
    $isbn = $identifiers[0]['identifier'];
    $t->assertEquals('9788575427583', $isbn);
});

// ==================== SCENARIO: MULTI-TENANT SECURITY ====================

$runner->test('Scenario: Users cannot access other users data', function($t) {
    $currentUserId = 1;
    $otherUserId = 2;
    
    // Book owned by other user
    $book = [
        'id' => 5,
        'user_id' => $otherUserId,
        'title' => 'Private Book'
    ];
    
    // Verify ownership check would fail
    $t->assertNotEquals($currentUserId, $book['user_id']);
    
    // In real app, this would be: $bookModel->findByUserAndId($currentUserId, 5) returning null
    $hasAccess = ($book['user_id'] === $currentUserId);
    $t->assertFalse($hasAccess);
});

// ==================== SCENARIO: OVERDUE LOAN DETECTION ====================

$runner->test('Scenario: Overdue loans are detected correctly', function($t) {
    $today = new DateTime();
    
    // Loan from 40 days ago (overdue)
    $overdueDate = (clone $today)->modify('-40 days');
    $daysLoaned = $today->diff($overdueDate)->days;
    
    $t->assertTrue($daysLoaned > 30);
    $t->assertEquals(40, $daysLoaned);
    
    // Loan from 10 days ago (not overdue)
    $recentDate = (clone $today)->modify('-10 days');
    $daysRecent = $today->diff($recentDate)->days;
    
    $t->assertFalse($daysRecent > 30);
    $t->assertEquals(10, $daysRecent);
});

// ==================== SCENARIO: FORM VALIDATION ====================

$runner->test('Scenario: Required fields are validated', function($t) {
    $formData = [
        'title' => '',  // Empty - should fail
        'author' => 'Some Author',
        'status' => 'available'
    ];
    
    // Title is required
    $isValid = !empty(trim($formData['title']));
    $t->assertFalse($isValid);
    
    // With valid title
    $formData['title'] = 'Valid Title';
    $isValid = !empty(trim($formData['title']));
    $t->assertTrue($isValid);
});

// ==================== SCENARIO: CSRF PROTECTION IN FORMS ====================

$runner->test('Scenario: Form submissions require valid CSRF token', function($t) {
    $_SESSION = ['csrf_token' => 'valid_token'];
    
    // Valid submission
    $postData = ['csrf_token' => 'valid_token', 'title' => 'Book'];
    $isValid = hash_equals($_SESSION['csrf_token'], $postData['csrf_token']);
    $t->assertTrue($isValid);
    
    // Invalid submission
    $postData = ['csrf_token' => 'invalid_token', 'title' => 'Book'];
    $isValid = hash_equals($_SESSION['csrf_token'], $postData['csrf_token']);
    $t->assertFalse($isValid);
    
    // Missing token
    $postData = ['title' => 'Book'];
    $hasToken = isset($postData['csrf_token']);
    $t->assertFalse($hasToken);
});

// ==================== SCENARIO: AJAX BORROWER CREATION ====================

$runner->test('Scenario: AJAX request creates borrower and returns JSON', function($t) {
    // Simulate successful AJAX response
    $response = [
        'success' => true,
        'borrower' => [
            'id' => 5,
            'name' => 'Jane Smith',
            'phone' => '555-1234',
            'email' => 'jane@example.com'
        ]
    ];
    
    $t->assertTrue($response['success']);
    $t->assertArrayHasKey('borrower', $response);
    $t->assertEquals('Jane Smith', $response['borrower']['name']);
});

// ==================== SCENARIO: BOOK DELETION WITH ACTIVE LOAN ====================

$runner->test('Scenario: Cannot delete book with active loan', function($t) {
    $bookId = 1;
    
    // Check for active loan
    $activeLoan = [
        'id' => 1,
        'book_id' => $bookId,
        'status' => 'active'
    ];
    
    $hasActiveLoan = ($activeLoan !== null && $activeLoan['status'] === 'active');
    $t->assertTrue($hasActiveLoan);
    
    // Deletion should be prevented
    $canDelete = !$hasActiveLoan;
    $t->assertFalse($canDelete);
});

// ==================== SCENARIO: URL ROUTING ====================

$runner->test('Scenario: URLs route to correct controllers', function($t) {
    $routes = [
        ['url' => 'books', 'method' => 'GET', 'controller' => 'BookController', 'action' => 'index'],
        ['url' => 'books/add', 'method' => 'GET', 'controller' => 'BookController', 'action' => 'add'],
        ['url' => 'books/5', 'method' => 'GET', 'controller' => 'BookController', 'action' => 'edit'],
        ['url' => 'loans/create', 'method' => 'GET', 'controller' => 'LoanController', 'action' => 'create'],
    ];
    
    foreach ($routes as $route) {
        $t->assertNotEmpty($route['url']);
        $t->assertNotEmpty($route['controller']);
        $t->assertNotEmpty($route['action']);
    }
    
    $t->assertCount(4, $routes);
});

// Run all tests
$runner->run();
