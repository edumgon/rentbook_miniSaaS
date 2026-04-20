<?php

/**
 * Database Configuration
 * 
 * Values are loaded from .env file or environment variables.
 * For cPanel: Update .env file with your actual credentials.
 */

return [
    'host' => Env::get('DB_HOST', 'localhost'),
    'database' => Env::get('DB_NAME', 'book_lending_db'),
    'username' => Env::get('DB_USER', 'root'),
    'password' => Env::get('DB_PASS', ''),
    'charset' => 'utf8mb4'
];
