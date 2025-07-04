<?php
// 创建一个测试SQLite数据库连接
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite'];
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
    
    // 创建一个测试表
    $db->exec('CREATE TABLE IF NOT EXISTS test_table (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )'];
    
    // 插入测试数据
    $stmt = $db->prepare('INSERT INTO test_table (name) VALUES (:name)'];
    $stmt->execute([':name' => 'Test Entry ' . date('Y-m-d H:i:s')]];
    
    // 查询数据
    $result = $db->query('SELECT * FROM test_table ORDER BY id DESC LIMIT 5'];
    $rows = $result->fetchAll(PDO::FETCH_ASSOC];
    
    echo '<h1>SQLite测试成功</h1>';
    echo '<p>成功连接到SQLite数据库并执行操作</p>';
    
    echo '<h2>最近5条记录:</h2>';
    echo '<pre>';
    print_r($rows];
    echo '</pre>';
    
} catch (PDOException $e) {
    echo '<h1>SQLite测试失败</h1>';
    echo '<p>错误信息: ' . $e->getMessage() . '</p>';
}
?>
