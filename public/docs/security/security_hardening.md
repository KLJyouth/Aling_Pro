# AlingAi Pro 安全加固指南

本文档提供了AlingAi Pro系统的安全加固建议和最佳实践。

## 1. 服务器安全

### 1.1 操作系统安全

- 保持操作系统更新到最新的安全补丁
- 禁用不必要的服务和端口
- 使用防火墙限制入站和出站连接
- 启用入侵检测系统(IDS)和入侵防御系统(IPS)
- 实施文件完整性监控

### 1.2 Web服务器安全

- 使用最新版本的Web服务器软件(Apache/Nginx)
- 禁用不必要的模块和功能
- 配置适当的访问控制和权限
- 启用HTTPS并配置SSL/TLS安全参数
  - 使用强密码套件
  - 启用HSTS(HTTP严格传输安全)
  - 禁用不安全的SSL/TLS版本
- 配置安全相关的HTTP头
  - Content-Security-Policy
  - X-Content-Type-Options
  - X-Frame-Options
  - X-XSS-Protection

## 2. PHP安全配置

### 2.1 PHP基本安全设置

- 在生产环境中禁用错误显示：`display_errors = Off`
- 启用错误日志：`log_errors = On`
- 禁用危险函数：`disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source`
- 限制文件上传大小：`upload_max_filesize = 10M`
- 设置内存限制：`memory_limit = 256M`
- 禁用远程文件包含：`allow_url_include = Off`
- 禁用PHP信息暴露：`expose_php = Off`

### 2.2 PHP-FPM安全设置

如果使用PHP-FPM：

- 使用专用用户和组运行PHP-FPM
- 使用Unix套接字而不是TCP/IP连接
- 限制最大子进程数
- 启用慢日志记录

## 3. 应用程序安全

### 3.1 认证和授权

- 实施强密码策略
  - 最小长度：12个字符
  - 要求包含大小写字母、数字和特殊字符
  - 定期密码更换策略
- 实施账户锁定机制（5次失败尝试后锁定10分钟）
- 使用安全的会话管理
  - 设置安全的Cookie属性（Secure, HttpOnly, SameSite）
  - 会话超时设置（120分钟）
  - 会话固定防护
- 实施多因素认证(MFA)
- 基于角色的访问控制(RBAC)

### 3.2 数据保护

- 敏感数据加密存储
- 使用安全的加密算法和适当的密钥管理
- 实施数据访问控制
- 定期数据备份和恢复测试
- 实施数据脱敏策略

### 3.3 输入验证和输出编码

- 验证所有用户输入
- 使用白名单而不是黑名单进行验证
- 对所有输出进行适当编码
- 使用参数化查询防止SQL注入
- 实施CSRF保护

## 4. 文件和目录权限

### 4.1 推荐权限设置

- 配置文件：`0400` (只读，仅所有者)
- 代码文件：`0444` (只读，所有用户)
- 可执行脚本：`0500` (可执行，仅所有者)
- 日志目录：`0700` (所有权限，仅所有者)
- 上传目录：`0700` (所有权限，仅所有者)
- 缓存目录：`0700` (所有权限，仅所有者)
- 数据库文件：`0600` (读写，仅所有者)

### 4.2 关键目录权限

```bash
# 设置正确的所有权
chown -R www-data:www-data /path/to/alingai

# 设置基本权限
find /path/to/alingai -type d -exec chmod 755 {} \;
find /path/to/alingai -type f -exec chmod 644 {} \;

# 设置特殊目录权限
chmod -R 700 /path/to/alingai/storage
chmod -R 700 /path/to/alingai/database
chmod 600 /path/to/alingai/database/database.sqlite
chmod -R 700 /path/to/alingai/public/uploads
```

## 5. 数据库安全

### 5.1 SQLite安全

- 将数据库文件放在Web根目录之外
- 设置严格的文件权限（0600）
- 启用WAL模式提高并发性能
- 定期备份数据库文件
- 使用参数化查询防止SQL注入

### 5.2 MySQL/MariaDB安全（如果使用）

- 删除默认用户和测试数据库
- 使用强密码
- 限制数据库用户权限
- 禁用远程root登录
- 启用SSL/TLS加密连接
- 定期备份数据库

## 6. 日志和监控

### 6.1 日志配置

- 启用详细的应用程序日志
- 记录所有安全相关事件
- 保护日志文件不被未授权访问
- 实施日志轮转策略
- 考虑使用集中式日志管理

### 6.2 监控

- 实施实时安全监控
- 设置异常检测和警报
- 监控文件完整性
- 监控用户活动，特别是管理员活动
- 定期审查日志

## 7. 定期安全维护

- 定期更新所有软件和依赖项
- 执行定期安全评估和渗透测试
- 审查和更新安全策略和程序
- 定期备份数据和配置
- 测试恢复程序

## 8. 事件响应计划

- 制定安全事件响应计划
- 定义角色和责任
- 建立通知和升级程序
- 记录和分析安全事件
- 定期测试和更新响应计划

---

通过实施这些安全措施，您可以显著提高AlingAi Pro系统的安全性。请根据您的具体环境和需求调整这些建议。 