#!/bin/bash

# AlingAi 项目完整构建和部署脚本
# 用于生产环境的一键部署

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# 日志函数
log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

log_info() {
    echo -e "${CYAN}[INFO]${NC} $1"
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

# 项目配置
PROJECT_NAME="AlingAi"
PROJECT_VERSION="1.0.0"
BUILD_DIR="$(pwd)"
DIST_DIR="$BUILD_DIR/dist"
BACKUP_DIR="$BUILD_DIR/backups"

# 显示项目信息
show_project_info() {
    echo -e "${PURPLE}"
    echo "========================================"
    echo "       AlingAi 项目构建部署工具"
    echo "========================================"
    echo -e "${NC}"
    echo "项目名称: $PROJECT_NAME"
    echo "版本: $PROJECT_VERSION"
    echo "构建目录: $BUILD_DIR"
    echo "输出目录: $DIST_DIR"
    echo ""
}

# 检查系统要求
check_requirements() {
    log_step "检查系统要求"
    
    # 检查PHP
    if command -v php >/dev/null 2>&1; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        log_success "PHP: $PHP_VERSION"
    else
        log_error "PHP未安装"
        exit 1
    fi
    
    # 检查Composer
    if command -v composer >/dev/null 2>&1; then
        COMPOSER_VERSION=$(composer --version | grep -o '[0-9]\+\.[0-9]\+\.[0-9]\+')
        log_success "Composer: $COMPOSER_VERSION"
    else
        log_error "Composer未安装"
        exit 1
    fi
    
    # 检查Node.js
    if command -v node >/dev/null 2>&1; then
        NODE_VERSION=$(node --version)
        log_success "Node.js: $NODE_VERSION"
    else
        log_warning "Node.js未安装，将跳过前端构建"
    fi
    
    # 检查Git
    if command -v git >/dev/null 2>&1; then
        GIT_VERSION=$(git --version)
        log_success "Git: $GIT_VERSION"
    else
        log_warning "Git未安装"
    fi
}

# 准备构建环境
prepare_build_env() {
    log_step "准备构建环境"
    
    # 创建必要目录
    mkdir -p "$DIST_DIR"
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$BUILD_DIR/storage/logs"
    mkdir -p "$BUILD_DIR/storage/cache"
    mkdir -p "$BUILD_DIR/storage/sessions"
    mkdir -p "$BUILD_DIR/storage/uploads"
    mkdir -p "$BUILD_DIR/storage/backups"
    
    log_success "目录结构创建完成"
}

# 安装依赖
install_dependencies() {
    log_step "安装项目依赖"
    
    # 安装PHP依赖
    log_info "安装PHP依赖..."
    composer install --no-dev --optimize-autoloader --no-interaction
    log_success "PHP依赖安装完成"
    
    # 安装Node.js依赖（如果存在package.json）
    if [ -f "package.json" ]; then
        log_info "安装Node.js依赖..."
        npm install --production
        log_success "Node.js依赖安装完成"
    fi
}

# 构建前端资源
build_frontend() {
    log_step "构建前端资源"
    
    if [ -f "package.json" ]; then
        # 开发环境构建
        if [ "$1" = "dev" ]; then
            log_info "构建开发环境前端资源..."
            npm run dev
        else
            log_info "构建生产环境前端资源..."
            npm run build
        fi
        log_success "前端资源构建完成"
    else
        log_warning "未找到package.json，跳过前端构建"
    fi
}

# 优化代码
optimize_code() {
    log_step "优化代码"
    
    # 清理不必要的文件
    log_info "清理临时文件..."
    find . -name "*.log" -type f -delete
    find . -name ".DS_Store" -type f -delete
    find . -name "Thumbs.db" -type f -delete
    
    # 优化Composer自动加载
    log_info "优化Composer自动加载..."
    composer dump-autoload --optimize --no-dev
    
    # 压缩CSS和JS文件（如果存在）
    if command -v uglifyjs >/dev/null 2>&1; then
        log_info "压缩JavaScript文件..."
        find public/js -name "*.js" ! -name "*.min.js" -exec uglifyjs {} -o {}.min.js \;
    fi
    
    log_success "代码优化完成"
}

# 配置环境
configure_environment() {
    log_step "配置环境"
    
    # 复制环境配置文件
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            cp .env.production .env
            log_success "生产环境配置文件已复制"
        elif [ -f ".env.example" ]; then
            cp .env.example .env
            log_warning "已复制示例配置文件，请手动配置"
        fi
    fi
    
    # 生成应用密钥
    if grep -q "APP_KEY=$" .env 2>/dev/null; then
        APP_KEY=$(openssl rand -base64 32)
        sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" .env
        log_success "应用密钥已生成"
    fi
    
    # 设置文件权限
    chmod 644 .env
    chmod -R 755 storage
    chmod -R 755 public/uploads
    
    log_success "环境配置完成"
}

# 数据库设置
setup_database() {
    log_step "设置数据库"
    
    # 检查数据库连接
    log_info "检查数据库连接..."
    php -r "
    require 'vendor/autoload.php';
    use AlingAi\Database\DatabaseManager;
    try {
        \$db = DatabaseManager::getInstance();
        \$connection = \$db->getConnection();
        echo 'Database connection: OK\n';
    } catch (Exception \$e) {
        echo 'Database connection failed: ' . \$e->getMessage() . '\n';
        exit(1);
    }
    "
    
    # 运行数据库迁移
    log_info "运行数据库迁移..."
    php database/migrate.php
    
    log_success "数据库设置完成"
}

# 运行测试
run_tests() {
    log_step "运行测试"
    
    if [ -f "phpunit.xml" ]; then
        log_info "运行PHPUnit测试..."
        vendor/bin/phpunit --configuration phpunit.xml
        log_success "测试完成"
    else
        log_warning "未找到测试配置，跳过测试"
    fi
}

# 生成文档
generate_docs() {
    log_step "生成文档"
    
    # 生成API文档
    log_info "生成API文档..."
    php -r "
    require 'vendor/autoload.php';
    use AlingAi\Services\APIDocumentationGenerator;
    \$generator = new APIDocumentationGenerator();
    // 这里需要注册实际的路由和模型
    \$generator->saveDocumentation('html');
    \$generator->saveDocumentation('json');
    echo 'API documentation generated\n';
    "
    
    log_success "文档生成完成"
}

# 创建发布包
create_release_package() {
    log_step "创建发布包"
    
    RELEASE_NAME="alingai-v${PROJECT_VERSION}-$(date +%Y%m%d%H%M%S)"
    RELEASE_DIR="$DIST_DIR/$RELEASE_NAME"
    
    # 创建发布目录
    mkdir -p "$RELEASE_DIR"
    
    # 复制项目文件
    log_info "复制项目文件..."
    
    # 复制核心文件
    cp -r src "$RELEASE_DIR/"
    cp -r public "$RELEASE_DIR/"
    cp -r resources "$RELEASE_DIR/"
    cp -r config "$RELEASE_DIR/"
    cp -r database "$RELEASE_DIR/"
    cp -r vendor "$RELEASE_DIR/"
    cp -r bin "$RELEASE_DIR/"
    cp -r scripts "$RELEASE_DIR/"
    cp -r nginx "$RELEASE_DIR/"
    cp -r docs "$RELEASE_DIR/"
    
    # 复制配置文件
    cp composer.json "$RELEASE_DIR/"
    cp composer.lock "$RELEASE_DIR/"
    cp .env.production "$RELEASE_DIR/"
    cp phpunit.xml "$RELEASE_DIR/"
    cp README.md "$RELEASE_DIR/"
    
    # 创建存储目录
    mkdir -p "$RELEASE_DIR/storage/logs"
    mkdir -p "$RELEASE_DIR/storage/cache"
    mkdir -p "$RELEASE_DIR/storage/sessions"
    mkdir -p "$RELEASE_DIR/storage/uploads"
    mkdir -p "$RELEASE_DIR/storage/backups"
    
    # 设置权限
    chmod -R 755 "$RELEASE_DIR"
    chmod -R 777 "$RELEASE_DIR/storage"
    
    # 创建压缩包
    log_info "创建压缩包..."
    cd "$DIST_DIR"
    tar -czf "${RELEASE_NAME}.tar.gz" "$RELEASE_NAME"
    
    # 创建校验文件
    sha256sum "${RELEASE_NAME}.tar.gz" > "${RELEASE_NAME}.tar.gz.sha256"
    
    cd "$BUILD_DIR"
    
    log_success "发布包创建完成: $DIST_DIR/${RELEASE_NAME}.tar.gz"
}

# 部署到服务器
deploy_to_server() {
    log_step "部署到服务器"
    
    if [ -z "$DEPLOY_SERVER" ]; then
        log_warning "未设置DEPLOY_SERVER环境变量，跳过自动部署"
        return
    fi
    
    # 上传发布包
    log_info "上传发布包到服务器..."
    RELEASE_FILE=$(ls -t $DIST_DIR/*.tar.gz | head -1)
    scp "$RELEASE_FILE" "$DEPLOY_SERVER:/tmp/"
    
    # 执行远程部署脚本
    log_info "执行远程部署..."
    ssh "$DEPLOY_SERVER" "bash /path/to/remote/deploy.sh $(basename $RELEASE_FILE)"
    
    log_success "部署完成"
}

# 性能测试
performance_test() {
    log_step "性能测试"
    
    if command -v ab >/dev/null 2>&1; then
        log_info "运行Apache Bench测试..."
        ab -n 100 -c 10 http://localhost/
    fi
    
    log_success "性能测试完成"
}

# 清理构建文件
cleanup() {
    log_step "清理构建文件"
    
    # 清理临时文件
    rm -rf storage/cache/*
    rm -rf storage/logs/*.log
    
    # 清理开发依赖
    composer install --no-dev --no-interaction
    
    log_success "清理完成"
}

# 显示构建结果
show_build_result() {
    echo ""
    log_success "构建完成！"
    echo ""
    echo "构建信息:"
    echo "- 项目: $PROJECT_NAME v$PROJECT_VERSION"
    echo "- 构建时间: $(date)"
    echo "- 发布包: $(ls -t $DIST_DIR/*.tar.gz 2>/dev/null | head -1 | xargs basename)"
    echo ""
    echo "下一步操作:"
    echo "1. 测试发布包"
    echo "2. 部署到测试环境"
    echo "3. 执行验收测试"
    echo "4. 部署到生产环境"
    echo ""
}

# 主要构建流程
build_project() {
    local build_type="${1:-production}"
    
    show_project_info
    check_requirements
    prepare_build_env
    install_dependencies
    build_frontend "$build_type"
    optimize_code
    configure_environment
    setup_database
    run_tests
    generate_docs
    create_release_package
    cleanup
    show_build_result
}

# 完整部署流程
deploy_project() {
    build_project "production"
    deploy_to_server
    performance_test
}

# 命令行参数处理
case "${1:-build}" in
    "build")
        build_project "${2:-production}"
        ;;
    "deploy")
        deploy_project
        ;;
    "test")
        run_tests
        ;;
    "docs")
        generate_docs
        ;;
    "clean")
        cleanup
        ;;
    "help"|"--help"|"-h")
        echo "AlingAi 构建部署工具"
        echo ""
        echo "用法: $0 [command] [options]"
        echo ""
        echo "命令:"
        echo "  build [dev|production]  - 构建项目（默认: production）"
        echo "  deploy                  - 构建并部署项目"
        echo "  test                    - 运行测试"
        echo "  docs                    - 生成文档"
        echo "  clean                   - 清理构建文件"
        echo "  help                    - 显示帮助信息"
        echo ""
        ;;
    *)
        log_error "未知命令: $1"
        echo "使用 '$0 help' 查看帮助信息"
        exit 1
        ;;
esac
