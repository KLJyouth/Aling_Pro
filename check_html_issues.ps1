# HTML Validator and Issue Checker
# This script checks HTML files for common issues and validates them

# Configuration
$config = @{
    BackupDir = "backups\html_check_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss")
    ReportDir = "reports"
    HtmlExtensions = @("html", "htm")
    Directories = @(
        "public",
        "admin",
        "docs-website",
        "admin-center"
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
    FilesWithIssues = 0
    TotalIssues = 0
    IssuesByType = @{
        "Unclosed Tags" = 0
        "Unquoted Attributes" = 0
        "Invalid Nesting" = 0
        "Broken Links" = 0
        "Accessibility Issues" = 0
        "SEO Issues" = 0
        "Responsive Design Issues" = 0
    }
}

$issueFiles = @()
$issueDetails = @{}

# Find all HTML files
function Find-HtmlFiles {
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
            
            if ($config.HtmlExtensions -contains $extension) {
                $htmlFiles += $file.FullName
            }
        }
    }
    
    Write-Host "Found $($htmlFiles.Count) HTML files to check for issues." -ForegroundColor Cyan
    
    return $htmlFiles
}

# Check for unclosed tags
function Check-UnclosedTags {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Check for unclosed tags
    $tags = @('div', 'span', 'p', 'a', 'table', 'tr', 'td', 'th', 'ul', 'ol', 'li', 'form', 'input', 'select', 'option', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'section', 'article', 'nav', 'header', 'footer', 'aside', 'main')
    
    foreach ($tag in $tags) {
        $openCount = ([regex]::Matches($Content, "<$tag(\s|>)")).Count
        $closeCount = ([regex]::Matches($Content, "</$tag>")).Count
        
        if ($openCount -gt $closeCount) {
            $diff = $openCount - $closeCount
            $issues += "Unclosed <$tag> tag found ($openCount open, $closeCount closed, $diff missing)"
            $stats.IssuesByType["Unclosed Tags"]++
        }
    }
    
    return $issues
}

# Check for unquoted attributes
function Check-UnquotedAttributes {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Check for unquoted attributes
    $matches = [regex]::Matches($Content, '(\w+)=([^\s"\'>]+)(?=\s|>)')
    
    if ($matches.Count -gt 0) {
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $issues += "Unquoted attribute at line $lineNumber: $($match.Value)"
            $stats.IssuesByType["Unquoted Attributes"]++
        }
    }
    
    return $issues
}

# Check for invalid nesting
function Check-InvalidNesting {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Check for invalid nesting patterns
    $invalidPatterns = @(
        # Block elements inside inline elements
        '<(span|a|b|i|em|strong).*?>.*?<(div|p|h[1-6]|ul|ol|table).*?>.*?</\2>.*?</\1>',
        # Table structure issues
        '<table.*?>(?!.*?<tbody|thead|tfoot).*?<tr',
        '<tr.*?>(?!.*?<td|th).*?</tr>',
        # List structure issues
        '<(ul|ol).*?>(?!.*?<li).*?</(ul|ol)>',
        # Form issues
        '<select.*?>(?!.*?<option).*?</select>'
    )
    
    foreach ($pattern in $invalidPatterns) {
        $matches = [regex]::Matches($Content, $pattern)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            $issues += "Invalid nesting at line $lineNumber"
            $stats.IssuesByType["Invalid Nesting"]++
        }
    }
    
    return $issues
}

# Check for broken links
function Check-BrokenLinks {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Extract all href and src attributes
    $hrefMatches = [regex]::Matches($Content, 'href\s*=\s*[\'"]([^\'"]+)[\'"]')
    $srcMatches = [regex]::Matches($Content, 'src\s*=\s*[\'"]([^\'"]+)[\'"]')
    
    $allLinks = @()
    foreach ($match in $hrefMatches) {
        $allLinks += $match.Groups[1].Value
    }
    foreach ($match in $srcMatches) {
        $allLinks += $match.Groups[1].Value
    }
    
    # Check each link
    foreach ($link in $allLinks) {
        # Skip external links, javascript, and anchors
        if ($link -match '^(http|https|ftp|mailto|tel|javascript|#)') {
            continue
        }
        
        # Check if the link is valid
        $basePath = Split-Path -Parent $FilePath
        $fullPath = Join-Path -Path $basePath -ChildPath $link
        
        if (-not (Test-Path -Path $fullPath)) {
            $lineNumber = 0
            foreach ($match in $hrefMatches) {
                if ($match.Groups[1].Value -eq $link) {
                    $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
                    break
                }
            }
            if ($lineNumber -eq 0) {
                foreach ($match in $srcMatches) {
                    if ($match.Groups[1].Value -eq $link) {
                        $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
                        break
                    }
                }
            }
            
            $issues += "Broken link at line $lineNumber: $link"
            $stats.IssuesByType["Broken Links"]++
        }
    }
    
    return $issues
}

