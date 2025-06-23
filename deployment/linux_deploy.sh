#!/bin/bash
# AlingAi Pro Linux 一键部署脚本
# 支持PHP8.1+、MySQL8.0+、Nginx1.20+
# 创建时间: 2025-06-07

set -e  # 遇到错误立即退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 配置变量
APP_NAME="AlingAi Pro"
APP_VERSION="2.0.0"
DEPLOY_DIR="/var/www/alingai-pro"
NGINX_CONF="/etc/nginx/sites-available/alingai-pro"
PHP_VERSION="8.1"
MYSQL_VERSION="8.0"
NGINX_VERSION="1.20"

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

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# 检查是否为root用户
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本需要root权限运行"
        log_info "请使用: sudo $0"
        exit 1
    fi
}

# 系统检测
detect_system() {
    log_step "检测系统信息..."
    
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$NAME
        VER=$VERSION_ID
        log_info "检测到系统: $OS $VER"
    else
        log_error "无法检测系统版本"
        exit 1
    fi
    
    # 检查系统架构
    ARCH=$(uname -m)
    log_info "系统架构: $ARCH"
    
    if [[ "$ARCH" != "x86_64" ]]; then
        log_warn "推荐使用x86_64架构，当前架构可能不被完全支持"
    fi
}

# 更新系统包
update_system() {
    log_step "更新系统包..."
    
    if command -v apt-get &> /dev/null; then
        apt-get update -y
        apt-get upgrade -y
        log_info "Ubuntu/Debian 系统包更新完成"
    elif command -v yum &> /dev/null; then
        yum update -y
        log_info "CentOS/RHEL 系统包更新完成"
    elif command -v dnf &> /dev/null; then
        dnf update -y
        log_info "Fedora 系统包更新完成"
    else
        log_error "不支持的包管理器"
        exit 1
    fi
}

# 安装PHP 8.1+
install_php() {
    log_step "安装PHP ${PHP_VERSION}..."
    
    if command -v apt-get &> /dev/null; then
        # Ubuntu/Debian
        apt-get install -y software-properties-common
        add-apt-repository ppa:ondrej/php -y
        apt-get update -y
        
        apt-get install -y \
            php${PHP_VERSION} \
            php${PHP_VERSION}-fpm \
            php${PHP_VERSION}-mysql \
            php${PHP_VERSION}-curl \
            php${PHP_VERSION}-json \
            php${PHP_VERSION}-mbstring \
            php${PHP_VERSION}-xml \
            php${PHP_VERSION}-zip \
            php${PHP_VERSION}-redis \
            php${PHP_VERSION}-bcmath \
            php${PHP_VERSION}-gd \
            php${PHP_VERSION}-intl \
            php${PHP_VERSION}-opcache
            
    elif command -v yum &> /dev/null; then
        # CentOS/RHEL
        yum install -y epel-release
        yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
        yum module enable php:remi-${PHP_VERSION} -y
        
        yum install -y \
            php \
            php-fpm \
            php-mysql \
            php-curl \
            php-json \
            php-mbstring \
            php-xml \
            php-zip \
            php-redis \
            php-bcmath \
            php-gd \
            php-intl \
            php-opcache
    fi
    
    # 验证PHP安装
    PHP_INSTALLED_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    if [[ "$PHP_INSTALLED_VERSION" == "$PHP_VERSION" ]]; then
        log_info "PHP ${PHP_VERSION} 安装成功"
    else
        log_error "PHP 安装失败或版本不匹配"
        exit 1
    fi
}

# 安装MySQL 8.0+
install_mysql() {
    log_step "安装MySQL ${MYSQL_VERSION}..."
    
    if command -v apt-get &> /dev/null; then
        # Ubuntu/Debian
        wget https://dev.mysql.com/get/mysql-apt-config_0.8.22-1_all.deb
        dpkg -i mysql-apt-config_0.8.22-1_all.deb
        apt-get update -y
        apt-get install -y mysql-server
        
    elif command -v yum &> /dev/null; then
        # CentOS/RHEL
        yum install -y https://dev.mysql.com/get/mysql80-community-release-el8-1.noarch.rpm
        yum install -y mysql-community-server
    fi
    
    # 启动MySQL服务
    systemctl start mysql
    systemctl enable mysql
    
    log_info "MySQL ${MYSQL_VERSION} 安装完成"
    log_warn "请手动运行: mysql_secure_installation 来配置MySQL安全设置"
}

# 安装Nginx 1.20+
install_nginx() {
    log_step "安装Nginx ${NGINX_VERSION}..."
    
    if command -v apt-get &> /dev/null; then
        # Ubuntu/Debian
        apt-get install -y nginx
        
    elif command -v yum &> /dev/null; then
        # CentOS/RHEL
        yum install -y nginx
    fi
    
    # 启动Nginx服务
    systemctl start nginx
    systemctl enable nginx
    
    # 验证Nginx安装
    NGINX_INSTALLED_VERSION=$(nginx -v 2>&1 | cut -d'/' -f2 | cut -d' ' -f1)
    log_info "Nginx ${NGINX_INSTALLED_VERSION} 安装成功"
}

