<?php
// 检查数据库表
try {
    $pdo = new PDO('mysql:host=111.180.205.70;dbname=alingai;charset=utf8mb4', 'AlingAi', 'e5bjzeWCr7k38TrZ');
    $stmt = $pdo->query('SHOW TABLES');
    echo "Existing tables:\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "  - " . $row[0] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
