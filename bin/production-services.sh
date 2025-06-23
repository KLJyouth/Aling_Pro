#!/bin/bash

# AlingAi Pro 生产环境服务启动脚本
# 适用于 CentOS 8.0+ x64 系统
# 功能：启动/停止/重启所有必需的系统服务

set -e

# 配置变量
PROJECT_ROOT="/var/www/alingai_pro"
WEBSOCKET_PORT=8080
PHP_FPM_POOL="www"
LOG_FILE="/var/log/alingai_pro_services.log"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 日志函数
log() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log_info() {
    log "${BLUE}[INFO]${NC} $1"
}

log_success() {
    log "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    log "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    log "${RED}[ERROR]${NC} $1"
}

# 检查是否为root用户
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本需要root权限运行"
        echo "请使用: sudo $0 $@"
        exit 1
    fi
}

# 检查服务状态
check_service_status() {
    local service_name=$1
    if systemctl is-active --quiet "$service_name"; then
        echo "running"
    else
        echo "stopped"
    fi
}

# 启动MySQL服务
start_mysql() {
    log_info "启动MySQL数据库服务..."
    
    if [[ $(check_service_status "mysqld") == "running" ]]; then
        log_warning "MySQL服务已经在运行"
        return 0
    fi
    
    systemctl start mysqld
    systemctl enable mysqld
    
    if [[ $(check_service_status "mysqld") == "running" ]]; then
        log_success "MySQL服务启动成功"
    else
        log_error "MySQL服务启动失败"
        return 1
    fi
}

# 启动Redis服务
start_redis() {
    log_info "启动Redis缓存服务..."
    
    if [[ $(check_service_status "redis") == "running" ]]; then
        log_warning "Redis服务已经在运行"
        return 0
    fi
    
    systemctl start redis
    systemctl enable redis
    
    if [[ $(check_service_status "redis") == "running" ]]; then
        log_success "Redis服务启动成功"
    else
        log_error "Redis服务启动失败"
        return 1
    fi
}

# 启动PHP-FPM服务
start_php_fpm() {
    log_info "启动PHP-FPM服务..."
    
    if [[ $(check_service_status "php-fpm") == "running" ]]; then
        log_warning "PHP-FPM服务已经在运行"
        return 0
    fi
    
    systemctl start php-fpm
    systemctl enable php-fpm
    
    if [[ $(check_service_status "php-fpm") == "running" ]]; then
        log_success "PHP-FPM服务启动成功"
    else
        log_error "PHP-FPM服务启动失败"
        return 1
    fi
}

# 启动Nginx服务
start_nginx() {
    log_info "启动Nginx Web服务器..."
    
    # 检查配置文件语法
    if ! nginx -t 2>/dev/null; then
        log_error "Nginx配置文件语法错误"
        nginx -t
        return 1
    fi
    
    if [[ $(check_service_status "nginx") == "running" ]]; then
        log_warning "Nginx服务已经在运行，重新加载配置..."
        systemctl reload nginx
        log_success "Nginx配置重新加载成功"
        return 0
    fi
    
    systemctl start nginx
    systemctl enable nginx
    
    if [[ $(check_service_status "nginx") == "running" ]]; then
        log_success "Nginx服务启动成功"
    else
        log_error "Nginx服务启动失败"
        return 1
    fi
}

# 启动WebSocket服务
start_websocket() {
    log_info "启动WebSocket服务..."
    
    local websocket_script="$PROJECT_ROOT/websocket/server.php"
    local pid_file="/var/run/alingai_websocket.pid"
    
    # 检查WebSocket服务脚本是否存在
    if [[ ! -f "$websocket_script" ]]; then
        log_error "WebSocket服务脚本不存在: $websocket_script"
        return 1
    fi
    
    # 检查是否已经在运行
    if [[ -f "$pid_file" ]] && kill -0 $(cat "$pid_file") 2>/dev/null; then
        log_warning "WebSocket服务已经在运行 (PID: $(cat $pid_file))"
        return 0
    fi
    
    # 启动WebSocket服务
    cd "$PROJECT_ROOT"
    nohup php "$websocket_script" > /var/log/alingai_websocket.log 2>&1 &
    echo $! > "$pid_file"
    
    sleep 2
    
    # 验证服务是否启动成功
    if kill -0 $(cat "$pid_file") 2>/dev/null; then
        log_success "WebSocket服务启动成功 (PID: $(cat $pid_file), Port: $WEBSOCKET_PORT)"
    else
        log_error "WebSocket服务启动失败"
        return 1
    fi
}

# 停止MySQL服务
stop_mysql() {
    log_info "停止MySQL数据库服务..."
    
    if [[ $(check_service_status "mysqld") == "stopped" ]]; then
        log_warning "MySQL服务已经停止"
        return 0
    fi
    
    systemctl stop mysqld
    log_success "MySQL服务停止成功"
}

# 停止Redis服务
stop_redis() {
    log_info "停止Redis缓存服务..."
    
    if [[ $(check_service_status "redis") == "stopped" ]]; then
        log_warning "Redis服务已经停止"
        return 0
    fi
    
    systemctl stop redis
    log_success "Redis服务停止成功"
}

# 停止PHP-FPM服务
stop_php_fpm() {
    log_info "停止PHP-FPM服务..."
    
    if [[ $(check_service_status "php-fpm") == "stopped" ]]; then
        log_warning "PHP-FPM服务已经停止"
        return 0
    fi
    
    systemctl stop php-fpm
    log_success "PHP-FPM服务停止成功"
}

