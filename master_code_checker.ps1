# Master Code Checker
# This script combines PHP syntax checking, PHP security scanning, and HTML validation

# Get the directory of this script
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# Check if required scripts exist
$phpSyntaxScript = Join-Path -Path $scriptDir -ChildPath "fix_all_php_issues.ps1"
$phpSecurityScript = Join-Path -Path $scriptDir -ChildPath "check_php_security.ps1"
$htmlCheckScript = Join-Path -Path $scriptDir -ChildPath "check_html_issues.ps1"

$missingScripts = @()
if (-not (Test-Path -Path $phpSyntaxScript)) {
    $missingScripts += "fix_all_php_issues.ps1"
}
if (-not (Test-Path -Path $phpSecurityScript)) {
    $missingScripts += "check_php_security.ps1"
}
if (-not (Test-Path -Path $htmlCheckScript)) {
    $missingScripts += "check_html_issues.ps1"
}

if ($missingScripts.Count -gt 0) {
    Write-Host "Error: The following required scripts are missing:" -ForegroundColor Red
    foreach ($script in $missingScripts) {
        Write-Host "  - $script" -ForegroundColor Red
    }
    Write-Host "Please make sure all required scripts are in the same directory as this script." -ForegroundColor Red
    exit 1
}

# Create reports directory
$reportsDir = Join-Path -Path $scriptDir -ChildPath "reports"
if (-not (Test-Path -Path $reportsDir)) {
    New-Item -Path $reportsDir -ItemType Directory -Force | Out-Null
    Write-Host "Created reports directory: $reportsDir" -ForegroundColor Green
}

# Create master report file
$masterReportFile = Join-Path -Path $reportsDir -ChildPath ("master_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")

# Function to run a script and capture its output
function Run-Script {
    param (
        [string]$ScriptPath,
        [string]$Description
    )
    
    Write-Host "`n=========================================" -ForegroundColor Cyan
    Write-Host "Running $Description..." -ForegroundColor Cyan
    Write-Host "=========================================" -ForegroundColor Cyan
    
    # Run the script and capture its output
    $output = & $ScriptPath
    
    # Return the output
    return $output
}

# Run all scripts
$phpSyntaxOutput = Run-Script -ScriptPath $phpSyntaxScript -Description "PHP Syntax Check"
$phpSecurityOutput = Run-Script -ScriptPath $phpSecurityScript -Description "PHP Security Check"
$htmlCheckOutput = Run-Script -ScriptPath $htmlCheckScript -Description "HTML Validation"

# Generate master report
$html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Code Check Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .summary {
            background-color: #f8f9fa;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .nav {
            background-color: #2c3e50;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        .nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .nav ul li {
            margin-right: 20px;
        }
        .nav ul li a {
            color: #fff;
            text-decoration: none;
        }
        .nav ul li a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-table th, .stats-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .stats-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="nav">
        <ul>
            <li><a href="#summary">Summary</a></li>
            <li><a href="#php-syntax">PHP Syntax</a></li>
            <li><a href="#php-security">PHP Security</a></li>
            <li><a href="#html-validation">HTML Validation</a></li>
        </ul>
    </div>
    
    <div class="container">
        <h1>Master Code Check Report</h1>
        <p>Generated on: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div id="summary" class="section summary">
            <h2>Summary</h2>
            <p>This report combines the results of PHP syntax checking, PHP security scanning, and HTML validation.</p>
            
            <h3>Individual Reports</h3>
            <ul>
                <li><a href="#php-syntax">PHP Syntax Check</a></li>
                <li><a href="#php-security">PHP Security Check</a></li>
                <li><a href="#html-validation">HTML Validation</a></li>
            </ul>
            
            <h3>Recommendations</h3>
            <ol>
                <li>Fix all PHP syntax errors first</li>
                <li>Address critical security vulnerabilities</li>
                <li>Fix HTML validation issues</li>
                <li>Run the checks again to ensure all issues are resolved</li>
            </ol>
        </div>
        
        <div id="php-syntax" class="section">
            <h2>PHP Syntax Check</h2>
            <p>Check for PHP syntax errors and fix common issues.</p>
            <a href="syntax_fix_report_latest.html" class="btn">View Full Report</a>
        </div>
        
        <div id="php-security" class="section">
            <h2>PHP Security Check</h2>
            <p>Scan for security vulnerabilities in PHP code.</p>
            <a href="security_check_report_latest.html" class="btn">View Full Report</a>
        </div>
        
        <div id="html-validation" class="section">
            <h2>HTML Validation</h2>
            <p>Check HTML files for syntax errors, accessibility issues, and best practices.</p>
            <a href="html_check_report_latest.html" class="btn">View Full Report</a>
        </div>
    </div>
</body>
</html>
"@

Set-Content -Path $masterReportFile -Value $html

# Create symbolic links to the latest reports
$latestPhpSyntaxReport = Get-ChildItem -Path $reportsDir -Filter "syntax_fix_report_*.html" | Sort-Object -Property LastWriteTime -Descending | Select-Object -First 1
$latestPhpSecurityReport = Get-ChildItem -Path $reportsDir -Filter "security_check_report_*.html" | Sort-Object -Property LastWriteTime -Descending | Select-Object -First 1
$latestHtmlReport = Get-ChildItem -Path $reportsDir -Filter "html_check_report_*.html" | Sort-Object -Property LastWriteTime -Descending | Select-Object -First 1

if ($latestPhpSyntaxReport) {
    Copy-Item -Path $latestPhpSyntaxReport.FullName -Destination (Join-Path -Path $reportsDir -ChildPath "syntax_fix_report_latest.html") -Force
}
if ($latestPhpSecurityReport) {
    Copy-Item -Path $latestPhpSecurityReport.FullName -Destination (Join-Path -Path $reportsDir -ChildPath "security_check_report_latest.html") -Force
}
if ($latestHtmlReport) {
    Copy-Item -Path $latestHtmlReport.FullName -Destination (Join-Path -Path $reportsDir -ChildPath "html_check_report_latest.html") -Force
}

Write-Host "`n=========================================" -ForegroundColor Green
Write-Host "Master Code Check Completed!" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green
Write-Host "Master report generated: $masterReportFile" -ForegroundColor Green