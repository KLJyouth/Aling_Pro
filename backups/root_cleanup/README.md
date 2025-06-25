# AlingAi Pro PHP开发指南

## 项目概述

AlingAi Pro是一个基于PHP的AI应用开发框架，为了确保代码质量和一致性，我们创建了一系列开发指南和工具，并制定了文档整理方案。

## 文档指南

我们提供了以下开发指南文档，帮助开发人员遵循最佳实践：

1. **[PHP开发指南](PHP_DEVELOPMENT_GUIDELINES.md)** - 综合性指南，包含命名空间规范、代码质量检查和最佳实践

2. **[PHP命名空间规范](PHP_NAMESPACE_STANDARDS.md)** - 详细的命名空间结构和使用规则

3. **[PHP代码质量自动化检查](PHP_CODE_QUALITY_AUTOMATION.md)** - 自动化代码质量检查工具的配置和使用指南

4. **[PHP最佳实践指南](PHP_BEST_PRACTICES_GUIDE.md)** - PHP编码最佳实践，包括类型声明、接口实现等

## 文档整理方案

为了更好地组织项目文件，提高可维护性和安全性，我们制定了文档整理方案：

1. **[文档整理方案](ALING_AI_PRO_DOCUMENTATION_PLAN.md)** - 详细的文档整理方案

2. **[文件分类表](FILE_CLASSIFICATION_TABLE.md)** - 文件分类表格

3. **[实施步骤](IMPLEMENTATION_STEPS.md)** - 实施步骤文档

## 自动化工具

为防止未来出现类似问题，我们开发了以下自动化工具：

1. **命名空间一致性检查工具** - `fix_namespace_consistency.php`
   - 检查和修复接口与实现类的命名空间不一致问题

2. **接口实现检查工具** - `check_interface_implementations.php`
   - 验证实现类是否正确实现了接口中定义的所有方法

3. **构造函数检查工具** - `fix_constructor_brackets.php`
   - 检查和修复构造函数中多余括号问题

4. **私有变量检查工具** - `fix_private_variables.php`
   - 检查和修复方法内部错误使用private关键字的问题

5. **代码质量检查脚本** - `run_code_quality_checks.bat`
   - 一键运行所有代码质量检查

## 预防措施

为防止未来出现类似问题，我们实施了以下预防措施：

1. **统一的命名空间规范**
   - 文档化的命名空间结构和规则
   - 自动化验证工具
   - 标准化的代码模板

2. **自动化检查工具**
   - CI/CD集成
   - Git钩子预提交检查
   - IDE实时检查配置

3. **代码审查流程**
   - 标准化的审查清单
   - 自动化辅助审查
   - 结对编程实践

4. **开发人员培训**
   - 新员工培训计划
   - 定期技术研讨会
   - 内部知识分享

## 使用指南

### 安装开发工具

```bash
composer require --dev squizlabs/php_codesniffer
composer require --dev phpstan/phpstan
composer require --dev vimeo/psalm
composer require --dev friendsofphp/php-cs-fixer
```

### 运行代码质量检查

```bash
./run_code_quality_checks.bat
```

### 检查接口实现

```bash
php check_interface_implementations.php --detailed
```

### 修复命名空间问题

```bash
php fix_namespace_consistency.php
```

## 目录结构

我们正在重组项目目录结构，新的结构如下：

```
AlingAi_pro/
├── docs/                           # 公开技术文档
│   ├── standards/                  # 编码标准文档
│   ├── guides/                     # 使用指南
│   └── references/                 # 参考资料
│
├── admin/                          # 管理员后台
│   ├── maintenance/                # IT运维中心
│   │   ├── tools/                  # 修复工具
│   │   ├── reports/                # 内部报告
│   │   └── logs/                   # 日志文件
│   └── security/                   # 安全相关
│
└── scripts/                        # 通用脚本
    ├── build/                      # 构建脚本
    └── quality/                    # 质量检查脚本
```

## 贡献指南

1. 确保你的代码符合我们的命名空间规范和编码标准
2. 在提交前运行代码质量检查工具
3. 提交代码前进行自我审查
4. 遵循标准的Git工作流程

## 联系方式

如有任何问题或建议，请联系项目维护团队。
