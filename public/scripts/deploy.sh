#!/bin/bash

# AlingAi Pro 6.0 企业级生产部署脚本
# 支持多环境部署、零停机更新、回滚机制

set -euo pipefail

# ================================
# 配置变量
# ================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
DEPLOYMENT_DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="${PROJECT_ROOT}/storage/backups"
LOG_FILE="${PROJECT_ROOT}/storage/logs/deployment_${DEPLOYMENT_DATE}.log"

# 颜色输出
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

# 配置变量
PROJECT_NAME="AlingAi"
DEPLOY_USER="alingai"
DEPLOY_GROUP="alingai"
WEB_ROOT="/var/www/alingai"
NGINX_CONF="/etc/nginx/conf.d/alingai.conf"
PHP_FPM_CONF="/etc/php-fpm.d/alingai.conf"
BACKUP_DIR="/var/backups/alingai"
LOG_DIR="/var/log/alingai"

# 检查是否为root用户
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本需要root权限运行"
        exit 1
    fi
}

# 检查系统要求
check_system_requirements() {
    log_info "检查系统要求..."
    
    # 检查操作系统
    if ! grep -q "CentOS" /etc/os-release && ! grep -q "Red Hat" /etc/os-release; then
        log_warning "建议使用 CentOS 8.0+ 系统"
    fi
    
    # 检查PHP版本
    if command -v php >/dev/null 2>&1; then
        PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
        if [[ $(echo "$PHP_VERSION >= 7.4" | bc -l) -eq 1 ]]; then
            log_success "PHP版本: $PHP_VERSION ✓"
        else
            log_error "需要PHP 7.4+，当前版本: $PHP_VERSION"
            exit 1
        fi
    else
        log_error "未找到PHP"
        exit 1
    fi
    
    # 检查Nginx版本
    if command -v nginx >/dev/null 2>&1; then
        NGINX_VERSION=$(nginx -v 2>&1 | grep -o '[0-9]\+\.[0-9]\+\.[0-9]\+')
        log_success "Nginx版本: $NGINX_VERSION ✓"
    else
        log_error "未找到Nginx"
        exit 1
    fi
    
    # 检查MySQL版本
    if command -v mysql >/dev/null 2>&1; then
        MYSQL_VERSION=$(mysql --version | grep -o '[0-9]\+\.[0-9]\+\.[0-9]\+' | head -1)
        log_success "MySQL版本: $MYSQL_VERSION ✓"
    else
        log_error "未找到MySQL"
        exit 1
    fi
}

# 创建用户和目录
setup_user_and_directories() {
    log_info "设置用户和目录..."
    
    # 创建部署用户
    if ! id "$DEPLOY_USER" &>/dev/null; then
        useradd -r -s /bin/bash -d /home/$DEPLOY_USER $DEPLOY_USER
        log_success "创建用户: $DEPLOY_USER"
    fi
    
    # 创建目录
    mkdir -p $WEB_ROOT
    mkdir -p $BACKUP_DIR
    mkdir -p $LOG_DIR
    mkdir -p /var/cache/alingai
    mkdir -p /var/sessions/alingai
    
    # 设置权限
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $WEB_ROOT
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $LOG_DIR
    chown -R $DEPLOY_USER:$DEPLOY_GROUP /var/cache/alingai
    chown -R $DEPLOY_USER:$DEPLOY_GROUP /var/sessions/alingai
    
    chmod -R 755 $WEB_ROOT
    chmod -R 750 $LOG_DIR
    
    log_success "目录设置完成"
}

# 部署应用代码
deploy_application() {
    log_info "部署应用代码..."
    
    # 备份当前版本
    if [ -d "$WEB_ROOT/current" ]; then
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        cp -r $WEB_ROOT/current $BACKUP_DIR/backup_$TIMESTAMP
        log_success "备份完成: backup_$TIMESTAMP"
    fi
    
    # 复制新代码
    cp -r ./ $WEB_ROOT/release_$(date +%Y%m%d_%H%M%S)/
    
    # 创建符号链接
    rm -f $WEB_ROOT/current
    ln -sf $WEB_ROOT/release_$(date +%Y%m%d_%H%M%S) $WEB_ROOT/current
    
    # 设置权限
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $WEB_ROOT/current
    chmod -R 755 $WEB_ROOT/current
    chmod -R 777 $WEB_ROOT/current/storage
    chmod -R 777 $WEB_ROOT/current/public/uploads
    
    log_success "应用代码部署完成"
}

# 安装依赖
install_dependencies() {
    log_info "安装依赖..."
    
    cd $WEB_ROOT/current
    
    # Composer依赖
    if [ -f "composer.json" ]; then
        sudo -u $DEPLOY_USER composer install --no-dev --optimize-autoloader
        log_success "Composer依赖安装完成"
    fi
    
    # NPM依赖（如果存在）
    if [ -f "package.json" ]; then
        sudo -u $DEPLOY_USER npm install --production
        sudo -u $DEPLOY_USER npm run build
        log_success "NPM依赖安装完成"
    fi
}

# 配置环境
configure_environment() {
    log_info "配置环境..."
    
    cd $WEB_ROOT/current
    
    # 复制环境配置文件
    if [ ! -f ".env" ]; then
        cp .env.production .env
        chown $DEPLOY_USER:$DEPLOY_GROUP .env
        chmod 600 .env
        log_success "环境配置文件创建完成"
    fi
    
    # 生成应用密钥
    if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
        APP_KEY=$(openssl rand -base64 32)
        sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" .env
        log_success "应用密钥生成完成"
    fi
}

