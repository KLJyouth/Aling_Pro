<!-- 用户编辑表单 -->
<div class="card">
    <div class="card-body">
        <form action="/admin/users/update/<?= $user['id'] ?>" method="post">
            <!-- CSRF令牌 -->
            <?= \App\Core\Security::csrfField() ?>
            
            <!-- 用户ID -->
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            
            <!-- 用户名 -->
            <div class="mb-3">
                <label for="username" class="form-label">用户名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_SESSION['form_data']['username'] ?? $user['username']) ?>" required>
                <div class="form-text">用户登录名，仅支持字母、数字和下划线，必须唯一</div>
            </div>
            
            <!-- 密码 -->
            <div class="mb-3">
                <label for="password" class="form-label">密码</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text">留空表示不修改密码，至少6个字符</div>
            </div>
            
            <!-- 姓名 -->
            <div class="mb-3">
                <label for="name" class="form-label">姓名</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_SESSION['form_data']['name'] ?? $user['name']) ?>">
                <div class="form-text">用户的真实姓名或昵称</div>
            </div>
            
            <!-- 邮箱 -->
            <div class="mb-3">
                <label for="email" class="form-label">邮箱 <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? $user['email']) ?>" required>
                <div class="form-text">用户联系邮箱，必须唯一</div>
            </div>
            
            <!-- 角色 -->
            <div class="mb-3">
                <label for="role" class="form-label">角色 <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role">
                    <?php foreach ($roles as $key => $value): ?>
                        <option value="<?= $key ?>" <?= ($_SESSION['form_data']['role'] ?? $user['role']) === $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">用户权限角色</div>
            </div>
            
            <!-- 状态 -->
            <div class="mb-3">
                <label for="status" class="form-label">状态</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?= ($_SESSION['form_data']['status'] ?? $user['status']) === 'active' ? 'selected' : '' ?>>活跃</option>
                    <option value="inactive" <?= ($_SESSION['form_data']['status'] ?? $user['status']) === 'inactive' ? 'selected' : '' ?>>停用</option>
                </select>
                <div class="form-text">停用的用户无法登录系统</div>
            </div>
            
            <!-- 注册时间 -->
            <div class="mb-3">
                <label class="form-label">注册时间</label>
                <input type="text" class="form-control" value="<?= $user['created_at'] ?>" readonly>
            </div>
            
            <!-- 最后登录 -->
            <div class="mb-3">
                <label class="form-label">最后登录</label>
                <input type="text" class="form-control" value="<?= $user['last_login'] ?? '从未登录' ?>" readonly>
            </div>
            
            <!-- 按钮 -->
            <div class="d-flex justify-content-between">
                <a href="/admin/users" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回用户列表
                </a>
                <div>
                    <a href="/admin/users/show/<?= $user['id'] ?>" class="btn btn-info me-2">
                        <i class="bi bi-eye"></i> 查看详情
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> 保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 额外的JS -->
<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 密码显示/隐藏切换
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
        
        // 表单验证
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const email = document.getElementById('email').value.trim();
            
            // 用户名验证
            if (!username) {
                isValid = false;
                showError('username', '用户名不能为空');
            } else if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
                isValid = false;
                showError('username', '用户名只能包含字母、数字和下划线，长度3-20个字符');
            } else {
                clearError('username');
            }
            
            // 密码验证（如果有输入）
            if (password && password.length < 6) {
                isValid = false;
                showError('password', '密码长度不能少于6个字符');
            } else {
                clearError('password');
            }
            
            // 邮箱验证
            if (!email) {
                isValid = false;
                showError('email', '邮箱不能为空');
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                isValid = false;
                showError('email', '邮箱格式不正确');
            } else {
                clearError('email');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            let errorDiv = field.parentNode.querySelector('.invalid-feedback');
            
            field.classList.add('is-invalid');
            
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                field.parentNode.appendChild(errorDiv);
            }
            
            errorDiv.textContent = message;
        }
        
        function clearError(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.remove('is-invalid');
            
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    });
</script>
<?php $extraScripts = ob_get_clean(); ?>

<?php
// 清除表单数据
unset($_SESSION['form_data']);
?> 