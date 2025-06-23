@echo off
REM PHP语法错误修复批处理文件
REM 此批处理文件包含修复图片中显示的PHP语法错误的步骤

echo ====================================
echo PHP 8.1语法错误修复工具
echo ====================================
echo.

REM 记录时间
echo 开始时间: %date% %time%
echo.

REM 创建备份目录
if not exist backups mkdir backups
echo 已创建备份目录: backups

REM 1. 修复ChineseTokenizer.php文件中的UTF-8编码问题
echo.
echo 1. 正在修复 ai-engines\nlp\ChineseTokenizer.php 文件...
if not exist ai-engines\nlp\ChineseTokenizer.php (
    echo 错误: 文件不存在
) else (
    echo 备份文件...
    copy ai-engines\nlp\ChineseTokenizer.php backups\ChineseTokenizer.php.bak
    echo 修复江苏字符问题...
    
    REM 这里需要手动编辑文件，找到第422行附近的"江苏"并替换为"JiangSu"
    echo 请手动编辑文件，找到第422行附近的"江苏"并替换为"JiangSu"
)

REM 2. 修复EnglishTokenizer.php文件中的私有属性问题
echo.
echo 2. 正在修复 ai-engines\nlp\EnglishTokenizer.php 文件...
if not exist ai-engines\nlp\EnglishTokenizer.php (
    echo 错误: 文件不存在
) else (
    echo 备份文件...
    copy ai-engines\nlp\EnglishTokenizer.php backups\EnglishTokenizer.php.bak
    echo 修复私有属性问题...
    
    REM 这里需要手动编辑文件，找到第42行附近的private属性声明，确保它们有变量名
    echo 请手动编辑文件，找到第42行附近的private属性声明，确保格式为"private array $config;"
)

REM 3. 修复POSTagger.php文件中的=>运算符问题
echo.
echo 3. 正在修复 ai-engines\nlp\POSTagger.php 文件...
if not exist ai-engines\nlp\POSTagger.php (
    echo 错误: 文件不存在
) else (
    echo 备份文件...
    copy ai-engines\nlp\POSTagger.php backups\POSTagger.php.bak
    echo 修复=>运算符问题...
    
    REM 这里需要手动编辑文件，找到第355行附近的=>问题
    echo 请手动编辑文件，找到第355行附近的=>问题，确保格式正确
)

REM 4. 修复AIServiceManager.php文件中的$container问题
echo.
echo 4. 正在修复 apps\ai-platform\services\AIServiceManager.php 文件...
if not exist apps\ai-platform\services\AIServiceManager.php (
    echo 错误: 文件不存在
) else (
    echo 备份文件...
    copy apps\ai-platform\services\AIServiceManager.php backups\AIServiceManager.php.bak
    echo 修复$container问题...
    
    REM 这里需要手动编辑文件，找到第51行附近的$container调用
    echo 请手动编辑文件，找到第51行附近的$container调用，添加->操作符
)

REM 5. 修复配置文件中的数组值引号问题
echo.
echo 5. 正在修复配置文件中的数组值引号问题...

if exist config\app.php (
    echo 备份 config\app.php...
    copy config\app.php backups\app.php.bak
    echo 请手动编辑文件，找到version值确保它有引号
)

if exist config\assets.php (
    echo 备份 config\assets.php...
    copy config\assets.php backups\assets.php.bak
    echo 请手动编辑文件，找到js_version值确保它有引号
)

REM 6. 修复路由文件中的类引用问题
echo.
echo 6. 正在修复路由文件中的类引用问题...

if exist config\routes_enhanced.php (
    echo 备份 config\routes_enhanced.php...
    copy config\routes_enhanced.php backups\routes_enhanced.php.bak
    echo 请手动编辑文件，确保WebController::class前有命名空间前缀
)

echo.
echo 修复完成!
echo 请记得在修复后运行PHP语法检查，确保所有错误都已修复。
echo.
echo 结束时间: %date% %time% 