# 停止Nginx服务
stop_nginx() {
    log_info "停止Nginx Web服务器..."
    
    if [[ $(check_service_status "nginx") == "stopped" ]]; then
        log_warning "Nginx服务已经停止"
        return 0
    fi
    
    systemctl stop nginx
    log_success "Nginx服务停止成功"
}

# 停止WebSocket服务
stop_websocket() {
    log_info "停止WebSocket服务..."
    
    local pid_file="/var/run/alingai_websocket.pid"
    
    if [[ ! -f "$pid_file" ]]; then
        log_warning "WebSocket服务PID文件不存在，可能未运行"
        return 0
    fi
    
    local pid=$(cat "$pid_file")
    
    if kill -0 "$pid" 2>/dev/null; then
        kill "$pid"
        rm -f "$pid_file"
        log_success "WebSocket服务停止成功"
    else
        log_warning "WebSocket服务进程不存在，清理PID文件"
        rm -f "$pid_file"
    fi
}

# 检查系统状态
check_system_status() {
    log_info "检查系统服务状态..."
    
    echo ""
    echo "======================================================"
    echo "           AlingAi Pro 系统服务状态"
    echo "======================================================"
    
    # 检查各个服务状态
    services=("mysqld:MySQL数据库" "redis:Redis缓存" "php-fpm:PHP-FPM" "nginx:Nginx Web服务器")
    
    for service_info in "${services[@]}"; do
        service_name=$(echo "$service_info" | cut -d: -f1)
        display_name=$(echo "$service_info" | cut -d: -f2)
        
        status=$(check_service_status "$service_name")
        if [[ "$status" == "running" ]]; then
            echo -e "${display_name}: ${GREEN}运行中${NC}"
        else
            echo -e "${display_name}: ${RED}已停止${NC}"
        fi
    done
    
    # 检查WebSocket服务
    local pid_file="/var/run/alingai_websocket.pid"
    if [[ -f "$pid_file" ]] && kill -0 $(cat "$pid_file") 2>/dev/null; then
        echo -e "WebSocket服务: ${GREEN}运行中${NC} (PID: $(cat $pid_file))"
    else
        echo -e "WebSocket服务: ${RED}已停止${NC}"
    fi
    
    echo "======================================================"
    echo ""
    
    # 检查端口占用
    log_info "检查端口占用情况..."
    echo "端口占用情况:"
    
    ports=("80:HTTP" "443:HTTPS" "3306:MySQL" "6379:Redis" "8080:WebSocket" "9000:PHP-FPM")
    for port_info in "${ports[@]}"; do
        port=$(echo "$port_info" | cut -d: -f1)
        desc=$(echo "$port_info" | cut -d: -f2)
        
        if netstat -tuln | grep -q ":$port "; then
            echo -e "  端口 $port ($desc): ${GREEN}使用中${NC}"
        else
            echo -e "  端口 $port ($desc): ${YELLOW}空闲${NC}"
        fi
    done
    
    echo ""
}

# 启动所有服务
start_all() {
    log_info "启动AlingAi Pro系统所有服务..."
    
    echo ""
    echo "======================================================"
    echo "         启动 AlingAi Pro 生产环境服务"
    echo "======================================================"
    echo ""
    
    start_mysql
    start_redis
    start_php_fpm
    start_nginx
    start_websocket
    
    echo ""
    log_success "所有服务启动完成！"
    
    sleep 2
    check_system_status
}

# 停止所有服务
stop_all() {
    log_info "停止AlingAi Pro系统所有服务..."
    
    echo ""
    echo "======================================================"
    echo "         停止 AlingAi Pro 生产环境服务"
    echo "======================================================"
    echo ""
    
    stop_websocket
    stop_nginx
    stop_php_fpm
    stop_redis
    stop_mysql
    
    echo ""
    log_success "所有服务停止完成！"
}

# 重启所有服务
restart_all() {
    log_info "重启AlingAi Pro系统所有服务..."
    
    stop_all
    sleep 3
    start_all
}

# 显示帮助信息
show_help() {
    echo ""
    echo "AlingAi Pro 生产环境服务管理脚本"
    echo ""
    echo "用法: $0 {start|stop|restart|status|help}"
    echo ""
    echo "命令:"
    echo "  start   - 启动所有服务"
    echo "  stop    - 停止所有服务"
    echo "  restart - 重启所有服务"
    echo "  status  - 检查服务状态"
    echo "  help    - 显示帮助信息"
    echo ""
    echo "单独服务控制:"
    echo "  $0 start mysql    - 启动MySQL服务"
    echo "  $0 start redis    - 启动Redis服务"
    echo "  $0 start php-fpm  - 启动PHP-FPM服务"
    echo "  $0 start nginx    - 启动Nginx服务"
    echo "  $0 start websocket- 启动WebSocket服务"
    echo ""
    echo "日志文件: $LOG_FILE"
    echo ""
}

# 主程序
main() {
    check_root
    
    case "${1:-}" in
        start)
            case "${2:-}" in
                mysql) start_mysql ;;
                redis) start_redis ;;
                php-fpm) start_php_fpm ;;
                nginx) start_nginx ;;
                websocket) start_websocket ;;
                *) start_all ;;
            esac
            ;;
        stop)
            case "${2:-}" in
                mysql) stop_mysql ;;
                redis) stop_redis ;;
                php-fpm) stop_php_fpm ;;
                nginx) stop_nginx ;;
                websocket) stop_websocket ;;
                *) stop_all ;;
            esac
            ;;
        restart)
            restart_all
            ;;
        status)
            check_system_status
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            echo "错误: 无效的命令 '${1:-}'"
            show_help
            exit 1
            ;;
    esac
}

# 创建日志目录
mkdir -p "$(dirname "$LOG_FILE")"

# 运行主程序
main "$@"
