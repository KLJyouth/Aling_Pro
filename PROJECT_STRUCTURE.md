# 项目文件结构

本文档描述了项目的文件结构，以便团队成员了解各种资源的位置。

## 主要目录结构

```
AlingAi_pro/
├── public/               # 主要资源目录
│   ├── admin/            # 管理相关文件
│   │   ├── maintenance/  # 维护工具和日志
│   │   │   ├── logs/     # 维护日志文件
│   │   │   ├── reports/  # 维护报告
│   │   │   └── tools/    # 维护工具脚本
│   │   └── security/     # 安全相关文件
│   ├── assets/           # 资源文件
│   │   └── images/       # 图片资源
│   ├── config/           # 配置文件
│   ├── docs/             # 文档文件
│   │   ├── guides/       # 用户和开发指南
│   │   └── reports/      # 项目报告
│   ├── logs/             # 日志文件
│   │   └── reports/      # 日志报告
│   └── tools/            # 工具脚本
│       ├── server/       # 服务器相关工具
│       └── tests/        # 测试工具
├── scripts/              # 项目脚本
├── src/                  # 源代码
├── backups/              # 备份文件
│   ├── root_cleanup/     # 根目录清理备份
│   ├── root_cleanup2/    # 第二轮根目录清理备份
│   ├── admin_cleanup/    # admin目录清理备份
│   ├── root_files_cleanup/ # 根目录文件清理备份
│   ├── remaining_files_cleanup/ # 剩余文件清理备份
│   ├── final_files_cleanup/ # 最终文件清理备份
│   └── last_files_cleanup/ # 最后文件清理备份
├── tools.bat             # 常用工具菜单
├── admin-tools.bat       # 管理工具菜单
├── docs.bat              # 文档访问菜单
└── artisan.bat           # Artisan命令启动脚本
```

## 文件组织原则

为了保持项目结构的一致性，我们采用了以下原则：

1. **源代码** - 所有源代码都应放在 `src/` 目录下
2. **文档** - 所有文档文件都应放在 `public/docs/` 目录下
   - 用户和开发指南放在 `public/docs/guides/`
   - 项目报告放在 `public/docs/reports/`
3. **管理工具** - 所有管理相关的工具和文件都应放在 `public/admin/` 目录下
4. **工具脚本** - 所有工具脚本都应放在 `public/tools/` 目录下
5. **配置文件** - 所有配置文件都应放在 `public/config/` 目录下

## 最近的结构变更

项目结构最近经历了重大整理：

1. 根目录下的文件已移动到相应的目录中
2. admin目录的内容已合并到public/admin目录中
3. 所有原始文件都有备份

## 如何访问常用工具

为了方便访问，我们在根目录下创建了符号链接和启动脚本，指向常用工具：

1. `tools.bat` - 启动常用工具菜单
2. `admin-tools.bat` - 启动管理工具菜单
3. `docs.bat` - 快速访问文档

## 未来文件组织指南

为了保持项目结构的一致性，请遵循以下指南：

1. 新增文件应直接放在对应的目录中，而不是放在根目录
2. 临时文件应放在 `temp/` 目录中，并在不需要时删除
3. 如需在根目录下访问某个工具，请使用符号链接或启动脚本，而不是复制文件
4. 定期清理不再需要的文件和目录 