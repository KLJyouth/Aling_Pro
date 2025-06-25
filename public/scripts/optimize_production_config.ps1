# AlingAi Pro 生产环境安全配置优化脚本
# 基于当前实际配置的安全增强

Write-Host "🔒 开始 AlingAi Pro 生产环境配置优化..."
Write-Host "当前时间: $(Get-Date)"

# 1. 备份当前配置
$backupFile = ".env.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item .env $backupFile
Write-Host "✅ 已备份当前配置到: $backupFile" -ForegroundColor Green

# 2. 创建生产环境优化配置
$productionConfig = @"
# AlingAi Pro 生产环境安全配置
# 基于原配置优化，生成时间: $(Get-Date)

# 环境配置（生产环境）
NODE_ENV=production
PORT=3000

# 应用配置（安全加固）
APP_NAME="AlingAi Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Shanghai
APP_LOCALE=zh_CN
APP_KEY="base64:3f8d!@^kLz9`$2xQw7pL0vB1nM4rT6yUe"

# MySQL 数据库配置（保持您的生产数据库）
DB_CONNECTION=mysql
DB_HOST=111.180.205.70
DB_PORT=3306
DB_DATABASE=alingai
DB_USERNAME=AlingAi
DB_PASSWORD=e5bjzeWCr7k38TrZ
DB_PREFIX=
MYSQL_HOST=111.180.205.70
MYSQL_USER=AlingAi
MYSQL_PASSWORD=e5bjzeWCr7k38TrZ
MYSQL_DATABASE=alingai
MYSQL_PORT=3306

# MongoDB 配置（已迁移至 MySQL，仅做备份）
MONGODB_URI=mongodb://Ai:168KLJyouth.@111.180.205.70:27017/Ai

# Redis缓存配置（安全增强）
REDIS_HOST=127.0.0.1
REDIS_PORT=6380
REDIS_PASSWORD=AlingAi_Redis_Secure_2025!
REDIS_DB=0
REDIS_PREFIX=alingai_pro:
REDIS_TIMEOUT=5
REDIS_READ_TIMEOUT=10

# 缓存配置（性能优化）
CACHE_DRIVER=redis
CACHE_PREFIX=alingai_pro:
CACHE_DEFAULT_TTL=3600

# 会话配置（安全增强）
SESSION_DRIVER=redis
SESSION_LIFETIME=7200
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_COOKIE_HTTPONLY=true
SESSION_COOKIE_SAMESITE=Strict

# 安全配置（强化）
JWT_SECRET="AlingAi_JWT_SuperSecure_Key_2025_!@#$%^&*()"
JWT_TTL=3600
JWT_REFRESH_TTL=604800
JWT_EXPIRE=7d
JWT_LEEWAY=60
JWT_ISSUER=alingai-pro
JWT_AUDIENCE=alingai-pro-users

# 速率限制（严格控制）
RATE_LIMIT_WINDOW=15
RATE_LIMIT_MAX=60
API_RATE_LIMIT_PER_MINUTE=60
API_RATE_LIMIT_PER_HOUR=1500

# 邮件通知配置（保持您的配置）
MAIL_DRIVER=smtp
SMTP_HOST=smtp.exmail.qq.com
SMTP_PORT=465
SMTP_SECURE=SSL
SMTP_USER=admin@gxggm.com
SMTP_PASS=PALtPBCRaEDp84xr
SMTP_FROM=admin@gxggm.com
ALERT_EMAIL=admin@gxggm.com
EMAIL_THROTTLE_INTERVAL=300000
MAIL_HOST=smtp.exmail.qq.com
MAIL_PORT=465
MAIL_USERNAME=admin@gxggm.com
MAIL_PASSWORD=PALtPBCRaEDp84xr
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=admin@gxggm.com
MAIL_FROM_NAME="AlingAi Pro"
MAIL_REPLY_TO_ADDRESS=admin@gxggm.com
MAIL_REPLY_TO_NAME="AlingAi Support"

# DeepSeek API Keys（保持您的配置）
DEEPSEEK_API_KEY=sk-11a9c376a35e4541b1468554bf6a6e4b
OPENAI_API_KEY=sk-11a9c376a35e4541b1468554bf6a6e4b
OPENAI_API_URL=https://api.deepseek.com/v1
OPENAI_MODEL=deepseek-chat
OPENAI_MAX_TOKENS=2048
OPENAI_TEMPERATURE=0.7

