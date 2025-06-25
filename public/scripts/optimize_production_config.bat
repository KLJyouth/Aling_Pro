@echo off
chcp 65001 >nul
echo.
echo ==========================================
echo    AlingAi Pro 生产环境配置优化
echo ==========================================
echo.

REM 备份当前配置
echo [1/4] 备份当前 .env 配置...
if exist .env (
    copy .env .env.backup.%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
    echo ✓ 已备份 .env 到 .env.backup.%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
) else (
    echo ⚠ 未找到 .env 文件
)

echo.
echo [2/4] 生成优化的生产环境配置...

REM 生成优化的 .env.production 文件
(
echo # AlingAi Pro 生产环境配置 - 安全优化版本
echo # 生成时间: %date% %time%
echo.
echo # 应用基础配置
echo APP_NAME="AlingAi Pro"
echo APP_ENV=production
echo APP_DEBUG=false
echo APP_URL=https://your-domain.com
echo FORCE_HTTPS=true
echo.
echo # 数据库配置 - 生产环境
echo DB_CONNECTION=mysql
echo DB_HOST=148.72.248.218
echo DB_PORT=3306
echo DB_DATABASE=alingai_pro
echo DB_USERNAME=alingai_user
echo DB_PASSWORD=7pD9mKqL2xNvR8sE
echo.
echo # 缓存配置 - Redis 安全加固
echo CACHE_DRIVER=redis
echo REDIS_HOST=127.0.0.1
echo REDIS_PORT=6380
echo REDIS_PASSWORD=SecureRedisPass2024!
echo REDIS_DB=0
echo.
echo # 会话安全配置
echo SESSION_DRIVER=redis
echo SESSION_LIFETIME=120
echo SESSION_SECURE_COOKIE=true
echo SESSION_HTTP_ONLY=true
echo SESSION_SAME_SITE=strict
echo.
echo # JWT 安全配置
echo JWT_SECRET=your_super_secure_jwt_secret_key_32_chars_or_more_2024
echo JWT_TTL=60
echo JWT_REFRESH_TTL=20160
echo.
echo # 邮件配置
echo MAIL_MAILER=smtp
echo MAIL_HOST=smtp.gmail.com
echo MAIL_PORT=587
echo MAIL_USERNAME=aoteman2024@gmail.com
echo MAIL_PASSWORD=vsie\ wpgx\ rkej\ jhfs
echo MAIL_ENCRYPTION=tls
echo MAIL_FROM_ADDRESS=aoteman2024@gmail.com
echo MAIL_FROM_NAME="AlingAi Pro"
echo.
echo # API 安全配置
echo API_RATE_LIMIT_PER_MINUTE=60
echo API_RATE_LIMIT_PER_HOUR=1000
echo.
echo # 文件上传安全配置
echo UPLOAD_PATH=storage/uploads
echo UPLOAD_MAX_SIZE=5242880
echo UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
echo.
echo # 日志配置
echo LOG_CHANNEL=stack
echo LOG_LEVEL=warning
echo LOG_FILE_PATH=./logs/app.log
echo.
echo # WebSocket 安全配置
echo WEBSOCKET_HOST=0.0.0.0
echo WEBSOCKET_PORT=8080
echo WEBSOCKET_SSL=true
echo.
echo # OpenAI API 配置
echo OPENAI_API_KEY=sk-proj-i2nKNLjOu-bZYJ2HaVs7YwQJBU9zAQyTqHWl7rCqpxA8GjdIbKv9s-JUgpT3BlbkFJyzQM7sQJ_r-xHw7Q0zqT0S-nA
echo OPENAI_API_URL=https://api.openai.com/v1
echo.
echo # 安全相关配置
echo BCRYPT_ROUNDS=12
echo HASH_DRIVER=bcrypt
echo.
echo # CORS 配置
echo CORS_ALLOWED_ORIGINS=https://your-domain.com
echo CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
echo CORS_ALLOWED_HEADERS=Content-Type,Authorization
echo.
echo # 错误报告配置
echo ERROR_REPORTING=false
echo DISPLAY_ERRORS=false
echo LOG_ERRORS=true
) > .env.production

echo ✓ 已生成 .env.production 配置文件

echo.
echo [3/4] 配置文件权限设置...

REM 设置文件权限（Windows）
icacls .env.production /inheritance:r /grant:r "%USERNAME%:(R,W)" /grant:r "Administrators:(F)" 2>nul
if %errorlevel% equ 0 (
    echo ✓ 已设置 .env.production 文件权限
) else (
    echo ⚠ 无法设置文件权限，请手动检查
)

echo.
echo [4/4] 生成 Redis 配置文件...

REM 生成 Redis 安全配置
(
echo # Redis 生产环境配置
echo bind 127.0.0.1
echo port 6380
echo requirepass SecureRedisPass2024!
echo save 900 1
echo save 300 10
echo save 60 10000
echo maxmemory 256mb
echo maxmemory-policy allkeys-lru
echo timeout 300
echo tcp-keepalive 300
echo databases 16
echo stop-writes-on-bgsave-error yes
echo rdbcompression yes
echo rdbchecksum yes
echo dbfilename dump.rdb
echo dir ./
) > redis.production.conf

echo ✓ 已生成 redis.production.conf 配置文件

echo.
echo ==========================================
echo            配置优化完成
echo ==========================================
echo.
echo ✅ 优化内容包括:
echo    • 关闭调试模式
echo    • 启用 HTTPS 强制
echo    • 启用安全会话 Cookie
echo    • Redis 密码保护和端口修改
echo    • 严格的文件上传限制
echo    • 强化的 API 速率限制
echo    • 安全的日志配置
echo.
echo ⚠ 注意事项:
echo    1. 请在测试环境中验证配置
echo    2. 确认所有服务密码已更新
echo    3. 验证 Redis 新端口可访问
echo    4. 检查所有 API 密钥有效性
echo.
echo 🔄 如要应用此配置，请执行:
echo    copy .env.production .env
echo.
echo 📊 建议执行后再次运行安全扫描验证改进效果
echo.
pause
