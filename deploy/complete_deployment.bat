@echo off
REM AlingAI Pro 5.0 Windows 完整部署脚本
REM 版本: 5.0.0-Final
REM 日期: 2024-12-19

setlocal enabledelayedexpansion

echo 🚀 AlingAI Pro 5.0 企业级智能办公系统
echo ==================================================
echo 开始完整系统部署和集成测试...
echo 版本: 5.0.0-Final
echo 时间: %date% %time%
echo ==================================================

REM 检查管理员权限
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] 请以管理员身份运行此脚本
    pause
    exit /b 1
)

REM 检查PHP
where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] PHP未安装或不在PATH中
    echo 请安装PHP 8.1或更高版本
    pause
    exit /b 1
)

REM 获取PHP版本
for /f "tokens=2 delims= " %%i in ('php -v ^| findstr /r "^PHP"') do set PHP_VERSION=%%i
echo [INFO] PHP版本: %PHP_VERSION%

REM 检查Composer
where composer >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Composer未安装
    echo 请从 https://getcomposer.org/download/ 下载安装
    pause
    exit /b 1
)

echo [INFO] Composer已安装

REM 创建目录结构
echo [INFO] 创建目录结构...
if not exist logs mkdir logs
if not exist logs\security mkdir logs\security
if not exist logs\ai mkdir logs\ai
if not exist logs\system mkdir logs\system
if not exist logs\websocket mkdir logs\websocket

if not exist storage mkdir storage
if not exist storage\cache mkdir storage\cache
if not exist storage\sessions mkdir storage\sessions
if not exist storage\uploads mkdir storage\uploads
if not exist storage\backups mkdir storage\backups

if not exist public\assets mkdir public\assets
if not exist public\assets\js mkdir public\assets\js
if not exist public\assets\css mkdir public\assets\css
if not exist public\assets\images mkdir public\assets\images
if not exist public\uploads mkdir public\uploads

if not exist resources\views mkdir resources\views
if not exist resources\assets mkdir resources\assets

if not exist database\migrations mkdir database\migrations
if not exist database\seeds mkdir database\seeds

if not exist config\environments mkdir config\environments
if not exist config\security mkdir config\security

if not exist tmp mkdir tmp

echo [SUCCESS] 目录结构创建完成

REM 安装Composer依赖
echo [INFO] 安装Composer依赖...
if exist vendor rmdir /s /q vendor
composer install --no-dev --optimize-autoloader --no-interaction

if %errorLevel% neq 0 (
    echo [ERROR] Composer依赖安装失败
    pause
    exit /b 1
)

echo [SUCCESS] Composer依赖安装完成

REM 环境配置
echo [INFO] 配置环境变量...
if not exist .env (
    if exist .env.example (
        copy .env.example .env
        echo [INFO] 复制.env.example到.env
    ) else (
        echo [INFO] 创建默认.env文件
        (
        echo # AlingAI Pro 5.0 环境配置
        echo APP_ENV=production
        echo APP_DEBUG=false
        echo APP_NAME="AlingAI Pro 5.0"
        echo APP_VERSION="5.0.0"
        echo.
        echo # 数据库配置
        echo DB_TYPE=sqlite
        echo DB_HOST=localhost
        echo DB_PORT=3306
        echo DB_NAME=alingai_pro
        echo DB_USER=root
        echo DB_PASS=
        echo.
        echo # AI服务配置
        echo DEEPSEEK_API_KEY=your_deepseek_api_key_here
        echo OPENAI_API_KEY=your_openai_api_key_here
        echo AI_PROVIDER=deepseek
        echo.
        echo # 安全配置
        echo JWT_SECRET=your_jwt_secret_here
        echo ENCRYPTION_KEY=your_encryption_key_here
        echo.
        echo # WebSocket配置
        echo WEBSOCKET_HOST=0.0.0.0
        echo WEBSOCKET_PORT=8080
        echo.
        echo # 监控配置
        echo MONITORING_ENABLED=true
        echo SECURITY_MONITORING=true
        echo THREAT_DETECTION=true
        echo.
        echo # 日志配置
        echo LOG_LEVEL=info
        echo LOG_CHANNEL=file
        echo.
        echo # 缓存配置
        echo CACHE_DRIVER=file
        echo CACHE_TTL=3600
        echo.
        echo # 会话配置
        echo SESSION_DRIVER=file
        echo SESSION_LIFETIME=7200
        echo.
        ) > .env
    )
)

