@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM AlingAI Pro 5.0 Windows 部署脚本
REM 适用于 Windows Server 2019+ / Windows 10+

set "ALINGAI_VERSION=5.0.0"
set "INSTALL_DIR=C:\AlingAI-Pro"
set "DATA_DIR=C:\AlingAI-Data"
set "LOG_DIR=C:\AlingAI-Logs"
set "SERVICE_NAME=AlingAI-Pro"
set "PHP_VERSION=8.3"
set "MYSQL_VERSION=8.0"
set "REDIS_VERSION=7.0"
set "NODE_VERSION=18"

REM 颜色代码
set "GREEN=[92m"
set "RED=[91m"
set "YELLOW=[93m"
set "BLUE=[94m"
set "CYAN=[96m"
set "NC=[0m"

echo %CYAN%
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║        AlingAI Pro 5.0 政企融合智能办公系统                 ║
echo ║               Windows 生产环境部署工具                      ║
echo ║                                                              ║
echo ║                    版本: %ALINGAI_VERSION%                           ║
echo ║                发布日期: 2025-06-09                          ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo %NC%

REM 检查管理员权限
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo %RED%错误: 请以管理员身份运行此脚本%NC%
    pause
    exit /b 1
)

echo %GREEN%[INFO] 开始 AlingAI Pro 5.0 Windows 部署...%NC%
echo.

REM 检查系统要求
echo %BLUE%检查系统要求...%NC%

REM 检查Windows版本
for /f "tokens=4-5 delims=. " %%i in ('ver') do set VERSION=%%i.%%j
if %VERSION% lss 10.0 (
    echo %RED%错误: 需要 Windows 10 或 Windows Server 2019 及以上版本%NC%
    pause
    exit /b 1
)

REM 检查内存
for /f "skip=1" %%p in ('wmic computersystem get TotalPhysicalMemory') do (
    set "MEMORY=%%p"
    goto :memory_check
)
:memory_check
set /a MEMORY_GB=%MEMORY:~0,-10%
if %MEMORY_GB% lss 4 (
    echo %RED%错误: 至少需要 4GB 内存，当前: %MEMORY_GB%GB%NC%
    pause
    exit /b 1
)

REM 检查磁盘空间
for /f "skip=1" %%d in ('wmic logicaldisk where caption^="C:" get size') do (
    set "DISK_SIZE=%%d"
    goto :disk_check
)
:disk_check
set /a DISK_GB=%DISK_SIZE:~0,-10%
if %DISK_GB% lss 20 (
    echo %RED%错误: 至少需要 20GB 磁盘空间%NC%
    pause
    exit /b 1
)

echo %GREEN%系统要求检查通过%NC%
echo.

REM 创建目录结构
echo %BLUE%创建目录结构...%NC%
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%DATA_DIR%" mkdir "%DATA_DIR%"
if not exist "%DATA_DIR%\uploads" mkdir "%DATA_DIR%\uploads"
if not exist "%DATA_DIR%\cache" mkdir "%DATA_DIR%\cache"
if not exist "%DATA_DIR%\sessions" mkdir "%DATA_DIR%\sessions"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"
echo %GREEN%目录结构创建完成%NC%
echo.

REM 下载并安装依赖
echo %BLUE%安装系统依赖...%NC%

REM 检查 Chocolatey
where choco >nul 2>&1
if %errorLevel% neq 0 (
    echo %YELLOW%安装 Chocolatey...%NC%
    powershell -Command "Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))"
    if %errorLevel% neq 0 (
        echo %RED%Chocolatey 安装失败%NC%
        pause
        exit /b 1
    )
    REM 刷新环境变量
    call refreshenv.cmd
)

REM 安装 PHP
echo %YELLOW%安装 PHP %PHP_VERSION%...%NC%
choco install php --version=%PHP_VERSION% -y
if %errorLevel% neq 0 (
    echo %RED%PHP 安装失败%NC%
    pause
    exit /b 1
)

REM 安装 Composer
echo %YELLOW%安装 Composer...%NC%
choco install composer -y
if %errorLevel% neq 0 (
    echo %RED%Composer 安装失败%NC%
    pause
    exit /b 1
)

REM 安装 MySQL
echo %YELLOW%安装 MySQL %MYSQL_VERSION%...%NC%
choco install mysql --version=%MYSQL_VERSION% -y
if %errorLevel% neq 0 (
    echo %RED%MySQL 安装失败%NC%
    pause
    exit /b 1
)

