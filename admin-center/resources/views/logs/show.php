<?php
/**
 * 系统日志详情页面
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">日志详情 #<?= $log['id'] ?></h5>
                    <a href="/admin/logs" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 返回列表
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">日志级别</label>
                            <?php
                            $levelBadge = [
                                'critical' => 'danger',
                                'error' => 'warning',
                                'warning' => 'info',
                                'info' => 'primary',
                                'debug' => 'secondary'
                            ];
                            $badgeClass = $levelBadge[$log['level']] ?? 'secondary';
                            ?>
                            <div>
                                <span class="badge bg-<?= $badgeClass ?> fs-6"><?= ucfirst($log['level']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">记录时间</label>
                            <div>
                                <i class="bi bi-clock"></i> <?= $log['created_at'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted">日志消息</label>
                    <div class="p-3 bg-light rounded">
                        <?= htmlspecialchars($log['message']) ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted">上下文数据</label>
                    <?php if (empty($context)): ?>
                        <div class="p-3 bg-light rounded text-muted">
                            <i class="bi bi-info-circle"></i> 无上下文数据
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded">
                            <pre class="mb-0"><code><?= htmlspecialchars(json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($log['trace'])): ?>
                    <div class="mb-4">
                        <label class="form-label text-muted">堆栈跟踪</label>
                        <div class="p-3 bg-light rounded overflow-auto" style="max-height: 400px;">
                            <pre class="mb-0"><code><?= htmlspecialchars($log['trace']) ?></code></pre>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($log['user_id'])): ?>
                    <div class="mb-4">
                        <label class="form-label text-muted">相关用户</label>
                        <div>
                            <a href="/admin/users/<?= $log['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-person"></i> 查看用户 #<?= $log['user_id'] ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="/admin/logs/<?= $log['id'] - 1 ?>" class="btn btn-sm btn-outline-secondary <?= $log['id'] <= 1 ? 'disabled' : '' ?>">
                            <i class="bi bi-chevron-left"></i> 上一条
                        </a>
                        <a href="/admin/logs/<?= $log['id'] + 1 ?>" class="btn btn-sm btn-outline-secondary">
                            下一条 <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteLogModal">
                            <i class="bi bi-trash"></i> 删除此日志
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($relatedLogs)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">相关日志</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%">级别</th>
                                    <th width="55%">消息</th>
                                    <th width="15%">时间</th>
                                    <th width="15%">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relatedLogs as $relatedLog): ?>
                                    <tr>
                                        <td><?= $relatedLog['id'] ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = $levelBadge[$relatedLog['level']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($relatedLog['level']) ?></span>
                                        </td>
                                        <td>
                                            <div class="log-message text-truncate" style="max-width: 500px;">
                                                <?= htmlspecialchars($relatedLog['message']) ?>
                                            </div>
                                        </td>
                                        <td><?= $relatedLog['created_at'] ?></td>
                                        <td>
                                            <a href="/admin/logs/<?= $relatedLog['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> 查看
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 删除日志确认模态框 -->
<div class="modal fade" id="deleteLogModal" tabindex="-1" aria-labelledby="deleteLogModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLogModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <p>您确定要删除此日志记录吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <form action="/admin/logs/<?= $log['id'] ?>/delete" method="post" class="d-inline">
                    <?= \App\Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div> 