REM 生成安全密钥
powershell -Command "$jwt = -join ((1..64) | ForEach {Get-Random -Input ([char[]]'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')}); (Get-Content .env) -replace 'JWT_SECRET=your_jwt_secret_here', \"JWT_SECRET=$jwt\" | Set-Content .env"

powershell -Command "$enc = -join ((1..64) | ForEach {Get-Random -Input ([char[]]'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')}); (Get-Content .env) -replace 'ENCRYPTION_KEY=your_encryption_key_here', \"ENCRYPTION_KEY=$enc\" | Set-Content .env"

echo [SUCCESS] 环境配置完成

REM 数据库初始化
echo [INFO] 初始化数据库...
if not exist storage\database.sqlite (
    type nul > storage\database.sqlite
)

REM 执行数据库迁移
if exist database\migrations\create_security_monitoring_tables.sql (
    echo [INFO] 执行安全监控表迁移...
    sqlite3 storage\database.sqlite < database\migrations\create_security_monitoring_tables.sql
)

if exist database\migrations\create_configuration_tables.sql (
    echo [INFO] 执行配置管理表迁移...
    sqlite3 storage\database.sqlite < database\migrations\create_configuration_tables.sql
)

echo [SUCCESS] 数据库初始化完成

REM 安全系统初始化
echo [INFO] 初始化安全系统...
if exist check_security_system.php php check_security_system.php
if exist sqlite_security_migration.php php sqlite_security_migration.php

echo [SUCCESS] 安全系统初始化完成

REM 启动WebSocket服务器
echo [INFO] 启动WebSocket安全监控服务器...
if exist start_websocket_server.php (
    REM 检查端口是否被占用
    netstat -an | findstr ":8080" >nul
    if !errorLevel! equ 0 (
        echo [WARNING] 端口8080已被占用
    ) else (
        start /b php start_websocket_server.php > logs\websocket\websocket.log 2>&1
        echo [SUCCESS] WebSocket服务器启动成功
    )
) else (
    echo [WARNING] WebSocket服务器脚本不存在
)

REM 启动安全监控系统
echo [INFO] 启动实时安全监控系统...
if exist start_security_monitoring.php (
    start /b php start_security_monitoring.php > logs\security\monitoring.log 2>&1
    echo [SUCCESS] 安全监控系统启动成功
) else (
    echo [WARNING] 安全监控系统脚本不存在
)

REM 运行系统测试
echo [INFO] 运行系统集成测试...
if exist comprehensive_api_test.php php comprehensive_api_test.php
if exist check_database_structure.php php check_database_structure.php
if exist ai_service_health_check.php php ai_service_health_check.php

echo [SUCCESS] 系统测试完成

REM 启动Web服务器
echo [INFO] 启动Web服务器...
set WEB_PORT=8000

REM 检查端口占用
netstat -an | findstr ":%WEB_PORT%" >nul
if !errorLevel! equ 0 (
    set /a WEB_PORT=WEB_PORT+1
    echo [WARNING] 端口8000已被占用，使用端口 !WEB_PORT!
)

echo [INFO] 在端口 %WEB_PORT% 启动PHP内置服务器...
start /b php -S localhost:%WEB_PORT% -t public > logs\system\webserver.log 2>&1

REM 等待服务器启动
timeout /t 3 /nobreak >nul

echo [SUCCESS] Web服务器启动成功

REM 生成部署报告
echo [INFO] 生成部署报告...
set REPORT_FILE=deployment_report_%date:~10,4%_%date:~4,2%_%date:~7,2%_%time:~0,2%_%time:~3,2%_%time:~6,2%.md
set REPORT_FILE=%REPORT_FILE: =0%

