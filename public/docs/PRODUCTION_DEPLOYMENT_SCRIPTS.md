# AlingAi Pro 生产环境配置优化脚本

## 快速安全配置脚本

根据当前 `.env` 配置，为您生成生产环境安全配置优化脚本。

### 1. 环境变量安全优化

创建生产环境配置文件：

```bash
#!/bin/bash

# AlingAi Pro 生产环境安全配置脚本
echo "🔒 开始 AlingAi Pro 生产环境安全配置..."

# 备份当前配置
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ 已备份当前 .env 配置"

# 创建生产环境安全配置
cat > .env.production << 'EOF'
# AlingAi Pro 生产环境配置
# 生成时间: $(date)

# 应用基础配置
APP_NAME="AlingAi Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 安全配置
SESSION_SECURE_COOKIE=true
SESSION_COOKIE_HTTPONLY=true
SESSION_COOKIE_SAMESITE=Strict
FORCE_HTTPS=true
CSRF_TOKEN_TIMEOUT=3600

# 数据库配置（请修改为您的实际配置）
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alingai_pro
DB_USERNAME=alingai_user  # 建议使用专用用户而非root
DB_PASSWORD=your_secure_db_password_here

# Redis 配置（启用密码保护）
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_secure_redis_password_here
REDIS_PORT=6380  # 使用非默认端口
REDIS_DB=0
REDIS_TIMEOUT=5
REDIS_READ_TIMEOUT=10

# 缓存配置
CACHE_DRIVER=redis
CACHE_PREFIX=alingai_pro
CACHE_DEFAULT_TTL=3600

# 邮件配置（保持您现有的配置）
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="AlingAi Pro"

# AI服务配置（保持您的API密钥）
DEEPSEEK_API_KEY=your_deepseek_api_key
OPENAI_API_KEY=your_openai_api_key
AI_DEFAULT_MODEL=deepseek-chat
AI_MAX_TOKENS=4000
AI_TEMPERATURE=0.7

# JWT配置
JWT_SECRET=your_jwt_secret_key
JWT_TTL=60
JWT_REFRESH_TTL=20160

# 文件上传配置（加强安全限制）
UPLOAD_MAX_SIZE=5242880  # 5MB，降低风险
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf
UPLOAD_PATH=storage/uploads
AVATAR_PATH=storage/avatars

# 安全配置
BCRYPT_ROUNDS=12
RATE_LIMIT_PER_MINUTE=60
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_DURATION=900  # 15分钟

# 监控和日志
LOG_CHANNEL=stack
LOG_LEVEL=warning  # 生产环境只记录警告和错误
LOG_MAX_FILES=10
MONITOR_ENABLE=true
PERFORMANCE_MONITORING=true

# WebSocket配置
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8080
WEBSOCKET_SSL=true  # 启用SSL

# 备份配置
BACKUP_ENABLE=true
BACKUP_SCHEDULE="0 2 * * *"  # 每天凌晨2点备份
BACKUP_RETENTION_DAYS=30
EOF

echo "✅ 生产环境配置文件已创建: .env.production"
echo "⚠️  请手动修改其中的密码、密钥和域名配置"
```

### 2. 数据库安全配置

```sql
-- 创建专用数据库用户
CREATE USER 'alingai_user'@'localhost' IDENTIFIED BY 'your_secure_password';

-- 授予必要权限（最小权限原则）
GRANT SELECT, INSERT, UPDATE, DELETE ON alingai_pro.* TO 'alingai_user'@'localhost';
GRANT CREATE, DROP, INDEX, ALTER ON alingai_pro.* TO 'alingai_user'@'localhost';

-- 刷新权限
FLUSH PRIVILEGES;

-- 移除测试数据库和匿名用户
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.user WHERE User = '';
DELETE FROM mysql.user WHERE User = 'root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
```

### 3. Redis 安全配置

```bash
# Redis 安全配置
sudo tee /etc/redis/redis.conf.secure << 'EOF'
# Redis 生产环境安全配置

# 绑定到本地地址
bind 127.0.0.1

# 修改默认端口
port 6380

# 启用密码认证
requirepass your_secure_redis_password_here

# 禁用危险命令
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command KEYS ""
rename-command CONFIG ""
rename-command SHUTDOWN ""
rename-command DEBUG ""

# 内存优化
maxmemory 256mb
maxmemory-policy allkeys-lru

# 日志配置
loglevel notice
logfile /var/log/redis/redis-server.log

# 安全配置
protected-mode yes
tcp-keepalive 300
timeout 300
EOF

# 重启 Redis 服务
sudo systemctl restart redis-server
echo "✅ Redis 安全配置已应用"
```

