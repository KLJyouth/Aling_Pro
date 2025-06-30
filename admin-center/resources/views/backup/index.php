<?php
/**
 * 备份管理页面
 */
?>

<div class="row">
    <!-- 备份操作 -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">数据库备份</h5>
                        <p class="text-muted mb-0">管理系统数据库备份，确保数据安全</p>
                    </div>
                    <div>
                        <a href="/admin/backup/create" class="btn btn-primary">
                            <i class="bi bi-download"></i> 创建新备份
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 备份列表 -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">备份列表</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="40%">文件名</th>
                                <th width="15%">大小</th>
                                <th width="20%">创建时间</th>
                                <th width="25%">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($backups)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">暂无备份文件</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark-zip text-primary me-2"></i>
                                            <?= htmlspecialchars($backup['filename']) ?>
                                        </td>
                                        <td><?= $backup['size'] ?></td>
                                        <td><?= $backup['date'] ?></td>
                                        <td>
                                            <a href="/admin/backup/<?= urlencode($backup['filename']) ?>/download" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> 下载
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#restoreModal<?= md5($backup['filename']) ?>">
                                                <i class="bi bi-arrow-counterclockwise"></i> 恢复
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= md5($backup['filename']) ?>">
                                                <i class="bi bi-trash"></i> 删除
                                            </button>
                                            
                                            <!-- 恢复确认模态框 -->
                                            <div class="modal fade" id="restoreModal<?= md5($backup['filename']) ?>" tabindex="-1" aria-labelledby="restoreModalLabel<?= md5($backup['filename']) ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="restoreModalLabel<?= md5($backup['filename']) ?>">确认恢复备份</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning">
                                                                <i class="bi bi-exclamation-triangle"></i> 警告：恢复备份将覆盖当前数据库中的所有数据！
                                                            </div>
                                                            <p>您确定要恢复此备份文件吗？</p>
                                                            <p><strong>文件名：</strong> <?= htmlspecialchars($backup['filename']) ?></p>
                                                            <p><strong>创建时间：</strong> <?= $backup['date'] ?></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                                            <form action="/admin/backup/<?= urlencode($backup['filename']) ?>/restore" method="post">
                                                                <?= \App\Core\Security::csrfField() ?>
                                                                <button type="submit" class="btn btn-success">确认恢复</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- 删除确认模态框 -->
                                            <div class="modal fade" id="deleteModal<?= md5($backup['filename']) ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= md5($backup['filename']) ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?= md5($backup['filename']) ?>">确认删除备份</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>您确定要删除此备份文件吗？此操作不可恢复。</p>
                                                            <p><strong>文件名：</strong> <?= htmlspecialchars($backup['filename']) ?></p>
                                                            <p><strong>创建时间：</strong> <?= $backup['date'] ?></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                                            <form action="/admin/backup/<?= urlencode($backup['filename']) ?>/delete" method="post">
                                                                <?= \App\Core\Security::csrfField() ?>
                                                                <button type="submit" class="btn btn-danger">确认删除</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
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
    </div>
    
    <!-- 备份说明 -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">备份说明</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> 关于数据库备份</h6>
                    <ul class="mb-0">
                        <li>备份文件包含数据库中的所有表结构和数据</li>
                        <li>建议定期创建备份，以防数据丢失</li>
                        <li>备份文件以 SQL 格式保存，并使用 gzip 压缩</li>
                        <li>恢复备份将覆盖当前数据库中的所有数据，请谨慎操作</li>
                        <li>可以在系统设置中配置自动备份功能</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> 注意事项</h6>
                    <ul class="mb-0">
                        <li>恢复备份前，建议先创建当前数据库的备份</li>
                        <li>恢复过程中请勿关闭浏览器或刷新页面</li>
                        <li>大型数据库的备份和恢复可能需要较长时间</li>
                        <li>如果备份文件较大，建议使用下载功能将备份保存到本地</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div> 