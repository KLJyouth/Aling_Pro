@echo off
setlocal enabledelayedexpansion

title AlingAi Pro Admin Tools

:menu
cls
echo ===================================
echo      AlingAi Pro Admin Tools
echo ===================================
echo.
echo  1. PHP Error Fix Tools
echo  2. Namespace Fix Tools
echo  3. Validation Tools
echo  4. Maintenance Reports
echo  5. Maintenance Logs
echo  0. Exit
echo.
echo ===================================
echo.

set /p choice=Enter your choice (0-5): 

if "%choice%"=="1" goto php_error_tools
if "%choice%"=="2" goto namespace_tools
if "%choice%"=="3" goto validation_tools
if "%choice%"=="4" goto maintenance_reports
if "%choice%"=="5" goto maintenance_logs
if "%choice%"=="0" goto end

echo Invalid choice. Please try again.
timeout /t 2 >nul
goto menu

:php_error_tools
cls
echo ===================================
echo        PHP Error Fix Tools
echo ===================================
echo.
cd /d "%~dp0public\admin\maintenance\tools"
echo Available PHP Error Fix Tools:
echo ----------------------------
dir /b fix_*.php
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p tool=Enter tool name (or 0 to go back): 
if "%tool%"=="0" goto menu

if exist "%tool%" (
    php "%tool%"
    pause
    goto php_error_tools
) else (
    echo Tool not found. Please try again.
    timeout /t 2 >nul
    goto php_error_tools
)

:namespace_tools
cls
echo ===================================
echo        Namespace Fix Tools
echo ===================================
echo.
cd /d "%~dp0public\admin\maintenance\tools"
echo Available Namespace Fix Tools:
echo ----------------------------
dir /b *namespace*.php
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p tool=Enter tool name (or 0 to go back): 
if "%tool%"=="0" goto menu

if exist "%tool%" (
    php "%tool%"
    pause
    goto namespace_tools
) else (
    echo Tool not found. Please try again.
    timeout /t 2 >nul
    goto namespace_tools
)

:validation_tools
cls
echo ===================================
echo        Validation Tools
echo ===================================
echo.
cd /d "%~dp0public\admin\maintenance\tools"
echo Available Validation Tools:
echo ----------------------------
dir /b *validate*.php check_*.php
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p tool=Enter tool name (or 0 to go back): 
if "%tool%"=="0" goto menu

if exist "%tool%" (
    php "%tool%"
    pause
    goto validation_tools
) else (
    echo Tool not found. Please try again.
    timeout /t 2 >nul
    goto validation_tools
)

:maintenance_reports
cls
echo ===================================
echo        Maintenance Reports
echo ===================================
echo.
cd /d "%~dp0public\admin\maintenance\reports"
echo Available Reports:
echo ----------------------------
dir /b *.md
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p report=Enter report name (or 0 to go back): 
if "%report%"=="0" goto menu

if exist "%report%" (
    notepad "%report%"
    goto maintenance_reports
) else (
    echo Report not found. Please try again.
    timeout /t 2 >nul
    goto maintenance_reports
)

:maintenance_logs
cls
echo ===================================
echo        Maintenance Logs
echo ===================================
echo.
cd /d "%~dp0public\admin\maintenance\logs"
echo Available Logs:
echo ----------------------------
dir /b *.md
echo.
echo  0. Back to Main Menu
echo.
echo ===================================
echo.

set /p log=Enter log name (or 0 to go back): 
if "%log%"=="0" goto menu

if exist "%log%" (
    notepad "%log%"
    goto maintenance_logs
) else (
    echo Log not found. Please try again.
    timeout /t 2 >nul
    goto maintenance_logs
)

:end
echo Thank you for using AlingAi Pro Admin Tools.
timeout /t 2 >nul
exit /b 0 