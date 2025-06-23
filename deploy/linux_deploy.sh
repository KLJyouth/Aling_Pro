#!/bin/bash

# AlingAI Pro 5.0 一键部署脚本
# 适用于 Linux 生产环境部署
# 支持 Ubuntu 20.04+ / CentOS 8+ / RHEL 8+

set -e

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 配置变量
ALINGAI_VERSION="5.0.0"
INSTALL_DIR="/opt/alingai-pro"
DATA_DIR="/var/lib/alingai"
LOG_DIR="/var/log/alingai"
CONFIG_DIR="/etc/alingai"
SERVICE_USER="alingai"
DB_NAME="alingai_pro_5"
DOMAIN_NAME=""
SSL_ENABLED=false
BACKUP_ENABLED=true
MONITORING_ENABLED=true

# 系统信息
OS=""
OS_VERSION=""
ARCH=""

# 依赖版本
PHP_VERSION="8.3"
NGINX_VERSION="latest"
MYSQL_VERSION="8.0"
REDIS_VERSION="7.0"
NODE_VERSION="18"

print_banner() {
    echo -e "${CYAN}"
    echo "╔══════════════════════════════════════════════════════════════╗"
    echo "║                                                              ║"
    echo "║        AlingAI Pro 5.0 政企融合智能办公系统                 ║"
    echo "║               Linux 生产环境一键部署工具                    ║"
    echo "║                                                              ║"
    echo "║                    版本: ${ALINGAI_VERSION}                           ║"
    echo "║                发布日期: 2025-06-09                          ║"
    echo "║                                                              ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
    exit 1
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] SUCCESS: $1${NC}"
}

# 检测操作系统
detect_os() {
    log "检测操作系统..."
    
    if [[ -f /etc/os-release ]]; then
        source /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    elif [[ -f /etc/redhat-release ]]; then
        OS="centos"
        OS_VERSION=$(grep -oE '[0-9]+\.[0-9]+' /etc/redhat-release | head -1)
    else
        error "不支持的操作系统"
    fi
    
    ARCH=$(uname -m)
    
    info "操作系统: $OS $OS_VERSION"
    info "架构: $ARCH"
    
    # 验证支持的系统
    case $OS in
        ubuntu)
            if [[ $(echo "$OS_VERSION >= 20.04" | bc -l) -eq 0 ]]; then
                error "需要 Ubuntu 20.04 或更高版本"
            fi
            ;;
        centos|rhel)
            if [[ $(echo "$OS_VERSION >= 8.0" | bc -l) -eq 0 ]]; then
                error "需要 CentOS/RHEL 8.0 或更高版本"
            fi
            ;;
        *)
            error "不支持的操作系统: $OS"
            ;;
    esac
}

# 检查系统要求
check_requirements() {
    log "检查系统要求..."
    
    # 检查内存
    MEMORY_GB=$(free -g | awk '/^Mem:/{print $2}')
    if [[ $MEMORY_GB -lt 4 ]]; then
        error "至少需要 4GB 内存，当前: ${MEMORY_GB}GB"
    fi
    
    # 检查磁盘空间
    DISK_GB=$(df / | awk 'NR==2{print int($4/1024/1024)}')
    if [[ $DISK_GB -lt 20 ]]; then
        error "至少需要 20GB 磁盘空间，当前可用: ${DISK_GB}GB"
    fi
    
    # 检查CPU核心数
    CPU_CORES=$(nproc)
    if [[ $CPU_CORES -lt 2 ]]; then
        warn "建议至少使用 2 个CPU核心，当前: ${CPU_CORES}"
    fi
    
    success "系统要求检查通过"
}

# 安装基础依赖
install_base_dependencies() {
    log "安装基础依赖..."
    
    case $OS in
        ubuntu)
            apt-get update
            apt-get install -y curl wget git unzip software-properties-common \
                ca-certificates lsb-release gnupg2 apt-transport-https \
                build-essential supervisor certbot python3-certbot-nginx \
                htop iotop nethogs tree vim nano bc firewalld fail2ban \
                logrotate cron rsync openssl
            ;;
        centos|rhel)
            yum update -y
            yum install -y epel-release
            yum install -y curl wget git unzip gcc gcc-c++ make \
                ca-certificates supervisor certbot python3-certbot-nginx \
                htop iotop tree vim nano bc
            ;;
    esac
    
    success "基础依赖安装完成"
}

