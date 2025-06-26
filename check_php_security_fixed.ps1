# PHP安全漏洞扫描工具
# 此脚本检查PHP代码中的常见安全漏洞

# 配置
$config = @{
    ReportDir = "reports"
    MaxFilesToDisplay = 50
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

# 创建报告目录
if (-not (Test-Path -Path $config.ReportDir)) {
    New-Item -Path $config.ReportDir -ItemType Directory -Force | Out-Null
    Write-Host "已创建报告目录: $($config.ReportDir)" -ForegroundColor Green
}

# 统计信息
$stats = @{
    FilesTotal = 0
    FilesChecked = 0
    FilesWithVulnerabilities = 0
    VulnerabilitiesFound = 0
    VulnerabilitiesByType = @{
        "SQL注入" = 0
        "XSS跨站脚本" = 0
        "文件包含" = 0
        "命令注入" = 0
        "硬编码凭据" = 0
        "不安全的配置" = 0
    }
}

$vulnerableFiles = @()
$vulnerabilityDetails = @{}

# 查找所有PHP文件
function Find-Files {
    $phpFiles = @()
    
    foreach ($dir in $config.Directories) {
        if (-not (Test-Path -Path $dir)) {
            continue
        }
        
        $allFiles = Get-ChildItem -Path $dir -Recurse -File
        
        foreach ($file in $allFiles) {
            # 检查是否应排除目录
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
            
            # 检查文件扩展名
            $extension = $file.Extension.TrimStart(".").ToLower()
            
            if ($config.PhpExtensions -contains $extension) {
                $phpFiles += $file.FullName
            }
        }
    }
    
    $stats.FilesTotal = $phpFiles.Count
    
    Write-Host "找到 $($stats.FilesTotal) 个PHP文件需要安全检查。" -ForegroundColor Cyan
    
    return $phpFiles
}

# 检查SQL注入漏洞
function Check-SqlInjection {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $vulnerabilities = @()
    
    # 定义SQL注入模式
    $patterns = @(
        '\$_GET\[[''"][^''"\]]*[''"\]\]\s*.*?(?:mysql_query|mysqli_query|->query)',
        '\$_POST\[[''"][^''"\]]*[''"\]\]\s*.*?(?:mysql_query|mysqli_query|->query)',
        '\$_REQUEST\[[''"][^''"\]]*[''"\]\]\s*.*?(?:mysql_query|mysqli_query|->query)',
        'SELECT\s+.*?FROM\s+.*?WHERE\s+.*?\$_',
        'INSERT\s+INTO\s+.*?VALUES\s*\(.*?\$_',
        'UPDATE\s+.*?SET\s+.*?\$_'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern)
        
        foreach ($match in $matches) {
            # 检查是否使用了参数化查询
            $context = $content.Substring([Math]::Max(0, $match.Index - 50), [Math]::Min(100, $content.Length - $match.Index + 50))
            
            # 如果没有使用参数化查询或预处理语句，则认为存在漏洞
            if (-not ($context -match "prepare|bind_param|bindParam|bindValue")) {
                $lineNumber = ($content.Substring(0, $match.Index).Split("`n")).Count
                $vulnerabilities += "SQL注入漏洞在第 ${lineNumber} 行: $($match.Value)"
                $stats.VulnerabilitiesByType["SQL注入"]++
                $stats.VulnerabilitiesFound++
            }
        }
    }
    
    return $vulnerabilities
}

# 检查XSS跨站脚本漏洞
function Check-XssVulnerabilities {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $vulnerabilities = @()
    
    # 定义XSS模式
    $patterns = @(
        'echo\s+\$_GET\[[''"][^''"\]]*[''"\]\]',
        'echo\s+\$_POST\[[''"][^''"\]]*[''"\]\]',
        'echo\s+\$_REQUEST\[[''"][^''"\]]*[''"\]\]',
        'echo\s+\$_COOKIE\[[''"][^''"\]]*[''"\]\]',
        'print\s+\$_GET\[[''"][^''"\]]*[''"\]\]',
        'print\s+\$_POST\[[''"][^''"\]]*[''"\]\]',
        'print\s+\$_REQUEST\[[''"][^''"\]]*[''"\]\]',
        'print\s+\$_COOKIE\[[''"][^''"\]]*[''"\]\]',
        '\$_GET\[[''"][^''"\]]*[''"\]\]\s*\.\s*[\''"]',
        '\$_POST\[[''"][^''"\]]*[''"\]\]\s*\.\s*[\''"]'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern)
        
        foreach ($match in $matches) {
            # 检查是否使用了XSS防护
            $context = $content.Substring([Math]::Max(0, $match.Index - 50), [Math]::Min(100, $content.Length - $match.Index + 50))
            
            # 如果没有使用htmlspecialchars或htmlentities，则认为存在漏洞
            if (-not ($context -match "htmlspecialchars|htmlentities|strip_tags")) {
                $lineNumber = ($content.Substring(0, $match.Index).Split("`n")).Count
                $vulnerabilities += "XSS漏洞在第 ${lineNumber} 行: $($match.Value)"
                $stats.VulnerabilitiesByType["XSS跨站脚本"]++
                $stats.VulnerabilitiesFound++
            }
        }
    }
    
    return $vulnerabilities
}

