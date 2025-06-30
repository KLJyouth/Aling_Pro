@echo off
setlocal EnableDelayedExpansion

:: 设置端口号（可修改）
set PORT=8000

:: 标题和版本
title AlingAi Pro 开发服务器 v1.0
echo ========================================
echo        AlingAi Pro 系统启动工具
echo ========================================
echo.

:: 检查是否存在PHP
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [错误] 未检测到PHP! 请安装PHP并确保其在PATH环境变量中。
    echo 您可以从 https://windows.php.net/download/ 下载PHP。
    pause
    exit /b 1
)

:: 检查工作目录
if not exist public\index.html (
    echo [错误] 未找到主程序文件 (public\index.html)!
    echo 请确保您在正确的目录中运行此脚本。
    pause
    exit /b 1
)

:: 获取PHP版本并检查
for /f "tokens=2 delims=:" %%a in ('php -v ^| findstr /C:"PHP"') do (
    set PHP_VERSION=%%a
    goto :continue
)
:continue
echo [信息] 当前PHP版本: %PHP_VERSION%

:: 显示启动信息
echo [信息] 正在启动 AlingAi Pro 开发服务器...
echo [信息] 访问地址: http://localhost:%PORT%
echo [信息] 按 Ctrl+C 停止服务器
echo.

:: 启动PHP开发服务器
cd public
echo [启动] 服务器启动时间: %TIME%
php -S localhost:%PORT% router_fixed.php

:: 如果服务器意外终止
echo.
echo [信息] 服务器已停止运行。
pause