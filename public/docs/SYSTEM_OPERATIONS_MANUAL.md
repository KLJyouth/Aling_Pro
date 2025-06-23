# 🛠️ AlingAi Pro 系统运维手册

## 📋 目录
1. [系统概述](#系统概述)
2. [日常运维](#日常运维)
3. [监控告警](#监控告警)
4. [故障排除](#故障排除)
5. [性能优化](#性能优化)
6. [安全管理](#安全管理)
7. [备份恢复](#备份恢复)
8. [系统升级](#系统升级)

## 🎯 系统概述

### 核心组件
- **AI服务引擎**: DeepSeek AI集成，负责智能分析
- **安全防护系统**: 实时威胁检测和自动防护
- **网络监控系统**: 24/7网络流量分析
- **威胁情报系统**: 全球威胁情报收集和分析
- **数据存储系统**: FileSystemDB轻量级数据库
- **WebSocket服务**: 实时通信服务
- **Web管理界面**: 系统管理和配置界面

### 系统架构
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Interface │    │  AI Service     │    │ Security System │
│                 │    │  (DeepSeek)     │    │                 │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
          ┌─────────────────────┴─────────────────────┐
          │            Core Router                    │
          └─────────────────────┬─────────────────────┘
                                │
    ┌───────────┬───────────────┼───────────────┬───────────┐
    │           │               │               │           │
┌───▼───┐ ┌────▼────┐ ┌────────▼────────┐ ┌────▼───┐ ┌────▼────┐
│Network│ │Threat   │ │  FileSystemDB   │ │WebSocket│ │Logging  │
│Monitor│ │Intel    │ │                 │ │Server  │ │System   │
└───────┘ └─────────┘ └─────────────────┘ └────────┘ └─────────┘
```

## 🔄 日常运维

### 系统启动
```bash
# 1. 启动WebSocket服务器 (后台运行)
cd "e:\Code\AlingAi\AlingAi_pro"
start /B php websocket_server.php

# 2. 启动Web服务器
php -S localhost:8080 -t public

# 3. 验证系统状态
php production_deployment_validator.php
```

### 系统停止
```bash
# 停止WebSocket服务器
taskkill /F /IM php.exe

# 停止Web服务器
Ctrl+C (在运行Web服务器的终端中)
```

### 日常检查清单
- [ ] 检查系统日志是否有错误
- [ ] 验证AI服务连接状态
- [ ] 检查安全威胁警报
- [ ] 监控系统资源使用情况
- [ ] 验证WebSocket服务状态
- [ ] 检查数据库完整性

## 📊 监控告警

### 系统健康检查
```bash
# 运行完整系统验证
php production_deployment_validator.php

# 检查特定组件
php check_ai_service.php           # AI服务状态
php check_security_system.php      # 安全系统状态
php check_database_health.php      # 数据库状态
```

### 日志监控
```bash
# 实时查看系统日志
tail -f logs/system.log

# 实时查看安全日志
tail -f logs/security.log

# 实时查看AI服务日志
tail -f logs/ai_service.log

# 查看错误日志
tail -f logs/error.log

# 查看访问日志
tail -f logs/access.log
```

### 性能监控指标
| 指标 | 正常范围 | 警告阈值 | 严重阈值 |
|------|----------|----------|----------|
| 内存使用率 | < 70% | 70-85% | > 85% |
| CPU使用率 | < 60% | 60-80% | > 80% |
| 磁盘使用率 | < 80% | 80-90% | > 90% |
| 响应时间 | < 200ms | 200-500ms | > 500ms |
| 威胁检测延迟 | < 1s | 1-3s | > 3s |

### 告警触发条件
- **高优先级**: 安全威胁检测、AI服务中断、数据库连接失败
- **中优先级**: 性能指标超过警告阈值、WebSocket连接异常
- **低优先级**: 配置文件更新、日志文件大小增长

## 🚨 故障排除

### 常见问题及解决方案

#### 1. AI服务连接失败
**症状**: 
- AI服务调用超时
- 日志中出现"DeepSeek API请求失败"

**排查步骤**:
```bash
# 1. 检查网络连接
ping api.deepseek.com

# 2. 验证API密钥
php debug_ai_service.php

# 3. 检查SSL证书
curl -I https://api.deepseek.com
```

**解决方案**:
- 检查网络防火墙设置
- 验证API密钥有效性
- 更新SSL证书配置

#### 2. 数据库权限错误
**症状**:
- "Permission denied" 错误
- 无法写入数据文件

**排查步骤**:
```bash
# 检查目录权限
ls -la storage/
ls -la storage/data/

# 测试写入权限
php check_file_permissions.php
```

**解决方案**:
```bash
# 修复权限
chmod -R 755 storage/
chmod -R 777 storage/data/
```

#### 3. WebSocket服务异常
**症状**:
- 客户端无法连接
- 实时更新停止

**排查步骤**:
```bash
# 检查端口是否被占用
netstat -an | findstr :8080

# 测试WebSocket连接
php test_websocket_connection.php
```

**解决方案**:
- 重启WebSocket服务器
- 更换端口配置
- 检查防火墙设置

#### 4. 内存不足错误
**症状**:
- "Fatal error: Allowed memory size"
- 系统响应缓慢

**排查步骤**:
```bash
# 检查当前内存使用
php -r "echo ini_get('memory_limit');"

# 监控内存使用
php memory_usage_monitor.php
```

**解决方案**:
```ini
# 在php.ini中增加内存限制
memory_limit = 1024M

# 或在脚本中临时设置
ini_set('memory_limit', '1024M');
```

## ⚡ 性能优化

### 配置优化
```ini
# php.ini 推荐配置
memory_limit = 1024M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
max_input_vars = 5000

# OPcache配置
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

### 数据库优化
```bash
# 数据库性能测试
php database_performance_test.php

# 数据库优化
php database_optimizer.php

# 清理过期数据
php cleanup_expired_data.php
```

### 缓存策略
```bash
# 启用缓存预热
php cache_warmup.php

# 缓存性能测试
php cache_performance_test.php

# 清理缓存
php cache_cleanup.php
```

## 🔐 安全管理

### 安全配置审核
```bash
# 运行安全检查
php security_audit.php

# 检查文件权限
php check_file_permissions.php

# 验证配置安全性
php validate_security_config.php
```

### 威胁监控
```bash
# 查看最新威胁
php view_recent_threats.php

# 威胁统计分析
php threat_statistics.php

# 生成安全报告
php generate_security_report.php
```

### 访问控制
- **API访问**: 基于JWT的身份验证
- **管理界面**: 多因素身份验证
- **文件访问**: 严格的文件权限控制
- **网络访问**: IP白名单和黑名单

## 💾 备份恢复

### 自动备份
```bash
# 设置定时备份 (Windows任务计划程序)
schtasks /create /tn "AlingAi Backup" /tr "php backup_system.php" /sc daily /st 02:00

# 手动备份
php backup_system.php

# 备份验证
php verify_backup.php
```

### 备份策略
- **每日备份**: 数据库和配置文件
- **每周备份**: 完整系统备份
- **每月备份**: 长期存档备份
- **实时备份**: 关键数据实时同步

### 恢复流程
```bash
# 1. 停止所有服务
php stop_all_services.php

# 2. 恢复数据
php restore_from_backup.php --backup-file=backup_20250608.tar.gz

# 3. 验证数据完整性
php verify_data_integrity.php

# 4. 重启服务
php start_all_services.php

# 5. 运行系统验证
php production_deployment_validator.php
```

## 🔄 系统升级

### 升级前准备
```bash
# 1. 创建完整备份
php create_full_backup.php

# 2. 记录当前配置
php export_current_config.php

# 3. 停止服务
php stop_all_services.php
```

### 升级流程
```bash
# 1. 下载新版本
wget https://github.com/alingai/alingai-pro/releases/latest/download/alingai-pro.tar.gz

# 2. 解压到临时目录
tar -xzf alingai-pro.tar.gz -C /tmp/

# 3. 备份当前版本
mv /current/alingai-pro /backup/alingai-pro-old

# 4. 部署新版本
mv /tmp/alingai-pro /current/

# 5. 恢复配置文件
cp /backup/config/* /current/alingai-pro/config/

# 6. 运行数据库迁移
php migrate_database.php

# 7. 重启服务
php start_all_services.php

# 8. 验证升级
php production_deployment_validator.php
```

### 回滚流程
```bash
# 如果升级失败，执行回滚
# 1. 停止新版本服务
php stop_all_services.php

# 2. 恢复旧版本
mv /current/alingai-pro /failed/alingai-pro-failed
mv /backup/alingai-pro-old /current/alingai-pro

# 3. 恢复数据库
php restore_database_backup.php

# 4. 重启服务
php start_all_services.php

# 5. 验证回滚
php production_deployment_validator.php
```

## 📞 技术支持

### 日志收集
```bash
# 收集所有相关日志
php collect_support_logs.php

# 生成系统诊断报告
php generate_diagnostic_report.php

# 导出系统配置
php export_system_config.php
```

### 远程诊断
```bash
# 启用远程诊断模式
php enable_remote_diagnostics.php

# 生成诊断访问链接
php generate_diagnostic_link.php
```

### 联系信息
- **技术文档**: 查看项目README和文档目录
- **问题报告**: 通过GitHub Issues提交
- **紧急支持**: 查看系统日志和诊断报告

---

## 📋 运维检查清单

### 每日检查 ✅
- [ ] 系统状态验证
- [ ] 日志文件检查
- [ ] 威胁警报审查
- [ ] 性能指标监控
- [ ] 备份状态确认

### 每周检查 ✅
- [ ] 系统完整性验证
- [ ] 安全配置审核
- [ ] 性能优化分析
- [ ] 备份测试恢复
- [ ] 系统更新检查

### 每月检查 ✅
- [ ] 安全漏洞扫描
- [ ] 性能基准测试
- [ ] 容量规划评估
- [ ] 文档更新维护
- [ ] 应急预案演练

---

*🛡️ AlingAi Pro - 专业级运维手册 v1.0*
*最后更新: 2025年6月8日*
