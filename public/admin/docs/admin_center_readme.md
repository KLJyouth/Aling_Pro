# AlingAi_pro 后台IT技术运维中心

这是AlingAi_pro项目的后台IT技术运维中心，提供了项目维护、系统监控、安全管理、运维报告和日志管理等功能。

## 功能模块

- **仪表盘**：显示系统状态、工具统计和最近日志
- **维护工具**：提供PHP代码修复、命名空间检查和编码修复等工具
- **系统监控**：监控服务器状态、PHP信息和数据库状态
- **安全管理**：用户、角色和权限管理
- **运维报告**：生成和查看运维报告
- **日志管理**：查看和管理系统日志

## 目录结构

```
admin-center/
├── app/                # 应用程序核心代码
│   ├── Core/           # 核心类库
│   └── Controllers/    # 控制器
├── config/             # 配置文件
├── public/             # 公共访问目录
├── resources/          # 资源文件
│   └── views/          # 视图文件
├── routes/             # 路由定义
├── storage/            # 存储目录
│   └── logs/           # 日志文件
├── tools/              # 维护工具
└── index.php           # 应用程序入口
```

## 安装和配置

1. 确保PHP版本 >= 7.4
2. 配置Web服务器（Apache/Nginx）指向 `public` 目录
3. 确保 `storage` 目录可写
4. 访问 `http://your-domain.com/` 进入系统

## Apache配置示例

```apache
<VirtualHost *:80>
    ServerName admin.your-domain.com
    DocumentRoot /path/to/admin-center/public
    
    <Directory /path/to/admin-center/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/admin-center-error.log
    CustomLog ${APACHE_LOG_DIR}/admin-center-access.log combined
</VirtualHost>
```

## Nginx配置示例

```nginx
server {
    listen 80;
    server_name admin.your-domain.com;
    root /path/to/admin-center/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

## 使用说明

1. 访问系统首页，默认进入仪表盘
2. 使用左侧导航菜单访问各功能模块
3. 维护工具中可以运行各种PHP代码修复和检查工具
4. 系统监控可以查看服务器和PHP的实时状态
5. 安全管理可以管理用户权限
6. 运维报告可以生成和查看各类报告
7. 日志管理可以查看和下载系统日志 
