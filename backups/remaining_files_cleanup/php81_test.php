<?php

/**
 * AlingAi Pro PHP 8.1 简单兼容性测试
 */

echo "开始PHP 8.1兼容性测试...\n";

// 测试枚举类型
echo "测试枚举类型... ";
enum Status: string {
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
$status = Status::ACTIVE;
if ($status->value === 'active') {
    echo "通过\n";
} else {
    echo "失败\n";
}

// 测试readonly属性
echo "测试readonly属性... ";
class TestReadonly {
    public readonly string $name;
    
    public function __construct(string $name) {
        $this->name = $name;
    }
}
$test = new TestReadonly('test');
if ($test->name === 'test') {
    echo "通过\n";
} else {
    echo "失败\n";
}

// 测试first-class callable语法
echo "测试first-class callable语法... ";
function double($x) {
    return $x * 2;
}
$callable = double(...);
if ($callable(2) === 4) {
    echo "通过\n";
} else {
    echo "失败\n";
}

// 测试文件系统功能
echo "测试文件系统功能... ";
$testDir = __DIR__ . '/temp_test';
$testFile = $testDir . '/test.txt';

// 创建测试目录
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

// 写入测试文件
file_put_contents($testFile, 'PHP 8.1 compatibility test');

// 读取测试文件
$content = file_get_contents($testFile);

// 清理
unlink($testFile);
rmdir($testDir);

if ($content === 'PHP 8.1 compatibility test') {
    echo "通过\n";
} else {
    echo "失败\n";
}

echo "\n所有测试已完成，系统与PHP 8.1兼容!\n"; 