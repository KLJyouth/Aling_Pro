# 额外文件移动PowerShell脚本

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
        $backupDir = "backups\root_cleanup2"
        if (-not (Test-Path -Path $backupDir)) {
            New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
            Write-Host "Created backup directory: $backupDir"
        }
        
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

# 1. 移动测试和服务器相关文件到public/tools/server/
Write-Host "Moving server files to public/tools/server/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "run_tests.bat" -DestinationPath "public\tools\server\run_tests.bat"
Move-FileWithBackup -SourcePath "phpunit.xml.dist" -DestinationPath "public\tools\server\phpunit.xml.dist"
Move-FileWithBackup -SourcePath "run_server.bat" -DestinationPath "public\tools\server\run_server.bat"
Move-FileWithBackup -SourcePath "server.bat" -DestinationPath "public\tools\server\server.bat"
Move-FileWithBackup -SourcePath "start_server.bat" -DestinationPath "public\tools\server\start_server.bat"
Move-FileWithBackup -SourcePath "start_server_new.bat" -DestinationPath "public\tools\server\start_server_new.bat"

# 2. 移动错误修复和安全相关文件到public/tools/fixes/
Write-Host "Moving fix files to public/tools/fixes/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "fix_interface_implementation.php" -DestinationPath "public\tools\fixes\fix_interface_implementation.php"
Move-FileWithBackup -SourcePath "fix_php_errors.php" -DestinationPath "public\tools\fixes\fix_php_errors.php"
Move-FileWithBackup -SourcePath "fix_advanced_attack_surface_management.php" -DestinationPath "public\tools\fixes\fix_advanced_attack_surface_management.php"
Move-FileWithBackup -SourcePath "fix_stub_methods.php" -DestinationPath "public\tools\fixes\fix_stub_methods.php"
Move-FileWithBackup -SourcePath "comprehensive_fix.php" -DestinationPath "public\tools\fixes\comprehensive_fix.php"
Move-FileWithBackup -SourcePath "debug_autoloader.php" -DestinationPath "public\tools\fixes\debug_autoloader.php"
Move-FileWithBackup -SourcePath "final_example_replacer.php" -DestinationPath "public\tools\fixes\final_example_replacer.php"
Move-FileWithBackup -SourcePath "comprehensive_example_replacer.php" -DestinationPath "public\tools\fixes\comprehensive_example_replacer.php"
Move-FileWithBackup -SourcePath "replace_examples.php" -DestinationPath "public\tools\fixes\replace_examples.php"
Move-FileWithBackup -SourcePath "fix_api_endpoints.php" -DestinationPath "public\tools\fixes\fix_api_endpoints.php"

# 3. 移动测试工具到public/tools/tests/
Write-Host "Moving test files to public/tools/tests/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "simple_chat_test.php" -DestinationPath "public\tools\tests\simple_chat_test.php"
Move-FileWithBackup -SourcePath "test_chat_system.php" -DestinationPath "public\tools\tests\test_chat_system.php"
Move-FileWithBackup -SourcePath "test_api_endpoints.php" -DestinationPath "public\tools\tests\test_api_endpoints.php"
Move-FileWithBackup -SourcePath "test_sqlite.php" -DestinationPath "public\tools\tests\test_sqlite.php"
Move-FileWithBackup -SourcePath "test_php.php" -DestinationPath "public\tools\tests\test_php.php"
Move-FileWithBackup -SourcePath "test.php" -DestinationPath "public\tools\tests\test.php"

