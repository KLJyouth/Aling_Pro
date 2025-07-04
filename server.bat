:: 检查工作目录
if not exist public\index.html (
    echo [错误] 未找到主程序文件 (public\index.html)!
    echo 请确保您在正确的目录中运行此脚本。
    pause
    exit /b 1
)

:: 显示启动信息
echo [信息] 正在启动 AlingAi Pro 开发服务器...
echo [信息] 访问地址: http://localhost:%PORT%
echo [信息] 按 Ctrl+C 停止服务器
echo.

:: 启动PHP开发服务器
cd public
echo [启动] 服务器启动时间: %TIME%
php -S localhost:%PORT% router.php

:: 如果服务器意外终止
echo.
echo [信息] 服务器已停止运行。
pause