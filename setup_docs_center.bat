@echo off
echo === AlingAi_pro Docusaurus文档中心安装脚本 ===
echo.

REM 检查Node.js是否已安装
where node >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo 未检测到Node.js! 请先安装Node.js: https://nodejs.org/
    exit /b 1
)

REM 创建Docusaurus项目
echo 正在创建Docusaurus项目...
npx @docusaurus/init@latest init docs-website classic

REM 创建文档分类目录
echo 正在创建文档分类目录...
mkdir docs-website\docs\standards
mkdir docs-website\docs\guides
mkdir docs-website\docs\references

REM 复制文档
echo 正在复制文档...
xcopy /E /I /Y docs\standards\*.* docs-website\docs\standards\
xcopy /E /I /Y docs\guides\*.* docs-website\docs\guides\
xcopy /E /I /Y docs\references\*.* docs-website\docs\references\

echo.
echo Docusaurus项目创建完成!
echo.
echo 后续步骤:
echo 1. 进入docs-website目录
echo 2. 修改文档元数据和侧边栏配置
echo 3. 执行 'npm start' 启动开发服务器
echo 4. 访问 http://localhost:3000 查看文档中心
pause 