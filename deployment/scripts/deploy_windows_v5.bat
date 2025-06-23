@echo off
setlocal enabledelayedexpansion

REM AlingAI Pro 5.0 Windows 部署脚本
REM 政企融合智能办公系统 Windows 环境自动部署工具

echo ==========================================
echo AlingAI Pro 5.0 Windows 部署脚本
echo 政企融合智能办公系统
echo ==========================================
echo.

REM 设置颜色
set "RED=[91m"
set "GREEN=[92m"
set "YELLOW=[93m"
set "BLUE=[94m"
set "NC=[0m"

REM 检查管理员权限
net session >nul 2>&1
if %errorLevel% == 0 (
    echo %GREEN%[INFO]%NC% 检测到管理员权限
) else (
    echo %RED%[ERROR]%NC% 请以管理员身份运行此脚本
    pause
    exit /b 1
)

REM 检查PHP环境
echo %BLUE%[INFO]%NC% 检查PHP环境...
php --version >nul 2>&1
if %errorLevel% == 0 (
    echo %GREEN%[SUCCESS]%NC% PHP已安装
    php --version
) else (
    echo %RED%[ERROR]%NC% 未检测到PHP，请先安装PHP 8.1或更高版本
    echo 推荐使用XAMPP或WampServer
    pause
    exit /b 1
)

REM 检查Composer
echo %BLUE%[INFO]%NC% 检查Composer...
composer --version >nul 2>&1
if %errorLevel% == 0 (
    echo %GREEN%[SUCCESS]%NC% Composer已安装
) else (
    echo %YELLOW%[WARNING]%NC% 未检测到Composer，正在下载安装...
    powershell -Command "Invoke-WebRequest -Uri https://getcomposer.org/installer -OutFile composer-setup.php"
    php composer-setup.php
    move composer.phar composer.exe
    del composer-setup.php
    echo %GREEN%[SUCCESS]%NC% Composer安装完成
)

REM 安装项目依赖
echo %BLUE%[INFO]%NC% 安装项目依赖...
if exist composer.json (
    composer install --optimize-autoloader
    echo %GREEN%[SUCCESS]%NC% 依赖安装完成
) else (
    echo %RED%[ERROR]%NC% 未找到composer.json文件，请确保在项目根目录运行
    pause
    exit /b 1
)

REM 创建必要目录
echo %BLUE%[INFO]%NC% 创建必要目录...
if not exist "storage" mkdir storage
if not exist "storage\logs" mkdir storage\logs
if not exist "storage\cache" mkdir storage\cache
if not exist "storage\sessions" mkdir storage\sessions
if not exist "storage\uploads" mkdir storage\uploads
if not exist "storage\backups" mkdir storage\backups
echo %GREEN%[SUCCESS]%NC% 目录创建完成

REM 配置环境变量
echo %BLUE%[INFO]%NC% 配置环境变量...
if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env
        echo %GREEN%[SUCCESS]%NC% 已创建.env文件
    ) else (
        echo %RED%[ERROR]%NC% 未找到.env.example文件
        pause
        exit /b 1
    )
) else (
    echo %YELLOW%[WARNING]%NC% .env文件已存在，跳过创建
)

REM 生成应用密钥
echo %BLUE%[INFO]%NC% 生成应用密钥...
powershell -Command "$key = [Convert]::ToBase64String((1..32 | ForEach {Get-Random -Maximum 256})); Write-Output $key" > temp_key.txt
set /p APP_KEY=<temp_key.txt
del temp_key.txt

REM 更新.env文件
echo %BLUE%[INFO]%NC% 更新配置文件...
powershell -Command "(Get-Content .env) -replace 'APP_ENV=.*', 'APP_ENV=development' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'APP_DEBUG=.*', 'APP_DEBUG=true' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'APP_KEY=.*', 'APP_KEY=base64:%APP_KEY%' | Set-Content .env"

REM 检查数据库配置
echo %BLUE%[INFO]%NC% 检查数据库配置...
echo 请确保已配置数据库连接信息在.env文件中
echo 默认配置：
echo   - 数据库: alingai_pro_v5
echo   - 用户名: root
echo   - 密码: （请在.env中设置）
echo.

