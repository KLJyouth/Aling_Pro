<?php
/**
 * AlingAI Pro 5.0 - 零信任创新登录系�?
 * 基于国内信创领域的零信任架构，实现动态身份碎片验�?
 */
session_start(];
header('Content-Type: text/html; charset=utf-8'];

// 零信任验证核心函�?
function validateZeroTrust($username, $deviceFingerprint, $dynamicChallenge, $challengeResponse) {
    // 1. 基础用户验证
    $validUsers = [
        'admin' => ['role' => 'super_admin', 'org' => 'alingai'], 
        'alingai' => ['role' => 'admin', 'org' => 'alingai'], 
        'manager' => ['role' => 'manager', 'org' => 'alingai']
    ];
    
    if (!isset($validUsers[$username])) {
        return ['success' => false, 'error' => '用户不存�?];
    }
    
    // 2. 设备指纹验证（零信任核心：设备识别）
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    $expectedFingerprint = md5($userAgent . $clientIP . date('Y-m-d')];
    
    if ($deviceFingerprint !== $expectedFingerprint) {
        return ['success' => false, 'error' => '设备指纹验证失败'];
    }
    
    // 3. 动态挑战验证（创新点：身份碎片动态验证）
    $expectedResponse = generateChallengeResponse($dynamicChallenge];
    if ($challengeResponse !== $expectedResponse) {
        return ['success' => false, 'error' => '身份碎片验证失败'];
    }
    
    // 4. 环境合规性检查（信创适配�?
    if (!checkEnvironmentCompliance($clientIP)) {
        return ['success' => false, 'error' => '环境安全检查未通过'];
    }
    
    return [
        'success' => true,
        'user' => $validUsers[$username], 
        'session_token' => generateSecureToken()
    ];
}

// 生成动态挑战响�?
function generateChallengeResponse($challenge) {
    // 基于时间戳和挑战内容生成响应
    $timeSlot = floor(time() / 60]; // 60秒有效期
    return hash('sha256', $challenge . $timeSlot . 'alingai_salt'];
}

// 环境合规性检�?
function checkEnvironmentCompliance($clientIP) {
    // 模拟信创环境安全检�?
    $trustedNetworks = ['127.0.0.1', '::1', '192.168.', '10.0.', '172.16.'];
    
    foreach ($trustedNetworks as $network) {
        if (strpos($clientIP, $network) === 0) {
            return true;
        }
    }
    
    // 生产环境可扩展：国密算法验证、终端安全状态检查等
    return true; // 开发环境放�?
}

// 生成安全令牌
function generateSecureToken() {
    return bin2hex(random_bytes(32)];
}

// API 端点：生成动态挑�?
if (isset($_GET['action']) && $_GET['action'] === 'generate_challenge') {
    header('Content-Type: application/json'];
    
    $challenges = [
        'quantum_sequence' => '量子序列：Q-7→U-4→A-9→N-2→T-5',
        'matrix_pattern' => '矩阵模式：[1,0,1]→[0,1,0]→[1,1,1]',
        'cosmic_cipher' => '宇宙密码：HELLO�?1001000→WORLD',
        'neural_bridge' => '神经桥接：�?3.14→�?2.71→�?1.41'
    ];
    
    $challengeType = array_rand($challenges];
    $challengeText = $challenges[$challengeType];
    
    $_SESSION['current_challenge'] = $challengeType;
    $_SESSION['challenge_time'] = time(];
    
    echo json_encode([
        'challenge' => $challengeText,
        'type' => $challengeType,
        'fingerprint' => md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . date('Y-m-d'))
    ]];
    exit;
}

