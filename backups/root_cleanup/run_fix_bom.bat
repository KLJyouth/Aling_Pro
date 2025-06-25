@echo off
echo 开始修复PHP文件中的BOM标记...

REM 尝试使用系统PHP
php -f fix_bom_markers.php

REM 如果系统PHP不可用，尝试使用便携式PHP
if %ERRORLEVEL% NEQ 0 (
    echo 系统PHP不可用，尝试使用便携式PHP...
    if exist portable_php\php.exe (
        portable_php\php.exe -f fix_bom_markers.php
    ) else (
        echo 错误：无法找到PHP执行程序。
        echo 请确保系统中安装了PHP或项目中包含便携式PHP。
        exit /b 1
    )
)

echo 完成！
pause