# Baidu Agent Configuration（保持您的配置）
MCP_ENDPOINT=https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/agent
AGENT_AUTH_TOKEN=MPxrokVoaHPkDno8UK7GUgB3UiF33Mll
API_ID=6pTSQx7eHDFHkFCSQKlwdzG2EwqjgJD2
BAIDU_APP_ID=6pTSQx7eHDFHkFCSQKlwdzG2EwqjgJD2
BAIDU_SECRET_KEY=MPxrokVoaHPkDno8UK7GUgB3UiF33Mll
BAIDU_API_KEY=MPxrokVoaHPkDno8UK7GUgB3UiF33Mll

# 日志配置（生产优化）
LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_FILE_PATH=./logs/app.log
LOG_MAX_FILES=10

# Memory Configuration
MEMORY_DB_PATH=./agents/memory.db
MEMORY_CLEAN_THRESHOLD=1000

# 监控配置（增强）
HEALTH_CHECK_FREQUENCY=300000
RESOURCE_CHECK_INTERVAL=60000
METRICS_RETENTION_DAYS=30
DB_MONITOR_INTERVAL=60000
MONITOR_ENABLE=true
PERFORMANCE_MONITORING=true

# 告警阈值配置（优化）
CPU_WARNING_THRESHOLD=70
CPU_CRITICAL_THRESHOLD=90
MEMORY_WARNING_THRESHOLD=80
MEMORY_CRITICAL_THRESHOLD=90
DISK_WARNING_THRESHOLD=85
DISK_CRITICAL_THRESHOLD=95
RESPONSE_TIME_WARNING=1000
RESPONSE_TIME_CRITICAL=5000

# 文件存储配置（安全限制）
FILESYSTEM_DRIVER=local
UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,pdf
AVATAR_PATH=storage/avatars
UPLOAD_PATH=storage/uploads

# 安全配置（强化）
FORCE_HTTPS=true
CSRF_PROTECTION=true
BCRYPT_ROUNDS=12
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_DURATION=900

# 功能开关（生产优化）
FEATURE_REGISTRATION=true
FEATURE_EMAIL_VERIFICATION=true
FEATURE_PASSWORD_RESET=true
FEATURE_ADMIN_PANEL=true
FEATURE_CHAT=true
FEATURE_DOCUMENTS=true

# Google服务配置
GOOGLE_ANALYTICS_ID=
GOOGLE_RECAPTCHA_SITE_KEY=
GOOGLE_RECAPTCHA_SECRET_KEY=

# 开发环境专用配置（生产环境禁用）
DEV_SHOW_ERRORS=false
DEV_ENABLE_PROFILER=false
DEV_ENABLE_DEBUG_BAR=false

# WebSocket服务器配置（安全增强）
WEBSOCKET_ENABLED=true
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8080
WEBSOCKET_SSL=true
WEBSOCKET_PATH=/ws
WEBSOCKET_AUTO_START=true

# 实时功能配置
REALTIME_NOTIFICATIONS=true
REALTIME_CHAT=true
REALTIME_MONITORING=true

# 备份配置
BACKUP_ENABLE=true
BACKUP_SCHEDULE="0 2 * * *"
BACKUP_RETENTION_DAYS=30
"@

# 写入生产配置文件
$productionConfig | Out-File -FilePath ".env.production" -Encoding UTF8
Write-Host "✅ 生产环境配置已创建: .env.production" -ForegroundColor Green

Write-Host "`n⚠️  重要提醒:" -ForegroundColor Yellow
Write-Host "1. 请检查并修改 .env.production 中的域名配置" -ForegroundColor Yellow
Write-Host "2. 确认 Redis 密码设置是否符合您的要求" -ForegroundColor Yellow
Write-Host "3. 验证所有 API 密钥仍然有效" -ForegroundColor Yellow
Write-Host "4. 在应用此配置前，请在测试环境中验证" -ForegroundColor Yellow

Write-Host ""
Write-Host "如要应用此配置，请执行:" -ForegroundColor Cyan
Write-Host "Copy-Item .env.production .env" -ForegroundColor Cyan
