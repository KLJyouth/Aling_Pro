# PHP命名空间规范

## 1. 命名空间结构

### 1.1 根命名空间

AlingAi_pro项目使用`AlingAi`作为根命名空间，所有代码都应位于此命名空间下。

### 1.2 命名空间层次结构

```
AlingAi\                      # 根命名空间
  ├── Engines\                # 核心引擎组件
  │    ├── NLP\               # 自然语言处理引擎
  │    ├── CV\                # 计算机视觉引擎
  │    ├── Speech\            # 语音处理引擎
  │    └── ML\                # 机器学习引擎
  │
  ├── Services\               # 服务层
  │    ├── AI\                # AI服务
  │    ├── Blockchain\        # 区块链服务
  │    ├── Cloud\             # 云服务
  │    └── Security\          # 安全服务
  │
  ├── Apps\                   # 应用程序
  │    ├── Platform\          # 平台应用
  │    ├── Enterprise\        # 企业应用
  │    ├── Government\        # 政府应用
  │    └── Security\          # 安全应用
  │
  ├── Core\                   # 核心框架
  │    ├── Database\          # 数据库操作
  │    ├── Http\              # HTTP相关
  │    ├── Config\            # 配置管理
  │    └── Cache\             # 缓存管理
  │
  ├── Utils\                  # 工具类
  │    ├── Helpers\           # 辅助函数
  │    ├── Security\          # 安全工具
  │    └── Formatters\        # 格式化工具
  │
  ├── Models\                 # 数据模型
  │    ├── Entity\            # 实体模型
  │    ├── DTO\               # 数据传输对象
  │    └── Repository\        # 仓储模型
  │
  ├── Interfaces\             # 接口定义
  │    ├── Engine\            # 引擎接口
  │    ├── Service\           # 服务接口
  │    └── Repository\        # 仓储接口
  │
  └── Exceptions\             # 异常类
       ├── Engine\            # 引擎异常
       ├── Service\           # 服务异常
       └── Validation\        # 验证异常
```

## 2. 命名空间命名规则

### 2.1 命名风格

- 命名空间应使用大驼峰命名法（PascalCase）
- 单词应具有描述性，避免缩写（除非是广泛接受的缩写，如NLP、CV等）
- 命名空间应反映代码的功能和职责

### 2.2 命名空间映射到文件路径

命名空间应与文件路径保持一致：

```
命名空间: AlingAi\Engines\NLP
文件路径: ai-engines/nlp/
```

```
命名空间: AlingAi\Apps\Enterprise\Services
文件路径: apps/enterprise/Services/
```

## 3. 接口和实现类的命名空间规则

### 3.1 接口和实现类位置

接口和其实现类必须位于**相同的命名空间**，以确保PHP能正确识别接口实现关系。

✅ **正确示例**：

```php
// 文件: ai-engines/nlp/TokenizerInterface.php
namespace AlingAi\Engines\NLP;

interface TokenizerInterface {
    // ...
}

// 文件: ai-engines/nlp/ChineseTokenizer.php
namespace AlingAi\Engines\NLP;

class ChineseTokenizer implements TokenizerInterface {
    // ...
}
```

❌ **错误示例**：

```php
// 文件: ai-engines/nlp/TokenizerInterface.php
namespace AlingAi\Engines\NLP;

interface TokenizerInterface {
    // ...
}

// 文件: ai-engines/nlp/ChineseTokenizer.php
namespace AlingAi\AI\Engines\NLP;  // 错误！命名空间不一致

class ChineseTokenizer implements TokenizerInterface {
    // ...
}
```

### 3.2 接口命名规则

- 接口名称应以`Interface`后缀结尾
- 接口应位于与其实现相同的命名空间中
- 接口文件名应与接口名称一致

```php
// 文件: ai-engines/nlp/TokenizerInterface.php
namespace AlingAi\Engines\NLP;

interface TokenizerInterface {
    // ...
}
```

### 3.3 抽象类命名规则

- 抽象类名称应以`Abstract`或`Base`前缀开始
- 抽象类应位于与其子类相同的命名空间中
- 抽象类文件名应与抽象类名称一致

