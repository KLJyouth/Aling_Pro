@echo off
chcp 65001 >nul
echo ==========================================
echo    AlingAi Pro System Launcher
echo    Quick Start Development Server
echo ==========================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP not installed or not in PATH
    echo Please install PHP and ensure it's accessible from command line
    pause
    exit /b 1
)

REM Display PHP version
echo [INFO] Detected PHP version:
php --version | findstr "PHP"
echo.

REM Check if port is available
echo [CHECK] Checking if port 8000 is available...
netstat -an | find "0.0.0.0:8000" >nul 2>&1
if %errorlevel% equ 0 (
    echo [WARNING] Port 8000 is occupied, trying port 8001...
    set PORT=8001
) else (
    set PORT=8000
)

REM Display startup info
echo [STARTUP] Starting AlingAi Pro development server...
echo [PORT] localhost:%PORT%
echo [PATH] %~dp0public
echo.

REM Check if public directory exists
if not exist "public" (
    echo [ERROR] Public directory not found
    echo Please ensure you're running this script from the project root
    pause
    exit /b 1
)

REM Check if key files exist
if not exist "public\index.html" (
    echo [ERROR] index.html not found
    pause
    exit /b 1
)

echo [READY] System ready to start, press any key to continue...
pause >nul

REM Start PHP development server
echo [STARTING] Launching server...
echo.
echo ==========================================
echo  🚀 AlingAi Pro System Started
echo  📱 Main Site: http://localhost:%PORT%
echo  🧪 Test Console: http://localhost:%PORT%/system-test-console.html
echo  ⚡ Press Ctrl+C to stop server
echo ==========================================
echo.

REM Try to open browser automatically
start http://localhost:%PORT%

REM Start PHP server
php -S localhost:%PORT% -t public

REM If server stops unexpectedly
echo.
echo [INFO] Server stopped
pause
