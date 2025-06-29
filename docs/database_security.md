# 数据库安全系统

本系统提供了全面的数据库安全保护功能，包括防爆破、防SQL注入、连接限制、审计日志、数据库防火墙等功能。

## 功能特点

1. **防爆破攻击**：监控并阻止暴力破解尝试，自动将可疑IP加入黑名单。
2. **SQL注入防御**：检测并阻止SQL注入攻击，记录可疑查询。
3. **连接限制**：限制每个IP和总体的数据库连接数，防止连接耗尽攻击。
4. **审计日志**：记录所有数据库操作，包括查询、修改和删除操作。
5. **数据库防火墙**：基于规则的查询过滤，阻止危险操作。
6. **长查询监控**：自动终止长时间运行的查询，防止资源耗尽。
7. **结构变化监控**：监控数据库结构变化，及时发现未授权的修改。
8. **漏洞扫描**：定期扫描数据库漏洞，提供修复建议。

## 安装与配置

### 1. 运行数据库迁移

```bash
php artisan migrate
```

这将创建所有必要的数据库表。

### 2. 注册服务提供者

在 `config/app.php` 文件中的 `providers` 数组中添加：

```php
App\Providers\DatabaseSecurityServiceProvider::class,
```

### 3. 注册中间件

在 `app/Http/Kernel.php` 文件中的 `$routeMiddleware` 数组中添加：

```php
'db.security' => \App\Http\Middleware\DatabaseSecurityMiddleware::class,
```

### 4. 应用中间件

在需要保护的路由上应用中间件：

```php
Route::group(['middleware' => ['db.security']], function () {
    // 受保护的路由
});
```

### 5. 设置计划任务

确保在服务器上设置了Laravel调度器：

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 配置选项

所有配置选项都在 `config/database_security.php` 文件中，您可以根据需要调整这些设置。

### 主要配置项

- **brute_force**: 防爆破攻击设置
- **sql_injection**: SQL注入防御设置
- **connection_limits**: 连接限制设置
- **audit**: 审计日志设置
- **firewall**: 防火墙规则设置
- **monitoring**: 监控设置
- **backup**: 备份设置
- **vulnerability_scan**: 漏洞扫描设置

## 使用方法

### 手动运行安全监控

```bash
# 运行所有安全监控任务
php artisan db:security-monitor

# 只终止长时间运行的查询
php artisan db:security-monitor --kill-long-queries

# 只监控数据库结构变化
php artisan db:security-monitor --monitor-changes

# 设置审计触发器
php artisan db:security-monitor --setup-triggers

# 设置数据库防火墙
php artisan db:security-monitor --setup-firewall
```

### 查看安全日志

所有安全事件都记录在 `database_security_logs` 表中，您可以通过管理面板或直接查询数据库来查看这些日志。

### 管理防火墙规则

您可以通过 `database_firewall_rules` 表添加、修改或删除防火墙规则。

## 注意事项

1. 首次使用时，请运行 `php artisan db:security-monitor --setup-triggers` 和 `php artisan db:security-monitor --setup-firewall` 来设置审计触发器和防火墙规则。
2. 审计日志可能会占用大量存储空间，请定期清理旧日志。
3. 在生产环境中使用前，请先在测试环境中测试所有功能。
4. 某些防火墙规则可能会阻止合法操作，请根据实际需求调整规则。

## 安全最佳实践

1. 定期更改数据库密码，使用强密码。
2. 限制数据库用户权限，遵循最小权限原则。
3. 定期备份数据库，测试恢复过程。
4. 保持数据库软件更新，应用安全补丁。
5. 使用加密连接（SSL/TLS）连接数据库。
6. 定期审计数据库用户和权限。
7. 监控数据库性能和异常活动。
