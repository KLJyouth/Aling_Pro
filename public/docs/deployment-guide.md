# AlingAi Pro Azure部署指南

## 部署信息
- 应用名称: AlingAi Pro
- 版本: 2.0.0
- 环境: production
- PHP版本: 8.1
- 构建时间: 2025-06-04 11:33:33

## 部署步骤

### 1. 准备Azure资源
```bash
# 使用Bicep模板部署基础设施
az deployment group create \
  --resource-group rg-alingai-pro-prod \
  --template-file infra/main.bicep \
  --parameters infra/main.prod.parameters.json
```

### 2. 部署应用代码
```bash
# 使用Azure CLI部署
az webapp deployment source config-zip \
  --name alingai-pro-prod-webapp \
  --resource-group rg-alingai-pro-prod \
  --src deployment.zip
```

### 3. 配置环境变量
在Azure Portal中配置以下环境变量：
- APP_KEY: 应用密钥
- DB_PASSWORD: 数据库密码
- DEEPSEEK_API_KEY: AI服务密钥
- MAIL_PASSWORD: 邮件服务密码

### 4. 执行数据库迁移
```bash
# 通过SSH连接执行
az webapp ssh --name alingai-pro-prod-webapp --resource-group rg-alingai-pro-prod
cd /home/site/wwwroot
php database_management.php migrate
```

### 5. 验证部署
访问应用URL验证部署成功：
- 主页: https://your-app.azurewebsites.net
- 健康检查: https://your-app.azurewebsites.net/api/system/status

## 监控和维护

### Application Insights
- 应用性能监控已自动配置
- 查看仪表板: Azure Portal > Application Insights

### 日志查看
```bash
# 查看应用日志
az webapp log tail --name alingai-pro-prod-webapp --resource-group rg-alingai-pro-prod
```

### 备份和恢复
- 数据库自动备份已配置（7天保留）
- 应用文件需要手动备份

## 故障排除

### 常见问题
1. 数据库连接失败：检查防火墙规则和连接字符串
2. 文件上传失败：检查存储权限和配置
3. 邮件发送失败：验证SMTP配置

### 支持联系
- 技术支持: admin@gxggm.com
- 文档更新: 2025-06-04 11:33:33
