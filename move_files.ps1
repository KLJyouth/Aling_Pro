# 文件移动PowerShell脚本

# 创建函数，移动文件并备份原始文件
function Move-FileWithBackup {
    param (
        [string]$SourcePath,
        [string]$DestinationPath
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
        $backupDir = "backups\root_cleanup"
        $fileName = Split-Path -Path $SourcePath -Leaf
        $backupPath = Join-Path -Path $backupDir -ChildPath $fileName
        
        # 如果是目录，则进行递归复制
        if ((Get-Item $SourcePath) -is [System.IO.DirectoryInfo]) {
            # 对于目录，我们需要保留目录结构
            $sourceDirName = Split-Path -Path $SourcePath -Leaf
            $backupDirPath = Join-Path -Path $backupDir -ChildPath $sourceDirName
            
            # 备份目录
            if (-not (Test-Path -Path $backupDirPath)) {
                New-Item -Path $backupDirPath -ItemType Directory -Force | Out-Null
            }
            Copy-Item -Path "$SourcePath\*" -Destination $backupDirPath -Recurse -Force
            Write-Host "Backed up directory: $SourcePath to $backupDirPath"
            
            # 复制到目标位置
            if (-not (Test-Path -Path $DestinationPath)) {
                New-Item -Path $DestinationPath -ItemType Directory -Force | Out-Null
            }
            Copy-Item -Path "$SourcePath\*" -Destination $DestinationPath -Recurse -Force
            Write-Host "Moved directory: $SourcePath to $DestinationPath"
        }
        else {
            # 对于文件，直接复制
            Copy-Item -Path $SourcePath -Destination $backupPath -Force
            Write-Host "Backed up file: $SourcePath to $backupPath"
            
            # 移动到目标位置
            Copy-Item -Path $SourcePath -Destination $DestinationPath -Force
            Write-Host "Moved file: $SourcePath to $DestinationPath"
        }
    }
    else {
        Write-Host "Source path does not exist: $SourcePath" -ForegroundColor Yellow
    }
}

# 1. 移动文档文件到public/docs/
Write-Host "Moving documentation files to public/docs/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "DOCUMENTATION_REORGANIZATION_SUMMARY.md" -DestinationPath "public\docs\reorganization_summary.md"
Move-FileWithBackup -SourcePath "ALING_AI_PRO_DOCUMENTATION_PLAN.md" -DestinationPath "public\docs\documentation_plan.md"
Move-FileWithBackup -SourcePath "directory_structure.md" -DestinationPath "public\docs\directory_structure.md"
Move-FileWithBackup -SourcePath "SUMMARY.md" -DestinationPath "public\docs\project_summary.md"
Move-FileWithBackup -SourcePath "README.md" -DestinationPath "public\docs\readme.md"
Move-FileWithBackup -SourcePath "IMPLEMENTATION_STEPS.md" -DestinationPath "public\docs\implementation_steps.md"
Move-FileWithBackup -SourcePath "IMPLEMENTATION_PLAN.md" -DestinationPath "public\docs\implementation_plan.md"
Move-FileWithBackup -SourcePath "docs" -DestinationPath "public\docs"

# 2. 移动管理文件到public/admin/
Write-Host "Moving admin files to public/admin/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "admin-setup.md" -DestinationPath "public\admin\docs\admin_setup.md"
Move-FileWithBackup -SourcePath "admin-center-setup.md" -DestinationPath "public\admin\docs\admin_center_setup.md"
Move-FileWithBackup -SourcePath "ADMIN_CENTER_IMPLEMENTATION_PLAN.md" -DestinationPath "public\admin\docs\implementation_plan.md"
# admin-center和admin目录下的文件已经合并到public/admin，不需要再次移动