### 4. Nginx 安全配置

```nginx
# /etc/nginx/sites-available/alingai-pro-ssl
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/alingai-pro/public;
    
    # SSL 配置
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # SSL 安全配置
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # 安全头
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options DENY always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self' wss:;" always;
    
    # 隐藏 Nginx 版本
    server_tokens off;
    
    # 文件上传大小限制
    client_max_body_size 10M;
    
    # 防止访问敏感文件
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /vendor/ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /storage/ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # 安全配置
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # 主入口
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 5. 系统安全加固脚本

```bash
#!/bin/bash

# 系统安全加固脚本
echo "🛡️ 开始系统安全加固..."

# 1. 更新系统
sudo apt update && sudo apt upgrade -y
echo "✅ 系统更新完成"

# 2. 配置防火墙
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 443/tcp    # HTTPS
sudo ufw --force enable
echo "✅ 防火墙配置完成"

# 3. 设置文件权限
sudo chown -R www-data:www-data /var/www/alingai-pro
sudo chmod -R 755 /var/www/alingai-pro
sudo chmod -R 775 /var/www/alingai-pro/storage
sudo chmod -R 775 /var/www/alingai-pro/bootstrap/cache
echo "✅ 文件权限设置完成"

# 4. 配置日志轮转
sudo tee /etc/logrotate.d/alingai-pro << 'EOF'
/var/log/alingai-pro/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}
EOF
echo "✅ 日志轮转配置完成"

# 5. 创建监控脚本
sudo tee /usr/local/bin/alingai-monitor.sh << 'EOF'
#!/bin/bash
# AlingAi Pro 系统监控脚本

# 检查关键服务
services=("nginx" "mysql" "redis-server" "php8.1-fpm")
for service in "${services[@]}"; do
    if ! systemctl is-active --quiet $service; then
        echo "$(date): 警告 - $service 服务未运行" >> /var/log/alingai-pro/monitor.log
        systemctl restart $service
    fi
done

# 检查磁盘空间
disk_usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $disk_usage -gt 80 ]; then
    echo "$(date): 警告 - 磁盘使用率达到 $disk_usage%" >> /var/log/alingai-pro/monitor.log
fi

# 检查内存使用
mem_usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $mem_usage -gt 90 ]; then
    echo "$(date): 警告 - 内存使用率达到 $mem_usage%" >> /var/log/alingai-pro/monitor.log
fi
EOF

sudo chmod +x /usr/local/bin/alingai-monitor.sh

# 6. 设置定时任务
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/alingai-monitor.sh") | crontab -
(crontab -l 2>/dev/null; echo "0 2 * * * /var/www/alingai-pro/bin/backup.php") | crontab -
echo "✅ 定时任务设置完成"

echo "🎉 系统安全加固完成！"
echo "请手动完成以下步骤："
echo "1. 修改 .env.production 中的密码和密钥"
echo "2. 配置 SSL 证书"
echo "3. 测试所有功能"
echo "4. 进行安全扫描"
```

## 📋 生产环境部署检查清单

### 部署前检查
- [ ] 复制 .env.production 为 .env 并修改所有敏感信息
- [ ] 创建专用数据库用户并测试连接
- [ ] 配置 Redis 密码并重启服务
- [ ] 申请并配置 SSL 证书
- [ ] 运行系统安全加固脚本
- [ ] 测试所有主要功能
- [ ] 配置监控和告警
- [ ] 准备备份策略

### 部署后验证
- [ ] 验证 HTTPS 强制重定向
- [ ] 测试用户注册和登录
- [ ] 验证邮件发送功能
- [ ] 测试文件上传限制
- [ ] 检查安全头设置
- [ ] 验证 API 功能
- [ ] 测试聊天系统
- [ ] 检查系统日志

---

**注意**: 请根据您的实际部署环境调整配置参数，特别是域名、密码和路径信息。建议在测试环境中充分验证后再应用到生产环境。
