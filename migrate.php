<?php
/**
 * AlingAi Pro 数据库迁移工具
 * 
 * 用于执行数据库迁移操作
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set("display_errors", 1);

// 设置时区
date_default_timezone_set("Asia/Shanghai");

// 定义根目录
define("ROOT_DIR", __DIR__);
define("DATABASE_DIR", ROOT_DIR . "/database");
define("MIGRATIONS_DIR", DATABASE_DIR . "/migrations");
define("STORAGE_DIR", ROOT_DIR . "/storage");
define("DB_FILE", STORAGE_DIR . "/database/alingai.sqlite");

// 检查命令行参数
if ($argc < 2) {
    echo "用法: php migrate.php [命令] [参数...]\n";
    echo "可用命令:\n";
    echo "  migrate                执行所有未执行的迁移\n";
    echo "  migrate:rollback       回滚最后一批迁移\n";
    echo "  migrate:reset          回滚所有迁移\n";
    echo "  migrate:refresh        回滚所有迁移并重新执行\n";
    echo "  migrate:status         显示迁移状态\n";
    echo "  make:migration [名称]   创建新的迁移文件\n";
    exit(1);
}

// 获取命令
$command = $argv[1];

// 执行命令
switch ($command) {
    case "migrate":
        migrate();
        break;
    case "migrate:rollback":
        rollback();
        break;
    case "migrate:reset":
        reset_migrations();
        break;
    case "migrate:refresh":
        reset_migrations();
        migrate();
        break;
    case "migrate:status":
        status();
        break;
    case "make:migration":
        if ($argc < 3) {
            echo "错误: 缺少迁移名称\n";
            exit(1);
        }
        make_migration($argv[2]);
        break;
    default:
        echo "错误: 未知命令 \"$command\"\n";
        exit(1);
}

/**
 * 执行迁移
 */
function migrate() {
    echo "正在执行迁移...\n";
    
    // 创建迁移表（如果不存在）
    create_migrations_table();
    
    // 获取已执行的迁移
    $db = get_database_connection();
    $executed = [];
    $result = $db->query("SELECT migration FROM migrations");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $executed[] = $row["migration"];
    }
    
    // 获取所有迁移文件
    $migrations = get_migration_files();
    
    // 执行未执行的迁移
    $batch = get_next_batch();
    $count = 0;
    
    foreach ($migrations as $migration) {
        $name = pathinfo($migration, PATHINFO_FILENAME);
        
        if (!in_array($name, $executed)) {
            echo "迁移: $name\n";
            
            // 执行迁移
            $path = MIGRATIONS_DIR . "/" . $migration;
            
            // 判断文件类型
            $extension = pathinfo($migration, PATHINFO_EXTENSION);
            
            if ($extension === "php") {
                // PHP迁移文件
                require_once $path;
                
                // 提取类名
                $className = extract_migration_class_name(file_get_contents($path));
                
                if ($className) {
                    $instance = new $className();
                    
                    if (method_exists($instance, "up")) {
                        $instance->up();
                    } else {
                        echo "警告: $name 缺少 up 方法\n";
                    }
                } else {
                    echo "警告: 无法从 $name 提取类名\n";
                }
            } else if ($extension === "sql") {
                // SQL迁移文件
                $sql = file_get_contents($path);
                $db->exec($sql);
            } else {
                echo "警告: 不支持的迁移文件类型: $extension\n";
                continue;
            }
            
            // 记录迁移
            $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$name, $batch]);
            
            $count++;
        }
    }
    
    echo "迁移完成: $count 个文件已执行\n";
}

/**
 * 回滚迁移
 */
function rollback() {
    echo "正在回滚迁移...\n";
    
    // 创建迁移表（如果不存在）
    create_migrations_table();
    
    // 获取最后一批迁移
    $db = get_database_connection();
    $batch = $db->query("SELECT MAX(batch) AS batch FROM migrations")->fetch(PDO::FETCH_ASSOC)["batch"];
    
    if (!$batch) {
        echo "没有可回滚的迁移\n";
        return;
    }
    
    $result = $db->query("SELECT migration FROM migrations WHERE batch = $batch ORDER BY id DESC");
    $migrations = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $migrations[] = $row["migration"];
    }
    
    // 回滚迁移
    $count = 0;
    
    foreach ($migrations as $name) {
        echo "回滚: $name\n";
        
        // 查找迁移文件
        $file = find_migration_file($name);
        
        if ($file) {
            $path = MIGRATIONS_DIR . "/" . $file;
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            
            if ($extension === "php") {
                // PHP迁移文件
                require_once $path;
                
                // 提取类名
                $className = extract_migration_class_name(file_get_contents($path));
                
                if ($className) {
                    $instance = new $className();
                    
                    if (method_exists($instance, "down")) {
                        $instance->down();
                    } else {
                        echo "警告: $name 缺少 down 方法\n";
                    }
                } else {
                    echo "警告: 无法从 $name 提取类名\n";
                }
            } else if ($extension === "sql") {
                // SQL迁移文件无法自动回滚
                echo "警告: 无法自动回滚SQL迁移文件: $name\n";
            }
        } else {
            echo "警告: 找不到迁移文件: $name\n";
        }
        
        // 删除迁移记录
        $stmt = $db->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$name]);
        
        $count++;
    }
    
    echo "回滚完成: $count 个文件已回滚\n";
}

