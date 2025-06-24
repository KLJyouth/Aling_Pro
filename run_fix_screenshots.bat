@echo off
chcp 65001 > nul
echo ===============================================
echo PHP Screenshot Errors Fix Tool
echo ===============================================
echo.

REM 检查PHP可执行文件路径
if exist portable_php\php.exe (
    set PHP_CMD=portable_php\php.exe
) else (
    where php >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        set PHP_CMD=php
    ) else (
        echo PHP not found. Please install PHP or set up portable PHP.
        pause
        exit /b 1
    )
)

echo Using PHP: %PHP_CMD%
echo.
echo Starting to fix screenshot errors...
echo.

REM 运行修复脚本
%PHP_CMD% fix_screenshot_errors.php

echo.
echo Fix completed!
echo Please check the generated log file.
echo.
pause