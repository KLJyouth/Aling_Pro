<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">安全管理中心</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">首页</a></li>
                <li class="breadcrumb-item active">安全管理</li>
            </ol>
            
            <!-- 安全概览 -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h2><?= $securityOverview['securityScore'] ?></h2>
                            <div>安全评分</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <h2><?= $securityOverview['vulnerabilities'] ?></h2>
                            <div>发现漏洞</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <h2><?= $securityOverview['criticalIssues'] ?></h2>
                            <div>严重问题</div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h2><?= $securityOverview['lastScan'] ?></h2>
                            <div>最后扫描时�?/div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">查看详情</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 安全功能导航 -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-shield-alt mr-1"></i>
                            安全管理功能
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/security/permissions" class="btn btn-outline-primary btn-lg btn-block">
                                        <i class="fas fa-key fa-2x mb-2"></i><br>
                                        权限管理
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/security/backups" class="btn btn-outline-success btn-lg btn-block">
                                        <i class="fas fa-database fa-2x mb-2"></i><br>
                                        备份管理
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/security/users" class="btn btn-outline-info btn-lg btn-block">
                                        <i class="fas fa-users fa-2x mb-2"></i><br>
                                        用户管理
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/security/roles" class="btn btn-outline-warning btn-lg btn-block">
                                        <i class="fas fa-user-tag fa-2x mb-2"></i><br>
                                        角色管理
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 安全检查结�?-->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-check-circle mr-1"></i>
                            安全检查结�?
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>检查项</th>
                                        <th>状�?/th>
                                        <th>最后检查时�?/th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($securityChecks as $check): ?>
                                    <tr>
                                        <td>
                                            <?= $check['name'] ?>
                                            <small class="d-block text-muted"><?= $check['description'] ?></small>
                                        </td>
                                        <td>
                                            <?php if ($check['status'] === 'pass'): ?>
                                                <span class="badge badge-success">通过</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">失败</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $check['lastCheck'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary">运行安全检�?/button>
                        </div>
                    </div>
                </div>
                
                <!-- 最近安全事�?-->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-history mr-1"></i>
                            最近安全事�?
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>类型</th>
                                            <th>严重程度</th>
                                            <th>用户</th>
                                            <th>时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEvents as $event): ?>
                                        <tr>
                                            <td><?= $event['type'] ?></td>
                                            <td>
                                                <?php 
                                                switch ($event['severity']) {
                                                    case '�?:
                                                        echo '<span class="badge badge-danger">�?/span>';
                                                        break;
                                                    case '�?:
                                                        echo '<span class="badge badge-warning">�?/span>';
                                                        break;
                                                    case '�?:
                                                        echo '<span class="badge badge-info">�?/span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge badge-secondary">信息</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?= $event['user'] ?></td>
                                            <td><?= $event['timestamp'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#" class="btn btn-primary">查看所有事�?/a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 
