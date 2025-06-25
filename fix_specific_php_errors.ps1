# 针对特定文件的PHP 8.1语法错误修复脚本

# 创建备份目录
$backupDir = "backups\php_specific_fix"
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

# 修复特定文件函数
function Fix-SpecificPhpFile {
    param (
        [string]$FilePath,
        [string]$ErrorType
    )
    
    if (Test-Path -Path $FilePath) {
        # 备份文件
        Backup-File -FilePath $FilePath
        
        # 读取文件内容
        $content = Get-Content -Path $FilePath -Raw
        $originalContent = $content
        
        # 根据错误类型应用特定修复
        switch ($ErrorType) {
            "WebController-class" {
                $content = $content -replace '", WebController-class . "', '"WebController-class"'
                $content = $content -replace '", WebController-class ."', '"WebController-class"'
                $content = $content -replace '", WebController-class."', '"WebController-class"'
            }
            "less-than" {
                $content = $content -replace '< ', '<'
            }
            "semicolon" {
                $content = $content -replace '; ', ';'
                $content = $content -replace ';  ', ';'
            }
            "array" {
                $content = $content -replace 'array\(', '['
                $content = $content -replace '\)', ']'
            }
            "equal" {
                $content = $content -replace ' = ', ' = '
                $content = $content -replace '=  ', '= '
                $content = $content -replace '  =', ' ='
            }
            "protected-string" {
                $content = $content -replace ': protected string \$version = "', 'protected string $version = "'
            }
            "parenthesis" {
                $content = $content -replace '\)\s*{', ') {'
            }
            "quote" {
                $content = $content -replace '""', '"'
            }
            "blockchain" {
                $content = $content -replace "'Blockchain'", '"Blockchain"'
            }
            "ssl" {
                $content = $content -replace "'ssl'", '"ssl"'
            }
            "mysql" {
                $content = $content -replace "'mysql'", '"mysql"'
            }
            "config" {
                $content = $content -replace "\\\$config\\['", '$config["'
                $content = $content -replace "\\]'", '"]'
            }
            "access" {
                $content = $content -replace "'Access'", '"Access"'
            }
            "libs" {
                $content = $content -replace "'libs'", '"libs"'
            }
            "js_version" {
                $content = $content -replace "'js_version'", '"js_version"'
            }
            "regex" {
                $content = $content -replace '"\]\+\$/"', '"]+$/"'
            }
            "string-cast" {
                $content = $content -replace '\(string \)', '(string)'
            }
            "arrow" {
                $content = $content -replace ' -> ', '->'
                $content = $content -replace ' ->  ', '->'
            }
            "supported_formats" {
                $content = $content -replace "'supported_formats'", '"supported_formats"'
            }
            "container" {
                $content = $content -replace "'\\$container\\['", '$container["'
                $content = $content -replace "\\]'", '"]'
            }
            "chinese-tokenizer" {
                $content = $content -replace '"\]\+\$\/u"', '"]+$/u"'
            }
        }
        
        # 如果内容有变化，则写入文件
        if ($content -ne $originalContent) {
            Set-Content -Path $FilePath -Value $content
            Write-Host "Fixed $ErrorType error in file: $FilePath" -ForegroundColor Green
        } else {
            Write-Host "No changes needed for $ErrorType in file: $FilePath" -ForegroundColor Cyan
        }
    } else {
        Write-Host "File not found: $FilePath" -ForegroundColor Red
    }
}