REM 安装 Redis
echo %YELLOW%安装 Redis...%NC%
choco install redis-64 -y
if %errorLevel% neq 0 (
    echo %RED%Redis 安装失败%NC%
    pause
    exit /b 1
)

REM 安装 Node.js
echo %YELLOW%安装 Node.js %NODE_VERSION%...%NC%
choco install nodejs --version=%NODE_VERSION% -y
if %errorLevel% neq 0 (
    echo %RED%Node.js 安装失败%NC%
    pause
    exit /b 1
)

REM 安装 Git
echo %YELLOW%安装 Git...%NC%
choco install git -y

REM 安装 IIS
echo %YELLOW%安装 IIS...%NC%
dism /online /enable-feature /featurename:IIS-WebServerRole /all
dism /online /enable-feature /featurename:IIS-WebServer /all
dism /online /enable-feature /featurename:IIS-CommonHttpFeatures /all
dism /online /enable-feature /featurename:IIS-HttpErrors /all
dism /online /enable-feature /featurename:IIS-HttpLogging /all
dism /online /enable-feature /featurename:IIS-Security /all
dism /online /enable-feature /featurename:IIS-RequestFiltering /all
dism /online /enable-feature /featurename:IIS-StaticContent /all
dism /online /enable-feature /featurename:IIS-DefaultDocument /all
dism /online /enable-feature /featurename:IIS-DirectoryBrowsing /all

echo %GREEN%系统依赖安装完成%NC%
echo.

REM 刷新环境变量
call refreshenv.cmd

REM 部署应用代码
echo %BLUE%部署应用代码...%NC%
cd /d "%INSTALL_DIR%"

REM 检查当前目录是否包含应用代码
if exist "composer.json" if exist "src\Core\Application.php" (
    echo %YELLOW%从当前目录复制代码...%NC%
    xcopy /E /Y /Q "%~dp0*" "%INSTALL_DIR%\"
) else (
    echo %YELLOW%从 GitHub 克隆代码...%NC%
    git clone https://github.com/AlingAI/AlingAI-Pro.git "%INSTALL_DIR%"
    cd /d "%INSTALL_DIR%"
    git checkout v5.0.0 2>nul || git checkout main
)

REM 安装 PHP 依赖
echo %YELLOW%安装 PHP 依赖...%NC%
composer install --no-dev --optimize-autoloader
if %errorLevel% neq 0 (
    echo %RED%PHP 依赖安装失败%NC%
    pause
    exit /b 1
)

REM 安装前端依赖
if exist "package.json" (
    echo %YELLOW%安装前端依赖...%NC%
    npm install
    if %errorLevel% neq 0 (
        echo %YELLOW%前端依赖安装失败，继续...%NC%
    ) else (
        npm run build
    )
)

echo %GREEN%应用代码部署完成%NC%
echo.

REM 配置 MySQL
echo %BLUE%配置 MySQL...%NC%
set /p MYSQL_ROOT_PASSWORD=请输入 MySQL root 密码: 

REM 启动 MySQL 服务
net start mysql80
if %errorLevel% neq 0 (
    echo %RED%MySQL 服务启动失败%NC%
    pause
    exit /b 1
)

REM 创建数据库和用户
echo %YELLOW%创建数据库...%NC%
mysql -u root -p%MYSQL_ROOT_PASSWORD% -e "CREATE DATABASE alingai_pro_5 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p%MYSQL_ROOT_PASSWORD% -e "CREATE USER 'alingai'@'localhost' IDENTIFIED BY '%MYSQL_ROOT_PASSWORD%';"
mysql -u root -p%MYSQL_ROOT_PASSWORD% -e "GRANT ALL PRIVILEGES ON alingai_pro_5.* TO 'alingai'@'localhost';"
mysql -u root -p%MYSQL_ROOT_PASSWORD% -e "FLUSH PRIVILEGES;"

echo %GREEN%MySQL 配置完成%NC%
echo.

REM 启动 Redis 服务
echo %BLUE%启动 Redis 服务...%NC%
net start redis
if %errorLevel% neq 0 (
    echo %YELLOW%Redis 服务启动失败，尝试手动启动...%NC%
    start /b redis-server
)

REM 配置应用
echo %BLUE%配置应用...%NC%
cd /d "%INSTALL_DIR%"

REM 生成随机密钥
for /f %%i in ('powershell -Command "[System.Web.Security.Membership]::GeneratePassword(32, 0)"') do set APP_KEY=%%i
for /f %%i in ('powershell -Command "[System.Web.Security.Membership]::GeneratePassword(64, 0)"') do set JWT_SECRET=%%i

