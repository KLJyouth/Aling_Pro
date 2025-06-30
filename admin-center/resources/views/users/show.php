<!-- 用户基本信息 -->
<div class="row mb-4">
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card h-100">
            <div class="card-body text-center">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? $user['username']) ?>&background=random&size=180" alt="用户头像" class="rounded-circle mb-3 img-thumbnail shadow" style="width: 180px; height: 180px;">
                
                <h4><?= htmlspecialchars($user['name'] ?? $user['username']) ?></h4>
                <p class="text-muted"><?= $user['role'] === 'admin' ? '管理员' : ($user['role'] === 'operator' ? '运维人员' : '普通用户') ?></p>
                
                <div class="mt-4 d-flex justify-content-center">
                    <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> 编辑
                    </a>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?= $user['id'] ?>" data-user-name="<?= htmlspecialchars($user['name'] ?? $user['username']) ?>">
                        <i class="bi bi-trash"></i> 删除
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">用户详细信息</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">用户ID</div>
                    <div class="col-sm-8"><?= $user['id'] ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">用户名</div>
                    <div class="col-sm-8"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">姓名</div>
                    <div class="col-sm-8"><?= htmlspecialchars($user['name'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">邮箱</div>
                    <div class="col-sm-8">
                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">角色</div>
                    <div class="col-sm-8">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-danger">管理员</span>
                        <?php elseif ($user['role'] === 'operator'): ?>
                            <span class="badge bg-primary">运维人员</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">普通用户</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">状态</div>
                    <div class="col-sm-8">
                        <?php if ($user['status'] === 'active'): ?>
                            <span class="badge bg-success">活跃</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">停用</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">注册时间</div>
                    <div class="col-sm-8"><?= $user['created_at'] ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">最后登录</div>
                    <div class="col-sm-8"><?= $user['last_login'] ?? '从未登录' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 登录历史 -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">登录历史记录</h5>
        <a href="#" class="btn btn-sm btn-outline-primary">查看全部</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>时间</th>
                        <th>IP地址</th>
                        <th>操作</th>
                        <th>用户代理</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loginHistory)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">暂无登录记录</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loginHistory as $index => $record): ?>
                            <tr>
                                <td><?= $record['id'] ?></td>
                                <td><?= $record['created_at'] ?></td>
                                <td><?= htmlspecialchars($record['ip_address']) ?></td>
                                <td>
                                    <?php if ($record['action'] === 'login'): ?>
                                        <span class="badge bg-success">登录</span>
                                    <?php elseif ($record['action'] === 'logout'): ?>
                                        <span class="badge bg-secondary">登出</span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($record['action']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($record['user_agent']) ?>"><?= htmlspecialchars($record['user_agent']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 删除用户确认模态框 -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <p>您确定要删除用户 <strong id="deleteUserName"></strong> 吗？此操作无法撤销。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <form id="deleteUserForm" action="/admin/users/delete/" method="post" style="display: inline;">
                    <?= \App\Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 额外的JS -->
<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 删除用户确认
        const deleteUserModal = document.getElementById('deleteUserModal');
        if (deleteUserModal) {
            deleteUserModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                
                document.getElementById('deleteUserName').textContent = userName;
                document.getElementById('deleteUserForm').action = '/admin/users/delete/' + userId;
            });
        }
    });
</script>
<?php $extraScripts = ob_get_clean(); ?> 