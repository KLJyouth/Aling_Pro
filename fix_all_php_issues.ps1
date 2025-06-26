# PHP and HTML Syntax Fixer
# This script checks and fixes common PHP and HTML syntax issues

# Configuration
$config = @{
    BackupDir = "backups\syntax_fix_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss")
    ReportDir = "reports"
    MaxFilesToDisplay = 50
    PhpExtensions = @("php", "inc", "module")
    HtmlExtensions = @("html", "htm")
    Directories = @(
        "ai-engines",
        "apps",
        "completed",
        "public",
        "admin",
        "includes",
        "src"
        "services",
        "core"
    )
    ExcludeDirs = @(
        "vendor",
        "node_modules",
        "backups"
    )
}

# Create backup directory
if (-not (Test-Path -Path $config.BackupDir)) {
    New-Item -Path $config.BackupDir -ItemType Directory -Force | Out-Null
    Write-Host "Created backup directory: $($config.BackupDir)" -ForegroundColor Green
}

# Create report directory
if (-not (Test-Path -Path $config.ReportDir)) {
    New-Item -Path $config.ReportDir -ItemType Directory -Force | Out-Null
    Write-Host "Created report directory: $($config.ReportDir)" -ForegroundColor Green
}

# Statistics
$stats = @{
    PhpTotal = 0
    PhpChecked = 0
    PhpErrors = 0
    PhpFixed = 0
    HtmlTotal = 0
    HtmlChecked = 0
    HtmlErrors = 0
    HtmlFixed = 0
}

$errorFiles = @()
$fixedFiles = @()
$errorDetails = @{}

# 设置控制台编码为UTF-8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8
chcp 65001 | Out-Null

# Find all PHP and HTML files
function Find-Files {
    $phpFiles = @()
    $htmlFiles = @()
    
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
            } elseif ($config.HtmlExtensions -contains $extension) {
                $htmlFiles += $file.FullName
            }
        }
    }
    
    $stats.PhpTotal = $phpFiles.Count
    $stats.HtmlTotal = $htmlFiles.Count
    
    Write-Host "Found $($stats.PhpTotal) PHP files and $($stats.HtmlTotal) HTML files to process." -ForegroundColor Cyan
    
    return @{
        PhpFiles = $phpFiles
        HtmlFiles = $htmlFiles
    }
}

# Backup a file before fixing
function Backup-File {
    param (
        [string]$FilePath
    )
    
    if (Test-Path -Path $FilePath) {
        $relativePath = $FilePath
        $backupPath = Join-Path -Path $config.BackupDir -ChildPath $relativePath
        
        # Create directory structure
        $backupDir = Split-Path -Path $backupPath -Parent
        if (-not (Test-Path -Path $backupDir)) {
            New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
        }
        
        # Copy the file
        Copy-Item -Path $FilePath -Destination $backupPath -Force
        return $true
    }
    
    return $false
}