# 安装PHP 8.3
install_php() {
    log "安装 PHP ${PHP_VERSION}..."
    
    case $OS in
        ubuntu)
            add-apt-repository -y ppa:ondrej/php
            apt-get update
            apt-get install -y php${PHP_VERSION} php${PHP_VERSION}-fpm \
                php${PHP_VERSION}-mysql php${PHP_VERSION}-redis \
                php${PHP_VERSION}-curl php${PHP_VERSION}-json \
                php${PHP_VERSION}-zip php${PHP_VERSION}-gd \
                php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml \
                php${PHP_VERSION}-bcmath php${PHP_VERSION}-intl \
                php${PHP_VERSION}-soap php${PHP_VERSION}-xsl \
                php${PHP_VERSION}-opcache php${PHP_VERSION}-cli
            ;;
        centos|rhel)
            dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
            dnf module enable -y php:remi-${PHP_VERSION}
            dnf install -y php php-fpm php-mysqlnd php-redis \
                php-curl php-json php-zip php-gd \
                php-mbstring php-xml php-bcmath php-intl \
                php-soap php-xsl php-opcache php-cli
            ;;
    esac
    
    # 配置PHP
    PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
    if [[ $OS == "centos" || $OS == "rhel" ]]; then
        PHP_INI="/etc/php.ini"
    fi
    
    # 优化PHP配置
    sed -i 's/memory_limit = .*/memory_limit = 512M/' $PHP_INI
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' $PHP_INI
    sed -i 's/post_max_size = .*/post_max_size = 100M/' $PHP_INI
    sed -i 's/max_execution_time = .*/max_execution_time = 300/' $PHP_INI
    sed -i 's/max_input_vars = .*/max_input_vars = 5000/' $PHP_INI
    
    # 安装Composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    
    success "PHP ${PHP_VERSION} 安装完成"
}

# 安装Nginx
install_nginx() {
    log "安装 Nginx..."
    
    case $OS in
        ubuntu)
            apt-get install -y nginx
            ;;
        centos|rhel)
            dnf install -y nginx
            ;;
    esac
    
    # 启用和启动Nginx
    systemctl enable nginx
    systemctl start nginx
    
    success "Nginx 安装完成"
}

# 安装MySQL 8.0
install_mysql() {
    log "安装 MySQL ${MYSQL_VERSION}..."
    
    # 生成随机密码
    MYSQL_ROOT_PASSWORD=$(openssl rand -base64 32)
    
    case $OS in
        ubuntu)
            apt-get install -y mysql-server-${MYSQL_VERSION}
            ;;
        centos|rhel)
            dnf module disable -y mysql
            dnf install -y https://dev.mysql.com/get/mysql80-community-release-el8-1.noarch.rpm
            dnf install -y mysql-community-server
            ;;
    esac
    
    # 启用和启动MySQL
    systemctl enable mysqld
    systemctl start mysqld
    
    # 配置MySQL
    if [[ $OS == "centos" || $OS == "rhel" ]]; then
        # 获取临时密码
        TEMP_PASSWORD=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $NF}')
        
        # 重置root密码
        mysql -u root -p"$TEMP_PASSWORD" --connect-expired-password <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';
EOF
    else
        # Ubuntu的安全安装
        mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$MYSQL_ROOT_PASSWORD';
EOF
    fi
    
    # 创建数据库和用户
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<EOF
CREATE DATABASE $DB_NAME DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alingai'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO 'alingai'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    # 保存密码
    echo "MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD" >> /root/.alingai_credentials
    echo "MYSQL_USER_PASSWORD=$MYSQL_ROOT_PASSWORD" >> /root/.alingai_credentials
    
    success "MySQL ${MYSQL_VERSION} 安装完成"
}

# 安装Redis
install_redis() {
    log "安装 Redis ${REDIS_VERSION}..."
    
    case $OS in
        ubuntu)
            apt-get install -y redis-server
            ;;
        centos|rhel)
            dnf install -y redis
            ;;
    esac
    
    # 配置Redis
    sed -i 's/# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf 2>/dev/null || \
    sed -i 's/# maxmemory <bytes>/maxmemory 256mb/' /etc/redis.conf
    
    # 启用和启动Redis
    systemctl enable redis
    systemctl start redis
    
    success "Redis ${REDIS_VERSION} 安装完成"
}

# 安装Node.js
install_nodejs() {
    log "安装 Node.js ${NODE_VERSION}..."
    
    # 使用NodeSource repository
    curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
    
    case $OS in
        ubuntu)
            apt-get install -y nodejs
            ;;
        centos|rhel)
            dnf install -y nodejs npm
            ;;
    esac
    
    # 全局安装必要的包
    npm install -g pm2 yarn
    
    success "Node.js ${NODE_VERSION} 安装完成"
}

# 创建系统用户
create_system_user() {
    log "创建系统用户..."
    
    if ! id "$SERVICE_USER" &>/dev/null; then
        useradd -r -m -s /bin/bash -d "$INSTALL_DIR" "$SERVICE_USER"
        success "系统用户 $SERVICE_USER 创建完成"
    else
        info "系统用户 $SERVICE_USER 已存在"
    fi
}

