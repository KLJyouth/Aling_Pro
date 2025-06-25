@echo off
chcp 65001 >nul
echo =================================================
echo   AlingAi Pro v2.0.0 快速启动
echo   系统完善版本 - 100%% 就绪
echo =================================================

echo.
echo 🔍 检查系统环境...

REM 检查PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP 未安装或未在 PATH 中
    pause
    exit /b 1
)

for /f "tokens=2" %%i in ('php -v ^| findstr /R "PHP [0-9]"') do set PHP_VERSION=%%i
echo ✅ PHP 版本: %PHP_VERSION%

REM 检查Composer依赖
if not exist "vendor\autoload.php" (
    echo 📦 安装 Composer 依赖...
    composer install
)
echo ✅ Composer 依赖已就绪

REM 创建必要目录
if not exist "storage\logs" mkdir storage\logs
if not exist "storage\cache" mkdir storage\cache  
if not exist "storage\uploads" mkdir storage\uploads
if not exist "public\uploads" mkdir public\uploads
echo ✅ 存储目录已准备

echo.
echo 🚀 启动系统服务...

REM 检查WebSocket是否已运行
netstat -an | findstr ":8080" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ WebSocket 服务器已在运行 ^(端口 8080^)
) else (
    echo 📡 启动 WebSocket 服务器...
    start /b php websocket_simple_react.php
    timeout /t 2 >nul
    echo ✅ WebSocket 服务器已启动
)

REM 检查Web服务器是否已运行  
netstat -an | findstr ":3000" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Web 服务器已在运行 ^(端口 3000^)
) else (
    echo 🌐 启动 Web 服务器...
    start /b php -S localhost:3000 -t public router.php
    timeout /t 3 >nul
    echo ✅ Web 服务器已启动
)

echo.
echo 🔍 检查服务状态...

REM 检查服务端口
netstat -an | findstr ":8080" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ WebSocket 端口 8080 正常监听
) else (
    echo ❌ WebSocket 端口 8080 未监听
)

netstat -an | findstr ":3000" >nul 2>&1  
if %errorlevel% equ 0 (
    echo ✅ Web 服务器端口 3000 正常监听
) else (
    echo ❌ Web 服务器端口 3000 未监听
)

echo.
echo 🧪 运行快速系统测试...
php system_status_check.php

echo.
echo =================================================
echo   🎉 AlingAi Pro 系统启动完成！
echo =================================================
echo.
echo 📱 访问地址:
echo   • 主页: http://localhost:3000
echo   • 系统测试: http://localhost:3000/system_test_complete.html
echo   • WebSocket: ws://localhost:8080
echo.
echo 🛠 管理命令:
echo   • 查看状态: php system_status_check.php
echo   • 集成测试: php integration_test.php  
echo   • 性能测试: php performance_test.php
echo   • 部署检查: php deployment_readiness.php
echo.
echo 按任意键打开浏览器访问系统...
pause >nul
start http://localhost:3000
