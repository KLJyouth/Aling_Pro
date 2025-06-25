<?php
/**
 * AlingAI Pro 5.0 - 零信任量子登录系�?
 * 基于图片科技感设计的创新登录界面
 * 融合全息环、量子球体、粒子系统等元素
 */
session_start(];
header('Content-Type: text/html; charset=utf-8'];

// 零信任验证核心函�?
function validateZeroTrust($username, $verifyCode, $deviceFingerprint) {
    // 1. 动态身份碎片验�?
    $expectedPattern = generateDynamicChallenge($username];
    
    // 2. 设备指纹验证（模拟零信任环境检查）
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    $deviceHash = md5($userAgent . $clientIP . date('Y-m-d-H')];
    
    // 3. 环境合规检查（信创场景适配�?
    $allowedIPs = ['127.0.0.1', '::1', '192.168.', '10.0.', '172.16.'];
    $isCompliantEnvironment = false;
    foreach ($allowedIPs as $ipPrefix) {
        if (strpos($clientIP, $ipPrefix) === 0) {
            $isCompliantEnvironment = true;
            break;
        }
    }
    
    // 4. 综合零信任判�?
    if ($verifyCode === $expectedPattern && 
        $deviceFingerprint === $deviceHash && 
        $isCompliantEnvironment &&
        in_[$username, ['admin', 'alingai', 'root'])) {
        return true;
    }
    
    return false;
}

// 生成动态挑�?
function generateDynamicChallenge($username) {
    $time = date('H:i'];
    $hash = substr(md5($username . $time], 0, 6];
    return strtoupper($hash];
}

// 获取设备指纹
function getDeviceFingerprint() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    return md5($userAgent . $clientIP . date('Y-m-d-H')];
}

// 处理AJAX请求 - 获取动态挑�?
if (isset($_GET['action']) && $_GET['action'] === 'get_challenge') {
    $username = $_GET['username'] ?? '';
    if ($username) {
        $challenge = generateDynamicChallenge($username];
        $deviceFingerprint = getDeviceFingerprint(];
        echo json_encode([
            'challenge' => $challenge,
            'deviceFingerprint' => $deviceFingerprint,
            'hint' => "量子验证�? {$challenge} | 当前时间: " . date('H:i:s')
        ]];
    }
    exit;
}

