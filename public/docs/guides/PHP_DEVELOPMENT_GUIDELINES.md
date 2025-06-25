# PHP开发指南

## 目录

1. [命名空间规范](#1-命名空间规范)
2. [代码质量自动化检查](#2-代码质量自动化检查)
3. [PHP最佳实践](#3-php最佳实践)
4. [预防措施](#4-预防措施)
5. [培训资源](#5-培训资源)

## 1. 命名空间规范

### 1.1 命名空间结构

AlingAi_pro项目使用`AlingAi`作为根命名空间，所有代码都应位于此命名空间下。

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
  │    └── Security\          # 安全服务
  │
  ├── Apps\                   # 应用程序
  │    ├── Platform\          # 平台应用
  │    └── Enterprise\        # 企业应用
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

### 1.2 接口和实现类的命名空间规则

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

### 1.3 命名空间映射到文件路径

命名空间应与文件路径保持一致：

```
命名空间: AlingAi\Engines\NLP
文件路径: ai-engines/nlp/
```

```
命名空间: AlingAi\Apps\Enterprise\Services
文件路径: apps/enterprise/Services/
```

### 1.4 命名空间检查工具

使用`check_interface_implementations.php`工具检查接口实现和命名空间一致性：

```bash
php check_interface_implementations.php --detailed
```

## 2. 代码质量自动化检查

### 2.1 工具清单

我们使用以下工具进行代码质量检查：

1. **PHP_CodeSniffer**：检查代码风格和编码标准
2. **PHPStan**：静态分析工具，检查类型错误和潜在问题
3. **Psalm**：类型检查工具，提供更严格的类型检查
4. **PHP-CS-Fixer**：自动修复代码风格问题
5. **接口实现检查工具**：检查接口实现完整性
6. **PHP Syntax Checker**：检查PHP语法错误

### 2.2 安装和配置

通过Composer安装：

```bash
composer require --dev squizlabs/php_codesniffer
composer require --dev phpstan/phpstan
composer require --dev vimeo/psalm
composer require --dev friendsofphp/php-cs-fixer
```

### 2.3 自动化检查脚本

使用`run_code_quality_checks.bat`脚本运行所有检查：

```batch
@echo off
echo ===================================
echo AlingAi Pro 代码质量检查工具
echo ===================================

echo 步骤1: PHP语法检查
for /r %%i in (ai-engines\*.php apps\*.php config\*.php) do (
    php -l "%%i" | findstr /v "No syntax errors"
)

echo 步骤2: 代码风格检查
vendor\bin\phpcs

echo 步骤3: 静态分析
vendor\bin\phpstan analyse

echo 步骤4: 类型检查
vendor\bin\psalm

echo 步骤5: 接口实现检查
php check_interface_implementations.php --detailed

pause
```

### 2.4 Git钩子集成

在`composer.json`中添加pre-commit钩子：

```json
{
  "scripts": {
    "pre-commit": [
      "php -l",
      "./vendor/bin/php-cs-fixer fix --dry-run",
      "./vendor/bin/phpstan analyse --no-progress",
      "php check_interface_implementations.php --ci"
    ]
  }
}
```

## 3. PHP最佳实践

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

### 3.2 接口实现规范

1. **方法签名一致**：实现类中的方法签名必须与接口定义完全一致
2. **参数类型和数量**：参数类型、数量和默认值必须匹配
3. **返回类型**：返回类型必须兼容

### 3.3 抽象类和方法

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

### 3.4 构造函数

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

### 3.5 变量作用域

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

## 4. 预防措施

### 4.1 统一的命名空间规范

1. **文档化规范**：维护并更新命名空间规范文档
2. **自动化验证**：使用自动化工具验证命名空间一致性
3. **代码模板**：为新文件提供标准化的代码模板

### 4.2 自动化检查工具

1. **持续集成**：在CI/CD流程中集成代码质量检查
2. **预提交检查**：使用Git钩子在提交前检查代码
3. **IDE集成**：配置IDE以实时检查代码问题

### 4.3 代码审查流程

1. **审查清单**：使用标准化的审查清单
2. **自动化审查**：使用自动化工具辅助代码审查
3. **结对编程**：鼓励结对编程以提高代码质量

### 4.4 编码规范

1. **PSR-12**：遵循PSR-12编码标准
2. **项目特定规范**：遵循项目特定的编码规范
3. **自动格式化**：使用自动格式化工具确保代码风格一致

## 5. 培训资源

### 5.1 内部培训

1. **新员工培训**：为新员工提供PHP最佳实践培训
2. **定期研讨会**：定期举办PHP最佳实践研讨会
3. **代码审查培训**：提供代码审查培训

### 5.2 外部资源

1. **官方文档**：
   - [PHP 官方文档](https://www.php.net/docs.php)
   - [PHP 8.1 新特性](https://www.php.net/releases/8.1/en.php)

2. **书籍**：
   - 《Modern PHP》by Josh Lockhart
   - 《PHP 7 Programming Cookbook》by Doug Bierer

3. **在线课程**：
   - Laracasts PHP 课程
   - Symfony Cast

### 5.3 持续学习

1. **代码示例库**：维护内部代码示例库
2. **知识分享**：鼓励团队成员分享知识和经验
3. **技术博客**：鼓励团队成员撰写技术博客

## 6. 总结

遵循本指南中的最佳实践，可以帮助我们：

1. 提高代码质量和可维护性
2. 减少常见错误和问题
3. 提高团队协作效率
4. 确保项目的长期稳定性

定期回顾和更新本指南，确保它与最新的PHP版本和最佳实践保持同步。 