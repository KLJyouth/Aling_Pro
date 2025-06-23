@echo off
REM AlingAI Pro 5.0 系统管理后台部署脚本 (Windows版本)
REM 整合测试文件并部署到后台管理系统

echo === AlingAI Pro 5.0 系统管理后台部署 ===
echo 开始整合测试文件并部署到后台管理系统...

REM 创建必要的目录
if not exist "admin\js" mkdir admin\js
if not exist "admin\css" mkdir admin\css
if not exist "storage\exports" mkdir storage\exports
if not exist "storage\logs" mkdir storage\logs

echo ✅ 目录结构创建完成

REM 移动并备份现有的测试文件
echo 📦 备份现有测试文件...
if not exist "backup\old_files" mkdir backup\old_files
if not exist "backup\old_files\test_files" mkdir backup\old_files\test_files
if not exist "backup\old_files\check_files" mkdir backup\old_files\check_files
if not exist "backup\old_files\debug_files" mkdir backup\old_files\debug_files
if not exist "backup\old_files\system_files" mkdir backup\old_files\system_files
if not exist "backup\old_files\error_files" mkdir backup\old_files\error_files

REM 备份测试文件
for %%f in (test_*.php) do (
    if exist "%%f" (
        echo 备份: %%f
        move "%%f" "backup\old_files\test_files\"
    )
)

REM 备份检查文件
for %%f in (check_*.php) do (
    if exist "%%f" (
        echo 备份: %%f
        move "%%f" "backup\old_files\check_files\"
    )
)

REM 备份调试文件
for %%f in (debug_*.php) do (
    if exist "%%f" (
        echo 备份: %%f
        move "%%f" "backup\old_files\debug_files\"
    )
)

REM 备份系统文件
for %%f in (system_*.php) do (
    if exist "%%f" (
        echo 备份: %%f
        move "%%f" "backup\old_files\system_files\"
    )
)

REM 备份错误处理文件
for %%f in (error_*.php) do (
    if exist "%%f" (
        echo 备份: %%f
        move "%%f" "backup\old_files\error_files\"
    )
)

REM 备份其他相关文件
if exist "fix_database_comprehensive.php" (
    echo 备份: fix_database_comprehensive.php
    move "fix_database_comprehensive.php" "backup\old_files\"
)

if exist "ultimate_database_fix_v2.php" (
    echo 备份: ultimate_database_fix_v2.php
    move "ultimate_database_fix_v2.php" "backup\old_files\"
)

if exist "final_system_validation.php" (
    echo 备份: final_system_validation.php
    move "final_system_validation.php" "backup\old_files\"
)

if exist "three_complete_compilation_validator.php" (
    echo 备份: three_complete_compilation_validator.php
    move "three_complete_compilation_validator.php" "backup\old_files\"
)

if exist "improved_health_check.php" (
    echo 备份: improved_health_check.php
    move "improved_health_check.php" "backup\old_files\"
)

if exist "system_health_check.php" (
    echo 备份: system_health_check.php
    move "system_health_check.php" "backup\old_files\"
)

echo ✅ 文件备份完成

REM 创建备份说明文件
echo # 已整合文件备份 > "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo 这些文件已经被整合到新的系统管理后台中： >> "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo ## 测试文件 (test_files/) >> "backup\old_files\README.md"
echo - 所有 test_*.php 文件的功能已整合到后台的"系统测试"模块 >> "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo ## 检查文件 (check_files/) >> "backup\old_files\README.md"
echo - 所有 check_*.php 文件的功能已整合到后台的"健康检查"模块 >> "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo ## 调试文件 (debug_files/) >> "backup\old_files\README.md"
echo - 所有 debug_*.php 文件的功能已整合到后台的"调试工具"模块 >> "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo ## 系统文件 (system_files/) >> "backup\old_files\README.md"
echo - 所有 system_*.php 文件的功能已整合到后台的"系统概览"模块 >> "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo ## 错误处理文件 (error_files/) >> "backup\old_files\README.md"
echo - 所有 error_*.php 文件的功能已整合到后台的"调试工具"模块 >> "backup\old_files\README.md"
echo. >> "backup\old_files\README.md"
echo ## 新的系统管理后台 >> "backup\old_files\README.md"
echo 访问 `/admin/` 目录使用统一的管理界面。 >> "backup\old_files\README.md"
echo 默认密码: admin123 >> "backup\old_files\README.md"

echo ✅ 备份说明文件创建完成

REM 验证部署
echo 🔍 验证部署结果...

if exist "admin\index.php" (
    echo ✅ 主入口文件存在
) else (
    echo ❌ 主入口文件缺失
    pause
    exit /b 1
)

if exist "admin\SystemManager.php" (
    echo ✅ 系统管理器存在
) else (
    echo ❌ 系统管理器缺失
    pause
    exit /b 1
)

if exist "admin\js\admin.js" (
    echo ✅ JavaScript文件存在
) else (
    echo ❌ JavaScript文件缺失
    pause
    exit /b 1
)

if exist "backup\old_files" (
    echo ✅ 文件备份完成
) else (
    echo ❌ 文件备份失败
    pause
    exit /b 1
)

echo.
echo === 部署完成 ===
echo ✅ 系统管理后台已成功部署
echo ✅ 原有测试文件已备份到 backup\old_files\
echo ✅ 新的管理后台位于 /admin/ 目录
echo.
echo 📖 使用说明:
echo 1. 访问 http://yourdomain.com/admin/
echo 2. 使用默认密码 'admin123' 登录
echo 3. 建议立即修改管理员密码
echo.
echo 🔧 集成功能:
echo • 系统概览 - 实时监控系统状态
echo • 数据库管理 - 数据库连接检查和修复
echo • 系统测试 - 整合所有测试功能
echo • 健康检查 - 系统健康状态检查
echo • 调试工具 - 系统调试和错误信息
echo • 系统优化 - 缓存清理和性能优化
echo • 日志管理 - 日志查看和导出
echo.
echo 🎉 AlingAI Pro 5.0 系统管理后台部署完成！
echo.
pause