# 安装Composer
install_composer() {
    log_step "安装Composer..."
    
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    
    log_info "Composer 安装完成"
}

# 安装Redis
install_redis() {
    log_step "安装Redis..."
    
    if command -v apt-get &> /dev/null; then
        apt-get install -y redis-server
    elif command -v yum &> /dev/null; then
        yum install -y redis
    fi
    
    systemctl start redis
    systemctl enable redis
    
    log_info "Redis 安装完成"
}

# 部署应用代码
deploy_application() {
    log_step "部署应用代码..."
    
    # 创建部署目录
    mkdir -p $DEPLOY_DIR
    cd $DEPLOY_DIR
    
    # 假设代码已经上传到服务器，这里进行配置
    # 在实际部署中，您可能需要从Git仓库克隆代码
    
    # 设置权限
    chown -R www-data:www-data $DEPLOY_DIR
    chmod -R 755 $DEPLOY_DIR
    chmod -R 777 $DEPLOY_DIR/storage
    chmod -R 777 $DEPLOY_DIR/public/uploads
    
    # 安装依赖
    if [ -f composer.json ]; then
        log_info "安装Composer依赖..."
        composer install --no-dev --optimize-autoloader
    fi
    
    # 复制环境配置
    if [ -f .env.production ]; then
        cp .env.production .env
        log_info "生产环境配置已应用"
    fi
    
    log_info "应用代码部署完成"
}

# 配置Nginx
configure_nginx() {
    log_step "配置Nginx..."
    
    cat > $NGINX_CONF << EOF
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root $DEPLOY_DIR/public;
    index index.php index.html index.htm;

    # 日志配置
    access_log /var/log/nginx/alingai-pro-access.log;
    error_log /var/log/nginx/alingai-pro-error.log;

    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # 主要位置块
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP处理
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问敏感文件
    location ~ /\. {
        deny all;
    }

    location ~ ^/(vendor|storage|config)/ {
        deny all;
    }
}
EOF

    # 启用站点
    ln -sf $NGINX_CONF /etc/nginx/sites-enabled/
    
    # 移除默认站点
    rm -f /etc/nginx/sites-enabled/default
    
    # 测试Nginx配置
    nginx -t
    
    if [ $? -eq 0 ]; then
        systemctl reload nginx
        log_info "Nginx配置完成"
    else
        log_error "Nginx配置测试失败"
        exit 1
    fi
}

# 配置PHP-FPM
configure_php_fpm() {
    log_step "配置PHP-FPM..."
    
    # 优化PHP-FPM配置
    PHP_FPM_CONF="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
    
    # 备份原配置
    cp $PHP_FPM_CONF $PHP_FPM_CONF.backup
    
    # 更新配置
    sed -i 's/pm.max_children = 5/pm.max_children = 50/' $PHP_FPM_CONF
    sed -i 's/pm.start_servers = 2/pm.start_servers = 10/' $PHP_FPM_CONF
    sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 5/' $PHP_FPM_CONF
    sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 15/' $PHP_FPM_CONF
    
    # 重启PHP-FPM
    systemctl restart php${PHP_VERSION}-fpm
    systemctl enable php${PHP_VERSION}-fpm
    
    log_info "PHP-FPM配置完成"
}

# 配置数据库
setup_database() {
    log_step "设置数据库..."
    
    # 生成随机密码
    DB_PASSWORD=$(openssl rand -base64 32)
    
    # 创建数据库和用户
    mysql -e "CREATE DATABASE IF NOT EXISTS alingai_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS 'alingai_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
    mysql -e "GRANT ALL PRIVILEGES ON alingai_pro.* TO 'alingai_user'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    
    log_info "数据库设置完成"
    log_info "数据库密码: $DB_PASSWORD"
    log_warn "请保存数据库密码并更新.env文件"
}

# 设置防火墙
setup_firewall() {
    log_step "配置防火墙..."
    
    if command -v ufw &> /dev/null; then
        # Ubuntu/Debian UFW
        ufw --force enable
        ufw allow ssh
        ufw allow 'Nginx Full'
        ufw allow 3306  # MySQL
        log_info "UFW防火墙配置完成"
        
    elif command -v firewall-cmd &> /dev/null; then
        # CentOS/RHEL firewalld
        systemctl start firewalld
        systemctl enable firewalld
        firewall-cmd --permanent --add-service=ssh
        firewall-cmd --permanent --add-service=http
        firewall-cmd --permanent --add-service=https
        firewall-cmd --permanent --add-service=mysql
        firewall-cmd --reload
        log_info "Firewalld防火墙配置完成"
    fi
}

