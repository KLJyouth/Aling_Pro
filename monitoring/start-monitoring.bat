@echo off
TITLE AlingAi API监控系统

echo ====================================================
echo             AlingAi API监控系统启动器
echo ====================================================
echo.
echo 正在检查环境...

REM 检查Node.js
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [错误] 未找到Node.js，请先安装Node.js
    goto :error
)

echo [成功] 已检测到Node.js

REM 设置环境变量
set NODE_ENV=development
set PORT=3000

echo.
echo 正在启动AlingAi API监控系统...
echo.
echo 功能亮点:
echo  * 实时API监控和指标收集
echo  * 智能告警系统
echo  * 可视化仪表盘
echo  * 健康检查
echo  * 模块化组件系统
echo.
echo 监控系统前端将在 http://localhost:%PORT% 上启动
echo 访问 http://localhost:%PORT%/components-demo 可查看组件示例
echo.
echo 按 Ctrl+C 可随时停止服务
echo.

REM 检查node_modules
if not exist "node_modules" (
    echo 首次运行，正在安装依赖...
    call npm install
    if %errorlevel% neq 0 goto :error
)

REM 启动服务
cd /d %~dp0
node src/index.js

if %errorlevel% neq 0 goto :error

goto :eof

:error
echo.
echo [错误] 启动失败，请检查上述错误信息
echo 按任意键退出...
pause >nul
exit /b 1 