# AlingAI Pro v4.0 - 快速启动指南

## 🚀 快速开始

### 1. 启动开发服务器
```bash
# 在项目根目录运行
php -S localhost:8000 -t public
```

### 2. 访问系统
- **主页**: http://localhost:8000
- **API文档**: http://localhost:8000/api

### 3. 测试API端点

#### 用户注册
```bash
curl -X POST http://localhost:8000/api/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com", 
    "password": "SecurePass123!",
    "confirm_password": "SecurePass123!"
  }'
```

#### 用户登录
```bash
curl -X POST http://localhost:8000/api/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "SecurePass123!"
  }'
```

#### 获取用户信息（需要JWT令牌）
```bash
curl -X GET http://localhost:8000/api/user.php \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 📂 项目结构
```
AlingAI_pro/
├── public/           # Web根目录
├── src/             # 核心源代码
├── storage/         # 数据存储
└── config/          # 配置文件
```

## ⚙️ 配置
- 修改 `src/Config/api_config.php` 调整API设置
- 检查 `storage/data/` 目录权限
- 更新JWT密钥用于生产环境

## 🛠️ 开发
- API端点位于 `public/api/`
- 核心组件位于 `src/Core/`
- 前端资源位于 `public/assets/`

## 📝 文档
- 详细文档: `PROJECT_COMPLETION_REPORT_V4.md`
- API配置: `src/Config/api_config.php`
- 架构分析: `ARCHITECTURE_ANALYSIS.md`

---
**系统状态**: ✅ 已就绪  
**版本**: v4.0  
**最后更新**: 2025年6月9日