# Check for accessibility issues
function Check-AccessibilityIssues {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Check for common accessibility issues
    $accessibilityPatterns = @(
        # Missing alt attribute on images
        '<img(?!.*?alt=)[^>]*>',
        # Empty alt attribute
        '<img.*?alt\s*=\s*[\'"][\s\'"].*?>',
        # Missing form labels
        '<input(?!.*?aria-label|.*?aria-labelledby|.*?id="[^"]*")[^>]*>',
        # Missing language attribute on html tag
        '<!DOCTYPE.*?>\s*<html(?!.*?lang=)[^>]*>',
        # Missing title tag
        '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<title>).)*<body'
    )
    
    foreach ($pattern in $accessibilityPatterns) {
        $matches = [regex]::Matches($Content, $pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            
            if ($pattern -eq '<img(?!.*?alt=)[^>]*>') {
                $issues += "Accessibility issue at line $lineNumber: Image missing alt attribute"
            } elseif ($pattern -eq '<img.*?alt\s*=\s*[\'"][\s\'"].*?>') {
                $issues += "Accessibility issue at line $lineNumber: Image with empty alt attribute"
            } elseif ($pattern -eq '<input(?!.*?aria-label|.*?aria-labelledby|.*?id="[^"]*")[^>]*>') {
                $issues += "Accessibility issue at line $lineNumber: Form input without proper labeling"
            } elseif ($pattern -eq '<!DOCTYPE.*?>\s*<html(?!.*?lang=)[^>]*>') {
                $issues += "Accessibility issue: HTML tag missing lang attribute"
            } elseif ($pattern -eq '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<title>).)*<body') {
                $issues += "Accessibility issue: Missing title tag"
            }
            
            $stats.IssuesByType["Accessibility Issues"]++
        }
    }
    
    return $issues
}

