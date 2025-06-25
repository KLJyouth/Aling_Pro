# AlingAi Pro 安装指南

## 1. 系统要求

### 硬件需求
- CPU: 最低4核，建议8核或更高
- 内存: 最低8GB，建议16GB或更高
- 存储: 最低50GB可用空间，SSD存储推荐
- 网络: 稳定的互联网连接

### 软件需求
- **操作系统**: Linux (推荐 Ubuntu 20.04 LTS 或更高版本)
- **Web服务器**: 
  - Apache 2.4+ 或 
  - Nginx 1.18+
- **PHP**: 8.1+，含以下扩展:
  - bcmath
  - ctype
  - curl
  - dom
  - fileinfo
  - json
  - mbstring
  - openssl
  - pcre
  - PDO
  - pdo_mysql 或 pdo_sqlite
  - tokenizer
  - xml
  - zip
- **数据库**:
  - MySQL 8.0+ 或
  - MariaDB 10.5+ 或
  - SQLite 3.8.8+
- **其他依赖**:
  - Composer 2.0+
  - Git 2.25+

## 2. 安装步骤

### 2.1 下载系统文件

**方法 1: 使用 Git 克隆**

```bash
# 克隆代码库
git clone https://github.com/yourusername/alingai_pro.git

# 进入项目目录
cd alingai_pro
```

**方法 2: 下载安装包**

从官方网站下载最新版安装包，然后解压:

```bash
wget https://example.com/alingai_pro_latest.zip
unzip alingai_pro_latest.zip
cd alingai_pro
```

### 2.2 配置Web服务器

#### Apache 配置

创建虚拟主机配置文件 `/etc/apache2/sites-available/alingai.conf`:

```
<VirtualHost *:80>
    ServerName alingai.yourdomain.com
    DocumentRoot /var/www/alingai_pro/public

    <Directory /var/www/alingai_pro/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/alingai_error.log
    CustomLog ${APACHE_LOG_DIR}/alingai_access.log combined
</VirtualHost>
```

启用配置并重启Apache:

