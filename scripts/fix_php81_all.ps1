# 修复PHP 8.1语法错误的PowerShell脚本
# 用于批量处理所有PHP文件

Write-Host "开始修复PHP 8.1语法兼容性问题..."

# 检查PHP命令是否可用
try {
    $phpVersion = php -v
    Write-Host "PHP版本信息: $phpVersion"
} 
catch {
    Write-Host "错误: PHP命令无法执行。请确保PHP已安装并添加到PATH环境变量中。" -ForegroundColor Red
    exit 1
}

# 运行PHP语法修复脚本
Write-Host "运行PHP语法修复脚本..."

try {
    # 运行fix_php81_syntax.php脚本
    php scripts/fix_php81_syntax.php
    
    # 运行fix_php_syntax_errors.php脚本
    php scripts/fix_php_syntax_errors.php
    
    # 最后进行语法检查
    php scripts/check_php_syntax.php
    
    Write-Host "PHP 8.1语法修复完成!" -ForegroundColor Green
} 
catch {
    Write-Host "修复过程中出现错误: $_" -ForegroundColor Red
    exit 1
}

Write-Host "所有PHP语法修复完成。"
