@echo off
chcp 65001 > nul
echo ===============================================
echo PHP Error and Chinese Encoding Fix Tool
echo ===============================================
echo.

REM Check if portable_php already has php.exe
if exist portable_php\php.exe (
    echo Found PHP in portable_php directory, using it...
    set PHP_CMD=portable_php\php.exe
    goto :run_fix
)

REM Check if regular PHP exists
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo Found system PHP, using it...
    set PHP_CMD=php
    goto :run_fix
)

REM Neither portable_php nor system PHP found
echo PHP not found. We need to download portable PHP.
echo.
echo Please wait while we download and set up PHP...
echo.

REM Download PHP using PowerShell
powershell -ExecutionPolicy Bypass -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/archives/php-8.1.2-nts-Win32-vs16-x64.zip' -OutFile 'php.zip'}"

REM Extract PHP
echo Extracting PHP...
powershell -ExecutionPolicy Bypass -Command "& {Expand-Archive -Force -Path 'php.zip' -DestinationPath 'portable_php'}"

REM Create basic php.ini
echo Creating php.ini...
echo [PHP] > portable_php\php.ini
echo display_errors = On >> portable_php\php.ini
echo error_reporting = E_ALL >> portable_php\php.ini
echo memory_limit = 512M >> portable_php\php.ini
echo default_charset = "UTF-8" >> portable_php\php.ini
echo extension_dir = "ext" >> portable_php\php.ini
echo extension=openssl >> portable_php\php.ini
echo extension=mbstring >> portable_php\php.ini

REM Clean up
del php.zip

REM Set PHP command
set PHP_CMD=portable_php\php.exe

REM Check if PHP was installed successfully
if not exist %PHP_CMD% (
    echo Failed to set up PHP. Please install PHP manually.
    echo.
    pause
    exit /b 1
)

echo PHP set up successfully!
echo.

:run_fix
echo Starting to scan and fix PHP files...
echo This may take a few minutes, please wait...
echo.

REM Run PHP fix script
%PHP_CMD% fix_all_php_errors.php

echo.
echo Scan and fix completed!
echo Please check the generated log and report files.
echo.
pause
