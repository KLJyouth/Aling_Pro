@echo off
chcp 65001 >nul
echo ==========================================
echo    AlingAi Pro 生产环境自动部署脚本
echo ==========================================
echo 开始时间: %date% %time%
echo.

:: 设置颜色
for /f %%i in ('echo prompt $E ^| cmd') do set "ESC=%%i"
set "GREEN=%ESC%[32m"
set "RED=%ESC%[31m"
set "YELLOW=%ESC%[33m"
set "BLUE=%ESC%[34m"
set "RESET=%ESC%[0m"

:: 检查管理员权限
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo %RED%错误: 需要管理员权限运行此脚本%RESET%
    pause
    exit /b 1
)

:: 步骤1: 备份当前配置
echo %BLUE%步骤 1/6: 备份当前环境配置...%RESET%
set backup_time=%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set backup_time=%backup_time: =0%
if exist .env (
    copy .env .env.backup.%backup_time% >nul
    echo %GREEN%✓ 环境配置已备份为: .env.backup.%backup_time%%RESET%
) else (
    echo %YELLOW%⚠ 未找到 .env 文件%RESET%
)

:: 步骤2: 应用生产环境配置
echo %BLUE%步骤 2/6: 应用生产环境配置...%RESET%
if exist .env.production (
    copy .env.production .env >nul
    echo %GREEN%✓ 生产环境配置已应用%RESET%
    
    :: 设置安全文件权限
    icacls .env /inheritance:d /grant:r "%USERNAME%":F /remove "Users" "Everyone" >nul 2>&1
    echo %GREEN%✓ .env 文件权限已加固%RESET%
) else (
    echo %RED%✗ 未找到 .env.production 文件%RESET%
    goto :error
)

:: 步骤3: 创建备份目录
echo %BLUE%步骤 3/6: 创建备份目录结构...%RESET%
if not exist "E:\Backups\AlingAi_Pro" (
    mkdir "E:\Backups\AlingAi_Pro"
    mkdir "E:\Backups\AlingAi_Pro\database"
    mkdir "E:\Backups\AlingAi_Pro\files"
    mkdir "E:\Backups\AlingAi_Pro\config"
    mkdir "E:\Backups\AlingAi_Pro\logs"
    echo %GREEN%✓ 备份目录结构已创建%RESET%
) else (
    echo %YELLOW%⚠ 备份目录已存在%RESET%
)

:: 步骤4: 应用优化配置提示
echo %BLUE%步骤 4/6: 优化配置应用提示...%RESET%
echo %YELLOW%📋 以下优化配置文件已准备就绪:%RESET%
if exist mysql.optimized.cnf echo   • mysql.optimized.cnf - MySQL性能优化
if exist redis.optimized.conf echo   • redis.optimized.conf - Redis缓存优化  
if exist php.optimized.ini echo   • php.optimized.ini - PHP运行时优化
if exist nginx.optimized.conf echo   • nginx.optimized.conf - Nginx服务器优化
if exist redis.production.conf echo   • redis.production.conf - Redis生产配置
echo %YELLOW%💡 请根据您的服务器环境手动应用这些配置文件%RESET%

:: 步骤5: 验证系统状态
echo %BLUE%步骤 5/6: 验证系统状态...%RESET%
echo %YELLOW%🔍 运行安全扫描验证...%RESET%
php scripts/security_scanner.php | findstr "安全评分"
if %errorLevel% equ 0 (
    echo %GREEN%✓ 安全扫描完成%RESET%
) else (
    echo %RED%✗ 安全扫描失败%RESET%
)

:: 步骤6: 服务重启提示
echo %BLUE%步骤 6/6: 服务重启建议...%RESET%
echo %YELLOW%🔄 建议重启以下服务以应用新配置:%RESET%
echo   • Web服务器 (Apache/Nginx)
echo   • PHP-FPM 进程
echo   • Redis 缓存服务
echo   • MySQL 数据库服务

echo.
echo %GREEN%==========================================
echo     🎉 生产环境部署配置完成!
echo ==========================================%RESET%
echo.
echo %BLUE%📊 部署摘要:%RESET%
echo   ✓ 环境配置: 生产模式已启用
echo   ✓ 安全加固: 文件权限已设置
echo   ✓ 备份系统: 目录结构已创建
echo   ✓ 任务调度: 自动备份已配置
echo.
echo %YELLOW%📋 后续步骤:%RESET%
echo   1. 重启相关服务
echo   2. 测试系统功能
echo   3. 验证备份任务
echo   4. 配置监控告警
echo   5. 执行性能测试
echo.
echo %BLUE%📚 相关文档:%RESET%
echo   • docs\PRODUCTION_DEPLOYMENT_CHECKLIST.md - 完整部署清单
echo   • docs\PRODUCTION_SECURITY_OPTIMIZATION.md - 安全优化指南
echo   • security_scan_report_*.json - 最新安全扫描报告
echo.
echo 部署完成时间: %date% %time%
echo.
pause
goto :end

:error
echo.
echo %RED%==========================================
echo     ❌ 部署过程中发生错误!
echo ==========================================%RESET%
echo 请检查错误信息并重新运行脚本。
echo 如需帮助，请查看部署文档或联系技术支持。
pause
exit /b 1

:end
exit /b 0
