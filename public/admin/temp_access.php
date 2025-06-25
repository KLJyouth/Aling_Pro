<?php
/**
 * 临时工具管理器访问入�?- 用于测试和开�?
 * 注意：生产环境请删除此文�?
 */
session_start(];

// 临时设置登录会话（仅用于开发测试）
$_SESSION['admin_logged_in'] = true;
$_SESSION['username'] = 'admin';
$_SESSION['login_time'] = time(];
$_SESSION['zero_trust_verified'] = true;

// 重定向到正常的工具管理器
header('Location: tools_manager.php'];
exit;
?>
