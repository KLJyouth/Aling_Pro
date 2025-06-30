<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'IT运维中心 - 安全登录' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 1rem;
        }
        .card {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            font-weight: 600;
            background-color: rgba(33, 37, 41, 0.8);
            color: #fff;
            text-align: center;
            border-bottom: none;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%);
            transform: rotate(45deg);
            z-index: -1;
        }
        .card-body {
            padding: 2rem;
            color: #f8f9fa;
            background-color: rgba(33, 37, 41, 0.9);
        }
        .form-control {
            border-radius: 0.25rem;
            padding: 0.75rem 1rem;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(13, 110, 253, 0.5);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .input-group-text {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0953cf 0%, #084298 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }
        .login-footer {
            margin-top: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }
        .logo {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
        }
        .form-label {
            color: rgba(255, 255, 255, 0.8);
        }
        .alert {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .logo-animate {
            animation: pulse 2s infinite;
        }
        .security-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: 0 0.25rem;
            font-size: 0.75rem;
            background-color: rgba(13, 110, 253, 0.2);
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(13, 110, 253, 0.3);
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="logo logo-animate">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2 class="mb-1">IT运维中心</h2>
                <p class="mb-0 text-white-50">安全管理系统</p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors) && count($errors) > 0): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $fieldErrors): ?>
                                <?php foreach ($fieldErrors as $errorMsg): ?>
                                    <li><?= $errorMsg ?></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="/admin/login">
                    <!-- CSRF令牌 -->
                    <?= \App\Core\Security::csrfField() ?>

                    <div class="mb-4">
                        <label for="username" class="form-label">用户名</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                value="<?= $username ?? '' ?>" placeholder="请输入管理员用户名" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">密码</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-key-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                placeholder="请输入密码" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-white-50" for="remember">记住我</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>安全登录
                    </button>
                </form>
            </div>
        </div>
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> AlingAi Pro IT运维中心</p>
            <div class="security-features mt-2">
                <span class="security-badge"><i class="bi bi-shield-check me-1"></i>零信任架构</span>
                <span class="security-badge"><i class="bi bi-fingerprint me-1"></i>多重认证</span>
                <span class="security-badge"><i class="bi bi-lock me-1"></i>端到端加密</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 切换密码显示/隐藏
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // 切换图标
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html> 