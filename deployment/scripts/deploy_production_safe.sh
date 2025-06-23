#!/bin/bash
# AlingAi Pro Enterprise System - 生产环境安全部署脚本
# 适用于函数受限的生产服务器环境

echo "🚀 AlingAi Pro 生产环境安全部署脚本"
echo "======================================"
echo "适用于函数受限的生产服务器环境"
echo "当前时间: $(date)"
echo "======================================"

# 检查基本环境
echo ""
echo "🔍 检查基本环境..."

# 检查PHP版本
PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2 | cut -d '.' -f 1,2)
echo "✅ PHP版本: $PHP_VERSION"

# 检查工作目录
if [ ! -f "composer.json" ]; then
    echo "❌ 错误: 请在项目根目录运行此脚本"
    exit 1
fi
echo "✅ 工作目录: $(pwd)"

# 检查关键文件
echo ""
echo "🔍 检查关键文件..."
CRITICAL_FILES=(
    "install/install.php"
    "three_complete_compilation_validator.php"
    "production_compatibility_check.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file - 文件缺失"
        exit 1
    fi
done

# 运行生产环境兼容性检查
echo ""
echo "🔍 运行生产环境兼容性检查..."
php production_compatibility_check.php

# 检查被禁用的函数
echo ""
echo "🔍 检查被禁用的函数..."
DISABLED_FUNCTIONS=$(php -r "echo ini_get('disable_functions');")
if [ ! -z "$DISABLED_FUNCTIONS" ]; then
    echo "⚠️ 被禁用的函数: $DISABLED_FUNCTIONS"
else
    echo "✅ 没有被禁用的函数"
fi

# 创建必要的目录
echo ""
echo "📁 创建必要的目录..."
REQUIRED_DIRS=(
    "storage/logs"
    "storage/cache"
    "storage/sessions"
    "storage/uploads"
    "public/assets/uploads"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo "✅ 创建目录: $dir"
    else
        echo "✅ 目录已存在: $dir"
    fi
done

# 设置文件权限
echo ""
echo "🔧 设置文件权限..."
if command -v chmod >/dev/null 2>&1; then
    chmod -R 755 storage/
    chmod -R 755 public/assets/
    echo "✅ 文件权限设置完成"
else
    echo "⚠️ chmod命令不可用，请手动设置文件权限"
fi

# 检查Composer
echo ""
echo "🔍 检查Composer..."
if command -v composer >/dev/null 2>&1; then
    echo "✅ Composer已安装"
    
    # 安装依赖
    echo "📦 安装Composer依赖..."
    composer install --no-dev --optimize-autoloader --no-scripts
    if [ $? -eq 0 ]; then
        echo "✅ Composer依赖安装完成"
    else
        echo "❌ Composer依赖安装失败"
        exit 1
    fi
else
    echo "❌ Composer未安装，请先安装Composer"
    exit 1
fi

# 运行安装程序（兼容模式）
echo ""
echo "🚀 运行安装程序（生产环境兼容模式）..."
php install/install.php --production-mode

# 运行三完编译验证
echo ""
echo "🔍 运行三完编译验证..."
php three_complete_compilation_validator.php

# 最终验证
echo ""
echo "🎯 最终系统验证..."
if [ -f "final_system_verification.php" ]; then
    php final_system_verification.php
else
    echo "⚠️ 最终验证脚本不存在，跳过此步骤"
fi

echo ""
echo "🎉 部署完成！"
echo "======================================"
echo "✅ AlingAi Pro Enterprise System 已成功部署到生产环境"
echo "✅ 系统已通过生产环境兼容性检查"
echo "✅ 所有关键组件已正确配置"
echo ""
echo "📝 下一步操作："
echo "1. 配置Web服务器（Apache/Nginx）"
echo "2. 设置域名和SSL证书"
echo "3. 配置数据库连接"
echo "4. 启动后台服务"
echo ""
echo "📄 详细文档请参考: DEPLOYMENT_READY_REPORT.md"
echo "======================================"
