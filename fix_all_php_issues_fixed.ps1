# PHP和HTML语法修复工具
# 此脚本检查并修复常见的PHP和HTML语法问题

# 配置
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

# 创建备份目录
if (-not (Test-Path -Path $config.BackupDir)) {
    New-Item -Path $config.BackupDir -ItemType Directory -Force | Out-Null
    Write-Host "已创建备份目录: $($config.BackupDir)" -ForegroundColor Green
}

# 创建报告目录
if (-not (Test-Path -Path $config.ReportDir)) {
    New-Item -Path $config.ReportDir -ItemType Directory -Force | Out-Null
    Write-Host "已创建报告目录: $($config.ReportDir)" -ForegroundColor Green
}

# 统计信息
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

# 查找所有PHP和HTML文件
function Find-Files {
    $phpFiles = @()
    $htmlFiles = @()
    
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
            } elseif ($config.HtmlExtensions -contains $extension) {
                $htmlFiles += $file.FullName
            }
        }
    }
    
    $stats.PhpTotal = $phpFiles.Count
    $stats.HtmlTotal = $htmlFiles.Count
    
    Write-Host "找到 $($stats.PhpTotal) 个PHP文件和 $($stats.HtmlTotal) 个HTML文件需要处理。" -ForegroundColor Cyan
    
    return @{
        PhpFiles = $phpFiles
        HtmlFiles = $htmlFiles
    }
}

# 备份文件
function Backup-File {
    param (
        [string]$FilePath
    )
    
    if (Test-Path -Path $FilePath) {
        $relativePath = $FilePath
        $backupPath = Join-Path -Path $config.BackupDir -ChildPath $relativePath
        
        # 创建目录结构
        $backupDir = Split-Path -Path $backupPath -Parent
        if (-not (Test-Path -Path $backupDir)) {
            New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
        }
        
        # 复制文件
        Copy-Item -Path $FilePath -Destination $backupPath -Force
        return $true
    }
    
    return $false
}

# 检查PHP语法（无需安装PHP）
function Test-PhpSyntax {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $errors = @()
    
    # 检查常见的PHP语法错误
    
    # 1. 检查括号不匹配
    $openBraces = ($content | Select-String -Pattern "{" -AllMatches).Matches.Count
    $closeBraces = ($content | Select-String -Pattern "}" -AllMatches).Matches.Count
    if ($openBraces -ne $closeBraces) {
        $errors += "花括号不匹配: $openBraces 个开括号 vs $closeBraces 个闭括号"
    }
    
    $openParens = ($content | Select-String -Pattern '\(' -AllMatches).Matches.Count
    $closeParens = ($content | Select-String -Pattern '\)' -AllMatches).Matches.Count
    if ($openParens -ne $closeParens) {
        $errors += "圆括号不匹配: $openParens 个开括号 vs $closeParens 个闭括号"
    }
    
    $openBrackets = ($content | Select-String -Pattern '\[' -AllMatches).Matches.Count
    $closeBrackets = ($content | Select-String -Pattern '\]' -AllMatches).Matches.Count
    if ($openBrackets -ne $closeBrackets) {
        $errors += "方括号不匹配: $openBrackets 个开括号 vs $closeBrackets 个闭括号"
    }
    
    # 2. 检查缺少分号
    if ($content -match '(?<!\;|\{|\})\s*\n\s*\$') {
        $errors += "可能缺少分号"
    }
    
    # 3. 检查字符串问题
    $singleQuotes = ($content | Select-String -Pattern "'" -AllMatches).Matches.Count
    if ($singleQuotes % 2 -ne 0) {
        $errors += "单引号数量不匹配: $singleQuotes"
    }
    
    $doubleQuotes = ($content | Select-String -Pattern '"' -AllMatches).Matches.Count
    if ($doubleQuotes % 2 -ne 0) {
        $errors += "双引号数量不匹配: $doubleQuotes"
    }
    
    # 4. 检查常见的PHP语法模式
    if ($content -match 'array\s*\(\s*\)\s*\{') {
        $errors += "无效的数组语法: array() {"
    }
    
    if ($content -match '\]\s*\(') {
        $errors += "无效的语法: ]("
    }
    
    if ($content -match '\)\s*\[') {
        $errors += "无效的语法: )["
    }
    
    # 5. 检查PHP开始/结束标签
    if (-not ($content -match "<\?php")) {
        $errors += "缺少PHP开始标签 <?php"
    }
    
    # 返回错误
    return $errors
}

