<!-- 搜索和过滤 -->
<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/users" method="get" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="搜索用户..." value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">所有角色</option>
                    <?php foreach ($roles as $key => $value): ?>
                        <option value="<?= $key ?>" <?= ($roleFilter == $key) ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">所有状态</option>
                    <option value="active" <?= ($statusFilter == 'active') ? 'selected' : '' ?>>活跃</option>
                    <option value="inactive" <?= ($statusFilter == 'inactive') ? 'selected' : '' ?>>停用</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">筛选</button>
            </div>
        </form>
    </div>
</div>

<!-- 用户列表 -->
<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>角色</th>
                        <th>状态</th>
                        <th>注册时间</th>
                        <th>最后登录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">未找到用户记录</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? $user['username']) ?>&background=random" alt="用户头像" width="36" height="36" class="rounded-circle me-2">
                                        <div>
                                            <div><?= htmlspecialchars($user['name'] ?? $user['username']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($user['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">管理员</span>
                                    <?php elseif ($user['role'] === 'operator'): ?>
                                        <span class="badge bg-primary">运维</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">用户</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['status'] === 'active'): ?>
                                        <span class="badge bg-success">活跃</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">停用</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $user['created_at'] ?></td>
                                <td><?= $user['last_login'] ?? '从未登录' ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/users/show/<?= $user['id'] ?>" class="btn btn-outline-info" title="查看详情">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-outline-primary" title="编辑用户">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="删除用户" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?= $user['id'] ?>" data-user-name="<?= htmlspecialchars($user['name'] ?? $user['username']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=1&search=<?= urlencode($searchTerm) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" aria-label="首页">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $currentPage - 1 ?>&search=<?= urlencode($searchTerm) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" aria-label="上一页">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">&laquo;&laquo;</span>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">&laquo;</span>
                </li>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if ($startPage > 1) {
                echo '<li class="page-item"><a class="page-link" href="/admin/users?page=1&search=' . urlencode($searchTerm) . '&role=' . urlencode($roleFilter) . '&status=' . urlencode($statusFilter) . '">1</a></li>';
                if ($startPage > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $currentPage) {
                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                } else {
                    echo '<li class="page-item"><a class="page-link" href="/admin/users?page=' . $i . '&search=' . urlencode($searchTerm) . '&role=' . urlencode($roleFilter) . '&status=' . urlencode($statusFilter) . '">' . $i . '</a></li>';
                }
            }
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="/admin/users?page=' . $totalPages . '&search=' . urlencode($searchTerm) . '&role=' . urlencode($roleFilter) . '&status=' . urlencode($statusFilter) . '">' . $totalPages . '</a></li>';
            }
            ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $currentPage + 1 ?>&search=<?= urlencode($searchTerm) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" aria-label="下一页">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $totalPages ?>&search=<?= urlencode($searchTerm) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" aria-label="末页">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">&raquo;</span>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">&raquo;&raquo;</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- 统计信息 -->
<div class="text-center text-muted mt-3">
    共 <?= $totalUsers ?> 个用户，当前显示 <?= count($users) ?> 个
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
        
        // 自动提交表单当选择变化时
        document.querySelectorAll('select[name="role"], select[name="status"]').forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    });
</script>
<?php $extraScripts = ob_get_clean(); ?> 