# 检查文件包含漏洞
function Check-FileInclusion {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $vulnerabilities = @()
    
    # 定义文件包含模式
    $patterns = @(
        '(?:include|require|include_once|require_once)\s*\(\s*\$_GET\[[''"][^''"\]]*[''"\]\]\s*\)',
        '(?:include|require|include_once|require_once)\s*\(\s*\$_POST\[[''"][^''"\]]*[''"\]\]\s*\)',
        '(?:include|require|include_once|require_once)\s*\(\s*\$_REQUEST\[[''"][^''"\]]*[''"\]\]\s*\)',
        '(?:include|require|include_once|require_once)\s*\$_GET\[[''"][^''"\]]*[''"\]\]',
        '(?:include|require|include_once|require_once)\s*\$_POST\[[''"][^''"\]]*[''"\]\]',
        '(?:include|require|include_once|require_once)\s*\$_REQUEST\[[''"][^''"\]]*[''"\]\]'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern)
        
        foreach ($match in $matches) {
            # 检查是否有路径验证
            $context = $content.Substring([Math]::Max(0, $match.Index - 50), [Math]::Min(100, $content.Length - $match.Index + 50))
            
            # 如果没有使用路径验证，则认为存在漏洞
            if (-not ($context -match "basename|realpath|pathinfo")) {
                $lineNumber = ($content.Substring(0, $match.Index).Split("`n")).Count
                $vulnerabilities += "文件包含漏洞在第 ${lineNumber} 行: $($match.Value)"
                $stats.VulnerabilitiesByType["文件包含"]++
                $stats.VulnerabilitiesFound++
            }
        }
    }
    
    return $vulnerabilities
}

# 检查命令注入漏洞
function Check-CommandInjection {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $vulnerabilities = @()
    
    # 定义命令注入模式
    $patterns = @(
        '(?:system|exec|passthru|shell_exec|popen|proc_open|eval)\s*\(\s*\$_GET\[[''"][^''"\]]*[''"\]\]\s*\)',
        '(?:system|exec|passthru|shell_exec|popen|proc_open|eval)\s*\(\s*\$_POST\[[''"][^''"\]]*[''"\]\]\s*\)',
        '(?:system|exec|passthru|shell_exec|popen|proc_open|eval)\s*\(\s*\$_REQUEST\[[''"][^''"\]]*[''"\]\]\s*\)',
        '\`\s*\$_GET\[[''"][^''"\]]*[''"\]\]\s*\`',
        '\`\s*\$_POST\[[''"][^''"\]]*[''"\]\]\s*\`',
        '\`\s*\$_REQUEST\[[''"][^''"\]]*[''"\]\]\s*\`'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern)
        
        foreach ($match in $matches) {
            # 检查是否有命令验证
            $context = $content.Substring([Math]::Max(0, $match.Index - 50), [Math]::Min(100, $content.Length - $match.Index + 50))
            
            # 如果没有使用命令验证，则认为存在漏洞
            if (-not ($context -match "escapeshellarg|escapeshellcmd")) {
                $lineNumber = ($content.Substring(0, $match.Index).Split("`n")).Count
                $vulnerabilities += "命令注入漏洞在第 ${lineNumber} 行: $($match.Value)"
                $stats.VulnerabilitiesByType["命令注入"]++
                $stats.VulnerabilitiesFound++
            }
        }
    }
    
    return $vulnerabilities
}

