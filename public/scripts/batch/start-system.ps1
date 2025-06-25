# AlingAi Pro System Launcher (PowerShell Version)
# 快速启动开发服务器

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   AlingAi Pro 系统启动器" -ForegroundColor Yellow
Write-Host "   快速启动开发服务器" -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# 检查PHP是否安装
try {
    $phpVersion = php --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[信息] 检测到的PHP版本:" -ForegroundColor Green
        Write-Host ($phpVersion | Select-String "PHP").Line -ForegroundColor White
    } else {
        throw "PHP not found"
    }
} catch {
    Write-Host "[错误] PHP未安装或未添加到PATH环境变量" -ForegroundColor Red
    Write-Host "请先安装PHP并确保可以在命令行中使用" -ForegroundColor Red
    Read-Host "按Enter键退出"
    exit 1
}

Write-Host ""

# 检查端口是否可用
$port = 8000
$portInUse = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
if ($portInUse) {
    Write-Host "[警告] 端口8000已被占用，尝试使用端口8001..." -ForegroundColor Yellow
    $port = 8001
}

# 显示启动信息
Write-Host "[启动] 正在启动AlingAi Pro开发服务器..." -ForegroundColor Green
Write-Host "[端口] localhost:$port" -ForegroundColor White
Write-Host "[路径] $PSScriptRoot\public" -ForegroundColor White
Write-Host ""

# 检查public目录是否存在
if (-not (Test-Path "public")) {
    Write-Host "[错误] 未找到public目录，请确保在正确的项目根目录下运行此脚本" -ForegroundColor Red
    Read-Host "按Enter键退出"
    exit 1
}

# 检查关键文件是否存在
if (-not (Test-Path "public\index.html")) {
    Write-Host "[错误] 未找到index.html文件" -ForegroundColor Red
    Read-Host "按Enter键退出"
    exit 1
}

Write-Host "[就绪] 系统准备启动，按任意键继续..." -ForegroundColor Cyan
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# 启动PHP开发服务器
Write-Host ""
Write-Host "[启动] 启动中..." -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  🚀 AlingAi Pro 系统已启动" -ForegroundColor Yellow
Write-Host "  📱 访问地址: http://localhost:$port" -ForegroundColor Green
Write-Host "  🧪 测试控制台: http://localhost:$port/system-test-console.html" -ForegroundColor Green
Write-Host "  ⚡ 按 Ctrl+C 停止服务器" -ForegroundColor Red
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# 尝试自动打开浏览器
try {
    Start-Process "http://localhost:$port"
    Write-Host "[浏览器] 已尝试打开默认浏览器" -ForegroundColor Green
} catch {
    Write-Host "[浏览器] 无法自动打开浏览器，请手动访问 http://localhost:$port" -ForegroundColor Yellow
}

Write-Host ""

# 启动PHP服务器
try {
    php -S "localhost:$port" -t public
} catch {
    Write-Host ""
    Write-Host "[错误] 服务器启动失败: $_" -ForegroundColor Red
} finally {
    Write-Host ""
    Write-Host "[信息] 服务器已停止" -ForegroundColor Yellow
    Read-Host "按Enter键退出"
}
