<?php

/**
 * TestRunner - Simple PHP Test Framework
 * 
 * Zero dependencies - pure PHP implementation.
 * Run: php tests/TestRunner.php
 */

class TestRunner
{
    private array $tests = [];
    private int $passed = 0;
    private int $failed = 0;
    private int $errors = 0;
    private array $results = [];
    
    /**
     * Register a test
     */
    public function test(string $name, callable $callback): void
    {
        $this->tests[] = ['name' => $name, 'callback' => $callback];
    }
    
    /**
     * Run all tests
     */
    public function run(): void
    {
        echo "🧪 Running Tests...\n";
        echo str_repeat('=', 60) . "\n\n";
        
        foreach ($this->tests as $test) {
            $this->runTest($test);
        }
        
        $this->report();
    }
    
    /**
     * Run single test
     */
    private function runTest(array $test): void
    {
        $name = $test['name'];
        $callback = $test['callback'];
        
        try {
            $callback($this);
            $this->passed++;
            $this->results[] = ['name' => $name, 'status' => 'PASS', 'message' => ''];
            echo "  ✅ PASS: {$name}\n";
        } catch (AssertionError $e) {
            $this->failed++;
            $this->results[] = ['name' => $name, 'status' => 'FAIL', 'message' => $e->getMessage()];
            echo "  ❌ FAIL: {$name}\n";
            echo "     {$e->getMessage()}\n";
        } catch (Exception $e) {
            $this->errors++;
            $this->results[] = ['name' => $name, 'status' => 'ERROR', 'message' => $e->getMessage()];
            echo "  💥 ERROR: {$name}\n";
            echo "     {$e->getMessage()}\n";
        }
    }
    
    /**
     * Print final report
     */
    private function report(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📊 Test Results:\n";
        echo "  ✅ Passed:  {$this->passed}\n";
        echo "  ❌ Failed:  {$this->failed}\n";
        echo "  💥 Errors:  {$this->errors}\n";
        echo "  📋 Total:   " . count($this->tests) . "\n";
        
        $total = count($this->tests);
        $successRate = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;
        
        echo "\n🎯 Success Rate: {$successRate}%\n";
        
        if ($this->failed === 0 && $this->errors === 0) {
            echo "\n🎉 All tests passed!\n";
            exit(0);
        } else {
            echo "\n⚠️  Some tests failed.\n";
            exit(1);
        }
    }
    
    // ==================== ASSERTION METHODS ====================
    
    /**
     * Assert equals
     */
    public function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $expStr = var_export($expected, true);
            $actStr = var_export($actual, true);
            throw new AssertionError($message ?: "Expected {$expStr}, got {$actStr}");
        }
    }
    
    /**
     * Assert true
     */
    public function assertTrue($condition, string $message = ''): void
    {
        if ($condition !== true) {
            throw new AssertionError($message ?: "Expected true, got false");
        }
    }
    
    /**
     * Assert false
     */
    public function assertFalse($condition, string $message = ''): void
    {
        if ($condition !== false) {
            throw new AssertionError($message ?: "Expected false, got true");
        }
    }
    
    /**
     * Assert not null
     */
    public function assertNotNull($value, string $message = ''): void
    {
        if ($value === null) {
            throw new AssertionError($message ?: "Expected non-null value");
        }
    }
    
    /**
     * Assert null
     */
    public function assertNull($value, string $message = ''): void
    {
        if ($value !== null) {
            throw new AssertionError($message ?: "Expected null, got " . var_export($value, true));
        }
    }
    
    /**
     * Assert contains
     */
    public function assertContains(string $needle, string $haystack, string $message = ''): void
    {
        if (strpos($haystack, $needle) === false) {
            throw new AssertionError($message ?: "String '{$needle}' not found in '{$haystack}'");
        }
    }
    
    /**
     * Assert array has key
     */
    public function assertArrayHasKey(string $key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new AssertionError($message ?: "Array does not have key '{$key}'");
        }
    }
    
    /**
     * Assert count
     */
    public function assertCount(int $expected, array $array, string $message = ''): void
    {
        $actual = count($array);
        if ($expected !== $actual) {
            throw new AssertionError($message ?: "Expected count {$expected}, got {$actual}");
        }
    }
    
    /**
     * Assert exception thrown
     */
    public function assertException(callable $callback, string $expectedClass = 'Exception', string $message = ''): void
    {
        try {
            $callback();
            throw new AssertionError($message ?: "Expected exception {$expectedClass} was not thrown");
        } catch (AssertionError $e) {
            throw $e;
        } catch (Exception $e) {
            if (!($e instanceof $expectedClass)) {
                throw new AssertionError($message ?: "Expected {$expectedClass}, got " . get_class($e));
            }
        }
    }
}

if (!class_exists('AssertionError')) {
    class AssertionError extends Exception {}
}
