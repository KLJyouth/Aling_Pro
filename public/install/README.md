# AlingAi Pro 5.0 安装指南

## 📋 安装前准备

### 系统要求
- **PHP**: 8.1 或更高版本
- **Web服务器**: Apache/Nginx
- **数据库**: SQLite/MySQL/PostgreSQL（任选其一）
- **扩展**: mbstring, pdo, curl, json, openssl

### 权限要求
确保以下目录具有写入权限：
```
storage/
public/uploads/
```

## 🚀 安装步骤

### 1. 下载和解压
将项目文件解压到您的Web服务器目录中。

### 2. 访问安装向导
在浏览器中访问：
```
http://your-domain.com/install/
```

### 3. 按照向导步骤操作

#### 步骤1: 欢迎页面
阅读安装说明和系统要求。

#### 步骤2: 系统检查
安装向导会自动检查：
- PHP版本兼容性
- 必需的PHP扩展
- 文件和目录权限
- 内存限制设置
- 数据库支持

#### 步骤3: 数据库配置
选择数据库类型并配置连接：

**SQLite（推荐用于小型部署）**
- 无需额外配置
- 数据库文件将自动创建

**MySQL**
```
主机: localhost
端口: 3306
数据库名: alingai_pro
用户名: your_username
密码: your_password
```

**PostgreSQL**
```
主机: localhost
端口: 5432
数据库名: alingai_pro
用户名: your_username
密码: your_password
```

#### 步骤4: 管理员设置
创建系统管理员账户：
- 用户名（3-20个字符）
- 邮箱地址
- 密码（至少8位）
- 站点名称
- 站点URL

#### 步骤5: 安装确认
检查所有配置，点击"开始安装"。

## 🔧 安装过程

安装向导将自动执行以下操作：

1. **创建配置文件** (.env)
   - 应用设置
   - 数据库连接
   - 安全密钥
   - API配置

2. **设置数据库**
   - 连接测试
   - 创建数据库（如需要）

3. **创建数据表**
   - 用户表
   - 聊天会话表
   - 消息表
   - 设置表
   - 文件表
   - 日志表

4. **创建管理员账户**
   - 加密密码存储
   - 分配管理员权限

5. **完成安装**
   - 创建安装锁文件
   - 初始化默认设置

## 📁 文件结构

安装完成后的主要文件结构：
```
AlingAi_pro/
├── public/
│   ├── install/          # 安装文件（建议删除）
│   ├── js/              # JavaScript文件
│   ├── assets/          # 静态资源
│   ├── index.html       # 主页
│   └── admin.html       # 管理后台
├── storage/
│   ├── database.db      # SQLite数据库（如使用）
│   ├── installed.lock   # 安装锁文件
│   ├── logs/           # 日志文件
│   └── uploads/        # 上传文件
├── .env                 # 配置文件
└── README.md           # 说明文档
```

## 🔒 安装后安全

### 1. 删除安装文件
为了安全，请删除 `public/install/` 目录：
```bash
rm -rf public/install/
```

### 2. 修改默认设置
- 更改管理员密码
- 配置OpenAI API密钥
- 设置邮件服务

### 3. 配置Web服务器
确保正确配置Web服务器规则，隐藏敏感文件。

## 🛠️ 配置选项

### 环境配置 (.env)
```env
# 应用设置
APP_NAME=AlingAi
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# 数据库设置
DB_CONNECTION=sqlite
DB_DATABASE=../storage/database.db

# OpenAI配置
OPENAI_API_KEY=your_api_key_here
OPENAI_MODEL=gpt-3.5-turbo

# 安全设置
JWT_SECRET=generated_secret_key
SESSION_LIFETIME=7200
```

### 系统设置
通过管理后台可以配置：
- 站点信息
- 用户权限
- API限制
- 缓存设置
- 邮件配置

## 🆘 故障排除

### 常见问题

**安装页面无法访问**
- 检查Web服务器配置
- 确认PHP正常运行
- 检查文件权限

**数据库连接失败**
- 验证数据库服务器状态
- 检查连接信息
- 确认数据库用户权限

**权限错误**
```bash
chmod -R 755 public/
chmod -R 777 storage/
```

**内存不足**
在 php.ini 中增加内存限制：
```ini
memory_limit = 256M
```

### 日志文件
检查以下日志获取详细错误信息：
- `storage/logs/app.log` - 应用日志
- `storage/logs/install.log` - 安装日志
- Web服务器错误日志

## 📞 技术支持

如果在安装过程中遇到问题：

1. 查看本文档的故障排除部分
2. 检查系统日志文件
3. 确认服务器环境符合要求
4. 联系技术支持团队

## 🔄 重新安装

如需重新安装：

1. 删除 `storage/installed.lock` 文件
2. 清空数据库（如果需要）
3. 删除 `.env` 配置文件
4. 重新访问安装向导

---

**注意**: 生产环境部署时，请务必按照安全最佳实践配置服务器和应用程序。
