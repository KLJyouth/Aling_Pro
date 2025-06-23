# AlingAi Pro 6.0 安装程序升级总结

## 概述

本文档总结了AlingAi Pro 6.0安装程序的升级内容。新的安装系统提供了更加友好的用户界面、更强大的系统检查功能和更安全的配置流程。

## 主要升级内容

### 1. 安装控制器

- **新增 `InstallController` 类**：提供统一的安装流程控制
- **系统检查功能**：在安装前全面检查系统环境
- **安装步骤管理**：将安装流程拆分为多个步骤，便于管理和错误处理
- **安装锁定机制**：防止重复安装

### 2. 安装程序类

- **新增 `Installer` 类**：实现具体的安装步骤
- **数据库迁移**：支持MySQL和SQLite的表结构自动创建
- **配置文件生成**：自动生成环境配置文件
- **安全密钥生成**：在安装过程中自动生成加密密钥

### 3. 用户界面改进

- **响应式设计**：适应不同屏幕尺寸的安装界面
- **实时反馈**：安装过程中提供实时进度和错误反馈
- **分步安装**：将安装流程分为多个步骤，逐步完成

### 4. 安全性增强

- **安装前检查**：检查目录权限、PHP版本和扩展
- **密码强度验证**：对管理员密码进行强度检查
- **自动生成安全密钥**：为系统和JWT自动生成安全密钥
- **安装后保护**：自动创建.htaccess文件保护敏感目录

## 安装步骤详解

新的安装流程包括以下步骤：

1. **系统环境检查**：
   - 检查PHP版本（要求8.0.0或更高）
   - 检查必要的PHP扩展
   - 检查目录权限
   - 检查是否已安装

2. **创建配置文件**：
   - 生成.env配置文件
   - 配置应用基本信息
   - 配置数据库连接
   - 生成并配置加密密钥

3. **设置数据库**：
   - 连接到数据库服务器
   - 创建数据库（如果不存在）
   - 配置数据库连接参数

4. **创建数据表**：
   - 创建用户表
   - 创建设置表
   - 创建会话表
   - 创建API令牌表
   - 创建日志表
   - 创建钱包和交易表

5. **创建管理员账户**：
   - 创建具有管理员权限的用户
   - 设置管理员偏好设置

6. **设置加密系统**：
   - 生成JWT密钥
   - 配置API加密
   - 更新.env文件

7. **完成安装**：
   - 创建必要的目录结构
   - 设置目录保护
   - 创建安装锁定文件

## 数据库结构

新的安装程序支持以下数据表的自动创建：

### 用户表

存储用户信息，包括认证和权限数据。

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT "user",
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at DATETIME DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    2fa_secret VARCHAR(255) DEFAULT NULL,
    2fa_enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
)
```

### 设置表

存储系统和用户设置。

```sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    key_name VARCHAR(100) NOT NULL,
    value TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (key_name),
    INDEX (user_id)
)
```

### 会话表

存储用户会话信息。

```sql
CREATE TABLE sessions (
    id VARCHAR(100) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    payload TEXT,
    last_activity DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX (user_id)
)
```

### API令牌表

存储API访问令牌。

```sql
CREATE TABLE api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (user_id)
)
```

### 日志表

存储系统日志。

```sql
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    created_at DATETIME NOT NULL,
    INDEX (user_id),
    INDEX (level)
)
```

### 钱包表

存储用户钱包信息。

```sql
CREATE TABLE wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) NOT NULL DEFAULT "CNY",
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (user_id)
)
```

### 交易表

存储钱包交易记录。

```sql
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    status VARCHAR(50) NOT NULL DEFAULT "completed",
    reference VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (wallet_id),
    INDEX (user_id),
    INDEX (type),
    INDEX (status)
)
```

## 环境配置

安装程序会生成包含以下配置的.env文件：

```
# 应用配置
APP_NAME="AlingAi Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_VERSION=6.0.0

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=alingai
DB_USERNAME=root
DB_PASSWORD=
DB_PREFIX=

# 缓存配置
CACHE_DRIVER=file
CACHE_PREFIX=alingai_

# 日志配置
LOG_CHANNEL=file
LOG_LEVEL=warning

# 会话配置
SESSION_DRIVER=file
SESSION_LIFETIME=120

# 邮件配置
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

# 加密配置
SYSTEM_ENCRYPTION_KEY=<自动生成的密钥>
SYSTEM_ENCRYPTION_IV=<自动生成的IV>

# JWT配置
JWT_SECRET=<自动生成的JWT密钥>
JWT_TTL=3600
JWT_REFRESH_TTL=604800

# API加密配置
API_ENCRYPTION_ENABLED=true
API_ENCRYPTION_ALGORITHM=AES-256-CBC
```

## 安装要求

新的安装系统要求：

- PHP 8.0.0或更高版本
- 以下PHP扩展：
  - pdo
  - pdo_mysql（MySQL）或pdo_sqlite（SQLite）
  - mbstring
  - json
  - openssl
  - curl
  - gd
  - xml
  - zip
- 可写的目录权限：
  - storage/
  - storage/logs/
  - storage/data/
  - storage/cache/
  - storage/sessions/
  - storage/uploads/
  - .env

## 安装后的安全措施

安装完成后，系统会自动实施以下安全措施：

1. 创建.htaccess文件保护敏感目录
2. 生成随机的加密密钥和JWT密钥
3. 设置适当的目录权限
4. 创建安装锁定文件，防止重复安装

## 后续开发计划

1. **多语言支持**：为安装界面添加多语言支持
2. **安装向导**：提供更详细的安装向导和帮助文档
3. **自动更新**：实现系统自动更新功能
4. **备份还原**：在安装过程中提供备份和还原选项
