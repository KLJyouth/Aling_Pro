#!/bin/bash
# AlingAi Pro 生产环境启动脚本
# 适用于 CentOS 8.0+ / RHEL 8.0+ / Ubuntu 20.04+

set -e

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目配置
PROJECT_NAME="AlingAi Pro"
PROJECT_DIR="/var/www/alingai_pro"
LOG_DIR="/var/log/alingai_pro"
USER="www-data"
GROUP="www-data"

# 函数定义
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_header() {
    echo -e "${BLUE}================================================================${NC}"
    echo -e "${BLUE}    $1${NC}"
    echo -e "${BLUE}================================================================${NC}"
}

# 检查运行权限
check_permissions() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本需要root权限运行"
        exit 1
    fi
}

# 检查系统要求
check_system_requirements() {
    log_header "检查系统要求"
    
    # 检查操作系统
    if [[ -f /etc/redhat-release ]]; then
        OS="rhel"
        log_info "检测到 Red Hat/CentOS 系统"
    elif [[ -f /etc/debian_version ]]; then
        OS="debian"
        log_info "检测到 Debian/Ubuntu 系统"
    else
        log_error "不支持的操作系统"
        exit 1
    fi
    
    # 检查PHP版本
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -c1-3)
        if (( $(echo "$PHP_VERSION >= 7.4" | bc -l) )); then
            log_info "PHP版本: $PHP_VERSION ✓"
        else
            log_error "PHP版本过低，需要7.4+，当前: $PHP_VERSION"
            exit 1
        fi
    else
        log_error "PHP未安装"
        exit 1
    fi
    
    # 检查MySQL
    if command -v mysql &> /dev/null; then
        MYSQL_VERSION=$(mysql --version | awk '{print $5}' | cut -d',' -f1)
        log_info "MySQL版本: $MYSQL_VERSION ✓"
    else
        log_error "MySQL未安装"
        exit 1
    fi
    
    # 检查Nginx
    if command -v nginx &> /dev/null; then
        NGINX_VERSION=$(nginx -v 2>&1 | cut -d'/' -f2)
        log_info "Nginx版本: $NGINX_VERSION ✓"
    else
        log_error "Nginx未安装"
        exit 1
    fi
}

# 安装依赖包
install_dependencies() {
    log_header "安装系统依赖"
    
    if [[ "$OS" == "rhel" ]]; then
        # CentOS/RHEL
        dnf update -y
        dnf install -y epel-release
        dnf install -y php php-cli php-fpm php-mysql php-redis php-gd php-mbstring \
                       php-curl php-xml php-zip php-intl php-opcache php-json \
                       composer redis supervisor
    elif [[ "$OS" == "debian" ]]; then
        # Ubuntu/Debian
        apt update -y
        apt install -y php php-cli php-fpm php-mysql php-redis php-gd php-mbstring \
                       php-curl php-xml php-zip php-intl php-opcache php-json \
                       composer redis-server supervisor
    fi
    
    log_info "系统依赖安装完成"
}

# 配置系统服务
configure_services() {
    log_header "配置系统服务"
    
    # 启用服务
    systemctl enable nginx
    systemctl enable mysql
    systemctl enable redis
    systemctl enable php-fpm
    systemctl enable supervisor
    
    # 配置PHP-FPM
    if [[ "$OS" == "rhel" ]]; then
        PHP_FPM_CONF="/etc/php-fpm.d/www.conf"
    else
        PHP_FPM_CONF="/etc/php/$(php -v | head -n1 | cut -d' ' -f2 | cut -c1-3)/fpm/pool.d/www.conf"
    fi
    
    # 备份原配置
    cp "$PHP_FPM_CONF" "$PHP_FPM_CONF.backup"
    
    # 优化PHP-FPM配置
    sed -i "s/^user = .*/user = $USER/" "$PHP_FPM_CONF"
    sed -i "s/^group = .*/group = $GROUP/" "$PHP_FPM_CONF"
    sed -i "s/^pm.max_children = .*/pm.max_children = 50/" "$PHP_FPM_CONF"
    sed -i "s/^pm.start_servers = .*/pm.start_servers = 5/" "$PHP_FPM_CONF"
    sed -i "s/^pm.min_spare_servers = .*/pm.min_spare_servers = 5/" "$PHP_FPM_CONF"
    sed -i "s/^pm.max_spare_servers = .*/pm.max_spare_servers = 35/" "$PHP_FPM_CONF"
    
    log_info "系统服务配置完成"
}

