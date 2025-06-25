# 整理根目录下剩余的PHP文件和MD文件

# 创建函数，移动文件并备份原始文件
function Move-FileWithBackup {
    param (
        [string]$SourcePath,
        [string]$DestinationPath,
        [string]$BackupDir
    )
    
    # 确保目标目录存在
    $destDir = Split-Path -Path $DestinationPath -Parent
    if (-not (Test-Path -Path $destDir)) {
        New-Item -Path $destDir -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: $destDir"
    }
    
    # 如果源文件存在且不是空文件
    if ((Test-Path -Path $SourcePath) -and (Get-Item $SourcePath).Length -gt 0) {
        # 创建备份
        if (-not (Test-Path -Path $BackupDir)) {
            New-Item -Path $BackupDir -ItemType Directory -Force | Out-Null
            Write-Host "Created backup directory: $BackupDir"
        }
        
        $fileName = Split-Path -Path $SourcePath -Leaf
        $backupPath = Join-Path -Path $BackupDir -ChildPath $fileName
        
        # 备份文件
        Copy-Item -Path $SourcePath -Destination $backupPath -Force
        Write-Host "Backed up file: $SourcePath to $backupPath"
        
        # 移动到目标位置
        Copy-Item -Path $SourcePath -Destination $DestinationPath -Force
        Write-Host "Moved file: $SourcePath to $DestinationPath"
        
        # 删除原始文件
        Remove-Item -Path $SourcePath -Force
        Write-Host "Deleted original file: $SourcePath"
    }
    elseif ((Test-Path -Path $SourcePath) -and (Get-Item $SourcePath).Length -eq 0) {
        # 如果是空文件，直接删除
        Remove-Item -Path $SourcePath -Force
        Write-Host "Deleted empty file: $SourcePath"
    }
    else {
        Write-Host "Source path does not exist: $SourcePath" -ForegroundColor Yellow
    }
}

# 创建备份目录
$backupDir = "backups\remaining_files_cleanup"
if (-not (Test-Path -Path $backupDir)) {
    New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
    Write-Host "Created backup directory: $backupDir"
}

# 确保目标目录存在
$docsDir = "public\docs\reports"
$guidesDir = "public\docs\guides"
$toolsDir = "public\tools"
$adminToolsDir = "public\admin\maintenance\tools"
$testDir = "public\tools\tests"

if (-not (Test-Path -Path $testDir)) {
    New-Item -Path $testDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $testDir"
}

# 处理根目录下的PHP测试文件
Write-Host "Processing PHP test files..." -ForegroundColor Cyan

$testPhpFiles = @(
    "php81_test.php",
    "php81_compatibility_test.php",
    "check_deprecated.php",
    "test_basic_api.php",
    "test_complete_api_system.php",
    "test_more_endpoints.php",
    "test_quantum_api.php",
    "test_simple_api.php",
    "test_cache_performance.php",
    "test_server.php",
    "test_simple.php",
    "test_sdk_system.php"
)

foreach ($file in $testPhpFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$testDir\$file" -BackupDir $backupDir
    }
}

# 处理根目录下的PHP工具文件
Write-Host "Processing PHP tool files..." -ForegroundColor Cyan

$toolPhpFiles = @(
    "complete_index_fixer.php",
    "comprehensive_advanced_fixer.php",
    "comprehensive_system_fixer.php",
    "final_cleanup_fixer.php",
    "special_syntax_fixer.php",
    "comprehensive_error_analysis.php",
    "diagnose_app.php",
    "diagnose_routes.php",
    "deep_performance_analysis.php",
    "performance_analysis.php"
)

foreach ($file in $toolPhpFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$toolsDir\$file" -BackupDir $backupDir
    }
}

# 处理根目录下的报告文档
Write-Host "Processing report documents..." -ForegroundColor Cyan

$reportFiles = @(
    "COMPREHENSIVE_SYSTEM_FIX_COMPLETION_REPORT_2025_06_16.md",
    "FINAL_FIXING_COMPLETION_CONFIRMATION_2025_06_16.md",
    "ALINGAI_PRO_API_COMPLETION_REPORT.md",
    "REDIS_ISSUE_FIX_REPORT.md",
    "API_OPTIMIZATION_COMPLETION_REPORT.md",
    "FINAL_COMPLETION_CONFIRMATION.md",
    "API_FIX_SUCCESS_REPORT.md",
    "HOMEPAGE_AS_INDEX_UPDATE_REPORT.md",
    "VERSION_TIMELINE_PROJECT_COMPLETION_REPORT.md",
    "SDK_DOWNLOAD_SYSTEM_COMPLETION_REPORT.md",
    "ALINGAI_PLATFORM_FINAL_DELIVERY_CONFIRMATION.md",
    "ALINGAI_PLATFORM_COMPREHENSIVE_COMPLETION_REPORT.md",
    "HOMEPAGE_FOOTER_COMPLETION_REPORT.md",
    "ALINGAI_PLATFORM_PAGES_COMPLETION_REPORT.md",
    "FINAL_PHP_CODE_FIX_COMPLETION_REPORT_2025_06_15.md",
    "QUANTUMRANDOMGENERATOR_FIX_COMPLETION_REPORT_2025_06_15.md",
    "COMPREHENSIVE_ERROR_FIX_REPORT_2025_06_15.md",
    "CRITICAL_ERRORS_FIX_COMPLETION_REPORT_2025_06_15.md",
    "CRITICAL_ERRORS_FIX_REPORT_2025_06_15.md"
)

foreach ($file in $reportFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$docsDir\$file" -BackupDir $backupDir
    }
}

# 处理配置文件
Write-Host "Processing configuration files..." -ForegroundColor Cyan

$configDir = "public\config"
if (Test-Path -Path "composer-minimal.json") {
    Move-FileWithBackup -SourcePath "composer-minimal.json" -DestinationPath "$configDir\composer-minimal.json" -BackupDir $backupDir
}

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $backupDir" -ForegroundColor Green 