# 创建目录结构
create_directories() {
    log "创建目录结构..."
    
    mkdir -p "$INSTALL_DIR"
    mkdir -p "$DATA_DIR"/{uploads,cache,sessions,logs}
    mkdir -p "$LOG_DIR"
    mkdir -p "$CONFIG_DIR"
    mkdir -p /var/run/alingai
    mkdir -p /var/lib/alingai/{backups,security,ai-models}
    
    # 设置权限
    chown -R "$SERVICE_USER:$SERVICE_USER" "$INSTALL_DIR"
    chown -R "$SERVICE_USER:$SERVICE_USER" "$DATA_DIR"
    chown -R "$SERVICE_USER:$SERVICE_USER" "$LOG_DIR"
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 750 "$DATA_DIR"
    chmod -R 750 "$LOG_DIR"
    
    success "目录结构创建完成"
}
    
    # 设置权限
    chown -R "$SERVICE_USER:$SERVICE_USER" "$INSTALL_DIR"
    chown -R "$SERVICE_USER:$SERVICE_USER" "$DATA_DIR"
    chown -R "$SERVICE_USER:$SERVICE_USER" "$LOG_DIR"
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 755 "$DATA_DIR"
    chmod -R 755 "$LOG_DIR"
    
    success "目录结构创建完成"
}

# 部署应用代码
deploy_application() {
    log "部署应用代码..."
    
    # 检查当前目录是否包含应用代码
    if [[ -f "composer.json" && -f "src/Core/Application.php" ]]; then
        info "从当前目录部署..."
        cp -r . "$INSTALL_DIR/"
    else
        # 从GitHub下载
        info "从GitHub下载最新代码..."
        git clone https://github.com/AlingAI/AlingAI-Pro.git "$INSTALL_DIR/"
        cd "$INSTALL_DIR"
        git checkout v5.0.0 2>/dev/null || git checkout main
    fi
    
    cd "$INSTALL_DIR"
    
    # 安装PHP依赖
    sudo -u "$SERVICE_USER" composer install --no-dev --optimize-autoloader
    
    # 安装前端依赖并构建
    if [[ -f "package.json" ]]; then
        sudo -u "$SERVICE_USER" npm install
        sudo -u "$SERVICE_USER" npm run build 2>/dev/null || true
    fi
    
    # 设置权限
    chown -R "$SERVICE_USER:$SERVICE_USER" "$INSTALL_DIR"
    find "$INSTALL_DIR" -type f -exec chmod 644 {} \;
    find "$INSTALL_DIR" -type d -exec chmod 755 {} \;
    chmod +x "$INSTALL_DIR/bin/"* 2>/dev/null || true
    
    success "应用代码部署完成"
}

# 配置应用
configure_application() {
    log "配置应用..."
    
    cd "$INSTALL_DIR"
    
    # 创建.env文件
    cat > .env <<EOF
# AlingAI Pro 5.0 生产环境配置
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${DOMAIN_NAME:-localhost}

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=alingai
DB_PASSWORD=$MYSQL_ROOT_PASSWORD

# Redis配置
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=

# 缓存配置
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# 日志配置
LOG_CHANNEL=daily
LOG_LEVEL=info

# DeepSeek AI配置
DEEPSEEK_API_KEY=sk-your-deepseek-api-key-here
DEEPSEEK_API_URL=https://api.deepseek.com/v1

# 安全配置
SECURITY_KEY=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -base64 64)
ENCRYPTION_KEY=$(openssl rand -base64 32)

# 文件存储
FILESYSTEM_DISK=local
UPLOAD_MAX_SIZE=100M

# 会话配置
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# CORS配置
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=*

# 监控配置
MONITORING_ENABLED=true
THREAT_DETECTION_ENABLED=true
AI_EVOLUTION_ENABLED=true
REAL_TIME_MONITORING=true
EOF

    # 设置环境文件权限
    chmod 600 .env
    chown "$SERVICE_USER:$SERVICE_USER" .env
    
    success "应用配置完成"
}

# 配置Nginx
configure_nginx() {
    log "配置Nginx..."
    
    # 创建Nginx站点配置
    cat > /etc/nginx/sites-available/alingai-pro <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN_NAME:-localhost} www.${DOMAIN_NAME:-localhost};
    
    # 强制HTTPS重定向
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN_NAME:-localhost} www.${DOMAIN_NAME:-localhost};
    
    root $INSTALL_DIR/public;
    index index.php index.html index.htm;
    
    # SSL配置
    ssl_certificate /etc/letsencrypt/live/${DOMAIN_NAME:-localhost}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN_NAME:-localhost}/privkey.pem;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;
    
    # 现代SSL配置
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # 安全头
    add_header Strict-Transport-Security "max-age=63072000" always;
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    
    # 文件上传大小限制
    client_max_body_size 100M;
    
    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    # 静态文件缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # PHP处理
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # 安全设置
        fastcgi_param SERVER_NAME \$server_name;
        fastcgi_param HTTPS on;
        fastcgi_param REQUEST_SCHEME https;
    }
    
    # 主路由
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # API路由
    location /api/ {
        try_files \$uri \$uri/ /index.php?\$query_string;
        
        # API限流
        limit_req zone=api burst=20 nodelay;
    }
    
    # 管理后台
    location /admin/ {
        try_files \$uri \$uri/ /index.php?\$query_string;
        
        # 限制访问IP（可选）
        # allow 192.168.1.0/24;
        # deny all;
    }
    
    # WebSocket代理（用于实时监控）
    location /ws/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
    
    # 禁止访问敏感文件
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(vendor|storage|database|config|\.env) {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # 日志配置
    access_log $LOG_DIR/nginx-access.log;
    error_log $LOG_DIR/nginx-error.log;
}

