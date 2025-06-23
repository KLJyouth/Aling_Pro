@echo off
echo AlingAi API监控系统启动脚本
echo ==================================

REM 检查Node.js是否安装
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
  echo 错误: 未找到Node.js，请先安装Node.js
  exit /b 1
)

REM 检查npm是否可用
where npm >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
  echo 错误: 未找到npm，请确保npm在PATH中
  exit /b 1
)

echo 正在安装依赖...
cd /d %~dp0
npm install

if %ERRORLEVEL% NEQ 0 (
  echo 错误: 安装依赖失败
  exit /b 1
)

echo 依赖安装成功!
echo 正在启动监控系统...

REM 创建日志目录
if not exist logs mkdir logs

REM 启动监控系统
npm start

echo 监控系统已关闭
exit /b 0 