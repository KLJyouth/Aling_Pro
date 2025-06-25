# 整理根目录下的md、bat和php文件

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
$backupDir = "backups\root_files_cleanup"
if (-not (Test-Path -Path $backupDir)) {
    New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
    Write-Host "Created backup directory: $backupDir"
}

# 确保目标目录存在
$docsDir = "public\docs\reports"
$guidesDir = "public\docs\guides"
$toolsDir = "public\tools"
$adminToolsDir = "public\admin\maintenance\tools"
$configDir = "public\config"

if (-not (Test-Path -Path $docsDir)) {
    New-Item -Path $docsDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $docsDir"
}

if (-not (Test-Path -Path $guidesDir)) {
    New-Item -Path $guidesDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $guidesDir"
}

if (-not (Test-Path -Path $toolsDir)) {
    New-Item -Path $toolsDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $toolsDir"
}

if (-not (Test-Path -Path $adminToolsDir)) {
    New-Item -Path $adminToolsDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $adminToolsDir"
}

if (-not (Test-Path -Path $configDir)) {
    New-Item -Path $configDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $configDir"
}

# 处理根目录下的Markdown文档
Write-Host "Processing Markdown files..." -ForegroundColor Cyan

# 项目计划和报告文档移动到public/docs/reports
$planReportFiles = @(
    "ALINGAI_UPGRADE_IMPLEMENTATION_PLAN.md",
    "AI_ENGINE_NLP_UPGRADE_DETAILS.md",
    "AI_ENGINE_SPEECH_UPGRADE_DETAILS.md",
    "AI_ENGINE_CV_UPGRADE_DETAILS.md",
    "ALINGAI_UPGRADE_PLAN.md",
    "AI_ENGINE_KNOWLEDGE_GRAPH_UPGRADE_DETAILS.md",
    "ALINGAI_PRO_UPGRADE_PLAN.md",
    "COMPREHENSIVE_UPGRADE_PLAN.md",
    "COMPLETION_REPORT.md",
    "CODE_PRIORITY_REPORT.md",
    "FINAL_RECOVERY_REPORT.md",
    "PROJECT_INTEGRITY_REPORT.md",
    "SRC_FILES_RECOVERY_REPORT.md"
)

foreach ($file in $planReportFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$docsDir\$file" -BackupDir $backupDir
    }
}

# 文件组织和清理相关文档移动到public/docs/reports
$organizationFiles = @(
    "FILE_ORGANIZATION_PLAN.md",
    "ROOT_CLEANUP_SUMMARY.md",
    "ADDITIONAL_FILE_MOVE_PLAN.md",
    "ADDITIONAL_CLEANUP_SUMMARY.md",
    "ADMIN_MERGE_SUMMARY.md",
    "FINAL_CLEANUP_SUMMARY.md"
)

foreach ($file in $organizationFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$docsDir\$file" -BackupDir $backupDir
    }
}

# 项目结构和状态文档移动到项目根目录保留（不移动）
$rootDocFiles = @(
    "PROJECT_STRUCTURE.md",
    "PROJECT_STATUS.md"
)

# PHP兼容性文档移动到public/docs/guides
if (Test-Path -Path "PHP81_COMPATIBILITY.md") {
    Move-FileWithBackup -SourcePath "PHP81_COMPATIBILITY.md" -DestinationPath "$guidesDir\PHP81_COMPATIBILITY.md" -BackupDir $backupDir
}

# 处理根目录下的PHP文件
Write-Host "Processing PHP files..." -ForegroundColor Cyan

# 工具类PHP文件移动到public/tools
$toolPhpFiles = @(
    "analyze_project.php",
    "complete_file_structure.php",
    "generate_autoload.php",
    "generate_empty_file_structure.php",
    "restore_empty_files.php",
    "restore_src_files.php",
    "php81_features_demo.php"
)

foreach ($file in $toolPhpFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$toolsDir\$file" -BackupDir $backupDir
    }
}

# 代码完成和修复相关PHP文件移动到public/admin/maintenance/tools
$adminToolPhpFiles = @(
    "run_all_completions.php",
    "complete_ai_files.php",
    "complete_security_files.php",
    "complete_core_files.php",
    "code_completion_plan.php",
    "run_all_fixes.php",
    "fix_admin_syntax.php",
    "fix_syntax.php.bak.20250618_034852",
    "fix_syntax_safety.php",
    "fix_fix_syntax.php"
)

foreach ($file in $adminToolPhpFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$adminToolsDir\$file" -BackupDir $backupDir
    }
}

# 日志文件移动到public/logs
$logDir = "public\logs"
if (-not (Test-Path -Path $logDir)) {
    New-Item -Path $logDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory: $logDir"
}

$logFiles = @(
    "all_completions.log",
    "code_completion.log",
    "ai_completion.log",
    "security_completion.log",
    "core_completion.log"
)

foreach ($file in $logFiles) {
    if (Test-Path -Path $file) {
        Move-FileWithBackup -SourcePath $file -DestinationPath "$logDir\$file" -BackupDir $backupDir
    }
}

# 配置文件移动到public/config
if (Test-Path -Path "autoload.php") {
    Move-FileWithBackup -SourcePath "autoload.php" -DestinationPath "$configDir\autoload.php" -BackupDir $backupDir
}

# 保留phpinfo.php在根目录（不移动）
# 保留setup_*.bat在根目录（不移动）
# 保留tools.bat, admin-tools.bat, docs.bat在根目录（不移动）
# 保留清理脚本在根目录（不移动）

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $backupDir" -ForegroundColor Green 