# 3. 移动工具文件到public/tools/
Write-Host "Moving tool files to public/tools/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "fix_scripts" -DestinationPath "public\tools\fixes"
Move-FileWithBackup -SourcePath "scripts" -DestinationPath "public\scripts"
Move-FileWithBackup -SourcePath "fix_php_simple.bat" -DestinationPath "public\tools\fixes\fix_php_simple.bat"
Move-FileWithBackup -SourcePath "fix_php_simple.php" -DestinationPath "public\tools\fixes\fix_php_simple.php"
Move-FileWithBackup -SourcePath "fix_php_file.bat" -DestinationPath "public\tools\fixes\fix_php_file.bat"
Move-FileWithBackup -SourcePath "fix_php_syntax_errors.php" -DestinationPath "public\tools\fixes\fix_php_syntax_errors.php"
Move-FileWithBackup -SourcePath "fix_bom_markers.ps1" -DestinationPath "public\tools\fixes\fix_bom_markers.ps1"
Move-FileWithBackup -SourcePath "run_fix_bom.bat" -DestinationPath "public\tools\fixes\run_fix_bom.bat"
Move-FileWithBackup -SourcePath "fix_bom_markers.php" -DestinationPath "public\tools\fixes\fix_bom_markers.php"
Move-FileWithBackup -SourcePath "fix_php81_remaining_errors.php" -DestinationPath "public\tools\fixes\fix_php81_remaining_errors.php"
Move-FileWithBackup -SourcePath "fix_chinese_tokenizer_unicode.php" -DestinationPath "public\tools\fixes\fix_chinese_tokenizer_unicode.php"
Move-FileWithBackup -SourcePath "fix_tokenizer.php" -DestinationPath "public\tools\fixes\fix_tokenizer.php"
Move-FileWithBackup -SourcePath "fix_chinese_tokenizer_utf8.php" -DestinationPath "public\tools\fixes\fix_chinese_tokenizer_utf8.php"
Move-FileWithBackup -SourcePath "fix_php81_syntax_manual.bat" -DestinationPath "public\tools\fixes\fix_php81_syntax_manual.bat"
Move-FileWithBackup -SourcePath "fix_english_tokenizer.php" -DestinationPath "public\tools\fixes\fix_english_tokenizer.php"
Move-FileWithBackup -SourcePath "fix_php_syntax.bat" -DestinationPath "public\tools\fixes\fix_php_syntax.bat"
Move-FileWithBackup -SourcePath "fix_chinese_tokenizer.php" -DestinationPath "public\tools\fixes\fix_chinese_tokenizer.php"
Move-FileWithBackup -SourcePath "fix_common_php_errors.php" -DestinationPath "public\tools\fixes\fix_common_php_errors.php"
Move-FileWithBackup -SourcePath "fix_php81_syntax_errors.php" -DestinationPath "public\tools\fixes\fix_php81_syntax_errors.php"
Move-FileWithBackup -SourcePath "fix_specific_php_errors.php" -DestinationPath "public\tools\fixes\fix_specific_php_errors.php"
Move-FileWithBackup -SourcePath "comprehensive_php_error_fix.php" -DestinationPath "public\tools\fixes\comprehensive_php_error_fix.php"
Move-FileWithBackup -SourcePath "fix_remaining_php_errors.php" -DestinationPath "public\tools\fixes\fix_remaining_php_errors.php"
Move-FileWithBackup -SourcePath "run_eslint_fix.bat" -DestinationPath "public\tools\fixes\run_eslint_fix.bat"
Move-FileWithBackup -SourcePath "run_php_linter_fix.bat" -DestinationPath "public\tools\fixes\run_php_linter_fix.bat"
Move-FileWithBackup -SourcePath "eslint.config.js" -DestinationPath "public\tools\fixes\eslint.config.js"

# 4. 移动配置文件到public/config/
Write-Host "Moving config files to public/config/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "config" -DestinationPath "public\config"

