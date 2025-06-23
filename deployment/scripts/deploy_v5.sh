#!/bin/bash

# AlingAI Pro 5.0 一键部署脚本
# 政企融合智能办公系统自动部署工具
# 支持 Ubuntu/CentOS/Debian 系统

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查系统
check_system() {
    log_info "检查系统环境..."
    
    # 检查操作系统
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if [ -f /etc/os-release ]; then
            . /etc/os-release
            OS=$ID
            VERSION=$VERSION_ID
        else
            log_error "无法检测操作系统版本"
            exit 1
        fi
    else
        log_error "不支持的操作系统: $OSTYPE"
        exit 1
    fi
    
    log_info "检测到操作系统: $OS $VERSION"
    
    # 检查是否为root用户
    if [[ $EUID -ne 0 ]]; then
        log_error "请以root用户身份运行此脚本"
        exit 1
    fi
    
    log_success "系统环境检查完成"
}

# 安装基础软件包
install_packages() {
    log_info "安装基础软件包..."
    
    case $OS in
        ubuntu|debian)
            apt-get update
            apt-get install -y curl wget git unzip software-properties-common \
                nginx mysql-server redis-server supervisor \
                php8.1 php8.1-fpm php8.1-mysql php8.1-redis php8.1-json \
                php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip \
                php8.1-gd php8.1-intl php8.1-bcmath
            ;;
        centos|rhel)
            yum update -y
            yum install -y epel-release
            yum install -y curl wget git unzip \
                nginx mysql-server redis supervisor \
                php php-fpm php-mysql php-redis php-json \
                php-curl php-mbstring php-xml php-zip \
                php-gd php-intl php-bcmath
            ;;
        *)
            log_error "不支持的操作系统: $OS"
            exit 1
            ;;
    esac
    
    log_success "基础软件包安装完成"
}

# 安装Composer
install_composer() {
    log_info "安装Composer..."
    
    if ! command -v composer &> /dev/null; then
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer
        log_success "Composer安装完成"
    else
        log_info "Composer已安装，跳过"
    fi
}

# 配置数据库
setup_database() {
    log_info "配置MySQL数据库..."
    
    # 启动MySQL服务
    systemctl start mysql
    systemctl enable mysql
    
    # 设置数据库密码
    read -s -p "请输入MySQL root密码: " MYSQL_PASSWORD
    echo
    
    # 创建数据库
    mysql -u root -p$MYSQL_PASSWORD -e "CREATE DATABASE IF NOT EXISTS alingai_pro_v5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -u root -p$MYSQL_PASSWORD -e "CREATE USER IF NOT EXISTS 'alingai'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD';"
    mysql -u root -p$MYSQL_PASSWORD -e "GRANT ALL PRIVILEGES ON alingai_pro_v5.* TO 'alingai'@'localhost';"
    mysql -u root -p$MYSQL_PASSWORD -e "FLUSH PRIVILEGES;"
    
    log_success "数据库配置完成"
}

# 配置Redis
setup_redis() {
    log_info "配置Redis..."
    
    systemctl start redis
    systemctl enable redis
    
    log_success "Redis配置完成"
}

# 配置Nginx
setup_nginx() {
    log_info "配置Nginx..."
    
    # 创建Nginx配置文件
    cat > /etc/nginx/sites-available/alingai-pro-v5 << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/alingai-pro-v5/public;
    index index_v5.php index.php index.html;

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # 文件上传大小限制
    client_max_body_size 100M;

    # 静态文件缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP处理
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # 主要路由
    location / {
        try_files $uri $uri/ /index_v5.php?$query_string;
    }

    # 禁止访问敏感文件
    location ~ /\. {
        deny all;
    }

    location ~ /(composer|package)\.json$ {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }
}
EOF

    # 启用站点
    ln -sf /etc/nginx/sites-available/alingai-pro-v5 /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # 测试配置并重启
    nginx -t
    systemctl restart nginx
    systemctl enable nginx
    
    log_success "Nginx配置完成"
}

# 部署应用代码
deploy_application() {
    log_info "部署应用代码..."
    
    # 创建目录
    mkdir -p /var/www/alingai-pro-v5
    cd /var/www/alingai-pro-v5
    
    # 如果当前目录已有代码，备份
    if [ -d "src" ]; then
        log_warning "检测到现有代码，创建备份..."
        tar -czf "backup_$(date +%Y%m%d_%H%M%S).tar.gz" .
    fi
    
    # 检查当前目录是否为项目目录
    if [ -f "composer.json" ] && [ -f "public/index_v5.php" ]; then
        log_info "在项目目录中运行，使用当前代码"
        PROJECT_DIR=$(pwd)
    else
        log_error "请在AlingAI Pro 5.0项目根目录中运行此脚本"
        exit 1
    fi
    
    # 如果不在/var/www/alingai-pro-v5目录，则复制文件
    if [ "$PROJECT_DIR" != "/var/www/alingai-pro-v5" ]; then
        log_info "复制项目文件到部署目录..."
        rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' \
            "$PROJECT_DIR/" /var/www/alingai-pro-v5/
        cd /var/www/alingai-pro-v5
    fi
    
    # 安装依赖
    log_info "安装Composer依赖..."
    composer install --optimize-autoloader --no-dev
    
    # 创建必要目录
    mkdir -p storage/logs storage/cache storage/sessions storage/uploads storage/backups
    
    # 设置权限
    chown -R www-data:www-data /var/www/alingai-pro-v5
    chmod -R 755 /var/www/alingai-pro-v5
    chmod -R 777 storage
    
    log_success "应用代码部署完成"
}

