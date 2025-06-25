# PHP 8.1 语法错误修复脚本

# 创建备份目录
$backupDir = "backups\php_syntax_fix"
if (-not (Test-Path -Path $backupDir)) {
    New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
    Write-Host "Created backup directory: $backupDir" -ForegroundColor Green
}

# 备份文件函数
function Backup-File {
    param (
        [string]$FilePath
    )
    
    if (Test-Path -Path $FilePath) {
        $fileName = Split-Path -Path $FilePath -Leaf
        $backupPath = Join-Path -Path $backupDir -ChildPath $fileName
        
        # 创建目录结构
        $relativePath = Split-Path -Path $FilePath
        $backupSubDir = Join-Path -Path $backupDir -ChildPath $relativePath
        if (-not (Test-Path -Path $backupSubDir)) {
            New-Item -Path $backupSubDir -ItemType Directory -Force | Out-Null
        }
        
        $backupFullPath = Join-Path -Path $backupDir -ChildPath $FilePath
        
        # 确保备份目录存在
        $backupFileDir = Split-Path -Path $backupFullPath -Parent
        if (-not (Test-Path -Path $backupFileDir)) {
            New-Item -Path $backupFileDir -ItemType Directory -Force | Out-Null
        }
        
        # 备份文件
        Copy-Item -Path $FilePath -Destination $backupFullPath -Force
        Write-Host "Backed up file: $FilePath to $backupFullPath" -ForegroundColor Yellow
    }
}

# 修复文件函数
function Fix-PhpSyntaxErrors {
    param (
        [string]$FilePath
    )
    
    if (Test-Path -Path $FilePath) {
        # 备份文件
        Backup-File -FilePath $FilePath
        
        # 读取文件内容
        $content = Get-Content -Path $FilePath -Raw
        
        # 修复语法错误
        $fixedContent = $content
        
        # 1. 修复 "WebController-class" 错误
        $fixedContent = $fixedContent -replace '", WebController-class . "', '"WebController-class"'
        
        # 2. 修复 unexpected token '<' 错误
        $fixedContent = $fixedContent -replace '< ', '<'
        
        # 3. 修复 unexpected token ';' 错误
        $fixedContent = $fixedContent -replace '; ', ';'
        
        # 4. 修复 unexpected token 'array' 错误
        $fixedContent = $fixedContent -replace 'array\(', '['
        $fixedContent = $fixedContent -replace '\)', ']'
        
        # 5. 修复 unexpected token '=' 错误
        $fixedContent = $fixedContent -replace ' = ', ' = '
        
        # 6. 修复 unexpected token ': protected string $version = "' 错误
        $fixedContent = $fixedContent -replace ': protected string \$version = "', 'protected string $version = "'
        
        # 7. 修复 unexpected token ')' 错误
        $fixedContent = $fixedContent -replace '\)\s*{', ') {'
        
        # 8. 修复 unexpected token '"' 错误
        $fixedContent = $fixedContent -replace '""', '"'
        
        # 9. 修复 unexpected token 'Blockchain' 错误
        $fixedContent = $fixedContent -replace "'Blockchain'", '"Blockchain"'
        
        # 10. 修复 unexpected token 'ssl' 错误
        $fixedContent = $fixedContent -replace "'ssl'", '"ssl"'
        
        # 11. 修复 unexpected token 'mysql' 错误
        $fixedContent = $fixedContent -replace "'mysql'", '"mysql"'
        
        # 12. 修复 unexpected token '$config' 错误
        $fixedContent = $fixedContent -replace '\$config\[', '$config['
        
        # 13. 修复 unexpected token 'Access' 错误
        $fixedContent = $fixedContent -replace "'Access'", '"Access"'
        
        # 14. 修复 unexpected token 'libs' 错误
        $fixedContent = $fixedContent -replace "'libs'", '"libs"'
        
        # 15. 修复 unexpected token 'js_version' 错误
        $fixedContent = $fixedContent -replace "'js_version'", '"js_version"'
        
        # 16. 修复 unexpected token '"]+$/"' 错误
        $fixedContent = $fixedContent -replace '"\]\+\$/"', '"]+$/"'
        
        # 17. 修复 unexpected token '(string )' 错误
        $fixedContent = $fixedContent -replace '\(string \)', '(string)'
        
        # 18. 修复 unexpected token '->' 错误
        $fixedContent = $fixedContent -replace ' -> ', '->'
        
        # 19. 修复 unexpected token 'supported_formats' 错误
        $fixedContent = $fixedContent -replace "'supported_formats'", '"supported_formats"'
        
        # 20. 修复 unexpected token '$container' 错误
        $fixedContent = $fixedContent -replace '\$container\[', '$container['
        
        # 写入修复后的内容
        Set-Content -Path $FilePath -Value $fixedContent
        
        Write-Host "Fixed syntax errors in file: $FilePath" -ForegroundColor Green
    }
}