# 创建SSL证书（Let's Encrypt）
setup_ssl() {
    log_step "设置SSL证书（可选）..."
    
    read -p "是否要配置Let's Encrypt SSL证书？(y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if command -v apt-get &> /dev/null; then
            apt-get install -y certbot python3-certbot-nginx
        elif command -v yum &> /dev/null; then
            yum install -y certbot python3-certbot-nginx
        fi
        
        read -p "请输入您的域名: " DOMAIN
        certbot --nginx -d $DOMAIN
        
        log_info "SSL证书配置完成"
    fi
}

# 系统优化
optimize_system() {
    log_step "系统优化..."
    
    # 增加文件描述符限制
    cat >> /etc/security/limits.conf << EOF
* soft nofile 65536
* hard nofile 65536
EOF

    # 内核参数优化
    cat >> /etc/sysctl.conf << EOF
# AlingAi Pro 优化参数
net.core.somaxconn = 65535
net.ipv4.tcp_max_tw_buckets = 6000
net.ipv4.ip_local_port_range = 1024 65000
net.ipv4.tcp_rmem = 4096 65536 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.core.rmem_default = 262144
net.core.rmem_max = 16777216
net.core.wmem_default = 262144
net.core.wmem_max = 16777216
EOF

    sysctl -p
    
    log_info "系统优化完成"
}

# 创建监控脚本
create_monitoring() {
    log_step "创建监控脚本..."
    
    cat > /usr/local/bin/alingai-monitor.sh << 'EOF'
#!/bin/bash
# AlingAi Pro 监控脚本

LOG_FILE="/var/log/alingai-monitor.log"
DEPLOY_DIR="/var/www/alingai-pro"

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> $LOG_FILE
}

# 检查服务状态
check_services() {
    services=("nginx" "php8.1-fpm" "mysql" "redis")
    
    for service in "${services[@]}"; do
        if systemctl is-active --quiet $service; then
            log_message "✓ $service 运行正常"
        else
            log_message "✗ $service 服务异常，尝试重启..."
            systemctl restart $service
        fi
    done
}

# 检查磁盘空间
check_disk_space() {
    usage=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
    if [ $usage -gt 80 ]; then
        log_message "⚠ 磁盘使用率过高: ${usage}%"
    fi
}

# 检查内存使用
check_memory() {
    mem_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
    log_message "内存使用率: ${mem_usage}%"
}

# 执行检查
check_services
check_disk_space
check_memory
EOF

    chmod +x /usr/local/bin/alingai-monitor.sh
    
    # 添加到cron
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/alingai-monitor.sh") | crontab -
    
    log_info "监控脚本创建完成"
}

# 创建备份脚本
create_backup_script() {
    log_step "创建备份脚本..."
    
    cat > /usr/local/bin/alingai-backup.sh << 'EOF'
#!/bin/bash
# AlingAi Pro 备份脚本

BACKUP_DIR="/backup/alingai-pro"
DEPLOY_DIR="/var/www/alingai-pro"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u alingai_user -p alingai_pro > $BACKUP_DIR/database_$DATE.sql

# 备份应用文件
tar -czf $BACKUP_DIR/application_$DATE.tar.gz -C $DEPLOY_DIR .

# 清理30天前的备份
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "备份完成: $DATE"
EOF

    chmod +x /usr/local/bin/alingai-backup.sh
    
    # 添加到cron（每天凌晨2点备份）
    (crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/alingai-backup.sh") | crontab -
    
    log_info "备份脚本创建完成"
}

# 主函数
main() {
    echo -e "${BLUE}"
    cat << "EOF"
    ╔═══════════════════════════════════════════════════════════╗
    ║                AlingAi Pro Linux 一键部署                ║
    ║                     v2.0.0 - 2025                        ║
    ║         PHP8.1+ | MySQL8.0+ | Nginx1.20+ | Redis        ║
    ╚═══════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    log_info "开始 $APP_NAME 部署流程..."
    
    # 执行部署步骤
    check_root
    detect_system
    update_system
    install_php
    install_mysql
    install_nginx
    install_composer
    install_redis
    deploy_application
    configure_nginx
    configure_php_fpm
    setup_database
    setup_firewall
    optimize_system
    create_monitoring
    create_backup_script
    setup_ssl
    
    echo -e "${GREEN}"
    cat << "EOF"
    ╔═══════════════════════════════════════════════════════════╗
    ║                    部署完成！                             ║
    ║                                                           ║
    ║  应用目录: /var/www/alingai-pro                           ║
    ║  Nginx配置: /etc/nginx/sites-available/alingai-pro       ║
    ║  监控脚本: /usr/local/bin/alingai-monitor.sh              ║
    ║  备份脚本: /usr/local/bin/alingai-backup.sh               ║
    ║                                                           ║
    ║  下一步:                                                  ║
    ║  1. 配置域名DNS                                           ║
    ║  2. 更新.env文件中的数据库密码                            ║
    ║  3. 运行数据库迁移                                        ║
    ║  4. 测试应用功能                                          ║
    ╚═══════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    log_info "$APP_NAME 部署成功！"
}

# 错误处理
trap 'log_error "部署过程中发生错误，请检查日志"; exit 1' ERR

# 运行主函数
main "$@"
