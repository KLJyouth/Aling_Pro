# AlingAi Pro 代码质量工具使用指南

为了保证代码质量，避免语法错误和编码风格问题，我们引入了以下代码质量工具：

## 1. PHP_CodeSniffer

PHP_CodeSniffer 用于检查代码是否符合编码标准，可以检测和修复编码标准问题。

### 使用方法

检查代码：
```
vendor/bin/phpcs --standard=phpcs.xml 文件路径或目录
```

自动修复代码：
```
vendor/bin/phpcbf --standard=phpcs.xml 文件路径或目录
```

## 2. PHPStan

PHPStan 是一个静态分析工具，可以发现代码中的错误，而无需实际运行代码。

### 使用方法
```
vendor/bin/phpstan analyse --configuration=phpstan.neon 文件路径或目录
```

## 3. 提交前代码检查

我们提供了一个 pre-commit 钩子脚本，用于在提交代码前自动检查代码质量。

### 安装

1. 将 `pre-commit.php` 文件复制到 `.git/hooks/pre-commit`
2. 确保文件有执行权限

## 4. 编码规范

我们采用 PSR-12 编码标准，主要规范包括：

- 使用 4 个空格进行缩进，不使用制表符
- 行长度不超过 120 个字符
- 所有属性和方法必须声明可见性（public、protected 或 private）
- 控制结构关键字后必须有一个空格

## 5. 文件编码

所有PHP文件必须使用UTF-8编码，不带BOM。