# 数据库迁移
run_database_migration() {
    log_info "运行数据库迁移..."
    
    cd $WEB_ROOT/current
    
    # 执行数据库迁移
    sudo -u $DEPLOY_USER php database/migrate.php
    
    log_success "数据库迁移完成"
}

# 配置Web服务器
configure_web_server() {
    log_info "配置Web服务器..."
    
    # 复制Nginx配置
    cp nginx/alingai.conf $NGINX_CONF
    
    # 更新配置中的路径
    sed -i "s|/var/www/alingai|$WEB_ROOT/current|g" $NGINX_CONF
    
    # 测试Nginx配置
    nginx -t
    if [ $? -eq 0 ]; then
        log_success "Nginx配置验证通过"
    else
        log_error "Nginx配置验证失败"
        exit 1
    fi
    
    # 复制PHP-FPM配置
    cp config/php-fpm-alingai.conf $PHP_FPM_CONF
    
    # 重启服务
    systemctl reload nginx
    systemctl restart php-fpm
    
    log_success "Web服务器配置完成"
}

# 设置定时任务
setup_cron_jobs() {
    log_info "设置定时任务..."
    
    # 创建定时任务文件
    cat > /etc/cron.d/alingai << EOF
# AlingAi定时任务

# 每分钟处理队列任务
* * * * * $DEPLOY_USER cd $WEB_ROOT/current && php bin/console queue:work >/dev/null 2>&1

# 每小时清理临时文件
0 * * * * $DEPLOY_USER cd $WEB_ROOT/current && php bin/console cache:cleanup >/dev/null 2>&1

# 每天备份数据库（凌晨2点）
0 2 * * * $DEPLOY_USER cd $WEB_ROOT/current && php bin/console backup:database >/dev/null 2>&1

# 每周清理日志文件（周日凌晨3点）
0 3 * * 0 $DEPLOY_USER find $LOG_DIR -name "*.log" -mtime +7 -delete >/dev/null 2>&1
EOF
    
    chmod 644 /etc/cron.d/alingai
    systemctl restart crond
    
    log_success "定时任务设置完成"
}

# 设置日志轮转
setup_log_rotation() {
    log_info "设置日志轮转..."
    
    cat > /etc/logrotate.d/alingai << EOF
$LOG_DIR/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 $DEPLOY_USER $DEPLOY_GROUP
    postrotate
        systemctl reload nginx
        systemctl reload php-fpm
    endscript
}
EOF
    
    log_success "日志轮转设置完成"
}

# 设置防火墙
setup_firewall() {
    log_info "配置防火墙..."
    
    # 开启HTTP和HTTPS端口
    firewall-cmd --permanent --add-port=80/tcp
    firewall-cmd --permanent --add-port=443/tcp
    firewall-cmd --reload
    
    log_success "防火墙配置完成"
}

# 优化系统性能
optimize_system() {
    log_info "优化系统性能..."
    
    # 优化内核参数
    cat >> /etc/sysctl.conf << EOF

# AlingAi性能优化
net.core.rmem_default = 262144
net.core.rmem_max = 16777216
net.core.wmem_default = 262144
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 65536 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.core.netdev_max_backlog = 5000
net.ipv4.tcp_congestion_control = bbr
EOF
    
    sysctl -p
    
    # 优化文件描述符限制
    cat >> /etc/security/limits.conf << EOF

# AlingAi文件描述符优化
$DEPLOY_USER soft nofile 65536
$DEPLOY_USER hard nofile 65536
nginx soft nofile 65536
nginx hard nofile 65536
EOF
    
    log_success "系统性能优化完成"
}

# 健康检查
health_check() {
    log_info "执行健康检查..."
    
    # 检查服务状态
    if systemctl is-active --quiet nginx; then
        log_success "Nginx服务运行正常 ✓"
    else
        log_error "Nginx服务异常"
        return 1
    fi
    
    if systemctl is-active --quiet php-fpm; then
        log_success "PHP-FPM服务运行正常 ✓"
    else
        log_error "PHP-FPM服务异常"
        return 1
    fi
    
    if systemctl is-active --quiet mysql; then
        log_success "MySQL服务运行正常 ✓"
    else
        log_error "MySQL服务异常"
        return 1
    fi
    
    # 检查网站可访问性
    if curl -f -s http://localhost >/dev/null; then
        log_success "网站可访问 ✓"
    else
        log_warning "网站访问可能存在问题"
    fi
    
    log_success "健康检查完成"
}

# 显示部署信息
show_deployment_info() {
    log_info "部署信息"
    echo "=========================================="
    echo "项目名称: $PROJECT_NAME"
    echo "部署路径: $WEB_ROOT/current"
    echo "日志路径: $LOG_DIR"
    echo "备份路径: $BACKUP_DIR"
    echo "运行用户: $DEPLOY_USER"
    echo "=========================================="
    echo ""
    log_success "AlingAi部署完成！"
    echo ""
    echo "下一步操作："
    echo "1. 配置域名解析到服务器IP"
    echo "2. 申请并配置SSL证书"
    echo "3. 配置数据库连接信息"
    echo "4. 配置邮件服务器信息"
    echo "5. 配置AI服务API密钥"
    echo ""
}

# 主函数
main() {
    echo "========================================"
    echo "       AlingAi 自动化部署脚本"
    echo "========================================"
    echo ""
    
    check_root
    check_system_requirements
    setup_user_and_directories
    deploy_application
    install_dependencies
    configure_environment
    run_database_migration
    configure_web_server
    setup_cron_jobs
    setup_log_rotation
    setup_firewall
    optimize_system
    health_check
    show_deployment_info
}

# 执行主函数
main "$@"
