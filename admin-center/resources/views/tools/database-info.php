<?php
/**
 * 数据库信息页面
 */
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">数据库信息</h5>
                        <p class="text-muted mb-0">查看数据库状态、表结构和性能指标</p>
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
    
    <?php if (isset($dbInfo['error'])): ?>
    <div class="col-12">
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($dbInfo['error']) ?>
        </div>
    </div>
    <?php else: ?>
    
    <!-- 数据库概览 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-database"></i> 数据库概览
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">数据库版本</th>
                            <td><?= htmlspecialchars($dbInfo['version']) ?></td>
                        </tr>
                        <tr>
                            <th>连接状态</th>
                            <td><?= htmlspecialchars($dbInfo['connection']) ?></td>
                        </tr>
                        <tr>
                            <th>服务器信息</th>
                            <td><?= htmlspecialchars($dbInfo['server_info'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>客户端版本</th>
                            <td><?= htmlspecialchars($dbInfo['client_version']) ?></td>
                        </tr>
                        <tr>
                            <th>驱动名称</th>
                            <td><?= htmlspecialchars($dbInfo['driver_name']) ?></td>
                        </tr>
                        <tr>
                            <th>数据库大小</th>
                            <td><?= number_format($dbInfo['size'], 2) ?> MB</td>
                        </tr>
                        <tr>
                            <th>表数量</th>
                            <td><?= number_format($dbInfo['tables']['table_count']) ?></td>
                        </tr>
                        <tr>
                            <th>数据大小</th>
                            <td><?= number_format($dbInfo['tables']['data_size_mb'], 2) ?> MB</td>
                        </tr>
                        <tr>
                            <th>索引大小</th>
                            <td><?= number_format($dbInfo['tables']['index_size_mb'], 2) ?> MB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 数据库状态 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-activity"></i> 数据库状态
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">运行时间</th>
                            <td><?= isset($dbInfo['status']['Uptime']) ? gmdate("H:i:s", $dbInfo['status']['Uptime']) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>当前连接数</th>
                            <td><?= $dbInfo['status']['Threads_connected'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>运行线程数</th>
                            <td><?= $dbInfo['status']['Threads_running'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>查询总数</th>
                            <td><?= isset($dbInfo['status']['Queries']) ? number_format($dbInfo['status']['Queries']) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>慢查询数</th>
                            <td><?= isset($dbInfo['status']['Slow_queries']) ? number_format($dbInfo['status']['Slow_queries']) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>打开表数</th>
                            <td><?= isset($dbInfo['status']['Opened_tables']) ? number_format($dbInfo['status']['Opened_tables']) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>创建临时表数</th>
                            <td><?= isset($dbInfo['status']['Created_tmp_tables']) ? number_format($dbInfo['status']['Created_tmp_tables']) : 'N/A' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 查询统计 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart"></i> 查询统计
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($dbInfo['status'])): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="text-primary mb-0">SELECT</h6>
                                <h3><?= number_format($dbInfo['status']['Com_select'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="text-success mb-0">INSERT</h6>
                                <h3><?= number_format($dbInfo['status']['Com_insert'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="text-warning mb-0">UPDATE</h6>
                                <h3><?= number_format($dbInfo['status']['Com_update'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="text-danger mb-0">DELETE</h6>
                                <h3><?= number_format($dbInfo['status']['Com_delete'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 查询统计信息不可用
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 数据库变量 -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear"></i> 数据库配置
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <?php if (isset($dbInfo['variables'])): ?>
                        <tr>
                            <th width="40%">最大连接数</th>
                            <td><?= $dbInfo['variables']['max_connections'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>连接超时</th>
                            <td><?= $dbInfo['variables']['connect_timeout'] ?? 'N/A' ?> 秒</td>
                        </tr>
                        <tr>
                            <th>等待超时</th>
                            <td><?= $dbInfo['variables']['wait_timeout'] ?? 'N/A' ?> 秒</td>
                        </tr>
                        <tr>
                            <th>最大数据包</th>
                            <td><?= isset($dbInfo['variables']['max_allowed_packet']) ? $this->formatBytes($dbInfo['variables']['max_allowed_packet']) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>InnoDB缓冲池大小</th>
                            <td><?= isset($dbInfo['variables']['innodb_buffer_pool_size']) ? $this->formatBytes($dbInfo['variables']['innodb_buffer_pool_size']) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>字符集</th>
                            <td><?= $dbInfo['variables']['character_set_server'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>排序规则</th>
                            <td><?= $dbInfo['variables']['collation_server'] ?? 'N/A' ?></td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">数据库变量信息不可用</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- 数据表列表 -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i> 数据表列表
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>表名</th>
                                <th>引擎</th>
                                <th>行数</th>
                                <th>数据大小</th>
                                <th>索引大小</th>
                                <th>创建时间</th>
                                <th>更新时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dbInfo['table_list']) && count($dbInfo['table_list']) > 0): ?>
                                <?php foreach ($dbInfo['table_list'] as $table): ?>
                                <tr>
                                    <td><?= htmlspecialchars($table['table_name']) ?></td>
                                    <td><?= htmlspecialchars($table['engine']) ?></td>
                                    <td><?= number_format($table['table_rows']) ?></td>
                                    <td><?= number_format($table['data_size_mb'], 2) ?> MB</td>
                                    <td><?= number_format($table['index_size_mb'], 2) ?> MB</td>
                                    <td><?= $table['create_time'] ? date('Y-m-d H:i:s', strtotime($table['create_time'])) : 'N/A' ?></td>
                                    <td><?= $table['update_time'] ? date('Y-m-d H:i:s', strtotime($table['update_time'])) : 'N/A' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">暂无数据表信息</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div> 