# Check PHP syntax (without PHP installed)
function Test-PhpSyntax {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $errors = @()
    
    # Check for common PHP syntax errors
    
    # 1. Check for mismatched brackets
    $openBraces = ($content | Select-String -Pattern "{" -AllMatches).Matches.Count
    $closeBraces = ($content | Select-String -Pattern "}" -AllMatches).Matches.Count
    if ($openBraces -ne $closeBraces) {
        $errors += "Mismatched curly braces: $openBraces opening vs $closeBraces closing"
    }
    
    $openParens = ($content | Select-String -Pattern "\(" -AllMatches).Matches.Count
    $closeParens = ($content | Select-String -Pattern "\)" -AllMatches).Matches.Count
    if ($openParens -ne $closeParens) {
        $errors += "Mismatched parentheses: $openParens opening vs $closeParens closing"
    }
    
    $openBrackets = ($content | Select-String -Pattern "\[" -AllMatches).Matches.Count
    $closeBrackets = ($content | Select-String -Pattern "\]" -AllMatches).Matches.Count
    if ($openBrackets -ne $closeBrackets) {
        $errors += "Mismatched square brackets: $openBrackets opening vs $closeBrackets closing"
    }
    
    # 2. Check for missing semicolons
    if ($content -match "(?<!\;|\{|\})\s*\n\s*\$") {
        $errors += "Possible missing semicolons detected"
    }
    
    # 3. Check for string issues
    $singleQuotes = ($content | Select-String -Pattern "'" -AllMatches).Matches.Count
    if ($singleQuotes % 2 -ne 0) {
        $errors += "Odd number of single quotes: $singleQuotes"
    }
    
    $doubleQuotes = ($content | Select-String -Pattern '"' -AllMatches).Matches.Count
    if ($doubleQuotes % 2 -ne 0) {
        $errors += "Odd number of double quotes: $doubleQuotes"
    }
    
    # 4. Check for common PHP syntax patterns
    if ($content -match "array\s*\(\s*\)\s*\{") {
        $errors += "Invalid array syntax: array() {"
    }
    
    if ($content -match "\]\s*\(") {
        $errors += "Invalid syntax: ]("
    }
    
    if ($content -match "\)\s*\[") {
        $errors += "Invalid syntax: )["
    }
    
    # 5. Check for PHP opening/closing tags
    if (-not ($content -match "<\?php")) {
        $errors += "Missing PHP opening tag <?php"
    }
    
    # Return errors
    return $errors
}

# Fix PHP syntax errors
function Fix-PhpSyntaxErrors {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $originalContent = $content
    
    # Apply fixes for common PHP syntax errors
    
    # 1. Fix array syntax
    $content = $content -replace 'array\s*\(', '['
    $content = $content -replace '\)(?=\s*[;,])', ']'
    
    # 2. Fix string concatenation
    $content = $content -replace '"(.*?)"\s*\.\s*"(.*?)"', '"$1$2"'
    
    # 3. Fix spacing around operators
    $content = $content -replace '\s+->(?=\w)', '->'
    $content = $content -replace '\(\s+', '('
    $content = $content -replace '\s+\)', ')'
    
    # 4. Fix quotes in array keys
    $content = $content -replace "\[\'(.*?)\'\]", '["$1"]'
    
    # 5. Fix class property declarations
    $content = $content -replace ':(\s*)protected(\s*)string(\s*)\$', 'protected string $'
    $content = $content -replace ':(\s*)private(\s*)string(\s*)\$', 'private string $'
    $content = $content -replace ':(\s*)public(\s*)string(\s*)\$', 'public string $'
    
    # 6. Fix regex patterns
    $content = $content -replace '"\]\+\$\/"', '"]+$/"'
    $content = $content -replace '"\]\+\$\/u"', '"]+$/u"'
    
    # 7. Fix string casting
    $content = $content -replace '\(string\s+\)', '(string)'
    
    # 8. Fix semicolons
    $content = $content -replace ';\s+\}', ';}'
    
    # 9. Fix closing brackets
    $content = $content -replace '\)\s*\{', ') {'
    
    # 10. Fix double quotes
    $content = $content -replace '""', '"'
    
    # 11. Fix PHP opening tag if missing
    if (-not ($content -match "<\?php")) {
        $content = "<?php`n" + $content
    }
    
    # Check if content has changed
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content
        return $true
    }
    
    return $false
}

