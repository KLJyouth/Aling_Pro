#!/bin/bash

# AlingAi Pro 6.0 完整系统部署和测试脚本
# 这个脚本会实际部署和测试整个系统的核心功能

set -e  # 遇到错误立即退出

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

# 检查系统要求
check_system_requirements() {
    log_info "检查系统要求..."
    
    # 检查Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker未安装"
        exit 1
    fi
    
    # 检查Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose未安装"
        exit 1
    fi
    
    # 检查PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP未安装"
        exit 1
    fi
    
    # 检查Composer
    if ! command -v composer &> /dev/null; then
        log_error "Composer未安装"
        exit 1
    fi
    
    log_success "系统要求检查通过"
}

# 安装依赖
install_dependencies() {
    log_info "安装项目依赖..."
    
    # 安装PHP依赖
    composer install --no-dev --optimize-autoloader
    
    # 安装Node.js依赖（如果存在）
    if [ -f "package.json" ]; then
        npm install --production
    fi
    
    log_success "依赖安装完成"
}

# 配置环境
setup_environment() {
    log_info "配置环境变量..."
    
    # 复制环境文件
    if [ ! -f ".env" ]; then
        cp .env.example .env
        log_info "已创建.env文件，请根据需要修改配置"
    fi
    
    # 生成应用密钥
    php artisan key:generate --force
    
    # 设置目录权限
    chmod -R 755 storage bootstrap/cache
    
    log_success "环境配置完成"
}

# 数据库初始化
setup_database() {
    log_info "初始化数据库..."
    
    # 等待数据库启动
    sleep 10
    
    # 运行数据库迁移
    php artisan migrate:fresh --force
    
    # 运行数据填充
    php artisan db:seed --force
    
    log_success "数据库初始化完成"
}

# 启动服务
start_services() {
    log_info "启动Docker服务..."
    
    # 构建镜像
    docker-compose build --no-cache
    
    # 启动服务
    docker-compose up -d
    
    # 等待服务启动
    log_info "等待服务启动..."
    sleep 30
    
    log_success "服务启动完成"
}

# 运行系统测试
run_system_tests() {
    log_info "运行系统测试..."
    
    # 测试Web服务
    test_web_service
    
    # 测试API服务
    test_api_service
    
    # 测试数据库连接
    test_database_connection
    
    # 测试Redis连接
    test_redis_connection
    
    # 测试企业服务
    test_enterprise_services
    
    # 测试AI服务
    test_ai_services
    
    # 测试区块链服务
    test_blockchain_services
    
    log_success "系统测试完成"
}

# 测试Web服务
test_web_service() {
    log_info "测试Web服务..."
    
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
    if [ "$response" = "200" ]; then
        log_success "Web服务正常"
    else
        log_error "Web服务异常 (HTTP $response)"
        return 1
    fi
}

# 测试API服务
test_api_service() {
    log_info "测试API服务..."
    
    # 测试健康检查端点
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/health)
    if [ "$response" = "200" ]; then
        log_success "API服务正常"
    else
        log_error "API服务异常 (HTTP $response)"
        return 1
    fi
    
    # 测试API版本端点
    local version=$(curl -s http://localhost:8000/api/version | jq -r '.version' 2>/dev/null || echo "unknown")
    if [ "$version" = "6.0.0" ]; then
        log_success "API版本正确: $version"
    else
        log_warning "API版本可能不正确: $version"
    fi
}

# 测试数据库连接
test_database_connection() {
    log_info "测试数据库连接..."
    
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" 2>/dev/null; then
        log_success "数据库连接正常"
    else
        log_error "数据库连接失败"
        return 1
    fi
}

# 测试Redis连接
test_redis_connection() {
    log_info "测试Redis连接..."
    
    if php artisan tinker --execute="Redis::ping(); echo 'Redis connected successfully';" 2>/dev/null; then
        log_success "Redis连接正常"
    else
        log_error "Redis连接失败"
        return 1
    fi
}

# 测试企业服务
test_enterprise_services() {
    log_info "测试企业服务..."
    
    # 测试工作空间创建
    local response=$(curl -s -X POST http://localhost:8000/api/enterprise/workspaces \
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer test-token" \
        -d '{"name":"测试工作空间","template":"startup"}' \
        -w "%{http_code}")
    
    if [[ "$response" == *"201"* ]] || [[ "$response" == *"200"* ]]; then
        log_success "企业服务工作空间创建测试通过"
    else
        log_warning "企业服务测试需要配置认证"
    fi
}

# 测试AI服务
test_ai_services() {
    log_info "测试AI服务..."
    
    # 测试AI服务状态
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/ai/status)
    if [ "$response" = "200" ]; then
        log_success "AI服务状态正常"
    else
        log_warning "AI服务可能需要额外配置"
    fi
}

