<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <a href="/admin">
                <img src="/admin-center/assets/images/logo.png" alt="AlingAi Pro" class="logo-img">
                <span class="logo-text">IT运维中心</span>
            </a>
        </div>
        <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <div class="sidebar-body">
        <ul class="nav flex-column">
            <!-- 仪表盘 -->
            <li class="nav-item">
                <a href="/admin" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>仪表盘</span>
                </a>
            </li>
            
            <!-- 用户管理 -->
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>用户管理</span>
                </a>
            </li>
            
            <!-- 日志管理 -->
            <li class="nav-item">
                <a href="/admin/logs" class="nav-link <?= $currentPage === 'logs' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>日志管理</span>
                </a>
            </li>
            
            <!-- 系统工具 -->
            <li class="nav-item">
                <a href="#toolsSubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info']) ? 'active' : '' ?>">
                    <i class="bi bi-tools"></i>
                    <span>系统工具</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse <?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info']) ? 'show' : '' ?>" id="toolsSubmenu">
                    <li>
                        <a href="/admin/tools" class="nav-link <?= $currentPage === 'tools' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 工具首页
                        </a>
                    </li>
                    <li>
                        <a href="/admin/tools/phpinfo" class="nav-link <?= $currentPage === 'phpinfo' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> PHP信息
                        </a>
                    </li>
                    <li>
                        <a href="/admin/tools/server-info" class="nav-link <?= $currentPage === 'server-info' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 服务器信息
                        </a>
                    </li>
                    <li>
                        <a href="/admin/tools/database-info" class="nav-link <?= $currentPage === 'database-info' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 数据库信息
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- 备份管理 -->
            <li class="nav-item">
                <a href="/admin/backup" class="nav-link <?= $currentPage === 'backup' ? 'active' : '' ?>">
                    <i class="bi bi-archive"></i>
                    <span>备份管理</span>
                </a>
            </li>
            
            <!-- 系统设置 -->
            <li class="nav-item">
                <a href="/admin/settings" class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>系统设置</span>
                </a>
            </li>
            
            <!-- 分隔线 -->
            <li class="nav-divider"></li>
            
            <!-- 官方网站 -->
            <li class="nav-item">
                <a href="/" target="_blank" class="nav-link">
                    <i class="bi bi-house"></i>
                    <span>官方网站</span>
                </a>
            </li>
            
            <!-- 帮助文档 -->
            <li class="nav-item">
                <a href="/docs" target="_blank" class="nav-link">
                    <i class="bi bi-question-circle"></i>
                    <span>帮助文档</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="version">
            <span>版本: 1.0.0</span>
        </div>
    </div>
</div> 