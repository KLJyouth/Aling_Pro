# Redis Setup Script for AlingAi Pro
Write-Host "=== AlingAi Pro Redis Setup ===" -ForegroundColor Green

# Create Redis config file
$redisConfig = @"
# Redis configuration for AlingAi Pro
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

$redisConfig | Out-File -FilePath "redis.conf" -Encoding UTF8
Write-Host "Redis config file created: redis.conf" -ForegroundColor Green

# Create environment variables
$envContent = @"

# Redis Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
CACHE_PREFIX=alingai_pro:
"@

$envContent | Out-File -FilePath ".env.redis" -Encoding UTF8
Write-Host "Redis environment config created: .env.redis" -ForegroundColor Green

# Check if Redis is available
try {
    $redisTest = Start-Process -FilePath "redis-server" -ArgumentList "--version" -NoNewWindow -Wait -PassThru -RedirectStandardOutput "redis_version.txt"
    if ($redisTest.ExitCode -eq 0) {
        Write-Host "Redis is available!" -ForegroundColor Green
        Get-Content "redis_version.txt"
        Remove-Item "redis_version.txt" -ErrorAction SilentlyContinue
    }
} catch {
    Write-Host "Redis not found. Please install Redis:" -ForegroundColor Yellow
    Write-Host "Option 1: Download from https://github.com/tporadowski/redis/releases" -ForegroundColor Cyan
    Write-Host "Option 2: Use Chocolatey: choco install redis-64" -ForegroundColor Cyan
    Write-Host "Option 3: Use Windows Subsystem for Linux" -ForegroundColor Cyan
}

Write-Host "Setup completed!" -ForegroundColor Green
