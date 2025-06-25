# 清理已移动的原始文件

# 删除函数，会检查备份是否存在，如果存在才删除
function Remove-FileIfBackedUp {
    param (
        [string]$SourcePath
    )
    
    # 如果源文件存在
    if (Test-Path -Path $SourcePath) {
        # 检查备份
        $backupDir = "backups\root_cleanup2"
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

# 1. 删除已移动的服务器相关文件
Write-Host "Cleaning up server files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "run_tests.bat"
Remove-FileIfBackedUp -SourcePath "phpunit.xml.dist"
Remove-FileIfBackedUp -SourcePath "run_server.bat"
Remove-FileIfBackedUp -SourcePath "server.bat"
Remove-FileIfBackedUp -SourcePath "start_server.bat"
Remove-FileIfBackedUp -SourcePath "start_server_new.bat"

# 2. 删除已移动的错误修复和安全相关文件
Write-Host "Cleaning up fix files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "fix_interface_implementation.php"
Remove-FileIfBackedUp -SourcePath "fix_php_errors.php"
Remove-FileIfBackedUp -SourcePath "fix_advanced_attack_surface_management.php"
Remove-FileIfBackedUp -SourcePath "fix_stub_methods.php"
Remove-FileIfBackedUp -SourcePath "comprehensive_fix.php"
Remove-FileIfBackedUp -SourcePath "debug_autoloader.php"
Remove-FileIfBackedUp -SourcePath "final_example_replacer.php"
Remove-FileIfBackedUp -SourcePath "comprehensive_example_replacer.php"
Remove-FileIfBackedUp -SourcePath "replace_examples.php"
Remove-FileIfBackedUp -SourcePath "fix_api_endpoints.php"

# 3. 删除已移动的测试工具
Write-Host "Cleaning up test files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "simple_chat_test.php"
Remove-FileIfBackedUp -SourcePath "test_chat_system.php"
Remove-FileIfBackedUp -SourcePath "test_api_endpoints.php"
Remove-FileIfBackedUp -SourcePath "test_sqlite.php"
Remove-FileIfBackedUp -SourcePath "test_php.php"
Remove-FileIfBackedUp -SourcePath "test.php"

# 4. 删除已移动的报告和文档文件
Write-Host "Cleaning up report files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "TESTING_IMPLEMENTATION_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "ENHANCED_CHAT_API_FIX_REPORT.txt"
Remove-FileIfBackedUp -SourcePath "AUTH_API_FIX_REPORT.txt"
Remove-FileIfBackedUp -SourcePath "FINAL_PHP_ERROR_FIX_REPORT.txt"
Remove-FileIfBackedUp -SourcePath "FINAL_PHP_ERROR_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "PHP_ERRORS_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "AdvancedAttackSurfaceManagement_fix_report.md"
Remove-FileIfBackedUp -SourcePath "AASM_Fix_Instructions.md"
Remove-FileIfBackedUp -SourcePath "fix_AdvancedAttackSurfaceManagement.md"
Remove-FileIfBackedUp -SourcePath "FINAL_COMPREHENSIVE_FIX_REPORT.md"
Remove-FileIfBackedUp -SourcePath "CHAT_SYSTEM_IMPLEMENTATION_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "EXAMPLE_IMPLEMENTATION_UPGRADE_REPORT.md"
Remove-FileIfBackedUp -SourcePath "SECURITY_IMPLEMENTATION_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "api_endpoints_fix_summary.md"
Remove-FileIfBackedUp -SourcePath "IMPLEMENTATION_TRACKING.md"
Remove-FileIfBackedUp -SourcePath "PROJECT_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "PUBLIC_FOLDER_UPGRADE_PLAN.md"
Remove-FileIfBackedUp -SourcePath "OPTIMIZATION_SUMMARY.md"
Remove-FileIfBackedUp -SourcePath "ALINGAI_UPGRADE_ENHANCEMENT_PLAN.md"
Remove-FileIfBackedUp -SourcePath "FILE_CLASSIFICATION_TABLE.md"

# 5. 删除已移动的配置文件
Write-Host "Cleaning up config files..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "php.ini.local"
Remove-FileIfBackedUp -SourcePath "php_extensions.ini"
# phpunit.xml.dist 已在第1步删除

# 6. 删除已移动的开发工具
Write-Host "Cleaning up dev tools..." -ForegroundColor Cyan
Remove-FileIfBackedUp -SourcePath "server_router.php"
Remove-FileIfBackedUp -SourcePath "router.php"
Remove-FileIfBackedUp -SourcePath "stubs.php"
Remove-FileIfBackedUp -SourcePath "output.json"
Remove-FileIfBackedUp -SourcePath "project_progress_temp.txt"

Write-Host "Cleanup completed!" -ForegroundColor Green
Write-Host "Remember that all original files have backups in backups\root_cleanup2 directory" -ForegroundColor Green 