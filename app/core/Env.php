<?php

/**
 * Env - Environment Variables Loader
 * 
 * Simple .env file parser for loading environment variables.
 * Pure PHP implementation.
 */
class Env
{
    private static array $variables = [];
    private static bool $loaded = false;

    /**
     * Reset loader state (used by tests to load a different .env in isolation).
     */
    public static function reset(): void
    {
        self::$variables = [];
        self::$loaded = false;
    }

    /**
     * Load .env file
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }
        
        $envFile = $path . '/.env';
        
        if (!file_exists($envFile)) {
            // Try to load from environment variables directly
            self::$loaded = true;
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (strlen($value) >= 2 && 
                    (($value[0] === '"' && $value[strlen($value)-1] === '"') ||
                     ($value[0] === "'" && $value[strlen($value)-1] === "'"))) {
                    $value = substr($value, 1, -1);
                }
                
                self::$variables[$key] = $value;
                
                // Also set as environment variable for compatibility
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // Check loaded variables first
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        // Check $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Check getenv
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Check if variable exists
     */
    public static function has(string $key): bool
    {
        return isset(self::$variables[$key]) || 
               isset($_ENV[$key]) || 
               getenv($key) !== false;
    }
}