# 检查硬编码凭据
function Check-HardcodedCredentials {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $vulnerabilities = @()
    
    # 定义硬编码凭据模式
    $patterns = @(
        'password\s*=\s*[''"](?!\$).*?[''"]',
        'passwd\s*=\s*[''"](?!\$).*?[''"]',
        'pwd\s*=\s*[''"](?!\$).*?[''"]',
        'username\s*=\s*[''"](?!\$).*?[''"]',
        'apikey\s*=\s*[''"](?!\$).*?[''"]',
        'api_key\s*=\s*[''"](?!\$).*?[''"]',
        'secret\s*=\s*[''"](?!\$).*?[''"]'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern)
        
        foreach ($match in $matches) {
            # 排除一些常见的假阳性
            $value = $match.Value
            if (($value -match "password\s*=\s*['\"]\s*['\""]") -or
                ($value -match "password\s*=\s*['\"].*?\\\$.*?['\""]") -or
                ($value -match "password\s*=\s*['\"]\{.*?\}['\""]")) {
                continue
            }
            
            $lineNumber = ($content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "硬编码凭据在第 ${lineNumber} 行: $($match.Value)"
            $stats.VulnerabilitiesByType["硬编码凭据"]++
            $stats.VulnerabilitiesFound++
        }
    }
    
    return $vulnerabilities
}

# 检查不安全的配置
function Check-InsecureConfiguration {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $vulnerabilities = @()
    
    # 定义不安全配置模式
    $patterns = @(
        'display_errors\s*=\s*On',
        'allow_url_fopen\s*=\s*On',
        'allow_url_include\s*=\s*On',
        'expose_php\s*=\s*On',
        'register_globals\s*=\s*On',
        'error_reporting\s*\(\s*0\s*\)',
        '@error_reporting\s*\(\s*0\s*\)',
        'mysqli_report\s*\(\s*MYSQLI_REPORT_OFF\s*\)'
    )
    
    foreach ($pattern in $patterns) {
        $matches = [regex]::Matches($content, $pattern)
        
        foreach ($match in $matches) {
            $lineNumber = ($content.Substring(0, $match.Index).Split("`n")).Count
            $vulnerabilities += "不安全的配置在第 ${lineNumber} 行: $($match.Value)"
            $stats.VulnerabilitiesByType["不安全的配置"]++
            $stats.VulnerabilitiesFound++
        }
    }
    
    return $vulnerabilities
}

# 处理PHP文件
function Process-Files {
    $phpFiles = Find-Files
    
    Write-Host "`n正在检查PHP安全漏洞..." -ForegroundColor Cyan
    
    foreach ($file in $phpFiles) {
        $stats.FilesChecked++
        
        Write-Host "正在检查文件 $($stats.FilesChecked)/$($stats.FilesTotal): $file" -NoNewline
        
        # 收集所有漏洞
        $allVulnerabilities = @()
        $allVulnerabilities += Check-SqlInjection -FilePath $file
        $allVulnerabilities += Check-XssVulnerabilities -FilePath $file
        $allVulnerabilities += Check-FileInclusion -FilePath $file
        $allVulnerabilities += Check-CommandInjection -FilePath $file
        $allVulnerabilities += Check-HardcodedCredentials -FilePath $file
        $allVulnerabilities += Check-InsecureConfiguration -FilePath $file
        
        if ($allVulnerabilities.Count -gt 0) {
            # 发现漏洞
            $stats.FilesWithVulnerabilities++
            $vulnerableFiles += $file
            $vulnerabilityDetails[$file] = $allVulnerabilities -join "`n"
            
            Write-Host " - 发现 $($allVulnerabilities.Count) 个安全问题!" -ForegroundColor Red
        } else {
            Write-Host " - 安全" -ForegroundColor Green
        }
    }
    
    Write-Host "`nPHP安全检查完成。" -ForegroundColor Cyan
    Write-Host "有漏洞的文件: $($stats.FilesWithVulnerabilities)" -ForegroundColor $(if ($stats.FilesWithVulnerabilities -gt 0) { "Red" } else { "Green" })
    Write-Host "发现的漏洞总数: $($stats.VulnerabilitiesFound)" -ForegroundColor $(if ($stats.VulnerabilitiesFound -gt 0) { "Red" } else { "Green" })
    
    # 显示漏洞类型统计
    Write-Host "`n漏洞类型统计:" -ForegroundColor Cyan
    foreach ($type in $stats.VulnerabilitiesByType.Keys) {
        $count = $stats.VulnerabilitiesByType[$type]
        Write-Host "  $type`: $count" -ForegroundColor $(if ($count -gt 0) { "Yellow" } else { "Green" })
    }
}