/**
 * 重置所有迁移
 */
function reset_migrations() {
    echo "正在重置所有迁移...\n";
    
    // 创建迁移表（如果不存在）
    create_migrations_table();
    
    // 获取所有已执行的迁移
    $db = get_database_connection();
    $result = $db->query("SELECT migration FROM migrations ORDER BY id DESC");
    $migrations = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $migrations[] = $row["migration"];
    }
    
    // 回滚所有迁移
    $count = 0;
    
    foreach ($migrations as $name) {
        echo "回滚: $name\n";
        
        // 查找迁移文件
        $file = find_migration_file($name);
        
        if ($file) {
            $path = MIGRATIONS_DIR . "/" . $file;
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            
            if ($extension === "php") {
                // PHP迁移文件
                require_once $path;
                
                // 提取类名
                $className = extract_migration_class_name(file_get_contents($path));
                
                if ($className) {
                    $instance = new $className();
                    
                    if (method_exists($instance, "down")) {
                        $instance->down();
                    } else {
                        echo "警告: $name 缺少 down 方法\n";
                    }
                } else {
                    echo "警告: 无法从 $name 提取类名\n";
                }
            } else if ($extension === "sql") {
                // SQL迁移文件无法自动回滚
                echo "警告: 无法自动回滚SQL迁移文件: $name\n";
            }
        } else {
            echo "警告: 找不到迁移文件: $name\n";
        }
        
        $count++;
    }
    
    // 清空迁移表
    $db->exec("DELETE FROM migrations");
    
    echo "重置完成: $count 个文件已回滚\n";
}

/**
 * 显示迁移状态
 */
function status() {
    echo "迁移状态:\n";
    
    // 创建迁移表（如果不存在）
    create_migrations_table();
    
    // 获取已执行的迁移
    $db = get_database_connection();
    $executed = [];
    $batches = [];
    $result = $db->query("SELECT migration, batch FROM migrations");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $executed[] = $row["migration"];
        $batches[$row["migration"]] = $row["batch"];
    }
    
    // 获取所有迁移文件
    $migrations = get_migration_files();
    
    // 显示状态
    echo str_repeat("-", 80) . "\n";
    echo sprintf("| %-50s | %-10s | %-10s |\n", "迁移", "状态", "批次");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($migrations as $migration) {
        $name = pathinfo($migration, PATHINFO_FILENAME);
        $status = in_array($name, $executed) ? "已执行" : "未执行";
        $batch = in_array($name, $executed) ? $batches[$name] : "";
        
        echo sprintf("| %-50s | %-10s | %-10s |\n", $name, $status, $batch);
    }
    
    echo str_repeat("-", 80) . "\n";
}

/**
 * 创建新的迁移文件
 */
function make_migration($name) {
    // 格式化名称
    $timestamp = date("Y_m_d_His");
    $filename = "{$timestamp}_{$name}.php";
    $path = MIGRATIONS_DIR . "/" . $filename;
    
    // 检查目录是否存在
    if (!is_dir(MIGRATIONS_DIR)) {
        mkdir(MIGRATIONS_DIR, 0755, true);
    }
    
    // 创建迁移文件
    $className = studly_case($name);
    
    $content = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {$className} extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
";
    
    file_put_contents($path, $content);
    
    echo "已创建迁移文件: $filename\n";
}

/**
 * 创建迁移表
 */
function create_migrations_table() {
    $db = get_database_connection();
    
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL
    )");
}

/**
 * 获取下一个批次号
 */
function get_next_batch() {
    $db = get_database_connection();
    $result = $db->query("SELECT MAX(batch) AS batch FROM migrations");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    return $row["batch"] ? $row["batch"] + 1 : 1;
}

/**
 * 获取所有迁移文件
 */
function get_migration_files() {
    $files = [];
    
    if (is_dir(MIGRATIONS_DIR)) {
        $dir = new DirectoryIterator(MIGRATIONS_DIR);
        
        foreach ($dir as $file) {
            if (!$file->isDot() && !$file->isDir()) {
                $extension = $file->getExtension();
                
                if ($extension === "php" || $extension === "sql") {
                    $files[] = $file->getFilename();
                }
            }
        }
        
        // 按名称排序
        sort($files);
    }
    
    return $files;
}

/**
 * 查找迁移文件
 */
function find_migration_file($name) {
    $files = get_migration_files();
    
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_FILENAME) === $name) {
            return $file;
        }
    }
    
    return null;
}

/**
 * 从文件内容中提取迁移类名
 */
function extract_migration_class_name($content) {
    if (preg_match("/class\s+([a-zA-Z0-9_]+)\s+extends\s+Migration/", $content, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * 将字符串转换为驼峰式大写开头
 */
function studly_case($value) {
    $value = ucwords(str_replace(["-", "_"], " ", $value));
    return str_replace(" ", "", $value);
}

/**
 * 获取数据库连接
 */
function get_database_connection() {
    static $db = null;
    
    if ($db === null) {
        if (!file_exists(DB_FILE)) {
            die("错误: 数据库文件不存在: " . DB_FILE);
        }
        
        try {
            $db = new PDO("sqlite:" . DB_FILE);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("错误: 无法连接到数据库: " . $e->getMessage());
        }
    }
    
    return $db;
}
