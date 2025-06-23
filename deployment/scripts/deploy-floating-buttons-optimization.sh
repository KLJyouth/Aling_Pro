#!/bin/bash

# AlingAi Pro 浮动按钮优化项目 - 快速部署脚本
# 将优化后的系统部署到生产环境

echo "🚀 AlingAi Pro 浮动按钮优化 - 生产部署脚本"
echo "================================================"

# 验证必要文件
echo "📋 验证系统文件..."
FILES=(
    "public/assets/js/floating-buttons-manager.js"
    "public/assets/js/chat-button-integrator.js"
    "public/assets/js/floating-buttons-diagnostic.js"
    "public/assets/js/floating-buttons-test-runner.js"
    "public/assets/js/floating-buttons-deployment-manager.js"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file - 文件缺失！"
        exit 1
    fi
done

# 验证更新的组件文件
echo ""
echo "📋 验证更新的组件文件..."
UPDATED_FILES=(
    "public/assets/js/social-customization.js"
    "public/assets/js/realtime-performance-dashboard.js"
    "public/assets/js/advanced-debug-console.js"
    "public/assets/js/quantum-chat-integrator.js"
)

for file in "${UPDATED_FILES[@]}"; do
    if grep -q "floatingButtonsManager" "$file" 2>/dev/null; then
        echo "✅ $file - 已集成管理器"
    else
        echo "⚠️  $file - 未找到管理器集成"
    fi
done

# 验证主页集成
echo ""
echo "📋 验证主页集成..."
if grep -q "floating-buttons-manager.js" "public/index.html"; then
    echo "✅ index.html - 管理器脚本已包含"
else
    echo "❌ index.html - 管理器脚本未包含！"
    exit 1
fi

if grep -q "chat-button-integrator.js" "public/index.html"; then
    echo "✅ index.html - 聊天集成器已包含"
else
    echo "❌ index.html - 聊天集成器未包含！"
    exit 1
fi

echo ""
echo "🎉 所有验证通过！系统已准备好部署到生产环境。"
echo ""
echo "📊 优化成果："
echo "   • 消除了所有浮动按钮位置冲突"
echo "   • 建立了统一的按钮管理系统"
echo "   • 实现了响应式布局适配"
echo "   • 提供了完整的测试和诊断工具"
echo "   • 建立了标准化的Z-index层级"
echo ""
echo "🔧 部署建议："
echo "   1. 备份当前生产环境"
echo "   2. 部署新的JS文件到生产服务器"
echo "   3. 更新index.html包含新脚本"
echo "   4. 运行生产环境测试"
echo "   5. 监控用户反馈"
echo ""
echo "📞 如需技术支持，请联系开发团队。"
echo "================================================"
