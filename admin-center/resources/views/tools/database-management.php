<?php
/**
 * 数据库管理页面视图
 */
// 引入布局模板
include_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4"><?= $pageHeader ?? '数据库管理' ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">数据库表</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                            <?php foreach ($tables as $table): ?>
                                <a href="?table=<?= htmlspecialchars($table) ?>&action=structure" 
                                   class="list-group-item list-group-item-action <?= ($currentTable == $table) ? 'active' : '' ?>">
                                    <i class="bi bi-table me-2"></i>
                                    <?= htmlspecialchars($table) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <?php if (!empty($currentTable)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link <?= ($result['type'] ?? '') == 'structure' ? 'active' : '' ?>" 
                                       href="?table=<?= htmlspecialchars($currentTable) ?>&action=structure">
                                       <i class="bi bi-list-columns me-1"></i> 表结构
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= ($result['type'] ?? '') == 'data' ? 'active' : '' ?>" 
                                       href="?table=<?= htmlspecialchars($currentTable) ?>&action=data">
                                       <i class="bi bi-grid me-1"></i> 数据浏览
                                    </a>
                                </li>
                                <li class="nav-item ms-auto">
                                    <a class="nav-link text-success" 
                                       href="?table=<?= htmlspecialchars($currentTable) ?>&action=optimize" 
                                       onclick="return confirm('确定要优化表 <?= htmlspecialchars($currentTable) ?> 吗？')">
                                       <i class="bi bi-speedometer me-1"></i> 优化表
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <?php if (isset($result['message'])): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?= htmlspecialchars($result['message']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (($result['type'] ?? '') == 'structure'): ?>
                                <h5 class="card-title mb-3">表 <?= htmlspecialchars($currentTable) ?> 的结构</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>字段名</th>
                                                <th>类型</th>
                                                <th>空</th>
                                                <th>键</th>
                                                <th>默认值</th>
                                                <th>额外</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result['data'] as $column): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($column['Field']) ?></td>
                                                    <td><?= htmlspecialchars($column['Type']) ?></td>
                                                    <td><?= $column['Null'] == 'YES' ? 'Yes' : 'No' ?></td>
                                                    <td><?= htmlspecialchars($column['Key']) ?></td>
                                                    <td><?= $column['Default'] !== null ? htmlspecialchars($column['Default']) : '<em>NULL</em>' ?></td>
                                                    <td><?= htmlspecialchars($column['Extra']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif (($result['type'] ?? '') == 'data'): ?>
                                <h5 class="card-title mb-3">表 <?= htmlspecialchars($currentTable) ?> 的数据</h5>
                                
                                <?php if (empty($result['data'])): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        表中没有数据
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <?php foreach (array_keys($result['data'][0]) as $header): ?>
                                                        <th><?= htmlspecialchars($header) ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($result['data'] as $row): ?>
                                                    <tr>
                                                        <?php foreach ($row as $value): ?>
                                                            <td>
                                                                <?php 
                                                                if ($value === null) {
                                                                    echo '<em class="text-muted">NULL</em>';
                                                                } else {
                                                                    echo htmlspecialchars($value);
                                                                }
                                                                ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <?php if (count($result['data']) >= $result['limit']): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            显示的记录已达到限制 (<?= $result['limit'] ?> 条)
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-database text-muted" style="font-size: 5rem;"></i>
                            <h4 class="mt-3">请从左侧选择一个数据库表</h4>
                            <p class="text-muted">选择后可以查看表结构和数据内容</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// 引入布局底部
include_once VIEWS_PATH . '/layouts/footer.php';
?> 