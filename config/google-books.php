<?php

/**
 * Google Books API Configuration
 * 
 * Configuration for Google Books API access.
 * API Key is loaded from environment variables for security.
 */

require_once __DIR__ . '/../app/core/Env.php';

return [
    'api_key' => Env::get('GOOGLE_BOOKS_API_KEY', ''),
    'enabled' => !empty(Env::get('GOOGLE_BOOKS_API_KEY', '')),
];
