# 主代码检查工具
# 此脚本按顺序运行所有代码检查工具并生成综合报告

# 配置
$config = @{
    ReportDir = "reports"
    ToolScripts = @(
        ".\fix_all_php_issues_fixed.ps1",
        ".\check_php_security_fixed.ps1",
        ".\check_html_issues_fixed.ps1"
    )
}

# 创建报告目录
if (-not (Test-Path -Path $config.ReportDir)) {
    New-Item -Path $config.ReportDir -ItemType Directory -Force | Out-Null
    Write-Host "已创建报告目录: $($config.ReportDir)" -ForegroundColor Green
}

# 统计信息
$stats = @{
    StartTime = Get-Date
    EndTime = $null
    Duration = $null
    ToolsRun = 0
    ToolsSucceeded = 0
    ToolsFailed = 0
    ReportsGenerated = @()
}

# 运行单个工具
function Run-Tool {
    param (
        [string]$ToolPath
    )
    
    if (-not (Test-Path -Path $ToolPath)) {
        Write-Host "工具脚本不存在: $ToolPath" -ForegroundColor Red
        return $false
    }
    
    try {
        Write-Host "`n=========================================" -ForegroundColor Cyan
        Write-Host "正在运行 $(Split-Path -Leaf $ToolPath)..." -ForegroundColor Cyan
        Write-Host "=========================================" -ForegroundColor Cyan
        
        # 执行工具脚本
        & $ToolPath
        
        if ($LASTEXITCODE -and $LASTEXITCODE -ne 0) {
            Write-Host "工具执行失败，退出代码: $LASTEXITCODE" -ForegroundColor Red
            return $false
        }
        
        return $true
    }
    catch {
        Write-Host "运行工具时出错: $_" -ForegroundColor Red
        return $false
    }
}

# 查找工具生成的最新报告
function Find-LatestReports {
    $reportFiles = @()
    
    if (Test-Path -Path $config.ReportDir) {
        $allReports = Get-ChildItem -Path $config.ReportDir -Filter "*.html" | Sort-Object LastWriteTime -Descending
        
        # 为每种工具找到最新的报告
        $phpSyntaxReport = $allReports | Where-Object { $_.Name -like "syntax_fix_report_*" } | Select-Object -First 1
        $phpSecurityReport = $allReports | Where-Object { $_.Name -like "php_security_report_*" } | Select-Object -First 1
        $htmlValidationReport = $allReports | Where-Object { $_.Name -like "html_validation_report_*" } | Select-Object -First 1
        
        if ($phpSyntaxReport) { $reportFiles += $phpSyntaxReport.FullName }
        if ($phpSecurityReport) { $reportFiles += $phpSecurityReport.FullName }
        if ($htmlValidationReport) { $reportFiles += $htmlValidationReport.FullName }
    }
    
    return $reportFiles
}

# 生成综合报告
function Generate-MasterReport {
    param (
        [array]$ReportFiles
    )
    
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("master_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    # 从各个报告文件中提取摘要信息
    $summaries = @()
    
    foreach ($file in $ReportFiles) {
        try {
            $content = Get-Content -Path $file -Raw
            $fileName = Split-Path -Leaf $file
            
            # 提取标题
            $titleMatch = [regex]::Match($content, '<title>(.*?)</title>')
            $title = if ($titleMatch.Success) { $titleMatch.Groups[1].Value } else { $fileName }
            
            # 提取摘要表格
            $summaryMatch = [regex]::Match($content, '<div class="summary">(.*?)</div>', [System.Text.RegularExpressions.RegexOptions]::Singleline)
            $summary = if ($summaryMatch.Success) { $summaryMatch.Groups[1].Value } else { "<p>无法提取摘要</p>" }
            
            $summaries += @{
                Title = $title
                Summary = $summary
                FileName = $fileName
                FilePath = $file
            }
        }
        catch {
            Write-Host "处理报告文件时出错: $file - $_" -ForegroundColor Red
        }
    }
    
    # 确定失败工具的CSS类
    $failedToolsClass = if ($stats.ToolsFailed -gt 0) { "error" } else { "success" }
    
    # 生成综合报告HTML
    $html = @"
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>代码检查综合报告</title>
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
        .tool-summary {
            background-color: #f8f9fa;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
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
        .report-link {
            display: inline-block;
            margin: 10px 0;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .report-link:hover {
            background-color: #45a049;
        }
        .success { color: #4CAF50; }
        .warning { color: #FF9800; }
        .error { color: #F44336; }
    </style>
</head>
<body>
    <div class="container">
        <h1>代码检查综合报告</h1>
        <p>生成时间: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>摘要</h2>
            <table class="stats-table">
                <tr>
                    <th>开始时间</th>
                    <td>$($stats.StartTime.ToString('yyyy-MM-dd HH:mm:ss'))</td>
                </tr>
                <tr>
                    <th>结束时间</th>
                    <td>$($stats.EndTime.ToString('yyyy-MM-dd HH:mm:ss'))</td>
                </tr>
                <tr>
                    <th>总耗时</th>
                    <td>$($stats.Duration.ToString())</td>
                </tr>
                <tr>
                    <th>运行的工具</th>
                    <td>$($stats.ToolsRun)</td>
                </tr>
                <tr>
                    <th>成功的工具</th>
                    <td><span class="success">$($stats.ToolsSucceeded)</span></td>
                </tr>
                <tr>
                    <th>失败的工具</th>
                    <td><span class="$failedToolsClass">$($stats.ToolsFailed)</span></td>
                </tr>
                <tr>
                    <th>生成的报告</th>
                    <td>$($stats.ReportsGenerated.Count)</td>
                </tr>
            </table>
        </div>
        
        <h2>各工具报告摘要</h2>
"@
    
    # 添加每个工具的摘要
    foreach ($summary in $summaries) {
        $html += @"
        <div class="tool-summary">
            <h3>$($summary.Title)</h3>
            $($summary.Summary)
            <p><a class="report-link" href="$($summary.FileName)" target="_blank">查看完整报告</a></p>
        </div>
"@
    }
    
    $html += @"
        <div class="recommendations">
            <h2>后续步骤建议</h2>
            <ul>
                <li>查看各个详细报告，修复发现的问题</li>
                <li>优先修复安全漏洞和严重的语法错误</li>
                <li>定期运行代码检查工具，保持代码质量</li>
                <li>考虑将代码检查集成到开发流程中</li>
                <li>对于无法自动修复的问题，制定手动修复计划</li>
            </ul>
        </div>
    </div>
</body>
</html>
"@
    
    Set-Content -Path $reportFile -Value $html
    Write-Host "综合报告已生成: $reportFile" -ForegroundColor Green
    
    return $reportFile
}

# 主执行
Write-Host "正在启动主代码检查工具..." -ForegroundColor Cyan

# 运行所有工具
foreach ($tool in $config.ToolScripts) {
    $stats.ToolsRun++
    $success = Run-Tool -ToolPath $tool
    
    if ($success) {
        $stats.ToolsSucceeded++
    } else {
        $stats.ToolsFailed++
    }
}

# 记录结束时间和持续时间
$stats.EndTime = Get-Date
$stats.Duration = $stats.EndTime - $stats.StartTime

# 查找生成的报告
$stats.ReportsGenerated = Find-LatestReports

# 生成综合报告
$masterReport = Generate-MasterReport -ReportFiles $stats.ReportsGenerated

Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host "主代码检查完成!" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "综合报告已生成: $masterReport" -ForegroundColor Green