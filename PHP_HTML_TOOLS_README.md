# PHP and HTML Validation Tools

This collection of tools is designed to help you check, validate, and fix issues in PHP and HTML files. The tools provide comprehensive reports on syntax errors, security vulnerabilities, and best practices.

## Tools Included

1. **PHP Syntax Checker and Fixer** (ix_all_php_issues.ps1)
   - Checks PHP files for syntax errors
   - Automatically fixes common syntax issues
   - Generates detailed reports

2. **PHP Security Vulnerability Scanner** (check_php_security.ps1)
   - Scans PHP files for security vulnerabilities
   - Detects SQL injection, XSS, file inclusion, and more
   - Provides recommendations for fixing issues

3. **HTML Validator** (check_html_issues.ps1)
   - Validates HTML files for syntax errors
   - Checks for accessibility issues
   - Identifies SEO best practices
   - Detects responsive design issues

4. **Master Code Checker** (master_code_checker.ps1)
   - Runs all three tools in sequence
   - Generates a comprehensive master report
   - Links to individual detailed reports

## Requirements

- Windows with PowerShell 5.0 or later
- No PHP installation required (the tools perform static analysis)

## Usage

### Running Individual Tools

1. **PHP Syntax Checker and Fixer**:
   `
   .\fix_all_php_issues.ps1
   `

2. **PHP Security Vulnerability Scanner**:
   `
   .\check_php_security.ps1
   `

3. **HTML Validator**:
   `
   .\check_html_issues.ps1
   `

### Running All Tools Together

`
.\master_code_checker.ps1
`

## Reports

All reports are generated in the eports directory. The master report provides links to the individual detailed reports.

### Report Types

- **PHP Syntax Report**: Lists files with syntax errors and fixes applied
- **PHP Security Report**: Details security vulnerabilities found and recommendations
- **HTML Validation Report**: Shows HTML issues and best practice violations

## Customization

Each tool can be customized by modifying the configuration section at the top of the script:

- Change directories to scan
- Exclude specific directories
- Adjust file extensions to check
- Modify reporting options

## Troubleshooting

If you encounter any issues:

1. Make sure all scripts are in the same directory
2. Check that the directories specified in the configuration exist
3. Ensure you have sufficient permissions to read and write files

## Best Practices

1. Run the PHP syntax checker first to fix basic syntax issues
2. Address critical security vulnerabilities identified by the security scanner
3. Fix HTML validation issues to improve site quality and user experience
4. Run the tools regularly as part of your development workflow