// 处理登录提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? ''];
    $verifyCode = trim($_POST['verify_code'] ?? ''];
    $deviceFingerprint = $_POST['device_fingerprint'] ?? '';
    
    if (validateZeroTrust($username, $verifyCode, $deviceFingerprint)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time(];
        $_SESSION['zero_trust_verified'] = true;
        header('Location: tools_manager.php'];
        exit;
    } else {
        $error = '零信任验证失败：身份碎片不匹配或环境未通过安全检�?;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlingAI Pro 5.0 - 零信任量子门�?/title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'JetBrains Mono', 'Consolas', 'SF Mono', monospace;
            overflow: hidden;
            background: #0a0a1a;
            color: #ffffff;
            height: 100vh;
        }

        /* 量子背景 - 模拟图片中的科技渐变 */
        .quantum-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, 
                #0a0a1a 0%,
                #1a1a2e 15%, 
                #16213e 30%, 
                #0f3460 45%, 
                #533483 60%, 
                #7209b7 75%,
                #a31acb 90%,
                #cc2b5e 100%];
            animation: background-pulse 10s infinite alternate;
        }

        @keyframes background-pulse {
            0% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        /* 粒子系统 - 模拟图片中的光点效果 */
        .particles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(0,255,255,0.8) 50%, transparent 100%];
            border-radius: 50%;
            animation: particle-float 8s infinite linear;
            pointer-events: none;
        }

        @keyframes particle-float {
            0% { 
                transform: translateY(100vh) translateX(0px) scale(0];
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% { 
                transform: translateY(-10vh) translateX(100px) scale(1];
                opacity: 0;
            }
        }

        /* 全息环形 - 模拟图片左侧的紫色圆�?*/
        .holographic-ring {
            position: absolute;
            top: 15%;
            left: 8%;
            width: 350px;
            height: 350px;
            border: 4px solid rgba(138, 43, 226, 0.8];
            border-radius: 50%;
            animation: ring-rotate 12s infinite linear;
            box-shadow: 
                0 0 30px rgba(138, 43, 226, 0.6],
                inset 0 0 30px rgba(138, 43, 226, 0.3],
                0 0 60px rgba(138, 43, 226, 0.4];
        }

        .holographic-ring::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 3px dashed rgba(255, 0, 255, 0.6];
            border-radius: 50%;
            animation: ring-rotate 8s infinite linear reverse;
        }

        .holographic-ring::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            background: radial-gradient(circle, #ff00ff 0%, transparent 70%];
            border-radius: 50%;
            transform: translate(-50%, -50%];
            box-shadow: 
                0 0 20px #ff00ff,
                0 0 40px #ff00ff;
            animation: core-pulse 2s infinite alternate;
        }

        @keyframes ring-rotate {
            from { transform: rotate(0deg]; }
            to { transform: rotate(360deg]; }
        }

        @keyframes core-pulse {
            0% { transform: translate(-50%, -50%) scale(0.8]; }
            100% { transform: translate(-50%, -50%) scale(1.5]; }
        }

        /* 量子球体 - 模拟图片右侧的发光星�?*/
        .quantum-sphere {
            position: absolute;
            top: 25%;
            right: 12%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle at 25% 25%, 
                rgba(255, 255, 255, 0.4) 0%, 
                rgba(0, 255, 255, 0.8) 20%,
                rgba(138, 43, 226, 0.9) 40%, 
                rgba(75, 0, 130, 0.95) 70%, 
                rgba(25, 25, 112, 1) 100%];
            border-radius: 50%;
            box-shadow: 
                0 0 80px rgba(138, 43, 226, 0.8],
                0 0 120px rgba(75, 0, 130, 0.6],
                inset -20px -20px 40px rgba(0, 0, 0, 0.3];
            animation: sphere-pulse 5s infinite alternate;
            position: relative;
        }

        .quantum-sphere::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 20%;
            width: 30px;
            height: 30px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.9) 0%, transparent 70%];
            border-radius: 50%;
            animation: highlight-move 6s infinite ease-in-out;
        }

        @keyframes sphere-pulse {
            0% { 
                transform: scale(0.95];
                box-shadow: 0 0 80px rgba(138, 43, 226, 0.8];
            }
            100% { 
                transform: scale(1.05];
                box-shadow: 0 0 120px rgba(138, 43, 226, 1], 0 0 160px rgba(75, 0, 130, 0.8];
            }
        }

        @keyframes highlight-move {
            0%, 100% { transform: translate(0, 0]; }
            50% { transform: translate(20px, 10px]; }
        }

        /* Hello World 文字 - 模拟图片底部的发光文�?*/
        .hello-world-text {
            position: absolute;
            bottom: 18%;
            left: 50%;
            transform: translateX(-50%];
            font-size: 4.5rem;
            font-weight: 900;
            background: linear-gradient(45deg, 
                #00ffff 0%, 
                #ff00ff 25%, 
                #ffff00 50%, 
                #00ff00 75%, 
                #00ffff 100%];
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 
                0 0 40px rgba(0, 255, 255, 0.8],
                0 0 80px rgba(255, 0, 255, 0.6],
                0 0 120px rgba(255, 255, 0, 0.4];
            animation: text-gradient 4s infinite, text-glow 3s infinite alternate;
            letter-spacing: 0.1em;
        }

        @keyframes text-gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes text-glow {
            0% { 
                filter: brightness(1) saturate(1];
                transform: translateX(-50%) scale(1];
            }
            100% { 
                filter: brightness(1.3) saturate(1.5];
                transform: translateX(-50%) scale(1.02];
            }
        }

        /* 代码雨效�?- 模拟图片左侧的代码流 */
        .code-rain {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            opacity: 0.15;
            overflow: hidden;
        }

        .code-column {
            position: absolute;
            top: -100%;
            color: #00ff00;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            line-height: 1.2;
            animation: code-fall 8s linear infinite;
            white-space: pre;
        }

        @keyframes code-fall {
            0% { transform: translateY(-100vh]; opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh]; opacity: 0; }
        }

        /* 登录面板 - 玻璃毛玻璃效�?*/
        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%];
            z-index: 100;
        }

        .login-panel {
            width: 480px;
            padding: 45px;
            background: rgba(255, 255, 255, 0.08];
            backdrop-filter: blur(25px];
            border: 2px solid rgba(255, 255, 255, 0.15];
            border-radius: 25px;
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.4],
                inset 0 1px 0 rgba(255, 255, 255, 0.2],
                0 0 100px rgba(138, 43, 226, 0.3];
            animation: panel-enter 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275];
            position: relative;
            overflow: hidden;
        }

        .login-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.03], transparent];
            animation: panel-shine 3s infinite;
        }

        @keyframes panel-enter {
            0% { 
                opacity: 0; 
                transform: scale(0.7) translateY(50px];
                filter: blur(10px];
            }
            100% { 
                opacity: 1; 
                transform: scale(1) translateY(0];
                filter: blur(0];
            }
        }

        @keyframes panel-shine {
            0% { transform: rotate(0deg) translate(-100%, -100%]; }
            100% { transform: rotate(0deg) translate(100%, 100%]; }
        }

        /* 登录头部 */
        .login-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 12px;
            background: linear-gradient(45deg, #00ffff, #ff00ff, #00ffff];
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(0, 255, 255, 0.5];
            animation: title-gradient 3s infinite;
        }

        @keyframes title-gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .login-subtitle {
            color: #a0a0ff;
            font-size: 1rem;
            font-weight: 300;
            opacity: 0.9;
        }

        /* 表单样式 */
        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #00ffff;
            font-size: 0.95rem;
            font-weight: 600;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.3];
        }

        .form-input {
            width: 100%;
            padding: 18px 25px;
            background: rgba(255, 255, 255, 0.12];
            border: 2px solid rgba(0, 255, 255, 0.3];
            border-radius: 15px;
            color: #ffffff;
            font-size: 1.1rem;
            font-family: inherit;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1];
            backdrop-filter: blur(10px];
        }

        .form-input:focus {
            outline: none;
            border-color: #00ffff;
            box-shadow: 
                0 0 25px rgba(0, 255, 255, 0.4],
                inset 0 0 20px rgba(0, 255, 255, 0.1];
            background: rgba(255, 255, 255, 0.18];
            transform: scale(1.02];
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6];
            font-style: italic;
        }

        /* 挑战显示区域 */
        .challenge-display {
            background: rgba(255, 0, 255, 0.15];
            border: 2px solid rgba(255, 0, 255, 0.4];
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            font-family: 'JetBrains Mono', monospace;
            display: none;
            animation: challenge-appear 0.6s ease-out;
        }

        @keyframes challenge-appear {
            0% { 
                opacity: 0; 
                transform: translateY(-20px) scale(0.95];
            }
            100% { 
                opacity: 1; 
                transform: translateY(0) scale(1];
            }
        }

        .challenge-title {
            color: #ff00ff;
            font-size: 0.9rem;
            margin-bottom: 12px;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(255, 0, 255, 0.5];
        }

        .challenge-content {
            color: #ffffff;
            background: rgba(0, 0, 0, 0.4];
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #ff00ff;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* 按钮样式 */
        .btn {
            padding: 18px 35px;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1];
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2], transparent];
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00ffff, #ff00ff, #00ffff];
            background-size: 300% 300%;
            color: #000000;
            width: 100%;
            animation: btn-gradient 3s infinite;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02];
            box-shadow: 
                0 15px 35px rgba(0, 255, 255, 0.4],
                0 5px 15px rgba(255, 0, 255, 0.3];
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @keyframes btn-gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1];
            color: #00ffff;
            border: 2px solid #00ffff;
            margin-bottom: 20px;
            width: 100%;
        }

        .btn-secondary:hover {
            background: rgba(0, 255, 255, 0.15];
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.5];
            transform: translateY(-2px];
        }

        /* 消息提示 */
        .message {
            padding: 18px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            animation: message-slide 0.4s ease-out;
            backdrop-filter: blur(10px];
        }

        .message-error {
            background: rgba(255, 0, 0, 0.15];
            border: 2px solid rgba(255, 0, 0, 0.4];
            color: #ff6b6b;
        }

        @keyframes message-slide {
            0% { 
                opacity: 0; 
                transform: translateY(-15px];
            }
            100% { 
                opacity: 1; 
                transform: translateY(0];
            }
        }

        /* 加载状�?*/
        .loading {
            display: none;
            text-align: center;
            color: #00ffff;
            margin-bottom: 20px;
        }

        .loading-spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(0, 255, 255, 0.3];
            border-top: 3px solid #00ffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg]; }
            100% { transform: rotate(360deg]; }
        }

        /* 底部信息 */
        .security-info {
            text-align: center; 
            margin-top: 25px; 
            color: rgba(255,255,255,0.6]; 
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .security-info p {
            margin-bottom: 5px;
        }

        /* 响应式设�?*/
        @media (max-width: 768px) {
            .login-panel {
                width: 90%;
                padding: 30px 25px;
            }

            .hello-world-text {
                font-size: 2.8rem;
                bottom: 15%;
            }

            .holographic-ring {
                width: 250px;
                height: 250px;
                left: 5%;
            }

            .quantum-sphere {
                width: 180px;
                height: 180px;
                right: 8%;
            }

            .login-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .login-panel {
                width: 95%;
                padding: 25px 20px;
            }

            .hello-world-text {
                font-size: 2.2rem;
            }

            .form-input {
                padding: 15px 20px;
            }

            .btn {
                padding: 15px 25px;
            }
        }
    </style>
</head>
<body>
    <!-- 量子背景容器 -->
    <div class="quantum-background"></div>
    
    <!-- 粒子系统 -->
    <div class="particles-container" id="particlesContainer"></div>
    
    <!-- 代码�?-->
    <div class="code-rain" id="codeRain"></div>
    
    <!-- 全息环形 -->
    <div class="holographic-ring"></div>
    
    <!-- 量子球体 -->
    <div class="quantum-sphere"></div>
    
    <!-- Hello World 文字 -->
    <div class="hello-world-text">Hello, World!</div>

    <!-- 登录容器 -->
    <div class="login-container">
        <div class="login-panel">
            <div class="login-header">
                <h1 class="login-title">
                    <i class="fas fa-atom"></i> 
                    零信任量子门�?
                </h1>
                <p class="login-subtitle">Zero Trust Quantum Gateway · 基于零信任架�?/p>
            </div>

            <?php if (isset($error)): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user-astronaut"></i> 
                        量子身份标识
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input"
                        placeholder="输入您的量子身份ID (admin/alingai/root)"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- 动态挑战显示区�?-->
                <div id="challengeDisplay" class="challenge-display">
                    <div class="challenge-title">
                        <i class="fas fa-brain"></i> 
                        动态身份碎片验�?
                    </div>
                    <div id="challengeContent" class="challenge-content">
                        等待生成量子挑战...
                    </div>
                </div>

                <div class="form-group">
                    <label for="verifyCode" class="form-label">
                        <i class="fas fa-key"></i> 
                        量子验证�?
                    </label>
                    <input 
                        type="text" 
                        id="verifyCode" 
                        name="verify_code" 
                        class="form-input"
                        placeholder="请输入动态验证码"
                        required
                    >
                </div>

                <!-- 隐藏的设备指纹字�?-->
                <input type="hidden" id="deviceFingerprint" name="device_fingerprint">

                <div class="form-group">
                    <button type="button" id="generateChallenge" class="btn btn-secondary">
                        <i class="fas fa-rocket"></i>
                        生成量子挑战
                    </button>
                </div>

                <div class="loading" id="loading">
                    <div class="loading-spinner"></div>
                    正在验证量子身份...
                </div>

                <button type="submit" id="loginBtn" class="btn btn-primary">
                    <i class="fas fa-portal-enter"></i>
                    启动量子传�?
                </button>
            </form>

            <div class="security-info">
                <p><i class="fas fa-shield-alt"></i> 基于零信任架构的量子安全认证</p>
                <p>支持国产信创环境 · 符合等级保护要求</p>
                <p>AlingAI Pro 5.0 · Quantum Security Protocol</p>
            </div>
        </div>
    </div>

    <script>
        // 粒子系统初始�?
        function initParticles() {
            const container = document.getElementById('particlesContainer'];
            const particleCount = 80;

            function createParticle() {
                const particle = document.createElement('div'];
                particle.className = 'particle';
                
                // 随机大小和位�?
                const size = Math.random() * 4 + 1;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                
                // 随机动画延迟和持续时�?
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = (Math.random() * 6 + 6) + 's';
                
                container.appendChild(particle];
                
                // 粒子生命周期结束后移�?
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle];
                    }
                }, 12000];
            }

            // 初始创建粒子
            for (let i = 0; i < particleCount; i++) {
                setTimeout(createParticle, Math.random() * 2000];
            }

            // 持续创建新粒�?
            setInterval(createParticle, 150];
        }

        // 代码雨效�?
        function initCodeRain() {
            const container = document.getElementById('codeRain'];
            const codeSnippets = [
                'function validateZeroTrust(()) {',
                'const quantum = new QuantumState(];',
                'if (device.isSecure()) {',
                'return crypto.encrypt(data];',
                'class SecurityProtocol {',
                'async checkCompliance() {',
                'const fingerprint = await getHash(];',
                'quantum.entangle(user, device];',
                'if (trust.level < THRESHOLD) {',
                'throw new SecurityException(];'
            ];

            function createCodeColumn() {
                const column = document.createElement('div'];
                column.className = 'code-column';
                
                // 随机选择代码片段
                let content = '';
                for (let i = 0; i < 15; i++) {
                    content += codeSnippets[Math.floor(Math.random() * codeSnippets.length)] + '\n';
                }
                column.textContent = content;
                
                // 随机位置和动�?
                column.style.left = Math.random() * 100 + '%';
                column.style.animationDelay = Math.random() * 3 + 's';
                column.style.animationDuration = (Math.random() * 4 + 8) + 's';
                
                container.appendChild(column];
                
                // 动画结束后移�?
                setTimeout(() => {
                    if (column.parentNode) {
                        column.parentNode.removeChild(column];
                    }
                }, 12000];
            }

            // 持续创建代码�?
            setInterval(createCodeColumn, 1500];
        }

        // 设备指纹生成
        function generateDeviceFingerprint() {
            const canvas = document.createElement('canvas'];
            const ctx = canvas.getContext('2d'];
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillText('Quantum fingerprint', 2, 2];
            
            const fingerprint = canvas.toDataURL() + 
                navigator.userAgent + 
                navigator.language + 
                screen.width + 'x' + screen.height + 
                new Date().getHours(];
                
            // 生成MD5风格的哈希（简化版�?
            let hash = 0;
            for (let i = 0; i < fingerprint.length; i++) {
                const char = fingerprint.charCodeAt(i];
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            return Math.abs(hash).toString(16).padStart(8, '0'];
        }

        // DOM 加载完成后初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 启动视觉效果
            initParticles(];
            initCodeRain(];
            
            // 设置设备指纹
            document.getElementById('deviceFingerprint').value = generateDeviceFingerprint(];

            // 获取DOM元素
            const usernameInput = document.getElementById('username'];
            const generateBtn = document.getElementById('generateChallenge'];
            const challengeDisplay = document.getElementById('challengeDisplay'];
            const challengeContent = document.getElementById('challengeContent'];
            const verifyCodeInput = document.getElementById('verifyCode'];
            const loginBtn = document.getElementById('loginBtn'];
            const loginForm = document.getElementById('loginForm'];
            const loading = document.getElementById('loading'];

            // 生成挑战按钮事件
            generateBtn.addEventListener('click', function() {
                const username = usernameInput.value.trim(];
                if (!username) {
                    alert('请先输入量子身份标识'];
                    usernameInput.focus(];
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<div class="loading-spinner"></div> 生成量子挑战�?..';

                // 发送AJAX请求获取挑战
                fetch(`?action=get_challenge&username=${encodeURIComponent(username)}`)
                    .then(response => response.json())
                    .then(data => {
                        challengeContent.innerHTML = `
                            <strong>挑战时间:</strong> ${new Date().toLocaleTimeString()}<br>
                            <strong>量子验证�?</strong> <span style="color: #00ffff; font-weight: bold;">${data.challenge}</span><br>
                            <strong>提示:</strong> ${data.hint}<br>
                            <em style="color: #ff00ff;">请在验证码输入框中输入上述量子验证码</em>
                        `;
                        
                        challengeDisplay.style.display = 'block';
                        verifyCodeInput.focus(];
                        
                        // 更新设备指纹
                        document.getElementById('deviceFingerprint').value = data.deviceFingerprint;
                        
                        generateBtn.innerHTML = '<i class="fas fa-check"></i> 量子挑战已生�?;
                        generateBtn.style.background = 'rgba(0, 255, 0, 0.2)';
                        generateBtn.style.borderColor = '#00ff00';
                        generateBtn.style.color = '#00ff00';
                    })
                    .catch(error => {
                        console.error('生成挑战失败:', error];
                        generateBtn.disabled = false;
                        generateBtn.innerHTML = '<i class="fas fa-rocket"></i> 生成量子挑战';
                        alert('生成量子挑战失败，请重试'];
                    }];
            }];

            // 表单提交处理
            loginForm.addEventListener('submit', function(e) {
                const username = usernameInput.value.trim(];
                const verifyCode = verifyCodeInput.value.trim(];
                
                if (!username || !verifyCode) {
                    e.preventDefault(];
                    alert('请完成所有字段的填写'];
                    return;
                }

                loading.style.display = 'block';
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<div class="loading-spinner"></div> 量子传送中...';
                
                // 表单会自动提交，这里只是显示加载状�?
            }];

            // 快捷键支�?
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    loginForm.submit(];
                }
                if (e.key === 'F5') {
                    e.preventDefault(];
                    location.reload(];
                }
            }];

            // 输入验证
            verifyCodeInput.addEventListener('input', function() {
                const value = this.value.trim(];
                if (value.length === 6) {
                    loginBtn.disabled = false;
                    loginBtn.style.opacity = '1';
                } else {
                    loginBtn.disabled = true;
                    loginBtn.style.opacity = '0.6';
                }
            }];

            // 初始状�?
            loginBtn.disabled = true;
            loginBtn.style.opacity = '0.6';

            console.log('🚀 AlingAI Pro 5.0 零信任量子登录系统已启动'];
            console.log('�?支持的身份标�? admin, alingai, root'];
            console.log('🔮 量子验证基于动态时间戳算法'];
        }];

        // 页面可见性变化时暂停/恢复动画（性能优化�?
        document.addEventListener('visibilitychange', function() {
            const isHidden = document.hidden;
            const particles = document.querySelectorAll('.particle, .code-column'];
            particles.forEach(element => {
                element.style.animationPlayState = isHidden ? 'paused' : 'running';
            }];
        }];
    </script>
</body>
</html>

