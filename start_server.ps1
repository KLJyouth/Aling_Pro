# AlingAi Pro 5.1.0 量子安全管理系统启动脚本
# PowerShell 版本

# 设置控制台颜色
$host.UI.RawUI.BackgroundColor = "Black"
$host.UI.RawUI.ForegroundColor = "Cyan"
Clear-Host

Write-Host "AlingAi Pro 5.1.0 量子安全管理系统" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host "正在启动服务器..." -ForegroundColor Yellow

# 检查PHP是否安装
try {
    $phpVersion = (php -v) | Select-Object -First 1
    Write-Host "[信息] 检测到PHP: $phpVersion" -ForegroundColor Cyan
} catch {
    Write-Host "[错误] 未找到PHP! 请确保已安装PHP并添加到系统PATH中。" -ForegroundColor Red
    Write-Host "您可以从 https://windows.php.net/download/ 下载PHP。" -ForegroundColor Red
    Read-Host "按Enter键退出"
    exit 1
}

# 检查router.php文件是否存在
if (-Not (Test-Path "public\router.php")) {
    Write-Host "[错误] 未找到router.php文件! 请确保您在正确的目录中。" -ForegroundColor Red
    Read-Host "按Enter键退出"
    exit 1
}

# 检查配置目录
if (-Not (Test-Path "config")) {
    Write-Host "[警告] 未找到config目录，正在创建..." -ForegroundColor Yellow
    New-Item -Path "config" -ItemType Directory -Force | Out-Null
    Write-Host "[信息] 已创建config目录" -ForegroundColor Cyan
}

# 检查日志目录
if (-Not (Test-Path "logs")) {
    Write-Host "[警告] 未找到logs目录，正在创建..." -ForegroundColor Yellow
    New-Item -Path "logs" -ItemType Directory -Force | Out-Null
    Write-Host "[信息] 已创建logs目录" -ForegroundColor Cyan
}

# 成功启动提示
Write-Host "[成功] 初始化完成!" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host "访问地址:" -ForegroundColor White
Write-Host "- 前端页面: http://localhost:8000" -ForegroundColor White
Write-Host "- 管理后台: http://localhost:8000/admin/" -ForegroundColor White
Write-Host "- API文档: http://localhost:8000/admin/api/documentation/" -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Green
Write-Host "安全说明:" -ForegroundColor White
Write-Host "- 默认管理员账户: admin" -ForegroundColor White
Write-Host "- 请在首次登录后修改默认密码" -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Green
Write-Host "按 Ctrl+C 停止服务器" -ForegroundColor Yellow
Write-Host ""

# 切换到public目录并启动服务器
try {
    Write-Host "[信息] 切换到public目录并启动PHP服务器..." -ForegroundColor Cyan
    
    # 使用Push-Location而不是Set-Location保存当前位置
    Push-Location -Path "public"
    
    # 直接启动PHP服务器，不使用&符号
    Write-Host "[信息] 执行: php -S localhost:8000 router.php" -ForegroundColor Cyan
    php -S localhost:8000 router.php
} catch {
    Write-Host "[错误] 启动PHP服务器时出错: $_" -ForegroundColor Red
} finally {
    # 使用Pop-Location恢复之前的位置
    Pop-Location
}

# 如果服务器意外停止
Write-Host ""
Write-Host "[信息] 服务器已停止运行。" -ForegroundColor Yellow
Write-Host "如需重新启动，请再次运行此脚本。" -ForegroundColor Yellow
Read-Host "按Enter键退出" 