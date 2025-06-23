#!/bin/bash

# AlingAI Pro 5.0 一键部署脚本
# 适用于Linux服务器环境：PHP 8.1+ + MySQL 8.0+ + Nginx 1.20+
# 作者: GitHub Copilot
# 版本: 5.0.0

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_success() {
    echo -e "${CYAN}[SUCCESS]${NC} $1"
}

# 显示Banner
show_banner() {
    echo -e "${PURPLE}"
    cat << "EOF"
    ___    ___                  ___    ____   ____             __________ 
   /   |  / (_)___  ____ ______/   |  /  _/  / __ \_________  / ____/ __ \
  / /| | / / / __ \/ __ `/ ___/ /| |  / /   / /_/ / ___/ __ \/ /_  / / / /
 / ___ |/ / / / / / /_/ (__  ) ___ |_/ /   / ____/ /  / /_/ / __/ / /_/ / 
/_/  |_/_/_/_/ /_/\__, /____/_/  |_/___/  /_/   /_/   \____/_/    \____/  
                /____/                                                    
                        超级智能办公一体化系统 5.0
EOF
    echo -e "${NC}"
    echo -e "${BLUE}========================================================${NC}"
    echo -e "${GREEN}AlingAI Pro 5.0 自动化部署系统${NC}"
    echo -e "${GREEN}支持政企融合、智能办公、AI决策等功能${NC}"
    echo -e "${BLUE}========================================================${NC}"
    echo ""
}

# 检查系统环境
check_system() {
    log_info "检查系统环境..."
    
    # 检查操作系统
    if [[ ! -f /etc/os-release ]]; then
        log_error "不支持的操作系统"
        exit 1
    fi
    
    . /etc/os-release
    OS=$ID
    VERSION=$VERSION_ID
    
    log_info "操作系统: $PRETTY_NAME"
    
    # 检查是否为root用户
    if [[ $EUID -ne 0 ]]; then
        log_error "请使用 root 用户运行此脚本"
        exit 1
    fi
    
    # 检查系统架构
    ARCH=$(uname -m)
    log_info "系统架构: $ARCH"
    
    # 检查内存
    MEMORY=$(free -m | awk 'NR==2{printf "%.1fGB", $2/1024}')
    log_info "系统内存: $MEMORY"
    
    # 检查磁盘空间
    DISK=$(df -h / | awk 'NR==2{print $4}')
    log_info "可用磁盘空间: $DISK"
}

# 更新系统包
update_system() {
    log_info "更新系统包..."
    
    case $OS in
        ubuntu|debian)
            apt-get update -y
            apt-get upgrade -y
            apt-get install -y curl wget unzip git software-properties-common
            ;;
        centos|rhel|rocky|almalinux)
            yum update -y
            yum install -y curl wget unzip git epel-release
            ;;
        *)
            log_error "不支持的操作系统: $OS"
            exit 1
            ;;
    esac
}

# 安装PHP 8.1+
install_php() {
    log_info "安装 PHP 8.1+..."
    
    case $OS in
        ubuntu|debian)
            add-apt-repository ppa:ondrej/php -y
            apt-get update -y
            apt-get install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis \
                php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip \
                php8.1-bcmath php8.1-json php8.1-intl php8.1-imagick \
                php8.1-swoole php8.1-mongodb php8.1-sqlite3 php8.1-pgsql \
                php8.1-xdebug php8.1-opcache
            ;;
        centos|rhel|rocky|almalinux)
            dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
            dnf module reset php -y
            dnf module enable php:remi-8.1 -y
            dnf install -y php php-fpm php-mysql php-redis php-curl php-gd \
                php-mbstring php-xml php-zip php-bcmath php-json php-intl \
                php-imagick php-swoole php-mongodb php-sqlite3 php-pgsql \
                php-xdebug php-opcache
            ;;
    esac
    
    # 配置PHP
    configure_php
    
    # 启动PHP-FPM
    systemctl enable php8.1-fpm || systemctl enable php-fpm
    systemctl start php8.1-fpm || systemctl start php-fpm
    
    PHP_VERSION=$(php --version | head -n1 | cut -d" " -f2)
    log_success "PHP $PHP_VERSION 安装完成"
}

# 配置PHP
configure_php() {
    log_info "配置 PHP..."
    
    # 找到PHP配置文件路径
    PHP_INI=$(php --ini | grep "Loaded Configuration File" | cut -d: -f2 | tr -d ' ')
    
    if [[ -z "$PHP_INI" ]]; then
        PHP_INI="/etc/php/8.1/fpm/php.ini"
    fi
    
    # 备份原配置
    cp "$PHP_INI" "$PHP_INI.backup"
    
    # 优化PHP配置
    cat >> "$PHP_INI" << 'EOF'

; AlingAI Pro 5.0 优化配置
memory_limit = 512M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000

; OPcache 优化
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.enable_file_override=1

; 错误日志
log_errors = On
error_log = /var/log/php_errors.log

; 会话配置
session.gc_maxlifetime = 7200
session.cookie_lifetime = 7200
session.cookie_secure = 1
session.cookie_httponly = 1
session.use_strict_mode = 1

; 安全配置
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
EOF

    log_success "PHP 配置优化完成"
}

# 安装MySQL 8.0+
install_mysql() {
    log_info "安装 MySQL 8.0+..."
    
    case $OS in
        ubuntu|debian)
            # 下载MySQL APT Repository
            wget https://dev.mysql.com/get/mysql-apt-config_0.8.24-1_all.deb
            dpkg -i mysql-apt-config_0.8.24-1_all.deb
            apt-get update -y
            
            # 设置MySQL root密码
            echo "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD" | debconf-set-selections
            echo "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD" | debconf-set-selections
            
            apt-get install -y mysql-server
            ;;
        centos|rhel|rocky|almalinux)
            dnf install -y mysql-server
            ;;
    esac
    
    # 启动MySQL
    systemctl enable mysqld
    systemctl start mysqld
    
    # 配置MySQL
    configure_mysql
    
    MYSQL_VERSION=$(mysql --version | cut -d" " -f3)
    log_success "MySQL $MYSQL_VERSION 安装完成"
}

# 配置MySQL
configure_mysql() {
    log_info "配置 MySQL..."
    
    # 生成随机密码
    if [[ -z "$MYSQL_ROOT_PASSWORD" ]]; then
        MYSQL_ROOT_PASSWORD=$(openssl rand -base64 32)
        echo "MySQL Root Password: $MYSQL_ROOT_PASSWORD" > /root/mysql_password.txt
        log_warn "MySQL root密码已保存到 /root/mysql_password.txt"
    fi
    
    # MySQL安全配置
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "
        ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';
        DELETE FROM mysql.user WHERE User='';
        DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
        DROP DATABASE IF EXISTS test;
        DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
        FLUSH PRIVILEGES;
    " 2>/dev/null || {
        # 如果是新安装，尝试无密码连接
        mysql -u root -e "
            ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';
            DELETE FROM mysql.user WHERE User='';
            DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
            DROP DATABASE IF EXISTS test;
            DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
            FLUSH PRIVILEGES;
        "
    }
    
    # 创建数据库和用户
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "
        CREATE DATABASE IF NOT EXISTS alingai_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        CREATE USER IF NOT EXISTS 'alingai'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
        GRANT ALL PRIVILEGES ON alingai_pro.* TO 'alingai'@'localhost';
        FLUSH PRIVILEGES;
    "
    
    # 优化MySQL配置
    cat >> /etc/mysql/mysql.conf.d/mysqld.cnf << 'EOF' || cat >> /etc/my.cnf << 'EOF'

[mysqld]
# AlingAI Pro 5.0 MySQL优化配置
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1

# 连接优化
max_connections = 500
max_connect_errors = 1000
table_open_cache = 4096
thread_cache_size = 256

# 查询优化
query_cache_type = 1
query_cache_size = 256M
sort_buffer_size = 4M
read_buffer_size = 2M
read_rnd_buffer_size = 8M
join_buffer_size = 8M

# 慢查询日志
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# 安全配置
bind-address = 127.0.0.1
local-infile = 0
EOF
    
    systemctl restart mysqld || systemctl restart mysql
    
    log_success "MySQL 配置优化完成"
}

# 安装Nginx 1.20+
install_nginx() {
    log_info "安装 Nginx 1.20+..."
    
    case $OS in
        ubuntu|debian)
            curl -fsSL https://nginx.org/keys/nginx_signing.key | apt-key add -
            echo "deb https://nginx.org/packages/ubuntu/ $(lsb_release -cs) nginx" > /etc/apt/sources.list.d/nginx.list
            apt-get update -y
            apt-get install -y nginx
            ;;
        centos|rhel|rocky|almalinux)
            cat > /etc/yum.repos.d/nginx.repo << 'EOF'
[nginx-stable]
name=nginx stable repo
baseurl=http://nginx.org/packages/centos/$releasever/$basearch/
gpgcheck=1
enabled=1
gpgkey=https://nginx.org/keys/nginx_signing.key
module_hotfixes=true
EOF
            yum install -y nginx
            ;;
    esac
    
    # 配置Nginx
    configure_nginx
    
    # 启动Nginx
    systemctl enable nginx
    systemctl start nginx
    
    NGINX_VERSION=$(nginx -v 2>&1 | cut -d/ -f2)
    log_success "Nginx $NGINX_VERSION 安装完成"
}

# 配置Nginx
configure_nginx() {
    log_info "配置 Nginx..."
    
    # 备份原配置
    cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup
    
    # 创建AlingAI Pro配置
    cat > /etc/nginx/conf.d/alingai-pro.conf << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    
    # 安全头部
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # 根目录
    root /var/www/alingai-pro/public;
    index index.php index.html index.htm;
    
    # 字符集
    charset utf-8;
    
    # 安全配置
    server_tokens off;
    
    # 限制请求大小
    client_max_body_size 100M;
    
    # 主要location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # PHP安全配置
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }
    
    # 静态资源缓存
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # API路由
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }
    
    # WebSocket支持
    location /websocket {
        proxy_pass http://127.0.0.1:9501;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
    
    # 管理员面板
    location /admin {
        try_files $uri $uri/ /admin/index.php?$query_string;
        
        # IP白名单（可选）
        # allow 127.0.0.1;
        # deny all;
    }
    
    # 安全配置
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~* \.(env|git|svn|htaccess|htpasswd)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # 日志配置
    access_log /var/log/nginx/alingai-pro.access.log;
    error_log /var/log/nginx/alingai-pro.error.log;
}
EOF
    
    # 删除默认配置
    rm -f /etc/nginx/conf.d/default.conf
    
    # 优化Nginx主配置
    cat > /etc/nginx/nginx.conf << 'EOF'
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

# 优化事件处理
events {
    worker_connections 2048;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # 日志格式
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
                    
    access_log /var/log/nginx/access.log main;
    
    # 性能优化
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_names_hash_bucket_size 64;
    
    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss 
               application/atom+xml image/svg+xml;
    
    # 缓冲区配置
    client_body_buffer_size 128k;
    client_header_buffer_size 32k;
    large_client_header_buffers 4 32k;
    
    # 限流配置
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=admin:10m rate=1r/s;
    
    # SSL配置（如果需要）
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # 包含站点配置
    include /etc/nginx/conf.d/*.conf;
}
EOF
    
    # 测试配置
    nginx -t
    
    log_success "Nginx 配置完成"
}

# 安装Redis
install_redis() {
    log_info "安装 Redis..."
    
    case $OS in
        ubuntu|debian)
            apt-get install -y redis-server
            ;;
        centos|rhel|rocky|almalinux)
            dnf install -y redis
            ;;
    esac
    
    # 配置Redis
    configure_redis
    
    # 启动Redis
    systemctl enable redis-server || systemctl enable redis
    systemctl start redis-server || systemctl start redis
    
    REDIS_VERSION=$(redis-server --version | cut -d" " -f3 | cut -d"=" -f2)
    log_success "Redis $REDIS_VERSION 安装完成"
}

# 配置Redis
configure_redis() {
    log_info "配置 Redis..."
    
    REDIS_CONF="/etc/redis/redis.conf"
    if [[ ! -f "$REDIS_CONF" ]]; then
        REDIS_CONF="/etc/redis.conf"
    fi
    
    # 备份配置
    cp "$REDIS_CONF" "$REDIS_CONF.backup"
    
    # 优化Redis配置
    cat >> "$REDIS_CONF" << 'EOF'

# AlingAI Pro 5.0 Redis优化配置
maxmemory 1gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000

# 安全配置
requirepass your_redis_password_here
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command KEYS ""
rename-command CONFIG ""

# 网络配置
bind 127.0.0.1
protected-mode yes
port 6379
tcp-keepalive 300

# 日志配置
loglevel notice
logfile /var/log/redis/redis-server.log
EOF
    
    # 生成Redis密码
    REDIS_PASSWORD=$(openssl rand -base64 32)
    sed -i "s/your_redis_password_here/$REDIS_PASSWORD/" "$REDIS_CONF"
    
    echo "Redis Password: $REDIS_PASSWORD" >> /root/redis_password.txt
    log_warn "Redis密码已保存到 /root/redis_password.txt"
}

# 安装Composer
install_composer() {
    log_info "安装 Composer..."
    
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    
    COMPOSER_VERSION=$(composer --version | cut -d" " -f3)
    log_success "Composer $COMPOSER_VERSION 安装完成"
}

# 部署AlingAI Pro应用
deploy_application() {
    log_info "部署 AlingAI Pro 5.0 应用..."
    
    # 创建应用目录
    mkdir -p /var/www/alingai-pro
    cd /var/www/alingai-pro
    
    # 如果是从Git部署
    if [[ -n "$GIT_REPOSITORY" ]]; then
        git clone "$GIT_REPOSITORY" .
    else
        # 复制当前目录的文件
        cp -r "$SCRIPT_DIR"/* .
    fi
    
    # 设置权限
    chown -R nginx:nginx /var/www/alingai-pro
    chmod -R 755 /var/www/alingai-pro
    chmod -R 775 /var/www/alingai-pro/storage
    chmod -R 775 /var/www/alingai-pro/cache
    chmod -R 775 /var/www/alingai-pro/logs
    
    # 安装依赖
    if [[ -f "composer.json" ]]; then
        composer install --no-dev --optimize-autoloader
    fi
    
    # 创建配置文件
    create_config_file
    
    # 运行数据库迁移
    run_database_migration
    
    log_success "应用部署完成"
}

# 创建配置文件
create_config_file() {
    log_info "创建配置文件..."
    
    cat > /var/www/alingai-pro/config.php << EOF
<?php
/**
 * AlingAI Pro 5.0 配置文件
 * 自动生成于: $(date)
 */

return [
    // 数据库配置
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'alingai_pro',
        'username' => 'alingai',
        'password' => '$DB_PASSWORD',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'engine' => 'InnoDB',
    ],
    
    // Redis配置
    'redis' => [
        'host' => 'localhost',
        'port' => 6379,
        'password' => '$REDIS_PASSWORD',
        'database' => 0,
    ],
    
    // 应用配置
    'app' => [
        'name' => 'AlingAI Pro',
        'version' => '5.0.0',
        'debug' => false,
        'timezone' => 'Asia/Shanghai',
        'locale' => 'zh-CN',
        'key' => '$(openssl rand -base64 32)',
    ],
    
    // 安全配置
    'security' => [
        'encryption_key' => '$(openssl rand -base64 32)',
        'jwt_secret' => '$(openssl rand -base64 64)',
        'csrf_token_lifetime' => 3600,
        'session_lifetime' => 7200,
    ],
    
    // AI服务配置
    'ai' => [
        'deepseek' => [
            'api_key' => '',
            'api_url' => 'https://api.deepseek.com',
            'model' => 'deepseek-chat',
        ],
    ],
    
    // 文件存储
    'storage' => [
        'default' => 'local',
        'local' => [
            'path' => '/var/www/alingai-pro/storage',
            'url' => '/storage',
        ],
    ],
    
    // 日志配置
    'logging' => [
        'level' => 'info',
        'path' => '/var/www/alingai-pro/logs',
        'max_files' => 30,
    ],
];
EOF
    
    chmod 600 /var/www/alingai-pro/config.php
    log_success "配置文件创建完成"
}

# 运行数据库迁移
run_database_migration() {
    log_info "运行数据库迁移..."
    
    cd /var/www/alingai-pro
    
    # 检查是否有迁移脚本
    if [[ -f "database/migrations.php" ]]; then
        php database/migrations.php
    elif [[ -f "install/database.sql" ]]; then
        mysql -u alingai -p"$DB_PASSWORD" alingai_pro < install/database.sql
    else
        log_warn "未找到数据库迁移文件"
    fi
    
    log_success "数据库迁移完成"
}

# 安装SSL证书
install_ssl() {
    log_info "安装 SSL 证书..."
    
    if [[ -n "$DOMAIN" ]]; then
        # 使用Let's Encrypt
        case $OS in
            ubuntu|debian)
                apt-get install -y certbot python3-certbot-nginx
                ;;
            centos|rhel|rocky|almalinux)
                dnf install -y certbot python3-certbot-nginx
                ;;
        esac
        
        certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "$EMAIL"
        
        log_success "SSL证书安装完成"
    else
        log_warn "未指定域名，跳过SSL证书安装"
    fi
}

# 配置防火墙
configure_firewall() {
    log_info "配置防火墙..."
    
    if command -v ufw >/dev/null 2>&1; then
        # Ubuntu/Debian - UFW
        ufw --force reset
        ufw default deny incoming
        ufw default allow outgoing
        ufw allow 22/tcp
        ufw allow 80/tcp
        ufw allow 443/tcp
        ufw --force enable
        log_success "UFW防火墙配置完成"
    elif command -v firewall-cmd >/dev/null 2>&1; then
        # CentOS/RHEL - firewalld
        systemctl enable firewalld
        systemctl start firewalld
        firewall-cmd --permanent --zone=public --add-service=http
        firewall-cmd --permanent --zone=public --add-service=https
        firewall-cmd --permanent --zone=public --add-service=ssh
        firewall-cmd --reload
        log_success "firewalld防火墙配置完成"
    else
        log_warn "未找到防火墙管理工具"
    fi
}

# 设置系统监控
setup_monitoring() {
    log_info "设置系统监控..."
    
    # 创建监控脚本
    cat > /usr/local/bin/alingai-monitor.sh << 'EOF'
#!/bin/bash
# AlingAI Pro 系统监控脚本

LOG_FILE="/var/log/alingai-monitor.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# 检查服务状态
check_service() {
    local service=$1
    if systemctl is-active --quiet $service; then
        echo "[$DATE] $service: 运行正常" >> $LOG_FILE
    else
        echo "[$DATE] $service: 服务异常" >> $LOG_FILE
        systemctl restart $service
    fi
}

# 检查磁盘空间
check_disk() {
    local usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    if [ $usage -gt 80 ]; then
        echo "[$DATE] 磁盘空间不足: ${usage}%" >> $LOG_FILE
    fi
}

# 检查内存使用
check_memory() {
    local usage=$(free | awk 'NR==2{printf "%.1f", $3*100/$2}')
    if (( $(echo "$usage > 90" | bc -l) )); then
        echo "[$DATE] 内存使用率过高: ${usage}%" >> $LOG_FILE
    fi
}

# 执行检查
check_service nginx
check_service php8.1-fpm
check_service mysql
check_service redis-server
check_disk
check_memory
EOF
    
    chmod +x /usr/local/bin/alingai-monitor.sh
    
    # 添加定时任务
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/alingai-monitor.sh") | crontab -
    
    log_success "系统监控设置完成"
}

# 创建启动脚本
create_startup_script() {
    log_info "创建启动脚本..."
    
    cat > /usr/local/bin/alingai-start.sh << 'EOF'
#!/bin/bash
# AlingAI Pro 5.0 启动脚本

echo "启动 AlingAI Pro 5.0 服务..."

# 启动基础服务
systemctl start mysql
systemctl start redis-server
systemctl start php8.1-fpm
systemctl start nginx

# 启动Swoole服务器（如果存在）
if [[ -f "/var/www/alingai-pro/server.php" ]]; then
    cd /var/www/alingai-pro
    php server.php -d &
    echo "Swoole服务器已启动"
fi

echo "所有服务启动完成"
EOF
    
    cat > /usr/local/bin/alingai-stop.sh << 'EOF'
#!/bin/bash
# AlingAI Pro 5.0 停止脚本

echo "停止 AlingAI Pro 5.0 服务..."

# 停止Swoole服务器
pkill -f "php server.php"

# 停止基础服务
systemctl stop nginx
systemctl stop php8.1-fpm
systemctl stop redis-server
systemctl stop mysql

echo "所有服务已停止"
EOF
    
    chmod +x /usr/local/bin/alingai-start.sh
    chmod +x /usr/local/bin/alingai-stop.sh
    
    log_success "启动脚本创建完成"
}

# 优化系统性能
optimize_system() {
    log_info "优化系统性能..."
    
    # 内核参数优化
    cat >> /etc/sysctl.conf << 'EOF'

# AlingAI Pro 5.0 系统优化
net.core.somaxconn = 65535
net.core.netdev_max_backlog = 5000
net.ipv4.tcp_max_syn_backlog = 65535
net.ipv4.tcp_fin_timeout = 30
net.ipv4.tcp_keepalive_time = 300
net.ipv4.tcp_keepalive_probes = 3
net.ipv4.tcp_keepalive_intvl = 30
net.ipv4.tcp_tw_reuse = 1
net.ipv4.ip_local_port_range = 1024 65535
vm.swappiness = 10
vm.dirty_ratio = 15
vm.dirty_background_ratio = 5
fs.file-max = 655350
EOF
    
    sysctl -p
    
    # 文件描述符限制
    cat >> /etc/security/limits.conf << 'EOF'
* soft nofile 655350
* hard nofile 655350
* soft nproc 655350
* hard nproc 655350
EOF
    
    log_success "系统性能优化完成"
}

# 生成部署报告
generate_report() {
    log_info "生成部署报告..."
    
    REPORT_FILE="/root/alingai-pro-deployment-report.txt"
    
    cat > "$REPORT_FILE" << EOF
============================================
AlingAI Pro 5.0 部署报告
============================================
部署时间: $(date)
操作系统: $PRETTY_NAME
服务器IP: $(curl -s ifconfig.me 2>/dev/null || echo "未知")

软件版本:
- PHP: $(php --version | head -n1)
- MySQL: $(mysql --version)
- Nginx: $(nginx -v 2>&1)
- Redis: $(redis-server --version)
- Composer: $(composer --version)

数据库信息:
- 数据库名: alingai_pro
- 用户名: alingai
- 密码: $DB_PASSWORD

Redis信息:
- 端口: 6379
- 密码: $REDIS_PASSWORD

应用信息:
- 安装路径: /var/www/alingai-pro
- 配置文件: /var/www/alingai-pro/config.php
- 日志目录: /var/www/alingai-pro/logs

管理命令:
- 启动服务: /usr/local/bin/alingai-start.sh
- 停止服务: /usr/local/bin/alingai-stop.sh
- 监控日志: tail -f /var/log/alingai-monitor.log

访问地址:
- HTTP: http://$(curl -s ifconfig.me 2>/dev/null || echo "your-server-ip")
- HTTPS: https://$(curl -s ifconfig.me 2>/dev/null || echo "your-domain") (如果配置了SSL)

安全提醒:
1. 请妥善保管数据库密码和Redis密码
2. 建议定期备份数据库
3. 建议配置SSL证书
4. 建议修改默认的管理员密码

============================================
部署完成！请访问上述地址开始使用 AlingAI Pro 5.0
============================================
EOF
    
    log_success "部署报告已生成: $REPORT_FILE"
}

# 主函数
main() {
    # 获取脚本目录
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    
    # 生成随机密码
    DB_PASSWORD=$(openssl rand -base64 32)
    MYSQL_ROOT_PASSWORD=$(openssl rand -base64 32)
    
    # 显示Banner
    show_banner
    
    # 参数解析
    while [[ $# -gt 0 ]]; do
        case $1 in
            --domain)
                DOMAIN="$2"
                shift 2
                ;;
            --email)
                EMAIL="$2"
                shift 2
                ;;
            --git-repo)
                GIT_REPOSITORY="$2"
                shift 2
                ;;
            --help)
                echo "用法: $0 [选项]"
                echo "选项:"
                echo "  --domain DOMAIN    指定域名（用于SSL证书）"
                echo "  --email EMAIL      指定邮箱（用于SSL证书）"
                echo "  --git-repo URL     从Git仓库部署"
                echo "  --help             显示帮助信息"
                exit 0
                ;;
            *)
                log_error "未知参数: $1"
                exit 1
                ;;
        esac
    done
    
    # 确认开始部署
    echo -e "${YELLOW}即将开始 AlingAI Pro 5.0 部署，是否继续？ [y/N]${NC}"
    read -r CONFIRM
    if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
        log_info "部署已取消"
        exit 0
    fi
    
    # 开始部署
    log_info "开始部署 AlingAI Pro 5.0..."
    
    check_system
    update_system
    install_php
    install_mysql
    install_nginx
    install_redis
    install_composer
    deploy_application
    
    if [[ -n "$DOMAIN" && -n "$EMAIL" ]]; then
        install_ssl
    fi
    
    configure_firewall
    setup_monitoring
    create_startup_script
    optimize_system
    generate_report
    
    # 启动所有服务
    /usr/local/bin/alingai-start.sh
    
    log_success "AlingAI Pro 5.0 部署完成！"
    echo ""
    echo -e "${GREEN}=================================${NC}"
    echo -e "${GREEN}  部署成功！${NC}"
    echo -e "${GREEN}=================================${NC}"
    echo -e "访问地址: ${BLUE}http://$(curl -s ifconfig.me 2>/dev/null || echo "your-server-ip")${NC}"
    echo -e "部署报告: ${BLUE}/root/alingai-pro-deployment-report.txt${NC}"
    echo -e "${GREEN}=================================${NC}"
}

# 执行主函数
main "$@"
