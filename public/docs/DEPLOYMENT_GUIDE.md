# AlingAI Pro 5.0 一键部署指南

## 🎯 部署概述

本指南将帮助您在Linux服务器上一键部署AlingAI Pro 5.0系统。支持的环境：
- **操作系统**：CentOS 7+, Ubuntu 18.04+, Debian 9+
- **PHP版本**：8.1+
- **MySQL版本**：8.0+
- **Nginx版本**：1.20+
- **Redis版本**：6.0+

---

## 🚀 一键安装脚本

### 快速部署命令
```bash
# 下载并执行一键部署脚本
curl -fsSL https://raw.githubusercontent.com/alingai/pro-v5/main/deploy.sh | bash

# 或者手动下载后执行
wget https://raw.githubusercontent.com/alingai/pro-v5/main/deploy.sh
chmod +x deploy.sh
./deploy.sh
```

### 自定义部署
```bash
# 交互式安装
./deploy.sh --interactive

# 指定安装目录
./deploy.sh --install-dir=/opt/alingai-pro

# 跳过环境检查
./deploy.sh --skip-check

# 仅安装依赖
./deploy.sh --deps-only
```

---

## 📋 系统要求检查

### 硬件要求
| 配置级别 | CPU | 内存 | 磁盘 | 网络 |
|----------|-----|------|------|------|
| 最小配置 | 2核 | 4GB | 50GB | 10Mbps |
| 推荐配置 | 4核 | 8GB | 100GB | 100Mbps |
| 生产配置 | 8核 | 16GB | 500GB | 1Gbps |

### 软件要求
```bash
# 检查系统版本
cat /etc/os-release

# 检查PHP版本
php --version

# 检查MySQL版本
mysql --version

# 检查Nginx版本
nginx -v

# 检查端口占用
netstat -tlnp | grep -E ':(80|443|3306|6379)'
```

---

## 🔧 手动安装步骤

### 1. 环境准备
```bash
# 更新系统包
sudo yum update -y  # CentOS/RHEL
sudo apt update && sudo apt upgrade -y  # Ubuntu/Debian

# 安装基础工具
sudo yum install -y wget curl git unzip  # CentOS/RHEL
sudo apt install -y wget curl git unzip  # Ubuntu/Debian
```

### 2. 安装PHP 8.1+
```bash
# CentOS/RHEL 安装PHP 8.1
sudo yum install -y epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
sudo yum module reset php
sudo yum module enable php:remi-8.1
sudo yum install -y php php-cli php-fpm php-mysqlnd php-zip php-devel \
    php-gd php-mcrypt php-mbstring php-curl php-xml php-pear php-bcmath php-json

# Ubuntu/Debian 安装PHP 8.1
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-zip \
    php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath php8.1-json

# 验证PHP安装
php --version
```

### 3. 安装MySQL 8.0+
```bash
# CentOS/RHEL 安装MySQL 8.0
sudo yum install -y https://dev.mysql.com/get/mysql80-community-release-el8-1.noarch.rpm
sudo yum install -y mysql-server
sudo systemctl start mysqld
sudo systemctl enable mysqld

# Ubuntu/Debian 安装MySQL 8.0
sudo apt install -y mysql-server-8.0
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置MySQL
sudo mysql_secure_installation

# 创建数据库和用户
mysql -u root -p << 'EOF'
CREATE DATABASE alingai_pro_v5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alingai'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON alingai_pro_v5.* TO 'alingai'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 4. 安装Nginx 1.20+
```bash
# CentOS/RHEL 安装Nginx
sudo yum install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Ubuntu/Debian 安装Nginx
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# 验证Nginx安装
nginx -v
```

### 5. 安装Redis
```bash
# CentOS/RHEL 安装Redis
sudo yum install -y redis
sudo systemctl start redis
sudo systemctl enable redis

# Ubuntu/Debian 安装Redis
sudo apt install -y redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server

# 验证Redis安装
redis-cli ping
```

### 6. 安装Composer
```bash
# 下载并安装Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 验证安装
composer --version
```

---

## 📦 部署应用程序

### 1. 下载源代码
```bash
# 创建安装目录
sudo mkdir -p /var/www/alingai-pro-v5
cd /var/www/alingai-pro-v5

# 从Git仓库克隆代码
git clone https://github.com/alingai/pro-v5.git .

# 或从压缩包解压
wget https://releases.alingai.com/v5.0.0/alingai-pro-v5.tar.gz
tar -xzf alingai-pro-v5.tar.gz --strip-components=1
```

### 2. 安装依赖
```bash
# 安装PHP依赖
composer install --no-dev --optimize-autoloader

# 设置文件权限
sudo chown -R nginx:nginx /var/www/alingai-pro-v5  # CentOS/RHEL
sudo chown -R www-data:www-data /var/www/alingai-pro-v5  # Ubuntu/Debian

sudo chmod -R 755 /var/www/alingai-pro-v5
sudo chmod -R 777 /var/www/alingai-pro-v5/storage
sudo chmod -R 777 /var/www/alingai-pro-v5/bootstrap/cache
```

### 3. 配置环境变量
```bash
# 复制环境配置文件
cp .env.example .env

