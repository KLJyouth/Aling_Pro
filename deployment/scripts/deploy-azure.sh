#!/bin/bash

# AlingAi Pro Azure部署脚本
# 遵循Azure最佳实践，包含完整的部署验证和监控设置

echo "🚀 开始AlingAi Pro Azure部署..."
echo "========================================"

# 检查必要工具
check_tools() {
    echo "🔍 检查必要工具..."
    
    if ! command -v az &> /dev/null; then
        echo "❌ Azure CLI未安装，请先安装Azure CLI"
        exit 1
    fi
    
    if ! command -v azd &> /dev/null; then
        echo "❌ Azure Developer CLI未安装，请先安装azd"
        exit 1
    fi
    
    echo "✅ 工具检查完成"
}

# 登录Azure
login_azure() {
    echo "🔐 检查Azure登录状态..."
    
    if ! az account show &> /dev/null; then
        echo "请登录Azure..."
        az login
    fi
    
    # 显示当前账户信息
    echo "当前Azure账户："
    az account show --query "{name:name, id:id, tenantId:tenantId}" -o table
    
    echo "✅ Azure登录验证完成"
}

# 设置环境变量
setup_environment() {
    echo "⚙️  设置部署环境..."
    
    # 从用户输入获取敏感信息
    read -s -p "请输入数据库管理员密码: " DB_PASSWORD
    echo
    read -s -p "请输入DeepSeek API密钥: " DEEPSEEK_API_KEY
    echo
    read -s -p "请输入SMTP邮件密码: " SMTP_PASSWORD
    echo
    
    # 导出环境变量
    export DB_PASSWORD="$DB_PASSWORD"
    export DEEPSEEK_API_KEY="$DEEPSEEK_API_KEY"
    export SMTP_PASSWORD="$SMTP_PASSWORD"
    
    echo "✅ 环境变量设置完成"
}

# 初始化azd项目
init_azd() {
    echo "🛠️  初始化Azure Developer CLI项目..."
    
    # 初始化azd环境
    azd init --environment prod
    
    echo "✅ azd项目初始化完成"
}

# 预览部署
preview_deployment() {
    echo "👀 预览部署计划..."
    
    # 使用azd预览部署
    azd provision --preview
    
    # 等待用户确认
    read -p "请检查上述部署计划。是否继续部署？(y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ 用户取消部署"
        exit 1
    fi
    
    echo "✅ 部署计划确认完成"
}

# 执行部署
deploy() {
    echo "🚀 开始部署到Azure..."
    
    # 使用azd进行完整部署
    azd up --environment prod
    
    if [ $? -eq 0 ]; then
        echo "✅ Azure基础设施部署成功"
    else
        echo "❌ Azure基础设施部署失败"
        exit 1
    fi
}

# 部署后配置
post_deployment_config() {
    echo "⚙️  执行部署后配置..."
    
    # 获取Web App名称
    WEB_APP_NAME=$(az webapp list --query "[?contains(name, 'alingai-pro-prod')].name" -o tsv)
    
    if [ -z "$WEB_APP_NAME" ]; then
        echo "❌ 无法找到Web App"
        exit 1
    fi
    
    echo "找到Web App: $WEB_APP_NAME"
    
    # 部署应用代码
    echo "📦 部署应用代码..."
    az webapp deployment source config-zip \
        --name "$WEB_APP_NAME" \
        --resource-group "rg-alingai-pro-prod" \
        --src deployment.zip
    
    # 运行数据库迁移
    echo "🗄️  执行数据库迁移..."
    az webapp ssh --name "$WEB_APP_NAME" --resource-group "rg-alingai-pro-prod" \
        --command "cd /home/site/wwwroot && php database_management.php migrate"
    
    echo "✅ 部署后配置完成"
}

# 验证部署
verify_deployment() {
    echo "🧪 验证部署..."
    
    # 获取Web App URL
    WEB_APP_URL=$(az webapp show --name "$WEB_APP_NAME" --resource-group "rg-alingai-pro-prod" \
        --query "defaultHostName" -o tsv)
    
    echo "应用URL: https://$WEB_APP_URL"
    
    # 健康检查
    echo "执行健康检查..."
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$WEB_APP_URL/api/system/status")
    
    if [ "$HTTP_CODE" -eq 200 ]; then
        echo "✅ 应用健康检查通过"
    else
        echo "❌ 应用健康检查失败 (HTTP $HTTP_CODE)"
        exit 1
    fi
    
    echo "✅ 部署验证完成"
}

# 设置监控
setup_monitoring() {
    echo "📊 设置应用监控..."
    
    # 获取Application Insights密钥
    INSTRUMENTATION_KEY=$(az monitor app-insights component show \
        --app "alingai-pro-prod-insights" \
        --resource-group "rg-alingai-pro-prod" \
        --query "instrumentationKey" -o tsv)
    
    echo "Application Insights密钥: $INSTRUMENTATION_KEY"
    
    # 配置应用设置
    az webapp config appsettings set \
        --name "$WEB_APP_NAME" \
        --resource-group "rg-alingai-pro-prod" \
        --settings "APPINSIGHTS_INSTRUMENTATIONKEY=$INSTRUMENTATION_KEY"
    
    echo "✅ 监控设置完成"
}

# 显示部署结果
show_results() {
    echo "🎉 部署完成！"
    echo "========================================"
    echo "应用URL: https://$WEB_APP_URL"
    echo "管理门户: https://portal.azure.com"
    echo ""
    echo "下一步操作:"
    echo "1. 配置自定义域名和SSL证书"
    echo "2. 设置备份策略"
    echo "3. 配置监控警报"
    echo "4. 进行负载测试"
    echo ""
    echo "部署日志已保存到: deployment.log"
}

# 主执行流程
main() {
    # 将输出同时保存到日志文件
    exec > >(tee -a deployment.log)
    exec 2>&1
    
    echo "部署开始时间: $(date)"
    
    check_tools
    login_azure
    setup_environment
    init_azd
    preview_deployment
    deploy
    post_deployment_config
    verify_deployment
    setup_monitoring
    show_results
    
    echo "部署完成时间: $(date)"
}

# 错误处理
set -e
trap 'echo "❌ 部署过程中发生错误，请检查日志"; exit 1' ERR

# 执行主流程
main "$@"
