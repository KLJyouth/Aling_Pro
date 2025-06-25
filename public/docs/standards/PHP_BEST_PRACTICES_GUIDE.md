# PHP 最佳实践指南

## 1. 命名空间规范

### 1.1 命名空间结构

我们的项目采用以下命名空间结构：

```
AlingAi\                      # 根命名空间
  ├── Engines\                # 核心引擎
  │    ├── NLP\               # 自然语言处理引擎
  │    ├── CV\                # 计算机视觉引擎
  │    ├── Speech\            # 语音处理引擎
  │    └── ML\                # 机器学习引擎
  │
  ├── AI\                     # AI应用层
  │    ├── Services\          # AI服务
  │    └── Models\            # AI模型
  │
  ├── Apps\                   # 应用程序
  │    ├── Platform\          # 平台应用
  │    ├── Enterprise\        # 企业应用
  │    └── Government\        # 政府应用
  │
  ├── Core\                   # 核心框架
  │    ├── Database\          # 数据库操作
  │    ├── Http\              # HTTP相关
  │    └── Config\            # 配置管理
  │
  └── Utils\                  # 工具类
       ├── Helpers\           # 辅助函数
       └── Security\          # 安全工具
```

### 1.2 命名空间使用规则

1. **一致性原则**：接口和实现类必须在同一命名空间或遵循一致的命名空间结构
2. **映射原则**：命名空间应与文件路径结构一致
3. **避免冲突**：不同功能模块应使用不同的命名空间
4. **明确职责**：命名空间应明确表示代码的功能和职责

### 1.3 常见错误示例

❌ **错误**：接口和实现类在不同命名空间
```php
// 接口在 AlingAi\Engines\NLP
namespace AlingAi\Engines\NLP;
interface TokenizerInterface { /* ... */ }

// 实现在 AlingAi\AI\Engines\NLP
namespace AlingAi\AI\Engines\NLP;
class ChineseTokenizer implements TokenizerInterface { /* ... */ }
```

✅ **正确**：接口和实现类在同一命名空间
```php
// 接口和实现都在 AlingAi\Engines\NLP
namespace AlingAi\Engines\NLP;
interface TokenizerInterface { /* ... */ }

namespace AlingAi\Engines\NLP;
class ChineseTokenizer implements TokenizerInterface { /* ... */ }
```

## 2. 接口实现规范

### 2.1 接口实现完整性

1. **方法签名一致**：实现类中的方法签名必须与接口定义完全一致
2. **参数类型和数量**：参数类型、数量和默认值必须匹配
3. **返回类型**：返回类型必须兼容

### 2.2 常见错误示例

❌ **错误**：方法签名不匹配
```php
// 接口定义
interface TokenizerInterface {
    public function tokenize(string $text, array $options = []): array;
}

// 实现类缺少参数
class Tokenizer implements TokenizerInterface {
    public function tokenize(string $text): array { /* ... */ }
}
```

✅ **正确**：方法签名完全匹配
```php
// 接口定义
interface TokenizerInterface {
    public function tokenize(string $text, array $options = []): array;
}

// 实现类完全匹配
class Tokenizer implements TokenizerInterface {
    public function tokenize(string $text, array $options = []): array { /* ... */ }
}
```

## 3. PHP 8.1 语法规范

### 3.1 类型声明

1. **使用严格类型**：在文件顶部添加 `declare(strict_types=1);`
2. **属性类型**：为所有属性声明类型
3. **方法参数和返回值**：为所有方法参数和返回值声明类型
4. **联合类型**：使用 `|` 表示联合类型，如 `string|int`

```php
<?php declare(strict_types=1);

class User {
    private int $id;
    private string $name;
    
    public function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }
}
```

### 3.2 抽象类和方法

1. **避免重复定义**：抽象方法不能同时有抽象声明和具体实现
2. **实现所有抽象方法**：子类必须实现所有抽象方法

❌ **错误**：抽象方法同时有声明和实现
```php
abstract class BaseEngine {
    abstract public function process(mixed $input, array $options = []): array;
    
    public function process() {
        // 具体实现
    }
}
```

✅ **正确**：抽象方法只有声明
```php
abstract class BaseEngine {
    abstract public function process(mixed $input, array $options = []): array;
}
```

### 3.3 构造函数

1. **参数列表**：避免多余的括号
2. **属性提升**：使用构造函数属性提升简化代码

❌ **错误**：构造函数有多余括号
```php
public function __construct((array $config = [])) {
    // ...
}
```

✅ **正确**：构造函数参数正确
```php
public function __construct(array $config = []) {
    // ...
}
```

✅ **更好**：使用构造函数属性提升
```php
public function __construct(
    private array $config = []
) {
    // 无需额外代码
}
```

### 3.4 变量作用域

1. **类属性**：使用 `private`、`protected` 或 `public` 关键字声明
2. **局部变量**：不使用访问修饰符