# 限流配置
limit_req_zone \$binary_remote_addr zone=api:10m rate=60r/m;
EOF

    # 启用站点
    ln -sf /etc/nginx/sites-available/alingai-pro /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # 测试配置
    nginx -t
    systemctl reload nginx
    
    success "Nginx配置完成"
}

# 配置PHP-FPM
configure_php_fpm() {
    log "配置PHP-FPM..."
    
    # 创建专用的PHP-FPM池
    cat > /etc/php/${PHP_VERSION}/fpm/pool.d/alingai.conf <<EOF
[alingai]
user = $SERVICE_USER
group = $SERVICE_USER
listen = /var/run/php/php${PHP_VERSION}-fpm-alingai.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; 性能优化
request_terminate_timeout = 300
request_slowlog_timeout = 10
slowlog = $LOG_DIR/php-slow.log

; 环境变量
env[HOSTNAME] = \$HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp

; 安全设置
php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f www@${DOMAIN_NAME:-localhost}
php_flag[display_errors] = off
php_admin_value[error_log] = $LOG_DIR/php-error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 512M
EOF

    # 重启PHP-FPM
    systemctl restart php${PHP_VERSION}-fpm
    
    success "PHP-FPM配置完成"
}

# 配置数据库
setup_database() {
    log "设置数据库..."
    
    cd "$INSTALL_DIR"
    
    # 运行数据库迁移
    sudo -u "$SERVICE_USER" php artisan migrate:install 2>/dev/null || true
    sudo -u "$SERVICE_USER" php artisan migrate --force
    
    # 导入安全监控表
    if [[ -f "database/migrations/create_security_monitoring_tables.sql" ]]; then
        mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME" < database/migrations/create_security_monitoring_tables.sql
    fi
    
    # 生成应用密钥
    sudo -u "$SERVICE_USER" php artisan key:generate --force
    
    # 清理缓存
    sudo -u "$SERVICE_USER" php artisan config:cache
    sudo -u "$SERVICE_USER" php artisan route:cache
    sudo -u "$SERVICE_USER" php artisan view:cache
    
    success "数据库设置完成"
}

# 配置系统服务
configure_services() {
    log "配置系统服务..."
    
    # AlingAI主服务
    cat > /etc/systemd/system/alingai-pro.service <<EOF
[Unit]
Description=AlingAI Pro 5.0 Application Server
After=network.target mysql.service redis.service
Wants=mysql.service redis.service

[Service]
Type=forking
User=$SERVICE_USER
Group=$SERVICE_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php $INSTALL_DIR/bin/server.php --daemon
ExecReload=/bin/kill -USR1 \$MAINPID
KillMode=mixed
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    # WebSocket安全监控服务
    cat > /etc/systemd/system/alingai-security-monitor.service <<EOF
[Unit]
Description=AlingAI Pro Security Monitoring WebSocket Server
After=network.target

[Service]
Type=simple
User=$SERVICE_USER
Group=$SERVICE_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php $INSTALL_DIR/start_security_monitoring.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    # AI工作队列服务
    cat > /etc/systemd/system/alingai-ai-workers.service <<EOF
[Unit]
Description=AlingAI Pro AI Workers
After=network.target redis.service

[Service]
Type=simple
User=$SERVICE_USER
Group=$SERVICE_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php $INSTALL_DIR/bin/ai-workers.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    # 重新加载systemd并启用服务
    systemctl daemon-reload
    systemctl enable alingai-pro
    systemctl enable alingai-security-monitor
    systemctl enable alingai-ai-workers
    
    success "系统服务配置完成"
}

# 配置防火墙
configure_firewall() {
    log "配置防火墙..."
    
    if command -v ufw &> /dev/null; then
        # Ubuntu UFW
        ufw --force enable
        ufw default deny incoming
        ufw default allow outgoing
        ufw allow ssh
        ufw allow 80
        ufw allow 443
        ufw allow 8080 # WebSocket
    elif command -v firewall-cmd &> /dev/null; then
        # CentOS/RHEL firewalld
        systemctl enable firewalld
        systemctl start firewalld
        firewall-cmd --permanent --add-service=ssh
        firewall-cmd --permanent --add-service=http
        firewall-cmd --permanent --add-service=https
        firewall-cmd --permanent --add-port=8080/tcp
        firewall-cmd --reload
    fi
    
    success "防火墙配置完成"
}

# 配置SSL证书
configure_ssl() {
    if [[ "$SSL_ENABLED" == "true" && -n "$DOMAIN_NAME" ]]; then
        log "配置SSL证书..."
        
        # 使用Let's Encrypt
        certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos --email admin@"$DOMAIN_NAME"
        
        # 设置自动续期
        echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
        
        success "SSL证书配置完成"
    fi
}

