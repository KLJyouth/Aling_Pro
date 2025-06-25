@echo off
echo ===================================
echo AlingAi Pro PHP错误修复验证工具
echo ===================================
echo 时间: %date% %time%
echo.

REM 创建备份目录
set BACKUP_DIR=backups\manual_fix_verify_%date:~0,4%%date:~5,2%%date:~8,2%
mkdir %BACKUP_DIR%
echo 已创建备份目录: %BACKUP_DIR%

REM 复制关键目录到备份
echo 正在创建备份...
xcopy /E /I /Y ai-engines\nlp %BACKUP_DIR%\ai-engines\nlp
xcopy /E /I /Y apps\ai-platform\Services\KnowledgeGraph %BACKUP_DIR%\apps\ai-platform\Services\KnowledgeGraph
echo 备份完成

echo.
echo ===================================
echo 步骤1: 验证命名空间修复
echo ===================================
echo 检查ChineseTokenizer.php命名空间...
findstr /C:"namespace AlingAi\Engines\NLP;" ai-engines\nlp\ChineseTokenizer.php
echo.

echo 检查EnglishTokenizer.php命名空间...
findstr /C:"namespace AlingAi\Engines\NLP;" ai-engines\nlp\EnglishTokenizer.php
echo.

echo 检查POSTagger.php命名空间...
findstr /C:"namespace AlingAi\Engines\NLP;" ai-engines\nlp\POSTagger.php
echo.

echo ===================================
echo 步骤2: 验证接口实现修复
echo ===================================
echo 检查POSTagger.php的tokenize方法签名...
findstr /C:"public function tokenize(string $text, array $options = []): array" ai-engines\nlp\POSTagger.php
echo.

echo ===================================
echo 步骤3: 验证重复方法修复
echo ===================================
echo 检查KnowledgeGraphProcessor.php中的BaseKGEngine类...
findstr /C:"public function process()" apps\ai-platform\Services\KnowledgeGraph\KnowledgeGraphProcessor.php
echo 如果没有输出，表示重复方法已被删除
echo.

echo ===================================
echo 步骤4: 验证PHP语法
echo ===================================
echo 验证ChineseTokenizer.php语法...
php -l ai-engines\nlp\ChineseTokenizer.php
echo.

echo 验证EnglishTokenizer.php语法...
php -l ai-engines\nlp\EnglishTokenizer.php
echo.

echo 验证POSTagger.php语法...
php -l ai-engines\nlp\POSTagger.php
echo.

echo 验证KnowledgeGraphProcessor.php语法...
php -l apps\ai-platform\Services\KnowledgeGraph\KnowledgeGraphProcessor.php
echo.

echo ===================================
echo 步骤5: 运行接口实现检查
echo ===================================
php check_interface_implementations.php --detailed
echo.

echo ===================================
echo 验证过程完成
echo ===================================
echo 请检查上述输出，确认所有修复是否成功
echo.

pause 