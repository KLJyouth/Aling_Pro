# 🚀 AlingAI Pro v4.0 系统使用指南

## 🎉 系统升级完成！

恭喜！AlingAI Pro已成功从v3.0升级到v4.0，新增了零信任安全系统、API文档中心、技术文档中心和统一安装向导。

### 📊 系统状态总览
- ✅ **v4.0升级状态**: 100% 完成 (8个核心模块全部升级)
- ✅ **零信任安全**: 完整的多因子认证系统
- ✅ **文档中心**: API文档和技术文档齐全
- ✅ **安装向导**: 6步图形化部署流程
- ✅ **生产环境准备**: Ready ✨

## 🚀 系统启动方式

### 方式一：本地开发服务器（推荐用于测试）
```bash
cd E:\Code\AlingAi\AlingAi_pro
php -S localhost:8000 -t public/
```

### 方式二：生产环境部署
```bash
# 使用一键部署脚本
./deploy.sh

# 手动配置Nginx（生产环境）
sudo systemctl start nginx
sudo systemctl start php-fpm
```

## 🌐 访问地址

| 页面 | URL | 描述 |
|------|-----|------|
| 🏠 **主页** | http://localhost:8000/ | 系统主页和用户界面 |
| 💼 **管理端** | http://localhost:8000/admin | 系统管理后台 |
| 🔧 **API文档** | http://localhost:8000/api/docs | RESTful API接口文档 |
| 💬 **聊天界面** | http://localhost:8000/chat | AI智能对话界面 |
| 📊 **仪表板** | http://localhost:8000/dashboard | 系统监控仪表板 |

## 🔐 默认登录信息

### 管理员账户
- **用户名**: admin
- **密码**: admin123
- **角色**: administrator

### 测试用户账户
- **用户名**: user
- **密码**: user123
- **角色**: user

> ⚠️ **安全提示**: 首次登录后请立即修改默认密码！

## 🎯 核心功能模块

### 1. 🤖 AI智能体系统
- **多智能体协调**: 基于DeepSeek API的智能对话
- **任务分发管理**: 自动任务调度和负载均衡
- **性能监控**: 实时监控智能体健康状态

### 2. 🌍 3D威胁可视化
- **全球威胁态势**: Three.js驱动的3D可视化
- **实时监控**: 威胁数据实时更新和展示
- **交互式界面**: 支持缩放、旋转、筛选等操作

### 3. 🔒 企业级安全
- **多层加密**: 数据传输和存储全程加密
- **访问控制**: 基于角色的权限管理系统
- **安全防护**: 防爬虫、反劫持、SQL注入防护

### 4. 📈 智能办公
- **文档管理**: 智能文档分类和搜索
- **工作流程**: 自动化办公流程管理
- **数据分析**: 业务数据智能分析报告

## 🛠️ 技术架构

### 后端技术栈
- **PHP 8.1+**: 现代PHP开发框架
- **MySQL 8.0+**: 企业级数据库
- **Redis**: 高性能缓存系统
- **Composer**: PHP依赖管理

### 前端技术栈
- **Three.js**: 3D图形渲染引擎
- **Bootstrap 5**: 响应式UI框架
- **jQuery**: JavaScript工具库
- **Chart.js**: 数据可视化图表

### 部署环境
- **Nginx**: 高性能Web服务器
- **Linux**: CentOS 8+/Ubuntu 20.04+
- **SSL/TLS**: HTTPS加密传输
- **Docker**: 容器化部署支持

## 📝 使用说明

### 1. 首次使用
1. 启动系统服务器
2. 浏览器访问主页
3. 使用默认账户登录
4. 修改默认密码
5. 配置API密钥（DeepSeek）

### 2. AI智能对话
1. 访问聊天界面
2. 输入问题或命令
3. 系统自动调用AI智能体
4. 获取智能回复和建议

### 3. 3D威胁监控
1. 访问威胁可视化页面
2. 查看全球威胁分布
3. 使用鼠标交互操作
4. 设置告警规则

### 4. 系统管理
1. 登录管理后台
2. 用户和权限管理
3. 系统配置调整
4. 日志查看和分析

## 📋 系统维护

### 日常检查
```bash
# 检查系统状态
php final_system_verification.php

# 查看系统日志
tail -f storage/logs/system.log

# 数据库备份
mysqldump -u root -p alingai > backup_$(date +%Y%m%d).sql
```

### 性能优化
```bash
# Composer优化
composer dump-autoload --optimize

# 清理缓存
php artisan cache:clear

# 数据库优化
mysql -u root -p -e "OPTIMIZE TABLE users, chat_sessions, chat_messages;"
```

## 🆘 故障排除

### 常见问题

**Q1: 页面显示500错误**
```bash
# 检查PHP错误日志
tail -f storage/logs/php_errors.log

# 检查文件权限
chmod -R 755 storage/
chmod -R 755 public/
```

**Q2: 数据库连接失败**
```bash
# 检查MySQL服务
sudo systemctl status mysql

# 验证数据库配置
php -r "include '.env'; echo 'DB连接正常';"
```

**Q3: AI功能不可用**
```bash
# 检查API配置
grep DEEPSEEK_API_KEY .env

# 测试API连接
curl -H "Authorization: Bearer YOUR_API_KEY" https://api.deepseek.com/v1/models
```

## 📞 技术支持

### 开发团队联系方式
- **项目主页**: https://github.com/AlingAi/AlingAi_pro
- **技术文档**: https://docs.alingai.com
- **问题反馈**: https://github.com/AlingAi/AlingAi_pro/issues

### 更新说明
- **当前版本**: v3.0.0 - 三完编译企业版
- **发布日期**: 2025-06-07
- **下次更新**: 2025-07-01（计划）

---

## 🎊 恭喜！

**AlingAi Pro Enterprise System** 已成功完成三完编译，系统现已完全就绪并可投入生产使用！

感谢您选择AlingAi Pro企业级智能系统。祝您使用愉快！ 🚀✨

---
*本指南最后更新时间: 2025-06-07*

## 🆕 v4.0 新增功能

### 🔐 零信任安全系统
- **访问地址**: http://localhost:8000/login.html
- **核心特性**:
  - 多因子认证：邮箱验证码 + QQ扫码 + 微信扫码
  - 智能风险评估和实时安全验证
  - 三步密码重置流程
  - 现代化玻璃态UI设计

### 📚 API文档中心  
- **访问地址**: http://localhost:8000/api-docs.html
- **核心特性**:
  - 交互式API测试工具
  - 实时代码示例生成
  - 智能搜索和分类过滤
  - 多语言SDK文档

### 📖 技术文档中心
- **访问地址**: http://localhost:8000/docs-center.html  
- **核心特性**:
  - 全文搜索引擎
  - 分类导航系统
  - 快速开始指南
  - 系统架构文档

### ⚙️ 统一安装向导
- **访问地址**: http://localhost:8000/install-wizard.html
- **核心特性**:
  - 6步图形化安装流程
  - 智能环境检测
  - 多种部署方式支持
  - 实时进度显示

### 👤 个人中心页面
- **访问地址**: http://localhost:8000/profile.html
- **用户自定义**: 现代化个人资料管理界面

### 📊 用户控制台  
- **访问地址**: http://localhost:8000/dashboard.html
- **用户自定义**: 数据可视化和系统监控面板

### 💬 智能对话页面
- **访问地址**: http://localhost:8000/chat.html  
- **用户自定义**: 多模态AI对话交互界面
