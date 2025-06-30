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

:: 检查PHP环境
echo [检查] 正在检查PHP环境...
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [错误] 未检测到PHP环境，请确保已安装PHP并添加到系统PATH中
    echo        您可以从 https://windows.php.net/download/ 下载PHP
    pause
    exit /b 1
)

:: 检查PHP版本
for /f "tokens=2 delims=:" %%a in ('php -v ^| findstr /r "PHP [0-9]"') do (
    set phpver=%%a
)
echo [信息] 检测到PHP版本:%phpver%

:: 检查端口占用
echo [检查] 检查端口 %PORT% 是否可用...
netstat -ano | findstr ":%PORT%" >nul
if %ERRORLEVEL% equ 0 (
    echo [警告] 端口 %PORT% 已被占用，尝试使用其他端口...
    set /a PORT=%PORT%+1
    echo [信息] 将使用端口 %PORT%
)

:: 检查项目文件
echo [检查] 检查项目文件...
if not exist "public\index.php" (
    echo [错误] 未找到项目文件，请确保在正确的目录中运行此脚本
    pause
    exit /b 1
)

:: 启动信息
echo.
echo [启动] 正在启动 AlingAi Pro 系统...
echo ========================================
echo 访问地址: http://localhost:%PORT%
echo 管理面板: http://localhost:%PORT%/admin/
echo API文档: http://localhost:%PORT%/api-docs
echo ========================================
echo 按 Ctrl+C 停止服务器
echo.

:: 自动打开浏览器（可选）
choice /c YN /m "是否自动打开浏览器? (Y/N)" /t 5 /d Y >nul
if %ERRORLEVEL% equ 1 (
    echo [信息] 正在打开浏览器...
    start http://localhost:%PORT%/
)

:: 启动服务器
cd public
php -S localhost:%PORT% router.php 