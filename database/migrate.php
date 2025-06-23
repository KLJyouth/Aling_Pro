<?php
/**
 * AlingAi Pro 数据库迁移执行脚本 (已重构)
 */

// 自动加载器
$autoloader = require_once __DIR__ . '/../autoload.php';
if (!$autoloader) {
    die("错误: 无法加载自动加载器。请运行 'composer install'。\n");
}

use AlingAi\Core\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 定义应用根目录
define('APP_ROOT', dirname(__DIR__));

// 设置一个简单的日志记录器
$logger = new Logger('migrate');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// 显示标题
echo "===========================================\n";
echo "      AlingAi Pro 数据库迁移工具\n";
echo "===========================================\n\n";

// 检查必要的PHP扩展
if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    die("错误: PDO 和 pdo_mysql 扩展是必需的。\n");
}

$migrationsDir = APP_ROOT . '/database/migrations';
if (!is_dir($migrationsDir)) {
    die("错误: 迁移目录不存在: $migrationsDir\n");
}

// 执行迁移
echo "开始执行数据库迁移...\n\n";

try {
    // 获取数据库连接
    $dbManager = new DatabaseManager($logger);
    $db = $dbManager->getConnection();
    
    // 检查并创建迁移状态表
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 获取已执行的迁移
    $executedMigrations = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

    // 获取当前最大的批次数
    $batch = ($db->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?: 0) + 1;

    // 扫描迁移目录获取所有迁移文件
    $migrationFiles = array_diff(scandir($migrationsDir), ['.', '..']);
    sort($migrationFiles); // 按文件名排序

    $newMigrations = 0;

    foreach ($migrationFiles as $migrationFile) {
        if (in_array($migrationFile, $executedMigrations)) {
            continue; // 跳过已执行的迁移
        }

        $migrationPath = $migrationsDir . '/' . $migrationFile;
        if (pathinfo($migrationPath, PATHINFO_EXTENSION) !== 'sql') {
            continue; // 只处理.sql文件
        }
        
        echo "正在执行迁移: $migrationFile\n";
        
        // 读取并执行SQL文件
        $sql = file_get_contents($migrationPath);
        
        // 移除注释并根据分号分割语句
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        // 在事务中执行迁移
        $db->beginTransaction();
        try {
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                     $db->exec($statement);
                }
            }
            
            // 记录迁移
            $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationFile, $batch]);
            
            $db->commit();

            $newMigrations++;
            echo "迁移成功: $migrationFile\n";

        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("迁移 '$migrationFile' 失败: " . $e->getMessage());
        }
    }
    
    if ($newMigrations > 0) {
        echo "\n成功执行了 " . $newMigrations . " 个新的数据库迁移。\n";
    } else {
        echo "\n数据库已经是最新状态，无需执行新的迁移。\n";
    }

} catch (PDOException $e) {
    echo "\n数据库操作失败: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n发生未知错误: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n数据库迁移完成！\n";
echo "===========================================\n";

