# 🚀 AlingAi Pro 5.0 - 生产环境部署快速指南

## 📋 部署前检查清单

### 🖥️ 服务器要求
- [ ] **操作系统**: Ubuntu 20.04+ / CentOS 8+ / Windows Server 2019+
- [ ] **PHP**: 8.1+ (含 opcache, redis, mysql, curl, mbstring, openssl 扩展)
- [ ] **Web服务器**: Nginx 1.18+ 或 Apache 2.4+
- [ ] **数据库**: MySQL 8.0+ 或 MariaDB 10.6+
- [ ] **缓存**: Redis 6.0+ (可选，推荐)
- [ ] **内存**: 最小 2GB，推荐 4GB+
- [ ] **磁盘**: 最小 10GB，推荐 50GB+

### 🔒 安全要求
- [ ] **SSL证书**: 有效的HTTPS证书
- [ ] **防火墙**: 仅开放必要端口 (80, 443, 22)
- [ ] **权限**: 适当的文件和目录权限
- [ ] **备份**: 自动备份策略

---

## 🚀 快速部署步骤

### 1️⃣ 服务器准备
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y
sudo apt install nginx php8.1-fpm php8.1-mysql php8.1-redis php8.1-curl php8.1-mbstring php8.1-xml mysql-server redis-server

# CentOS/RHEL
sudo yum update -y
sudo yum install nginx php81-fpm php81-mysql php81-redis mysql-server redis
```

### 2️⃣ 项目部署
```bash
# 下载项目代码
cd /var/www
sudo git clone https://github.com/your-org/alingai-pro.git
cd alingai-pro

# 安装依赖
sudo composer install --no-dev --optimize-autoloader

# 配置环境
sudo cp .env.production .env
sudo nano .env  # 编辑配置
```

### 3️⃣ 数据库设置
```sql
-- 创建数据库
CREATE DATABASE alingai_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alingai_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON alingai_pro.* TO 'alingai_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4️⃣ Web服务器配置
```bash
# Nginx配置
sudo cp nginx/alingai-pro.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/alingai-pro.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Apache配置 (如果使用Apache)
sudo cp public/.htaccess.production public/.htaccess
```

### 5️⃣ 权限设置
```bash
# 设置目录权限
sudo chown -R www-data:www-data /var/www/alingai-pro
sudo chmod -R 755 /var/www/alingai-pro
sudo chmod -R 775 /var/www/alingai-pro/storage
sudo chmod -R 775 /var/www/alingai-pro/cache
sudo chmod 600 /var/www/alingai-pro/.env
```

### 6️⃣ 监控设置
```bash
# 设置定时任务
sudo crontab -e

# 添加以下行：
*/5 * * * * /var/www/alingai-pro/bin/health-check.sh
0 2 * * * /var/www/alingai-pro/bin/backup.sh
0 3 * * * find /var/www/alingai-pro/storage/logs -name '*.log' -mtime +30 -delete
```

---

## 🔧 配置文件说明

### 📄 .env 主要配置项
```bash
# 应用基本信息
APP_NAME="AlingAi Pro 5.0"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 数据库配置
DB_HOST=127.0.0.1
DB_DATABASE=alingai_pro
DB_USERNAME=alingai_user
DB_PASSWORD=your_secure_password

# AI服务配置
OPENAI_API_KEY=your_openai_key
ANTHROPIC_API_KEY=your_anthropic_key

# 缓存配置
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
```

### 🌐 Nginx关键配置
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/alingai-pro/public;
    
    # SSL配置
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## ✅ 部署验证

### 🔍 功能测试
```bash
# 1. 访问主页
curl -I https://your-domain.com

# 2. API健康检查
curl https://your-domain.com/api/system/health

# 3. 运行系统检查
php scripts/project_integrity_checker.php

# 4. 性能测试
php scripts/performance_tester.php
```

### 📊 预期结果
- ✅ **HTTP状态**: 200 OK
- ✅ **响应时间**: < 50ms
- ✅ **健康分数**: > 85%
- ✅ **性能评分**: > 90/100

---

## 🛠️ 维护命令

### 📋 日常维护
```bash
# 查看系统状态
php scripts/final_system_validator.php

# 清理缓存
rm -rf storage/cache/*
rm -rf cache/*

# 查看日志
tail -f storage/logs/alingai.log

# 备份数据
./bin/backup.sh
```

### 🔧 故障排除
```bash
# 检查错误日志
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.1-fpm.log

# 重启服务
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
sudo systemctl restart redis
```

---

## 📞 技术支持

### 🆘 常见问题
1. **500错误**: 检查PHP错误日志和文件权限
2. **数据库连接失败**: 验证数据库配置和权限
3. **缓存问题**: 清理缓存目录和重启Redis
4. **性能问题**: 检查OPcache和系统资源

### 📚 资源链接
- **完整文档**: [项目Wiki](https://github.com/your-org/alingai-pro/wiki)
- **API文档**: [API参考](https://your-domain.com/docs/api)
- **问题反馈**: [GitHub Issues](https://github.com/your-org/alingai-pro/issues)

---

## 🎯 性能优化建议

### ⚡ 高性能配置
1. **启用OPcache**: `opcache.enable=1`
2. **增加内存限制**: `memory_limit=512M`
3. **优化MySQL**: 使用提供的 `mysql-optimization.cnf`
4. **配置Redis**: 启用持久化和优化内存
5. **CDN集成**: 配置静态资源CDN

### 📈 扩展建议
1. **负载均衡**: 多服务器部署时使用Nginx负载均衡
2. **容器化**: 考虑Docker部署提高可移植性
3. **监控集成**: 接入Prometheus + Grafana监控栈
4. **日志聚合**: 使用ELK栈统一日志管理

---

**部署完成后，记得运行全面的功能测试和性能测试！**

🎉 **祝您部署成功！** 🎉
