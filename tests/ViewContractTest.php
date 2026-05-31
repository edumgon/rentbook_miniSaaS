<?php

/**
 * View ↔ Controller Contract Tests
 *
 * Static, DB-free guard against the class of regression where a Clean
 * Architecture controller stops producing an array key that a view still
 * consumes (e.g. dashboard.php using $loan['borrower_name'] while
 * DashboardController::loanToArray() forgot to resolve the borrower).
 *
 * For each (view, controller mapping) pair we extract:
 *   - the keys the view reads     ($loan['x'], $book['x'], $borrower['x'])
 *   - the keys the mapping emits   ('x' => ... inside its `return [ ... ];`)
 * and assert every consumed key is produced.
 */

require_once __DIR__ . '/bootstrap.php';

$runner = new TestRunner();

/** Extract distinct $var['key'] references used in a view file. */
function vc_view_keys(string $file, string $var): array
{
    if (!file_exists($file)) {
        throw new Exception("View file not found: {$file}");
    }
    $src = file_get_contents($file);
    preg_match_all('/\$' . preg_quote($var, '/') . "\\['([a-zA-Z_][a-zA-Z0-9_]*)'\\]/", $src, $m);
    return array_values(array_unique($m[1]));
}

/** Extract the keys produced inside a method's `return [ ... ];` block. */
function vc_produced_keys(string $file, string $method): array
{
    if (!file_exists($file)) {
        throw new Exception("Controller file not found: {$file}");
    }
    $src = file_get_contents($file);
    $methodPos = strpos($src, "function {$method}(");
    if ($methodPos === false) {
        throw new Exception("Method {$method}() not found in {$file}");
    }
    $retPos = strpos($src, 'return [', $methodPos);
    if ($retPos === false) {
        throw new Exception("No array return found in {$method}() of {$file}");
    }
    $endPos = strpos($src, '];', $retPos);
    $block = substr($src, $retPos, $endPos - $retPos);
    preg_match_all("/'([a-zA-Z_][a-zA-Z0-9_]*)'\\s*=>/", $block, $m);
    return array_values(array_unique($m[1]));
}

function vc_assert_contract($t, string $viewFile, string $var, string $ctrlFile, string $method): void
{
    $consumed = vc_view_keys($viewFile, $var);
    $produced = vc_produced_keys($ctrlFile, $method);
    foreach ($consumed as $key) {
        $t->assertTrue(
            in_array($key, $produced, true),
            sprintf(
                "%s reads \$%s['%s'] but %s::%s() does not produce it (produces: %s)",
                basename($viewFile),
                $var,
                $key,
                basename($ctrlFile, '.php'),
                $method,
                implode(', ', $produced)
            )
        );
    }
}

$IA = APP_PATH . '/InterfaceAdapter/Controller';
$V  = APP_PATH . '/views';

// ---- Loan mapping ----
$runner->test('Contract: dashboard.php loan keys <- DashboardController::loanToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/dashboard.php", 'loan', "$IA/DashboardController.php", 'loanToArray');
});

$runner->test('Contract: loans/list.php loan keys <- LoanController::loanToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/loans/list.php", 'loan', "$IA/LoanController.php", 'loanToArray');
});

$runner->test('Contract: loans/history.php loan keys <- LoanController::loanToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/loans/history.php", 'loan', "$IA/LoanController.php", 'loanToArray');
});

// ---- Book mapping ----
$runner->test('Contract: loans/history.php book keys <- LoanController::bookToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/loans/history.php", 'book', "$IA/LoanController.php", 'bookToArray');
});

$runner->test('Contract: loans/create.php book keys <- LoanController::bookToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/loans/create.php", 'book', "$IA/LoanController.php", 'bookToArray');
});

// ---- Borrower mapping ----
$runner->test('Contract: loans/create.php borrower keys <- LoanController::borrowerToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/loans/create.php", 'borrower', "$IA/LoanController.php", 'borrowerToArray');
});

$runner->test('Contract: borrowers/list.php borrower keys <- BorrowerController::borrowerToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/borrowers/list.php", 'borrower', "$IA/BorrowerController.php", 'borrowerToArray');
});

$runner->test('Contract: borrowers/edit.php borrower keys <- BorrowerController::borrowerToArray', function ($t) use ($IA, $V) {
    vc_assert_contract($t, "$V/borrowers/edit.php", 'borrower', "$IA/BorrowerController.php", 'borrowerToArray');
});

$runner->run();
