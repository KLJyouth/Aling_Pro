@echo off
REM 修复截图中显示的PHP语法错误
REM 通过调用已有的工具实现

echo ======================================================
echo 截图错误修复工具
echo 用于修复unexpected token类型的PHP语法错误
echo ======================================================
echo.

REM 检查PHP环境
if exist portable_php\php.exe (
    echo 使用便携式PHP环境
    set PHP_CMD=portable_php\php.exe
) else (
    where php >nul 2>nul
    if %errorlevel% equ 0 (
        echo 使用系统PHP环境
        set PHP_CMD=php
    ) else (
        echo 错误: 未找到PHP环境，请先安装PHP或解压portable_php
        exit /b 1
    )
)

echo 使用PHP: %PHP_CMD%
echo.

REM 创建备份目录
set BACKUP_DIR=backups\screenshot_fix_%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_DIR=%BACKUP_DIR: =0%

echo 创建备份目录: %BACKUP_DIR%
if not exist %BACKUP_DIR% mkdir %BACKUP_DIR%

REM 备份关键文件
echo 备份关键文件...

REM 配置文件
echo 备份配置文件...
if exist config\*.php (
    if not exist %BACKUP_DIR%\config mkdir %BACKUP_DIR%\config
    copy config\*.php %BACKUP_DIR%\config\
)

REM 应用文件
echo 备份应用文件...
if exist apps\ai-platform\Services\CV\*.php (
    if not exist %BACKUP_DIR%\apps\ai-platform\Services\CV mkdir %BACKUP_DIR%\apps\ai-platform\Services\CV /p
    copy apps\ai-platform\Services\CV\*.php %BACKUP_DIR%\apps\ai-platform\Services\CV\
)

if exist apps\ai-platform\Services\KnowledgeGraph\*.php (
    if not exist %BACKUP_DIR%\apps\ai-platform\Services\KnowledgeGraph mkdir %BACKUP_DIR%\apps\ai-platform\Services\KnowledgeGraph /p
    copy apps\ai-platform\Services\KnowledgeGraph\*.php %BACKUP_DIR%\apps\ai-platform\Services\KnowledgeGraph\
)

if exist apps\ai-platform\Services\Speech\*.php (
    if not exist %BACKUP_DIR%\apps\ai-platform\Services\Speech mkdir %BACKUP_DIR%\apps\ai-platform\Services\Speech /p
    copy apps\ai-platform\Services\Speech\*.php %BACKUP_DIR%\apps\ai-platform\Services\Speech\
)

if exist public\admin\api\documentation\*.php (
    if not exist %BACKUP_DIR%\public\admin\api\documentation mkdir %BACKUP_DIR%\public\admin\api\documentation /p
    copy public\admin\api\documentation\*.php %BACKUP_DIR%\public\admin\api\documentation\
)

echo 备份完成！

REM 运行修复工具
echo 运行修复工具...

REM 1. 首先运行简单修复工具
echo 1. 修复基本语法错误...
%PHP_CMD% -f fix_php_simple.php apps\ai-platform\Services\CV\ComputerVisionProcessor.php
%PHP_CMD% -f fix_php_simple.php apps\ai-platform\Services\KnowledgeGraph\KnowledgeGraphProcessor.php
%PHP_CMD% -f fix_php_simple.php apps\ai-platform\Services\Speech\SpeechProcessor.php
%PHP_CMD% -f fix_php_simple.php apps\blockchain\Services\BlockchainServiceManager.php
%PHP_CMD% -f fix_php_simple.php apps\blockchain\Services\SmartContractManager.php
%PHP_CMD% -f fix_php_simple.php apps\blockchain\Services\WalletManager.php

REM 2. 修复配置文件问题
echo 2. 修复配置文件问题...
%PHP_CMD% -f fix_php_syntax_errors.php

REM 3. 修复中文编码问题
echo 3. 修复中文编码问题...
%PHP_CMD% -f fix_chinese_tokenizer.php

REM 4. 运行综合修复工具
echo 4. 运行综合修复工具...
%PHP_CMD% -f fix_all_php_errors.php

echo.
echo 修复过程完成！请检查以下文件:
echo - SCREENSHOT_ERRORS_FIX_REPORT.md (如果存在)
echo - PHP_ERRORS_FIX_REPORT.md
echo.

echo 备份文件位于: %BACKUP_DIR%

echo.
echo 按任意键继续...
pause > nul