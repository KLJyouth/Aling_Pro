<?php
/**
 * 系统工具首页
 */
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">系统工具</h5>
                        <p class="text-muted mb-0">系统诊断和维护工具集合</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <div class="tool-icon mb-3">
                    <i class="bi bi-filetype-php text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5>PHP信息</h5>
                <p class="text-muted">查看PHP配置、扩展和环境信息</p>
                <a href="/admin/tools/phpinfo" class="btn btn-primary">
                    <i class="bi bi-info-circle"></i> 查看详情
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <div class="tool-icon mb-3">
                    <i class="bi bi-server text-success" style="font-size: 3rem;"></i>
                </div>
                <h5>服务器信息</h5>
                <p class="text-muted">查看服务器硬件、操作系统和性能信息</p>
                <a href="/admin/tools/server-info" class="btn btn-success">
                    <i class="bi bi-info-circle"></i> 查看详情
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <div class="tool-icon mb-3">
                    <i class="bi bi-database text-info" style="font-size: 3rem;"></i>
                </div>
                <h5>数据库信息</h5>
                <p class="text-muted">查看数据库状态、表结构和性能指标</p>
                <a href="/admin/tools/database-info" class="btn btn-info">
                    <i class="bi bi-info-circle"></i> 查看详情
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">系统维护任务</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">缓存管理</h6>
                                <p class="card-text">清除系统缓存，优化性能</p>
                                <a href="/admin/cache/clear" class="btn btn-outline-primary">
                                    <i class="bi bi-trash"></i> 清除缓存
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">数据库优化</h6>
                                <p class="card-text">优化数据库表，提高查询效率</p>
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#optimizeDatabaseModal">
                                    <i class="bi bi-speedometer2"></i> 优化数据库
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">数据库备份</h6>
                                <p class="card-text">创建数据库备份，保障数据安全</p>
                                <a href="/admin/backup/create" class="btn btn-outline-success">
                                    <i class="bi bi-download"></i> 创建备份
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">系统日志</h6>
                                <p class="card-text">查看和管理系统日志记录</p>
                                <a href="/admin/logs" class="btn btn-outline-secondary">
                                    <i class="bi bi-journal-text"></i> 查看日志
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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