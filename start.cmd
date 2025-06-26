@echo off
color 0B
cls
echo AlingAi Pro 5.1.0 量子安全管理系统
echo =======================================
echo 正在启动服务器...

:: 检查PHP是否安装
where php >nul 2>nul
if %errorlevel% neq 0 (
    color 0C
    echo [错误] 未找到PHP! 请确保已安装PHP并添加到系统PATH中。
    echo 您可以从 https://windows.php.net/download/ 下载PHP。
    pause
    exit /b 1
)

:: 显示PHP版本
php -v | findstr /C:"PHP"
if %errorlevel% neq 0 (
    echo [警告] 无法获取PHP版本信息，但PHP命令可用
) else (
    echo [信息] PHP检测正常
)

:: 检查router.php文件是否存在
if not exist "public\router.php" (
    color 0C
    echo [错误] 未找到router.php文件! 请确保您在正确的目录中。
    pause
    exit /b 1
)

:: 检查配置目录
if not exist "config" (
    echo [警告] 未找到config目录，正在创建...
    mkdir config
    echo [信息] 已创建config目录
)

:: 检查日志目录
if not exist "logs" (
    echo [警告] 未找到logs目录，正在创建...
    mkdir logs
    echo [信息] 已创建logs目录
)

:: 成功启动提示
color 0A
echo [成功] 初始化完成!
echo =======================================
echo 访问地址:
echo - 前端页面: http://localhost:8000
echo - 管理后台: http://localhost:8000/admin/
echo - API文档: http://localhost:8000/admin/api/documentation/
echo =======================================
echo 安全说明:
echo - 默认管理员账户: admin
echo - 请在首次登录后修改默认密码
echo =======================================
echo 按 Ctrl+C 停止服务器
echo.

:: 启动服务器
echo [信息] 切换到public目录并启动PHP服务器...
cd public
echo [信息] 执行: php -S localhost:8000 router.php
php -S localhost:8000 router.php

:: 如果服务器意外停止
color 0E
echo.
echo [信息] 服务器已停止运行。
echo 如需重新启动，请再次运行此脚本。
pause 