# Basic HTML validation
function Test-HtmlSyntax {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $errors = @()
    
    # Check for unclosed tags
    $tags = @('div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'input', 'select', 'option')
    
    foreach ($tag in $tags) {
        $openCount = ([regex]::Matches($content, "<$tag(\s|>)")).Count
        $closeCount = ([regex]::Matches($content, "</$tag>")).Count
        
        if ($openCount -gt $closeCount) {
            $errors += "Unclosed <$tag> tag found ($openCount open, $closeCount closed)"
        }
    }
    
    # Check for malformed attributes
    $malformedAttrs = [regex]::Matches($content, '(\w+)=([^\s"\'>]+)(?=\s|>)')
    if ($malformedAttrs.Count -gt 0) {
        foreach ($match in $malformedAttrs) {
            $errors += "Unquoted attribute found: $($match.Value)"
        }
    }
    
    # Check for invalid nesting
    if ($content -match "<(p|div)>.*?<(table|ul|ol)>.*?</\1>.*?</\2>") {
        $errors += "Invalid nesting of block elements detected"
    }
    
    return $errors
}

# Fix HTML errors
function Fix-HtmlSyntaxErrors {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $originalContent = $content
    
    # 1. Fix unquoted attributes
    $content = [regex]::Replace($content, '(\w+)=([^\s"\'>]+)(?=\s|>)', '$1="$2"')
    
    # 2. Fix unclosed tags (basic approach)
    $tags = @('div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'select')
    
    foreach ($tag in $tags) {
        $openCount = ([regex]::Matches($content, "<$tag(\s|>)")).Count
        $closeCount = ([regex]::Matches($content, "</$tag>")).Count
        
        # Add missing closing tags at the end
        if ($openCount -gt $closeCount) {
            $diff = $openCount - $closeCount
            $content = $content + (("</$tag>" * $diff) -join "")
        }
    }
    
    # Check if content has changed
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content
        return $true
    }
    
    return $false
}

# Process PHP files
function Process-PhpFiles {
    $files = Find-Files
    $phpFiles = $files.PhpFiles
    
    Write-Host "`nChecking PHP syntax..." -ForegroundColor Cyan
    
    foreach ($file in $phpFiles) {
        $stats.PhpChecked++
        
        Write-Host "Checking file $($stats.PhpChecked)/$($stats.PhpTotal): $file" -NoNewline
        
        # Check syntax
        $errors = Test-PhpSyntax -FilePath $file
        
        if ($errors.Count -gt 0) {
            # Syntax error found
            $stats.PhpErrors++
            $errorFiles += $file
            $errorDetails[$file] = $errors -join "`n"
            
            Write-Host " - Error found!" -ForegroundColor Red
            
            # Backup file
            Backup-File -FilePath $file
            
            # Try to fix the file
            if (Fix-PhpSyntaxErrors -FilePath $file) {
                $stats.PhpFixed++
                $fixedFiles += $file
                Write-Host "  - Fixed!" -ForegroundColor Green
            } else {
                Write-Host "  - Could not fix automatically." -ForegroundColor Yellow
            }
        } else {
            Write-Host " - OK" -ForegroundColor Green
        }
    }
    
    Write-Host "`nPHP syntax check completed." -ForegroundColor Cyan
    Write-Host "Files with errors: $($stats.PhpErrors)" -ForegroundColor $(if ($stats.PhpErrors -gt 0) { "Red" } else { "Green" })
    Write-Host "Files fixed: $($stats.PhpFixed)" -ForegroundColor Green
}

# Process HTML files
function Process-HtmlFiles {
    $files = Find-Files
    $htmlFiles = $files.HtmlFiles
    
    Write-Host "`nChecking HTML files..." -ForegroundColor Cyan
    
    foreach ($file in $htmlFiles) {
        $stats.HtmlChecked++
        
        Write-Host "Checking file $($stats.HtmlChecked)/$($stats.HtmlTotal): $file" -NoNewline
        
        # Check syntax
        $errors = Test-HtmlSyntax -FilePath $file
        
        if ($errors.Count -gt 0) {
            # HTML errors found
            $stats.HtmlErrors++
            $errorFiles += $file
            $errorDetails[$file] = $errors -join "`n"
            
            Write-Host " - Error found!" -ForegroundColor Red
            
            # Backup file
            Backup-File -FilePath $file
            
            # Try to fix the file
            if (Fix-HtmlSyntaxErrors -FilePath $file) {
                $stats.HtmlFixed++
                $fixedFiles += $file
                Write-Host "  - Fixed!" -ForegroundColor Green
            } else {
                Write-Host "  - Could not fix automatically." -ForegroundColor Yellow
            }
        } else {
            Write-Host " - OK" -ForegroundColor Green
        }
    }
    
    Write-Host "`nHTML check completed." -ForegroundColor Cyan
    Write-Host "Files with errors: $($stats.HtmlErrors)" -ForegroundColor $(if ($stats.HtmlErrors -gt 0) { "Red" } else { "Green" })
    Write-Host "Files fixed: $($stats.HtmlFixed)" -ForegroundColor Green
}

# Generate HTML report
function Generate-Report {
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("syntax_fix_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    $html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syntax Fix Report</title>
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
        .error-files {
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
        .fixed {
            color: #4CAF50;
        }
        .not-fixed {
            color: #F44336;
        }
        .error-details {
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Syntax Fix Report</h1>
        <p>Generated on: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>Summary</h2>
            <table class="stats-table">
                <tr>
                    <th>Type</th>
                    <th>Total Files</th>
                    <th>Checked</th>
                    <th>With Errors</th>
                    <th>Fixed</th>
                </tr>
                <tr>
                    <td>PHP</td>
                    <td>$($stats.PhpTotal)</td>
                    <td>$($stats.PhpChecked)</td>
                    <td>$($stats.PhpErrors)</td>
                    <td>$($stats.PhpFixed)</td>
                </tr>
                <tr>
                    <td>HTML</td>
                    <td>$($stats.HtmlTotal)</td>
                    <td>$($stats.HtmlChecked)</td>
                    <td>$($stats.HtmlErrors)</td>
                    <td>$($stats.HtmlFixed)</td>
                </tr>
            </table>
        </div>
"@
    
    # Files with errors
    if ($errorFiles.Count -gt 0) {
        $html += @"
        <div class="error-files">
            <h2>Files with Errors</h2>
            <ul class="file-list">
"@
        
        $displayCount = 0
        foreach ($file in $errorFiles) {
            $displayCount++
            if ($displayCount -gt $config.MaxFilesToDisplay) {
                $html += "<li>... and $($errorFiles.Count - $config.MaxFilesToDisplay) more files</li>"
                break
            }
            
            $fixed = $fixedFiles -contains $file
            $statusClass = if ($fixed) { "fixed" } else { "not-fixed" }
            $statusText = if ($fixed) { "Fixed" } else { "Not Fixed" }
            
            $html += @"
                <li>
                    $([System.Web.HttpUtility]::HtmlEncode($file)) - <span class="$statusClass">$statusText</span>
"@
            
            if ($errorDetails.ContainsKey($file)) {
                $html += @"
                    <div class="error-details">$([System.Web.HttpUtility]::HtmlEncode($errorDetails[$file]))</div>
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
    
    # Fixed files
    if ($fixedFiles.Count -gt 0) {
        $html += @"
        <div class="fixed-files">
            <h2>Fixed Files</h2>
            <ul class="file-list">
"@
        
        $displayCount = 0
        foreach ($file in $fixedFiles) {
            $displayCount++
            if ($displayCount -gt $config.MaxFilesToDisplay) {
                $html += "<li>... and $($fixedFiles.Count - $config.MaxFilesToDisplay) more files</li>"
                break
            }
            
            $html += @"
                <li>$([System.Web.HttpUtility]::HtmlEncode($file))</li>
"@
        }
        
        $html += @"
            </ul>
        </div>
"@
    }
    
    $html += @"
    </div>
</body>
</html>
"@
    
    Set-Content -Path $reportFile -Value $html
    Write-Host "Report generated: $reportFile" -ForegroundColor Green
}

# Main execution
Write-Host "Starting PHP and HTML Syntax Fixer..." -ForegroundColor Cyan

# Process files
Process-PhpFiles
Process-HtmlFiles

# Generate report
Generate-Report

Write-Host "`nSyntax fixing completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $($config.BackupDir)" -ForegroundColor Green

# 创建一个函数来重新编码文件
function Convert-FileEncoding {
    param (
        [string]$Path,
        [string]$Encoding = "utf8"
    )
    
    $content = Get-Content -Path $Path -Raw
    Set-Content -Path $Path -Value $content -Encoding $Encoding -Force
    Write-Host "已将文件 $Path 转换为 $Encoding 编码" -ForegroundColor Green
}

# 转换所有PowerShell脚本文件
Get-ChildItem -Path . -Filter "*.ps1" | ForEach-Object {
    Convert-FileEncoding -Path $_.FullName -Encoding utf8BOM
}