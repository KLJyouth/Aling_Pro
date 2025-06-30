<div class="sidebar">
    <div class="sidebar-header">
        <div class="app-brand">
            <a href="/admin" class="brand-link">
                <span class="brand-logo">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <span class="brand-text">IT运维中心</span>
            </a>
        </div>
    </div>

    <div class="sidebar-body">
        <ul class="nav">
            <!-- 仪表盘 -->
            <li class="nav-item">
                <a href="/admin" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <span class="nav-text">仪表盘</span>
                </a>
            </li>

            <!-- 用户管理 -->
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-people"></i>
                    <span class="nav-text">用户管理</span>
                </a>
            </li>
            
            <!-- 系统日志 -->
            <li class="nav-item">
                <a href="/admin/logs" class="nav-link <?= $currentPage === 'logs' ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-journal-text"></i>
                    <span class="nav-text">系统日志</span>
                </a>
            </li>
            
            <!-- 备份管理 -->
            <li class="nav-item">
                <a href="/admin/backup" class="nav-link <?= $currentPage === 'backup' ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-archive"></i>
                    <span class="nav-text">备份管理</span>
                </a>
            </li>
            
            <!-- 系统工具 -->
            <li class="nav-item">
                <a href="#" class="nav-link <?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#tools-collapse" aria-expanded="<?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info']) ? 'true' : 'false' ?>">
                    <i class="nav-icon bi bi-tools"></i>
                    <span class="nav-text">系统工具</span>
                    <i class="nav-arrow bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info']) ? 'show' : '' ?>" id="tools-collapse">
                    <ul class="nav-sub">
                        <li class="nav-item">
                            <a href="/admin/tools/phpinfo" class="nav-link <?= $currentPage === 'phpinfo' ? 'active' : '' ?>">
                                <span class="nav-text">PHP信息</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/tools/server-info" class="nav-link <?= $currentPage === 'server-info' ? 'active' : '' ?>">
                                <span class="nav-text">服务器信息</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/tools/database-info" class="nav-link <?= $currentPage === 'database-info' ? 'active' : '' ?>">
                                <span class="nav-text">数据库信息</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- 系统设置 -->
            <li class="nav-item">
                <a href="/admin/settings" class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-gear"></i>
                    <span class="nav-text">系统设置</span>
                </a>
            </li>
        </ul>
    </div>
</div> 