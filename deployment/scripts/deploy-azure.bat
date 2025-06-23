@echo off
REM AlingAi Pro Azure部署脚本 (Windows版本)
REM 遵循Azure最佳实践，包含完整的部署验证和监控设置

echo 🚀 开始AlingAi Pro Azure部署...
echo ========================================

REM 检查必要工具
echo 🔍 检查必要工具...

where az >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Azure CLI未安装，请先安装Azure CLI
    pause
    exit /b 1
)

where azd >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Azure Developer CLI未安装，请先安装azd
    pause
    exit /b 1
)

echo ✅ 工具检查完成

REM 检查Azure登录状态
echo 🔐 检查Azure登录状态...

az account show >nul 2>nul
if %errorlevel% neq 0 (
    echo 请登录Azure...
    az login
)

REM 显示当前账户信息
echo 当前Azure账户：
az account show --query "{name:name, id:id, tenantId:tenantId}" -o table

echo ✅ Azure登录验证完成

REM 设置环境变量
echo ⚙️  设置部署环境...
echo 请输入以下配置信息：

set /p DB_PASSWORD="数据库管理员密码: "
set /p DEEPSEEK_API_KEY="DeepSeek API密钥: "
set /p SMTP_PASSWORD="SMTP邮件密码: "

echo ✅ 环境变量设置完成

REM 初始化azd项目
echo 🛠️  初始化Azure Developer CLI项目...

REM 检查是否已初始化
if not exist ".azure" (
    azd init --environment prod
)

echo ✅ azd项目初始化完成

REM 预览部署
echo 👀 预览部署计划...
echo 正在生成部署预览...

azd provision --preview

REM 等待用户确认
set /p CONTINUE="请检查上述部署计划。是否继续部署？(y/n): "
if /i not "%CONTINUE%"=="y" (
    echo ❌ 用户取消部署
    pause
    exit /b 1
)

echo ✅ 部署计划确认完成

REM 执行部署
echo 🚀 开始部署到Azure...

azd up --environment prod

if %errorlevel% equ 0 (
    echo ✅ Azure基础设施部署成功
) else (
    echo ❌ Azure基础设施部署失败
    pause
    exit /b 1
)

REM 部署后配置
echo ⚙️  执行部署后配置...

REM 获取Web App名称
for /f "tokens=*" %%i in ('az webapp list --query "[?contains(name, 'alingai-pro-prod')].name" -o tsv') do set WEB_APP_NAME=%%i

if "%WEB_APP_NAME%"=="" (
    echo ❌ 无法找到Web App
    pause
    exit /b 1
)

echo 找到Web App: %WEB_APP_NAME%

REM 部署应用代码
echo 📦 部署应用代码...
az webapp deployment source config-zip --name "%WEB_APP_NAME%" --resource-group "rg-alingai-pro-prod" --src deployment.zip

if %errorlevel% equ 0 (
    echo ✅ 应用代码部署成功
) else (
    echo ❌ 应用代码部署失败
    pause
    exit /b 1
)

REM 配置应用设置
echo ⚙️  配置应用设置...
az webapp config appsettings set --name "%WEB_APP_NAME%" --resource-group "rg-alingai-pro-prod" --settings "DB_PASSWORD=%DB_PASSWORD%" "DEEPSEEK_API_KEY=%DEEPSEEK_API_KEY%" "MAIL_PASSWORD=%SMTP_PASSWORD%"

echo ✅ 部署后配置完成

REM 验证部署
echo 🧪 验证部署...

REM 获取Web App URL
for /f "tokens=*" %%i in ('az webapp show --name "%WEB_APP_NAME%" --resource-group "rg-alingai-pro-prod" --query "defaultHostName" -o tsv') do set WEB_APP_URL=%%i

echo 应用URL: https://%WEB_APP_URL%

REM 健康检查
echo 执行健康检查...
powershell -Command "try { $response = Invoke-WebRequest -Uri 'https://%WEB_APP_URL%/api/system/status' -TimeoutSec 30; if ($response.StatusCode -eq 200) { Write-Host '✅ 应用健康检查通过' } else { Write-Host '❌ 应用健康检查失败' } } catch { Write-Host '❌ 健康检查请求失败' }"

echo ✅ 部署验证完成

REM 设置监控
echo 📊 设置应用监控...

REM 获取Application Insights密钥
for /f "tokens=*" %%i in ('az monitor app-insights component show --app "alingai-pro-prod-insights" --resource-group "rg-alingai-pro-prod" --query "instrumentationKey" -o tsv') do set INSTRUMENTATION_KEY=%%i

echo Application Insights密钥: %INSTRUMENTATION_KEY%

REM 配置应用设置
az webapp config appsettings set --name "%WEB_APP_NAME%" --resource-group "rg-alingai-pro-prod" --settings "APPINSIGHTS_INSTRUMENTATIONKEY=%INSTRUMENTATION_KEY%"

echo ✅ 监控设置完成

REM 显示部署结果
echo 🎉 部署完成！
echo ========================================
echo 应用URL: https://%WEB_APP_URL%
echo 管理门户: https://portal.azure.com
echo.
echo 下一步操作:
echo 1. 配置自定义域名和SSL证书
echo 2. 设置备份策略
echo 3. 配置监控警报
echo 4. 进行负载测试
echo.
echo 部署日志已保存到当前目录

REM 打开浏览器访问应用
echo 是否要打开浏览器访问应用？
set /p OPEN_BROWSER="(y/n): "
if /i "%OPEN_BROWSER%"=="y" (
    start https://%WEB_APP_URL%
)

echo.
echo 🎊 AlingAi Pro Azure部署完成！
pause