REM 询问是否运行数据库迁移
set /p MIGRATE="是否运行数据库迁移? (y/n): "
if /i "%MIGRATE%"=="y" (
    echo %BLUE%[INFO]%NC% 运行数据库迁移...
    php migrate.php migrate
    if %errorLevel% == 0 (
        echo %GREEN%[SUCCESS]%NC% 数据库迁移完成
        
        set /p SEED="是否初始化示例数据? (y/n): "
        if /i "!SEED!"=="y" (
            php migrate.php seed
            echo %GREEN%[SUCCESS]%NC% 示例数据初始化完成
        )
    ) else (
        echo %RED%[ERROR]%NC% 数据库迁移失败，请检查数据库配置
    )
)

REM 创建启动脚本
echo %BLUE%[INFO]%NC% 创建启动脚本...
(
echo @echo off
echo echo 启动AlingAI Pro 5.0开发服务器...
echo echo 访问地址: http://localhost:8080
echo echo 按Ctrl+C停止服务器
echo echo.
echo cd /d "%~dp0"
echo php -S localhost:8080 -t public public/index_v5.php
) > start_server.bat

(
echo @echo off
echo echo 停止AlingAI Pro 5.0服务器...
echo taskkill /f /im php.exe
echo echo 服务器已停止
) > stop_server.bat

echo %GREEN%[SUCCESS]%NC% 启动脚本创建完成

REM 创建管理脚本
echo %BLUE%[INFO]%NC% 创建管理脚本...
(
echo @echo off
echo setlocal enabledelayedexpansion
echo.
echo echo ==========================================
echo echo AlingAI Pro 5.0 管理工具
echo echo ==========================================
echo echo.
echo echo 1. 启动开发服务器
echo echo 2. 运行数据库迁移
echo echo 3. 初始化示例数据
echo echo 4. 查看系统状态
echo echo 5. 清理缓存
echo echo 6. 备份数据
echo echo 7. 退出
echo echo.
echo set /p choice="请选择操作 (1-7): "
echo.
echo if "!choice!"=="1" (
echo     call start_server.bat
echo ^) else if "!choice!"=="2" (
echo     php migrate.php migrate
echo ^) else if "!choice!"=="3" (
echo     php migrate.php seed
echo ^) else if "!choice!"=="4" (
echo     echo 系统状态检查...
echo     php -m ^| findstr -i "pdo mysql redis"
echo     echo.
echo     echo PHP版本:
echo     php --version
echo ^) else if "!choice!"=="5" (
echo     echo 清理缓存...
echo     if exist "storage\cache" rmdir /s /q storage\cache
echo     mkdir storage\cache
echo     echo 缓存清理完成
echo ^) else if "!choice!"=="6" (
echo     echo 创建数据备份...
echo     set backup_name=backup_%%date:~0,4%%%%date:~5,2%%%%date:~8,2%%_%%time:~0,2%%%%time:~3,2%%%%time:~6,2%%
echo     set backup_name=!backup_name: =0!
echo     mkdir storage\backups\!backup_name! 2^>nul
echo     xcopy /e /i /y storage\* storage\backups\!backup_name!\storage\ ^>nul
echo     copy .env storage\backups\!backup_name!\ ^>nul
echo     echo 备份已创建: storage\backups\!backup_name!
echo ^) else if "!choice!"=="7" (
echo     exit /b
echo ^) else (
echo     echo 无效选择
echo ^)
echo.
echo pause
) > admin.bat

echo %GREEN%[SUCCESS]%NC% 管理脚本创建完成

