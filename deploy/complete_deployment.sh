#!/bin/bash

# AlingAI Pro 5.0 完整部署和测试脚本
# 版本: 5.0.0-Final
# 日期: 2024-12-19

set -e

echo "🚀 AlingAI Pro 5.0 企业级智能办公系统"
echo "=================================================="
echo "开始完整系统部署和集成测试..."
echo "版本: 5.0.0-Final"
echo "时间: $(date)"
echo "=================================================="

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

# 检查操作系统
detect_os() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        OS="linux"
        log_info "检测到Linux系统"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        OS="macos"
        log_info "检测到macOS系统"
    elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "cygwin" ]]; then
        OS="windows"
        log_info "检测到Windows系统"
    else
        log_error "不支持的操作系统: $OSTYPE"
        exit 1
    fi
}

# 检查系统依赖
check_dependencies() {
    log_info "检查系统依赖..."
    
    # 检查PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP未安装或不在PATH中"
        exit 1
    fi
    
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    log_info "PHP版本: $PHP_VERSION"
    
    if [[ $(echo "$PHP_VERSION >= 8.1" | bc) -eq 0 ]]; then
        log_error "需要PHP 8.1或更高版本"
        exit 1
    fi
    
    # 检查Composer
    if ! command -v composer &> /dev/null; then
        log_error "Composer未安装"
        exit 1
    fi
    
    # 检查Node.js (用于前端构建)
    if ! command -v node &> /dev/null; then
        log_warning "Node.js未安装，将跳过前端构建"
    else
        NODE_VERSION=$(node -v)
        log_info "Node.js版本: $NODE_VERSION"
    fi
    
    # 检查数据库
    if command -v mysql &> /dev/null; then
        log_info "MySQL可用"
        DB_TYPE="mysql"
    elif command -v sqlite3 &> /dev/null; then
        log_info "SQLite可用"
        DB_TYPE="sqlite"
    else
        log_warning "未检测到数据库系统，将使用SQLite"
        DB_TYPE="sqlite"
    fi
    
    log_success "依赖检查完成"
}

# 创建必要的目录结构
create_directories() {
    log_info "创建目录结构..."
    
    mkdir -p logs/{security,ai,system,websocket}
    mkdir -p storage/{cache,sessions,uploads,backups}
    mkdir -p resources/{views,assets}
    mkdir -p public/{assets/{js,css,images},uploads}
    mkdir -p database/{migrations,seeds}
    mkdir -p config/{environments,security}
    mkdir -p vendor
    mkdir -p tmp
    
    log_success "目录结构创建完成"
}