# 配置环境变量
setup_environment() {
    log_info "配置环境变量..."
    
    # 复制环境配置文件
    if [ ! -f .env ]; then
        cp .env.example .env
        log_info "已创建.env文件"
    fi
    
    # 生成应用密钥
    APP_KEY=$(openssl rand -base64 32)
    JWT_SECRET=$(openssl rand -base64 64)
    
    # 更新配置
    sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
    sed -i "s/APP_KEY=.*/APP_KEY=base64:$APP_KEY/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=alingai_pro_v5/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=alingai/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$MYSQL_PASSWORD/" .env
    sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
    
    log_success "环境变量配置完成"
}

# 运行数据库迁移
run_migrations() {
    log_info "运行数据库迁移..."
    
    php migrate.php migrate
    
    log_info "初始化示例数据..."
    php migrate.php seed
    
    log_success "数据库迁移完成"
}

# 配置系统服务
setup_services() {
    log_info "配置系统服务..."
    
    # 创建系统服务文件
    cat > /etc/systemd/system/alingai-queue.service << 'EOF'
[Unit]
Description=AlingAI Pro Queue Worker
After=redis.service mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/alingai-pro-v5
ExecStart=/usr/bin/php queue_worker.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

    # 启用服务
    systemctl daemon-reload
    systemctl enable alingai-queue
    systemctl start alingai-queue
    
    log_success "系统服务配置完成"
}

# 配置SSL证书
setup_ssl() {
    read -p "是否要配置SSL证书? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log_info "配置SSL证书..."
        
        # 安装Certbot
        case $OS in
            ubuntu|debian)
                apt-get install -y certbot python3-certbot-nginx
                ;;
            centos|rhel)
                yum install -y certbot python3-certbot-nginx
                ;;
        esac
        
        read -p "请输入域名: " DOMAIN
        
        # 获取SSL证书
        certbot --nginx -d $DOMAIN
        
        log_success "SSL证书配置完成"
    fi
}

# 配置防火墙
setup_firewall() {
    log_info "配置防火墙..."
    
    if command -v ufw &> /dev/null; then
        ufw --force enable
        ufw allow ssh
        ufw allow 80/tcp
        ufw allow 443/tcp
        log_success "UFW防火墙配置完成"
    elif command -v firewalld &> /dev/null; then
        systemctl start firewalld
        systemctl enable firewalld
        firewall-cmd --permanent --add-service=ssh
        firewall-cmd --permanent --add-service=http
        firewall-cmd --permanent --add-service=https
        firewall-cmd --reload
        log_success "Firewalld防火墙配置完成"
    else
        log_warning "未检测到防火墙，请手动配置"
    fi
}

# 性能优化
optimize_performance() {
    log_info "进行性能优化..."
    
    # PHP优化
    cat >> /etc/php/8.1/fpm/php.ini << 'EOF'

; AlingAI Pro 5.0 性能优化
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
post_max_size = 100M
upload_max_filesize = 100M
max_file_uploads = 20

; OPcache优化
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=1
EOF

    # 重启PHP-FPM
    systemctl restart php8.1-fpm
    
    log_success "性能优化完成"
}

# 创建监控脚本
create_monitoring() {
    log_info "创建监控脚本..."
    
    cat > /usr/local/bin/alingai-monitor << 'EOF'
#!/bin/bash

# AlingAI Pro 5.0 系统监控脚本

LOG_FILE="/var/log/alingai-monitor.log"
ALERT_EMAIL="admin@alingai.com"

# 检查服务状态
check_services() {
    services=("nginx" "mysql" "redis" "php8.1-fpm" "alingai-queue")
    
    for service in "${services[@]}"; do
        if ! systemctl is-active --quiet $service; then
            echo "$(date): 服务 $service 已停止" >> $LOG_FILE
            systemctl start $service
            echo "$(date): 尝试重启服务 $service" >> $LOG_FILE
        fi
    done
}

# 检查磁盘空间
check_disk_space() {
    THRESHOLD=90
    USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    
    if [ $USAGE -gt $THRESHOLD ]; then
        echo "$(date): 磁盘使用率过高: ${USAGE}%" >> $LOG_FILE
    fi
}

# 检查内存使用
check_memory() {
    THRESHOLD=90
    USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
    
    if [ $USAGE -gt $THRESHOLD ]; then
        echo "$(date): 内存使用率过高: ${USAGE}%" >> $LOG_FILE
    fi
}

# 执行检查
check_services
check_disk_space
check_memory

echo "$(date): 监控检查完成" >> $LOG_FILE
EOF

    chmod +x /usr/local/bin/alingai-monitor
    
    # 添加到crontab
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/alingai-monitor") | crontab -
    
    log_success "监控脚本创建完成"
}

