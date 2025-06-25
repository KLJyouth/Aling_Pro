<?php

/**
 * AlingAi Pro PHP 8.1 新特性演�?
 * 
 * 本脚本演示了PHP 8.1中的新特性，以及如何在AlingAi Pro系统中利用这些特�?
 */

echo "PHP 8.1 新特性演示\n";
echo "===================\n\n";

// 1. 枚举类型
echo "1. 枚举类型\n";
echo "------------\n";

// 定义状态枚�?
enum UserStatus: string {
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case BANNED = 'banned';
    
    // 枚举可以有方�?
    public function getLabel(): string {
        return match($this) {
            self::ACTIVE => '活跃',
            self::INACTIVE => '不活�?,
            self::PENDING => '待审�?,
            self::BANNED => '已封�?
        };
    }
    
    // 静态方�?
    public static function fromLabel(string $label): ?self {
        return match($label) {
            '活跃' => self::ACTIVE,
            '不活�? => self::INACTIVE,
            '待审�? => self::PENDING,
            '已封�? => self::BANNED,
            default => null
        };
    }
}

// 使用枚举
$status = UserStatus::ACTIVE;
echo "用户状�? {$status->value} ({$status->getLabel()})\n";

// 从标签创建枚�?
$statusFromLabel = UserStatus::fromLabel('待审�?];
echo "从标签创建的状�? {$statusFromLabel->value}\n";

// 使用枚举进行比较
if ($status === UserStatus::ACTIVE) {
    echo "用户是活跃的\n";
}

echo "\n";

// 2. Readonly 属�?
echo "2. Readonly 属性\n";
echo "----------------\n";

class User {
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        public string $displayName,
        private array $preferences = []
    ) {}
    
    public function getPreference(string $key, $default = null) {
        return $this->preferences[$key] ?? $default;
    }
    
    public function setPreference(string $key, $value): void {
        $this->preferences[$key] = $value;
    }
}

$user = new User(
    id: 'usr_123456',
    username: 'demo_user',
    email: 'demo@example.com',
    displayName: 'Demo User'
];

echo "用户ID: {$user->id}\n";
echo "用户�? {$user->username}\n";
echo "邮箱: {$user->email}\n";

// 可以修改非readonly属�?
$user->displayName = 'Updated Name';
echo "显示名称(已更�?: {$user->displayName}\n";

// 尝试修改readonly属性会导致错误
try {
    $user->id = 'new_id';
} catch (Error $e) {
    echo "错误: {$e->getMessage()}\n";
}

echo "\n";

// 3. First-class callable 语法
echo "3. First-class callable 语法\n";
echo "---------------------------\n";

// 定义一些函�?
function double(int $x): int {
    return $x * 2;
}

function triple(int $x): int {
    return $x * 3;
}

function applyTo[array $numbers, callable $func): array {
    return array_map($func, $numbers];
}

// 使用first-class callable语法
$doubleFunc = double(...];
$tripleFunc = triple(...];

$numbers = [1, 2, 3, 4, 5];
$doubled = applyTo[$numbers, $doubleFunc];
$tripled = applyTo[$numbers, $tripleFunc];

echo "原始数组: " . implode(', ', $numbers) . "\n";
echo "加倍后: " . implode(', ', $doubled) . "\n";
echo "三倍后: " . implode(', ', $tripled) . "\n";

echo "\n";

// 4. array_is_list 函数
echo "4. array_is_list 函数\n";
echo "-------------------\n";

$list1 = [1, 2, 3, 4, 5];
$list2 = ['a' => 1, 'b' => 2, 'c' => 3];
$list3 = [0 => 'a', 1 => 'b', 3 => 'c']; // 键不连续

echo "数组1是列表吗? " . (array_is_list($list1) ? '�? : '�?) . "\n";
echo "数组2是列表吗? " . (array_is_list($list2) ? '�? : '�?) . "\n";
echo "数组3是列表吗? " . (array_is_list($list3) ? '�? : '�?) . "\n";

echo "\n";

// 5. 在初始化器中使用new
echo "5. 在初始化器中使用new\n";
echo "---------------------\n";

class Logger {
    public function log(string $message): void {
        echo "[LOG] $message\n";
    }
}

class Service {
    public function __construct(
        private Logger $logger = new Logger()
    ) {}
    
    public function doSomething(): void {
        $this->logger->log("Service is doing something"];
    }
}

$service = new Service(];
$service->doSomething(];

echo "\n";

// 6. never 返回类型
echo "6. never 返回类型\n";
echo "----------------\n";

function redirect(string $url): never {
    echo "重定向到: $url\n";
    echo "此函数永不返回\n";
    exit;
}

// 不会执行到这�?
try {
    redirect('https://example.com'];
    echo "这行不会被执行\n";
} catch (Throwable $e) {
    echo "这里也不会被执行\n";
} finally {
    echo "finally块会被执行\n";
}

echo "这行不会被执行\n"; 