# 修复PHP语法错误
function Fix-PhpSyntaxErrors {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $originalContent = $content
    
    # 应用常见PHP语法错误的修复
    
    # 1. 修复数组语法
    $content = $content -replace 'array\s*\(', '['
    $content = $content -replace '\)(?=\s*[;,])', ']'
    
    # 2. 修复字符串连接
    $content = $content -replace '"(.*?)"\s*\.\s*"(.*?)"', '"$1$2"'
    
    # 3. 修复运算符周围的空格
    $content = $content -replace '\s+->(?=\w)', '->'
    $content = $content -replace '\(\s+', '('
    $content = $content -replace '\s+\)', ')'
    
    # 4. 修复数组键中的引号
    $content = $content -replace "\[\'(.*?)\'\]", '["$1"]'
    
    # 5. 修复类属性声明
    $content = $content -replace ':(\s*)protected(\s*)string(\s*)\$', 'protected string $'
    $content = $content -replace ':(\s*)private(\s*)string(\s*)\$', 'private string $'
    $content = $content -replace ':(\s*)public(\s*)string(\s*)\$', 'public string $'
    
    # 6. 修复正则表达式模式
    $content = $content -replace '"\]\+\$\/"', '"]+$/"'
    $content = $content -replace '"\]\+\$\/u"', '"]+$/u"'
    
    # 7. 修复字符串转换
    $content = $content -replace '\(string\s+\)', '(string)'
    
    # 8. 修复分号
    $content = $content -replace ';\s+\}', ';}'
    
    # 9. 修复闭合括号
    $content = $content -replace '\)\s*\{', ') {'
    
    # 10. 修复双引号
    $content = $content -replace '""', '"'
    
    # 11. 如果缺少则添加PHP开始标签
    if (-not ($content -match "<\?php")) {
        $content = "<?php`n" + $content
    }
    
    # 检查内容是否已更改
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content
        return $true
    }
    
    return $false
}