# 编辑配置文件
nano .env
```

配置内容：
```env
# 应用配置
APP_NAME="AlingAI Pro 5.0"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alingai_pro_v5
DB_USERNAME=alingai
DB_PASSWORD=secure_password_here

# Redis配置
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AI服务配置
DEEPSEEK_API_KEY=your_deepseek_api_key
DEEPSEEK_API_ENDPOINT=https://api.deepseek.com/v1

# 安全配置
JWT_SECRET=your_jwt_secret_key_here
ENCRYPTION_KEY=your_encryption_key_here

# 日志配置
LOG_CHANNEL=stack
LOG_LEVEL=info
```

### 4. 配置Nginx
创建Nginx虚拟主机配置：
```bash
sudo nano /etc/nginx/sites-available/alingai-pro-v5
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    root /var/www/alingai-pro-v5/public;
    index index.php index.html index.htm;
    
    # SSL证书配置
    ssl_certificate /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE+AESGCM:ECDHE+CHACHA20:DHE+AESGCM:DHE+CHACHA20:!aNULL:!SHA1:!WEAK;
    ssl_prefer_server_ciphers on;
    
    # 安全头部
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;
    
    # 主要路由处理
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;  # Ubuntu/Debian
        # fastcgi_pass 127.0.0.1:9000;  # CentOS/RHEL
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # 安全设置
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    # WebSocket支持
    location /ws {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # 禁止访问敏感文件
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|storage|vendor)/ {
        deny all;
    }
    
    # 限制上传大小
    client_max_body_size 100M;
}
```

启用站点：
```bash
sudo ln -s /etc/nginx/sites-available/alingai-pro-v5 /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🗄️ 数据库初始化

### 1. 运行数据库迁移
```bash
cd /var/www/alingai-pro-v5

# 执行数据库迁移
php artisan migrate

# 或使用内置迁移工具
php migrate.php migrate

# 种子数据（可选）
php migrate.php seed
```

### 2. 创建管理员账户
```bash
# 使用命令行创建管理员
php artisan user:create admin admin@example.com secure_password

# 或通过数据库直接插入
mysql -u alingai -p alingai_pro_v5 << 'EOF'
INSERT INTO users (username, email, password, role, created_at, updated_at) 
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW());
EOF
```

---

## 🔐 SSL证书配置

### 1. 使用Let's Encrypt免费证书
```bash
# 安装Certbot
sudo yum install -y certbot python3-certbot-nginx  # CentOS/RHEL
sudo apt install -y certbot python3-certbot-nginx  # Ubuntu/Debian

# 申请证书
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# 设置自动续期
sudo crontab -e
# 添加以下行：
0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. 使用自签名证书（测试环境）
```bash
# 创建证书目录
sudo mkdir -p /etc/ssl/private /etc/ssl/certs

# 生成私钥
sudo openssl genrsa -out /etc/ssl/private/your-domain.key 2048

# 生成证书
sudo openssl req -new -x509 -key /etc/ssl/private/your-domain.key \
    -out /etc/ssl/certs/your-domain.crt -days 365 \
    -subj "/C=CN/ST=Beijing/L=Beijing/O=AlingAI/CN=your-domain.com"
```

---

## 🚀 启动服务

### 1. 启动PHP-FPM
```bash
sudo systemctl start php8.1-fpm  # Ubuntu/Debian
sudo systemctl start php-fpm     # CentOS/RHEL
sudo systemctl enable php8.1-fpm
```

### 2. 启动WebSocket服务
```bash
# 创建systemd服务文件
sudo nano /etc/systemd/system/alingai-websocket.service
```

```ini
[Unit]
Description=AlingAI Pro 5.0 WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/alingai-pro-v5
ExecStart=/usr/bin/php /var/www/alingai-pro-v5/websocket-server.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
# 启动WebSocket服务
sudo systemctl daemon-reload
sudo systemctl start alingai-websocket
sudo systemctl enable alingai-websocket
```

### 3. 启动队列处理器
```bash
# 创建队列服务文件
sudo nano /etc/systemd/system/alingai-queue.service
```

```ini
[Unit]
Description=AlingAI Pro 5.0 Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/alingai-pro-v5
ExecStart=/usr/bin/php /var/www/alingai-pro-v5/artisan queue:work --sleep=3 --tries=3
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
# 启动队列服务
sudo systemctl start alingai-queue
sudo systemctl enable alingai-queue
```

---

## 🔍 部署验证

### 1. 系统健康检查
```bash
# 检查所有服务状态
sudo systemctl status nginx php8.1-fpm mysql redis-server alingai-websocket alingai-queue

# 检查端口监听
netstat -tlnp | grep -E ':(80|443|3306|6379|8080)'

# 检查磁盘空间
df -h

# 检查内存使用
free -h
```

### 2. 应用功能测试
```bash
# 测试主页访问
curl -I https://your-domain.com

# 测试API接口
curl -H "Accept: application/json" https://your-domain.com/api/v5/core-architecture/status

