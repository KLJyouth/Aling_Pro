<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">备份管理</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">首页</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/security">安全管理</a></li>
                <li class="breadcrumb-item active">备份管理</li>
            </ol>
            
            <!-- 备份操作 -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-database mr-1"></i>
                            创建备份
                        </div>
                        <div class="card-body">
                            <form id="backupForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="backupType">备份类型</label>
                                            <select class="form-control" id="backupType" name="type">
                                                <option value="full">完整备份</option>
                                                <option value="database">数据库备�?/option>
                                                <option value="files">文件备份</option>
                                                <option value="config">配置备份</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="backupDescription">备份描述</label>
                                            <input type="text" class="form-control" id="backupDescription" name="description" placeholder="请输入备份描�?>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" id="createBackupBtn">
                                    <i class="fas fa-save mr-1"></i> 创建备份
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 备份列表 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    备份列表
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="backupsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>类型</th>
                                    <th>大小</th>
                                    <th>创建时间</th>
                                    <th>状�?/th>
                                    <th>描述</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?= $backup['id'] ?></td>
                                    <td><?= $backup['name'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($backup['type']) {
                                            case 'full':
                                                echo '<span class="badge badge-primary">完整备份</span>';
                                                break;
                                            case 'database':
                                                echo '<span class="badge badge-info">数据库备�?/span>';
                                                break;
                                            case 'files':
                                                echo '<span class="badge badge-success">文件备份</span>';
                                                break;
                                            case 'config':
                                                echo '<span class="badge badge-warning">配置备份</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $backup['size'] ?></td>
                                    <td><?= $backup['date'] ?></td>
                                    <td>
                                        <?php 
                                        switch ($backup['status']) {
                                            case 'completed':
                                                echo '<span class="badge badge-success">完成</span>';
                                                break;
                                            case 'in_progress':
                                                echo '<span class="badge badge-warning">进行�?/span>';
                                                break;
                                            case 'failed':
                                                echo '<span class="badge badge-danger">失败</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $backup['description'] ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-success restore-backup" data-id="<?= $backup['id'] ?>">
                                                <i class="fas fa-undo-alt"></i> 恢复
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info">
                                                <i class="fas fa-download"></i> 下载
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- 备份配置 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog mr-1"></i>
                    备份配置
                </div>
                <div class="card-body">
                    <form id="backupConfigForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="backupPath">备份路径</label>
                                    <input type="text" class="form-control" id="backupPath" name="backup_path" value="<?= $backupConfig['backup_path'] ?>">
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="autoBackup" name="auto_backup" <?= $backupConfig['auto_backup'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="autoBackup">启用自动备份</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="backupFrequency">备份频率</label>
                                    <select class="form-control" id="backupFrequency" name="backup_frequency">
                                        <option value="hourly" <?= $backupConfig['backup_frequency'] == 'hourly' ? 'selected' : '' ?>>每小�?/option>
                                        <option value="daily" <?= $backupConfig['backup_frequency'] == 'daily' ? 'selected' : '' ?>>每天</option>
                                        <option value="weekly" <?= $backupConfig['backup_frequency'] == 'weekly' ? 'selected' : '' ?>>每周</option>
                                        <option value="monthly" <?= $backupConfig['backup_frequency'] == 'monthly' ? 'selected' : '' ?>>每月</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="backupTime">备份时间</label>
                                    <input type="time" class="form-control" id="backupTime" name="backup_time" value="<?= $backupConfig['backup_time'] ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="keepBackups">保留备份数量</label>
                                    <input type="number" class="form-control" id="keepBackups" name="keep_backups" value="<?= $backupConfig['keep_backups'] ?>">
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="compressBackups" name="compress_backups" <?= $backupConfig['compress_backups'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="compressBackups">压缩备份文件</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>备份内容</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="backupDatabase" name="backup_types[database]" <?= $backupConfig['backup_types']['database'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="backupDatabase">数据�?/label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="backupFiles" name="backup_types[files]" <?= $backupConfig['backup_types']['files'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="backupFiles">文件</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="backupConfig" name="backup_types[config]" <?= $backupConfig['backup_types']['config'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="backupConfig">配置</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> 保存配置
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 恢复备份确认模态框 -->
<div class="modal fade" id="restoreBackupModal" tabindex="-1" role="dialog" aria-labelledby="restoreBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreBackupModalLabel">恢复备份确认</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要恢复此备份吗？这将覆盖当前的数据�?/p>
                <p class="text-danger">警告：此操作无法撤销�?/p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmRestoreBtn">确认恢复</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 初始化数据表�?
        $('#backupsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Chinese.json"
            }
        }];
        
        // 创建备份
        $('#backupForm').on('submit', function(e) {
            e.preventDefault(];
            
            var formData = {
                type: $('#backupType').val(),
                description: $('#backupDescription').val()
            };
            
            $('#createBackupBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> 创建�?..').attr('disabled', true];
            
            // 发送AJAX请求
            $.ajax({
                url: '<?= BASE_URL ?>/security/create-backup',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // 显示成功消息
                        alert('备份创建成功'];
                        // 刷新页面
                        window.location.reload(];
                    } else {
                        alert('备份创建失败: ' + response.message];
                        $('#createBackupBtn').html('<i class="fas fa-save mr-1"></i> 创建备份').attr('disabled', false];
                    }
                },
                error: function() {
                    alert('发生错误，请稍后重试'];
                    $('#createBackupBtn').html('<i class="fas fa-save mr-1"></i> 创建备份').attr('disabled', false];
                }
            }];
        }];
        
        // 恢复备份
        var backupIdToRestore;
        
        $('.restore-backup').on('click', function() {
            backupIdToRestore = $(this).data('id'];
            $('#restoreBackupModal').modal('show'];
        }];
        
        $('#confirmRestoreBtn').on('click', function() {
            // 发送AJAX请求
            $.ajax({
                url: '<?= BASE_URL ?>/security/restore-backup',
                type: 'POST',
                data: {
                    backup_id: backupIdToRestore
                },
                dataType: 'json',
                success: function(response) {
                    $('#restoreBackupModal').modal('hide'];
                    
                    if (response.success) {
                        // 显示成功消息
                        alert('备份恢复成功'];
                        // 刷新页面
                        window.location.reload(];
                    } else {
                        alert('备份恢复失败: ' + response.message];
                    }
                },
                error: function() {
                    $('#restoreBackupModal').modal('hide'];
                    alert('发生错误，请稍后重试'];
                }
            }];
        }];
        
        // 保存备份配置
        $('#backupConfigForm').on('submit', function(e) {
            e.preventDefault(];
            
            // 在实际应用中，这里应该发送AJAX请求保存配置
            alert('配置保存成功'];
        }];
    }];
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 

