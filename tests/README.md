# AlingAi Pro Testing Suite

This directory contains various tests to ensure the AlingAi Pro application works correctly. The tests are designed to validate both the PHP backend and the HTML/JavaScript frontend.

## Test Types

1. **PHP Syntax Checking**: Validates all PHP files for syntax errors
2. **HTML Validation**: Checks HTML files for structure issues and broken links
3. **JavaScript Checking**: Analyzes JavaScript code for common issues and best practices
4. **API Tests**: Tests the API endpoints for correct functionality
5. **Unit Tests**: Tests individual components in isolation
6. **Integration Tests**: Tests how components work together

## Running the Tests

### All Tests

To run all tests at once, use the test runner script:

```bash
php tests/run_all_tests.php
```

This will execute all the tests and generate a comprehensive report in `tests/test_report.md`.

### Individual Tests

You can also run specific tests:

#### PHP Syntax Check

```bash
php tests/php_lint.php
```

This will check all PHP files for syntax errors and generate a report in `tests/lint_report.md`.

#### HTML Validation

```bash
php tests/html_validator.php
```

This will check all HTML files for issues and generate a report in `tests/html_validation_report.md`.

#### JavaScript Check

```bash
php tests/javascript_checker.php
```

This will check all JavaScript code for issues and generate a report in `tests/javascript_check_report.md`.

#### API Tests

```bash
php tests/run_api_tests.php
```

This will test all API endpoints and verify their functionality.

#### PHPUnit Tests

To run the PHPUnit tests, you need to have PHPUnit installed:

```bash
vendor/bin/phpunit
```

This will run all the unit and integration tests defined in the `tests/Unit` and `tests/Integration` directories.

## Test Reports

After running the tests, you can find the following reports:

- `tests/test_report.md`: Comprehensive report of all tests
- `tests/lint_report.md`: PHP syntax errors report
- `tests/html_validation_report.md`: HTML validation issues report
- `tests/javascript_check_report.md`: JavaScript issues report

## Continuous Integration

These tests are designed to be run in a CI/CD pipeline. You can add them to your GitHub Actions workflow or other CI system to automatically run tests on each commit or pull request.

## Adding New Tests

### Adding Unit Tests

1. Create a new PHP file in the `tests/Unit` directory
2. Extend the `PHPUnit\Framework\TestCase` class
3. Add test methods prefixed with `test`

Example:

```php
<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
```

### Adding Integration Tests

1. Create a new PHP file in the `tests/Integration` directory
2. Extend the `PHPUnit\Framework\TestCase` class
3. Add test methods prefixed with `test`

Integration tests should test how components work together, such as API endpoints or database interactions.

## Best Practices

1. **Write Tests First**: Follow Test-Driven Development (TDD) principles by writing tests before implementing features
2. **Keep Tests Small**: Each test should test one specific thing
3. **Use Descriptive Names**: Test method names should describe what they're testing
4. **Isolate Tests**: Tests should not depend on each other
5. **Mock External Dependencies**: Use mock objects to isolate tests from external systems
6. **Run Tests Regularly**: Run tests before committing code to catch issues early 