REM 创建Windows服务安装脚本
echo %BLUE%[INFO]%NC% 创建Windows服务脚本...
(
echo @echo off
echo REM AlingAI Pro 5.0 Windows服务安装脚本
echo echo 安装AlingAI Pro 5.0 Windows服务...
echo.
echo REM 检查NSSM
echo nssm version ^>nul 2^>^&1
echo if %%errorLevel%% neq 0 (
echo     echo 错误: 未找到NSSM，请先下载并安装NSSM
echo     echo 下载地址: https://nssm.cc/download
echo     pause
echo     exit /b 1
echo ^)
echo.
echo REM 安装服务
echo set "APP_PATH=%%~dp0"
echo nssm install "AlingAI Pro 5.0" php
echo nssm set "AlingAI Pro 5.0" Application "%%APP_PATH%%php"
echo nssm set "AlingAI Pro 5.0" AppParameters "-S 0.0.0.0:8080 -t %%APP_PATH%%public %%APP_PATH%%public/index_v5.php"
echo nssm set "AlingAI Pro 5.0" AppDirectory "%%APP_PATH%%"
echo nssm set "AlingAI Pro 5.0" DisplayName "AlingAI Pro 5.0"
echo nssm set "AlingAI Pro 5.0" Description "AlingAI Pro 5.0 政企融合智能办公系统"
echo nssm set "AlingAI Pro 5.0" Start SERVICE_AUTO_START
echo.
echo echo 服务安装完成
echo echo 使用以下命令管理服务:
echo echo   启动服务: net start "AlingAI Pro 5.0"
echo echo   停止服务: net stop "AlingAI Pro 5.0"
echo echo   卸载服务: nssm remove "AlingAI Pro 5.0" confirm
echo.
echo pause
) > install_service.bat

REM 创建开发者工具脚本
echo %BLUE%[INFO]%NC% 创建开发者工具...
(
echo @echo off
echo echo AlingAI Pro 5.0 开发者工具
echo echo ==========================================
echo echo.
echo echo 1. 代码格式化检查
echo echo 2. 运行单元测试
echo echo 3. 生成API文档
echo echo 4. 性能测试
echo echo 5. 安全扫描
echo echo 6. 返回主菜单
echo echo.
echo set /p dev_choice="请选择操作 (1-6): "
echo.
echo if "%%dev_choice%%"=="1" (
echo     echo 运行代码格式化检查...
echo     if exist vendor\bin\php-cs-fixer.bat (
echo         vendor\bin\php-cs-fixer.bat fix --dry-run --diff
echo     ^) else (
echo         echo PHP-CS-Fixer未安装
echo     ^)
echo ^) else if "%%dev_choice%%"=="2" (
echo     echo 运行单元测试...
echo     if exist vendor\bin\phpunit.bat (
echo         vendor\bin\phpunit.bat
echo     ^) else (
echo         echo PHPUnit未安装
echo     ^)
echo ^) else if "%%dev_choice%%"=="3" (
echo     echo 生成API文档...
echo     php -f generate_docs.php
echo ^) else if "%%dev_choice%%"=="4" (
echo     echo 运行性能测试...
echo     php performance_test.php
echo ^) else if "%%dev_choice%%"=="5" (
echo     echo 运行安全扫描...
echo     php security_scan.php
echo ^) else if "%%dev_choice%%"=="6" (
echo     call admin.bat
echo ^)
echo.
echo pause
) > dev_tools.bat

REM 创建配置向导
echo %BLUE%[INFO]%NC% 创建配置向导...
(
echo @echo off
echo setlocal enabledelayedexpansion
echo echo AlingAI Pro 5.0 配置向导
echo echo ==========================================
echo echo.
echo echo 请按提示输入配置信息:
echo echo.
echo.
echo set /p db_host="数据库主机 [localhost]: "
echo if "!db_host!"=="" set db_host=localhost
echo.
echo set /p db_port="数据库端口 [3306]: "
echo if "!db_port!"=="" set db_port=3306
echo.
echo set /p db_name="数据库名称 [alingai_pro_v5]: "
echo if "!db_name!"=="" set db_name=alingai_pro_v5
echo.
echo set /p db_user="数据库用户名 [root]: "
echo if "!db_user!"=="" set db_user=root
echo.
echo set /p db_pass="数据库密码: "
echo.
echo set /p redis_host="Redis主机 [127.0.0.1]: "
echo if "!redis_host!"=="" set redis_host=127.0.0.1
echo.
echo set /p redis_port="Redis端口 [6379]: "
echo if "!redis_port!"=="" set redis_port=6379
echo.
echo echo 更新配置文件...
echo powershell -Command "^(Get-Content .env^) -replace 'DB_HOST=.*', 'DB_HOST=!db_host!' | Set-Content .env"
echo powershell -Command "^(Get-Content .env^) -replace 'DB_PORT=.*', 'DB_PORT=!db_port!' | Set-Content .env"
echo powershell -Command "^(Get-Content .env^) -replace 'DB_DATABASE=.*', 'DB_DATABASE=!db_name!' | Set-Content .env"
echo powershell -Command "^(Get-Content .env^) -replace 'DB_USERNAME=.*', 'DB_USERNAME=!db_user!' | Set-Content .env"
echo powershell -Command "^(Get-Content .env^) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=!db_pass!' | Set-Content .env"
echo powershell -Command "^(Get-Content .env^) -replace 'REDIS_HOST=.*', 'REDIS_HOST=!redis_host!' | Set-Content .env"
echo powershell -Command "^(Get-Content .env^) -replace 'REDIS_PORT=.*', 'REDIS_PORT=!redis_port!' | Set-Content .env"
echo.
echo echo 配置已更新！
echo echo.
echo pause
) > config_wizard.bat

