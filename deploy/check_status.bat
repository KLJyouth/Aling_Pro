@echo off
REM AlingAI Pro 5.0 服务状态检查脚本

echo 🔍 AlingAI Pro 5.0 服务状态检查
echo ==================================

REM 检查Web服务器
echo [INFO] 检查Web服务器状态...
netstat -an | findstr ":8000" >nul
if %errorLevel% equ 0 (
    echo    ✅ Web服务器: 运行中 (端口: 8000)
) else (
    netstat -an | findstr ":8001" >nul
    if !errorLevel! equ 0 (
        echo    ✅ Web服务器: 运行中 (端口: 8001)
    ) else (
        echo    ❌ Web服务器: 未运行
    )
)

REM 检查WebSocket服务器
echo [INFO] 检查WebSocket服务器状态...
netstat -an | findstr ":8080" >nul
if %errorLevel% equ 0 (
    echo    ✅ WebSocket服务器: 运行中 (端口: 8080)
) else (
    echo    ❌ WebSocket服务器: 未运行
)

REM 检查PHP进程
echo [INFO] 检查PHP进程...
tasklist | findstr "php.exe" >nul
if %errorLevel% equ 0 (
    echo    ✅ PHP进程: 运行中
    echo [INFO] PHP进程详情:
    tasklist | findstr "php.exe"
) else (
    echo    ❌ PHP进程: 未找到
)

REM 检查日志文件
echo [INFO] 检查日志文件...
if exist logs\system\webserver.log (
    echo    ✅ Web服务器日志: 存在
) else (
    echo    ❌ Web服务器日志: 不存在
)

if exist logs\websocket\websocket.log (
    echo    ✅ WebSocket日志: 存在
) else (
    echo    ❌ WebSocket日志: 不存在
)

if exist logs\security\monitoring.log (
    echo    ✅ 安全监控日志: 存在
) else (
    echo    ❌ 安全监控日志: 不存在
)

REM 检查数据库
echo [INFO] 检查数据库...
if exist storage\database.sqlite (
    echo    ✅ SQLite数据库: 存在
) else (
    echo    ❌ SQLite数据库: 不存在
)

REM 检查配置文件
echo [INFO] 检查配置文件...
if exist .env (
    echo    ✅ 环境配置: 存在
) else (
    echo    ❌ 环境配置: 不存在
)

if exist config\routes.php (
    echo    ✅ 路由配置: 存在
) else (
    echo    ❌ 路由配置: 不存在
)

echo.
echo ==================================
echo 检查完成
echo ==================================

pause
