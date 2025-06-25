<?php

// 测试SQLite连接
try {
    $db = new PDO('sqlite:database/database.sqlite'];
    echo 'SQLite连接成功�?;
} catch (PDOException $e) {
    echo 'SQLite连接错误: ' . $e->getMessage(];
}
