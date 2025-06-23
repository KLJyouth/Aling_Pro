#!/bin/bash

# AlingAI Pro 5.0 系统管理后台部署脚本
# 整合测试文件并部署到后台管理系统

echo "=== AlingAI Pro 5.0 系统管理后台部署 ==="
echo "开始整合测试文件并部署到后台管理系统..."

# 创建必要的目录
mkdir -p admin/js
mkdir -p admin/css
mkdir -p storage/exports
mkdir -p storage/logs

# 设置目录权限
chmod 755 admin
chmod 755 storage/exports
chmod 755 storage/logs
chmod 644 admin/*.php
chmod 644 admin/js/*.js

echo "✅ 目录结构创建完成"

# 移动并备份现有的测试文件
echo "📦 备份现有测试文件..."
mkdir -p backup/old_files
mkdir -p backup/old_files/test_files
mkdir -p backup/old_files/check_files
mkdir -p backup/old_files/debug_files
mkdir -p backup/old_files/system_files
mkdir -p backup/old_files/error_files

# 备份测试文件
for file in test_*.php; do
    if [ -f "$file" ]; then
        echo "备份: $file"
        mv "$file" backup/old_files/test_files/
    fi
done

# 备份检查文件
for file in check_*.php; do
    if [ -f "$file" ]; then
        echo "备份: $file"
        mv "$file" backup/old_files/check_files/
    fi
done

# 备份调试文件
for file in debug_*.php; do
    if [ -f "$file" ]; then
        echo "备份: $file"
        mv "$file" backup/old_files/debug_files/
    fi
done

# 备份系统文件
for file in system_*.php; do
    if [ -f "$file" ]; then
        echo "备份: $file"
        mv "$file" backup/old_files/system_files/
    fi
done

# 备份错误处理文件
for file in error_*.php; do
    if [ -f "$file" ]; then
        echo "备份: $file"
        mv "$file" backup/old_files/error_files/
    fi
done

# 备份其他相关文件
misc_files=(
    "fix_database_comprehensive.php"
    "ultimate_database_fix_v2.php"
    "final_system_validation.php"
    "three_complete_compilation_validator.php"
    "improved_health_check.php"
    "system_health_check.php"
)

for file in "${misc_files[@]}"; do
    if [ -f "$file" ]; then
        echo "备份: $file"
        mv "$file" backup/old_files/
    fi
done

echo "✅ 文件备份完成"

# 创建备份说明文件
cat > backup/old_files/README.md << 'EOF'
# 已整合文件备份

这些文件已经被整合到新的系统管理后台中：

## 测试文件 (test_files/)
- 所有 test_*.php 文件的功能已整合到后台的"系统测试"模块

## 检查文件 (check_files/)
- 所有 check_*.php 文件的功能已整合到后台的"健康检查"模块

## 调试文件 (debug_files/)
- 所有 debug_*.php 文件的功能已整合到后台的"调试工具"模块

## 系统文件 (system_files/)
- 所有 system_*.php 文件的功能已整合到后台的"系统概览"模块

## 错误处理文件 (error_files/)
- 所有 error_*.php 文件的功能已整合到后台的"调试工具"模块

## 其他文件
- 数据库修复工具已整合到后台的"数据库管理"模块
- 系统验证工具已整合到后台的"健康检查"模块

## 新的系统管理后台
访问 `/admin/` 目录使用统一的管理界面。

默认密码: admin123
EOF

echo "✅ 备份说明文件创建完成"

# 创建后台访问配置
cat > admin/.htaccess << 'EOF'
# 后台安全配置
DirectoryIndex index.php

# 防止直接访问敏感文件
<Files "SystemManager.php">
    Order allow,deny
    Deny from all
</Files>

<Files "download.php">
    Order allow,deny
    Allow from all
</Files>

# 启用GZIP压缩
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
EOF

echo "✅ 后台安全配置完成"

# 验证部署
echo "🔍 验证部署结果..."

if [ -f "admin/index.php" ]; then
    echo "✅ 主入口文件存在"
else
    echo "❌ 主入口文件缺失"
    exit 1
fi

if [ -f "admin/SystemManager.php" ]; then
    echo "✅ 系统管理器存在"
else
    echo "❌ 系统管理器缺失"
    exit 1
fi

if [ -f "admin/js/admin.js" ]; then
    echo "✅ JavaScript文件存在"
else
    echo "❌ JavaScript文件缺失"
    exit 1
fi

if [ -d "backup/old_files" ]; then
    echo "✅ 文件备份完成"
else
    echo "❌ 文件备份失败"
    exit 1
fi

echo ""
echo "=== 部署完成 ==="
echo "✅ 系统管理后台已成功部署"
echo "✅ 原有测试文件已备份到 backup/old_files/"
echo "✅ 新的管理后台位于 /admin/ 目录"
echo ""
echo "📖 使用说明:"
echo "1. 访问 http://yourdomain.com/admin/"
echo "2. 使用默认密码 'admin123' 登录"
echo "3. 建议立即修改管理员密码"
echo ""
echo "🔧 集成功能:"
echo "• 系统概览 - 实时监控系统状态"
echo "• 数据库管理 - 数据库连接检查和修复"
echo "• 系统测试 - 整合所有测试功能"
echo "• 健康检查 - 系统健康状态检查"
echo "• 调试工具 - 系统调试和错误信息"
echo "• 系统优化 - 缓存清理和性能优化"
echo "• 日志管理 - 日志查看和导出"
echo ""
echo "🎉 AlingAI Pro 5.0 系统管理后台部署完成！"