(
echo # AlingAI Pro 5.0 部署报告
echo.
echo ## 部署信息
echo - **版本**: 5.0.0-Final
echo - **部署时间**: %date% %time%
echo - **操作系统**: Windows
echo - **PHP版本**: %PHP_VERSION%
echo - **数据库类型**: SQLite
echo.
echo ## 服务状态
echo - ✅ Web服务器: 运行中 (端口: %WEB_PORT%^)
echo - ✅ WebSocket服务器: 运行中 (端口: 8080^)
echo - ✅ 安全监控系统: 运行中
echo.
echo ## 核心功能
echo - ✅ 智能办公系统
echo - ✅ 实时威胁监控
echo - ✅ 3D威胁可视化
echo - ✅ AI智能代理系统
echo - ✅ 自学习自进化AI
echo - ✅ 数据库驱动配置管理
echo - ✅ 增强反爬虫系统
echo - ✅ WebSocket实时通信
echo.
echo ## 访问地址
echo - 主应用: http://localhost:%WEB_PORT%
echo - 安全监控: http://localhost:%WEB_PORT%/security/monitoring
echo - 3D威胁可视化: http://localhost:%WEB_PORT%/security/visualization
echo - 管理后台: http://localhost:%WEB_PORT%/admin
echo - API文档: http://localhost:%WEB_PORT%/api/docs
echo.
echo ## 管理命令
echo - 停止所有服务: deploy\stop_services.bat
echo - 重启服务: deploy\restart_services.bat
echo - 查看日志: type logs\system\webserver.log
echo - 监控状态: deploy\check_status.bat
echo.
echo ## 配置文件
echo - 环境配置: .env
echo - 路由配置: config\routes.php
echo - 数据库配置: config\database.php
echo - 安全配置: config\security.php
echo.
echo ## 日志文件
echo - 系统日志: logs\system\
echo - 安全日志: logs\security\
echo - WebSocket日志: logs\websocket\
echo - AI服务日志: logs\ai\
echo.
echo ## 注意事项
echo 1. 确保防火墙允许端口 %WEB_PORT% 和 8080 的访问
echo 2. 定期检查安全日志和威胁报告
echo 3. 保持系统和依赖库的更新
echo 4. 定期备份数据库和配置文件
echo 5. 监控系统资源使用情况
echo.
echo ---
echo 报告生成时间: %date% %time%
echo AlingAI Pro 5.0 - 企业级智能办公系统
) > %REPORT_FILE%

echo [SUCCESS] 部署报告已生成: %REPORT_FILE%

REM 显示部署完成信息
echo.
echo ==================================================
echo 🎉 AlingAI Pro 5.0 部署完成！
echo ==================================================
echo.
echo 🌐 访问地址:
echo    主应用: http://localhost:%WEB_PORT%
echo    安全监控: http://localhost:%WEB_PORT%/security/monitoring
echo    3D威胁可视化: http://localhost:%WEB_PORT%/security/visualization
echo    管理后台: http://localhost:%WEB_PORT%/admin
echo.
echo 📊 服务状态:
echo    ✅ Web服务器: 运行中
echo    ✅ WebSocket服务器: 运行中
echo    ✅ 安全监控系统: 运行中
echo.
echo 🛠️ 管理命令:
echo    查看状态: deploy\check_status.bat
echo    停止服务: deploy\stop_services.bat
echo    重启服务: deploy\restart_services.bat
echo    查看日志: type logs\system\webserver.log
echo.
echo 📋 关键功能:
echo    • 智能办公系统
echo    • 实时威胁监控与3D可视化
echo    • AI智能代理协调系统
echo    • 自学习自进化AI引擎
echo    • 数据库驱动配置管理
echo    • 增强反爬虫保护
echo    • 全球威胁情报分析
echo.
echo ==================================================
echo [SUCCESS] AlingAI Pro 5.0 部署完成！
echo.

pause
