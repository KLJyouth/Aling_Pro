# HTML验证工具
# 此脚本检查HTML文件的语法、可访问性、SEO和响应式设计问题

# 配置
$config = @{
    ReportDir = "reports"
    MaxFilesToDisplay = 50
    HtmlExtensions = @("html", "htm", "php")
    Directories = @(
        "public",
        "admin",
        "apps",
        "completed",
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
    FilesWithIssues = 0
    IssuesFound = 0
    IssuesByType = @{
        "语法错误" = 0
        "可访问性问题" = 0
        "SEO问题" = 0
        "响应式设计问题" = 0
    }
}

$issueFiles = @()
$issueDetails = @{}

# 查找所有HTML文件
function Find-Files {
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
            
            if ($config.HtmlExtensions -contains $extension) {
                $htmlFiles += $file.FullName
            }
        }
    }
    
    $stats.FilesTotal = $htmlFiles.Count
    
    Write-Host "找到 $($stats.FilesTotal) 个HTML文件需要验证。" -ForegroundColor Cyan
    
    return $htmlFiles
}

# 检查HTML语法问题
function Check-HtmlSyntax {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $issues = @()
    
    # 检查未闭合标签
    $tags = @('div', 'span', 'p', 'a', 'table', 'tr', 'td', 'ul', 'ol', 'li', 'form', 'input', 'select', 'option', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6')
    
    foreach ($tag in $tags) {
        $openTagPattern = "<$tag(\s|>)"
        $closeTagPattern = "</$tag>"
        
        $openCount = [regex]::Matches($content, $openTagPattern).Count
        $closeCount = [regex]::Matches($content, $closeTagPattern).Count
        
        if ($openCount -gt $closeCount) {
            $issues += "未闭合的 <$tag> 标签 ($openCount 个开标签, $closeCount 个闭标签)"
            $stats.IssuesByType["语法错误"]++
            $stats.IssuesFound++
        }
    }
    
    # 检查未加引号的属性
    $unquotedAttrPattern = '(\w+)=([^\s"''>\$]+)(?=\s|>)'
    $malformedAttrs = [regex]::Matches($content, $unquotedAttrPattern)
    
    if ($malformedAttrs.Count -gt 0) {
        foreach ($match in $malformedAttrs) {
            $issues += "未加引号的属性: $($match.Value)"
            $stats.IssuesByType["语法错误"]++
            $stats.IssuesFound++
        }
    }
    
    # 检查无效的HTML5 DOCTYPE
    if (-not ($content -match "<!DOCTYPE html>")) {
        $issues += "缺少HTML5 DOCTYPE声明"
        $stats.IssuesByType["语法错误"]++
        $stats.IssuesFound++
    }
    
    # 检查无效嵌套
    $invalidNestingPattern = "<(p|div)>.*?<(table|ul|ol)>.*?</\1>.*?</\2>"
    if ($content -match $invalidNestingPattern) {
        $issues += "检测到块元素的无效嵌套"
        $stats.IssuesByType["语法错误"]++
        $stats.IssuesFound++
    }
    
    return $issues
}

# 检查可访问性问题
function Check-Accessibility {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $issues = @()
    
    # 检查图片是否缺少alt属性
    $imgTags = [regex]::Matches($content, "<img\s+[^>]*>")
    foreach ($img in $imgTags) {
        if (-not ($img.Value -match "alt=")) {
            $issues += "图片缺少alt属性: $($img.Value)"
            $stats.IssuesByType["可访问性问题"]++
            $stats.IssuesFound++
        }
    }
    
    # 检查表单元素是否缺少标签
    $inputTags = [regex]::Matches($content, "<input\s+[^>]*>")
    foreach ($input in $inputTags) {
        # 检查是否有id属性但没有相关的label
        if ($input.Value -match "id=[""']([^""']*)[""']") {
            $id = $matches[1]
            if (-not ($content -match "<label\s+[^>]*for=[""']$id[""'][^>]*>")) {
                $issues += "表单元素缺少关联的label: $($input.Value)"
                $stats.IssuesByType["可访问性问题"]++
                $stats.IssuesFound++
            }
        }
    }
    
    # 检查颜色对比度问题（简化版）
    $colorPattern = "color:\s*#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})"
    $bgColorPattern = "background-color:\s*#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})"
    
    $colorMatches = [regex]::Matches($content, $colorPattern)
    $bgColorMatches = [regex]::Matches($content, $bgColorPattern)
    
    if ($colorMatches.Count -gt 0 -and $bgColorMatches.Count -gt 0) {
        # 简单检查：如果同时使用了颜色和背景色，但没有使用高对比度颜色
        $issues += "可能存在颜色对比度问题，建议检查文本和背景色的对比度"
        $stats.IssuesByType["可访问性问题"]++
        $stats.IssuesFound++
    }
    
    return $issues
}

# 检查SEO问题
function Check-SEO {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $issues = @()
    
    # 检查标题是否存在
    if (-not ($content -match "<title>.*?</title>")) {
        $issues += "缺少<title>标签"
        $stats.IssuesByType["SEO问题"]++
        $stats.IssuesFound++
    }
    
    # 检查meta描述
    if (-not ($content -match "<meta\s+[^>]*name=[""']description[""'][^>]*>")) {
        $issues += "缺少meta描述标签"
        $stats.IssuesByType["SEO问题"]++
        $stats.IssuesFound++
    }
    
    # 检查标题层次结构
    if (-not ($content -match "<h1")) {
        $issues += "缺少H1标题标签"
        $stats.IssuesByType["SEO问题"]++
        $stats.IssuesFound++
    }
    
    # 检查图片是否缺少alt属性（SEO角度）
    $imgTags = [regex]::Matches($content, "<img\s+[^>]*>")
    foreach ($img in $imgTags) {
        if (-not ($img.Value -match "alt=")) {
            $issues += "图片缺少alt属性（影响SEO）: $($img.Value)"
            $stats.IssuesByType["SEO问题"]++
            $stats.IssuesFound++
        }
    }
    
    # 检查链接是否有描述性文本
    $aTags = [regex]::Matches($content, "<a\s+[^>]*>([^<]*)</a>")
    foreach ($a in $aTags) {
        $linkText = $a.Groups[1].Value.Trim()
        if ($linkText -eq "" -or $linkText -eq "点击这里" -or $linkText -eq "这里" -or $linkText -eq "链接") {
            $issues += "链接缺少描述性文本: $($a.Value)"
            $stats.IssuesByType["SEO问题"]++
            $stats.IssuesFound++
        }
    }
    
    return $issues
}

# 检查响应式设计问题
function Check-ResponsiveDesign {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    $issues = @()
    
    # 检查viewport meta标签
    if (-not ($content -match "<meta\s+[^>]*name=[""']viewport[""'][^>]*>")) {
        $issues += "缺少viewport meta标签，可能影响移动设备显示"
        $stats.IssuesByType["响应式设计问题"]++
        $stats.IssuesFound++
    }
    
    # 检查固定宽度
    $fixedWidthPattern = "width:\s*\d+px"
    $fixedWidths = [regex]::Matches($content, $fixedWidthPattern)
    
    if ($fixedWidths.Count -gt 0) {
        $issues += "检测到固定宽度（px）样式，可能影响响应式设计: $($fixedWidths.Count) 处"
        $stats.IssuesByType["响应式设计问题"]++
        $stats.IssuesFound++
    }
    
    # 检查是否使用了媒体查询
    if (-not ($content -match "@media")) {
        $issues += "未检测到媒体查询(@media)，可能缺少响应式设计支持"
        $stats.IssuesByType["响应式设计问题"]++
        $stats.IssuesFound++
    }
    
    # 检查是否使用了百分比或弹性布局
    $flexPatterns = "display:\s*flex|display:\s*grid|width:\s*\d+%"
    if (-not ($content -match $flexPatterns)) {
        $issues += "未检测到弹性布局(flex/grid)或百分比宽度，可能缺少响应式设计支持"
        $stats.IssuesByType["响应式设计问题"]++
        $stats.IssuesFound++
    }
    
    return $issues
}

# 处理HTML文件
function Process-Files {
    $htmlFiles = Find-Files
    
    Write-Host "`n正在验证HTML文件..." -ForegroundColor Cyan
    
    foreach ($file in $htmlFiles) {
        $stats.FilesChecked++
        
        Write-Host "正在检查文件 $($stats.FilesChecked)/$($stats.FilesTotal): $file" -NoNewline
        
        # 收集所有问题
        $allIssues = @()
        $allIssues += Check-HtmlSyntax -FilePath $file
        $allIssues += Check-Accessibility -FilePath $file
        $allIssues += Check-SEO -FilePath $file
        $allIssues += Check-ResponsiveDesign -FilePath $file
        
        if ($allIssues.Count -gt 0) {
            # 发现问题
            $stats.FilesWithIssues++
            $issueFiles += $file
            $issueDetails[$file] = $allIssues -join "`n"
            
            Write-Host " - 发现 $($allIssues.Count) 个问题!" -ForegroundColor Yellow
        } else {
            Write-Host " - 通过" -ForegroundColor Green
        }
    }
    
    Write-Host "`nHTML验证完成。" -ForegroundColor Cyan
    Write-Host "有问题的文件: $($stats.FilesWithIssues)" -ForegroundColor $(if ($stats.FilesWithIssues -gt 0) { "Yellow" } else { "Green" })
    Write-Host "发现的问题总数: $($stats.IssuesFound)" -ForegroundColor $(if ($stats.IssuesFound -gt 0) { "Yellow" } else { "Green" })
    
    # 显示问题类型统计
    Write-Host "`n问题类型统计:" -ForegroundColor Cyan
    foreach ($type in $stats.IssuesByType.Keys) {
        $count = $stats.IssuesByType[$type]
        Write-Host "  $type`: $count" -ForegroundColor $(if ($count -gt 0) { "Yellow" } else { "Green" })
    }
}

# 生成HTML报告
function Generate-Report {
    $reportFile = Join-Path -Path $config.ReportDir -ChildPath ("html_validation_report_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss") + ".html")
    
    $html = @"
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML验证报告</title>
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
            border-left: 4px solid #FF9800;
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
        .issue-type {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 3px;
            display: inline-block;
            color: white;
        }
        .syntax { background-color: #F44336; }
        .accessibility { background-color: #FF9800; }
        .seo { background-color: #4CAF50; }
        .responsive { background-color: #2196F3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>HTML验证报告</h1>
        <p>生成时间: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>
        
        <div class="summary">
            <h2>摘要</h2>
            <table class="stats-table">
                <tr>
                    <th>文件总数</th>
                    <th>已检查</th>
                    <th>有问题</th>
                    <th>问题总数</th>
                </tr>
                <tr>
                    <td>$($stats.FilesTotal)</td>
                    <td>$($stats.FilesChecked)</td>
                    <td>$($stats.FilesWithIssues)</td>
                    <td>$($stats.IssuesFound)</td>
                </tr>
            </table>
            
            <h3>问题类型分布</h3>
            <table class="stats-table">
                <tr>
                    <th>问题类型</th>
                    <th>数量</th>
                </tr>
"@
    
    foreach ($type in $stats.IssuesByType.Keys) {
        $count = $stats.IssuesByType[$type]
        $cssClass = switch ($type) {
            "语法错误" { "syntax" }
            "可访问性问题" { "accessibility" }
            "SEO问题" { "seo" }
            "响应式设计问题" { "responsive" }
            default { "" }
        }
        
        $html += @"
                <tr>
                    <td><span class="issue-type $cssClass">$type</span></td>
                    <td>$count</td>
                </tr>
"@
    }
    
    $html += @"
            </table>
        </div>
"@
    
    # 有问题的文件
    if ($issueFiles.Count -gt 0) {
        $html += @"
        <div class="issue-files">
            <h2>有问题的文件</h2>
            <ul class="file-list">
"@
        
        $displayCount = 0
        foreach ($file in $issueFiles) {
            $displayCount++
            if ($displayCount -gt $config.MaxFilesToDisplay) {
                $html += "<li>... 以及其他 $($issueFiles.Count - $config.MaxFilesToDisplay) 个文件</li>"
                break
            }
            
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
            <h2>改进建议</h2>
            <h3>语法问题</h3>
            <ul>
                <li>确保所有HTML标签正确闭合</li>
                <li>使用HTML验证器检查文档的有效性</li>
                <li>确保属性值使用引号</li>
                <li>添加正确的DOCTYPE声明</li>
            </ul>
            
            <h3>可访问性问题</h3>
            <ul>
                <li>为所有图片添加有意义的alt属性</li>
                <li>确保表单元素有关联的label</li>
                <li>使用足够的颜色对比度</li>
                <li>确保页面可以通过键盘导航</li>
            </ul>
            
            <h3>SEO问题</h3>
            <ul>
                <li>添加描述性的title标签</li>
                <li>使用meta描述标签</li>
                <li>正确使用标题层次结构（H1-H6）</li>
                <li>为链接使用描述性文本</li>
            </ul>
            
            <h3>响应式设计问题</h3>
            <ul>
                <li>添加viewport meta标签</li>
                <li>使用相对单位（%、em、rem）而不是固定像素</li>
                <li>实现媒体查询以适应不同屏幕尺寸</li>
                <li>考虑使用弹性布局（Flexbox或Grid）</li>
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
Write-Host "正在启动HTML验证工具..." -ForegroundColor Cyan

# 处理文件
Process-Files

# 生成报告
Generate-Report

Write-Host "`nHTML验证完成!" -ForegroundColor Green