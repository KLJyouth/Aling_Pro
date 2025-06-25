<?php
/**
 * AlingAi Pro - 数据库导入工具
 * 此脚本用于导入数据库结构和初始数据
 * 支持大型SQL文件的分批导入
 * 
 * 使用方法：
 * 1. 通过Web访问: http://your-domain.com/install/import-database.php
 * 2. 通过命令行: php import-database.php [--mysql] [--sqlite] [--host=localhost] [--port=3306] [--user=root] [--password=your_password] [--database=alingai_pro]
 */

// 设置错误报告级别
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 命令行参数解析
$isCli = (php_sapi_name() === 'cli');
$options = [];

if ($isCli) {
    $options = getopt('', ['mysql', 'sqlite', 'host::', 'port::', 'user::', 'password::', 'database::', 'sql::', 'batch::']);
    
    // 设置默认值
    $dbType = isset($options['mysql']) ? 'mysql' : (isset($options['sqlite']) ? 'sqlite' : null);
    $host = $options['host'] ?? 'localhost';
    $port = $options['port'] ?? 3306;
    $user = $options['user'] ?? 'root';
    $password = $options['password'] ?? '';
    $database = $options['database'] ?? 'alingai_pro';
    $sqlFile = $options['sql'] ?? '../database/schema.sql';
    $batchSize = $options['batch'] ?? 20;
    
    // 如果没有指定数据库类型，提示并退出
    if ($dbType === null) {
        echo "请指定数据库类型: --mysql 或 --sqlite\n";
        exit(1);
    }
} else {
    // Web请求处理
    header('Content-Type: text/html; charset=utf-8');
    
    // 获取表单提交的数据或使用默认值
    $dbType = $_POST['db_type'] ?? null;
    $host = $_POST['host'] ?? 'localhost';
    $port = $_POST['port'] ?? 3306;
    $user = $_POST['user'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $database = $_POST['database'] ?? 'alingai_pro';
    $sqlFile = $_POST['sql_file'] ?? '../database/schema.sql';
    $batchSize = $_POST['batch_size'] ?? 20;
    
    // 检查是否是表单提交或第一次访问
    $isFormSubmit = isset($_POST['submit']);
    
    // 如果不是表单提交，显示表单并退出
    if (!$isFormSubmit) {
        showForm();
        exit;
    }
    
    // 验证参数
    if ($dbType === null) {
        showError("请选择数据库类型");
        exit;
    }
}

// 确保SQL文件存在
if (!file_exists($sqlFile)) {
    $message = "找不到SQL文件: $sqlFile";
    if ($isCli) {
        echo "$message\n";
        exit(1);
    } else {
        showError($message);
        exit;
    }
}

// 创建日志记录器
$logFile = './import-log-' . date('Y-m-d-H-i-s') . '.txt';
$logger = function($message) use ($logFile, $isCli) {
    $timestamp = date('[Y-m-d H:i:s]');
    $logMessage = "$timestamp $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    if ($isCli) {
        echo $message . "\n";
    }
};

// 开始导入过程
$logger("开始数据库导入过程...");
$logger("数据库类型: $dbType");
$logger("SQL文件: $sqlFile");

try {
    // 连接数据库
    if ($dbType === 'mysql') {
        $logger("连接到MySQL数据库...");
        $logger("主机: $host, 端口: $port, 用户: $user, 数据库: $database");
        
        // 尝试连接到数据库服务器
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查数据库是否存在，如果不存在则创建
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$database]);
        
        if (!$stmt->fetch()) {
            $logger("数据库 '$database' 不存在，正在创建...");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $logger("数据库创建成功");
        }
        
        // 连接到指定的数据库
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 导入MySQL数据库结构
        importMySQLDatabase($pdo, $sqlFile, $batchSize, $logger);
    } else if ($dbType === 'sqlite') {
        $logger("使用SQLite数据库...");
        
        // 确保数据库目录存在
        $dbDir = dirname($database);
        if (!is_dir($dbDir) && !empty($dbDir) && $dbDir !== '.') {
            mkdir($dbDir, 0755, true);
        }
        
        // 连接到SQLite数据库
        $pdo = new PDO("sqlite:$database");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 导入SQLite数据库结构
        importSQLiteDatabase($pdo, $sqlFile, $batchSize, $logger);
    } else {
        throw new Exception("不支持的数据库类型: $dbType");
    }
    
    $logger("数据库导入完成！");
    
    // 显示成功消息
    if (!$isCli) {
        showSuccess("数据库导入成功！详情请查看日志文件: $logFile");
    } else {
        echo "数据库导入成功！详情请查看日志文件: $logFile\n";
    }
} catch (Exception $e) {
    $errorMessage = "导入过程出错: " . $e->getMessage();
    $logger($errorMessage);
    
    if (!$isCli) {
        showError($errorMessage);
    } else {
        echo "$errorMessage\n";
        exit(1);
    }
}

/**
 * 导入MySQL数据库结构
 */
