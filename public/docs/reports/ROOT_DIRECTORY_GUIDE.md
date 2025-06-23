# AlingAi Pro 5.0 - 根目录指南

**更新时间**: 2025-06-11 08:45:15  
**维护脚本**: final_root_cleanup.php

## 🏗️ 项目结构概览

这是AlingAi Pro 5.0的根目录。项目采用现代化的目录结构，将不同功能的文件分类组织。

### 📁 主要目录说明

| 目录 | 用途 | 描述 |
|------|------|------|
| `public/` | **Web根目录** | 所有Web可访问的文件 |
| `src/` | **源代码** | 核心业务逻辑代码 |
| `config/` | **配置文件** | 系统配置和设置 |
| `database/` | **数据库** | 数据库脚本和迁移文件 |
| `storage/` | **存储** | 日志、缓存、临时文件 |
| `scripts/` | **脚本** | 维护、部署、管理脚本 |
| `tests/` | **测试** | 单元测试和集成测试 |
| `docs/` | **文档** | 项目文档和说明 |
| `vendor/` | **依赖** | Composer依赖包 |
| `tools/` | **工具** | 开发和运维工具 |

### 🔧 核心文件说明

#### 保留在根目录的文件
- `router.php` - 主路由器
- `worker.php` - 后台工作进程
- `composer.json` - Composer配置
- `README.md` - 项目说明
- `.env*` - 环境配置文件

#### Public目录结构
```
public/
├── admin/          # 管理后台
├── api/            # API接口
├── assets/         # 静态资源
├── docs/           # 在线文档
├── test/           # 测试工具
├── tools/          # 管理工具
├── monitor/        # 监控工具
├── uploads/        # 用户上传
└── index.php       # Web入口
```

#### Scripts目录结构
```
scripts/
├── maintenance/    # 维护脚本
├── system/         # 系统脚本
├── validation/     # 验证脚本
├── performance/    # 性能脚本
├── migration/      # 迁移脚本
└── batch/          # 批处理脚本
```

## 🚀 快速开始

### 开发环境
```bash
# 安装依赖
composer install

# 启动开发服务器
php -S localhost:8000 -t public

# 或使用批处理脚本
# Windows
scripts/batch/start.bat

# Linux/Mac
scripts/batch/start.sh
```

### 生产环境
```bash
# 使用快速启动脚本
scripts/batch/quick_start.bat  # Windows
scripts/batch/start.sh         # Linux/Mac

# 或手动启动
php scripts/system/init_system.php
php scripts/system/start_system.php
```

## 🛠️ 管理工具

### Web管理界面
- 管理后台: `/admin/`
- 系统监控: `/monitor/`
- 测试工具: `/test/`
- 在线文档: `/docs/`

### 命令行工具
- 系统初始化: `php scripts/system/init_system.php`
- 性能优化: `php scripts/performance/optimize_production.php`
- 数据库管理: `php database/management/migrate_database.php`
- 缓存预热: `php scripts/performance/cache_warmup.php`

## 📚 文档链接

- [系统架构](docs/ARCHITECTURE_DIAGRAM.md)
- [部署指南](docs/DEPLOYMENT_GUIDE.md)
- [用户手册](docs/USER_MANUAL.md)
- [API文档](public/docs/api/)
- [开发规范](docs/CODE_STANDARDS.md)

## 🔐 安全注意事项

1. **环境配置**: 确保`.env`文件安全，不要提交到版本控制
2. **文件权限**: Public目录外的文件不应直接Web访问
3. **上传安全**: uploads目录已配置安全限制
4. **日志保护**: 敏感日志文件受到访问保护

## 📞 支持

如需帮助，请参考:
- 在线文档: `/docs/`
- 系统状态: `/monitor/health.php`
- 错误日志: `storage/logs/`

---
*此文档由 final_root_cleanup.php 自动生成*
