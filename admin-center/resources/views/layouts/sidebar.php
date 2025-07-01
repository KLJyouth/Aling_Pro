<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <a href="/admin">
                <img src="/admin-center/public/assets/images/logo.svg" alt="AlingAi Pro" class="logo-img">
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
            
            <!-- 系统监控 -->
            <li class="nav-item">
                <a href="#monitoringSubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['monitoring', 'resources', 'status']) ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i>
                    <span>系统监控</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse <?= in_array($currentPage, ['monitoring', 'resources', 'status']) ? 'show' : '' ?>" id="monitoringSubmenu">
                    <li>
                        <a href="/admin/monitoring" class="nav-link <?= $currentPage === 'monitoring' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 监控面板
                        </a>
                    </li>
                    <li>
                        <a href="/admin/monitoring/resources" class="nav-link <?= $currentPage === 'resources' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 资源使用
                        </a>
                    </li>
                    <li>
                        <a href="/admin/monitoring/status" class="nav-link <?= $currentPage === 'status' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 服务状态
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- 用户管理 -->
            <li class="nav-item">
                <a href="#usersSubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['users', 'user-create', 'user-edit']) ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>用户管理</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse <?= in_array($currentPage, ['users', 'user-create', 'user-edit']) ? 'show' : '' ?>" id="usersSubmenu">
                    <li>
                        <a href="/admin/users" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 用户列表
                        </a>
                    </li>
                    <li>
                        <a href="/admin/users/create" class="nav-link <?= $currentPage === 'user-create' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 添加用户
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- 日志管理 -->
            <li class="nav-item">
                <a href="#logsSubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['logs', 'error-logs', 'access-logs']) ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>日志管理</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse <?= in_array($currentPage, ['logs', 'error-logs', 'access-logs']) ? 'show' : '' ?>" id="logsSubmenu">
                    <li>
                        <a href="/admin/logs" class="nav-link <?= $currentPage === 'logs' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 日志概览
                        </a>
                    </li>
                    <li>
                        <a href="/admin/logs/error" class="nav-link <?= $currentPage === 'error-logs' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 错误日志
                        </a>
                    </li>
                    <li>
                        <a href="/admin/logs/access" class="nav-link <?= $currentPage === 'access-logs' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 访问日志
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- 系统工具 -->
            <li class="nav-item">
                <a href="#toolsSubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info', 'database-management', 'cache-optimizer', 'security-checker']) ? 'active' : '' ?>">
                    <i class="bi bi-tools"></i>
                    <span>系统工具</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse <?= in_array($currentPage, ['tools', 'phpinfo', 'server-info', 'database-info', 'database-management', 'cache-optimizer', 'security-checker']) ? 'show' : '' ?>" id="toolsSubmenu">
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
                    <li>
                        <a href="/admin/tools/database-management" class="nav-link <?= $currentPage === 'database-management' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 数据库管理
                        </a>
                    </li>
                    <li>
                        <a href="/admin/tools/cache-optimizer" class="nav-link <?= $currentPage === 'cache-optimizer' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 缓存优化
                        </a>
                    </li>
                    <li>
                        <a href="/admin/tools/security-checker" class="nav-link <?= $currentPage === 'security-checker' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 安全检测
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- 安全管理 -->
            <li class="nav-item">
                <a href="#securitySubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['security', 'firewall', 'scan']) ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>安全管理</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse <?= in_array($currentPage, ['security', 'firewall', 'scan']) ? 'show' : '' ?>" id="securitySubmenu">
                    <li>
                        <a href="/admin/security" class="nav-link <?= $currentPage === 'security' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 安全概览
                        </a>
                    </li>
                    <li>
                        <a href="/admin/security/firewall" class="nav-link <?= $currentPage === 'firewall' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 防火墙设置
                        </a>
                    </li>
                    <li>
                        <a href="/admin/security/scan" class="nav-link <?= $currentPage === 'scan' ? 'active' : '' ?>">
                            <i class="bi bi-circle"></i> 安全扫描
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
            
            <!-- 运维报告 -->
            <li class="nav-item">
                <a href="/admin/reports" class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>运维报告</span>
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
            <span>版本: <?= \App\Core\Config::get('app.version', '1.0.0') ?></span>
        </div>
    </div>
</div> 