function importMySQLDatabase($pdo, $sqlFile, $batchSize, $logger) {
    $sql = file_get_contents($sqlFile);
    
    // 分割SQL语句
    $statements = splitSqlStatements($sql);
    $totalStatements = count($statements);
    
    $logger("共找到 " . $totalStatements . " 条SQL语句");
    $logger("以每批 " . $batchSize . " 条语句进行导入");
    
    $batches = array_chunk($statements, $batchSize);
    $batchCount = count($batches);
    
    $logger("共分为 " . $batchCount . " 批处理");
    
    $successCount = 0;
    $errorCount = 0;
    
    $pdo->beginTransaction();
    
    try {
        foreach ($batches as $batchIndex => $batch) {
            $logger("正在处理批次 " . ($batchIndex + 1) . "/" . $batchCount);
            
            foreach ($batch as $index => $statement) {
                $statement = trim($statement);
                
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                        $successCount++;
                    } catch (PDOException $e) {
                        $errorCount++;
                        $logger("SQL错误: " . $e->getMessage());
                        $logger("出错的SQL语句: " . $statement);
                        
                        // 如果是关键错误，抛出异常
                        if (isKeyError($e)) {
                            throw $e;
                        }
                    }
                }
            }
            
            // 提交每个批次的更改
            $pdo->commit();
            $pdo->beginTransaction();
            
            $logger("已完成批次 " . ($batchIndex + 1) . "/" . $batchCount . "，当前成功: " . $successCount . "，失败: " . $errorCount);
        }
        
        // 最后一个事务的提交
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("导入过程中出现严重错误: " . $e->getMessage());
    }
    
    $logger("导入完成，成功执行: " . $successCount . " 条语句，失败: " . $errorCount . " 条语句");
}

/**
 * 导入SQLite数据库结构
 */
function importSQLiteDatabase($pdo, $sqlFile, $batchSize, $logger) {
    $sql = file_get_contents($sqlFile);
    
    // 分割SQL语句
    $statements = splitSqlStatements($sql);
    $totalStatements = count($statements);
    
    $logger("共找到 " . $totalStatements . " 条SQL语句");
    $logger("以每批 " . $batchSize . " 条语句进行导入");
    
    $batches = array_chunk($statements, $batchSize);
    $batchCount = count($batches);
    
    $logger("共分为 " . $batchCount . " 批处理");
    
    $successCount = 0;
    $errorCount = 0;
    
    $pdo->beginTransaction();
    
    try {
        foreach ($batches as $batchIndex => $batch) {
            $logger("正在处理批次 " . ($batchIndex + 1) . "/" . $batchCount);
            
            foreach ($batch as $index => $statement) {
                $statement = trim($statement);
                
                if (!empty($statement)) {
                    // 转换MySQL语法为SQLite语法
                    $statement = convertMySQLToSQLite($statement);
                    
                    try {
                        $pdo->exec($statement);
                        $successCount++;
                    } catch (PDOException $e) {
                        $errorCount++;
                        $logger("SQL错误: " . $e->getMessage());
                        $logger("出错的SQL语句: " . $statement);
                        
                        // 如果是关键错误，抛出异常
                        if (isKeyError($e)) {
                            throw $e;
                        }
                    }
                }
            }
            
            // 提交每个批次的更改
            $pdo->commit();
            $pdo->beginTransaction();
            
            $logger("已完成批次 " . ($batchIndex + 1) . "/" . $batchCount . "，当前成功: " . $successCount . "，失败: " . $errorCount);
        }
        
        // 最后一个事务的提交
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("导入过程中出现严重错误: " . $e->getMessage());
    }
    
    $logger("导入完成，成功执行: " . $successCount . " 条语句，失败: " . $errorCount . " 条语句");
}

/**
 * 分割SQL语句
 */
function splitSqlStatements($sql) {
    // 移除注释
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // 按分号分割
    $statements = preg_split('/;\s*$/m', $sql);
    
    // 过滤空语句
    return array_filter($statements, function($stmt) {
        return trim($stmt) !== '';
    });
}

/**
 * 将MySQL语句转换为SQLite语句
 */
function convertMySQLToSQLite($statement) {
    // 替换AUTO_INCREMENT
    $statement = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $statement);
    
    // 移除ENGINE、CHARSET设置
    $statement = preg_replace('/ENGINE=InnoDB.*?;/i', ';', $statement);
    $statement = preg_replace('/COLLATE.*?;/i', ';', $statement);
    
    // 替换日期时间函数
    $statement = str_replace('CURRENT_TIMESTAMP', "datetime('now')", $statement);
    $statement = str_replace('NOW()', "datetime('now')", $statement);
    
    // 移除ON UPDATE CURRENT_TIMESTAMP
    $statement = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $statement);
    
    // INT类型转换
    $statement = preg_replace('/\bint\(\d+\)/i', 'INTEGER', $statement);
    
    // 替换枚举类型
    $statement = preg_replace('/enum\(([^)]+)\)/i', 'TEXT CHECK(value IN ($1))', $statement);
    
    return $statement;
}

