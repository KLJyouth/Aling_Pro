<?php
/**
 * AlingAi Pro - 登录处理
 * 
 * 处理用户登录请求，包括常规登录和社交登录
 */

// 启动会话
session_start();

// 设置增强的安全头部
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 检查是否已登录
if (isset($_SESSION['user_id']) && !isset($_GET['logout'])) {
    // 已登录，重定向到仪表盘
    header('Location: /dashboard');
    exit;
}

// 处理登出请求
if (isset($_GET['logout'])) {
    // 清除会话数据
    session_unset();
    session_destroy();
    
    // 重定向到首页
    header('Location: /');
    exit;
}

// 处理社交登录
if (isset($_GET['provider'])) {
    $provider = $_GET['provider'];
    
    // 验证提供商
    if (!in_array($provider, ['google', 'github'])) {
        $_SESSION['error'] = '不支持的登录方式';
        header('Location: /login');
        exit;
    }
    
    // 检查是否为回调
    if (isset($_GET['callback'])) {
        // 处理社交登录回调
        processSocialCallback($provider);
    } else {
        // 重定向到社交登录提供商
        redirectToProvider($provider);
    }
    
    exit;
}

// 处理表单提交
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 加载安全配置
    $securityConfig = include __DIR__ . '/config/security.php';
    
    // 检查IP限制
    if ($securityConfig['ip_restrictions']['enabled']) {
        $clientIP = $_SERVER['REMOTE_ADDR'];
        
        // 检查黑名单
        if (in_array($clientIP, $securityConfig['ip_restrictions']['blacklist'])) {
            $error = '您的IP地址已被禁止访问';
            // 记录安全事件
            logSecurityEvent('login_blocked', [
                'reason' => 'ip_blacklisted',
                'ip' => $clientIP
            ]);
            sleep(2); // 延迟响应，防止暴力破解
        }
        
        // 检查白名单（如果启用）
        if (!empty($securityConfig['ip_restrictions']['whitelist']) && 
            !in_array($clientIP, $securityConfig['ip_restrictions']['whitelist'])) {
            $error = '您的IP地址未被授权访问';
            // 记录安全事件
            logSecurityEvent('login_blocked', [
                'reason' => 'ip_not_whitelisted',
                'ip' => $clientIP
            ]);
            sleep(2); // 延迟响应，防止暴力破解
        }
    }
    
    // 检查请求限制
    if (empty($error) && $securityConfig['rate_limiting']['enabled']) {
        $clientIP = $_SERVER['REMOTE_ADDR'];
        $attempts = checkLoginAttempts($clientIP);
        
        if ($attempts >= $securityConfig['rate_limiting']['login_attempts']['max_attempts']) {
            $error = '登录尝试次数过多，请稍后再试';
            // 记录安全事件
            logSecurityEvent('login_blocked', [
                'reason' => 'rate_limited',
                'ip' => $clientIP,
                'attempts' => $attempts
            ]);
            sleep(5); // 延迟响应，防止暴力破解
        }
    }
    
    if (empty($error)) {
        // 验证表单数据
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (!$email) {
            $error = '请输入有效的电子邮件地址';
        } elseif (empty($password)) {
            $error = '请输入密码';
        } else {
            // 尝试登录
            $user = authenticateUser($email, $password);
            
            if ($user) {
                // 检查用户状态
                if ($user['status'] !== 'active') {
                    $error = '您的账户未激活，请先验证邮箱';
                    // 记录安全事件
                    logSecurityEvent('login_failed', [
                        'reason' => 'account_inactive',
                        'email' => $email,
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                } else {
                    // 检查是否需要双因素认证
                    $requireTwoFactor = false;
                    if ($securityConfig['two_factor']['enabled']) {
                        if (in_array($user['role'], $securityConfig['two_factor']['enforce_for_roles'])) {
                            $requireTwoFactor = true;
                        }
                    }
                    
                    if ($requireTwoFactor && !isset($_POST['two_factor_code'])) {
                        // 生成并发送双因素认证码
                        $tfaCode = generateTwoFactorCode($user['id']);
                        sendTwoFactorCode($user['email'], $tfaCode);
                        
                        // 保存用户信息到会话，但标记为未完成双因素认证
                        $_SESSION['tfa_pending'] = true;
                        $_SESSION['tfa_user_id'] = $user['id'];
                        $_SESSION['tfa_remember'] = $remember;
                        
                        // 显示双因素认证表单
                        $showTwoFactorForm = true;
                    } else if ($requireTwoFactor && isset($_POST['two_factor_code'])) {
                        // 验证双因素认证码
                        $tfaCode = $_POST['two_factor_code'];
                        if (verifyTwoFactorCode($user['id'], $tfaCode)) {
                            // 双因素认证成功，完成登录
                            completeLogin($user, $remember);
                        } else {
                            $error = '双因素认证码无效或已过期';
                            // 记录安全事件
                            logSecurityEvent('login_failed', [
                                'reason' => 'invalid_tfa_code',
                                'email' => $email,
                                'ip' => $_SERVER['REMOTE_ADDR']
                            ]);
                        }
                    } else {
                        // 不需要双因素认证，直接完成登录
                        completeLogin($user, $remember);
                    }
                }
            } else {
                $error = '邮箱或密码错误';
                // 记录失败的登录尝试
                recordFailedLogin($email, $_SERVER['REMOTE_ADDR']);
                
                // 记录安全事件
                logSecurityEvent('login_failed', [
                    'reason' => 'invalid_credentials',
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                
                // 延迟响应，防止暴力破解
                sleep(1);
            }
        }
    }
}

/**
 * 完成登录过程
 * 
 * @param array $user 用户数据
 * @param bool $remember 是否记住登录状态
 */
function completeLogin($user, $remember) {
    // 加载安全配置
    $securityConfig = include __DIR__ . '/config/security.php';
    
    // 清除双因素认证标记
    unset($_SESSION['tfa_pending']);
    unset($_SESSION['tfa_user_id']);
    unset($_SESSION['tfa_remember']);
    
    // 设置会话安全选项
    session_regenerate_id(true);
    
    // 设置会话过期时间
    $_SESSION['created_at'] = time();
    $_SESSION['expires_at'] = time() + ($securityConfig['session']['lifetime'] * 60);
    $_SESSION['last_activity'] = time();
    
    // 设置用户信息
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // 设置设备标识
    $deviceId = $_COOKIE['device_id'] ?? null;
    if (!$deviceId) {
        $deviceId = bin2hex(random_bytes(16));
        setcookie('device_id', $deviceId, time() + 60*60*24*365, '/', '', true, true);
    }
    $_SESSION['device_id'] = $deviceId;
    
    // 如果选择了"记住我"，设置cookie
    if ($remember) {
        $token = generateRememberToken($user['id']);
        setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', true, true);
    }
    
    // 记录登录信息
    recordLogin($user['id'], $_SERVER['REMOTE_ADDR']);
    
    // 记录安全事件
    logSecurityEvent('login_success', [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'is_admin' => ($user['role'] === 'admin')
    ]);
    
    // 如果是管理员登录，发送通知
    if ($user['role'] === 'admin' && $securityConfig['notifications']['security_events']['admin_login']) {
        sendSecurityNotification('admin_login', [
            'user_id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'time' => date('Y-m-d H:i:s')
        ]);
    }
    
    // 重定向到仪表盘
    header('Location: /dashboard');
    exit;
}

/**
 * 检查登录尝试次数
 * 
 * @param string $ip IP地址
 * @return int 尝试次数
 */
function checkLoginAttempts($ip) {
    $db = connectToDatabase();
    $securityConfig = include __DIR__ . '/config/security.php';
    
    // 获取配置的衰减时间（分钟）
    $decayMinutes = $securityConfig['rate_limiting']['login_attempts']['decay_minutes'];
    
    // 计算时间窗口
    $timeWindow = date('Y-m-d H:i:s', time() - ($decayMinutes * 60));
    
    // 查询在时间窗口内的失败尝试次数
    $stmt = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND success = 0 AND created_at > ?');
    $stmt->execute([$ip, $timeWindow]);
    
    return (int)$stmt->fetchColumn();
}

/**
 * 记录失败的登录尝试
 * 
 * @param string $email 邮箱
 * @param string $ip IP地址
 */
function recordFailedLogin($email, $ip) {
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO login_attempts (email, ip_address, user_agent, success, created_at) VALUES (?, ?, ?, 0, NOW())');
    $stmt->execute([$email, $ip, $_SERVER['HTTP_USER_AGENT']]);
}

/**
 * 生成双因素认证码
 * 
 * @param int $userId 用户ID
 * @return string 认证码
 */
function generateTwoFactorCode($userId) {
    // 生成6位随机数字
    $code = sprintf('%06d', mt_rand(0, 999999));
    $expires = date('Y-m-d H:i:s', time() + 600); // 10分钟有效期
    
    $db = connectToDatabase();
    
    // 删除旧的认证码
    $stmt = $db->prepare('DELETE FROM two_factor_codes WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // 插入新的认证码
    $stmt = $db->prepare('INSERT INTO two_factor_codes (user_id, code, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $code, $expires]);
    
    return $code;
}

/**
 * 发送双因素认证码
 * 
 * @param string $email 邮箱
 * @param string $code 认证码
 * @return bool 是否发送成功
 */
function sendTwoFactorCode($email, $code) {
    $subject = 'AlingAi Pro - 安全验证码';
    
    $message = "
    <html>
    <head>
        <title>安全验证码</title>
    </head>
    <body>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
            <div style='background-color: #6B46C1; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;'>
                <h1>安全验证码</h1>
            </div>
            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px;'>
                <p>您正在登录 AlingAi Pro，请使用以下验证码完成登录：</p>
                <p style='text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #6B46C1;'>
                    {$code}
                </p>
                <p>此验证码将在10分钟后过期。</p>
                <p>如果您没有尝试登录，请忽略此邮件并考虑修改您的密码。</p>
                <p>谢谢！<br>AlingAi Pro 安全团队</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: AlingAi Pro <noreply@alingai.pro>\r\n";
    
    // 实际项目中，应该使用专业的邮件发送服务
    return mail($email, $subject, $message, $headers);
}

/**
 * 验证双因素认证码
 * 
 * @param int $userId 用户ID
 * @param string $code 认证码
 * @return bool 是否有效
 */
function verifyTwoFactorCode($userId, $code) {
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT id FROM two_factor_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()');
    $stmt->execute([$userId, $code]);
    
    $result = $stmt->fetch() !== false;
    
    if ($result) {
        // 验证成功后删除认证码，防止重复使用
        $stmt = $db->prepare('DELETE FROM two_factor_codes WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
    
    return $result;
}

/**
 * 记录安全事件
 * 
 * @param string $event 事件类型
 * @param array $data 事件数据
 */
function logSecurityEvent($event, $data) {
    $securityConfig = include __DIR__ . '/config/security.php';
    
    if ($securityConfig['logging']['enabled']) {
        $db = connectToDatabase();
        $stmt = $db->prepare('INSERT INTO security_logs (event_type, event_data, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$event, json_encode($data), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    }
}

/**
 * 发送安全通知
 * 
 * @param string $event 事件类型
 * @param array $data 事件数据
 */
function sendSecurityNotification($event, $data) {
    $securityConfig = include __DIR__ . '/config/security.php';
    $channel = $securityConfig['notifications']['default_channel'];
    
    switch ($channel) {
        case 'email':
            sendSecurityEmail($event, $data);
            break;
        case 'sms':
            // 实现SMS通知
            break;
        case 'push':
            // 实现推送通知
            break;
    }
}

/**
 * 发送安全事件邮件通知
 * 
 * @param string $event 事件类型
 * @param array $data 事件数据
 */
function sendSecurityEmail($event, $data) {
    // 获取管理员邮箱
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT email FROM users WHERE role = "admin" AND status = "active"');
    $stmt->execute();
    $adminEmails = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($adminEmails)) {
        return;
    }
    
    $subject = 'AlingAi Pro - 安全事件通知: ' . ucfirst($event);
    
    $message = "
    <html>
    <head>
        <title>安全事件通知</title>
    </head>
    <body>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
            <div style='background-color: #6B46C1; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;'>
                <h1>安全事件通知</h1>
            </div>
            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px;'>
                <p>系统检测到以下安全事件：</p>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <th style='text-align: left; padding: 8px; border-bottom: 1px solid #ddd;'>事件类型</th>
                        <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($event) . "</td>
                    </tr>";
    
    foreach ($data as $key => $value) {
        $message .= "
                    <tr>
                        <th style='text-align: left; padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars(ucfirst($key)) . "</th>
                        <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($value) . "</td>
                    </tr>";
    }
    
    $message .= "
                </table>
                <p>请及时检查系统安全状态。</p>
                <p>谢谢！<br>AlingAi Pro 安全团队</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: AlingAi Pro <noreply@alingai.pro>\r\n";
    
    // 发送给所有管理员
    foreach ($adminEmails as $email) {
        mail($email, $subject, $message, $headers);
    }
}

/**
 * 验证用户凭据
 * 
 * @param string $email 用户邮箱
 * @param string $password 用户密码
 * @return array|false 成功时返回用户数据，失败时返回false
 */
function authenticateUser($email, $password) {
    // 连接数据库
    $db = connectToDatabase();
    
    // 查询用户
    $stmt = $db->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND status = "active"');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 验证密码
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

/**
 * 连接到数据库
 * 
 * @return PDO 数据库连接
 */
function connectToDatabase() {
    $host = 'localhost';
    $dbname = 'alingai_pro';
    $username = 'root';
    $password = '';
    
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die('数据库连接失败: ' . $e->getMessage());
    }
}

/**
 * 生成"记住我"令牌
 * 
 * @param int $userId 用户ID
 * @return string 生成的令牌
 */
function generateRememberToken($userId) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 60*60*24*30);
    
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $token, $expires]);
    
    return $token;
}

/**
 * 记录用户登录
 * 
 * @param int $userId 用户ID
 * @param string $ip 用户IP地址
 */
function recordLogin($userId, $ip) {
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $ip, $_SERVER['HTTP_USER_AGENT']]);
}

/**
 * 重定向到社交登录提供商
 * 
 * @param string $provider 提供商名称
 */
function redirectToProvider($provider) {
    // 加载OAuth配置
    $config = include __DIR__ . '/config/oauth.php';
    
    // 检查提供商是否启用
    if (!isset($config['providers'][$provider]) || !$config['providers'][$provider]['enabled']) {
        $_SESSION['error'] = '不支持的登录方式';
        header('Location: /login');
        exit;
    }
    
    $providerConfig = $config['providers'][$provider];
    
    // 生成随机状态令牌以防止CSRF攻击
    $state = bin2hex(random_bytes(16));
    $_SESSION[$config['state_key']] = [
        'state' => $state,
        'provider' => $provider,
        'expires' => time() + $config['state_ttl']
    ];
    
    // 构建授权URL
    $params = [
        'client_id' => $providerConfig['client_id'],
        'redirect_uri' => $providerConfig['redirect_uri'],
        'response_type' => 'code',
        'scope' => implode(' ', $providerConfig['scopes']),
        'state' => $state
    ];
    
    $url = $providerConfig['auth_url'] . '?' . http_build_query($params);
    header("Location: $url");
    exit;
}

/**
 * 处理社交登录回调
 * 
 * @param string $provider 提供商名称
 */
function processSocialCallback($provider) {
    // 加载OAuth配置
    $config = include __DIR__ . '/config/oauth.php';
    
    // 检查提供商是否启用
    if (!isset($config['providers'][$provider]) || !$config['providers'][$provider]['enabled']) {
        $_SESSION['error'] = '不支持的登录方式';
        header('Location: /login');
        exit;
    }
    
    // 验证状态令牌
    if (!isset($_GET['state']) || 
        !isset($_SESSION[$config['state_key']]) || 
        $_GET['state'] !== $_SESSION[$config['state_key']]['state'] ||
        $_SESSION[$config['state_key']]['provider'] !== $provider ||
        $_SESSION[$config['state_key']]['expires'] < time()) {
        
        // 清除会话中的状态令牌
        unset($_SESSION[$config['state_key']]);
        
        $_SESSION['error'] = '安全验证失败，请重试';
        header('Location: /login');
        exit;
    }
    
    // 清除会话中的状态令牌
    unset($_SESSION[$config['state_key']]);
    
    // 检查是否有错误
    if (isset($_GET['error'])) {
        $_SESSION['error'] = '授权失败: ' . $_GET['error'];
        header('Location: /login');
        exit;
    }
    
    // 检查是否有授权码
    if (!isset($_GET['code'])) {
        $_SESSION['error'] = '授权失败: 未收到授权码';
        header('Location: /login');
        exit;
    }
    
    $providerConfig = $config['providers'][$provider];
    $code = $_GET['code'];
    
    // 使用授权码获取访问令牌
    $tokenData = getAccessToken($provider, $code, $providerConfig);
    if (!$tokenData) {
        $_SESSION['error'] = '获取访问令牌失败';
        header('Location: /login');
        exit;
    }
    
    // 使用访问令牌获取用户信息
    $socialUser = getUserInfo($provider, $tokenData['access_token'], $providerConfig);
    if (!$socialUser) {
        $_SESSION['error'] = '获取用户信息失败';
        header('Location: /login');
        exit;
    }
    
    // 查找或创建用户
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT id, name, email, role, status FROM users WHERE email = ?');
    $stmt->execute([$socialUser['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user && $config['auto_create_user']) {
        // 创建新用户
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $socialUser['name'],
            $socialUser['email'],
            $password,
            $config['default_role'],
            $config['require_email_verification'] ? 'pending' : 'active'
        ]);
        
        $userId = $db->lastInsertId();
        $user = [
            'id' => $userId,
            'name' => $socialUser['name'],
            'email' => $socialUser['email'],
            'role' => $config['default_role'],
            'status' => $config['require_email_verification'] ? 'pending' : 'active'
        ];
        
        // 记录OAuth连接
        $stmt = $db->prepare('INSERT INTO oauth_connections (user_id, provider, provider_user_id, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$userId, $provider, $socialUser['id']]);
    } elseif (!$user) {
        $_SESSION['error'] = '自动创建用户已禁用';
        header('Location: /login');
        exit;
    }
    
    // 检查用户状态
    if ($user['status'] !== 'active') {
        $_SESSION['error'] = '您的账户未激活，请先验证邮箱';
        header('Location: /login');
        exit;
    }
    
    // 登录用户
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // 记录登录信息
    recordLogin($user['id'], $_SERVER['REMOTE_ADDR']);
    
    // 记录OAuth登录
    if ($config['logging']['enabled']) {
        $stmt = $db->prepare('INSERT INTO oauth_logins (user_id, provider, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$user['id'], $provider, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    }
    
    // 重定向到成功页面
    header('Location: ' . $config['success_redirect']);
    exit;
}

/**
 * 获取访问令牌
 * 
 * @param string $provider 提供商名称
 * @param string $code 授权码
 * @param array $config 提供商配置
 * @return array|false 成功时返回令牌数据，失败时返回false
 */
function getAccessToken($provider, $code, $config) {
    $params = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code' => $code,
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['token_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($provider === 'github') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return false;
    }
    
    // 解析响应
    if ($provider === 'github') {
        $data = json_decode($response, true);
    } else {
        parse_str($response, $data);
    }
    
    return isset($data['access_token']) ? $data : false;
}

/**
 * 获取用户信息
 * 
 * @param string $provider 提供商名称
 * @param string $accessToken 访问令牌
 * @param array $config 提供商配置
 * @return array|false 成功时返回用户数据，失败时返回false
 */
function getUserInfo($provider, $accessToken, $config) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['userinfo_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // 设置授权头
    if ($provider === 'google') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $accessToken,
            'User-Agent: AlingAi-Pro-App',
            'Accept: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return false;
    }
    
    $userData = json_decode($response, true);
    
    // 提取用户信息
    $user = [];
    
    if ($provider === 'google') {
        $user['id'] = $userData['sub'];
        $user['name'] = $userData['name'] ?? ($userData['given_name'] . ' ' . $userData['family_name']);
        $user['email'] = $userData['email'];
        $user['avatar'] = $userData['picture'] ?? null;
    } else if ($provider === 'github') {
        $user['id'] = $userData['id'];
        $user['name'] = $userData['name'] ?? $userData['login'];
        
        // GitHub可能不会直接返回邮箱，需要额外请求
        if (empty($userData['email'])) {
            $emails = getGitHubEmails($accessToken);
            $user['email'] = $emails ? $emails[0]['email'] : null;
        } else {
            $user['email'] = $userData['email'];
        }
        
        $user['avatar'] = $userData['avatar_url'] ?? null;
    }
    
    return $user;
}

/**
 * 获取GitHub用户的邮箱
 * 
 * @param string $accessToken 访问令牌
 * @return array|false 成功时返回邮箱列表，失败时返回false
 */
function getGitHubEmails($accessToken) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $accessToken,
        'User-Agent: AlingAi-Pro-App',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return false;
    }
    
    $emails = json_decode($response, true);
    
    // 筛选出主要的、已验证的邮箱
    $primaryEmails = array_filter($emails, function($email) {
        return $email['primary'] === true && $email['verified'] === true;
    });
    
    if (count($primaryEmails) > 0) {
        return $primaryEmails;
    }
    
    // 如果没有主要邮箱，返回任何已验证的邮箱
    $verifiedEmails = array_filter($emails, function($email) {
        return $email['verified'] === true;
    });
    
    return count($verifiedEmails) > 0 ? $verifiedEmails : false;
}

// 显示登录页面
// 检查是否需要显示双因素认证表单
$showTwoFactorForm = isset($_SESSION['tfa_pending']) && $_SESSION['tfa_pending'] === true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安全登录 - AlingAi Pro</title>
    <meta name="description" content="AlingAi Pro零信任安全登录系统">
    
    <!-- 核心资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind配置 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'quantum-purple': '#6B46C1',
                        'quantum-blue': '#3B82F6',
                        'quantum-cyan': '#06B6D4',
                        'neon-green': '#10B981',
                        'cyber-orange': '#F59E0B'
                    },
                    fontFamily: {
                        'mono': ['JetBrains Mono', 'monospace'],
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background: linear-gradient(135deg, #0F0F23 0%, #1A1A40 25%, #2D1B69 50%, #6B46C1 100%);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6B46C1, #3B82F6);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
        }
        
        .social-btn {
            transition: all 0.3s ease;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .verification-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- 登录容器 -->
    <div class="glassmorphism rounded-3xl p-8 w-full max-w-lg shadow-2xl z-10 relative">
        <!-- Logo和标题 -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-4 glassmorphism rounded-full flex items-center justify-center">
                <i class="fas fa-shield-halved text-3xl text-blue-400"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">安全登录</h1>
            <p class="text-gray-300">多重身份验证保护您的账户安全</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($showTwoFactorForm): ?>
            <!-- 双因素认证表单 -->
            <form method="POST" action="/login" class="space-y-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-4 glassmorphism rounded-full flex items-center justify-center">
                        <i class="fas fa-key text-2xl text-green-400"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">双因素认证</h2>
                    <p class="text-gray-300">我们已向您的邮箱发送了验证码，请输入验证码完成登录</p>
                </div>
                
                <div>
                    <label for="two_factor_code" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-lock-open mr-2"></i>验证码
                    </label>
                    <div class="flex justify-center space-x-2">
                        <input type="text" id="two_factor_code" name="two_factor_code" required
                            class="w-full px-4 py-3 glassmorphism rounded-lg text-white text-center text-2xl font-mono placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                            placeholder="000000" maxlength="6" autocomplete="one-time-code" inputmode="numeric">
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="w-full btn-primary text-white py-3 px-4 rounded-lg font-medium">
                        <i class="fas fa-check-circle mr-2"></i>验证并登录
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/login?resend=1" class="text-blue-400 hover:text-blue-300 text-sm">
                        <i class="fas fa-redo mr-2"></i>重新发送验证码
                    </a>
                </div>
            </form>
        <?php else: ?>
            <!-- 常规登录表单 -->
            <form method="POST" action="/login" class="space-y-6">
                <div>
                    <label for="email" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-envelope mr-2"></i>邮箱地址
                    </label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="请输入您的邮箱地址">
                </div>
                
                <div>
                    <label for="password" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>密码
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                            placeholder="请输入您的密码">
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-300">记住我</label>
                    </div>
                    <a href="/forgot-password" class="text-sm text-blue-400 hover:text-blue-300">忘记密码？</a>
                </div>
                
                <div>
                    <button type="submit" class="w-full btn-primary text-white py-3 px-4 rounded-lg font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>登录
                    </button>
                </div>
                
                <!-- 分隔线 -->
                <div class="relative flex items-center my-8">
                    <div class="flex-grow border-t border-gray-600"></div>
                    <span class="flex-shrink mx-4 text-gray-400">或使用以下方式登录</span>
                    <div class="flex-grow border-t border-gray-600"></div>
                </div>
                
                <!-- 社交登录按钮 -->
                <div class="grid grid-cols-2 gap-4">
                    <a href="/login/google" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                        <i class="fab fa-google text-red-400 mr-2"></i> Google登录
                    </a>
                    <a href="/login/github" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                        <i class="fab fa-github text-gray-300 mr-2"></i> GitHub登录
                    </a>
                </div>
                
                <!-- 注册链接 -->
                <div class="mt-8 text-center">
                    <p class="text-gray-400">
                        还没有账号？
                        <a href="/register" class="text-blue-400 hover:text-blue-300">立即注册</a>
                    </p>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        // 切换密码显示/隐藏
        const togglePasswordBtn = document.getElementById('togglePassword');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // 切换图标
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
        
        // 双因素认证码输入优化
        const tfaInput = document.getElementById('two_factor_code');
        if (tfaInput) {
            tfaInput.addEventListener('input', function() {
                // 只允许输入数字
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // 自动提交表单当输入6位数字
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
        }
    </script>
</body>
</html>

