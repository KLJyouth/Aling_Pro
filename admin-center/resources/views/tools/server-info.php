<?php
/**
 * 服务器信息页面
 */
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">服务器信息</h5>
                        <p class="text-muted mb-0">查看服务器硬件、操作系统和性能信息</p>
                    </div>
                    <div>
                        <a href="/admin/tools" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 返回工具列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 系统信息 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-cpu"></i> 系统信息
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">操作系统</th>
                            <td><?= htmlspecialchars($serverInfo['os']['name']) ?></td>
                        </tr>
                        <tr>
                            <th>系统版本</th>
                            <td><?= htmlspecialchars($serverInfo['os']['version']) ?></td>
                        </tr>
                        <tr>
                            <th>系统架构</th>
                            <td><?= htmlspecialchars($serverInfo['os']['architecture']) ?></td>
                        </tr>
                        <tr>
                            <th>主机名</th>
                            <td><?= htmlspecialchars($serverInfo['os']['hostname']) ?></td>
                        </tr>
                        <?php if (isset($serverInfo['cpu'])): ?>
                        <tr>
                            <th>CPU型号</th>
                            <td><?= htmlspecialchars($serverInfo['cpu']['model']) ?></td>
                        </tr>
                        <tr>
                            <th>CPU核心数</th>
                            <td><?= htmlspecialchars($serverInfo['cpu']['cores']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>服务器时间</th>
                            <td><?= htmlspecialchars($serverInfo['time']['server_time']) ?></td>
                        </tr>
                        <tr>
                            <th>时区</th>
                            <td><?= htmlspecialchars($serverInfo['time']['timezone']) ?></td>
                        </tr>
                        <tr>
                            <th>运行时间</th>
                            <td><?= htmlspecialchars($serverInfo['time']['uptime']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 内存和磁盘 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-hdd"></i> 资源使用情况
                </h5>
            </div>
            <div class="card-body">
                <!-- 磁盘使用情况 -->
                <h6>磁盘使用情况</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>总容量: <?= $serverInfo['disk']['total'] ?></span>
                        <span>已使用: <?= $serverInfo['disk']['usage'] ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" 
                            style="width: <?= str_replace('%', '', $serverInfo['disk']['usage']) ?>%" 
                            aria-valuenow="<?= str_replace('%', '', $serverInfo['disk']['usage']) ?>" 
                            aria-valuemin="0" aria-valuemax="100">
                            <?= $serverInfo['disk']['usage'] ?>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>已用: <?= $serverInfo['disk']['used'] ?></small>
                        <small>可用: <?= $serverInfo['disk']['free'] ?></small>
                    </div>
                </div>
                
                <!-- 内存使用情况 -->
                <?php if (isset($serverInfo['memory'])): ?>
                <h6>内存使用情况</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>总内存: <?= $serverInfo['memory']['total'] ?></span>
                        <span>已使用: <?= $serverInfo['memory']['usage'] ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: <?= str_replace('%', '', $serverInfo['memory']['usage']) ?>%" 
                            aria-valuenow="<?= str_replace('%', '', $serverInfo['memory']['usage']) ?>" 
                            aria-valuemin="0" aria-valuemax="100">
                            <?= $serverInfo['memory']['usage'] ?>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>已用: <?= $serverInfo['memory']['used'] ?></small>
                        <small>可用: <?= $serverInfo['memory']['free'] ?></small>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 负载信息 -->
                <h6>系统负载</h6>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> 
                    系统负载信息可通过命令行工具查看，如 <code>top</code> 或 <code>htop</code>。
                </div>
            </div>
        </div>
    </div>
    
    <!-- Web服务器信息 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-globe"></i> Web服务器信息
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">服务器软件</th>
                            <td><?= htmlspecialchars($serverInfo['server']['software']) ?></td>
                        </tr>
                        <tr>
                            <th>协议</th>
                            <td><?= htmlspecialchars($serverInfo['server']['protocol']) ?></td>
                        </tr>
                        <tr>
                            <th>服务器地址</th>
                            <td><?= htmlspecialchars($serverInfo['server']['addr']) ?></td>
                        </tr>
                        <tr>
                            <th>服务器名称</th>
                            <td><?= htmlspecialchars($serverInfo['server']['name']) ?></td>
                        </tr>
                        <tr>
                            <th>服务器端口</th>
                            <td><?= htmlspecialchars($serverInfo['server']['port']) ?></td>
                        </tr>
                        <tr>
                            <th>文档根目录</th>
                            <td><?= htmlspecialchars($serverInfo['server']['document_root']) ?></td>
                        </tr>
                        <tr>
                            <th>客户端IP</th>
                            <td><?= htmlspecialchars($serverInfo['server']['remote_addr']) ?></td>
                        </tr>
                        <tr>
                            <th>用户代理</th>
                            <td class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($serverInfo['server']['user_agent']) ?>">
                                <?= htmlspecialchars($serverInfo['server']['user_agent']) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- PHP信息 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-filetype-php"></i> PHP信息
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">PHP版本</th>
                            <td><?= htmlspecialchars($serverInfo['php']['version']) ?></td>
                        </tr>
                        <tr>
                            <th>SAPI接口</th>
                            <td><?= htmlspecialchars($serverInfo['php']['sapi']) ?></td>
                        </tr>
                        <tr>
                            <th>内存限制</th>
                            <td><?= htmlspecialchars($serverInfo['php']['memory_limit']) ?></td>
                        </tr>
                        <tr>
                            <th>最大执行时间</th>
                            <td><?= htmlspecialchars($serverInfo['php']['max_execution_time']) ?></td>
                        </tr>
                        <tr>
                            <th>上传文件大小限制</th>
                            <td><?= htmlspecialchars($serverInfo['php']['upload_max_filesize']) ?></td>
                        </tr>
                        <tr>
                            <th>POST数据大小限制</th>
                            <td><?= htmlspecialchars($serverInfo['php']['post_max_size']) ?></td>
                        </tr>
                        <tr>
                            <th>错误显示</th>
                            <td><?= htmlspecialchars($serverInfo['php']['display_errors']) ?></td>
                        </tr>
                        <tr>
                            <th>最大输入变量</th>
                            <td><?= htmlspecialchars($serverInfo['php']['max_input_vars']) ?></td>
                        </tr>
                        <tr>
                            <th>默认字符集</th>
                            <td><?= htmlspecialchars($serverInfo['php']['default_charset']) ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-3">
                    <h6>已加载扩展</h6>
                    <div class="border rounded p-2 bg-light">
                        <div style="max-height: 150px; overflow-y: auto;">
                            <?= nl2br(htmlspecialchars($serverInfo['php']['extensions'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 