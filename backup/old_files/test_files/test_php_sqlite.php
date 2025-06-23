<?php
echo "Testing PHP and SQLite...\n";
echo "PHP Version: " . phpversion() . "\n";

// 检查SQLite扩展
if (extension_loaded('pdo_sqlite')) {
    echo "PDO SQLite: 支持\n";
    
    try {
        $pdo = new PDO('sqlite::memory:');
        echo "SQLite内存数据库: 连接成功\n";
        
        $pdo->exec("CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)");
        $pdo->exec("INSERT INTO test (name) VALUES ('测试')");
        
        $stmt = $pdo->query("SELECT * FROM test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "测试查询结果: " . json_encode($result) . "\n";
        
        echo "✅ SQLite功能正常\n";
    } catch (Exception $e) {
        echo "❌ SQLite错误: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ PDO SQLite扩展未安装\n";
}

echo "测试完成\n";
?>