# Check for SEO issues
function Check-SeoIssues {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Check for common SEO issues
    $seoPatterns = @(
        # Missing title
        '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<title>).)*<body',
        # Missing meta description
        '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<meta\s+name\s*=\s*[\'"]description[\'"]).).*<body',
        # Missing h1 tag
        '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<h1).)*</html>',
        # Multiple h1 tags
        '<h1.*?>.*?</h1>.*?<h1.*?>.*?</h1>'
    )
    
    foreach ($pattern in $seoPatterns) {
        $matches = [regex]::Matches($Content, $pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
        foreach ($match in $matches) {
            if ($pattern -eq '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<title>).)*<body') {
                $issues += "SEO issue: Missing title tag"
            } elseif ($pattern -eq '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<meta\s+name\s*=\s*[\'"]description[\'"]).).*<body') {
                $issues += "SEO issue: Missing meta description"
            } elseif ($pattern -eq '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<h1).)*</html>') {
                $issues += "SEO issue: Missing h1 tag"
            } elseif ($pattern -eq '<h1.*?>.*?</h1>.*?<h1.*?>.*?</h1>') {
                $issues += "SEO issue: Multiple h1 tags"
            }
            
            $stats.IssuesByType["SEO Issues"]++
        }
    }
    
    return $issues
}

# Check for responsive design issues
function Check-ResponsiveDesignIssues {
    param (
        [string]$Content,
        [string]$FilePath
    )
    
    $issues = @()
    
    # Check for common responsive design issues
    $responsivePatterns = @(
        # Missing viewport meta tag
        '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<meta\s+name\s*=\s*[\'"]viewport[\'"]).).*<body',
        # Fixed width elements
        'width\s*:\s*\d+px',
        'width\s*=\s*[\'"](\d+)(?!%)[\'"]',
        # Non-responsive tables
        '<table(?!.*?class\s*=\s*[\'"].*?responsive.*?[\'"])[^>]*>'
    )
    
    foreach ($pattern in $responsivePatterns) {
        $matches = [regex]::Matches($Content, $pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
        foreach ($match in $matches) {
            $lineNumber = ($Content.Substring(0, $match.Index).Split("`n")).Count
            
            if ($pattern -eq '<!DOCTYPE.*?>\s*<html.*?>(?:(?!<meta\s+name\s*=\s*[\'"]viewport[\'"]).).*<body') {
                $issues += "Responsive design issue: Missing viewport meta tag"
            } elseif ($pattern -eq 'width\s*:\s*\d+px') {
                $issues += "Responsive design issue at line $lineNumber: Fixed width in CSS"
            } elseif ($pattern -eq 'width\s*=\s*[\'"](\d+)(?!%)[\'"]') {
                $issues += "Responsive design issue at line $lineNumber: Fixed width attribute"
            } elseif ($pattern -eq '<table(?!.*?class\s*=\s*[\'"].*?responsive.*?[\'"])[^>]*>') {
                $issues += "Responsive design issue at line $lineNumber: Non-responsive table"
            }
            
            $stats.IssuesByType["Responsive Design Issues"]++
        }
    }
    
    return $issues
}

# Check a single file for issues
function Check-HtmlFileForIssues {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $fileIssues = @()
    
    # Run all issue checks
    $unclosedTags = Check-UnclosedTags -Content $content -FilePath $FilePath
    $unquotedAttributes = Check-UnquotedAttributes -Content $content -FilePath $FilePath
    $invalidNesting = Check-InvalidNesting -Content $content -FilePath $FilePath
    $brokenLinks = Check-BrokenLinks -Content $content -FilePath $FilePath
    $accessibilityIssues = Check-AccessibilityIssues -Content $content -FilePath $FilePath
    $seoIssues = Check-SeoIssues -Content $content -FilePath $FilePath
    $responsiveDesignIssues = Check-ResponsiveDesignIssues -Content $content -FilePath $FilePath
    
    # Combine all issues
    $fileIssues += $unclosedTags
    $fileIssues += $unquotedAttributes
    $fileIssues += $invalidNesting
    $fileIssues += $brokenLinks
    $fileIssues += $accessibilityIssues
    $fileIssues += $seoIssues
    $fileIssues += $responsiveDesignIssues
    
    return $fileIssues
}

# Generate HTML report
function Generate-Report {
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("html_check_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    $html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML Check Report</title>
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
        .issue-files {
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
        .issue-details {
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
        <h1>HTML Check Report</h1>
        <p>Generated on: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>Summary</h2>
            <p>Total files checked: $($stats.FilesChecked)</p>
            <p>Files with issues: $($stats.FilesWithIssues)</p>
            <p>Total issues found: $($stats.TotalIssues)</p>
            
            <h3>Issues by Type</h3>
            <table class="stats-table">
                <tr>
                    <th>Issue Type</th>
                    <th>Count</th>
                    <th>Severity</th>
                </tr>
                <tr>
                    <td>Unclosed Tags</td>
                    <td>$($stats.IssuesByType["Unclosed Tags"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>Unquoted Attributes</td>
                    <td>$($stats.IssuesByType["Unquoted Attributes"])</td>
                    <td class="medium">Medium</td>
                </tr>
                <tr>
                    <td>Invalid Nesting</td>
                    <td>$($stats.IssuesByType["Invalid Nesting"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>Broken Links</td>
                    <td>$($stats.IssuesByType["Broken Links"])</td>
                    <td class="high">High</td>
                </tr>
                <tr>
                    <td>Accessibility Issues</td>
                    <td>$($stats.IssuesByType["Accessibility Issues"])</td>
                    <td class="medium">Medium</td>
                </tr>
                <tr>
                    <td>SEO Issues</td>
                    <td>$($stats.IssuesByType["SEO Issues"])</td>
                    <td class="low">Low</td>
                </tr>
                <tr>
                    <td>Responsive Design Issues</td>
                    <td>$($stats.IssuesByType["Responsive Design Issues"])</td>
                    <td class="medium">Medium</td>
                </tr>
            </table>
        </div>
"@
    
    # Files with issues
    if ($issueFiles.Count -gt 0) {
        $html += @"
        <div class="issue-files">
            <h2>Files with Issues</h2>
            <ul class="file-list">
"@
        
        foreach ($file in $issueFiles) {
            $html += @"
                <li>
                    $([System.Web.HttpUtility]::HtmlEncode($file))
"@
            
            if ($issueDetails.ContainsKey($file)) {
                $html += @"
                    <div class="issue-details">$([System.Web.HttpUtility]::HtmlEncode($issueDetails[$file]))</div>
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
                <li>Ensure all HTML tags are properly closed</li>
                <li>Always quote attribute values</li>
                <li>Use proper nesting of HTML elements</li>
                <li>Fix broken links and references</li>
                <li>Add alt attributes to all images for accessibility</li>
                <li>Include proper meta tags for SEO</li>
                <li>Use responsive design techniques for better mobile experience</li>
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
Write-Host "Starting HTML Validator and Issue Checker..." -ForegroundColor Cyan

# Find HTML files
$htmlFiles = Find-HtmlFiles

# Check each file
$fileCount = 0
foreach ($file in $htmlFiles) {
    $fileCount++
    $stats.FilesChecked++
    
    Write-Host "Checking file $fileCount/$($htmlFiles.Count): $file" -NoNewline
    
    # Check for issues
    $issues = Check-HtmlFileForIssues -FilePath $file
    
    if ($issues.Count -gt 0) {
        # File has issues
        $stats.FilesWithIssues++
        $stats.TotalIssues += $issues.Count
        $issueFiles += $file
        $issueDetails[$file] = $issues -join "`n"
        
        Write-Host " - $($issues.Count) issues found!" -ForegroundColor Red
    } else {
        Write-Host " - OK" -ForegroundColor Green
    }
}

# Generate report
Generate-Report

Write-Host "`nHTML check completed!" -ForegroundColor Green
Write-Host "Files checked: $($stats.FilesChecked)" -ForegroundColor Cyan
Write-Host "Files with issues: $($stats.FilesWithIssues)" -ForegroundColor $(if ($stats.FilesWithIssues -gt 0) { "Red" } else { "Green" })
Write-Host "Total issues found: $($stats.TotalIssues)" -ForegroundColor $(if ($stats.TotalIssues -gt 0) { "Red" } else { "Green" })