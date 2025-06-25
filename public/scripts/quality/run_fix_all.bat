@echo off
echo ===================================
echo AlingAi Pro PHP错误修复工具
echo ===================================
echo 时间: %date% %time%
echo.

REM 创建主备份目录
set BACKUP_DIR=backups\master_backup_%date:~0,4%%date:~5,2%%date:~8,2%
mkdir %BACKUP_DIR%
echo 已创建主备份目录: %BACKUP_DIR%

REM 复制关键目录到备份
echo 正在创建备份...
xcopy /E /I /Y ai-engines %BACKUP_DIR%\ai-engines
xcopy /E /I /Y apps %BACKUP_DIR%\apps
xcopy /E /I /Y config %BACKUP_DIR%\config
echo 备份完成

echo.
echo ===================================
echo 步骤1: 修复命名空间不一致问题
echo ===================================
php fix_scripts\fix_namespace_consistency.php
echo.

echo ===================================
echo 步骤2: 修复接口实现不完整问题
echo ===================================
php fix_scripts\fix_interface_implementation.php
echo.

echo ===================================
echo 步骤3: 修复重复方法问题
echo ===================================
php fix_scripts\fix_duplicate_methods.php
echo.

echo ===================================
echo 步骤4: 修复构造函数多余括号问题
echo ===================================
php fix_scripts\fix_constructor_brackets.php
echo.

echo ===================================
echo 步骤5: 修复私有变量错误声明问题
echo ===================================
php fix_scripts\fix_private_variables.php
echo.

echo ===================================
echo 步骤6: 验证修复结果
echo ===================================
php validate_fixed_files.php
echo.

echo ===================================
echo 修复过程完成
echo ===================================
echo 请查看各修复工具生成的报告以获取详细信息
echo.

pause 