❌ **错误**：在方法内使用访问修饰符
```php
public function process() {
    private $result = $this->calculate();
    return $result;
}
```

✅ **正确**：局部变量不使用访问修饰符
```php
public function process() {
    $result = $this->calculate();
    return $result;
}
```

## 4. 代码质量保证

### 4.1 自动化检查工具

1. **PHP_CodeSniffer**：检查代码风格
   ```bash
   composer require --dev squizlabs/php_codesniffer
   ./vendor/bin/phpcs --standard=PSR12 src/
   ```

2. **PHPStan**：静态分析工具
   ```bash
   composer require --dev phpstan/phpstan
   ./vendor/bin/phpstan analyse src/ --level=8
   ```

3. **Psalm**：类型检查工具
   ```bash
   composer require --dev vimeo/psalm
   ./vendor/bin/psalm --init
   ./vendor/bin/psalm
   ```

4. **PHP-CS-Fixer**：自动修复代码风格
   ```bash
   composer require --dev friendsofphp/php-cs-fixer
   ./vendor/bin/php-cs-fixer fix src/
   ```

5. **接口实现检查工具**：
   ```bash
   php check_interface_implementations.php --detailed
   ```

### 4.2 CI/CD 集成

在 CI/CD 流程中集成以下检查：

1. **语法检查**：
   ```bash
   find src -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
   ```

2. **代码风格检查**：
   ```bash
   ./vendor/bin/phpcs --standard=PSR12 src/
   ```

3. **静态分析**：
   ```bash
   ./vendor/bin/phpstan analyse src/ --level=8
   ```

4. **接口实现检查**：
   ```bash
   php check_interface_implementations.php --ci
   ```

## 5. 代码审查流程

### 5.1 审查清单

每次代码审查应检查以下内容：

1. **命名空间一致性**：接口和实现类是否在正确的命名空间
2. **接口实现完整性**：所有接口方法是否正确实现
3. **类型声明**：是否使用了适当的类型声明
4. **错误处理**：是否妥善处理了异常和错误
5. **代码风格**：是否符合项目代码风格规范
6. **性能考虑**：是否有性能问题或优化空间
7. **安全性**：是否存在安全漏洞

### 5.2 审查流程

1. **自动化检查**：提交前运行自动化检查工具
2. **提交代码**：提交代码到版本控制系统
3. **创建合并请求**：创建合并请求并指定审查者
4. **代码审查**：审查者根据审查清单检查代码
5. **反馈和修改**：根据反馈修改代码
6. **最终审查**：审查者进行最终审查
7. **合并代码**：通过审查后合并代码

## 6. 开发环境配置

### 6.1 IDE 配置

#### PHPStorm 配置

1. **启用 PHP 8.1 支持**：
   - 设置 > 语言和框架 > PHP > PHP 语言级别 > 8.1

2. **启用代码检查**：
   - 设置 > 编辑器 > 检查 > PHP > 质量工具

3. **配置 PHP_CodeSniffer**：
   - 设置 > 语言和框架 > PHP > 质量工具 > PHP_CodeSniffer
   - 选择 PSR-12 标准

4. **配置 PHPStan**：
   - 设置 > 语言和框架 > PHP > 质量工具 > PHPStan
   - 设置级别为 8

### 6.2 Git 钩子

使用 Git 钩子在提交前自动检查代码：

1. **安装 husky**：
   ```bash
   composer require --dev typicode/husky
   ```

2. **配置 pre-commit 钩子**：
   ```bash
   {
     "scripts": {
       "pre-commit": "php -l && ./vendor/bin/phpcs"
     }
   }
   ```

## 7. 培训和资源

### 7.1 推荐学习资源

1. **官方文档**：
   - [PHP 官方文档](https://www.php.net/docs.php)
   - [PHP 8.1 新特性](https://www.php.net/releases/8.1/en.php)

2. **书籍**：
   - 《Modern PHP》by Josh Lockhart
   - 《PHP 7 Programming Cookbook》by Doug Bierer

3. **在线课程**：
   - Laracasts PHP 课程
   - Symfony Cast

### 7.2 内部培训计划

1. **基础培训**：
   - PHP 8.1 新特性
   - 类型系统和严格类型
   - 命名空间和自动加载

2. **进阶培训**：
   - 接口设计和实现
   - 依赖注入和容器
   - 单元测试和测试驱动开发

3. **实践培训**：
   - 代码审查实践
   - 常见错误和修复方法
   - 性能优化技巧

## 8. 结论

遵循本指南中的最佳实践，可以帮助我们：

1. 提高代码质量和可维护性
2. 减少常见错误和问题
3. 提高团队协作效率
4. 确保项目的长期稳定性

定期回顾和更新本指南，确保它与最新的PHP版本和最佳实践保持同步。 