/**
 * 判断是否为关键错误
 */
function isKeyError($exception) {
    // 表已存在不视为关键错误
    if (stripos($exception->getMessage(), 'already exists') !== false) {
        return false;
    }
    
    // 字段已存在不视为关键错误
    if (stripos($exception->getMessage(), 'duplicate column name') !== false) {
        return false;
    }
    
    return true;
}

/**
 * 显示HTML表单
 */
function showForm() {
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AlingAi Pro - 数据库导入工具</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            h1 {
                color: #2c3e50;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            input[type="text"], input[type="password"], input[type="number"], select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .radio-group {
                margin: 10px 0;
            }
            .radio-group label {
                display: inline;
                margin-right: 15px;
                font-weight: normal;
            }
            button {
                background-color: #3498db;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
            }
            button:hover {
                background-color: #2980b9;
            }
            .info {
                background-color: #f8f9fa;
                border-left: 4px solid #17a2b8;
                padding: 10px 15px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <h1>AlingAi Pro - 数据库导入工具</h1>
        <div class="info">
            <p>此工具用于导入AlingAi Pro数据库结构和初始数据。</p>
            <p>对于大型数据库，工具将以批次方式导入以提高成功率。</p>
        </div>
        
        <form method="post" action="">
            <div class="form-group">
                <label>数据库类型:</label>
                <div class="radio-group">
                    <label><input type="radio" name="db_type" value="mysql" checked> MySQL/MariaDB</label>
                    <label><input type="radio" name="db_type" value="sqlite"> SQLite</label>
                </div>
            </div>
            
            <div id="mysql-options">
                <div class="form-group">
                    <label for="host">数据库主机:</label>
                    <input type="text" id="host" name="host" value="localhost">
                </div>
                
                <div class="form-group">
                    <label for="port">数据库端口:</label>
                    <input type="number" id="port" name="port" value="3306">
                </div>
                
                <div class="form-group">
                    <label for="user">数据库用户名:</label>
                    <input type="text" id="user" name="user" value="root">
                </div>
                
                <div class="form-group">
                    <label for="password">数据库密码:</label>
                    <input type="password" id="password" name="password">
                </div>
                
                <div class="form-group">
                    <label for="database">数据库名称:</label>
                    <input type="text" id="database" name="database" value="alingai_pro">
                </div>
            </div>
            
            <div id="sqlite-options" style="display:none;">
                <div class="form-group">
                    <label for="sqlite-database">SQLite数据库文件路径:</label>
                    <input type="text" id="sqlite-database" name="sqlite_database" value="../../database/alingai.sqlite">
                </div>
            </div>
            
            <div class="form-group">
                <label for="sql-file">SQL文件路径:</label>
                <input type="text" id="sql-file" name="sql_file" value="../database/schema.sql">
            </div>
            
            <div class="form-group">
                <label for="batch-size">每批执行语句数:</label>
                <input type="number" id="batch-size" name="batch_size" value="20" min="1" max="100">
            </div>
            
            <button type="submit" name="submit">开始导入</button>
        </form>
        
        <script>
            // 切换数据库类型选项显示
            document.querySelectorAll(\'input[name="db_type"]\').forEach(radio => {
                radio.addEventListener(\'change\', function() {
                    if (this.value === \'mysql\') {
                        document.getElementById(\'mysql-options\').style.display = \'block\';
                        document.getElementById(\'sqlite-options\').style.display = \'none\';
                    } else {
                        document.getElementById(\'mysql-options\').style.display = \'none\';
                        document.getElementById(\'sqlite-options\').style.display = \'block\';
                    }
                });
            });
        </script>
    </body>
    </html>';
}

/**
 * 显示错误消息
 */
function showError($message) {
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AlingAi Pro - 导入错误</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            h1 {
                color: #e74c3c;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .error {
                background-color: #fdf7f7;
                border-left: 4px solid #e74c3c;
                padding: 10px 15px;
                margin: 20px 0;
            }
            .back-link {
                display: inline-block;
                margin-top: 20px;
                color: #3498db;
                text-decoration: none;
            }
            .back-link:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <h1>导入过程中发生错误</h1>
        <div class="error">
            <p>' . htmlspecialchars($message) . '</p>
        </div>
        <a href="' . $_SERVER['PHP_SELF'] . '" class="back-link">返回导入表单</a>
    </body>
    </html>';
}

/**
 * 显示成功消息
 */
function showSuccess($message) {
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AlingAi Pro - 导入成功</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            h1 {
                color: #27ae60;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .success {
                background-color: #f8f9f8;
                border-left: 4px solid #27ae60;
                padding: 10px 15px;
                margin: 20px 0;
            }
            .back-link {
                display: inline-block;
                margin-top: 20px;
                color: #3498db;
                text-decoration: none;
            }
            .back-link:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <h1>导入成功</h1>
        <div class="success">
            <p>' . htmlspecialchars($message) . '</p>
        </div>
        <a href="../index.php" class="back-link">返回首页</a>
    </body>
    </html>';
}
?> 