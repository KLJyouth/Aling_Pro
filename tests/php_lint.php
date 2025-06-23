<?php
/**
 * AlingAi Pro - PHP Linter
 * 
 * This script checks all PHP files in the project for syntax errors
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('BASE_DIR', dirname(__DIR__));
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

// Results tracking
$passedFiles = 0;
$totalFiles = 0;
$failedFiles = [];

/**
 * Print a message to the console
 * 
 * @param string $message The message to print
 * @param string $color The color to use
 */
function printMessage($message, $color = COLOR_RESET) {
    echo $color . $message . COLOR_RESET . "\n";
}

/**
 * Check if a file has PHP syntax errors
 * 
 * @param string $file The file to check
 * @return array Result with status and error message if any
 */
function checkSyntax($file) {
    $output = [];
    $returnCode = 0;
    
    // Use PHP to check syntax
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        return [
            'valid' => false,
            'error' => implode("\n", $output)
        ];
    }
    
    return [
        'valid' => true
    ];
}

/**
 * Recursively find all PHP files in a directory
 * 
 * @param string $dir The directory to search
 * @return array List of PHP files
 */
function findPhpFiles($dir) {
    $phpFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $phpFiles[] = $file->getPathname();
        }
    }
    
    return $phpFiles;
}

// Start the linting process
printMessage("\n" . COLOR_BLUE . '=== AlingAi Pro PHP Linter ===' . COLOR_RESET);
printMessage('Starting lint at: ' . date('Y-m-d H:i:s'));

// Find all PHP files
$directories = [
    BASE_DIR . '/public',
    BASE_DIR . '/src',
    BASE_DIR . '/config'
];

$phpFiles = [];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $phpFiles = array_merge($phpFiles, findPhpFiles($dir));
    }
}

$totalFiles = count($phpFiles);
printMessage('Found ' . $totalFiles . ' PHP files to check');

// Check each file
foreach ($phpFiles as $file) {
    $relativePath = str_replace(BASE_DIR . '/', '', $file);
    printMessage('Checking ' . $relativePath . '... ', COLOR_YELLOW);
    
    $result = checkSyntax($file);
    
    if ($result['valid']) {
        $passedFiles++;
        printMessage('  ' . COLOR_GREEN . ' PASSED' . COLOR_RESET);
    } else {
        $failedFiles[] = [
            'file' => $relativePath,
            'error' => $result['error']
        ];
        printMessage('  ' . COLOR_RED . ' FAILED' . COLOR_RESET);
        printMessage('    ' . $result['error'], COLOR_RED);
    }
}

// Print summary
printMessage("\n" . COLOR_BLUE . '=== Lint Summary ===' . COLOR_RESET);
printMessage('Total files: ' . $totalFiles);
printMessage('Passed files: ' . COLOR_GREEN . $passedFiles . COLOR_RESET);
printMessage('Failed files: ' . (count($failedFiles) > 0 ? COLOR_RED : COLOR_GREEN) . count($failedFiles) . COLOR_RESET);

// Print failed files if any
if (count($failedFiles) > 0) {
    printMessage("\n" . COLOR_RED . '=== Failed Files ===' . COLOR_RESET);
    foreach ($failedFiles as $index => $failure) {
        printMessage(($index + 1) . '. ' . $failure['file'] . ':', COLOR_RED);
        printMessage('   ' . $failure['error'], COLOR_RED);
    }
}

printMessage("\n" . COLOR_BLUE . '=== End of Lint ===' . COLOR_RESET);

// Generate a report file
$reportFile = BASE_DIR . '/tests/lint_report.md';
$report = '# PHP Linting Report' . "\n\n";
$report .= 'Generated on: ' . date('Y-m-d H:i:s') . "\n\n";
$report .= '## Summary' . "\n\n";
$report .= '- Total files checked: ' . $totalFiles . "\n";
$report .= '- Files with no syntax errors: ' . $passedFiles . "\n";
$report .= '- Files with syntax errors: ' . count($failedFiles) . "\n\n";

if (count($failedFiles) > 0) {
    $report .= '## Files with Syntax Errors' . "\n\n";
    foreach ($failedFiles as $index => $failure) {
        $report .= '### ' . ($index + 1) . '. ' . $failure['file'] . "\n\n";
        $report .= '```' . "\n" . $failure['error'] . "\n" . '```' . "\n\n";
    }
}

file_put_contents($reportFile, $report);
printMessage('Lint report saved to: tests/lint_report.md');

// Exit with error code if any files failed
exit(count($failedFiles) > 0 ? 1 : 0);