REM 创建 .env 文件
(
echo # AlingAI Pro 5.0 Windows 生产环境配置
echo APP_ENV=production
echo APP_DEBUG=false
echo APP_URL=http://localhost
echo.
echo # 数据库配置
echo DB_CONNECTION=mysql
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_DATABASE=alingai_pro_5
echo DB_USERNAME=alingai
echo DB_PASSWORD=%MYSQL_ROOT_PASSWORD%
echo.
echo # Redis配置
echo REDIS_HOST=127.0.0.1
echo REDIS_PORT=6379
echo REDIS_PASSWORD=
echo.
echo # 缓存配置
echo CACHE_DRIVER=redis
echo SESSION_DRIVER=redis
echo QUEUE_CONNECTION=redis
echo.
echo # 日志配置
echo LOG_CHANNEL=daily
echo LOG_LEVEL=info
echo LOG_PATH=%LOG_DIR%
echo.
echo # 安全配置
echo APP_KEY=%APP_KEY%
echo JWT_SECRET=%JWT_SECRET%
echo.
echo # 文件存储
echo FILESYSTEM_DISK=local
echo UPLOAD_PATH=%DATA_DIR%\uploads
echo UPLOAD_MAX_SIZE=100M
echo.
echo # 监控配置
echo MONITORING_ENABLED=true
echo BACKUP_ENABLED=true
) > .env

echo %GREEN%应用配置完成%NC%
echo.

REM 运行数据库迁移
echo %BLUE%运行数据库迁移...%NC%
if exist "bin\migrate.php" (
    php bin\migrate.php
    if %errorLevel% neq 0 (
        echo %YELLOW%数据库迁移失败，请手动运行%NC%
    )
)

REM 配置 IIS
echo %BLUE%配置 IIS...%NC%

REM 导入 URL Rewrite 模块
if not exist "%windir%\system32\inetsrv\rewrite.dll" (
    echo %YELLOW%正在下载 URL Rewrite 模块...%NC%
    powershell -Command "Invoke-WebRequest -Uri 'https://download.microsoft.com/download/1/2/8/128E2E22-C1B9-44A4-BE2A-5859ED1D4592/rewrite_amd64_en-US.msi' -OutFile '%TEMP%\urlrewrite.msi'"
    msiexec /i "%TEMP%\urlrewrite.msi" /quiet
)

REM 创建 web.config
(
echo ^<?xml version="1.0" encoding="UTF-8"?^>
echo ^<configuration^>
echo     ^<system.webServer^>
echo         ^<rewrite^>
echo             ^<rules^>
echo                 ^<rule name="AlingAI Pro Router" stopProcessing="true"^>
echo                     ^<match url=".*" /^>
echo                     ^<conditions logicalGrouping="MatchAll"^>
echo                         ^<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" /^>
echo                         ^<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" /^>
echo                     ^</conditions^>
echo                     ^<action type="Rewrite" url="index.php" /^>
echo                 ^</rule^>
echo             ^</rules^>
echo         ^</rewrite^>
echo         ^<defaultDocument^>
echo             ^<files^>
echo                 ^<clear /^>
echo                 ^<add value="index.php" /^>
echo             ^</files^>
echo         ^</defaultDocument^>
echo     ^</system.webServer^>
echo ^</configuration^>
) > public\web.config