# 部署项目文件
deploy_project() {
    log_header "部署项目文件"
    
    # 创建项目目录
    mkdir -p "$PROJECT_DIR"
    mkdir -p "$LOG_DIR"
    
    # 设置目录所有者
    chown -R "$USER:$GROUP" "$PROJECT_DIR"
    chown -R "$USER:$GROUP" "$LOG_DIR"
    
    # 复制项目文件（假设从源码目录复制）
    if [[ -d "./public" && -d "./src" ]]; then
        cp -r ./* "$PROJECT_DIR/"
        log_info "项目文件复制完成"
    else
        log_warn "请将项目文件手动复制到 $PROJECT_DIR"
    fi
    
    # 设置文件权限
    chmod -R 755 "$PROJECT_DIR"
    chmod -R 775 "$PROJECT_DIR/storage"
    chmod 600 "$PROJECT_DIR/.env"
    
    log_info "项目部署完成"
}

# 配置Nginx
configure_nginx() {
    log_header "配置Nginx"
    
    # 复制Nginx配置
    if [[ -f "$PROJECT_DIR/nginx/production.conf" ]]; then
        cp "$PROJECT_DIR/nginx/production.conf" "/etc/nginx/sites-available/alingai_pro"
        
        # 创建符号链接
        ln -sf "/etc/nginx/sites-available/alingai_pro" "/etc/nginx/sites-enabled/"
        
        # 删除默认站点
        rm -f "/etc/nginx/sites-enabled/default"
        
        # 测试Nginx配置
        nginx -t
        if [[ $? -eq 0 ]]; then
            log_info "Nginx配置验证成功"
        else
            log_error "Nginx配置验证失败"
            exit 1
        fi
    else
        log_warn "未找到Nginx配置文件，请手动配置"
    fi
    
    log_info "Nginx配置完成"
}

# 初始化数据库
initialize_database() {
    log_header "初始化数据库"
    
    if [[ -f "$PROJECT_DIR/bin/mysql-setup.php" ]]; then
        cd "$PROJECT_DIR"
        php bin/mysql-setup.php
        log_info "数据库初始化完成"
    else
        log_warn "数据库初始化脚本未找到，请手动初始化"
    fi
}

# 配置WebSocket服务
configure_websocket() {
    log_header "配置WebSocket服务"
    
    # 创建Supervisor配置
    cat > /etc/supervisor/conf.d/alingai_websocket.conf << EOF
[program:alingai_websocket]
command=php $PROJECT_DIR/bin/websocket-server.php
directory=$PROJECT_DIR
user=$USER
autostart=true
autorestart=true
stderr_logfile=$LOG_DIR/websocket_error.log
stdout_logfile=$LOG_DIR/websocket_access.log
environment=PATH="/usr/local/bin:/usr/bin:/bin"
EOF
    
    log_info "WebSocket服务配置完成"
}

# 启动所有服务
start_services() {
    log_header "启动系统服务"
    
    # 重新加载systemd
    systemctl daemon-reload
    
    # 启动服务
    systemctl start mysql
    systemctl start redis
    systemctl start php-fpm
    systemctl start nginx
    
    # 重新加载Supervisor
    supervisorctl reread
    supervisorctl update
    supervisorctl start alingai_websocket
    
    # 检查服务状态
    echo ""
    log_info "服务状态检查:"
    systemctl is-active mysql && echo "  ✓ MySQL: 运行中" || echo "  ✗ MySQL: 未运行"
    systemctl is-active redis && echo "  ✓ Redis: 运行中" || echo "  ✗ Redis: 未运行"
    systemctl is-active php-fpm && echo "  ✓ PHP-FPM: 运行中" || echo "  ✗ PHP-FPM: 未运行"
    systemctl is-active nginx && echo "  ✓ Nginx: 运行中" || echo "  ✗ Nginx: 未运行"
    supervisorctl status alingai_websocket | grep RUNNING && echo "  ✓ WebSocket: 运行中" || echo "  ✗ WebSocket: 未运行"
    
    log_info "所有服务启动完成"
}

# 运行就绪检查
run_readiness_check() {
    log_header "运行生产就绪检查"
    
    if [[ -f "$PROJECT_DIR/bin/production-readiness.php" ]]; then
        cd "$PROJECT_DIR"
        php bin/production-readiness.php
    else
        log_warn "生产就绪检查脚本未找到"
    fi
}

# 创建启动后任务
create_post_deployment_tasks() {
    log_header "创建部署后任务"
    
    # 创建crontab任务
    (crontab -l 2>/dev/null; echo "# AlingAi Pro 定时任务") | crontab -
    (crontab -l 2>/dev/null; echo "0 2 * * * cd $PROJECT_DIR && php bin/backup.php > /dev/null 2>&1") | crontab -
    (crontab -l 2>/dev/null; echo "*/5 * * * * cd $PROJECT_DIR && php bin/health-check.php > /dev/null 2>&1") | crontab -
    
    log_info "定时任务创建完成"
    log_info "  - 每天凌晨2点自动备份"
    log_info "  - 每5分钟健康检查"
}

# 显示部署完成信息
show_completion_info() {
    log_header "🎉 AlingAi Pro 部署完成！"
    
    echo ""
    echo "📋 部署信息:"
    echo "  • 项目目录: $PROJECT_DIR"
    echo "  • 日志目录: $LOG_DIR"
    echo "  • 访问地址: http://$(hostname -I | awk '{print $1}')"
    echo ""
    echo "🔧 管理命令:"
    echo "  • 查看状态: systemctl status nginx mysql redis php-fpm"
    echo "  • 重启服务: systemctl restart nginx"
    echo "  • 查看日志: tail -f $LOG_DIR/*.log"
    echo "  • WebSocket: supervisorctl status alingai_websocket"
    echo ""
    echo "📊 监控工具:"
    echo "  • 健康检查: php $PROJECT_DIR/bin/health-check.php"
    echo "  • 备份系统: php $PROJECT_DIR/bin/backup.php"
    echo "  • 系统优化: php $PROJECT_DIR/bin/system-optimizer.php"
    echo ""
    echo "🚀 ${PROJECT_NAME} 已成功部署到生产环境！"
}

# 主函数
main() {
    log_header "AlingAi Pro 生产环境部署脚本 v1.0.0"
    
    check_permissions
    check_system_requirements
    install_dependencies
    configure_services
    deploy_project
    configure_nginx
    initialize_database
    configure_websocket
    start_services
    run_readiness_check
    create_post_deployment_tasks
    show_completion_info
    
    echo ""
    log_info "部署完成！请访问您的服务器IP地址查看网站。"
}

# 运行主函数
main "$@"
