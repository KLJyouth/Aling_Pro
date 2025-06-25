---
id: chinese-encoding-standards
title: 中文编码规范指南
sidebar_label: 中文编码规范
---

# 中文编码规范指南

## 基本原则

1. **统一使用UTF-8编码**：所有包含中文的PHP文件必须使用UTF-8编码保存，不使用BOM标记。
2. **避免中文直接编码**：尽量使用Unicode编码点表示中文字符，特别是在正则表达式中。
3. **使用标准化函数**：在处理中文字符串时，优先使用PHP的多字节字符串函数(mb_*)。
4. **编辑器统一配置**：团队成员使用的编辑器必须统一配置为UTF-8编码。

## 编码实践

### 1. 正确设置编码头信息

在PHP文件开头添加适当的编码声明：

```php
// 文件开头设置字符集
header("Content-Type: text/html; charset=utf-8");
```

### 2. 使用Unicode编码点替代直接中文字符

在代码中，尤其是正则表达式中，使用Unicode编码点表示中文字符：

```php
// 不推荐
$pattern = '/^[，。！？：；、（）【】《》""'']+$/u';

// 推荐
$pattern = '/^[\x{FF0C}\x{3002}\x{FF01}\x{FF1F}\x{FF1A}\x{FF1B}\x{3001}\x{FF08}\x{FF09}\x{3010}\x{3011}\x{300A}\x{300B}\x{201C}\x{201D}\x{2018}\x{2019}]+$/u';
```

### 3. 使用多字节字符串函数

处理中文字符串时，应使用PHP的mb_*系列函数：

```php
// 不推荐
$length = strlen($chinese_text);

// 推荐
$length = mb_strlen($chinese_text, 'UTF-8');
```

### 4. JSON编码中文时避免转义

在输出JSON数据时，设置不转义Unicode字符：

```php
echo json_encode($data, JSON_UNESCAPED_UNICODE);
```

### 5. 数据库连接和查询

确保数据库连接和表设置为UTF-8编码：

```php
// PDO连接
$pdo = new PDO('mysql:host=localhost;dbname=mydb;charset=utf8mb4', $username, $password);

// 或者在连接后设置
$mysqli->set_charset('utf8mb4');
```

## 避免的做法

1. **不要使用Windows记事本编辑PHP文件**，它可能默认使用不同的编码保存文件。
2. **不要混用不同的编码**，所有文件应该统一使用UTF-8。
3. **不要在字符串中直接连接中文和变量**，使用占位符或模板字符串。
4. **不要使用非标准的字符串处理函数**处理中文。

## 编辑器配置指南

### VS Code
1. 打开设置(Ctrl+,)
2. 搜索"encoding"
3. 将"Files: Encoding"设置为"utf8"

### PHPStorm
1. 进入File > Settings > Editor > File Encodings
2. 将"Global Encoding"和"Project Encoding"设置为"UTF-8"
3. 将"Default encoding for properties files"设置为"UTF-8"

### Sublime Text
1. 打开文件时，选择View > Encoding > UTF-8
2. 保存文件时，选择File > Save with Encoding > UTF-8

## 检查和修复编码问题

定期使用项目中的验证工具检查编码问题：

```
php validate_only.php
```

发现问题后，使用修复工具进行修复：

```
php systematic_fix.php
```

## 新文件创建指南

1. 创建新文件时，确保从一开始就使用UTF-8编码保存。
2. 添加适当的文件头注释，包括编码信息。
3. 如果文件包含中文，使用上述指南中的最佳实践。

通过遵循这些编码规范，我们可以避免中文乱码问题，确保代码在不同环境中的兼容性，并提高整体代码质量。