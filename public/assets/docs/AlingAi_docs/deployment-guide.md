# 部署指南

## 环境要求
- Node.js v16+
- PostgreSQL 12+
- Redis 6+

## 安装步骤
1. `npm install` 安装依赖
2. 配置 `.env` 环境变量
3. 初始化数据库 `npm run migrate`

## 生产环境部署
- 使用 PM2 进程管理
- Nginx 反向代理配置
- 日志轮转设置