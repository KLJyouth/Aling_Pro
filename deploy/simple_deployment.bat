@echo off
REM AlingAI Pro 5.0 简化部署脚本 (无需管理员权限)
REM 版本: 5.0.0-Final

echo 🚀 AlingAI Pro 5.0 简化部署启动
echo ==================================================
echo 开始基础部署流程...
echo 时间: %date% %time%
echo ==================================================

REM 检查PHP
where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] PHP未安装或不在PATH中
    pause
    exit /b 1
)

echo [INFO] PHP检查通过

REM 创建必要目录
echo [INFO] 创建目录结构...
if not exist logs mkdir logs
if not exist logs\security mkdir logs\security
if not exist logs\websocket mkdir logs\websocket
if not exist logs\system mkdir logs\system
if not exist storage\cache mkdir storage\cache
if not exist storage\uploads mkdir storage\uploads
if not exist storage\backups mkdir storage\backups
if not exist public\assets mkdir public\assets

echo [INFO] 目录结构创建完成

REM 运行健康检查
echo [INFO] 运行系统健康检查...
php quick_health_check.php

REM 初始化数据库
echo [INFO] 初始化数据库系统...
echo <?php > init_db.php
echo require_once 'src/Database/DatabaseManagerSimple.php'; >> init_db.php
echo $db = \AlingAI\Database\DatabaseManager::getInstance(); >> init_db.php
echo $db->initializeSystemDefaults(); >> init_db.php
echo echo "数据库初始化完成\n"; >> init_db.php

php init_db.php
del init_db.php

REM 启动Web服务器 (后台)
echo [INFO] 启动内置Web服务器...
echo 服务器地址: http://localhost:8000
echo 按Ctrl+C停止服务器

REM 启动PHP内置服务器
start /b php -S localhost:8000 -t public public/index.php

REM 等待服务器启动
timeout /t 3 /nobreak > nul

REM 测试服务器
echo [INFO] 测试Web服务器连接...
curl -s http://localhost:8000 > nul 2>&1
if %errorLevel% equ 0 (
    echo [SUCCESS] Web服务器启动成功！
) else (
    echo [WARNING] Web服务器可能未正常启动
)

echo ==================================================
echo 🎉 基础部署完成！
echo ==================================================
echo 📊 部署摘要:
echo   ✓ 目录结构已创建
echo   ✓ 数据库已初始化  
echo   ✓ Web服务器已启动
echo.
echo 🌐 访问地址:
echo   • 主页: http://localhost:8000
echo   • 监控面板: http://localhost:8000/security/dashboard
echo   • API接口: http://localhost:8000/api/status
echo.
echo 💡 下一步:
echo   1. 打开浏览器访问 http://localhost:8000
echo   2. 查看实时监控面板
echo   3. 运行完整系统测试: php complete_system_test.php
echo.
echo 🔧 管理命令:
echo   • 停止服务: deploy\stop_services.bat
echo   • 重启服务: deploy\restart_services.bat  
echo   • 检查状态: deploy\check_status.bat
echo ==================================================

pause