# 创建监控脚本
create_monitoring_scripts() {
    log "创建监控脚本..."
    
    # 系统监控脚本
    cat > /usr/local/bin/alingai-monitor.sh <<'EOF'
#!/bin/bash
# AlingAI Pro 5.0 系统监控脚本

LOG_FILE="/var/log/alingai/monitor.log"
INSTALL_DIR="/opt/alingai-pro"

check_service() {
    local service=$1
    if systemctl is-active --quiet "$service"; then
        echo "$(date): $service is running" >> "$LOG_FILE"
    else
        echo "$(date): WARNING - $service is not running" >> "$LOG_FILE"
        systemctl restart "$service"
    fi
}

# 检查核心服务
check_service "nginx"
check_service "mysql"
check_service "redis"
check_service "php8.3-fpm"
check_service "alingai-pro"
check_service "alingai-security-monitor"

# 检查磁盘空间
DISK_USAGE=$(df /opt | awk 'NR==2{print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 90 ]; then
    echo "$(date): WARNING - Disk usage is ${DISK_USAGE}%" >> "$LOG_FILE"
fi

# 检查内存使用
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ "$MEM_USAGE" -gt 90 ]; then
    echo "$(date): WARNING - Memory usage is ${MEM_USAGE}%" >> "$LOG_FILE"
fi
EOF

    chmod +x /usr/local/bin/alingai-monitor.sh
    
    # 添加到crontab
    echo "*/5 * * * * /usr/local/bin/alingai-monitor.sh" | crontab -
    
    success "监控脚本创建完成"
}

# 创建备份脚本
create_backup_scripts() {
    if [[ "$BACKUP_ENABLED" == "true" ]]; then
        log "创建备份脚本..."
        
        cat > /usr/local/bin/alingai-backup.sh <<EOF
#!/bin/bash
# AlingAI Pro 5.0 备份脚本

BACKUP_DIR="/var/lib/alingai/backups"
DATE=\$(date +%Y%m%d_%H%M%S)
DB_BACKUP="\$BACKUP_DIR/database_\$DATE.sql"
APP_BACKUP="\$BACKUP_DIR/application_\$DATE.tar.gz"

# 创建备份目录
mkdir -p "\$BACKUP_DIR"

# 数据库备份
mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME" > "\$DB_BACKUP"
gzip "\$DB_BACKUP"

# 应用文件备份
tar -czf "\$APP_BACKUP" -C "$INSTALL_DIR" --exclude='storage/cache/*' --exclude='storage/logs/*' .

# 清理旧备份（保留7天）
find "\$BACKUP_DIR" -name "*.sql.gz" -mtime +7 -delete
find "\$BACKUP_DIR" -name "*.tar.gz" -mtime +7 -delete

echo "\$(date): Backup completed - DB: \$DB_BACKUP.gz, APP: \$APP_BACKUP" >> "$LOG_DIR/backup.log"
EOF

        chmod +x /usr/local/bin/alingai-backup.sh
        
        # 添加到crontab（每天凌晨2点备份）
        echo "0 2 * * * /usr/local/bin/alingai-backup.sh" | crontab -
        
        success "备份脚本创建完成"
    fi
}

# 优化系统性能
optimize_system() {
    log "优化系统性能..."
    
    # 内核参数优化
    cat >> /etc/sysctl.conf <<EOF

# AlingAI Pro 5.0 优化参数
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 12582912 16777216
net.ipv4.tcp_wmem = 4096 12582912 16777216
net.core.netdev_max_backlog = 5000
net.ipv4.tcp_congestion_control = bbr
vm.swappiness = 10
fs.file-max = 65536
EOF

    sysctl -p
    
    # MySQL优化
    cat >> /etc/mysql/mysql.conf.d/99-alingai.cnf <<EOF
[mysqld]
# AlingAI Pro 5.0 MySQL优化
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 64M
max_connections = 200
EOF

    systemctl restart mysql
    
    success "系统性能优化完成"
}

# 启动所有服务
start_services() {
    log "启动所有服务..."
    
    systemctl start alingai-pro
    systemctl start alingai-security-monitor
    systemctl start alingai-ai-workers
    
    # 等待服务启动
    sleep 5
    
    # 检查服务状态
    if systemctl is-active --quiet alingai-pro; then
        success "AlingAI Pro主服务启动成功"
    else
        error "AlingAI Pro主服务启动失败"
    fi
    
    if systemctl is-active --quiet alingai-security-monitor; then
        success "安全监控服务启动成功"
    else
        warn "安全监控服务启动失败"
    fi
    
    if systemctl is-active --quiet alingai-ai-workers; then
        success "AI工作队列服务启动成功"
    else
        warn "AI工作队列服务启动失败"
    fi
}

# 执行部署后测试
run_deployment_tests() {
    log "执行部署后测试..."
    
    cd "$INSTALL_DIR"
    
    # 健康检查
    if curl -f -s http://localhost/health >/dev/null; then
        success "HTTP健康检查通过"
    else
        warn "HTTP健康检查失败"
    fi
    
    # 数据库连接测试
    if sudo -u "$SERVICE_USER" php artisan tinker --execute="DB::connection()->getPdo();" &>/dev/null; then
        success "数据库连接测试通过"
    else
        error "数据库连接测试失败"
    fi
    
    # Redis连接测试
    if redis-cli ping | grep -q PONG; then
        success "Redis连接测试通过"
    else
        warn "Redis连接测试失败"
    fi
    
    success "部署后测试完成"
}

