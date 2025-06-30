<?php
/**
 * 系统设置页面
 */
?>

<div class="row">
    <!-- 设置表单 -->
    <div class="col-lg-8 order-lg-1 order-2">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">系统设置</h5>
            </div>
            <div class="card-body">
                <form action="/admin/settings/save" method="post">
                    <!-- CSRF令牌 -->
                    <?= \App\Core\Security::csrfField() ?>
                    
                    <!-- 基本设置 -->
                    <h6 class="border-bottom pb-2 mb-3">基本设置</h6>
                    
                    <div class="mb-3">
                        <label for="site_name" class="form-label">网站名称 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="site_name" name="settings[site_name]" value="<?= htmlspecialchars($_SESSION['form_data']['site_name'] ?? $settings['site_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">网站描述</label>
                        <textarea class="form-control" id="site_description" name="settings[site_description]" rows="2"><?= htmlspecialchars($_SESSION['form_data']['site_description'] ?? $settings['site_description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">管理员邮箱</label>
                        <input type="email" class="form-control" id="admin_email" name="settings[admin_email]" value="<?= htmlspecialchars($_SESSION['form_data']['admin_email'] ?? $settings['admin_email']) ?>">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="timezone" class="form-label">时区</label>
                            <select class="form-select" id="timezone" name="settings[timezone]">
                                <?php
                                $timezones = [
                                    'Asia/Shanghai' => '(GMT+8:00) 北京、上海、香港',
                                    'Asia/Tokyo' => '(GMT+9:00) 东京',
                                    'Europe/London' => '(GMT+0:00) 伦敦',
                                    'America/New_York' => '(GMT-5:00) 纽约',
                                    'America/Los_Angeles' => '(GMT-8:00) 洛杉矶'
                                ];
                                $selectedTimezone = $_SESSION['form_data']['timezone'] ?? $settings['timezone'];
                                foreach ($timezones as $value => $label):
                                ?>
                                    <option value="<?= $value ?>" <?= $selectedTimezone === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_format" class="form-label">日期格式</label>
                            <select class="form-select" id="date_format" name="settings[date_format]">
                                <?php
                                $formats = [
                                    'Y-m-d H:i:s' => date('Y-m-d H:i:s') . ' (Y-m-d H:i:s)',
                                    'Y年m月d日 H:i:s' => date('Y年m月d日 H:i:s') . ' (Y年m月d日 H:i:s)',
                                    'd/m/Y H:i:s' => date('d/m/Y H:i:s') . ' (d/m/Y H:i:s)',
                                    'm/d/Y H:i:s' => date('m/d/Y H:i:s') . ' (m/d/Y H:i:s)'
                                ];
                                $selectedFormat = $_SESSION['form_data']['date_format'] ?? $settings['date_format'];
                                foreach ($formats as $value => $label):
                                ?>
                                    <option value="<?= $value ?>" <?= $selectedFormat === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- 显示设置 -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">显示设置</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="items_per_page" class="form-label">每页显示条目数</label>
                            <select class="form-select" id="items_per_page" name="settings[items_per_page]">
                                <?php
                                $options = [10, 20, 30, 50, 100];
                                $selectedOption = (int)($_SESSION['form_data']['items_per_page'] ?? $settings['items_per_page']);
                                foreach ($options as $option):
                                ?>
                                    <option value="<?= $option ?>" <?= $selectedOption === $option ? 'selected' : '' ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="theme" class="form-label">主题</label>
                            <select class="form-select" id="theme" name="settings[theme]">
                                <?php
                                $themes = [
                                    'default' => '默认主题',
                                    'dark' => '深色主题',
                                    'light' => '浅色主题'
                                ];
                                $selectedTheme = $_SESSION['form_data']['theme'] ?? $settings['theme'];
                                foreach ($themes as $value => $label):
                                ?>
                                    <option value="<?= $value ?>" <?= $selectedTheme === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- 系统设置 -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">系统设置</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="settings[maintenance_mode]" value="1" <?= ($_SESSION['form_data']['maintenance_mode'] ?? $settings['maintenance_mode']) == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenance_mode">维护模式</label>
                        </div>
                        <div class="form-text">开启后，除管理员外的用户将无法访问系统</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_registration" name="settings[enable_registration]" value="1" <?= ($_SESSION['form_data']['enable_registration'] ?? $settings['enable_registration']) == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_registration">允许注册</label>
                        </div>
                        <div class="form-text">开启后，新用户可以自行注册账号</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="log_level" class="form-label">日志级别</label>
                        <select class="form-select" id="log_level" name="settings[log_level]">
                            <?php
                            $logLevels = [
                                'debug' => '调试 (Debug)',
                                'info' => '信息 (Info)',
                                'warning' => '警告 (Warning)',
                                'error' => '错误 (Error)',
                                'critical' => '严重 (Critical)'
                            ];
                            $selectedLogLevel = $_SESSION['form_data']['log_level'] ?? $settings['log_level'];
                            foreach ($logLevels as $value => $label):
                            ?>
                                <option value="<?= $value ?>" <?= $selectedLogLevel === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">设置系统记录的最低日志级别</div>
                    </div>
                    
                    <!-- 备份设置 -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">备份设置</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="backup_auto" name="settings[backup_auto]" value="1" <?= ($_SESSION['form_data']['backup_auto'] ?? $settings['backup_auto']) == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="backup_auto">自动备份</label>
                        </div>
                        <div class="form-text">开启后，系统将按设定的时间间隔自动备份数据</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="backup_interval" class="form-label">备份间隔</label>
                        <select class="form-select" id="backup_interval" name="settings[backup_interval]" <?= ($_SESSION['form_data']['backup_auto'] ?? $settings['backup_auto']) != '1' ? 'disabled' : '' ?>>
                            <?php
                            $intervals = [
                                'hourly' => '每小时',
                                'daily' => '每天',
                                'weekly' => '每周',
                                'monthly' => '每月'
                            ];
                            $selectedInterval = $_SESSION['form_data']['backup_interval'] ?? $settings['backup_interval'];
                            foreach ($intervals as $value => $label):
                            ?>
                                <option value="<?= $value ?>" <?= $selectedInterval === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> 保存设置
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 系统信息 -->
    <div class="col-lg-4 order-lg-2 order-1">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">系统信息</h5>
            </div>
            <div class="card-body">
                <div class="system-info-item">
                    <strong>PHP版本：</strong> <?= $systemInfo['php_version'] ?>
                </div>
                <div class="system-info-item">
                    <strong>MySQL版本：</strong> <?= $systemInfo['database_version'] ?>
                </div>
                <div class="system-info-item">
                    <strong>服务器软件：</strong> <?= $systemInfo['server_software'] ?>
                </div>
                <div class="system-info-item">
                    <strong>操作系统：</strong> <?= $systemInfo['operating_system'] ?>
                </div>
                <div class="system-info-item">
                    <strong>内存限制：</strong> <?= $systemInfo['memory_limit'] ?>
                </div>
                <div class="system-info-item">
                    <strong>最大执行时间：</strong> <?= $systemInfo['max_execution_time'] ?>
                </div>
                <div class="system-info-item">
                    <strong>最大上传文件：</strong> <?= $systemInfo['upload_max_filesize'] ?>
                </div>
                <div class="system-info-item">
                    <strong>POST最大大小：</strong> <?= $systemInfo['post_max_size'] ?>
                </div>
                <div class="system-info-item">
                    <strong>磁盘空间：</strong> 可用 <?= $systemInfo['disk_free_space'] ?> / 总计 <?= $systemInfo['disk_total_space'] ?>
                </div>
                <div class="system-info-item">
                    <strong>服务器时间：</strong> <?= $systemInfo['server_time'] ?>
                </div>
                <div class="system-info-item">
                    <strong>时区：</strong> <?= $systemInfo['timezone'] ?>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">快速操作</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#clearCacheModal">
                        <i class="bi bi-trash"></i> 清除缓存
                    </button>
                    <a href="/admin/backup/create" class="btn btn-outline-success">
                        <i class="bi bi-download"></i> 立即备份
                    </a>
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#optimizeDatabaseModal">
                        <i class="bi bi-speedometer2"></i> 优化数据库
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#maintenanceModeModal">
                        <i class="bi bi-tools"></i> <?= $settings['maintenance_mode'] == '1' ? '关闭维护模式' : '开启维护模式' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 清除缓存确认模态框 -->
<div class="modal fade" id="clearCacheModal" tabindex="-1" aria-labelledby="clearCacheModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearCacheModalLabel">确认清除缓存</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <p>您确定要清除所有系统缓存吗？这可能会暂时影响系统性能。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="/admin/cache/clear" class="btn btn-primary">确认清除</a>
            </div>
        </div>
    </div>
</div>

<!-- 优化数据库确认模态框 -->
<div class="modal fade" id="optimizeDatabaseModal" tabindex="-1" aria-labelledby="optimizeDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="optimizeDatabaseModalLabel">确认优化数据库</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <p>您确定要优化数据库吗？这将执行OPTIMIZE TABLE操作，可能需要一些时间完成。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="/admin/database/optimize" class="btn btn-primary">确认优化</a>
            </div>
        </div>
    </div>
</div>

<!-- 维护模式确认模态框 -->
<div class="modal fade" id="maintenanceModeModal" tabindex="-1" aria-labelledby="maintenanceModeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maintenanceModeModalLabel"><?= $settings['maintenance_mode'] == '1' ? '确认关闭维护模式' : '确认开启维护模式' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <?php if ($settings['maintenance_mode'] == '1'): ?>
                    <p>您确定要关闭维护模式吗？这将使系统对所有用户可访问。</p>
                <?php else: ?>
                    <p>您确定要开启维护模式吗？这将使系统除管理员外的用户无法访问。</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="/admin/maintenance/toggle" class="btn btn-primary"><?= $settings['maintenance_mode'] == '1' ? '确认关闭' : '确认开启' ?></a>
            </div>
        </div>
    </div>
</div>

<!-- 额外的JS -->
<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 自动备份开关联动
        document.getElementById('backup_auto').addEventListener('change', function() {
            document.getElementById('backup_interval').disabled = !this.checked;
        });
    });
</script>
<?php $extraScripts = ob_get_clean(); ?>

<?php
// 清除表单数据
unset($_SESSION['form_data']);
?> 