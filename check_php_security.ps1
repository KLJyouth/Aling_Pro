# PHP Security Vulnerability Scanner
# This script checks PHP files for common security vulnerabilities

# Configuration
$config = @{
    BackupDir = "backups\security_check_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss")
    ReportDir = "reports"
    PhpExtensions = @("php", "inc", "module")
    Directories = @(
        "ai-engines",
        "apps",
        "completed",
        "public",
        "admin",
        "includes",
        "src",
        "services",
        "core"
    )
    ExcludeDirs = @(
        "vendor",
        "node_modules",
        "backups"
    )
}

# Create report directory
if (-not (Test-Path -Path $config.ReportDir)) {
    New-Item -Path $config.ReportDir -ItemType Directory -Force | Out-Null
    Write-Host "Created report directory: $($config.ReportDir)" -ForegroundColor Green
}

# Statistics
$stats = @{
    FilesChecked = 0
    FilesWithVulnerabilities = 0
    TotalVulnerabilities = 0
    VulnerabilitiesByType = @{
        "SQL Injection" = 0
        "XSS" = 0
        "File Inclusion" = 0
        "Command Injection" = 0
        "Hardcoded Credentials" = 0
        "Insecure Functions" = 0
        "Unvalidated Redirects" = 0
    }
}

$vulnerableFiles = @()
$vulnerabilityDetails = @{}

# Find all PHP files
function Find-PhpFiles {
    $phpFiles = @()
    
    foreach ($dir in $config.Directories) {
        if (-not (Test-Path -Path $dir)) {
            continue
        }
        
        $allFiles = Get-ChildItem -Path $dir -Recurse -File
        
        foreach ($file in $allFiles) {
            # Check if directory should be excluded
            $pathToCheck = $file.FullName
            $excluded = $false
            foreach ($excludeDir in $config.ExcludeDirs) {
                if ($pathToCheck -match "\\$excludeDir\\") {
                    $excluded = $true
                    break
                }
            }
            
            if ($excluded) {
                continue
            }
            
            # Check file extensions
            $extension = $file.Extension.TrimStart(".").ToLower()
            
            if ($config.PhpExtensions -contains $extension) {
                $phpFiles += $file.FullName
            }
        }
    }
    
    Write-Host "Found $($phpFiles.Count) PHP files to check for security vulnerabilities." -ForegroundColor Cyan
    
    return $phpFiles
}

