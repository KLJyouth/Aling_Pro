@echo off
echo ========================================
echo  AlingAi Pro 数据库集成测试启动器
echo ========================================
echo.

echo 正在启动API模拟服务器...
start "API Server" cmd /k "node test-api-server.js"

echo 等待服务器启动...
timeout /t 3 /nobreak > nul

echo 正在打开测试页面...
start "" "test-database-integration.html"

echo.
echo ✅ 测试环境已启动！
echo.
echo 📋 已启动的服务：
echo   - API模拟服务器: http://localhost:3001
echo   - 测试页面: test-database-integration.html
echo.
echo 💡 测试说明：
echo   1. 测试页面会自动检查认证状态
echo   2. 点击各种测试按钮验证功能
echo   3. 查看控制台输出了解详细信息
echo   4. API服务器会随机返回认证/非认证状态
echo.
echo 按任意键关闭此窗口...
pause > nul
