@echo off
REM AlingAI Pro 5.0 重启服务脚本

echo 🔄 重启AlingAI Pro 5.0服务
echo ===========================

REM 停止所有服务
echo [INFO] 停止现有服务...
call deploy\stop_services.bat

REM 等待服务完全停止
echo [INFO] 等待服务完全停止...
timeout /t 5 /nobreak >nul

REM 检查和创建必要目录
if not exist tmp mkdir tmp
if not exist logs\system mkdir logs\system
if not exist logs\websocket mkdir logs\websocket
if not exist logs\security mkdir logs\security

REM 启动Web服务器
echo [INFO] 启动Web服务器...
set WEB_PORT=8000

netstat -an | findstr ":%WEB_PORT%" >nul
if !errorLevel! equ 0 (
    set /a WEB_PORT=WEB_PORT+1
    echo [WARNING] 端口8000已被占用，使用端口 !WEB_PORT!
)

start /b php -S localhost:%WEB_PORT% -t public > logs\system\webserver.log 2>&1
echo    ✅ Web服务器启动成功 (端口: %WEB_PORT%)

REM 启动WebSocket服务器
echo [INFO] 启动WebSocket服务器...
if exist start_websocket_server.php (
    netstat -an | findstr ":8080" >nul
    if !errorLevel! neq 0 (
        start /b php start_websocket_server.php > logs\websocket\websocket.log 2>&1
        echo    ✅ WebSocket服务器启动成功 (端口: 8080)
    ) else (
        echo    ⚠️ 端口8080已被占用，跳过WebSocket启动
    )
) else (
    echo    ⚠️ WebSocket服务器脚本不存在
)

REM 启动安全监控系统
echo [INFO] 启动安全监控系统...
if exist start_security_monitoring.php (
    start /b php start_security_monitoring.php > logs\security\monitoring.log 2>&1
    echo    ✅ 安全监控系统启动成功
) else (
    echo    ⚠️ 安全监控系统脚本不存在
)

REM 等待服务启动
echo [INFO] 等待服务启动...
timeout /t 3 /nobreak >nul

REM 检查服务状态
echo [INFO] 检查服务状态...
call deploy\check_status.bat

echo.
echo ===========================
echo ✅ 服务重启完成
echo ===========================
echo.
echo 🌐 访问地址:
echo    主应用: http://localhost:%WEB_PORT%
echo    安全监控: http://localhost:%WEB_PORT%/security/monitoring
echo    3D威胁可视化: http://localhost:%WEB_PORT%/security/visualization
echo.

pause