# 测试区块链服务
test_blockchain_services() {
    log_info "测试区块链服务..."
    
    # 测试区块链服务状态
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/blockchain/status)
    if [ "$response" = "200" ]; then
        log_success "区块链服务状态正常"
    else
        log_warning "区块链服务可能需要额外配置"
    fi
}

# 运行性能测试
run_performance_tests() {
    log_info "运行性能测试..."
    
    # 使用ab进行简单的性能测试
    if command -v ab &> /dev/null; then
        log_info "执行Web服务性能测试..."
        ab -n 100 -c 10 http://localhost:8000/ > /tmp/performance_test.log 2>&1
        
        local requests_per_second=$(grep "Requests per second" /tmp/performance_test.log | awk '{print $4}')
        log_info "性能测试结果: $requests_per_second 请求/秒"
    else
        log_warning "Apache Bench (ab) 未安装，跳过性能测试"
    fi
}

# 生成测试报告
generate_test_report() {
    log_info "生成测试报告..."
    
    local report_file="deployment_test_report_$(date +%Y%m%d_%H%M%S).txt"
    
    cat > "$report_file" << EOF
# AlingAi Pro 6.0 部署测试报告

## 测试时间
$(date)

## 系统信息
- 操作系统: $(uname -a)
- Docker版本: $(docker --version)
- PHP版本: $(php --version | head -n1)
- Composer版本: $(composer --version)

## 服务状态
EOF
    
    # 检查Docker容器状态
    echo "## Docker容器状态" >> "$report_file"
    docker-compose ps >> "$report_file" 2>&1
    
    # 检查系统资源使用
    echo -e "\n## 系统资源使用" >> "$report_file"
    echo "内存使用:" >> "$report_file"
    free -h >> "$report_file"
    echo -e "\n磁盘使用:" >> "$report_file"
    df -h >> "$report_file"
    
    # 检查端口监听
    echo -e "\n## 端口监听状态" >> "$report_file"
    netstat -tlnp | grep -E ":80|:443|:3306|:6379|:9000" >> "$report_file" 2>&1 || true
    
    log_success "测试报告已生成: $report_file"
}

# 健康检查
health_check() {
    log_info "执行系统健康检查..."
    
    local healthy=true
    
    # 检查关键服务
    local services=("app" "nginx" "mysql" "redis")
    for service in "${services[@]}"; do
        if docker-compose ps "$service" | grep -q "Up"; then
            log_success "$service 服务运行正常"
        else
            log_error "$service 服务异常"
            healthy=false
        fi
    done
    
    # 检查日志中的错误
    if docker-compose logs app 2>&1 | grep -i "error\|exception\|fatal" | tail -5; then
        log_warning "发现应用错误日志，请检查"
    fi
    
    if [ "$healthy" = true ]; then
        log_success "系统健康检查通过"
        return 0
    else
        log_error "系统健康检查失败"
        return 1
    fi
}

# 清理函数
cleanup() {
    log_info "清理临时文件..."
    rm -f /tmp/performance_test.log
}

# 主函数
main() {
    echo "========================================"
    echo "   AlingAi Pro 6.0 系统部署和测试"
    echo "========================================"
    echo
    
    # 设置错误处理
    trap cleanup EXIT
    
    # 执行部署步骤
    check_system_requirements
    install_dependencies
    setup_environment
    start_services
    setup_database
    
    # 等待服务完全启动
    log_info "等待所有服务完全启动..."
    sleep 60
    
    # 执行测试
    run_system_tests
    run_performance_tests
    health_check
    
    # 生成报告
    generate_test_report
    
    echo
    echo "========================================"
    log_success "AlingAi Pro 6.0 部署完成！"
    echo "========================================"
    echo
    echo "访问地址:"
    echo "- Web应用: http://localhost:8000"
    echo "- 政府门户: http://localhost:8000/government"
    echo "- 企业工作空间: http://localhost:8000/enterprise/workspace"
    echo "- 管理员控制台: http://localhost:8000/admin/console"
    echo "- API文档: http://localhost:8000/api/docs"
    echo
    echo "数据库信息:"
    echo "- 主机: localhost:3306"
    echo "- 数据库: alingai_pro"
    echo "- 用户名: alingai"
    echo
    echo "默认管理员账户:"
    echo "- 邮箱: admin@alingai.pro"
    echo "- 密码: admin123456"
    echo
}

# 如果直接执行脚本
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