# 生成HTML报告
function Generate-Report {
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("php_security_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    $html = @"
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP安全漏洞报告</title>
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
        .vulnerable-files {
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
        .chart-container {
            width: 100%;
            height: 300px;
            margin: 20px 0;
        }
        .vulnerability-type {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 3px;
            display: inline-block;
            color: white;
        }
        .sql-injection { background-color: #F44336; }
        .xss { background-color: #FF9800; }
        .file-inclusion { background-color: #9C27B0; }
        .command-injection { background-color: #E91E63; }
        .hardcoded-credentials { background-color: #673AB7; }
        .insecure-config { background-color: #3F51B5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP安全漏洞报告</h1>
        <p>生成时间: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>摘要</h2>
            <table class="stats-table">
                <tr>
                    <th>文件总数</th>
                    <th>已检查</th>
                    <th>有漏洞</th>
                    <th>漏洞总数</th>
                </tr>
                <tr>
                    <td>$($stats.FilesTotal)</td>
                    <td>$($stats.FilesChecked)</td>
                    <td>$($stats.FilesWithVulnerabilities)</td>
                    <td>$($stats.VulnerabilitiesFound)</td>
                </tr>
            </table>
            
            <h3>漏洞类型分布</h3>
            <table class="stats-table">
                <tr>
                    <th>漏洞类型</th>
                    <th>数量</th>
                </tr>
"@
    
    foreach ($type in $stats.VulnerabilitiesByType.Keys) {
        $count = $stats.VulnerabilitiesByType[$type]
        $cssClass = switch ($type) {
            "SQL注入" { "sql-injection" }
            "XSS跨站脚本" { "xss" }
            "文件包含" { "file-inclusion" }
            "命令注入" { "command-injection" }
            "硬编码凭据" { "hardcoded-credentials" }
            "不安全的配置" { "insecure-config" }
            default { "" }
        }
        
        $html += @"
                <tr>
                    <td><span class="vulnerability-type $cssClass">$type</span></td>
                    <td>$count</td>
                </tr>
"@
    }
    
    $html += @"
            </table>
        </div>
"@
    
    # 有漏洞的文件
    if ($vulnerableFiles.Count -gt 0) {
        $html += @"
        <div class="vulnerable-files">
            <h2>有漏洞的文件</h2>
            <ul class="file-list">
"@
        
        $displayCount = 0
        foreach ($file in $vulnerableFiles) {
            $displayCount++
            if ($displayCount -gt $config.MaxFilesToDisplay) {
                $html += "<li>... 以及其他 $($vulnerableFiles.Count - $config.MaxFilesToDisplay) 个文件</li>"
                break
            }
            
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
            <h2>安全建议</h2>
            <ul>
                <li><strong>SQL注入:</strong> 使用参数化查询（PDO或mysqli的预处理语句）而不是直接拼接SQL字符串。</li>
                <li><strong>XSS跨站脚本:</strong> 使用htmlspecialchars()或htmlentities()函数过滤输出到页面的所有用户输入。</li>
                <li><strong>文件包含:</strong> 使用白名单验证文件路径，避免直接使用用户输入作为文件路径。</li>
                <li><strong>命令注入:</strong> 避免将用户输入传递给系统命令，如果必须，使用escapeshellarg()和escapeshellcmd()函数。</li>
                <li><strong>硬编码凭据:</strong> 使用环境变量或配置文件存储敏感信息，确保不在代码中硬编码密码和API密钥。</li>
                <li><strong>不安全的配置:</strong> 在生产环境中禁用错误显示，关闭不必要的PHP功能如allow_url_include。</li>
            </ul>
        </div>
    </div>
</body>
</html>
"@
    
    Set-Content -Path $reportFile -Value $html -Encoding UTF8
    Write-Host "报告已生成: $reportFile" -ForegroundColor Green
}

# 主执行
Write-Host "正在启动PHP安全漏洞扫描..." -ForegroundColor Cyan

# 处理文件
Process-Files

# 生成报告
Generate-Report

Write-Host "`nPHP安全检查完成!" -ForegroundColor Green