# 主部署流程
main() {
    print_banner
    
    log "开始 AlingAI Pro 5.0 部署流程..."
    
    # 执行部署步骤
    detect_os
    check_requirements
    install_base_dependencies
    install_php
    install_nginx
    install_mysql
    install_redis
    install_nodejs
    create_system_user
    create_directories
    deploy_application
    configure_application
    configure_nginx
    configure_php_fpm
    setup_database
    configure_services
    configure_firewall
    configure_ssl
    create_monitoring_scripts
    create_backup_scripts
    optimize_system
    start_services
    run_deployment_tests
    
    echo -e "${GREEN}"
    cat << "EOF"
    ╔═══════════════════════════════════════════════════════════╗
    ║                    部署完成！                             ║
    ║                                                           ║
    ║  🚀 AlingAI Pro 5.0 政企融合智能办公系统                 ║
    ║  🧠 自我学习、自我修复、自我进化AI系统                    ║
    ║  🌐 全球威胁情报3D可视化系统                              ║
    ║  🛡️ 智能安全监控与防护系统                               ║
    ║                                                           ║
    ║  访问地址: https://localhost                              ║
    ║  管理后台: https://localhost/admin                        ║
    ║  3D威胁可视化: https://localhost/threat-visualization-3d  ║
    ║  AI进化控制台: https://localhost/ai-evolution-console     ║
    ║                                                           ║
    ║  系统凭据已保存到: /root/.alingai_credentials             ║
    ║  日志目录: /var/log/alingai                               ║
    ║  配置目录: /etc/alingai                                   ║
    ║                                                           ║
    ╚═══════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    log "AlingAI Pro 5.0 部署成功完成！"
}

# 脚本入口点
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    # 检查root权限
    if [[ $EUID -ne 0 ]]; then
        error "此脚本需要root权限运行"
    fi
    
    # 处理命令行参数
    while [[ $# -gt 0 ]]; do
        case $1 in
            --domain)
                DOMAIN_NAME="$2"
                shift 2
                ;;
            --ssl)
                SSL_ENABLED=true
                shift
                ;;
            --no-backup)
                BACKUP_ENABLED=false
                shift
                ;;
            --no-monitoring)
                MONITORING_ENABLED=false
                shift
                ;;
            -h|--help)
                echo "AlingAI Pro 5.0 Linux部署脚本"
                echo "用法: $0 [选项]"
                echo "选项:"
                echo "  --domain DOMAIN     设置域名"
                echo "  --ssl              启用SSL证书"
                echo "  --no-backup        禁用自动备份"
                echo "  --no-monitoring    禁用监控"
                echo "  -h, --help         显示帮助信息"
                exit 0
                ;;
            *)
                error "未知参数: $1"
                ;;
        esac
    done
    
    # 执行主部署流程
    main
fi
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# AI服务配置
DEEPSEEK_API_KEY=
OPENAI_API_KEY=

# 安全配置
APP_KEY=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -base64 64)

# 文件存储
FILESYSTEM_DISK=local
UPLOAD_MAX_SIZE=100M

# 监控配置
MONITORING_ENABLED=$MONITORING_ENABLED
BACKUP_ENABLED=$BACKUP_ENABLED
EOF
    
    # 设置配置文件权限
    chmod 600 .env
    chown "$SERVICE_USER:$SERVICE_USER" .env
    
    # 运行数据库迁移
    sudo -u "$SERVICE_USER" php bin/migrate.php
    
    success "应用配置完成"
}

