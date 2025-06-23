#!/bin/bash

# AlingAI Pro 5.0 管理后台最终验证脚本

echo "=== AlingAI Pro 5.0 管理后台最终验证 ==="
echo "验证时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# 验证计数器
passed=0
total=0

# 检查函数
check_file() {
    total=$((total + 1))
    if [ -f "$1" ]; then
        echo "✅ $2: $1"
        passed=$((passed + 1))
    else
        echo "❌ $2: $1 (缺失)"
    fi
}

check_dir() {
    total=$((total + 1))
    if [ -d "$1" ]; then
        count=$(find "$1" -name "*.php" | wc -l)
        echo "✅ $2: $1 ($count 个文件)"
        passed=$((passed + 1))
    else
        echo "❌ $2: $1 (不存在)"
    fi
}

echo "🔍 检查管理后台核心文件:"
check_file "admin/index.php" "主入口文件"
check_file "admin/SystemManager.php" "系统管理器"
check_file "admin/login.php" "登录页面"
check_file "admin/download.php" "下载处理器"
check_file "admin/js/admin.js" "JavaScript文件"
check_file "admin/css/admin.css" "样式文件"

echo ""
echo "📦 检查备份目录:"
check_dir "backup/old_files/test_files" "测试文件备份"
check_dir "backup/old_files/check_files" "检查文件备份"
check_dir "backup/old_files/debug_files" "调试文件备份"
check_dir "backup/old_files/system_files" "系统文件备份"
check_dir "backup/old_files/error_files" "错误处理文件备份"

echo ""
echo "🔧 检查PHP语法:"
for file in "admin/index.php" "admin/SystemManager.php"; do
    total=$((total + 1))
    if php -l "$file" > /dev/null 2>&1; then
        echo "✅ PHP语法检查: $file"
        passed=$((passed + 1))
    else
        echo "❌ PHP语法检查: $file (有错误)"
    fi
done

echo ""
echo "📊 验证结果:"
echo "通过: $passed/$total"
echo "成功率: $(( passed * 100 / total ))%"

echo ""
if [ $passed -eq $total ]; then
    echo "🎉 所有验证项目通过!"
    echo "✅ 管理后台部署成功"
    echo "🌐 访问地址: http://localhost:8080/admin/"
    echo "🔑 默认密码: admin123"
    echo ""
    echo "📋 功能模块:"
    echo "• 系统概览 - 实时监控系统状态"
    echo "• 数据库管理 - 数据库检查和修复"
    echo "• 系统测试 - 整合所有测试功能"
    echo "• 健康检查 - 系统健康状态检查"
    echo "• 调试工具 - 系统调试和错误信息"
    echo "• 系统优化 - 缓存清理和性能优化"
    echo "• 日志管理 - 日志查看和导出"
    echo ""
    echo "🎊 AlingAI Pro 5.0 管理后台整合完成!"
else
    echo "⚠️ 部分验证项目失败，请检查上述错误"
    exit 1
fi