# 基本HTML验证
function Test-HtmlSyntax {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $errors = @()
    
    # 检查未闭合标签
    $tags = @('div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'input', 'select', 'option')
    
    foreach ($tag in $tags) {
        $openTagPattern = "<$tag(\s|>)"
        $closeTagPattern = "</$tag>"
        
        $openCount = [regex]::Matches($content, $openTagPattern).Count
        $closeCount = [regex]::Matches($content, $closeTagPattern).Count
        
        if ($openCount -gt $closeCount) {
            $errors += "未闭合的 <$tag> 标签 ($openCount 个开标签, $closeCount 个闭标签)"
        }
    }
    
    # 检查未加引号的属性
    $unquotedAttrPattern = '(\w+)=([^\s"''>\$]+)(?=\s|>)'
    $malformedAttrs = [regex]::Matches($content, $unquotedAttrPattern)
    
    if ($malformedAttrs.Count -gt 0) {
        foreach ($match in $malformedAttrs) {
            $errors += "发现未加引号的属性: $($match.Value)"
        }
    }
    
    # 检查无效嵌套
    $invalidNestingPattern = "<(p|div)>.*?<(table|ul|ol)>.*?</\1>.*?</\2>"
    if ($content -match $invalidNestingPattern) {
        $errors += "检测到块元素的无效嵌套"
    }
    
    return $errors
}

# 修复HTML错误
function Fix-HtmlSyntaxErrors {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $originalContent = $content
    
    # 1. 修复未加引号的属性
    $unquotedAttrPattern = '(\w+)=([^\s"''>\$]+)(?=\s|>)'
    $content = [regex]::Replace($content, $unquotedAttrPattern, '$1="$2"')
    
    # 2. 修复未闭合标签（基本方法）
    $tags = @('div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'select')
    
    foreach ($tag in $tags) {
        $openTagPattern = "<$tag(\s|>)"
        $closeTagPattern = "</$tag>"
        
        $openCount = [regex]::Matches($content, $openTagPattern).Count
        $closeCount = [regex]::Matches($content, $closeTagPattern).Count
        
        # 在末尾添加缺失的闭合标签
        if ($openCount -gt $closeCount) {
            $diff = $openCount - $closeCount
            $content = $content + (("</$tag>" * $diff) -join "")
        }
    }
    
    # 检查内容是否已更改
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content
        return $true
    }
    
    return $false
}

# 处理PHP文件
function Process-PhpFiles {
    $files = Find-Files
    $phpFiles = $files.PhpFiles
    
    Write-Host "`n正在检查PHP语法..." -ForegroundColor Cyan
    
    foreach ($file in $phpFiles) {
        $stats.PhpChecked++
        
        Write-Host "正在检查文件 $($stats.PhpChecked)/$($stats.PhpTotal): $file" -NoNewline
        
        # 检查语法
        $errors = Test-PhpSyntax -FilePath $file
        
        if ($errors.Count -gt 0) {
            # 发现语法错误
            $stats.PhpErrors++
            $errorFiles += $file
            $errorDetails[$file] = $errors -join "`n"
            
            Write-Host " - 发现错误!" -ForegroundColor Red
            
            # 备份文件
            Backup-File -FilePath $file
            
            # 尝试修复文件
            if (Fix-PhpSyntaxErrors -FilePath $file) {
                $stats.PhpFixed++
                $fixedFiles += $file
                Write-Host "  - 已修复!" -ForegroundColor Green
            } else {
                Write-Host "  - 无法自动修复。" -ForegroundColor Yellow
            }
        } else {
            Write-Host " - 正常" -ForegroundColor Green
        }
    }
    
    Write-Host "`nPHP语法检查完成。" -ForegroundColor Cyan
    Write-Host "有错误的文件: $($stats.PhpErrors)" -ForegroundColor $(if ($stats.PhpErrors -gt 0) { "Red" } else { "Green" })
    Write-Host "已修复的文件: $($stats.PhpFixed)" -ForegroundColor Green
}

# 处理HTML文件
function Process-HtmlFiles {
    $files = Find-Files
    $htmlFiles = $files.HtmlFiles
    
    Write-Host "`n正在检查HTML文件..." -ForegroundColor Cyan
    
    foreach ($file in $htmlFiles) {
        $stats.HtmlChecked++
        
        Write-Host "正在检查文件 $($stats.HtmlChecked)/$($stats.HtmlTotal): $file" -NoNewline
        
        # 检查语法
        $errors = Test-HtmlSyntax -FilePath $file
        
        if ($errors.Count -gt 0) {
            # 发现HTML错误
            $stats.HtmlErrors++
            $errorFiles += $file
            $errorDetails[$file] = $errors -join "`n"
            
            Write-Host " - 发现错误!" -ForegroundColor Red
            
            # 备份文件
            Backup-File -FilePath $file
            
            # 尝试修复文件
            if (Fix-HtmlSyntaxErrors -FilePath $file) {
                $stats.HtmlFixed++
                $fixedFiles += $file
                Write-Host "  - 已修复!" -ForegroundColor Green
            } else {
                Write-Host "  - 无法自动修复。" -ForegroundColor Yellow
            }
        } else {
            Write-Host " - 正常" -ForegroundColor Green
        }
    }
    
    Write-Host "`nHTML检查完成。" -ForegroundColor Cyan
    Write-Host "有问题的文件: $($stats.HtmlErrors)" -ForegroundColor $(if ($stats.HtmlErrors -gt 0) { "Red" } else { "Green" })
    Write-Host "已修复的文件: $($stats.HtmlFixed)" -ForegroundColor Green
}

# 生成HTML报告
function Generate-Report {
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("syntax_fix_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    $html = @"
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>语法修复报告</title>
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
        <h1>语法修复报告</h1>
        <p>生成时间: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>摘要</h2>
            <table class="stats-table">
                <tr>
                    <th>类型</th>
                    <th>文件总数</th>
                    <th>已检查</th>
                    <th>有错误</th>
                    <th>已修复</th>
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
    
    # 有错误的文件
    if ($errorFiles.Count -gt 0) {
        $html += @"
        <div class="error-files">
            <h2>有错误的文件</h2>
            <ul class="file-list">
"@
        
        $displayCount = 0
        foreach ($file in $errorFiles) {
            $displayCount++
            if ($displayCount -gt $config.MaxFilesToDisplay) {
                $html += "<li>... 以及其他 $($errorFiles.Count - $config.MaxFilesToDisplay) 个文件</li>"
                break
            }
            
            $fixed = $fixedFiles -contains $file
            $statusClass = if ($fixed) { "fixed" } else { "not-fixed" }
            $statusText = if ($fixed) { "已修复" } else { "未修复" }
            
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
    
    # 已修复的文件
    if ($fixedFiles.Count -gt 0) {
        $html += @"
        <div class="fixed-files">
            <h2>已修复的文件</h2>
            <ul class="file-list">
"@
        
        $displayCount = 0
        foreach ($file in $fixedFiles) {
            $displayCount++
            if ($displayCount -gt $config.MaxFilesToDisplay) {
                $html += "<li>... 以及其他 $($fixedFiles.Count - $config.MaxFilesToDisplay) 个文件</li>"
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
    
    Set-Content -Path $reportFile -Value $html -Encoding UTF8
    Write-Host "报告已生成: $reportFile" -ForegroundColor Green
}

# 主执行
Write-Host "正在启动PHP和HTML语法修复工具..." -ForegroundColor Cyan

# 处理文件
Process-PhpFiles
Process-HtmlFiles

# 生成报告
Generate-Report

Write-Host "`n语法修复完成!" -ForegroundColor Green
Write-Host "所有原始文件已备份到 $($config.BackupDir)" -ForegroundColor Green