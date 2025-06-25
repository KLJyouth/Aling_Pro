# 简单的PowerShell验证脚本
# 用于扫描项目中的问题但不进行修复

$log_file = "validation_log_" + (Get-Date -Format "yyyyMMdd_HHmmss") + ".log"
$report_file = "VALIDATION_REPORT.md"

# 初始化日志
Set-Content -Path $log_file -Value ("=== 验证扫描日志 - " + (Get-Date -Format "yyyy-MM-dd HH:mm:ss") + " ===`n`n")
Write-Host "开始验证扫描..."

# 统计数据
$stats = @{
    "total_files" = 0
    "scanned_files" = 0
    "encoding_issues" = 0
    "syntax_errors" = 0
    "php81_issues" = 0
    "error_files" = 0
}

# 问题文件列表
$issue_files = @{
    "encoding_issues" = @()
    "syntax_errors" = @()
    "php81_issues" = @()
}

# 要排除的目录
$exclude_dirs = @(
    ".git",
    "vendor",
    "node_modules",
    "backups",
    "backup",
    "tmp",
    "temp",
    "logs",
    "php_temp",
    "portable_php"
)

# 要处理的文件扩展名
$file_extensions = @("php", "phtml", "php5", "php7", "phps")

# 记录日志
function Log-Message {
    param($message)
    
    Write-Host $message
    Add-Content -Path $log_file -Value "$message`n"
}

# 检查是否需要排除目录
function Should-ExcludeDir {
    param($dir)
    
    $basename = Split-Path -Path $dir -Leaf
    return $exclude_dirs -contains $basename
}

# 检查文件类型
function Is-TargetFile {
    param($file)
    
    $ext = [System.IO.Path]::GetExtension($file).TrimStart(".").ToLower()
    return $file_extensions -contains $ext
}

# 检查中文乱码
function Check-ChineseEncoding {
    param($content)
    
    # 检测是否有中文乱码(锟斤拷)
    if ($content -match "锟斤拷") {
        return $true
    }
    
    return $false
}

# 检查语法错误
function Check-SyntaxErrors {
    param($content)
    
    # 检查特定的语法错误模式
    $has_issues = $false
    
    # 检查缺少引号的version标记
    if ($content -match '\[version\]') {
        $has_issues = $true
    }
    
    # 检查数组键值对问题
    if ($content -match '=>([^,\s\n\]]*?)(\s*[\]\)])') {
        $has_issues = $true
    }
    
    return $has_issues
}

# 扫描目录
function Scan-Directory {
    param($dir)
    
    $items = Get-ChildItem -Path $dir -ErrorAction SilentlyContinue
    
    foreach ($item in $items) {
        if ($item.PSIsContainer) {
            if (-not (Should-ExcludeDir $item.FullName)) {
                Scan-Directory $item.FullName
            }
        } else {
            $stats.total_files++
            
            if (Is-TargetFile $item.FullName) {
                Validate-File $item.FullName
            }
        }
    }
}

# 验证文件
function Validate-File {
    param($file)
    
    $stats.scanned_files++
    
    try {
        $content = Get-Content -Path $file -Raw -ErrorAction Stop -Encoding UTF8
        
        # 检查中文乱码
        if (Check-ChineseEncoding $content) {
            $stats.encoding_issues++
            $issue_files.encoding_issues += $file
            Log-Message "发现中文乱码: $file"
        }
        
        # 检查语法错误
        if (Check-SyntaxErrors $content) {
            $stats.syntax_errors++
            $issue_files.syntax_errors += $file
            Log-Message "发现语法错误: $file"
        }
        
        # PHP 8.1兼容性问题需要PHP来检查，这里我们只进行简单的检查
        if ($content -match '\[version\]' -or $content -match '\[email\]' -or $content -match 'each\(') {
            $stats.php81_issues++
            $issue_files.php81_issues += $file
            Log-Message "可能存在PHP 8.1兼容性问题: $file"
        }
        
    } catch {
        Log-Message "验证文件时出错 {$file}: $_"
        $stats.error_files++
    }
}

# 生成报告
function Generate-Report {
    $report = "# 验证扫描报告`n`n"
    $report += "## 扫描统计`n`n"
    $report += "* 扫描时间: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')`n"
    $report += "* 总文件数: $($stats.total_files)`n"
    $report += "* 扫描文件数: $($stats.scanned_files)`n"
    $report += "* 错误文件数: $($stats.error_files)`n`n"
    
    $report += "## 问题类型`n`n"
    $report += "* 中文乱码问题: $($stats.encoding_issues)`n"
    $report += "* 语法错误: $($stats.syntax_errors)`n"
    $report += "* PHP 8.1兼容性问题: $($stats.php81_issues)`n`n"
    
    $report += "## 问题文件列表`n`n"
    
    if ($issue_files.encoding_issues.Count -gt 0) {
        $report += "### 中文乱码问题文件`n`n"
        foreach ($file in $issue_files.encoding_issues) {
            $report += "* `$file`n"
        }
        $report += "`n"
    }
    
    if ($issue_files.syntax_errors.Count -gt 0) {
        $report += "### 语法错误文件`n`n"
        foreach ($file in $issue_files.syntax_errors) {
            $report += "* `$file`n"
        }
        $report += "`n"
    }
    
    if ($issue_files.php81_issues.Count -gt 0) {
        $report += "### PHP 8.1兼容性问题文件`n`n"
        foreach ($file in $issue_files.php81_issues) {
            $report += "* `$file`n"
        }
        $report += "`n"
    }
    
    $report += "## 建议`n`n"
    $report += "1. 在所有PHP文件中统一使用UTF-8编码，避免中文乱码问题`n"
    $report += "2. 使用PHP代码质量工具(如PHP_CodeSniffer)来自动检查代码规范`n"
    $report += "3. 考虑升级项目依赖，确保与最新版PHP兼容`n"
    $report += "4. 为开发团队提供编码规范指南，特别是关于中文字符的处理`n`n"
    
    $report += "## 后续步骤`n`n"
    $report += "1. 运行系统化验证和修复脚本(systematic_fix.php)修复发现的问题`n"
    $report += "2. 对修复后的代码进行功能测试，确保功能正常`n"
    $report += "3. 对特别重要或复杂的文件进行手动检查`n"
    
    Set-Content -Path $report_file -Value $report -Encoding UTF8
    Log-Message "已生成报告: $report_file"
}

# 主函数
function Main {
    Log-Message "开始验证扫描项目..."
    
    $start_time = Get-Date
    
    # 从当前目录开始扫描
    Scan-Directory "."
    
    $end_time = Get-Date
    $execution_time = [math]::Round(($end_time - $start_time).TotalSeconds, 2)
    
    Log-Message "扫描完成!"
    Log-Message "执行时间: $execution_time 秒"
    
    Generate-Report
}

# 执行主函数
Main 