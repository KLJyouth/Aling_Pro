<?php
/**
 * AlingAi Pro - Test Runner
 * 
 * This script runs all tests and generates a comprehensive report
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('TEST_START_TIME', microtime(true));
define('BASE_DIR', dirname(__DIR__));
define('COLOR_GREEN', '\033[32m');
define('COLOR_RED', '\033[31m');
define('COLOR_YELLOW', '\033[33m');
define('COLOR_BLUE', '\033[34m');
define('COLOR_RESET', '\033[0m');

/**
 * Print a message to the console
 * 
 * @param string \ The message to print
 * @param string \ The color to use
 */
function printMessage(\, \ = COLOR_RESET) {
    echo \ . \ . COLOR_RESET . '\n';
}

/**
 * Run a command and return the output
 * 
 * @param string \ The command to run
 * @return array The output and exit code
 */
function runCommand(\) {
    \ = [];
    \ = 0;
    
    exec(\ . ' 2>&1', \, \);
    
    return [
        'output' => \,
        'exit_code' => \
    ];
}

// Start the test runner
printMessage('\n' . COLOR_BLUE . '=== AlingAi Pro Test Runner ===' . COLOR_RESET);
printMessage('Starting tests at: ' . date('Y-m-d H:i:s'));

// Define tests to run
\ = [
    [
        'name' => 'PHP Syntax Check',
        'command' => 'php tests/php_lint.php',
        'description' => 'Checks all PHP files for syntax errors'
    ],
    [
        'name' => 'HTML Validation',
        'command' => 'php tests/html_validator.php',
        'description' => 'Checks all HTML files for issues'
    ],
    [
        'name' => 'API Tests',
        'command' => 'php tests/run_api_tests.php',
        'description' => 'Tests all API endpoints'
    ]
];

// Run each test
\ = [];

foreach (\ as \) {
    printMessage('\n' . COLOR_BLUE . 'Running test: ' . \['name'] . COLOR_RESET);
    printMessage('Description: ' . \['description']);
    
    \ = microtime(true);
    \ = runCommand(\['command']);
    \ = round(microtime(true) - \, 2);
    
    \ = \['exit_code'] === 0;
    
    \[] = [
        'name' => \['name'],
        'success' => \,
        'duration' => \,
        'output' => \['output'],
        'exit_code' => \['exit_code']
    ];
    
    if (\) {
        printMessage('  ' . COLOR_GREEN . ' PASSED' . COLOR_RESET . ' in {\}s');
    } else {
        printMessage('  ' . COLOR_RED . ' FAILED' . COLOR_RESET . ' in {\}s');
        printMessage('  Exit code: ' . \['exit_code']);
        
        // Print the last few lines of output
        \ = array_slice(\['output'], -5);
        foreach (\ as \) {
            printMessage('  ' . \);
        }
    }
}

// Print test summary
\ = count(\);
\ = count(array_filter(\, function(\) {
    return \['success'];
}));
\ = \ - \;
\ = round(microtime(true) - TEST_START_TIME, 2);

printMessage('\n' . COLOR_BLUE . '=== Test Summary ===' . COLOR_RESET);
printMessage('Total tests: \');
printMessage('Passed tests: ' . COLOR_GREEN . \ . COLOR_RESET);
printMessage('Failed tests: ' . (\ > 0 ? COLOR_RED : COLOR_GREEN) . \ . COLOR_RESET);
printMessage('Total duration: {\}s');

// Generate a comprehensive report
\ = BASE_DIR . '/tests/test_report.md';
\ = '# AlingAi Pro Test Report\n\n';
\ .= 'Generated on: ' . date('Y-m-d H:i:s') . '\n\n';
\ .= '## Summary\n\n';
\ .= '- Total tests: \\n';
\ .= '- Passed tests: \\n';
\ .= '- Failed tests: \\n';
\ .= '- Total duration: {\}s\n\n';

\ .= '## Test Results\n\n';
foreach (\ as \ => \) {
    \ = \['success'] ? '' : '';
    \ .= '### ' . (\ + 1) . '. ' . \['name'] . ' ' . \ . '\n\n';
    \ .= '- Duration: ' . \['duration'] . 's\n';
    \ .= '- Exit code: ' . \['exit_code'] . '\n\n';
    
    if (!\['success']) {
        \ .= '#### Output (last 20 lines)\n\n';
        \ .= '`\n';
        \ = array_slice(\['output'], -20);
        \ .= implode('\n', \);
        \ .= '\n`\n\n';
    }
}

\ .= '## Recommendations\n\n';

if (\ > 0) {
    \ .= 'Based on the test results, the following actions are recommended:\n\n';
    
    foreach (\ as \) {
        if (!\['success']) {
            if (\['name'] === 'PHP Syntax Check') {
                \ .= '- Fix PHP syntax errors identified in the lint report\n';
            } else if (\['name'] === 'HTML Validation') {
                \ .= '- Address HTML issues and broken links identified in the validation report\n';
            } else if (\['name'] === 'API Tests') {
                \ .= '- Fix API endpoints that are not functioning correctly\n';
            }
        }
    }
} else {
    \ .= 'All tests passed successfully! Here are some recommendations for continued improvement:\n\n';
    \ .= '- Implement additional unit tests for core functionality\n';
    \ .= '- Set up continuous integration to run these tests automatically\n';
    \ .= '- Consider adding performance benchmarking tests\n';
}

file_put_contents(\, \);
printMessage('Comprehensive test report saved to: tests/test_report.md');

printMessage('\n' . COLOR_BLUE . '=== End of Test Runner ===' . COLOR_RESET);

// Exit with error code if any tests failed
exit(\ > 0 ? 1 : 0);