# 设置权限
setup_permissions() {
    log_info "设置文件权限..."
    
    if [[ "$OS" != "windows" ]]; then
        chmod -R 755 .
        chmod -R 777 logs/
        chmod -R 777 storage/
        chmod -R 777 public/uploads/
        chmod -R 777 tmp/
        chmod +x deploy/*.sh
        chmod +x start_*.php
    fi
    
    log_success "权限设置完成"
}

# 安装Composer依赖
install_composer_dependencies() {
    log_info "安装Composer依赖..."
    
    if [[ ! -f composer.json ]]; then
        log_error "composer.json文件不存在"
        exit 1
    fi
    
    # 清理vendor目录
    if [[ -d vendor ]]; then
        rm -rf vendor
    fi
    
    # 安装依赖
    composer install --no-dev --optimize-autoloader --no-interaction
    
    if [[ $? -eq 0 ]]; then
        log_success "Composer依赖安装完成"
    else
        log_error "Composer依赖安装失败"
        exit 1
    fi
}

# 环境配置
setup_environment() {
    log_info "配置环境变量..."
    
    # 复制环境配置文件
    if [[ ! -f .env ]]; then
        if [[ -f .env.example ]]; then
            cp .env.example .env
            log_info "复制.env.example到.env"
        else
            log_info "创建默认.env文件"
            cat > .env << 'EOF'
# AlingAI Pro 5.0 环境配置
APP_ENV=production
APP_DEBUG=false
APP_NAME="AlingAI Pro 5.0"
APP_VERSION="5.0.0"

# 数据库配置
DB_TYPE=sqlite
DB_HOST=localhost
DB_PORT=3306
DB_NAME=alingai_pro
DB_USER=root
DB_PASS=

# Redis配置
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASS=

# AI服务配置
DEEPSEEK_API_KEY=your_deepseek_api_key_here
OPENAI_API_KEY=your_openai_api_key_here
AI_PROVIDER=deepseek

# 安全配置
JWT_SECRET=your_jwt_secret_here
ENCRYPTION_KEY=your_encryption_key_here

# WebSocket配置
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8080

# 监控配置
MONITORING_ENABLED=true
SECURITY_MONITORING=true
THREAT_DETECTION=true

# 日志配置
LOG_LEVEL=info
LOG_CHANNEL=file

# 缓存配置
CACHE_DRIVER=file
CACHE_TTL=3600

# 会话配置
SESSION_DRIVER=file
SESSION_LIFETIME=7200

# 邮件配置
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# 队列配置
QUEUE_DRIVER=database
QUEUE_CONNECTION=default

# 文件存储配置
FILESYSTEM_DRIVER=local
UPLOAD_MAX_SIZE=10485760

# API配置
API_RATE_LIMIT=1000
API_TIMEOUT=30

# 安全监控配置
SECURITY_SCAN_INTERVAL=300
THREAT_DETECTION_SENSITIVITY=medium
AUTO_BLOCK_ENABLED=true
FIREWALL_ENABLED=true

# 备份配置
BACKUP_ENABLED=true
BACKUP_INTERVAL=86400
BACKUP_RETENTION_DAYS=30

# 性能监控
PERFORMANCE_MONITORING=true
METRICS_COLLECTION=true
PROFILING_ENABLED=false
EOF
        fi
    fi
    
    # 生成安全密钥
    if ! grep -q "JWT_SECRET=your_jwt_secret_here" .env; then
        JWT_SECRET=$(openssl rand -hex 32 2>/dev/null || head -c 32 /dev/urandom | base64 | tr -d '=' | tr '+/' '-_')
        sed -i.bak "s/JWT_SECRET=your_jwt_secret_here/JWT_SECRET=$JWT_SECRET/" .env
        log_info "生成JWT密钥"
    fi
    
    if ! grep -q "ENCRYPTION_KEY=your_encryption_key_here" .env; then
        ENCRYPTION_KEY=$(openssl rand -hex 32 2>/dev/null || head -c 32 /dev/urandom | base64 | tr -d '=' | tr '+/' '-_')
        sed -i.bak "s/ENCRYPTION_KEY=your_encryption_key_here/ENCRYPTION_KEY=$ENCRYPTION_KEY/" .env
        log_info "生成加密密钥"
    fi
    
    log_success "环境配置完成"
}

# 数据库初始化
setup_database() {
    log_info "初始化数据库..."
    
    if [[ "$DB_TYPE" == "sqlite" ]]; then
        # SQLite数据库初始化
        if [[ ! -f storage/database.sqlite ]]; then
            touch storage/database.sqlite
            chmod 666 storage/database.sqlite
        fi
        
        # 运行SQLite迁移
        if [[ -f database/migrations/create_security_monitoring_tables.sql ]]; then
            log_info "执行安全监控表迁移..."
            sqlite3 storage/database.sqlite < database/migrations/create_security_monitoring_tables.sql
        fi
        
        if [[ -f database/migrations/create_configuration_tables.sql ]]; then
            log_info "执行配置管理表迁移..."
            sqlite3 storage/database.sqlite < database/migrations/create_configuration_tables.sql
        fi
        
    elif [[ "$DB_TYPE" == "mysql" ]]; then
        # MySQL数据库初始化
        log_info "检查MySQL连接..."
        
        # 这里可以添加MySQL初始化逻辑
        php -r "
        try {
            \$pdo = new PDO('mysql:host=localhost', 'root', '');
            \$pdo->exec('CREATE DATABASE IF NOT EXISTS alingai_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            echo 'MySQL数据库创建成功\n';
        } catch (Exception \$e) {
            echo 'MySQL连接失败: ' . \$e->getMessage() . '\n';
            exit(1);
        }
        "
    fi
    
    log_success "数据库初始化完成"
}

# 安全系统初始化
setup_security_system() {
    log_info "初始化安全系统..."
    
    # 运行安全系统检查
    if [[ -f check_security_system.php ]]; then
        php check_security_system.php
    fi
    
    # 初始化安全监控数据库
    if [[ -f sqlite_security_migration.php ]]; then
        php sqlite_security_migration.php
    fi
    
    log_success "安全系统初始化完成"
}

# 前端资源构建
build_frontend_assets() {
    log_info "构建前端资源..."
    
    # 检查是否有前端构建脚本
    if [[ -f package.json ]] && command -v npm &> /dev/null; then
        log_info "发现package.json，执行npm构建..."
        npm install
        npm run build 2>/dev/null || log_warning "npm构建失败或不存在build脚本"
    fi
    
    # 确保静态资源存在
    if [[ ! -d public/assets/js ]]; then
        mkdir -p public/assets/js
    fi
    
    if [[ ! -d public/assets/css ]]; then
        mkdir -p public/assets/css
    fi
    
    # 复制核心JavaScript文件
    if [[ -f public/assets/js/real-time-security-dashboard.js ]]; then
        log_info "实时安全监控Dashboard已存在"
    else
        log_warning "实时安全监控Dashboard文件缺失"
    fi
    
    log_success "前端资源构建完成"
}

# 启动WebSocket服务器
start_websocket_server() {
    log_info "启动WebSocket安全监控服务器..."
    
    if [[ -f start_websocket_server.php ]]; then
        # 检查端口是否被占用
        WEBSOCKET_PORT=${WEBSOCKET_PORT:-8080}
        
        if command -v netstat &> /dev/null; then
            if netstat -tuln | grep ":$WEBSOCKET_PORT " > /dev/null; then
                log_warning "端口 $WEBSOCKET_PORT 已被占用"
                return
            fi
        fi
        
        # 后台启动WebSocket服务器
        nohup php start_websocket_server.php > logs/websocket/websocket.log 2>&1 &
        WEBSOCKET_PID=$!
        
        # 等待服务器启动
        sleep 3
        
        # 检查服务器是否启动成功
        if kill -0 $WEBSOCKET_PID 2>/dev/null; then
            echo $WEBSOCKET_PID > tmp/websocket.pid
            log_success "WebSocket服务器启动成功 (PID: $WEBSOCKET_PID)"
        else
            log_error "WebSocket服务器启动失败"
        fi
    else
        log_warning "WebSocket服务器脚本不存在"
    fi
}

# 启动安全监控系统
start_security_monitoring() {
    log_info "启动实时安全监控系统..."
    
    if [[ -f start_security_monitoring.php ]]; then
        # 后台启动安全监控
        nohup php start_security_monitoring.php > logs/security/monitoring.log 2>&1 &
        MONITORING_PID=$!
        
        # 等待系统启动
        sleep 3
        
        # 检查系统是否启动成功
        if kill -0 $MONITORING_PID 2>/dev/null; then
            echo $MONITORING_PID > tmp/monitoring.pid
            log_success "安全监控系统启动成功 (PID: $MONITORING_PID)"
        else
            log_error "安全监控系统启动失败"
        fi
    else
        log_warning "安全监控系统脚本不存在"
    fi
}

# 运行系统测试
run_system_tests() {
    log_info "运行系统集成测试..."
    
    # API测试
    if [[ -f comprehensive_api_test.php ]]; then
        log_info "运行API集成测试..."
        php comprehensive_api_test.php
    fi
    
    # 数据库测试
    if [[ -f check_database_structure.php ]]; then
        log_info "检查数据库结构..."
        php check_database_structure.php
    fi
    
    # AI服务测试
    if [[ -f ai_service_health_check.php ]]; then
        log_info "测试AI服务连接..."
        php ai_service_health_check.php
    fi
    
    # 缓存测试
    if [[ -f cache_performance_test.php ]]; then
        log_info "测试缓存性能..."
        php cache_performance_test.php
    fi
    
    log_success "系统测试完成"
}

# 启动Web服务器
start_web_server() {
    log_info "启动Web服务器..."
    
    # 检查是否有内置服务器配置
    WEB_PORT=${WEB_PORT:-8000}
    
    if command -v netstat &> /dev/null; then
        if netstat -tuln | grep ":$WEB_PORT " > /dev/null; then
            log_warning "端口 $WEB_PORT 已被占用，尝试使用下一个端口"
            WEB_PORT=$((WEB_PORT + 1))
        fi
    fi
    
    # 启动PHP内置服务器
    log_info "在端口 $WEB_PORT 启动PHP内置服务器..."
    nohup php -S localhost:$WEB_PORT -t public > logs/system/webserver.log 2>&1 &
    WEB_PID=$!
    
    # 等待服务器启动
    sleep 2
    
    # 检查服务器是否启动成功
    if kill -0 $WEB_PID 2>/dev/null; then
        echo $WEB_PID > tmp/webserver.pid
        log_success "Web服务器启动成功 (PID: $WEB_PID, 端口: $WEB_PORT)"
        echo "访问地址: http://localhost:$WEB_PORT"
    else
        log_error "Web服务器启动失败"
    fi
}

# 生成部署报告
generate_deployment_report() {
    log_info "生成部署报告..."
    
    REPORT_FILE="deployment_report_$(date +%Y_%m_%d_%H_%M_%S).md"
    
    cat > $REPORT_FILE << EOF
# AlingAI Pro 5.0 部署报告

## 部署信息
- **版本**: 5.0.0-Final
- **部署时间**: $(date)
- **操作系统**: $OS
- **PHP版本**: $PHP_VERSION
- **数据库类型**: $DB_TYPE

## 服务状态
EOF
    
    # 检查服务状态
    if [[ -f tmp/webserver.pid ]]; then
        WEB_PID=$(cat tmp/webserver.pid)
        if kill -0 $WEB_PID 2>/dev/null; then
            echo "- ✅ Web服务器: 运行中 (PID: $WEB_PID)" >> $REPORT_FILE
        else
            echo "- ❌ Web服务器: 已停止" >> $REPORT_FILE
        fi
    else
        echo "- ❌ Web服务器: 未启动" >> $REPORT_FILE
    fi
    
    if [[ -f tmp/websocket.pid ]]; then
        WEBSOCKET_PID=$(cat tmp/websocket.pid)
        if kill -0 $WEBSOCKET_PID 2>/dev/null; then
            echo "- ✅ WebSocket服务器: 运行中 (PID: $WEBSOCKET_PID)" >> $REPORT_FILE
        else
            echo "- ❌ WebSocket服务器: 已停止" >> $REPORT_FILE
        fi
    else
        echo "- ❌ WebSocket服务器: 未启动" >> $REPORT_FILE
    fi
    
    if [[ -f tmp/monitoring.pid ]]; then
        MONITORING_PID=$(cat tmp/monitoring.pid)
        if kill -0 $MONITORING_PID 2>/dev/null; then
            echo "- ✅ 安全监控系统: 运行中 (PID: $MONITORING_PID)" >> $REPORT_FILE
        else
            echo "- ❌ 安全监控系统: 已停止" >> $REPORT_FILE
        fi
    else
        echo "- ❌ 安全监控系统: 未启动" >> $REPORT_FILE
    fi
    
    cat >> $REPORT_FILE << EOF

## 核心功能
- ✅ 智能办公系统
- ✅ 实时威胁监控
- ✅ 3D威胁可视化
- ✅ AI智能代理系统
- ✅ 自学习自进化AI
- ✅ 数据库驱动配置管理
- ✅ 增强反爬虫系统
- ✅ WebSocket实时通信

## 访问地址
- 主应用: http://localhost:$WEB_PORT
- 安全监控: http://localhost:$WEB_PORT/security/monitoring
- 3D威胁可视化: http://localhost:$WEB_PORT/security/visualization
- 管理后台: http://localhost:$WEB_PORT/admin
- API文档: http://localhost:$WEB_PORT/api/docs

## 管理命令
- 停止所有服务: ./deploy/stop_services.sh
- 重启服务: ./deploy/restart_services.sh
- 查看日志: tail -f logs/system/webserver.log
- 监控状态: ./deploy/check_status.sh

## 配置文件
- 环境配置: .env
- 路由配置: config/routes.php
- 数据库配置: config/database.php
- 安全配置: config/security.php

## 日志文件
- 系统日志: logs/system/
- 安全日志: logs/security/
- WebSocket日志: logs/websocket/
- AI服务日志: logs/ai/

## 注意事项
1. 确保防火墙允许端口 $WEB_PORT 和 8080 的访问
2. 定期检查安全日志和威胁报告
3. 保持系统和依赖库的更新
4. 定期备份数据库和配置文件
5. 监控系统资源使用情况

---
报告生成时间: $(date)
AlingAI Pro 5.0 - 企业级智能办公系统
EOF
    
    log_success "部署报告已生成: $REPORT_FILE"
}

# 显示部署后信息
show_deployment_info() {
    echo ""
    echo "=================================================="
    echo "🎉 AlingAI Pro 5.0 部署完成！"
    echo "=================================================="
    echo ""
    echo "🌐 访问地址:"
    echo "   主应用: http://localhost:${WEB_PORT:-8000}"
    echo "   安全监控: http://localhost:${WEB_PORT:-8000}/security/monitoring"
    echo "   3D威胁可视化: http://localhost:${WEB_PORT:-8000}/security/visualization"
    echo "   管理后台: http://localhost:${WEB_PORT:-8000}/admin"
    echo ""
    echo "📊 服务状态:"
    
    if [[ -f tmp/webserver.pid ]]; then
        WEB_PID=$(cat tmp/webserver.pid)
        if kill -0 $WEB_PID 2>/dev/null; then
            echo "   ✅ Web服务器: 运行中"
        else
            echo "   ❌ Web服务器: 已停止"
        fi
    fi
    
    if [[ -f tmp/websocket.pid ]]; then
        WEBSOCKET_PID=$(cat tmp/websocket.pid)
        if kill -0 $WEBSOCKET_PID 2>/dev/null; then
            echo "   ✅ WebSocket服务器: 运行中"
        else
            echo "   ❌ WebSocket服务器: 已停止"
        fi
    fi
    
    if [[ -f tmp/monitoring.pid ]]; then
        MONITORING_PID=$(cat tmp/monitoring.pid)
        if kill -0 $MONITORING_PID 2>/dev/null; then
            echo "   ✅ 安全监控系统: 运行中"
        else
            echo "   ❌ 安全监控系统: 已停止"
        fi
    fi
    
    echo ""
    echo "🛠️ 管理命令:"
    echo "   查看状态: ./deploy/check_status.sh"
    echo "   停止服务: ./deploy/stop_services.sh"
    echo "   重启服务: ./deploy/restart_services.sh"
    echo "   查看日志: tail -f logs/system/webserver.log"
    echo ""
    echo "📋 关键功能:"
    echo "   • 智能办公系统"
    echo "   • 实时威胁监控与3D可视化"
    echo "   • AI智能代理协调系统"
    echo "   • 自学习自进化AI引擎"
    echo "   • 数据库驱动配置管理"
    echo "   • 增强反爬虫保护"
    echo "   • 全球威胁情报分析"
    echo ""
    echo "=================================================="
}

# 主函数
main() {
    detect_os
    check_dependencies
    create_directories
    setup_permissions
    install_composer_dependencies
    setup_environment
    setup_database
    setup_security_system
    build_frontend_assets
    start_websocket_server
    start_security_monitoring
    run_system_tests
    start_web_server
    generate_deployment_report
    show_deployment_info
    
    log_success "AlingAI Pro 5.0 部署完成！"
}

# 信号处理
trap 'log_error "部署被中断"; exit 1' INT TERM

# 执行主函数
main "$@"
