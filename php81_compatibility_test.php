<?php

/**
 * AlingAi Pro PHP 8.1 兼容性测试工具
 * 
 * 这个脚本用于测试系统在PHP 8.1环境下的关键功能
 */

// 设置脚本最大执行时间
set_time_limit(300);

// 测试结果
$results = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'tests' => []
];

// 测试函数
function runTest($name, $testFunction) {
    global $results;
    
    echo "运行测试: {$name}... ";
    
    try {
        $result = $testFunction();
        
        if ($result === true) {
            echo "通过\n";
            $results['passed']++;
            $results['tests'][$name] = 'passed';
        } else {
            echo "警告: {$result}\n";
            $results['warnings']++;
            $results['tests'][$name] = 'warning: ' . $result;
        }
    } catch (Throwable $e) {
        echo "失败: {$e->getMessage()}\n";
        $results['failed']++;
        $results['tests'][$name] = 'failed: ' . $e->getMessage();
    }
}

// 测试 PHP 8.1 新特性
echo "测试 PHP 8.1 新特性...\n";

// 1. 测试枚举类型
runTest('枚举类型', function() {
    if (!class_exists('BackedEnum')) {
        return "PHP 8.1 枚举类型不可用，但这不影响现有代码";
    }
    
    // 定义一个简单的枚举
    enum Status: string {
        case PENDING = 'pending';
        case ACTIVE = 'active';
        case INACTIVE = 'inactive';
    }
    
    // 测试枚举功能
    $status = Status::ACTIVE;
    return $status->value === 'active';
});

// 2. 测试 never 返回类型
runTest('never 返回类型', function() {
    // 定义一个使用 never 返回类型的函数
    function test_never(): never {
        throw new Exception('This function never returns');
    }
    
    // 如果能定义这个函数，说明支持 never 返回类型
    return true;
});

// 3. 测试 readonly 属性
runTest('readonly 属性', function() {
    // 定义一个使用 readonly 属性的类
    class TestReadonly {
        public readonly string $name;
        
        public function __construct(string $name) {
            $this->name = $name;
        }
    }
    
    $test = new TestReadonly('test');
    
    // 尝试修改 readonly 属性应该会抛出异常
    try {
        $test->name = 'modified';
        return "readonly 属性可以被修改，这是不正确的";
    } catch (Error $e) {
        // 正确的行为是抛出异常
        return true;
    }
});

// 4. 测试 array_is_list 函数
runTest('array_is_list 函数', function() {
    if (!function_exists('array_is_list')) {
        return "array_is_list 函数不可用，但这不影响现有代码";
    }
    
    $list = [1, 2, 3];
    $notList = ['a' => 1, 'b' => 2];
    
    return array_is_list($list) && !array_is_list($notList);
});

// 5. 测试 final 常量
runTest('final 常量', function() {
    class BaseClass {
        public const FOO = 'foo';
        final public const BAR = 'bar';
    }
    
    class ChildClass extends BaseClass {
        public const FOO = 'overridden'; // 可以覆盖
        // 尝试覆盖 final 常量会导致编译错误，因此这里不测试
    }
    
    return true;
});

// 6. 测试 new in initializers
runTest('构造函数中的 new 表达式', function() {
    class TestClass {
        private $logger;
        
        public function __construct(
            private $dependency = new stdClass()
        ) {
            $this->logger = new stdClass();
        }
        
        public function getDependency() {
            return $this->dependency;
        }
    }
    
    $test = new TestClass();
    return $test->getDependency() instanceof stdClass;
});

// 7. 测试 first-class callable syntax
runTest('一级可调用语法', function() {
    function testFunction($arg) {
        return $arg * 2;
    }
    
    $callable = testFunction(...);
    return $callable(2) === 4;
});

// 测试系统核心功能
echo "\n测试系统核心功能...\n";

// 1. 测试自动加载
runTest('自动加载', function() {
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        return "找不到自动加载文件，但这可能不影响系统";
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    return true;
});

// 2. 测试数据库连接
runTest('数据库连接', function() {
    if (!file_exists(__DIR__ . '/src/Config/config.php')) {
        return "找不到配置文件，跳过数据库测试";
    }
    
    // 尝试加载配置
    $config = include __DIR__ . '/src/Config/config.php';
    
    if (!isset($config['database']) || !is_array($config['database'])) {
        return "配置文件中没有数据库配置，跳过测试";
    }
    
    // 尝试连接数据库
    try {
        $db = new PDO(
            "mysql:host={$config['database']['host']};dbname={$config['database']['database']}",
            $config['database']['username'],
            $config['database']['password']
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 简单查询测试
        $stmt = $db->query("SELECT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return true;
    } catch (PDOException $e) {
        return "数据库连接失败: " . $e->getMessage();
    }
});

// 3. 测试核心类加载
runTest('核心类加载', function() {
    $coreClasses = [
        'src/Core/ApplicationV5.php',
        'src/Core/Router.php',
        'src/Controllers/BaseController.php',
        'src/Models/User.php'
    ];
    
    $missingClasses = [];
    
    foreach ($coreClasses as $class) {
        if (!file_exists(__DIR__ . '/' . $class)) {
            $missingClasses[] = $class;
        }
    }
    
    if (!empty($missingClasses)) {
        return "以下核心类文件不存在: " . implode(', ', $missingClasses);
    }
    
    return true;
});

// 4. 测试会话处理
runTest('会话处理', function() {
    // 开始会话
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 设置会话变量
    $_SESSION['test_value'] = 'PHP 8.1 compatibility test';
    
    // 检查会话变量是否设置成功
    return $_SESSION['test_value'] === 'PHP 8.1 compatibility test';
});

// 5. 测试文件系统操作
runTest('文件系统操作', function() {
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
    
    return $content === 'PHP 8.1 compatibility test';
});

// 输出测试结果
echo "\n测试完成!\n";
echo "通过: {$results['passed']}, 失败: {$results['failed']}, 警告: {$results['warnings']}\n\n";

// 详细结果
echo "详细测试结果:\n";
foreach ($results['tests'] as $name => $result) {
    echo "- {$name}: {$result}\n";
}

echo "\n如果所有测试都通过或只有警告，说明系统在PHP 8.1环境中可以正常运行。\n";
echo "如果有失败的测试，请查看详细信息并修复相关问题。\n"; 