# Check for SQL Injection vulnerabilities
function Check-SqlInjection {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for direct use of user input in SQL queries
    $patterns = @(
        '\$_GET\[[\'"].*?[\'"]\].*?mysql_query',
        '\$_POST\[[\'"].*?[\'"]\].*?mysql_query',
        '\$_REQUEST\[[\'"].*?[\'"]\].*?mysql_query',
        '\$_GET\[[\'"].*?[\'"]\].*?mysqli_query',
        '\$_POST\[[\'"].*?[\'"]\].*?mysqli_query',
        '\$_REQUEST\[[\'"].*?[\'"]\].*?mysqli_query',
        '\$_GET\[[\'"].*?[\'"]\].*?->query',
        '\$_POST\[[\'"].*?[\'"]\].*?->query',
        '\$_REQUEST\[[\'"].*?[\'"]\].*?->query',
        'SELECT.*?FROM.*?\$_',
        'INSERT.*?INTO.*?\$_',
        'UPDATE.*?SET.*?\$_',
        'DELETE.*?FROM.*?\$_'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "SQL Injection vulnerability at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check for XSS vulnerabilities
function Check-XSS {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for direct output of user input without sanitization
    $patterns = @(
        'echo\s+\$_GET',
        'echo\s+\$_POST',
        'echo\s+\$_REQUEST',
        'echo\s+\$_COOKIE',
        'print\s+\$_GET',
        'print\s+\$_POST',
        'print\s+\$_REQUEST',
        'print\s+\$_COOKIE',
        '\$_GET.*?(?<!htmlspecialchars|htmlentities|strip_tags)',
        '\$_POST.*?(?<!htmlspecialchars|htmlentities|strip_tags)',
        '\$_REQUEST.*?(?<!htmlspecialchars|htmlentities|strip_tags)',
        '\$_COOKIE.*?(?<!htmlspecialchars|htmlentities|strip_tags)'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "XSS vulnerability at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check for File Inclusion vulnerabilities
function Check-FileInclusion {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for direct inclusion of user input
    $patterns = @(
        'include\s*\(\s*\$_GET',
        'include\s*\(\s*\$_POST',
        'include\s*\(\s*\$_REQUEST',
        'include_once\s*\(\s*\$_GET',
        'include_once\s*\(\s*\$_POST',
        'include_once\s*\(\s*\$_REQUEST',
        'require\s*\(\s*\$_GET',
        'require\s*\(\s*\$_POST',
        'require\s*\(\s*\$_REQUEST',
        'require_once\s*\(\s*\$_GET',
        'require_once\s*\(\s*\$_POST',
        'require_once\s*\(\s*\$_REQUEST'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "File Inclusion vulnerability at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check for Command Injection vulnerabilities
function Check-CommandInjection {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for direct execution of user input
    $patterns = @(
        'system\s*\(\s*\$_',
        'exec\s*\(\s*\$_',
        'passthru\s*\(\s*\$_',
        'shell_exec\s*\(\s*\$_',
        'popen\s*\(\s*\$_',
        'proc_open\s*\(\s*\$_',
        'eval\s*\(\s*\$_',
        '`\s*\$_'  # Backtick execution
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "Command Injection vulnerability at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check for Hardcoded Credentials
function Check-HardcodedCredentials {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for hardcoded credentials
    $patterns = @(
        'password\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'passwd\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'pwd\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'username\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'user\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'apikey\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'api_key\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'secret\s*=\s*[\'"](?!.*\$).*?[\'"]',
        'token\s*=\s*[\'"](?!.*\$).*?[\'"]'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "Hardcoded Credential at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check for Insecure Functions
function Check-InsecureFunctions {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for insecure functions
    $patterns = @(
        'md5\s*\(',
        'sha1\s*\(',
        'mt_rand\s*\(',
        'rand\s*\(',
        'srand\s*\(',
        'unserialize\s*\(\s*\$_'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "Insecure Function at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check for Unvalidated Redirects
function Check-UnvalidatedRedirects {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $vulnerabilities = @()
    
    # Check for unvalidated redirects
    $patterns = @(
        'header\s*\(\s*[\'"]Location:\s*[\'"].*?\$_',
        'header\s*\(\s*[\'"]Location:\s*\$_'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "Unvalidated Redirect at line $lineNumber: $($match.Value)"
        }
    }
    
    return $vulnerabilities
}

# Check a single file for vulnerabilities
function Check-PhpFileForVulnerabilities {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $fileVulnerabilities = @()
    
    # Run all vulnerability checks
    $sqlInjection = Check-SqlInjection -Content $content -FilePath $FilePath
    $xss = Check-XSS -Content $content -FilePath $FilePath
    $fileInclusion = Check-FileInclusion -Content $content -FilePath $FilePath
    $commandInjection = Check-CommandInjection -Content $content -FilePath $FilePath
    $hardcodedCredentials = Check-HardcodedCredentials -Content $content -FilePath $FilePath
    $insecureFunctions = Check-InsecureFunctions -Content $content -FilePath $FilePath
    $unvalidatedRedirects = Check-UnvalidatedRedirects -Content $content -FilePath $FilePath
    
    # Update statistics
    $stats.VulnerabilitiesByType["SQL Injection"] += $sqlInjection.Count
    $stats.VulnerabilitiesByType["XSS"] += $xss.Count
    $stats.VulnerabilitiesByType["File Inclusion"] += $fileInclusion.Count
    $stats.VulnerabilitiesByType["Command Injection"] += $commandInjection.Count
    $stats.VulnerabilitiesByType["Hardcoded Credentials"] += $hardcodedCredentials.Count
    $stats.VulnerabilitiesByType["Insecure Functions"] += $insecureFunctions.Count
    $stats.VulnerabilitiesByType["Unvalidated Redirects"] += $unvalidatedRedirects.Count
    
    # Combine all vulnerabilities
    $fileVulnerabilities += $sqlInjection
    $fileVulnerabilities += $xss
    $fileVulnerabilities += $fileInclusion
    $fileVulnerabilities += $commandInjection
    $fileVulnerabilities += $hardcodedCredentials
    $fileVulnerabilities += $insecureFunctions
    $fileVulnerabilities += $unvalidatedRedirects
    
    return $fileVulnerabilities
}

# Generate HTML report
function Generate-Report {
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("security_check_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    $html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Security Check Report</title>
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
        .vulnerability-files {
            margin-bottom: 20px;
        }
        .file-list {
            list-style-type: none;
            padding-left: 0;
        }
        .file-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .file-list li:last-child {
            border-bottom: none;
        }
        .vulnerability-details {
            background-color: #f8f9fa;
            border-left: 4px solid #F44336;
            padding: 15px;
            margin-top: 10px;
            font-family: monospace;
            white-space: pre-wrap;
            overflow-x: auto;
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
        .high {
            color: #F44336;
            font-weight: bold;
        }
        .medium {
            color: #FF9800;
            font-weight: bold;
        }
        .low {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP Security Vulnerability Check Report</h1>
        <p>Generated on: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>Summary</h2>
            <p>Total files checked: $($stats.FilesChecked)</p>
            <p>Files with vulnerabilities: $($stats.FilesWithVulnerabilities)</p>
            <p>Total vulnerabilities found: $($stats.TotalVulnerabilities)</p>
            
            <h3>Vulnerabilities by Type</h3>
            <table class="stats-table">
                <tr>
                    <th>Vulnerability Type</th>
                    <th>Count</th>
                    <th>Risk Level</th>
                </tr>
                <tr>
                    <td>SQL Injection</td>
                    <td>$($stats.VulnerabilitiesByType["SQL Injection"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>Cross-Site Scripting (XSS)</td>
                    <td>$($stats.VulnerabilitiesByType["XSS"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>File Inclusion</td>
                    <td>$($stats.VulnerabilitiesByType["File Inclusion"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>Command Injection</td>
                    <td>$($stats.VulnerabilitiesByType["Command Injection"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>Hardcoded Credentials</td>
                    <td>$($stats.VulnerabilitiesByType["Hardcoded Credentials"])</td>
                    <td class="medium">Medium</td>
                </tr>
                <tr>
                    <td>Insecure Functions</td>
                    <td>$($stats.VulnerabilitiesByType["Insecure Functions"])</td>
                    <td class="medium">Medium</td>
                </tr>
                <tr>
                    <td>Unvalidated Redirects</td>
                    <td>$($stats.VulnerabilitiesByType["Unvalidated Redirects"])</td>
                    <td class="medium">Medium</td>
                </tr>
            </table>
        </div>
"@
    
    # Vulnerable files
    if ($vulnerableFiles.Count -gt 0) {
        $html += @"
        <div class="vulnerability-files">
            <h2>Files with Vulnerabilities</h2>
            <ul class="file-list">
"@
        
        foreach ($file in $vulnerableFiles) {
            $html += @"
                <li>
                    $([System.Web.HttpUtility]::HtmlEncode($file))
"@
            
            if ($vulnerabilityDetails.ContainsKey($file)) {
                $html += @"
                    <div class="vulnerability-details">$([System.Web.HttpUtility]::HtmlEncode($vulnerabilityDetails[$file]))</div>
"@
            }
            
            $html += @"
                </li>
"@
        }
        
        $html += @"
            </ul>
        </div>
"@
    }
    
    $html += @"
        <div class="recommendations">
            <h2>Recommendations</h2>
            <ul>
                <li>Use prepared statements or parameterized queries to prevent SQL injection</li>
                <li>Always sanitize and validate user input before displaying it (use htmlspecialchars, htmlentities)</li>
                <li>Avoid including files based on user input</li>
                <li>Never use user input directly in system commands</li>
                <li>Store credentials in environment variables or a secure configuration file, not in the code</li>
                <li>Use secure alternatives to insecure functions (e.g., password_hash instead of md5/sha1)</li>
                <li>Validate all redirects and ensure they only go to allowed destinations</li>
            </ul>
        </div>
    </div>
</body>
</html>
"@
    
    Set-Content -Path $reportFile -Value $html
    Write-Host "Report generated: $reportFile" -ForegroundColor Green
}

# Main execution
Write-Host "Starting PHP Security Vulnerability Scanner..." -ForegroundColor Cyan

# Find PHP files
$phpFiles = Find-PhpFiles

# Check each file
$fileCount = 0
foreach ($file in $phpFiles) {
    $fileCount++
    $stats.FilesChecked++
    
    Write-Host "Checking file $fileCount/$($phpFiles.Count): $file" -NoNewline
    
    # Check for vulnerabilities
    $vulnerabilities = Check-PhpFileForVulnerabilities -FilePath $file
    
    if ($vulnerabilities.Count -gt 0) {
        # File has vulnerabilities
        $stats.FilesWithVulnerabilities++
        $stats.TotalVulnerabilities += $vulnerabilities.Count
        $vulnerableFiles += $file
        $vulnerabilityDetails[$file] = $vulnerabilities -join "`n"
        
        Write-Host " - $($vulnerabilities.Count) vulnerabilities found!" -ForegroundColor Red
    } else {
        Write-Host " - OK" -ForegroundColor Green
    }
}

# Generate report
Generate-Report

Write-Host "`nSecurity check completed!" -ForegroundColor Green
Write-Host "Files checked: $($stats.FilesChecked)" -ForegroundColor Cyan
Write-Host "Files with vulnerabilities: $($stats.FilesWithVulnerabilities)" -ForegroundColor $(if ($stats.FilesWithVulnerabilities -gt 0) { "Red" } else { "Green" })
Write-Host "Total vulnerabilities found: $($stats.TotalVulnerabilities)" -ForegroundColor $(if ($stats.TotalVulnerabilities -gt 0) { "Red" } else { "Green" })