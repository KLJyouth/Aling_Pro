# 下载便携版PHP脚本
$downloadUrl = "https://windows.php.net/downloads/releases/php-8.2.8-nts-Win32-vs16-x64.zip"
$downloadPath = "php.zip"
$extractPath = "portable_php"

Write-Host "正在下载PHP便携版..." -ForegroundColor Green
try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $downloadPath
    Write-Host "下载完成!" -ForegroundColor Green
} catch {
    Write-Host "下载失败: $_" -ForegroundColor Red
    exit 1
}

Write-Host "正在解压文件..." -ForegroundColor Green
try {
    Expand-Archive -Path $downloadPath -DestinationPath $extractPath -Force
    Write-Host "解压完成!" -ForegroundColor Green
} catch {
    Write-Host "解压失败: $_" -ForegroundColor Red
    exit 1
}

# 创建php.ini文件
$phpIniPath = Join-Path -Path $extractPath -ChildPath "php.ini"
$phpIniContent = @"
[PHP]
display_errors = On
error_reporting = E_ALL
memory_limit = 512M
post_max_size = 50M
upload_max_filesize = 50M
max_execution_time = 300
default_charset = "UTF-8"
extension_dir = "ext"
extension=openssl
extension=curl
extension=mbstring
extension=gd
extension=pdo_mysql
extension=mysqli
extension=fileinfo
"@

Write-Host "正在创建php.ini配置文件..." -ForegroundColor Green
try {
    Set-Content -Path $phpIniPath -Value $phpIniContent -Encoding UTF8
    Write-Host "php.ini创建完成!" -ForegroundColor Green
} catch {
    Write-Host "创建php.ini失败: $_" -ForegroundColor Red
    exit 1
}

# 删除下载的zip文件
Remove-Item -Path $downloadPath -Force

Write-Host "PHP便携版安装完成！" -ForegroundColor Green
Write-Host "您现在可以运行 .\run_fix_all_php_errors.bat 来修复PHP文件" -ForegroundColor Green 