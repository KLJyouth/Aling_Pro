@echo off
echo ===================================================
echo PHP 语法验证工具 - AlingAi Pro
echo ===================================================
echo.

REM 检查PHP可执行文件
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo 未找到PHP可执行文件！
    echo 请确保PHP已安装并添加到系统PATH中。
    echo.
    echo 尝试使用便携式PHP...
    
    if exist "portable_php\php.exe" (
        set PHP_CMD=portable_php\php.exe
        echo 使用便携式PHP: portable_php\php.exe
    ) else if exist "php_temp\php.exe" (
        set PHP_CMD=php_temp\php.exe
        echo 使用临时PHP: php_temp\php.exe
    ) else (
        echo 未找到便携式PHP！
        echo 请安装PHP或下载便携式PHP。
        echo.
        pause
        exit /b 1
    )
) else (
    set PHP_CMD=php
    echo 使用系统PHP: %PHP_CMD%
)

echo.
echo 开始验证已修复的PHP文件...
echo.

%PHP_CMD% validate_fixed_files.php

echo.
echo 验证完成！
echo.
echo 请查看生成的报告文件了解详细信息。
echo.
pause
