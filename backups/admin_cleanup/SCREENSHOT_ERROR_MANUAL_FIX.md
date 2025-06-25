# 截图错误手动修复指南

## 准备工作

1. **创建备份目录**
   ```bash
   mkdir -p backups/screenshot_fix_$(date +%Y%m%d_%H%M%S)
   ```

2. **备份需要修复的文件**
   ```bash
   # 备份服务类文件
   cp apps/ai-platform/Services/CV/ComputerVisionProcessor.php backups/screenshot_fix_date/
   cp apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php backups/screenshot_fix_date/
   cp apps/ai-platform/Services/Speech/SpeechProcessor.php backups/screenshot_fix_date/
   cp apps/blockchain/Services/BlockchainServiceManager.php backups/screenshot_fix_date/
   cp apps/blockchain/Services/SmartContractManager.php backups/screenshot_fix_date/
   cp apps/blockchain/Services/WalletManager.php backups/screenshot_fix_date/
   
   # 备份配置文件
   cp completed/Config/database.php backups/screenshot_fix_date/
   cp config/database.php backups/screenshot_fix_date/
   ```

## 常见错误修复步骤

### 1. 修复构造函数多余的括号

**查找这样的代码：**
```php
public function __construct((array $config = [])) {
```

**修改为：**
```php
public function __construct(array $config = []) {
```

### 2. 修复配置文件中值缺少引号

**查找这样的代码：**
```php
'mysql' => [
    'driver' => mysql,
    'host' => localhost,
]
```

**修改为：**
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => 'localhost',
]
```

### 3. 修复行尾多余的分号和引号

**查找这样的代码：**
```php
'max_nodes' => 10000,';
'max_relationships' => 50000,';
```

**修改为：**
```php
'max_nodes' => 10000,
'max_relationships' => 50000,
```

### 4. 修复私有变量错误声明

**查找这样的代码：**
```php
private $entities = $this->models['entity_extraction']->extract($text, $options);';
```

**修改为：**
```php
$entities = $this->models['entity_extraction']->extract($text, $options);
```

### 5. 修复对象方法调用语法错误

**查找这样的代码：**
```php
$containersomething()
```

**修改为：**
```php
$container->something()
```

### 6. 修复命名空间引用问题

**查找这样的代码：**
```php
WebController::class
```

**修改为：**
```php
\AlingAi\Controllers\WebController::class
```

### 7. 修复字符串缺少结束引号

**查找这样的代码：**
```php
protected string $version = "
```

**修改为：**
```php
protected string $version = "6.0.0";
```

### 8. 修复PDO常量引用

**查找这样的代码：**
```php
'options' => [
    'PDO::ATTR_EMULATE_PREPARES' => false,
    'PDO::ATTR_STRINGIFY_FETCHES' => false,
]
```

**修改为：**
```php
'options' => [
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_STRINGIFY_FETCHES => false,
]
```

## 手动修复特定文件

### 1. 修复 KnowledgeGraphProcessor.php

打开文件 `apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php`

1. 搜索 `__construct((array $config = []))`，修改为 `__construct(array $config = [])`
2. 搜索结尾为 `,';` 的行，删除 `';`
3. 搜索 `private $` 后面跟变量名和赋值的情况，删除 `private` 关键字

### 2. 修复 SpeechProcessor.php

打开文件 `apps/ai-platform/Services/Speech/SpeechProcessor.php`

1. 搜索 `__construct((array $config = []))`，修改为 `__construct(array $config = [])`
2. 搜索结尾为 `,';` 的行，删除 `';`
3. 搜索 `private $` 后面跟变量名和赋值的情况，删除 `private` 关键字

### 3. 修复 SmartContractManager.php

打开文件 `apps/blockchain/Services/SmartContractManager.php`

1. 查找 `protected string $serviceName = 'SmartContractManager';'`，修改为 `protected string $serviceName = 'SmartContractManager';`
2. 查找 `protected string $version = '6.0.0';'`，修改为 `protected string $version = '6.0.0';`
3. 查找结尾为 `';` 的数组项，删除多余的 `';`

### 4. 修复 database.php

打开文件 `config/database.php` 和 `completed/Config/database.php`

1. 查找 `'driver' => mysql,`，修改为 `'driver' => 'mysql',`
2. 查找 `'PDO::ATTR_EMULATE_PREPARES' => false,`，修改为 `PDO::ATTR_EMULATE_PREPARES => false,`

## 使用现有工具进行修复

如果您可以运行PHP和批处理文件，可以使用项目中已有的工具：

1. **使用fix_php_simple.php修复特定文件**
   ```bash
   php fix_php_simple.php apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php
   ```

2. **使用fix_all_php_errors.php修复所有文件**
   ```bash
   php fix_all_php_errors.php
   ```

3. **使用fix_screenshot_errors.php修复截图中的错误**
   ```bash
   php fix_screenshot_errors.php
   ```

## 验证修复

修复后，使用PHP的语法检查功能验证：

```bash
php -l apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php
```

如果输出为：`No syntax errors detected in apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php`，则表示修复成功。

## 长期解决方案

1. **使用IDE实时语法检查**：配置PHPStorm或VSCode进行实时PHP语法检查
2. **遵循编码规范**：使用项目中的`CHINESE_ENCODING_STANDARDS.md`规范
3. **定期验证**：使用`validate_only.php`定期检查项目中的问题
4. **自动化测试**：在CI/CD流程中加入语法检查步骤