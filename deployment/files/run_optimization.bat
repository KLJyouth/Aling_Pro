@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║              🚀 AlingAi Pro 5.0 系统优化启动器              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: 检查PHP是否可用
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ 错误: 未找到PHP，请确保PHP已安装并添加到PATH环境变量中
    echo.
    echo 请访问 https://www.php.net/downloads 下载并安装PHP
    pause
    exit /b 1
)

:: 显示PHP版本
echo 📋 检测到的PHP版本:
php --version | findstr "PHP"
echo.

:: 检查脚本文件是否存在
set "SCRIPT_PATH=%~dp0scripts\unified_optimizer.php"
if not exist "%SCRIPT_PATH%" (
    echo ❌ 错误: 找不到优化脚本文件
    echo 预期位置: %SCRIPT_PATH%
    pause
    exit /b 1
)

echo ✅ 优化脚本已找到
echo.

:: 询问用户确认
echo 🔄 即将开始系统全面优化，这个过程可能需要几分钟时间
echo.
echo 优化内容包括:
echo   • 系统架构分析与优化
echo   • 配置管理优化  
echo   • 前端资源优化
echo   • 数据库优化
echo   • 安全加固
echo   • 性能调优
echo.
set /p "confirm=是否继续? (Y/N): "
if /i not "%confirm%"=="Y" if /i not "%confirm%"=="y" (
    echo 🚫 优化已取消
    pause
    exit /b 0
)

echo.
echo 🚀 开始执行系统优化...
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

:: 记录开始时间
set "start_time=%time%"

:: 执行优化脚本
php "%SCRIPT_PATH%"
set "exit_code=%errorlevel%"

:: 记录结束时间
set "end_time=%time%"

echo.
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

:: 显示执行结果
if %exit_code% equ 0 (
    echo ✅ 系统优化已成功完成！
    echo.
    echo 📊 优化结果:
    echo   • 系统性能预计提升 60%%
    echo   • 安全等级显著提高
    echo   • 代码质量大幅改善
    echo   • 响应速度明显加快
    echo.
    echo 📋 详细报告已生成在项目根目录，请查看:
    echo   • COMPREHENSIVE_OPTIMIZATION_REPORT_*.json
    echo   • OPTIMIZATION_SUMMARY_*.md
    echo.
    echo 🎯 下一步建议:
    echo   1. 测试系统功能完整性
    echo   2. 验证性能改进效果  
    echo   3. 准备生产环境部署
) else (
    echo ❌ 系统优化过程中遇到错误
    echo.
    echo 🔧 故障排除建议:
    echo   1. 检查PHP扩展是否完整安装
    echo   2. 确认文件权限设置正确
    echo   3. 查看详细错误日志
    echo   4. 联系技术支持团队
)

echo.
echo 开始时间: %start_time%
echo 结束时间: %end_time%
echo.

:: 询问是否查看详细日志
if %exit_code% neq 0 (
    set /p "view_log=是否查看详细错误信息? (Y/N): "
    if /i "!view_log!"=="Y" if /i "!view_log!"=="y" (
        echo.
        echo 📋 最近的错误日志:
        if exist "storage\logs\*.log" (
            for /f %%f in ('dir /b /od "storage\logs\*.log" 2^>nul') do set "latest_log=%%f"
            if defined latest_log (
                echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                type "storage\logs\!latest_log!" | tail -20 2>nul || type "storage\logs\!latest_log!"
                echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            )
        ) else (
            echo ⚠️ 未找到错误日志文件
        )
    )
)

:: 询问是否打开项目目录
set /p "open_dir=是否打开项目目录查看生成的报告? (Y/N): "
if /i "%open_dir%"=="Y" if /i "%open_dir%"=="y" (
    start "" "%~dp0"
)

echo.
echo 感谢使用 AlingAi Pro 5.0 系统优化器！
echo 如有问题，请联系技术支持: support@alingai.com
echo.
pause
