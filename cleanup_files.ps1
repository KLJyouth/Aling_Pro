# 清理已移动的原始文件

# 删除函数，会检查备份是否存在，如果存在才删除
function Remove-FileIfBackedUp {
    param (
        [string]$SourcePath
    )
    
    # 如果源文件存在
    if (Test-Path -Path $SourcePath) {
        # 检查备份
        $backupDir = "backups\root_cleanup"
        $fileName = Split-Path -Path $SourcePath -Leaf
        $backupPath = Join-Path -Path $backupDir -ChildPath $fileName
        
        if (Test-Path -Path $backupPath) {
            if ((Get-Item $SourcePath) -is [System.IO.DirectoryInfo]) {
                # 对于目录，递归删除
                Remove-Item -Path $SourcePath -Recurse -Force
                Write-Host "Deleted directory: $SourcePath" -ForegroundColor Green
            } else {
                # 对于文件，直接删除
                Remove-Item -Path $SourcePath -Force
                Write-Host "Deleted file: $SourcePath" -ForegroundColor Green
            }
        } else {
            Write-Host "Backup not found for: $SourcePath, skipping deletion" -ForegroundColor Yellow
        }
    } else {
        Write-Host "Source path not found: $SourcePath" -ForegroundColor Yellow
    }
}

Write-Host "Cleaning up files that have been moved..." -ForegroundColor Cyan

# 1. 删除已移动的文档文件
Write-Host "Cleaning up documentation files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "DOCUMENTATION_REORGANIZATION_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "ALING_AI_PRO_DOCUMENTATION_PLAN.md"
Remove-FileIfBackedUp -SourcePath "directory_structure.md"
Remove-FileIfBackedUp -SourcePath "SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "README.md"
Remove-FileIfBackedUp -SourcePath "IMPLEMENTATION_STEPS.md"
Remove-FileIfBackedUp -SourcePath "IMPLEMENTATION_PLAN.md"
Remove-FileIfBackedUp -SourcePath "docs"

# 2. 删除已移动的管理文件
Write-Host "Cleaning up admin files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "admin-setup.md"
Remove-FileIfBackedUp -SourcePath "admin-center-setup.md"
Remove-FileIfBackedUp -SourcePath "ADMIN_CENTER_IMPLEMENTATION_PLAN.md"
# admin-center和admin目录已经合并，但保留原目录以备检查

# 3. 删除已移动的工具文件
Write-Host "Cleaning up tool files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "fix_scripts"
Remove-FileIfBackedUp -SourcePath "scripts"
Remove-FileIfBackedUp -SourcePath "fix_php_simple.bat"
Remove-FileIfBackedUp -SourcePath "fix_php_simple.php"
Remove-FileIfBackedUp -SourcePath "fix_php_file.bat"
Remove-FileIfBackedUp -SourcePath "fix_php_syntax_errors.php"
Remove-FileIfBackedUp -SourcePath "fix_bom_markers.ps1"
Remove-FileIfBackedUp -SourcePath "run_fix_bom.bat"
Remove-FileIfBackedUp -SourcePath "fix_bom_markers.php"
Remove-FileIfBackedUp -SourcePath "fix_php81_remaining_errors.php"
Remove-FileIfBackedUp -SourcePath "fix_chinese_tokenizer_unicode.php"
Remove-FileIfBackedUp -SourcePath "fix_tokenizer.php"
Remove-FileIfBackedUp -SourcePath "fix_chinese_tokenizer_utf8.php"
Remove-FileIfBackedUp -SourcePath "fix_php81_syntax_manual.bat"
Remove-FileIfBackedUp -SourcePath "fix_english_tokenizer.php"
Remove-FileIfBackedUp -SourcePath "fix_php_syntax.bat"
Remove-FileIfBackedUp -SourcePath "fix_chinese_tokenizer.php"
Remove-FileIfBackedUp -SourcePath "fix_common_php_errors.php"
Remove-FileIfBackedUp -SourcePath "fix_php81_syntax_errors.php"
Remove-FileIfBackedUp -SourcePath "fix_specific_php_errors.php"
Remove-FileIfBackedUp -SourcePath "comprehensive_php_error_fix.php"
Remove-FileIfBackedUp -SourcePath "fix_remaining_php_errors.php"
Remove-FileIfBackedUp -SourcePath "run_eslint_fix.bat"
Remove-FileIfBackedUp -SourcePath "run_php_linter_fix.bat"
Remove-FileIfBackedUp -SourcePath "eslint.config.js"

# 4. 删除已移动的配置文件
Write-Host "Cleaning up config files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "config"

# 5. 删除已移动的报告文件
Write-Host "Cleaning up report files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "PHP_BOM_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "PHP81_SYNTAX_FIX_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "PHP81_REMAINING_ERRORS_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "FINAL_PHP81_SYNTAX_FIX_REPORT_NEW.md"
Remove-FileIfBackedUp -SourcePath "CHINESE_TOKENIZER_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "FINAL_PHP81_SYNTAX_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "PHP_8.1_FIX_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "MANUAL_PHP81_FIX_GUIDE_NEW.md"
Remove-FileIfBackedUp -SourcePath "PHP81_SYNTAX_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "PHP81_SYNTAX_FIX_EXECUTION_REPORT.md"
Remove-FileIfBackedUp -SourcePath "FINAL_PHP81_FIX_STEPS.md"
Remove-FileIfBackedUp -SourcePath "PHP81_SYNTAX_ERROR_FIX_PLAN.md"
Remove-FileIfBackedUp -SourcePath "MANUAL_PHP81_FIX_GUIDE.md"
Remove-FileIfBackedUp -SourcePath "PHP81_SYNTAX_FIX_PLAN.md"
Remove-FileIfBackedUp -SourcePath "PHP81_SYNTAX_FIX_GUIDE.md"
Remove-FileIfBackedUp -SourcePath "LINTER_FIX_README.md"
Remove-FileIfBackedUp -SourcePath "UNICODE_ENCODING_RECOMMENDATION.md"

# 6. 删除已移动的指南文件
Write-Host "Cleaning up guide files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "DOCUSAURUS_SETUP_GUIDE.md"
Remove-FileIfBackedUp -SourcePath "MANUAL_REORGANIZATION_STEPS.md"
Remove-FileIfBackedUp -SourcePath "docs-setup.md"
Remove-FileIfBackedUp -SourcePath "manual_steps.md"
Remove-FileIfBackedUp -SourcePath "STEP_BY_STEP_MANUAL_REORGANIZATION.md"
Remove-FileIfBackedUp -SourcePath "REORGANIZATION_IMPLEMENTATION_GUIDE.md"
Remove-FileIfBackedUp -SourcePath "MANUAL_REORGANIZATION_INSTRUCTIONS.md"
Remove-FileIfBackedUp -SourcePath "NEXT_STEPS_AFTER_REORGANIZATION.md"
Remove-FileIfBackedUp -SourcePath "NEXT_STEPS_PLAN.md"

# 7. 清理可以删除的临时文件
Write-Host "Cleaning up temporary files..." -ForegroundColor Cyan
if (Test-Path -Path "temp.php") {
    Remove-Item -Path "temp.php" -Force
    Write-Host "Deleted temporary file: temp.php" -ForegroundColor Green
}

Write-Host "Cleanup completed!" -ForegroundColor Green
Write-Host "Remember that all original files have backups in backups\root_cleanup directory" -ForegroundColor Green 