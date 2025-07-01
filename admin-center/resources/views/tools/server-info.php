<?php
/**
 * 服务器信息页面视图
 */
// 引入布局模板
include_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4"><?= $pageHeader ?? '服务器信息' ?></h2>
    
    <div class="row">
        <div class="col-md-6">
            <!-- 操作系统信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pc me-2"></i> 操作系统信息
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">操作系统:</th>
                                <td><?= htmlspecialchars($serverInfo['os']['name']) ?></td>
                            </tr>
                            <tr>
                                <th>版本:</th>
                                <td><?= htmlspecialchars($serverInfo['os']['version']) ?></td>
                            </tr>
                            <tr>
                                <th>架构:</th>
                                <td><?= htmlspecialchars($serverInfo['os']['architecture']) ?></td>
                            </tr>
                            <tr>
                                <th>主机名:</th>
                                <td><?= htmlspecialchars($serverInfo['os']['hostname']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- PHP信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-filetype-php me-2"></i> PHP 信息
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">PHP 版本:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['version']) ?></td>
                            </tr>
                            <tr>
                                <th>运行模式:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['sapi']) ?></td>
                            </tr>
                            <tr>
                                <th>内存限制:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['memory_limit']) ?></td>
                            </tr>
                            <tr>
                                <th>最大执行时间:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['max_execution_time']) ?></td>
                            </tr>
                            <tr>
                                <th>上传文件大小限制:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['upload_max_filesize']) ?></td>
                            </tr>
                            <tr>
                                <th>POST大小限制:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['post_max_size']) ?></td>
                            </tr>
                            <tr>
                                <th>错误显示:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['display_errors']) ?></td>
                            </tr>
                            <tr>
                                <th>默认字符集:</th>
                                <td><?= htmlspecialchars($serverInfo['php']['default_charset']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted">
                    <a href="/admin/tools/phpinfo" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-info-circle me-1"></i> 查看完整PHP信息
                    </a>
                </div>
            </div>
            
            <!-- 时间信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i> 时间信息
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">服务器时间:</th>
                                <td><?= htmlspecialchars($serverInfo['time']['server_time']) ?></td>
                            </tr>
                            <tr>
                                <th>时区:</th>
                                <td><?= htmlspecialchars($serverInfo['time']['timezone']) ?></td>
                            </tr>
                            <tr>
                                <th>运行时间:</th>
                                <td><?= htmlspecialchars($serverInfo['time']['uptime'] ?? '未知') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- 服务器信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-server me-2"></i> Web服务器信息
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">服务器软件:</th>
                                <td><?= htmlspecialchars($serverInfo['server']['software']) ?></td>
                            </tr>
                            <tr>
                                <th>协议:</th>
                                <td><?= htmlspecialchars($serverInfo['server']['protocol']) ?></td>
                            </tr>
                            <tr>
                                <th>服务器地址:</th>
                                <td><?= htmlspecialchars($serverInfo['server']['addr']) ?></td>
                            </tr>
                            <tr>
                                <th>服务器名:</th>
                                <td><?= htmlspecialchars($serverInfo['server']['name']) ?></td>
                            </tr>
                            <tr>
                                <th>端口:</th>
                                <td><?= htmlspecialchars($serverInfo['server']['port']) ?></td>
                            </tr>
                            <tr>
                                <th>文档根目录:</th>
                                <td><?= htmlspecialchars($serverInfo['server']['document_root']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 磁盘信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-hdd me-2"></i> 磁盘信息
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">总空间:</th>
                                <td><?= htmlspecialchars($serverInfo['disk']['total']) ?></td>
                            </tr>
                            <tr>
                                <th>已用空间:</th>
                                <td><?= htmlspecialchars($serverInfo['disk']['used']) ?></td>
                            </tr>
                            <tr>
                                <th>剩余空间:</th>
                                <td><?= htmlspecialchars($serverInfo['disk']['free']) ?></td>
                            </tr>
                            <tr>
                                <th>使用率:</th>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <?php
                                        $usagePercent = (float) $serverInfo['disk']['usage'];
                                        $progressClass = 'bg-success';
                                        if ($usagePercent > 70) $progressClass = 'bg-warning';
                                        if ($usagePercent > 90) $progressClass = 'bg-danger';
                                        ?>
                                        <div class="progress-bar <?= $progressClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $usagePercent ?>%"
                                             aria-valuenow="<?= $usagePercent ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= htmlspecialchars($serverInfo['disk']['usage']) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (isset($serverInfo['memory'])): ?>
            <!-- 内存信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-memory me-2"></i> 内存信息
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%">总内存:</th>
                                <td><?= htmlspecialchars($serverInfo['memory']['total']) ?></td>
                            </tr>
                            <tr>
                                <th>已用内存:</th>
                                <td><?= htmlspecialchars($serverInfo['memory']['used']) ?></td>
                            </tr>
                            <tr>
                                <th>剩余内存:</th>
                                <td><?= htmlspecialchars($serverInfo['memory']['free']) ?></td>
                            </tr>
                            <tr>
                                <th>使用率:</th>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <?php
                                        $usagePercent = (float) $serverInfo['memory']['usage_percent'];
                                        $progressClass = 'bg-success';
                                        if ($usagePercent > 70) $progressClass = 'bg-warning';
                                        if ($usagePercent > 90) $progressClass = 'bg-danger';
                                        ?>
                                        <div class="progress-bar <?= $progressClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $usagePercent ?>%"
                                             aria-valuenow="<?= $usagePercent ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= htmlspecialchars($serverInfo['memory']['usage_percent']) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- PHP扩展 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-plugin me-2"></i> PHP 扩展
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php 
                $extensions = explode(', ', $serverInfo['php']['extensions']);
                $chunks = array_chunk($extensions, ceil(count($extensions) / 4));
                
                foreach ($chunks as $chunk): 
                ?>
                    <div class="col-md-3">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($chunk as $extension): ?>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <?= htmlspecialchars($extension) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
// 引入布局底部
include_once VIEWS_PATH . '/layouts/footer.php';
?> 