# 配置Nginx
configure_nginx() {
    log "配置Nginx..."
    
    cat > /etc/nginx/sites-available/alingai-pro <<EOF
server {
    listen 80;
    server_name ${DOMAIN_NAME:-localhost};
    root $INSTALL_DIR/public;
    index index.php index.html;

    # 安全配置
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml application/json;

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP处理
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # 安全设置
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 4 32k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    # API路由
    location /api/ {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # 管理后台
    location /admin/ {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # 默认路由
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # 安全：禁止访问敏感文件
    location ~ /\. {
        deny all;
    }
    
    location ~ /(vendor|config|logs|storage|tests)/ {
        deny all;
    }

    # 日志
    access_log $LOG_DIR/nginx-access.log;
    error_log $LOG_DIR/nginx-error.log;
}
EOF
    
    # 启用站点
    if [[ $OS == "ubuntu" ]]; then
        ln -sf /etc/nginx/sites-available/alingai-pro /etc/nginx/sites-enabled/
        rm -f /etc/nginx/sites-enabled/default
    else
        # CentOS/RHEL
        cat > /etc/nginx/conf.d/alingai-pro.conf <<EOF
$(cat /etc/nginx/sites-available/alingai-pro)
EOF
    fi
    
    # 测试配置
    nginx -t
    systemctl reload nginx
    
    success "Nginx配置完成"
}

# 配置SSL证书
configure_ssl() {
    if [[ "$SSL_ENABLED" == "true" && -n "$DOMAIN_NAME" ]]; then
        log "配置SSL证书..."
        
        # 使用Let's Encrypt
        certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos --email admin@"$DOMAIN_NAME"
        
        # 设置自动续期
        echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
        
        success "SSL证书配置完成"
    fi
}

# 配置系统服务
configure_services() {
    log "配置系统服务..."
    
    # AlingAI 主服务
    cat > /etc/systemd/system/alingai-pro.service <<EOF
[Unit]
Description=AlingAI Pro 5.0 Application Server
After=network.target mysql.service redis.service
Wants=mysql.service redis.service

[Service]
Type=forking
User=$SERVICE_USER
Group=$SERVICE_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php $INSTALL_DIR/bin/server.php start
ExecStop=/usr/bin/php $INSTALL_DIR/bin/server.php stop
ExecReload=/usr/bin/php $INSTALL_DIR/bin/server.php reload
Restart=always
RestartSec=3
PIDFile=/var/run/alingai/alingai-pro.pid

[Install]
WantedBy=multi-user.target
EOF
    
    # WebSocket服务
    cat > /etc/systemd/system/alingai-websocket.service <<EOF
[Unit]
Description=AlingAI Pro WebSocket Server
After=network.target redis.service

[Service]
Type=simple
User=$SERVICE_USER
Group=$SERVICE_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php $INSTALL_DIR/bin/websocket.php
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF
    
    # 队列工作进程
    cat > /etc/systemd/system/alingai-queue.service <<EOF
[Unit]
Description=AlingAI Pro Queue Worker
After=network.target redis.service mysql.service

[Service]
Type=simple
User=$SERVICE_USER
Group=$SERVICE_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php $INSTALL_DIR/bin/queue.php work
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF
    
    # 重新加载systemd
    systemctl daemon-reload
    
    # 启用服务
    systemctl enable alingai-pro
    systemctl enable alingai-websocket
    systemctl enable alingai-queue
    
    success "系统服务配置完成"
}

# 配置监控
configure_monitoring() {
    if [[ "$MONITORING_ENABLED" == "true" ]]; then
        log "配置监控系统..."
        
        # 安装Prometheus Node Exporter
        cd /tmp
        wget https://github.com/prometheus/node_exporter/releases/latest/download/node_exporter-*linux-amd64.tar.gz
        tar xvf node_exporter-*linux-amd64.tar.gz
        cp node_exporter-*/node_exporter /usr/local/bin/
        
        # 创建node_exporter服务
        cat > /etc/systemd/system/node_exporter.service <<EOF
[Unit]
Description=Node Exporter
After=network.target

[Service]
Type=simple
User=nobody
ExecStart=/usr/local/bin/node_exporter
Restart=always

[Install]
WantedBy=multi-user.target
EOF
        
        systemctl enable node_exporter
        systemctl start node_exporter
        
        success "监控系统配置完成"
    fi
}

# 配置备份
configure_backup() {
    if [[ "$BACKUP_ENABLED" == "true" ]]; then
        log "配置备份系统..."
        
        mkdir -p /opt/alingai-backup
        
        # 创建备份脚本
        cat > /opt/alingai-backup/backup.sh <<'EOF'
#!/bin/bash

# AlingAI Pro 备份脚本
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/alingai-backup/$BACKUP_DATE"
MYSQL_ROOT_PASSWORD=$(grep MYSQL_ROOT_PASSWORD /root/.alingai_credentials | cut -d'=' -f2)

mkdir -p "$BACKUP_DIR"

# 备份数据库
mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" alingai_pro_5 > "$BACKUP_DIR/database.sql"

# 备份应用文件
tar -czf "$BACKUP_DIR/application.tar.gz" -C /opt/alingai-pro .

# 备份数据文件
tar -czf "$BACKUP_DIR/data.tar.gz" -C /var/lib/alingai .

# 删除7天前的备份
find /opt/alingai-backup -type d -mtime +7 -exec rm -rf {} \;

echo "备份完成: $BACKUP_DIR"
EOF
        
        chmod +x /opt/alingai-backup/backup.sh
        
        # 设置定时备份（每天凌晨2点）
        echo "0 2 * * * /opt/alingai-backup/backup.sh" | crontab -
        
        success "备份系统配置完成"
    fi
}

# 启动服务
start_services() {
    log "启动服务..."
    
    # 启动应用服务
    systemctl start alingai-pro
    systemctl start alingai-websocket
    systemctl start alingai-queue
    
    # 检查服务状态
    sleep 5
    
    if systemctl is-active --quiet alingai-pro; then
        success "AlingAI Pro 主服务启动成功"
    else
        error "AlingAI Pro 主服务启动失败"
    fi
    
    if systemctl is-active --quiet alingai-websocket; then
        success "WebSocket 服务启动成功"
    else
        warn "WebSocket 服务启动失败"
    fi
    
    if systemctl is-active --quiet alingai-queue; then
        success "队列服务启动成功"
    else
        warn "队列服务启动失败"
    fi
}

# 运行健康检查
health_check() {
    log "运行健康检查..."
    
    # 检查Web服务
    if curl -sSf http://localhost/ >/dev/null; then
        success "Web服务健康检查通过"
    else
        error "Web服务健康检查失败"
    fi
    
    # 检查数据库连接
    if mysql -u alingai -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1;" >/dev/null 2>&1; then
        success "数据库连接检查通过"
    else
        error "数据库连接检查失败"
    fi
    
    # 检查Redis连接
    if redis-cli ping | grep -q PONG; then
        success "Redis连接检查通过"
    else
        error "Redis连接检查失败"
    fi
    
    success "健康检查完成"
}

# 显示部署信息
show_deployment_info() {
    echo -e "${GREEN}"
    echo "╔══════════════════════════════════════════════════════════════╗"
    echo "║                                                              ║"
    echo "║            🎉 AlingAI Pro 5.0 部署成功！                    ║"
    echo "║                                                              ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    
    echo -e "${CYAN}部署信息:${NC}"
    echo "• 安装目录: $INSTALL_DIR"
    echo "• 数据目录: $DATA_DIR"
    echo "• 日志目录: $LOG_DIR"
    echo "• 配置目录: $CONFIG_DIR"
    echo "• 系统用户: $SERVICE_USER"
    
    echo -e "\n${CYAN}访问信息:${NC}"
    if [[ -n "$DOMAIN_NAME" ]]; then
        echo "• 网站地址: http${SSL_ENABLED:+s}://$DOMAIN_NAME"
        echo "• 管理后台: http${SSL_ENABLED:+s}://$DOMAIN_NAME/admin"
        echo "• API接口: http${SSL_ENABLED:+s}://$DOMAIN_NAME/api"
    else
        echo "• 网站地址: http://$(hostname -I | awk '{print $1}')"
        echo "• 管理后台: http://$(hostname -I | awk '{print $1}')/admin"
        echo "• API接口: http://$(hostname -I | awk '{print $1}')/api"
    fi
    
    echo -e "\n${CYAN}数据库信息:${NC}"
    echo "• 数据库名: $DB_NAME"
    echo "• 用户名: alingai"
    echo "• 密码: 请查看 /root/.alingai_credentials"
    
    echo -e "\n${CYAN}服务管理:${NC}"
    echo "• 启动服务: systemctl start alingai-pro"
    echo "• 停止服务: systemctl stop alingai-pro"
    echo "• 重启服务: systemctl restart alingai-pro"
    echo "• 查看状态: systemctl status alingai-pro"
    echo "• 查看日志: journalctl -u alingai-pro -f"
    
    echo -e "\n${CYAN}重要文件:${NC}"
    echo "• 应用配置: $INSTALL_DIR/.env"
    echo "• Nginx配置: /etc/nginx/sites-available/alingai-pro"
    echo "• 系统密码: /root/.alingai_credentials"
    
    if [[ "$BACKUP_ENABLED" == "true" ]]; then
        echo "• 备份脚本: /opt/alingai-backup/backup.sh"
    fi
    
    if [[ "$MONITORING_ENABLED" == "true" ]]; then
        echo "• 监控地址: http://$(hostname -I | awk '{print $1}'):9100/metrics"
    fi
    
    echo -e "\n${YELLOW}注意事项:${NC}"
    echo "• 请妥善保管 /root/.alingai_credentials 文件中的密码"
    echo "• 建议定期更新系统和应用"
    echo "• 查看日志以确保服务正常运行"
    
    if [[ -z "$DOMAIN_NAME" ]]; then
        echo "• 建议配置域名和SSL证书"
    fi
    
    echo -e "\n${GREEN}感谢使用 AlingAI Pro 5.0！${NC}"
}

# 主函数
main() {
    # 检查权限
    if [[ $EUID -ne 0 ]]; then
        error "请使用root权限运行此脚本"
    fi
    
    # 显示横幅
    print_banner
    
    # 读取用户输入
    read -p "请输入域名 (留空使用IP地址): " DOMAIN_NAME
    read -p "是否启用SSL证书? (y/N): " ssl_input
    [[ "$ssl_input" =~ ^[Yy]$ ]] && SSL_ENABLED=true
    
    read -p "是否启用备份? (Y/n): " backup_input
    [[ "$backup_input" =~ ^[Nn]$ ]] && BACKUP_ENABLED=false
    
    read -p "是否启用监控? (Y/n): " monitoring_input
    [[ "$monitoring_input" =~ ^[Nn]$ ]] && MONITORING_ENABLED=false
    
    # 开始部署
    echo -e "\n${BLUE}开始部署 AlingAI Pro 5.0...${NC}\n"
    
    detect_os
    check_requirements
    install_base_dependencies
    install_php
    install_nginx
    install_mysql
    install_redis
    install_nodejs
    create_system_user
    create_directories
    deploy_application
    configure_application
    configure_nginx
    configure_ssl
    configure_services
    configure_monitoring
    configure_backup
    start_services
    health_check
    show_deployment_info
    
    success "AlingAI Pro 5.0 部署完成！"
}

# 错误处理
trap 'error "部署过程中发生错误，请检查日志"' ERR

# 运行主函数
main "$@"