REM 创建 IIS 网站
%windir%\system32\inetsrv\appcmd.exe delete site "Default Web Site" 2>nul
%windir%\system32\inetsrv\appcmd.exe add site /name:"AlingAI Pro" /physicalPath:"%INSTALL_DIR%\public" /bindings:http/*:80:

REM 配置 FastCGI
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='C:\tools\php\php-cgi.exe',arguments='',maxInstances='4',idleTimeout='300',activityTimeout='30',requestTimeout='90',instanceMaxRequests='1000',protocol='NamedPipe',flushNamedPipe='False']" /commit:apphost

REM 配置处理程序映射
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/handlers /+"[name='PHP-FastCGI',path='*.php',verb='GET,HEAD,POST,PUT,DELETE',modules='FastCgiModule',scriptProcessor='C:\tools\php\php-cgi.exe',resourceType='Either',requireAccess='Script']" /commit:apphost

echo %GREEN%IIS 配置完成%NC%
echo.

REM 创建 Windows 服务
echo %BLUE%创建 Windows 服务...%NC%

REM 创建服务脚本
(
echo @echo off
echo cd /d "%INSTALL_DIR%"
echo php bin\server.php start
) > "%INSTALL_DIR%\start_service.bat"

REM 使用 NSSM 创建服务（如果可用）
where nssm >nul 2>&1
if %errorLevel% equ 0 (
    nssm install "%SERVICE_NAME%" "%INSTALL_DIR%\start_service.bat"
    nssm set "%SERVICE_NAME%" DisplayName "AlingAI Pro 5.0"
    nssm set "%SERVICE_NAME%" Description "AlingAI Pro 5.0 政企融合智能办公系统"
    nssm set "%SERVICE_NAME%" Start SERVICE_AUTO_START
) else (
    echo %YELLOW%NSSM 未安装，请手动创建服务或使用任务计划程序%NC%
)

REM 启动服务
echo %BLUE%启动服务...%NC%
iisreset /start
net start w3svc

if "%SERVICE_NAME%" neq "" (
    net start "%SERVICE_NAME%" 2>nul
)

echo %GREEN%服务启动完成%NC%
echo.

REM 运行健康检查
echo %BLUE%运行健康检查...%NC%

REM 检查 Web 服务
powershell -Command "try { Invoke-WebRequest -Uri 'http://localhost' -UseBasicParsing | Out-Null; Write-Host 'Web服务检查通过' -ForegroundColor Green } catch { Write-Host 'Web服务检查失败' -ForegroundColor Red }"

REM 检查数据库连接
mysql -u alingai -p%MYSQL_ROOT_PASSWORD% -e "SELECT 1;" >nul 2>&1
if %errorLevel% equ 0 (
    echo %GREEN%数据库连接检查通过%NC%
) else (
    echo %RED%数据库连接检查失败%NC%
)

REM 检查 Redis 连接
redis-cli ping >nul 2>&1
if %errorLevel% equ 0 (
    echo %GREEN%Redis连接检查通过%NC%
) else (
    echo %YELLOW%Redis连接检查失败%NC%
)

echo.

REM 创建启动桌面快捷方式
echo %BLUE%创建桌面快捷方式...%NC%
powershell -Command "$WshShell = New-Object -comObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%USERPROFILE%\Desktop\AlingAI Pro 5.0.lnk'); $Shortcut.TargetPath = 'http://localhost'; $Shortcut.Save()"

REM 显示部署信息
echo.
echo %GREEN%
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║            🎉 AlingAI Pro 5.0 部署成功！                    ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo %NC%

echo %CYAN%部署信息:%NC%
echo • 安装目录: %INSTALL_DIR%
echo • 数据目录: %DATA_DIR%
echo • 日志目录: %LOG_DIR%
echo • PHP 版本: %PHP_VERSION%
echo • MySQL 版本: %MYSQL_VERSION%
echo • Redis 版本: %REDIS_VERSION%
echo • Node.js 版本: %NODE_VERSION%
echo.

echo %CYAN%访问信息:%NC%
echo • 网站地址: http://localhost
echo • 管理后台: http://localhost/admin
echo • API接口: http://localhost/api
echo.

echo %CYAN%数据库信息:%NC%
echo • 数据库名: alingai_pro_5
echo • 用户名: alingai
echo • 密码: %MYSQL_ROOT_PASSWORD%
echo.

echo %CYAN%服务管理:%NC%
echo • IIS 管理器: inetmgr
echo • 重启 IIS: iisreset
echo • MySQL 服务: net start/stop mysql80
echo • Redis 服务: net start/stop redis

if "%SERVICE_NAME%" neq "" (
    echo • AlingAI 服务: net start/stop "%SERVICE_NAME%"
)

echo.

echo %CYAN%重要文件:%NC%
echo • 应用配置: %INSTALL_DIR%\.env
echo • IIS 配置: %INSTALL_DIR%\public\web.config
echo • PHP 配置: C:\tools\php\php.ini
echo.

echo %YELLOW%注意事项:%NC%
echo • 请妥善保管数据库密码
echo • 建议定期备份数据
echo • 查看 IIS 日志以确保服务正常运行
echo • 如需配置 HTTPS，请在 IIS 管理器中添加 SSL 证书
echo.

echo %GREEN%感谢使用 AlingAI Pro 5.0！%NC%
echo.

REM 询问是否打开浏览器
set /p open_browser=是否现在打开浏览器访问系统？(Y/n): 
if /i "!open_browser!" neq "n" (
    start http://localhost
)

pause
exit /b 0
