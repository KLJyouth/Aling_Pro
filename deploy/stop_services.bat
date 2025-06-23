@echo off
REM AlingAI Pro 5.0 停止所有服务脚本

echo 🛑 停止AlingAI Pro 5.0所有服务
echo ===============================

REM 停止PHP进程
echo [INFO] 停止PHP进程...
taskkill /f /im php.exe >nul 2>&1
if %errorLevel% equ 0 (
    echo    ✅ PHP进程已停止
) else (
    echo    ℹ️ 没有运行的PHP进程
)

REM 清理PID文件
echo [INFO] 清理PID文件...
if exist tmp\webserver.pid del tmp\webserver.pid
if exist tmp\websocket.pid del tmp\websocket.pid
if exist tmp\monitoring.pid del tmp\monitoring.pid

echo    ✅ PID文件已清理

REM 等待端口释放
echo [INFO] 等待端口释放...
timeout /t 2 /nobreak >nul

REM 检查端口状态
echo [INFO] 检查端口状态...
netstat -an | findstr ":8000" >nul
if %errorLevel% neq 0 (
    echo    ✅ 端口8000已释放
) else (
    echo    ⚠️ 端口8000仍被占用
)

netstat -an | findstr ":8080" >nul
if %errorLevel% neq 0 (
    echo    ✅ 端口8080已释放
) else (
    echo    ⚠️ 端口8080仍被占用
)

echo.
echo ===============================
echo ✅ 所有服务已停止
echo ===============================

pause
