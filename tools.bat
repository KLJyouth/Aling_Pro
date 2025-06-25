@echo off
setlocal enabledelayedexpansion

title AlingAi Pro Tools Menu

:menu
cls
echo ===================================
echo        AlingAi Pro Tools Menu
echo ===================================
echo.
echo  1. Server Tools
echo  2. Test Tools
echo  3. Maintenance Tools
echo  4. Configuration Tools
echo  5. Documentation
echo  0. Exit
echo.
echo ===================================
echo.

set /p choice=Enter your choice (0-5): 

if "%choice%"=="1" goto server_tools
if "%choice%"=="2" goto test_tools
if "%choice%"=="3" goto maintenance_tools
if "%choice%"=="4" goto config_tools
if "%choice%"=="5" goto documentation
if "%choice%"=="0" goto end

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto menu

:server_tools
cls
echo ===================================
echo        Server Tools
echo ===================================
echo.
echo  1. Start Server
echo  2. Stop Server
echo  3. Restart Server
echo  4. Server Status
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p server_choice=Enter your choice (0-4): 

if "%server_choice%"=="1" (
    cd /d "%~dp0public\tools\server"
    call start_server.bat
    pause
    goto server_tools
)
if "%server_choice%"=="2" (
    cd /d "%~dp0public\tools\server"
    call stop_server.bat
    pause
    goto server_tools
)
if "%server_choice%"=="3" (
    cd /d "%~dp0public\tools\server"
    call restart_server.bat
    pause
    goto server_tools
)
if "%server_choice%"=="4" (
    cd /d "%~dp0public\tools\server"
    call server_status.bat
    pause
    goto server_tools
)
if "%server_choice%"=="0" goto menu

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto server_tools

:test_tools
cls
echo ===================================
echo        Test Tools
echo ===================================
echo.
echo  1. Run Unit Tests
echo  2. Run Integration Tests
echo  3. Run All Tests
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p test_choice=Enter your choice (0-3): 

if "%test_choice%"=="1" (
    cd /d "%~dp0public\tools\tests"
    call run_unit_tests.bat
    pause
    goto test_tools
)
if "%test_choice%"=="2" (
    cd /d "%~dp0public\tools\tests"
    call run_integration_tests.bat
    pause
    goto test_tools
)
if "%test_choice%"=="3" (
    cd /d "%~dp0public\tools\tests"
    call run_all_tests.bat
    pause
    goto test_tools
)
if "%test_choice%"=="0" goto menu

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto test_tools

:maintenance_tools
cls
echo ===================================
echo        Maintenance Tools
echo ===================================
echo.
echo  1. PHP Error Fix Tools
echo  2. Namespace Fix Tools
echo  3. Validation Tools
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p maint_choice=Enter your choice (0-3): 

if "%maint_choice%"=="1" (
    cd /d "%~dp0public\admin\maintenance\tools"
    echo Available PHP Error Fix Tools:
    echo ----------------------------
    dir /b fix_*.php
    echo.
    set /p tool=Enter tool name: 
    php !tool!
    pause
    goto maintenance_tools
)
if "%maint_choice%"=="2" (
    cd /d "%~dp0public\admin\maintenance\tools"
    echo Available Namespace Fix Tools:
    echo ----------------------------
    dir /b *namespace*.php
    echo.
    set /p tool=Enter tool name: 
    php !tool!
    pause
    goto maintenance_tools
)
if "%maint_choice%"=="3" (
    cd /d "%~dp0public\admin\maintenance\tools"
    echo Available Validation Tools:
    echo ----------------------------
    dir /b *validate*.php check_*.php
    echo.
    set /p tool=Enter tool name: 
    php !tool!
    pause
    goto maintenance_tools
)
if "%maint_choice%"=="0" goto menu

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto maintenance_tools

:config_tools
cls
echo ===================================
echo        Configuration Tools
echo ===================================
echo.
echo  1. Edit Configuration
echo  2. View Configuration
echo  3. Reset Configuration
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p config_choice=Enter your choice (0-3): 

if "%config_choice%"=="1" (
    cd /d "%~dp0public\config"
    notepad config.ini
    goto config_tools
)
if "%config_choice%"=="2" (
    cd /d "%~dp0public\config"
    type config.ini | more
    pause
    goto config_tools
)
if "%config_choice%"=="3" (
    cd /d "%~dp0public\config"
    copy config.default.ini config.ini
    echo Configuration reset to default.
    pause
    goto config_tools
)
if "%config_choice%"=="0" goto menu

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto config_tools

:documentation
cls
echo ===================================
echo        Documentation
echo ===================================
echo.
echo  1. User Guides
echo  2. Developer Guides
echo  3. Project Reports
echo  4. Project Structure
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p doc_choice=Enter your choice (0-4): 

if "%doc_choice%"=="1" (
    cd /d "%~dp0public\docs\guides"
    echo Available User Guides:
    echo ----------------------------
    dir /b *user*.md
    echo.
    set /p guide=Enter guide name (or press Enter to list all): 
    if "!guide!"=="" (
        dir /b *user*.md
    ) else (
        notepad !guide!
    )
    pause
    goto documentation
)
if "%doc_choice%"=="2" (
    cd /d "%~dp0public\docs\guides"
    echo Available Developer Guides:
    echo ----------------------------
    dir /b *dev*.md
    echo.
    set /p guide=Enter guide name (or press Enter to list all): 
    if "!guide!"=="" (
        dir /b *dev*.md
    ) else (
        notepad !guide!
    )
    pause
    goto documentation
)
if "%doc_choice%"=="3" (
    cd /d "%~dp0public\docs\reports"
    echo Available Project Reports:
    echo ----------------------------
    dir /b *.md
    echo.
    set /p report=Enter report name (or press Enter to list all): 
    if "!report!"=="" (
        dir /b *.md
    ) else (
        notepad !report!
    )
    pause
    goto documentation
)
if "%doc_choice%"=="4" (
    notepad "%~dp0PROJECT_STRUCTURE.md"
    pause
    goto documentation
)
if "%doc_choice%"=="0" goto menu

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto documentation

:end
echo Thank you for using AlingAi Pro Tools.
timeout /t 2 >nul
exit /b 0 