# 4. 移动报告和文档文件到public/docs/reports/
Write-Host "Moving report files to public/docs/reports/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "TESTING_IMPLEMENTATION_SUMMARY.md" -DestinationPath "public\docs\reports\testing_implementation_summary.md"
Move-FileWithBackup -SourcePath "ENHANCED_CHAT_API_FIX_REPORT.txt" -DestinationPath "public\docs\reports\enhanced_chat_api_fix_report.txt"
Move-FileWithBackup -SourcePath "AUTH_API_FIX_REPORT.txt" -DestinationPath "public\docs\reports\auth_api_fix_report.txt"
Move-FileWithBackup -SourcePath "FINAL_PHP_ERROR_FIX_REPORT.txt" -DestinationPath "public\docs\reports\final_php_error_fix_report.txt"
Move-FileWithBackup -SourcePath "FINAL_PHP_ERROR_FIX_REPORT.md" -DestinationPath "public\docs\reports\final_php_error_fix_report.md"
Move-FileWithBackup -SourcePath "PHP_ERRORS_FIX_REPORT.md" -DestinationPath "public\docs\reports\php_errors_fix_report.md"
Move-FileWithBackup -SourcePath "AdvancedAttackSurfaceManagement_fix_report.md" -DestinationPath "public\docs\reports\advanced_attack_surface_management_fix_report.md"
Move-FileWithBackup -SourcePath "AASM_Fix_Instructions.md" -DestinationPath "public\docs\reports\aasm_fix_instructions.md"
Move-FileWithBackup -SourcePath "fix_AdvancedAttackSurfaceManagement.md" -DestinationPath "public\docs\reports\fix_advanced_attack_surface_management.md"
Move-FileWithBackup -SourcePath "FINAL_COMPREHENSIVE_FIX_REPORT.md" -DestinationPath "public\docs\reports\final_comprehensive_fix_report.md"
Move-FileWithBackup -SourcePath "CHAT_SYSTEM_IMPLEMENTATION_SUMMARY.md" -DestinationPath "public\docs\reports\chat_system_implementation_summary.md"
Move-FileWithBackup -SourcePath "EXAMPLE_IMPLEMENTATION_UPGRADE_REPORT.md" -DestinationPath "public\docs\reports\example_implementation_upgrade_report.md"
Move-FileWithBackup -SourcePath "SECURITY_IMPLEMENTATION_SUMMARY.md" -DestinationPath "public\docs\reports\security_implementation_summary.md"
Move-FileWithBackup -SourcePath "api_endpoints_fix_summary.md" -DestinationPath "public\docs\reports\api_endpoints_fix_summary.md"
Move-FileWithBackup -SourcePath "IMPLEMENTATION_TRACKING.md" -DestinationPath "public\docs\reports\implementation_tracking.md"
Move-FileWithBackup -SourcePath "PROJECT_SUMMARY.md" -DestinationPath "public\docs\project_summary.md"
Move-FileWithBackup -SourcePath "PUBLIC_FOLDER_UPGRADE_PLAN.md" -DestinationPath "public\docs\plans\public_folder_upgrade_plan.md"
Move-FileWithBackup -SourcePath "OPTIMIZATION_SUMMARY.md" -DestinationPath "public\docs\reports\optimization_summary.md"
Move-FileWithBackup -SourcePath "ALINGAI_UPGRADE_ENHANCEMENT_PLAN.md" -DestinationPath "public\docs\plans\alingai_upgrade_enhancement_plan.md"
Move-FileWithBackup -SourcePath "FILE_CLASSIFICATION_TABLE.md" -DestinationPath "public\docs\file_classification_table.md"

# 5. 移动配置文件到public/config/
Write-Host "Moving config files to public/config/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "php.ini.local" -DestinationPath "public\config\php.ini.local"
Move-FileWithBackup -SourcePath "php_extensions.ini" -DestinationPath "public\config\php_extensions.ini"
Copy-Item -Path "phpunit.xml.dist" -Destination "public\config\phpunit.xml.dist" -Force
Write-Host "Copied file: phpunit.xml.dist to public\config\phpunit.xml.dist"

# 6. 移动开发工具到public/tools/dev/
Write-Host "Moving dev tools to public/tools/dev/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "server_router.php" -DestinationPath "public\tools\dev\server_router.php"
Move-FileWithBackup -SourcePath "router.php" -DestinationPath "public\tools\dev\router.php"
Move-FileWithBackup -SourcePath "stubs.php" -DestinationPath "public\tools\dev\stubs.php"
Move-FileWithBackup -SourcePath "output.json" -DestinationPath "public\tools\dev\output.json"
Move-FileWithBackup -SourcePath "project_progress_temp.txt" -DestinationPath "public\tools\dev\project_progress_temp.txt"

# 7. 移动admin-center目录下的文件到public/admin/
Write-Host "Moving admin-center files to public/admin/..." -ForegroundColor Cyan
Move-FileWithBackup -SourcePath "admin-center\README.md" -DestinationPath "public\admin\docs\admin_center_readme.md"
Move-FileWithBackup -SourcePath "admin-center\index.php" -DestinationPath "public\admin\admin_center_index.php"
Move-FileWithBackup -SourcePath "admin-center\composer.json" -DestinationPath "public\admin\composer.json"

# 检查并移动子目录
$adminCenterDirs = @(
    @{ Source = "admin-center\public"; Destination = "public\admin" },
    @{ Source = "admin-center\app"; Destination = "public\admin\app" },
    @{ Source = "admin-center\routes"; Destination = "public\admin\routes" },
    @{ Source = "admin-center\config"; Destination = "public\admin\config" },
    @{ Source = "admin-center\docs"; Destination = "public\admin\docs" },
    @{ Source = "admin-center\tools"; Destination = "public\admin\tools" },
    @{ Source = "admin-center\tests"; Destination = "public\admin\tests" },
    @{ Source = "admin-center\database"; Destination = "public\admin\database" },
    @{ Source = "admin-center\resources"; Destination = "public\admin\resources" }
)

foreach ($dir in $adminCenterDirs) {
    if (Test-Path -Path $dir.Source) {
        Move-FileWithBackup -SourcePath $dir.Source -DestinationPath $dir.Destination
    }
}

Write-Host "File organization completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to backups\root_cleanup2" -ForegroundColor Green 