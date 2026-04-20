#!/usr/bin/env php
<?php

/**
 * Run All Tests
 * 
 * Execute all test suites and generate a combined report.
 * Usage: php tests/run.php
 */

echo "🧪 Book Lending Manager - Test Suite\n";
echo str_repeat('=', 70) . "\n\n";

$tests = [
    'Configuration Tests' => 'ConfigTest.php',
    'Security Tests' => 'SecurityTest.php',
    'Unit Tests' => 'UnitTest.php',
    'Integration Tests' => 'IntegrationTest.php'
];

$totalPassed = 0;
$totalFailed = 0;
$totalErrors = 0;

foreach ($tests as $name => $file) {
    echo "📦 Running: {$name}\n";
    echo str_repeat('-', 70) . "\n";
    
    $testFile = __DIR__ . '/' . $file;
    
    if (!file_exists($testFile)) {
        echo "  ⚠️  Test file not found: {$file}\n\n";
        continue;
    }
    
    // Capture output
    ob_start();
    $exitCode = 0;
    
    try {
        // Run the test file
        passthru('php ' . escapeshellarg($testFile) . ' 2>&1', $exitCode);
    } catch (Exception $e) {
        echo "  💥 Fatal error: " . $e->getMessage() . "\n";
        $exitCode = 1;
    }
    
    $output = ob_get_clean();
    echo $output;
    
    // Parse results from output
    if (preg_match('/✅ Passed:\s+(\d+)/', $output, $matches)) {
        $totalPassed += (int)$matches[1];
    }
    if (preg_match('/❌ Failed:\s+(\d+)/', $output, $matches)) {
        $totalFailed += (int)$matches[1];
    }
    if (preg_match('/💥 Errors:\s+(\d+)/', $output, $matches)) {
        $totalErrors += (int)$matches[1];
    }
    
    echo "\n";
}

// Final summary
echo str_repeat('=', 70) . "\n";
echo "📊 FINAL SUMMARY\n";
echo str_repeat('=', 70) . "\n";
echo "  ✅ Total Passed:  {$totalPassed}\n";
echo "  ❌ Total Failed:  {$totalFailed}\n";
echo "  💥 Total Errors:  {$totalErrors}\n";

$grandTotal = $totalPassed + $totalFailed + $totalErrors;
if ($grandTotal > 0) {
    $successRate = round(($totalPassed / $grandTotal) * 100, 1);
    echo "\n🎯 Overall Success Rate: {$successRate}%\n";
}

if ($totalFailed === 0 && $totalErrors === 0) {
    echo "\n🎉 ALL TESTS PASSED! System is ready.\n";
    exit(0);
} else {
    echo "\n⚠️  Some tests failed. Review the output above.\n";
    exit(1);
}
