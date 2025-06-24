# 简单的PHP下载脚本
# 用于下载和解压PHP便携版

# 定义PHP版本和下载URL
$phpVersion = "8.1.0"
$downloadUrl = "https://windows.php.net/downloads/releases/archives/php-$phpVersion-nts-Win32-vs16-x64.zip"
$phpZipFile = "php_temp.zip"
$extractPath = "portable_php"

Write-Host "开始下载PHP $phpVersion..."

# 创建目录
if (-not (Test-Path $extractPath)) {
    New-Item -ItemType Directory -Path $extractPath -Force | Out-Null
}

# 下载PHP
try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $phpZipFile
    Write-Host "PHP下载完成"
} catch {
    Write-Host "PHP下载失败: $_" -ForegroundColor Red
    exit 1
}

# 解压PHP
try {
    Write-Host "正在解压PHP..."
    Expand-Archive -Path $phpZipFile -DestinationPath $extractPath -Force
    Write-Host "PHP解压完成"
} catch {
    Write-Host "PHP解压失败: $_" -ForegroundColor Red
    exit 1
}

# 创建PHP配置文件
$phpIni = @"
memory_limit = 1024M
max_execution_time = 300
"@

Set-Content -Path "$extractPath\php.ini" -Value $phpIni -Encoding UTF8
Write-Host "PHP配置文件创建完成"

# 检查是否成功
if (Test-Path "$extractPath\php.exe") {
    Write-Host "PHP安装成功，您现在可以运行验证和修复脚本" -ForegroundColor Green
} else {
    Write-Host "PHP安装失败，未找到php.exe" -ForegroundColor Red
    exit 1
}

# 清理
Remove-Item $phpZipFile -Force
Write-Host "临时文件清理完成" 