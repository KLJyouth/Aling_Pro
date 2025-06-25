@echo off
REM AlingAi Pro Windows 快速启动脚本
REM Quick Start Script for AlingAi Pro on Windows

echo ==========================================
echo   AlingAi Pro v2.0.0 快速启动
echo   三完编译版本 - 100%%完成
echo ==========================================

REM 检查PHP版本
echo 🔍 检查PHP环境...
php -r "echo '   PHP版本: ' . PHP_VERSION . PHP_EOL;"

REM 检查必要扩展
echo 🔍 检查PHP扩展...
php -m | findstr "pdo" >nul && echo    ✅ PDO扩展已安装 || echo    ❌ PDO扩展未安装
php -m | findstr "json" >nul && echo    ✅ JSON扩展已安装 || echo    ❌ JSON扩展未安装
php -m | findstr "curl" >nul && echo    ✅ CURL扩展已安装 || echo    ❌ CURL扩展未安装
php -m | findstr "mbstring" >nul && echo    ✅ MBString扩展已安装 || echo    ❌ MBString扩展未安装

REM 安装依赖
echo 📦 安装Composer依赖...
composer install --no-dev --optimize-autoloader

REM 检查配置文件
echo ⚙️ 检查配置文件...
if exist ".env" (
    echo    ✅ .env配置文件存在
) else (
    echo    ❌ .env配置文件不存在，请复制.env.example
)

REM 运行测试
echo 🧪 运行集成测试...
php bin/integration-test.php

REM 启动提示
echo.
echo 🚀 启动说明:
echo    1. 配置Web服务器指向public/目录
echo    2. 启动WebSocket服务: php bin/websocket-server.php
echo    3. 配置数据库连接
echo    4. 访问网站进行最终测试
echo.
echo ✅ AlingAi Pro 准备就绪！
echo ==========================================
pause