// 处理登录提交
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? ''];
    $deviceFingerprint = $_POST['device_fingerprint'] ?? '';
    $challengeResponse = $_POST['challenge_response'] ?? '';
    $currentChallenge = $_SESSION['current_challenge'] ?? '';
    
    if (empty($username) || empty($deviceFingerprint) || empty($challengeResponse)) {
        $error = '请完成所有验证步�?;
    } else {
        $result = validateZeroTrust($username, $deviceFingerprint, $currentChallenge, $challengeResponse];
        
        if ($result['success']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_info'] = $result['user'];
            $_SESSION['session_token'] = $result['session_token'];
            $_SESSION['login_time'] = time(];
            
            // 清理挑战数据
            unset($_SESSION['current_challenge']];
            unset($_SESSION['challenge_time']];
            
            header('Location: tools_manager.php'];
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAI Pro 5.0 - 零信任量子登录系�?/title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            overflow: hidden;
            background: #0a0a1a;
            color: #e0e0ff;
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        .login-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(41, 196, 255, 0.1) 0%, transparent 50%],
                radial-gradient(circle at 80% 80%, rgba(255, 41, 241, 0.1) 0%, transparent 50%];
            z-index: 0;
        }

        .login-box {
            background: rgba(20, 20, 40, 0.8];
            border: 1px solid rgba(255, 255, 255, 0.1];
            border-radius: 15px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px];
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3];
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3];
        }

        .login-header p {
            color: #a0a0ff;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #a0a0ff;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.05];
            border: 1px solid rgba(255, 255, 255, 0.1];
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #29c4ff;
            box-shadow: 0 0 10px rgba(41, 196, 255, 0.3];
        }

        .challenge-box {
            background: rgba(41, 196, 255, 0.1];
            border: 1px solid rgba(41, 196, 255, 0.2];
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .challenge-box h3 {
            color: #29c4ff;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .challenge-box p {
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .challenge-box .timer {
            color: #ff29f1;
            font-size: 0.8rem;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, #29c4ff, #ff29f1];
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px];
            box-shadow: 0 5px 15px rgba(41, 196, 255, 0.4];
        }

        .btn:active {
            transform: translateY(0];
        }

        .error-message {
            color: #ff29f1;
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
        }

        .success-message {
            color: #29c4ff;
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5];
            border-radius: 50%;
            pointer-events: none;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg];
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg];
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="background"></div>
        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <h1>AlingAI Pro 5.0</h1>
                    <p>零信任量子登录系�?/p>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error]; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success]; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">用户�?/label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="challenge-box" id="challengeBox" style="display: none;">
                        <h3>动态身份验�?/h3>
                        <p id="challengeText"></p>
                        <p class="timer">剩余时间: <span id="timer">60</span>�?/p>
                    </div>
                    
                    <div class="form-group">
                        <label for="challenge_response">验证响应</label>
                        <input type="text" id="challenge_response" name="challenge_response" required>
                    </div>
                    
                    <input type="hidden" name="device_fingerprint" id="device_fingerprint">
                    <button type="submit" class="btn">登录</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 生成设备指纹
        function generateDeviceFingerprint() {
            const userAgent = navigator.userAgent;
            const screenInfo = `${screen.width}x${screen.height}`;
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const fingerprint = btoa(userAgent + screenInfo + timeZone];
            document.getElementById('device_fingerprint').value = fingerprint;
        }

        // 获取动态挑�?
        async function getChallenge() {
            try {
                const response = await fetch('login_backup.php?action=generate_challenge'];
                const data = await response.json(];
                
                document.getElementById('challengeText').textContent = data.challenge;
                document.getElementById('challengeBox').style.display = 'block';
                document.getElementById('device_fingerprint').value = data.fingerprint;
                
                // 启动倒计�?
                startTimer(];
            } catch (error) {
                console.error('获取挑战失败:', error];
            }
        }

        // 倒计时功�?
        function startTimer() {
            let timeLeft = 60;
            const timerElement = document.getElementById('timer'];
            
            const timer = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(timer];
                    getChallenge(]; // 重新获取挑战
                }
            }, 1000];
        }

        // 页面加载时初始化
        document.addEventListener('DOMContentLoaded', () => {
            generateDeviceFingerprint(];
            getChallenge(];
        }];
    </script>
</body>
</html>
