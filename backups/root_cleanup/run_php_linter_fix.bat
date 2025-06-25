@echo off
echo ===== AlingAi Pro PHP Linter和错误修复工具 =====
echo 时间: %date% %time%
echo.

set PROJECT_ROOT=%~dp0
set PHP_PORTABLE_DIR=%PROJECT_ROOT%\portable_php
set PHP_ZIP_FILE=%PROJECT_ROOT%\portable_php.zip
set PHP_URL=https://windows.php.net/downloads/releases/php-8.2.15-Win32-vs16-x64.zip
set PHP_EXE=%PHP_PORTABLE_DIR%\php.exe

echo 正在检查Portable PHP...

if not exist "%PHP_PORTABLE_DIR%" (
    echo Portable PHP不存在，正在下载...
    
    echo 下载PHP...
    powershell -Command "& {Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_ZIP_FILE%'}"
    
    if %ERRORLEVEL% neq 0 (
        echo 下载失败！请检查网络连接或手动下载PHP。
        echo 下载地址: %PHP_URL%
        echo 请将下载的文件解压到 %PHP_PORTABLE_DIR% 目录，然后重新运行此脚本。
        pause
        exit /b 1
    )
    
    echo 创建目录...
    mkdir "%PHP_PORTABLE_DIR%"
    
    echo 解压PHP...
    powershell -Command "& {Expand-Archive -Path '%PHP_ZIP_FILE%' -DestinationPath '%PHP_PORTABLE_DIR%' -Force}"
    
    echo 清理...
    del "%PHP_ZIP_FILE%"
    
    echo 配置PHP...
    copy "%PROJECT_ROOT%\php.ini.local" "%PHP_PORTABLE_DIR%\php.ini"
)

echo 检查PHP是否可用...
"%PHP_EXE%" -v

if %ERRORLEVEL% neq 0 (
    echo PHP安装有问题，请检查错误信息。
    pause
    exit /b 1
)

echo PHP配置正常，准备执行错误修复脚本...
echo.

echo 运行PHP错误修复脚本...
"%PHP_EXE%" "%PROJECT_ROOT%\fix_remaining_php_errors.php"

echo.
echo 完成！
echo 请查看生成的报告文件了解详细情况。
pause 