# 测试数据库连接
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=alingai_pro_v5', 'alingai', 'password');
    echo 'Database connection: OK\n';
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"
```

### 3. 性能基准测试
```bash
# 使用Apache Bench测试
ab -n 1000 -c 10 https://your-domain.com/

# 使用curl测试响应时间
curl -w "@curl-format.txt" -o /dev/null -s https://your-domain.com/
```

创建curl格式文件：
```bash
cat > curl-format.txt << 'EOF'
     time_namelookup:  %{time_namelookup}\n
        time_connect:  %{time_connect}\n
     time_appconnect:  %{time_appconnect}\n
    time_pretransfer:  %{time_pretransfer}\n
       time_redirect:  %{time_redirect}\n
  time_starttransfer:  %{time_starttransfer}\n
                     ----------\n
          time_total:  %{time_total}\n
EOF
```

---

## 🛠️ 维护和监控

### 1. 日志管理
```bash
# 查看应用日志
tail -f /var/www/alingai-pro-v5/storage/logs/laravel.log

# 查看Nginx日志
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# 查看PHP-FPM日志
tail -f /var/log/php8.1-fpm.log

# 日志轮转配置
sudo nano /etc/logrotate.d/alingai-pro
```

### 2. 备份策略
```bash
# 创建备份脚本
sudo nano /usr/local/bin/alingai-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backup/alingai-pro-v5"
DATE=$(date +%Y%m%d_%H%M%S)

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u alingai -p alingai_pro_v5 > $BACKUP_DIR/database_$DATE.sql

# 备份应用文件
tar -czf $BACKUP_DIR/application_$DATE.tar.gz -C /var/www alingai-pro-v5

# 清理旧备份（保留7天）
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# 设置定时备份
sudo chmod +x /usr/local/bin/alingai-backup.sh
sudo crontab -e
# 添加：每天凌晨2点备份
0 2 * * * /usr/local/bin/alingai-backup.sh
```

### 3. 监控配置
```bash
# 安装监控工具
sudo yum install -y htop iotop nethogs  # CentOS/RHEL
sudo apt install -y htop iotop nethogs  # Ubuntu/Debian

# 创建监控脚本
sudo nano /usr/local/bin/alingai-monitor.sh
```

```bash
#!/bin/bash
LOG_FILE="/var/log/alingai-monitor.log"

# 检查系统资源
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
MEM_USAGE=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
DISK_USAGE=$(df -h / | awk 'NR==2{print $5}' | cut -d'%' -f1)

# 检查服务状态
NGINX_STATUS=$(systemctl is-active nginx)
PHP_STATUS=$(systemctl is-active php8.1-fpm)
MYSQL_STATUS=$(systemctl is-active mysql)
REDIS_STATUS=$(systemctl is-active redis-server)

# 记录到日志
echo "$(date): CPU:${CPU_USAGE}% MEM:${MEM_USAGE}% DISK:${DISK_USAGE}% NGINX:$NGINX_STATUS PHP:$PHP_STATUS MYSQL:$MYSQL_STATUS REDIS:$REDIS_STATUS" >> $LOG_FILE

# 告警阈值检查
if (( $(echo "$CPU_USAGE > 80" | bc -l) )); then
    echo "CPU usage alert: ${CPU_USAGE}%" | mail -s "AlingAI Alert" admin@example.com
fi
```

---

## 🆘 故障排除

### 常见问题和解决方案

#### 1. 500内部服务器错误
```bash
# 检查PHP错误日志
tail -f /var/log/php8.1-fpm.log

# 检查应用日志
tail -f /var/www/alingai-pro-v5/storage/logs/laravel.log

# 检查文件权限
sudo chown -R www-data:www-data /var/www/alingai-pro-v5
sudo chmod -R 755 /var/www/alingai-pro-v5
```

#### 2. 数据库连接失败
```bash
# 检查MySQL服务状态
sudo systemctl status mysql

# 测试数据库连接
mysql -u alingai -p -h 127.0.0.1 alingai_pro_v5

# 检查防火墙设置
sudo ufw status  # Ubuntu/Debian
sudo firewall-cmd --list-all  # CentOS/RHEL
```

#### 3. 静态资源404错误
```bash
# 检查Nginx配置
sudo nginx -t

# 重新加载Nginx
sudo systemctl reload nginx

# 检查文件路径
ls -la /var/www/alingai-pro-v5/public/
```

#### 4. WebSocket连接失败
```bash
# 检查WebSocket服务
sudo systemctl status alingai-websocket

# 检查端口监听
netstat -tlnp | grep :8080

# 重启WebSocket服务
sudo systemctl restart alingai-websocket
```

---

## 📞 技术支持

如果您在部署过程中遇到问题，请：

1. **查看日志文件**：检查相关服务的错误日志
2. **检查系统状态**：确认所有服务正常运行
3. **参考文档**：查阅详细的技术文档
4. **联系支持**：通过以下方式获取帮助
   - 技术论坛：https://forum.alingai.com
   - 工单系统：https://support.alingai.com
   - 邮件支持：support@alingai.com

---

**部署指南版本**：v5.0.0  
**最后更新**：2025年6月10日  
**下次更新**：根据系统版本发布
