# 下载和解压PHP的PowerShell脚本
Write-Host "准备下载和解压PHP..."

# 设置TLS 1.2
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

# 创建portable_php目录
if (-not (Test-Path -Path "portable_php")) {
    New-Item -Path "portable_php" -ItemType Directory
    Write-Host "创建portable_php目录"
}

# 下载PHP
$phpUrl = "https://windows.php.net/downloads/releases/php-8.2.8-nts-Win32-vs16-x64.zip"
$phpZip = "php.zip"

Write-Host "正在下载PHP，请稍候..."
Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip

# 解压PHP
Write-Host "正在解压PHP..."
Expand-Archive -Path $phpZip -DestinationPath "portable_php" -Force

# 创建php.ini
$phpIni = @"
[PHP]
display_errors = On
error_reporting = E_ALL
memory_limit = 512M
default_charset = "UTF-8"
extension_dir = "ext"
extension=openssl
extension=mbstring
"@

Set-Content -Path "portable_php\php.ini" -Value $phpIni

# 清理
Remove-Item -Path $phpZip

Write-Host "PHP已成功下载和解压到portable_php目录"