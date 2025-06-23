# AlingAi Pro 5.0 - 完整部署指南

## 📋 目录
- [系统要求](#-系统要求)
- [快速部署](#-快速部署)
- [详细配置](#-详细配置)
- [性能优化](#-性能优化)
- [监控配置](#-监控配置)
- [故障排除](#-故障排除)

## 🔧 系统要求

### 基础环境
- **PHP**: 8.0+ (推荐 8.1+)
- **Composer**: 2.0+
- **Web服务器**: Apache 2.4+ / Nginx 1.18+ / PHP内置服务器
- **数据库**: MySQL 8.0+ / PostgreSQL 12+
- **Redis**: 6.0+ (可选，用于缓存)

### PHP扩展要求
```bash
# 必需扩展
php-json
php-mbstring
php-curl
php-openssl
php-pdo
php-pdo-mysql

# 推荐扩展
php-redis
php-opcache
php-zip
php-gd
```

## 🚀 快速部署

### 1. 克隆项目
```bash
git clone https://github.com/AlingAI/AlingAI-Pro.git
cd AlingAI-Pro
```

### 2. 安装依赖
```bash
composer install --optimize-autoloader --no-dev
```

### 3. 环境配置
```bash
# 复制环境配置文件
cp .env.example .env

# 编辑配置
nano .env
```

### 4. 基本配置示例
```env
# 应用配置
APP_NAME="AlingAi Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alingai_pro
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 缓存配置
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# API密钥
OPENAI_API_KEY=your_openai_key
ANTHROPIC_API_KEY=your_anthropic_key
```

### 5. 启动服务

#### 开发环境 (PHP内置服务器)
```bash
# 基本启动
php -S localhost:8000 -t public/ router.php

# 或使用Composer脚本
composer serve
```

#### 生产环境 (Nginx)
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/alingai-pro/public;
    
    index index.php index.html index.htm;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

## ⚙️ 详细配置

### 数据库设置
```bash
# 创建数据库
mysql -u root -p -e "CREATE DATABASE alingai_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 运行迁移
php database/migrate.php

# 或使用简化安装
php database/setup_simple.php
```

### 缓存配置
```bash
# Redis配置 (推荐)
sudo systemctl start redis-server
sudo systemctl enable redis-server

# 文件缓存 (备选)
# 在.env中设置 CACHE_DRIVER=file
```

### WebSocket服务器 (可选)
```bash
# 启动WebSocket服务器
php websocket_server.php

# 后台运行
nohup php websocket_server.php > storage/logs/websocket.log 2>&1 &
```

## 🚄 性能优化

### 1. PHP优化
```ini
# php.ini 优化配置
memory_limit = 256M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.enable_file_override = 1
```

### 2. 缓存策略
```bash
# 启用OPCache预加载
echo "opcache.preload=/path/to/alingai-pro/config/preload.php" >> /etc/php/8.1/fpm/php.ini

# Redis配置优化
redis-cli CONFIG SET maxmemory 256mb
redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

### 3. Web服务器优化

#### Nginx配置
```nginx
# gzip压缩
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

# 缓存静态资源
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}

# 连接优化
keepalive_timeout 65;
client_max_body_size 100M;
```

### 4. 数据库优化
```sql
-- MySQL配置优化
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL query_cache_size = 64M;
SET GLOBAL max_connections = 200;

-- 创建索引
CREATE INDEX idx_created_at ON conversations(created_at);
CREATE INDEX idx_user_id ON messages(user_id);
```

## 📊 监控配置

### 1. 系统监控
```bash
# 创建监控脚本
cat > /usr/local/bin/alingai-monitor << 'EOF'
#!/bin/bash
curl -f http://localhost:8000/api/system/health || echo "Health check failed at $(date)" >> /var/log/alingai-health.log
EOF

chmod +x /usr/local/bin/alingai-monitor

# 添加到crontab
echo "*/5 * * * * /usr/local/bin/alingai-monitor" | crontab -
```

### 2. 日志管理
```bash
# 创建日志轮转配置
cat > /etc/logrotate.d/alingai-pro << 'EOF'
/path/to/alingai-pro/storage/logs/*.log {
    daily
    rotate 30
    compress
    missingok
    notifempty
    create 644 www-data www-data
}
EOF
```

### 3. 性能监控端点
```bash
# 获取系统状态
curl http://localhost:8000/api/system/status

# 获取性能指标
curl http://localhost:8000/api/system/metrics

# 健康检查
curl http://localhost:8000/api/system/health
```

## 🔍 故障排除

### 常见问题

#### 1. API超时问题
```bash
# 检查PHP配置
php -i | grep max_execution_time

# 增加超时时间
echo "max_execution_time = 60" >> /etc/php/8.1/fpm/php.ini
systemctl restart php8.1-fpm
```

#### 2. 内存不足
```bash
# 检查内存使用
php -i | grep memory_limit

# 增加内存限制
echo "memory_limit = 256M" >> /etc/php/8.1/fpm/php.ini
```

#### 3. 数据库连接问题
```bash
# 测试数据库连接
php database/test_connection.php

# 检查MySQL状态
systemctl status mysql
```

#### 4. 静态资源404
```bash
# 检查文件权限
find public/assets -type f -exec chmod 644 {} \;
find public/assets -type d -exec chmod 755 {} \;

# 检查Web服务器配置
nginx -t
```

### 日志查看
```bash
# 应用日志
tail -f storage/logs/app.log

# Web服务器日志
tail -f /var/log/nginx/error.log

# PHP错误日志
tail -f /var/log/php8.1-fpm.log

# 系统资源监控
htop
```

### 性能调试
```bash
# 启用详细错误报告 (仅开发环境)
echo "APP_DEBUG=true" >> .env

# 查看慢查询
mysql -e "SHOW VARIABLES LIKE 'slow_query_log';"

# 监控Redis性能
redis-cli --latency

# 检查OPCache状态
php -r "print_r(opcache_get_status());"
```

## 🔐 安全配置

### 1. 文件权限
```bash
# 设置正确的文件权限
chown -R www-data:www-data /path/to/alingai-pro
find /path/to/alingai-pro -type f -exec chmod 644 {} \;
find /path/to/alingai-pro -type d -exec chmod 755 {} \;
chmod -R 777 storage/
```

### 2. SSL配置
```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
}
```

### 3. 防火墙设置
```bash
# UFW配置
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

## 📱 Docker部署

### Dockerfile示例
```dockerfile
FROM php:8.1-fpm

# 安装扩展
RUN docker-php-ext-install pdo pdo_mysql opcache

# 复制项目文件
COPY . /var/www/html

# 设置权限
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
```

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build: .
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: alingai_pro

  redis:
    image: redis:alpine
```

## 📞 支持

如果您在部署过程中遇到问题，请：

1. 检查[故障排除](#-故障排除)部分
2. 查看项目日志文件
3. 提交issue到GitHub仓库
4. 联系技术支持团队

---

**部署完成后**，访问 `http://your-domain.com` 开始使用AlingAi Pro 5.0！