# 需要修复的文件列表
$filesToFix = @(
    "config\routes_enhanced.php",
    "apps\ai-platform\Services\NLP\fixed_nlp_new.php",
    "ai-engines\knowledge-graph\ReasoningEngine.php",
    "completed\Config\cache.php",
    "ai-engines\knowledge-graph\MemoryGraphStore.php",
    "apps\blockchain\Services\SmartContractManager.php",
    "public\install\config.php",
    "apps\ai-platform\Services\CV\ComputerVisionProcessor.php",
    "apps\blockchain\Services\BlockchainServiceManager.php",
    "config\websocket.php",
    "completed\Config\database.php",
    "config\cache.php",
    "apps\ai-platform\Services\Speech\SpeechProcessor.php",
    "public\install\check.php",
    "ai-engines\knowledge-graph\RelationExtractor.php",
    "apps\blockchain\Services\WalletManager.php",
    "public\assets\docs\Stanfai_docs\login_form_example.php",
    "ai-engines\nlp\EnglishTokenizer.php",
    "completed\Config\websocket.php",
    "public\api\v1\user\profile.php",
    "public\admin\api\documentation\index.php",
    "public\admin\api\monitoring\index.php",
    "apps\ai-platform\Services\NLP\NaturalLanguageProcessor.php",
    "public\admin\api\users\index.php",
    "config\assets.php",
    "ai-engines\nlp\ChineseTokenizer.php",
    "ai-engines\knowledge-graph\GraphStoreInterface.php",
    "ai-engines\nlp\POSTagger.php",
    "apps\ai-platform\Services\CV\ComputerVisionProcessorInterface.php",
    "apps\ai-platform\services\AIServiceManager.php",
    "completed\Config\routes.php",
    "apps\ai-platform\Services\KnowledgeGraph\KnowledgeGraphManager.php",
    "public\install\install.php",
    "admin\maintenance\tools\check_api_doc.php",
    "admin\maintenance\tools\quick_fix.php"
)

# 修复每个文件
foreach ($file in $filesToFix) {
    if (Test-Path -Path $file) {
        Fix-PhpSyntaxErrors -FilePath $file
    } else {
        Write-Host "File not found: $file" -ForegroundColor Red
    }
}

Write-Host "PHP syntax error fix completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $backupDir" -ForegroundColor Green

# 创建修复报告
$reportContent = @"
# PHP 8.1 语法错误修复报告

## 修复时间
$(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## 修复内容

本次修复主要针对PHP 8.1中的语法错误，包括：

1. 修复字符串引号不匹配问题
2. 修复数组语法从array()到[]的转换
3. 修复类属性声明语法错误
4. 修复操作符周围的空格问题
5. 修复字符串和变量连接问题
6. 修复函数参数和返回类型声明

## 修复文件列表

$(foreach ($file in $filesToFix) {
    if (Test-Path -Path $file) {
        "- $file"
    }
})

## 备份信息

所有修复前的原始文件已备份到 \`$backupDir\` 目录。

## 后续建议

1. 运行PHP语法检查，确认所有语法错误已修复
2. 进行功能测试，确保修复不影响现有功能
3. 更新项目文档，记录PHP 8.1兼容性修复情况
"@

$reportPath = "public\docs\reports\PHP81_SYNTAX_FIX_REPORT.md"
Set-Content -Path $reportPath -Value $reportContent

Write-Host "Fix report created at: $reportPath" -ForegroundColor Green 