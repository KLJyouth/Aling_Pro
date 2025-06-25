@echo off
title AlingAi Pro - 用户中心增强版启动器
echo.
echo =================================================
echo   🚀 AlingAi Pro - 用户中心增强版启动器
echo =================================================
echo.
echo 正在启动PHP开发服务器...
echo.

cd /d "%~dp0public"

echo 服务器地址: http://localhost:8080
echo.
echo 可访问的页面:
echo   📋 功能测试页面: http://localhost:8080/test-profile-enhanced.html
echo   👤 用户中心增强版: http://localhost:8080/profile-enhanced.html
echo   🏠 系统主页: http://localhost:8080/index.html
echo.
echo 按 Ctrl+C 停止服务器
echo.

php -S localhost:8080

pause