REM 测试系统
echo %BLUE%[INFO]%NC% 测试系统组件...
php -m | findstr -i "pdo" >nul
if %errorLevel% == 0 (
    echo %GREEN%[SUCCESS]%NC% PDO扩展已安装
) else (
    echo %RED%[ERROR]%NC% PDO扩展未安装
)

php -m | findstr -i "json" >nul
if %errorLevel% == 0 (
    echo %GREEN%[SUCCESS]%NC% JSON扩展已安装
) else (
    echo %RED%[ERROR]%NC% JSON扩展未安装
)

php -m | findstr -i "mbstring" >nul
if %errorLevel% == 0 (
    echo %GREEN%[SUCCESS]%NC% mbstring扩展已安装
) else (
    echo %RED%[ERROR]%NC% mbstring扩展未安装
)

REM 创建快捷方式脚本
echo %BLUE%[INFO]%NC% 创建桌面快捷方式...
(
echo Set WshShell = CreateObject^("WScript.Shell"^)
echo Set oShellLink = WshShell.CreateShortcut^(WshShell.SpecialFolders^("Desktop"^) ^& "\AlingAI Pro 5.0 管理.lnk"^)
echo oShellLink.TargetPath = "%cd%\admin.bat"
echo oShellLink.WorkingDirectory = "%cd%"
echo oShellLink.Description = "AlingAI Pro 5.0 管理工具"
echo oShellLink.Save
echo.
echo Set oShellLink2 = WshShell.CreateShortcut^(WshShell.SpecialFolders^("Desktop"^) ^& "\启动AlingAI Pro 5.0.lnk"^)
echo oShellLink2.TargetPath = "%cd%\start_server.bat"
echo oShellLink2.WorkingDirectory = "%cd%"
echo oShellLink2.Description = "启动AlingAI Pro 5.0开发服务器"
echo oShellLink2.Save
) > create_shortcuts.vbs

cscript //nologo create_shortcuts.vbs >nul 2>&1
del create_shortcuts.vbs
if %errorLevel% == 0 (
    echo %GREEN%[SUCCESS]%NC% 桌面快捷方式创建完成
)

REM 完成部署
echo.
echo ==========================================
echo %GREEN%部署完成！%NC%
echo ==========================================
echo.
echo 系统信息:
echo   - 项目目录: %cd%
echo   - 配置文件: .env
echo   - 日志目录: storage\logs
echo   - 备份目录: storage\backups
echo.
echo 管理工具:
echo   - 主管理面板: admin.bat
echo   - 配置向导: config_wizard.bat
echo   - 开发者工具: dev_tools.bat
echo   - 启动服务器: start_server.bat
echo   - 停止服务器: stop_server.bat
echo.
echo 数据库管理:
echo   - 运行迁移: php migrate.php migrate
echo   - 初始化数据: php migrate.php seed
echo   - 查看状态: php migrate.php status
echo.
echo 下一步操作:
echo   1. 运行 config_wizard.bat 配置数据库连接
echo   2. 运行 start_server.bat 启动开发服务器
echo   3. 访问 http://localhost:8080 查看应用
echo.
echo %YELLOW%注意:%NC% 生产环境部署请使用专业的Web服务器(如IIS、Apache、Nginx)
echo.

REM 询问是否立即启动
set /p START_NOW="是否立即启动配置向导? (y/n): "
if /i "%START_NOW%"=="y" (
    call config_wizard.bat
)

echo.
echo 感谢使用AlingAI Pro 5.0！
pause
