#!/bin/bash
# AlingAi Pro 快速启动脚本
# Quick Start Script for AlingAi Pro

echo "=========================================="
echo "  AlingAi Pro v2.0.0 快速启动"
echo "  三完编译版本 - 100%完成"
echo "=========================================="

# 检查PHP版本
echo "🔍 检查PHP环境..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "   PHP版本: $PHP_VERSION"

# 检查必要扩展
echo "🔍 检查PHP扩展..."
php -m | grep -q "pdo" && echo "   ✅ PDO扩展已安装" || echo "   ❌ PDO扩展未安装"
php -m | grep -q "json" && echo "   ✅ JSON扩展已安装" || echo "   ❌ JSON扩展未安装"
php -m | grep -q "curl" && echo "   ✅ CURL扩展已安装" || echo "   ❌ CURL扩展未安装"
php -m | grep -q "mbstring" && echo "   ✅ MBString扩展已安装" || echo "   ❌ MBString扩展未安装"

# 安装依赖
echo "📦 安装Composer依赖..."
composer install --no-dev --optimize-autoloader

# 设置权限
echo "🔐 设置文件权限..."
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env

# 检查配置文件
echo "⚙️ 检查配置文件..."
if [ -f ".env" ]; then
    echo "   ✅ .env配置文件存在"
else
    echo "   ❌ .env配置文件不存在，请复制.env.example"
fi

# 运行测试
echo "🧪 运行集成测试..."
php bin/integration-test.php

# 启动提示
echo ""
echo "🚀 启动说明:"
echo "   1. 配置Nginx指向public/目录"
echo "   2. 启动WebSocket服务: php bin/websocket-server.php"
echo "   3. 配置数据库连接"
echo "   4. 访问网站进行最终测试"
echo ""
echo "✅ AlingAi Pro 准备就绪！"
echo "=========================================="
