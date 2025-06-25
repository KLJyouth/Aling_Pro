# 整理根目录下剩余的文件

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
    
    # 如果源文件存在
    if (Test-Path -Path $SourcePath) {
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
    else {
        Write-Host "Source path does not exist: $SourcePath" -ForegroundColor Yellow
    }
}

# 创建备份目录
$backupDir = "backups\final_files_cleanup"
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
$configDir = "public\config"

# 处理MD文档文件
Write-Host "Processing Markdown files..." -ForegroundColor Cyan

$reportFiles = @(
    "COMPREHENSIVE_FIX_STRATEGY.md",
    "COMPREHENSIVE_SYSTEM_FIX_AND_AGENT.md",
    "DEPLOYMENT_OPERATIONS_GUIDE.md",
    "DEVELOPER_INTEGRATION_GUIDE.md",
    "FINAL_DELIVERY_CHECKLIST.md",
    "FINAL_FIXING_COMPLETION_CONFIRMATION.md",
    "FINAL_PROJECT_COMPLETION_REPORT_2025_06_15.md",
    "FINAL_PROJECT_DELIVERY_CONFIRMATION.md",
    "FINAL_PROJECT_DELIVERY_REPORT.md",
    "FINAL_TEST_FIX_COMPLETION_REPORT.md",
    "PHP_CODE_FIX_COMPLETION_REPORT_2025_06_15.md",
    "SM4_ENGINE_OPTIMIZATION_REPORT.md",
    "SM4_OPTIMIZATION_RECOMMENDATIONS.md",
    "SYSTEM_FIX_COMPLETION_REPORT_GCM.md",
    "SYSTEM_FIX_COMPLETION_REPORT.md",
    "SYSTEMMONITOR_CONTROLLER_FIX_COMPLETION_REPORT.md"
)

foreach ($file in $reportFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$docsDir\$file" -BackupDir $backupDir
    }
}

# 将开发指南移动到guides目录
$guideFiles = @(
    "DEPLOYMENT_OPERATIONS_GUIDE.md",
    "DEVELOPER_INTEGRATION_GUIDE.md"
)

foreach ($file in $guideFiles) {
    $sourcePath = "$docsDir\$file"
    if (Test-Path -Path $sourcePath) {
        Move-FileWithBackup -SourcePath $sourcePath -DestinationPath "$guidesDir\$file" -BackupDir $backupDir
    }
}

# 处理PHP文件
Write-Host "Processing PHP files..." -ForegroundColor Cyan

$testPhpFiles = @(
    "debug_app.php",
    "demo_quantum_encryption_final.php",
    "final_api_test_report.php",
    "fix_error_tracker.php",
    "quantum_system_verification.php",
    "simple_test.php"
)

foreach ($file in $testPhpFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$testDir\$file" -BackupDir $backupDir
    }
}

# 处理配置文件
Write-Host "Processing configuration files..." -ForegroundColor Cyan

$configFiles = @(
    "composer.json",
    "composer.lock",
    "php.ini.production"
)

foreach ($file in $configFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$configDir\$file" -BackupDir $backupDir
    }
}

# 处理图片文件
if (Test-Path -Path "image.png") {
    $imagesDir = "public\assets\images"
    if (-not (Test-Path -Path $imagesDir)) {
        New-Item -Path $imagesDir -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: $imagesDir"
    }
    Move-FileWithBackup -SourcePath "image.png" -DestinationPath "$imagesDir\image.png" -BackupDir $backupDir
}

# 处理文本文件
if (Test-Path -Path "fix_report.txt") {
    $reportsDir = "public\docs\reports"
    Move-FileWithBackup -SourcePath "fix_report.txt" -DestinationPath "$reportsDir\fix_report.txt" -BackupDir $backupDir
}

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $backupDir" -ForegroundColor Green 