```php
// 文件: ai-engines/nlp/AbstractTokenizer.php
namespace AlingAi\Engines\NLP;

abstract class AbstractTokenizer implements TokenizerInterface {
    // ...
}
```

## 4. 命名空间使用规则

### 4.1 导入命名空间

- 使用`use`语句导入需要的类、接口或特性
- `use`语句应按字母顺序排序
- 相同命名空间下的类应分组排序

```php
use AlingAi\Core\Config\ConfigManager;
use AlingAi\Engines\NLP\TokenizerInterface;
use AlingAi\Exceptions\Engine\TokenizerException;
use AlingAi\Utils\Helpers\StringHelper;
```

### 4.2 别名使用

当导入的类名冲突时，使用`as`关键字创建别名：

```php
use AlingAi\Engines\NLP\Tokenizer as NLPTokenizer;
use AlingAi\Services\AI\Tokenizer as AITokenizer;
```

### 4.3 全局命名空间

访问全局命名空间中的类或函数时，使用前导反斜杠：

```php
$exception = new \Exception('An error occurred');
$json = \json_encode($data);
```

## 5. 常见问题和解决方案

### 5.1 命名空间不一致问题

**问题**：接口和实现类位于不同的命名空间，导致PHP无法识别接口实现关系。

**解决方案**：

1. 确保接口和所有实现类都位于相同的命名空间
2. 使用`fix_namespace_consistency.php`工具检查和修复命名空间不一致问题

```bash
php fix_namespace_consistency.php
```

### 5.2 命名空间自动加载问题

**问题**：类无法被自动加载，出现"Class not found"错误。

**解决方案**：

1. 确保命名空间与文件路径一致
2. 检查composer.json中的自动加载配置

```json
"autoload": {
    "psr-4": {
        "AlingAi\\": ["ai-engines/", "apps/", "src/"]
    }
}
```

3. 更新自动加载器

```bash
composer dump-autoload
```

### 5.3 命名空间冲突

**问题**：不同模块中的类名冲突。

**解决方案**：

1. 使用更具体的命名空间划分
2. 使用别名解决冲突

```php
use AlingAi\Engines\NLP\Tokenizer as NLPTokenizer;
use AlingAi\Services\AI\Tokenizer as AITokenizer;
```

## 6. 命名空间检查工具

### 6.1 自动化检查

使用`check_interface_implementations.php`工具检查接口实现和命名空间一致性：

```bash
php check_interface_implementations.php --detailed
```

### 6.2 IDE集成

配置PHPStorm或VS Code以检测命名空间问题：

**PHPStorm**：
- 启用PHP Inspections
- 配置PSR-4命名空间验证

**VS Code**：
- 安装PHP Intelephense扩展
- 配置命名空间验证

## 7. 命名空间迁移指南

当需要更改命名空间结构时，请遵循以下步骤：

1. **创建备份**：
   ```bash
   mkdir -p backups/namespace_migration_$(date +%Y%m%d)
   cp -r affected_directory backups/namespace_migration_$(date +%Y%m%d)/
   ```

2. **更新命名空间**：
   - 修改类文件中的命名空间声明
   - 更新所有导入（use语句）
   - 更新类型提示和文档注释

3. **更新自动加载器**：
   ```bash
   composer dump-autoload
   ```

4. **运行测试**：
   ```bash
   php vendor/bin/phpunit
   ```

5. **检查接口实现**：
   ```bash
   php check_interface_implementations.php --detailed
   ```

## 8. 最佳实践

1. **一致性**：接口和实现类应位于相同的命名空间
2. **映射**：命名空间应与文件路径结构一致
3. **明确性**：命名空间应明确表示代码的功能和职责
4. **避免冲突**：不同功能模块应使用不同的命名空间
5. **简洁性**：避免过深的命名空间嵌套（通常不超过5层）
6. **自动化检查**：定期运行命名空间一致性检查工具
7. **文档化**：在代码注释中明确说明类的命名空间和用途

## 9. 结论

遵循本文档中的命名空间规范，可以帮助我们：

1. 避免命名空间不一致导致的接口实现问题
2. 提高代码的可读性和可维护性
3. 简化自动加载和依赖管理
4. 减少命名冲突和相关错误

所有团队成员都应熟悉并遵循这些规范，以确保代码库的一致性和质量。 