# 定义需要修复的文件和错误类型
$filesToFix = @(
    @{
        "path" = "config\routes_enhanced.php"
        "error" = "WebController-class"
    },
    @{
        "path" = "apps\ai-platform\Services\NLP\fixed_nlp_new.php"
        "error" = "less-than"
    },
    @{
        "path" = "ai-engines\knowledge-graph\ReasoningEngine.php"
        "error" = "semicolon"
    },
    @{
        "path" = "completed\Config\cache.php"
        "error" = "array"
    },
    @{
        "path" = "ai-engines\knowledge-graph\MemoryGraphStore.php"
        "error" = "equal"
    },
    @{
        "path" = "apps\blockchain\Services\SmartContractManager.php"
        "error" = "protected-string"
    },
    @{
        "path" = "public\install\config.php"
        "error" = "parenthesis"
    },
    @{
        "path" = "apps\ai-platform\Services\CV\ComputerVisionProcessor.php"
        "error" = "quote"
    },
    @{
        "path" = "apps\blockchain\Services\BlockchainServiceManager.php"
        "error" = "blockchain"
    },
    @{
        "path" = "config\websocket.php"
        "error" = "ssl"
    },
    @{
        "path" = "completed\Config\database.php"
        "error" = "mysql"
    },
    @{
        "path" = "config\cache.php"
        "error" = "array"
    },
    @{
        "path" = "apps\ai-platform\Services\Speech\SpeechProcessor.php"
        "error" = "config"
    },
    @{
        "path" = "public\install\check.php"
        "error" = "access"
    },
    @{
        "path" = "ai-engines\knowledge-graph\RelationExtractor.php"
        "error" = "semicolon"
    },
    @{
        "path" = "apps\blockchain\Services\WalletManager.php"
        "error" = "protected-string"
    },
    @{
        "path" = "public\assets\docs\Stanfai_docs\login_form_example.php"
        "error" = "libs"
    },
    @{
        "path" = "ai-engines\nlp\EnglishTokenizer.php"
        "error" = "semicolon"
    },
    @{
        "path" = "completed\Config\websocket.php"
        "error" = "ssl"
    },
    @{
        "path" = "public\api\v1\user\profile.php"
        "error" = "parenthesis"
    },
    @{
        "path" = "public\admin\api\documentation\index.php"
        "error" = "access"
    },
    @{
        "path" = "public\admin\api\monitoring\index.php"
        "error" = "access"
    },
    @{
        "path" = "apps\ai-platform\Services\NLP\NaturalLanguageProcessor.php"
        "error" = "config"
    },
    @{
        "path" = "public\admin\api\users\index.php"
        "error" = "access"
    },
    @{
        "path" = "config\assets.php"
        "error" = "js_version"
    },
    @{
        "path" = "ai-engines\nlp\ChineseTokenizer.php"
        "error" = "chinese-tokenizer"
    },
    @{
        "path" = "ai-engines\knowledge-graph\GraphStoreInterface.php"
        "error" = "string-cast"
    },
    @{
        "path" = "ai-engines\nlp\POSTagger.php"
        "error" = "arrow"
    },
    @{
        "path" = "apps\ai-platform\Services\CV\ComputerVisionProcessorInterface.php"
        "error" = "supported_formats"
    },
    @{
        "path" = "apps\ai-platform\services\AIServiceManager.php"
        "error" = "container"
    },
    @{
        "path" = "completed\Config\routes.php"
        "error" = "WebController-class"
    },
    @{
        "path" = "apps\ai-platform\Services\KnowledgeGraph\KnowledgeGraphManager.php"
        "error" = "config"
    },
    @{
        "path" = "public\install\install.php"
        "error" = "access"
    },
    @{
        "path" = "admin\maintenance\tools\check_api_doc.php"
        "error" = "quote"
    },
    @{
        "path" = "admin\maintenance\tools\quick_fix.php"
        "error" = "equal"
    }
)

# 修复每个文件
foreach ($file in $filesToFix) {
    Fix-SpecificPhpFile -FilePath $file.path -ErrorType $file.error
}

Write-Host "Specific PHP syntax error fixes completed!" -ForegroundColor Green
Write-Host "All original files have been backed up to $backupDir" -ForegroundColor Green

# 创建修复报告
$reportContent = "# 特定PHP语法错误修复报告`r`n`r`n"
$reportContent += "## 修复时间`r`n"
$reportContent += "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')`r`n`r`n"
$reportContent += "## 修复内容`r`n`r`n"

$reportContent += "本次修复针对以下特定的PHP 8.1语法错误：`r`n`r`n"

$errorTypes = $filesToFix | ForEach-Object { $_.error } | Sort-Object -Unique
foreach ($errorType in $errorTypes) {
    $reportContent += "### $errorType 错误`r`n`r`n"
    $reportContent += "修复的文件：`r`n`r`n"
    
    $filesWithError = $filesToFix | Where-Object { $_.error -eq $errorType }
    foreach ($file in $filesWithError) {
        if (Test-Path -Path $file.path) {
            $reportContent += "- $($file.path)`r`n"
        }
    }
    
    $reportContent += "`r`n"
}

$reportContent += "## 备份信息`r`n`r`n"
$reportContent += "所有修复前的原始文件已备份到 `backups\php_specific_fix` 目录。`r`n`r`n"

$reportPath = "public\docs\reports\PHP_SPECIFIC_FIX_REPORT.md"
Set-Content -Path $reportPath -Value $reportContent

Write-Host "Fix report created at: $reportPath" -ForegroundColor Green 