# 5. 移动报告文件到public/docs/reports/
Write-Host "Moving report files to public/docs/reports/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "PHP_BOM_FIX_REPORT.md" -DestinationPath "public\docs\reports\php_bom_fix_report.md"
Move-FileWithBackup -SourcePath "PHP81_SYNTAX_FIX_SUMMARY.md" -DestinationPath "public\docs\reports\php81_syntax_fix_summary.md"
Move-FileWithBackup -SourcePath "PHP81_REMAINING_ERRORS_FIX_REPORT.md" -DestinationPath "public\docs\reports\php81_remaining_errors_fix_report.md"
Move-FileWithBackup -SourcePath "FINAL_PHP81_SYNTAX_FIX_REPORT_NEW.md" -DestinationPath "public\docs\reports\final_php81_syntax_fix_report_new.md"
Move-FileWithBackup -SourcePath "CHINESE_TOKENIZER_FIX_REPORT.md" -DestinationPath "public\docs\reports\chinese_tokenizer_fix_report.md"
Move-FileWithBackup -SourcePath "FINAL_PHP81_SYNTAX_FIX_REPORT.md" -DestinationPath "public\docs\reports\final_php81_syntax_fix_report.md"
Move-FileWithBackup -SourcePath "PHP_8.1_FIX_SUMMARY.md" -DestinationPath "public\docs\reports\php_8.1_fix_summary.md"
Move-FileWithBackup -SourcePath "MANUAL_PHP81_FIX_GUIDE_NEW.md" -DestinationPath "public\docs\reports\manual_php81_fix_guide_new.md"
Move-FileWithBackup -SourcePath "PHP81_SYNTAX_FIX_REPORT.md" -DestinationPath "public\docs\reports\php81_syntax_fix_report.md"
Move-FileWithBackup -SourcePath "PHP81_SYNTAX_FIX_EXECUTION_REPORT.md" -DestinationPath "public\docs\reports\php81_syntax_fix_execution_report.md"
Move-FileWithBackup -SourcePath "FINAL_PHP81_FIX_STEPS.md" -DestinationPath "public\docs\reports\final_php81_fix_steps.md"
Move-FileWithBackup -SourcePath "PHP81_SYNTAX_ERROR_FIX_PLAN.md" -DestinationPath "public\docs\reports\php81_syntax_error_fix_plan.md"
Move-FileWithBackup -SourcePath "MANUAL_PHP81_FIX_GUIDE.md" -DestinationPath "public\docs\reports\manual_php81_fix_guide.md"
Move-FileWithBackup -SourcePath "PHP81_SYNTAX_FIX_PLAN.md" -DestinationPath "public\docs\reports\php81_syntax_fix_plan.md"
Move-FileWithBackup -SourcePath "PHP81_SYNTAX_FIX_GUIDE.md" -DestinationPath "public\docs\reports\php81_syntax_fix_guide.md"
Move-FileWithBackup -SourcePath "LINTER_FIX_README.md" -DestinationPath "public\docs\reports\linter_fix_readme.md"
Move-FileWithBackup -SourcePath "UNICODE_ENCODING_RECOMMENDATION.md" -DestinationPath "public\docs\reports\unicode_encoding_recommendation.md"

# 6. 移动指南文件到public/docs/guides/
Write-Host "Moving guide files to public/docs/guides/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "DOCUSAURUS_SETUP_GUIDE.md" -DestinationPath "public\docs\guides\docusaurus_setup_guide.md"
Move-FileWithBackup -SourcePath "MANUAL_REORGANIZATION_STEPS.md" -DestinationPath "public\docs\guides\manual_reorganization_steps.md"
Move-FileWithBackup -SourcePath "docs-setup.md" -DestinationPath "public\docs\guides\docs_setup.md"
Move-FileWithBackup -SourcePath "manual_steps.md" -DestinationPath "public\docs\guides\manual_steps.md"
Move-FileWithBackup -SourcePath "STEP_BY_STEP_MANUAL_REORGANIZATION.md" -DestinationPath "public\docs\guides\step_by_step_manual_reorganization.md"
Move-FileWithBackup -SourcePath "REORGANIZATION_IMPLEMENTATION_GUIDE.md" -DestinationPath "public\docs\guides\reorganization_implementation_guide.md"
Move-FileWithBackup -SourcePath "MANUAL_REORGANIZATION_INSTRUCTIONS.md" -DestinationPath "public\docs\guides\manual_reorganization_instructions.md"
Move-FileWithBackup -SourcePath "NEXT_STEPS_AFTER_REORGANIZATION.md" -DestinationPath "public\docs\guides\next_steps_after_reorganization.md"
Move-FileWithBackup -SourcePath "NEXT_STEPS_PLAN.md" -DestinationPath "public\docs\guides\next_steps_plan.md"

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to backups\root_cleanup" -ForegroundColor Green 