# 解压PHP脚本
# 将php.zip解压到portable_php目录

$zipFile = "php.zip"
$extractPath = "portable_php"

Write-Host "开始解压PHP..."

# 检查zip文件是否存在
if (!(Test-Path $zipFile)) {
    Write-Host "错误: PHP压缩包 $zipFile 不存在!" -ForegroundColor Red
    exit 1
}

# 创建目录
if (!(Test-Path $extractPath)) {
    New-Item -ItemType Directory -Path $extractPath -Force | Out-Null
    Write-Host "创建目录: $extractPath"
}

# 解压PHP
try {
    Write-Host "正在解压PHP..."
    Expand-Archive -Path $zipFile -DestinationPath $extractPath -Force
    Write-Host "PHP解压完成"
} catch {
    Write-Host "PHP解压失败: $_" -ForegroundColor Red
    exit 1
}

# 创建PHP配置文件
$phpIni = @"
extension_dir = "ext"
memory_limit = 1024M
max_execution_time = 300
display_errors = On
error_reporting = E_ALL
"@

Set-Content -Path "$extractPath\php.ini" -Value $phpIni -Encoding UTF8
Write-Host "PHP配置文件创建完成"

# 检查是否成功
if (Test-Path "$extractPath\php.exe") {
    Write-Host "PHP环境设置成功，您现在可以运行验证和修复脚本" -ForegroundColor Green
} else {
    Write-Host "PHP环境设置失败，未找到php.exe" -ForegroundColor Red
    exit 1
}

Write-Host "PHP版本信息:"
& ".\$extractPath\php.exe" -v