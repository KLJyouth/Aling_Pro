# 整理最后剩余的文件

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
$backupDir = "backups\last_files_cleanup"
if (-not (Test-Path -Path $backupDir)) {
    New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
    Write-Host "Created backup directory: $backupDir"
}

# 确保目标目录存在
$docsDir = "public\docs\reports"
$guidesDir = "public\docs\guides"
$configDir = "public\config"
$toolsDir = "public\tools"
$scriptsDir = "scripts"

# 处理MD文档文件
Write-Host "Processing Markdown files..." -ForegroundColor Cyan

$reportFiles = @(
    "FINAL_PROJECT_DELIVERY_CONFIRMATION_2025_06_15.md",
    "FINAL_FIXING_COMPLETION_CONFIRMATION_2025_06_15.md",
    "CODE_FIX_COMPLETION_REPORT_2025_06_15.md",
    "ALINGAI_PRO_6.0_FINAL_TECHNICAL_DOCUMENTATION.md",
    "COMPREHENSIVE_SYSTEM_FIX_AND_AGENT_PROMPT.md"
)

foreach ($file in $reportFiles) {
    if (Test-Path -Path $file) {
        if ((Get-Item $file).Length -eq 0) {
            # 如果是空文件，直接删除
            Remove-Item -Path $file -Force
            Write-Host "Deleted empty file: $file" -ForegroundColor Yellow
        } else {
            Move-FileWithBackup -SourcePath $file -DestinationPath "$docsDir\$file" -BackupDir $backupDir
        }
    }
}

# 处理JSON文件
Write-Host "Processing JSON files..." -ForegroundColor Cyan

if (Test-Path -Path "advanced_fix_report_2025_06_16_03_05_47.json") {
    $logsDir = "public\logs\reports"
    if (-not (Test-Path -Path $logsDir)) {
        New-Item -Path $logsDir -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: $logsDir"
    }
    Move-FileWithBackup -SourcePath "advanced_fix_report_2025_06_16_03_05_47.json" -DestinationPath "$logsDir\advanced_fix_report_2025_06_16_03_05_47.json" -BackupDir $backupDir
}

# 处理artisan文件
Write-Host "Processing artisan file..." -ForegroundColor Cyan

if (Test-Path -Path "artisan") {
    if (-not (Test-Path -Path $scriptsDir)) {
        New-Item -Path $scriptsDir -ItemType Directory -Force | Out-Null
        Write-Host "Created directory: $scriptsDir"
    }
    Move-FileWithBackup -SourcePath "artisan" -DestinationPath "$scriptsDir\artisan" -BackupDir $backupDir
}

# 创建artisan.bat启动脚本
Write-Host "Creating artisan.bat launcher..." -ForegroundColor Cyan

$artisanBatContent = @"
@echo off
php scripts\artisan %*
"@

Set-Content -Path "artisan.bat" -Value $artisanBatContent
Write-Host "Created artisan.bat launcher in root directory"

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $backupDir" -ForegroundColor Green 