```bash
sudo a2ensite alingai.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx 配置

创建配置文件 `/etc/nginx/sites-available/alingai`:

```
server {
    listen 80;
    server_name alingai.yourdomain.com;
    root /var/www/alingai_pro/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

启用配置并重启Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/alingai /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

### 2.3 设置目录权限

确保Web服务器能够写入必要的目录:

```bash
# 假设Web服务器运行为www-data用户
sudo chown -R www-data:www-data /var/www/alingai_pro/storage
sudo chown -R www-data:www-data /var/www/alingai_pro/public/uploads
sudo chown -R www-data:www-data /var/www/alingai_pro/bootstrap/cache

# 设置适当的权限
sudo chmod -R 775 /var/www/alingai_pro/storage
sudo chmod -R 775 /var/www/alingai_pro/public/uploads
sudo chmod -R 775 /var/www/alingai_pro/bootstrap/cache
```

### 2.4 安装依赖

使用Composer安装PHP依赖:

```bash
cd /var/www/alingai_pro
composer install --no-dev --optimize-autoloader
```

### 2.5 通过Web界面安装

访问 `http://alingai.yourdomain.com/install/` 开始Web安装向导。

1. 欢迎页面 - 点击"开始安装"
2. 环境检查 - 系统会检查所需的PHP版本和扩展
3. 数据库设置 - 配置数据库连接信息
4. 管理员账户设置 - 创建管理员用户
5. 完成安装

### 2.6 通过命令行安装 (可选)

如果您更喜欢使用命令行安装，可以使用以下命令:

```bash
cd /var/www/alingai_pro
php public/install/install.php --db-type=mysql --db-host=localhost --db-port=3306 --db-name=alingai_pro --db-user=root --db-password=your_password --admin-name=admin --admin-email=admin@example.com --admin-password=your_password
```

## 3. 数据库导入和管理

### 3.1 使用Web界面导入数据库

1. 访问 `http://alingai.yourdomain.com/install/import-database.php`
2. 选择数据库类型 (MySQL 或 SQLite)
3. 填写数据库连接信息
4. 设置SQL文件路径 (默认为 `../database/schema.sql`)
5. 点击"开始导入"按钮

### 3.2 使用命令行导入数据库

```bash
# MySQL数据库导入
cd /var/www/alingai_pro
php public/install/import-database.php --mysql --host=localhost --user=root --password=your_password --database=alingai_pro

# SQLite数据库导入
php public/install/import-database.php --sqlite --database=/var/www/alingai_pro/database/alingai.sqlite
```

### 3.3 数据库备份

创建定期备份脚本 `/var/www/alingai_pro/scripts/backup_db.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/alingai"
DATE=$(date +%Y%m%d_%H%M%S)
MYSQL_USER="root"
MYSQL_PASSWORD="your_password"
DATABASE="alingai_pro"

# 创建备份目录
mkdir -p $BACKUP_DIR

# 数据库备份
mysqldump -u$MYSQL_USER -p$MYSQL_PASSWORD $DATABASE | gzip > $BACKUP_DIR/alingai_$DATE.sql.gz

# 保留最近30天的备份
find $BACKUP_DIR -name "alingai_*.sql.gz" -type f -mtime +30 -delete
```

设置权限并添加到定时任务:

```bash
sudo chmod +x /var/www/alingai_pro/scripts/backup_db.sh

# 添加到crontab, 每天凌晨3点执行
(crontab -l 2>/dev/null; echo "0 3 * * * /var/www/alingai_pro/scripts/backup_db.sh") | crontab -
```

## 4. 配置Linux服务器自动启动

### 4.1 安装服务脚本

AlingAi Pro 提供了一个服务控制脚本，可以配置为系统服务自动启动和关闭:

```bash
# 复制服务脚本到系统目录
sudo cp /var/www/alingai_pro/scripts/alingai_service.sh /etc/init.d/alingai

# 设置执行权限
sudo chmod +x /etc/init.d/alingai

# 配置为系统服务
sudo update-rc.d alingai defaults
```

### 4.2 手动控制服务

安装完成后，可以使用以下命令手动控制服务:

```bash
# 启动服务
sudo service alingai start

# 查看服务状态
sudo service alingai status

# 停止服务
sudo service alingai stop

# 重启服务
sudo service alingai restart
```

### 4.3 系统重启自动启动

配置完成后，AlingAi Pro 服务会在系统启动时自动运行，无需手动干预。

## 5. 安装后配置

### 5.1 安全设置

完成安装后，请执行以下安全设置:

1. 删除或限制访问安装目录:
   ```bash
   sudo rm -rf /var/www/alingai_pro/public/install
   # 或设置访问限制
   echo "Deny from all" > /var/www/alingai_pro/public/install/.htaccess
   ```

2. 确保敏感配置文件权限正确:
   ```bash
   sudo chmod 640 /var/www/alingai_pro/config/config.php
   ```

3. 定期更新系统和依赖:
   ```bash
   cd /var/www/alingai_pro
   composer update
   ```

### 5.2 性能优化

1. 启用PHP OPCache:
   ```
   # 编辑PHP配置文件 /etc/php/8.1/fpm/php.ini
   [opcache]
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.interned_strings_buffer=8
   opcache.max_accelerated_files=10000
   opcache.revalidate_freq=60
   opcache.save_comments=1
   ```

2. 配置缓存系统 (Redis 或 Memcached)
3. 启用Web服务器压缩

### 5.3 日志管理

配置 logrotate 管理日志文件:

```bash
sudo nano /etc/logrotate.d/alingai
```

添加以下内容:

```
/var/www/alingai_pro/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.1-fpm.service > /dev/null 2>/dev/null || true
    endscript
}
```

## 6. 故障排除

### 6.1 常见问题

1. **500 Internal Server Error**
   - 检查日志文件: `/var/www/alingai_pro/storage/logs/app.log`
   - 检查Web服务器日志: `/var/log/nginx/error.log` 或 `/var/log/apache2/error.log`
   - 确保目录权限正确
   - 验证PHP配置

2. **数据库连接错误**
   - 验证数据库服务是否运行: `sudo systemctl status mysql`
   - 检查数据库连接凭据
   - 确认用户权限

3. **页面显示空白**
   - 启用PHP错误显示 (仅测试环境)
   - 检查PHP错误日志
   - 验证PHP内存限制是否足够

### 6.2 如何获取支持

如果遇到难以解决的问题，可以通过以下方式获取支持:

1. 官方文档: `http://alingai.yourdomain.com/docs/`
2. 问题论坛: `https://community.alingai.com`
3. 提交错误报告: `https://github.com/yourusername/alingai_pro/issues`
4. 电子邮件支持: `support@alingai.com`

## 7. 升级指南

### 7.1 备份重要数据

在升级前，确保备份所有关键数据:

```bash
# 备份数据库
mysqldump -u root -p alingai_pro > alingai_backup.sql

# 备份配置文件
cp -r /var/www/alingai_pro/config /var/backups/alingai_config_backup

# 备份上传文件
cp -r /var/www/alingai_pro/public/uploads /var/backups/alingai_uploads_backup
```

### 7.2 执行升级

```bash
# 进入项目目录
cd /var/www/alingai_pro

# 如果使用Git管理代码
git pull origin main

# 更新依赖
composer install --no-dev --optimize-autoloader

# 清除缓存
php scripts/artisan cache:clear

# 更新数据库结构
php public/install/import-database.php --mysql --host=localhost --user=root --password=your_password --database=alingai_pro --sql=upgrade/update_to_latest.sql
```

### 7.3 验证升级

升级完成后，访问系统并检查以下内容:

1. 验证管理员登录功能
2. 检查主要功能是否正常工作
3. 确认数据是否完整
4. 查看系统日志是否有错误

## 8. 参考

- PHP 文档: https://www.php.net/docs.php
- MySQL 文档: https://dev.mysql.com/doc/
- Nginx 文档: https://nginx.org/en/docs/
- Apache 文档: https://httpd.apache.org/docs/
- AlingAi Pro GitHub: https://github.com/yourusername/alingai_pro