# 创建备份脚本
create_backup() {
    log_info "创建备份脚本..."
    
    cat > /usr/local/bin/alingai-backup << 'EOF'
#!/bin/bash

# AlingAI Pro 5.0 备份脚本

BACKUP_DIR="/var/backups/alingai-pro-v5"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="alingai_pro_v5"
DB_USER="alingai"
APP_DIR="/var/www/alingai-pro-v5"

mkdir -p $BACKUP_DIR

# 数据库备份
mysqldump -u $DB_USER -p$MYSQL_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# 应用文件备份
tar -czf $BACKUP_DIR/application_$DATE.tar.gz -C $(dirname $APP_DIR) $(basename $APP_DIR) --exclude=vendor --exclude=node_modules

# 清理旧备份（保留30天）
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "$(date): 备份完成" >> /var/log/alingai-backup.log
EOF

    chmod +x /usr/local/bin/alingai-backup
    
    # 添加到每日备份
    (crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/alingai-backup") | crontab -
    
    log_success "备份脚本创建完成"
}

# 系统健康检查
health_check() {
    log_info "执行系统健康检查..."
    
    # 检查HTTP响应
    if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
        log_success "HTTP服务正常"
    else
        log_error "HTTP服务异常"
    fi
    
    # 检查数据库连接
    if mysql -u alingai -p$MYSQL_PASSWORD -e "SELECT 1" alingai_pro_v5 &>/dev/null; then
        log_success "数据库连接正常"
    else
        log_error "数据库连接异常"
    fi
    
    # 检查Redis连接
    if redis-cli ping | grep -q "PONG"; then
        log_success "Redis连接正常"
    else
        log_error "Redis连接异常"
    fi
    
    log_success "系统健康检查完成"
}

# 显示部署结果
show_results() {
    echo
    echo "=========================================="
    log_success "AlingAI Pro 5.0 部署完成！"
    echo "=========================================="
    echo
    echo "系统信息："
    echo "  - 应用目录: /var/www/alingai-pro-v5"
    echo "  - 日志目录: /var/www/alingai-pro-v5/storage/logs"
    echo "  - 备份目录: /var/backups/alingai-pro-v5"
    echo "  - 配置文件: /var/www/alingai-pro-v5/.env"
    echo
    echo "服务管理："
    echo "  - 查看应用状态: systemctl status alingai-queue"
    echo "  - 重启应用: systemctl restart alingai-queue"
    echo "  - 查看日志: tail -f /var/www/alingai-pro-v5/storage/logs/alingai-pro-v5.log"
    echo
    echo "数据库信息："
    echo "  - 数据库名: alingai_pro_v5"
    echo "  - 用户名: alingai"
    echo "  - 密码: [已设置]"
    echo
    echo "管理命令："
    echo "  - 手动备份: /usr/local/bin/alingai-backup"
    echo "  - 系统监控: /usr/local/bin/alingai-monitor"
    echo "  - 数据库迁移: cd /var/www/alingai-pro-v5 && php migrate.php"
    echo
    echo "访问地址："
    echo "  - HTTP: http://$(hostname -I | awk '{print $1}')"
    if [ -n "$DOMAIN" ]; then
        echo "  - HTTPS: https://$DOMAIN"
    fi
    echo
    log_info "请确保防火墙已开放80和443端口"
    log_info "建议定期检查系统日志和备份"
    echo
}

# 主函数
main() {
    echo "=========================================="
    echo "AlingAI Pro 5.0 一键部署脚本"
    echo "政企融合智能办公系统"
    echo "=========================================="
    echo
    
    # 确认部署
    read -p "确定要开始部署吗? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_info "部署已取消"
        exit 0
    fi
    
    # 执行部署步骤
    check_system
    install_packages
    install_composer
    setup_database
    setup_redis
    setup_nginx
    deploy_application
    setup_environment
    run_migrations
    setup_services
    setup_ssl
    setup_firewall
    optimize_performance
    create_monitoring
    create_backup
    health_check
    show_results
    
    log_success "部署完成！"
}

# 执行主函数
main "$@"
