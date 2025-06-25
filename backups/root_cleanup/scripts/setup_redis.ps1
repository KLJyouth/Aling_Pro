# Redis 配置脚本
# 适用于 Windows 开发环境

Write-Host "=== AlingAi Pro Redis 配置脚本 ===" -ForegroundColor Green

# 检查是否安装了 Redis
$redisInstalled = $false
try {
    $redisProcess = Get-Process redis-server -ErrorAction SilentlyContinue
    if ($redisProcess) {
        Write-Host "Redis 服务正在运行" -ForegroundColor Yellow
        $redisInstalled = $true
    } else {
        # 尝试启动 Redis
        Start-Process -FilePath "redis-server" -WindowStyle Hidden -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 2
        $redisProcess = Get-Process redis-server -ErrorAction SilentlyContinue
        if ($redisProcess) {
            Write-Host "Redis 服务已成功启动" -ForegroundColor Green
            $redisInstalled = $true
        }
    }
} catch {
    Write-Host "Redis 未安装或未找到" -ForegroundColor Red
}

if (-not $redisInstalled) {
    Write-Host "准备下载并配置 Redis..." -ForegroundColor Yellow
    
    # 创建 Redis 目录
    $redisDir = "C:\tools\redis"
    if (-not (Test-Path $redisDir)) {
        New-Item -ItemType Directory -Path $redisDir -Force
        Write-Host "创建 Redis 目录: $redisDir" -ForegroundColor Green
    }
    
    # 下载 Redis for Windows
    $redisUrl = "https://github.com/microsoftarchive/redis/releases/download/win-3.0.504/Redis-x64-3.0.504.zip"
    $redisZip = "$redisDir\redis.zip"
    
    try {
        Write-Host "正在下载 Redis..." -ForegroundColor Yellow
        Invoke-WebRequest -Uri $redisUrl -OutFile $redisZip -UseBasicParsing
        
        # 解压
        Expand-Archive -Path $redisZip -DestinationPath $redisDir -Force
        Remove-Item $redisZip
        
        # 添加到 PATH
        $currentPath = [Environment]::GetEnvironmentVariable("PATH", "User")
        if ($currentPath -notcontains $redisDir) {
            [Environment]::SetEnvironmentVariable("PATH", "$currentPath;$redisDir", "User")
            Write-Host "已将 Redis 添加到 PATH" -ForegroundColor Green
        }
        
        Write-Host "Redis 安装完成！" -ForegroundColor Green
        
    } catch {
        Write-Host "下载 Redis 失败: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "请手动安装 Redis 或使用 Chocolatey: choco install redis-64" -ForegroundColor Yellow
    }
}

# 创建 Redis 配置文件
$redisConfig = @"
# Redis 配置文件 - AlingAi Pro
port 6379
bind 127.0.0.1
timeout 0
save 900 1
save 300 10
save 60 10000
rdbcompression yes
dbfilename alingai_dump.rdb
dir ./
maxmemory 256mb
maxmemory-policy allkeys-lru
"@

$configPath = ".\redis.conf"
$redisConfig | Out-File -FilePath $configPath -Encoding UTF8
Write-Host "Redis 配置文件已创建: $configPath" -ForegroundColor Green

# 创建环境配置
$envContent = @"

# Redis 缓存配置
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
CACHE_PREFIX=alingai_pro:
"@

$envFile = ".\.env.redis"
$envContent | Out-File -FilePath $envFile -Encoding UTF8
Write-Host "Redis 环境配置已创建: $envFile" -ForegroundColor Green

Write-Host ""
Write-Host "=== 配置完成 ===" -ForegroundColor Green
Write-Host "请将 .env.redis 的内容添加到主 .env 文件中" -ForegroundColor Yellow
Write-Host "或者运行以下命令启动 Redis:" -ForegroundColor Yellow
Write-Host "redis-server redis.conf" -ForegroundColor Cyan
