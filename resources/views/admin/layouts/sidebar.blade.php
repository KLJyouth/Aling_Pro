<!-- 数据库超级运维 -->
<li class="nav-item has-treeview {{ request()->is("admin/database*") ? "menu-open" : "" }}">
    <a href="#" class="nav-link {{ request()->is("admin/database*") ? "active" : "" }}">
        <i class="nav-icon fas fa-database"></i>
        <p>
            数据库超级运维
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route("admin.database.index") }}" class="nav-link {{ request()->is("admin/database") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>概览</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.database.tables") }}" class="nav-link {{ request()->is("admin/database/tables") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>表管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.database.structure") }}" class="nav-link {{ request()->is("admin/database/structure") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>数据库结构</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.database.backup.index") }}" class="nav-link {{ request()->is("admin/database/backup") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>备份管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.database.monitor") }}" class="nav-link {{ request()->is("admin/database/monitor") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>性能监控</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.database.slow-queries") }}" class="nav-link {{ request()->is("admin/database/slow-queries") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>慢查询分析</p>
            </a>
        </li>
    </ul>
</li>

<!-- 管理员管理 -->
<li class="nav-item has-treeview {{ request()->is("admin/admin-management*") ? "menu-open" : "" }}">
    <a href="#" class="nav-link {{ request()->is("admin/admin-management*") ? "active" : "" }}">
        <i class="nav-icon fas fa-user-shield"></i>
        <p>
            管理员管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route("admin.users.index") }}" class="nav-link {{ request()->is("admin/admin-management/users") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>管理员列表</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->is("admin/admin-management/roles*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>角色管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.permissions.index") }}" class="nav-link {{ request()->is("admin/admin-management/permissions") && !request()->is("admin/admin-management/permissions/groups*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>权限管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.permission.groups") }}" class="nav-link {{ request()->is("admin/admin-management/permissions/groups*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>权限组管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.logs.login") }}" class="nav-link {{ request()->is("admin/admin-management/logs/login*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>登录日志</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.logs.operation") }}" class="nav-link {{ request()->is("admin/admin-management/logs/operation*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>操作日志</p>
            </a>
        </li>
    </ul>
</li>

<!-- API风控监管 -->
<li class="nav-item has-treeview {{ request()->is("admin/api-security*") ? "menu-open" : "" }}">
    <a href="#" class="nav-link {{ request()->is("admin/api-security*") ? "active" : "" }}">
        <i class="nav-icon fas fa-shield-alt"></i>
        <p>
            API风控监管
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route("admin.api.dashboard") }}" class="nav-link {{ request()->is("admin/api-security/dashboard") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>监控仪表板</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.api.realtime") }}" class="nav-link {{ request()->is("admin/api-security/realtime") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>实时监控</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.api.interfaces.index") }}" class="nav-link {{ request()->is("admin/api-security/interfaces*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>接口管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.api.risk.rules.index") }}" class="nav-link {{ request()->is("admin/api-security/risk-rules*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>风控规则</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.api.risk.events.index") }}" class="nav-link {{ request()->is("admin/api-security/risk-events") && !request()->is("admin/api-security/risk-events/statistics") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>风险事件</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.api.risk.events.statistics") }}" class="nav-link {{ request()->is("admin/api-security/risk-events/statistics") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>风险统计</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.api.blacklists.index") }}" class="nav-link {{ request()->is("admin/api-security/blacklists*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>黑名单管理</p>
            </a>
        </li>
    </ul>
</li>

<!-- 用户管理 -->
<li class="nav-item has-treeview {{ request()->is("admin/users*") ? "menu-open" : "" }}">
    <a href="#" class="nav-link {{ request()->is("admin/users*") ? "active" : "" }}">
        <i class="nav-icon fas fa-users"></i>
        <p>
            用户管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route("admin.users.index") }}" class="nav-link {{ request()->is("admin/users") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>用户列表</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.users.verifications.index") }}" class="nav-link {{ request()->is("admin/users/verifications*") && !request()->is("admin/users/verifications/pending") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>用户认证管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.users.verifications.pending") }}" class="nav-link {{ request()->is("admin/users/verifications/pending") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>待审核认证</p>
            </a>
        </li>
    </ul>
</li>

<!-- AI接口管理 -->
<li class="nav-item has-treeview {{ request()->is("admin/ai*") ? "menu-open" : "" }}">
    <a href="#" class="nav-link {{ request()->is("admin/ai*") ? "active" : "" }}">
        <i class="nav-icon fas fa-robot"></i>
        <p>
            AI接口管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route("admin.ai.providers.index") }}" class="nav-link {{ request()->is("admin/ai/providers*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>模型提供商</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.agents.index") }}" class="nav-link {{ request()->is("admin/ai/agents*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>智能体管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.api-keys.index") }}" class="nav-link {{ request()->is("admin/ai/api-keys*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API密钥管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.settings.index") }}" class="nav-link {{ request()->is("admin/ai/settings") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>接口设置</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.settings.usage-stats") }}" class="nav-link {{ request()->is("admin/ai/settings/usage-stats") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>使用统计</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.advanced-settings.index") }}" class="nav-link {{ request()->is("admin/ai/advanced-settings*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>高级设置</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.logs.api") }}" class="nav-link {{ request()->is("admin/ai/logs/api*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API调用日志</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.logs.audit") }}" class="nav-link {{ request()->is("admin/ai/logs/audit*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>审计日志</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.ai.testing.index") }}" class="nav-link {{ request()->is("admin/ai/testing*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>智能体测试工具</p>
            </a>
        </li>
    </ul>
</li>

<!-- 第三方登录管理 -->
<li class="nav-item has-treeview {{ request()->is("admin/oauth*") ? "menu-open" : "" }}">
    <a href="#" class="nav-link {{ request()->is("admin/oauth*") ? "active" : "" }}">
        <i class="nav-icon fas fa-sign-in-alt"></i>
        <p>
            第三方登录管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route("admin.oauth.providers.index") }}" class="nav-link {{ request()->is("admin/oauth/providers*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>提供商管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.oauth.user-accounts.index") }}" class="nav-link {{ request()->is("admin/oauth/user-accounts*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>用户账号管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route("admin.oauth.logs.index") }}" class="nav-link {{ request()->is("admin/oauth/logs*") ? "active" : "" }}">
                <i class="far fa-circle nav-icon"></i>
                <p>登录日志</p>
            </a>
        </li>
    </ul>
</li>

<!-- API管理菜单 -->
<li class="nav-item has-treeview {{ request()->is('admin/security/api*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/security/api*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-key"></i>
        <p>
            API管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.security.api.documentation.index') }}" class="nav-link {{ request()->is('admin/security/api/documentation*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API文档</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.security.api.documentation.test-tool') }}" class="nav-link {{ request()->is('admin/security/api/documentation/test-tool*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API测试工具</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.security.api.interfaces.index') }}" class="nav-link {{ request()->is('admin/security/api/interfaces*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API接口管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.security.api.keys.index') }}" class="nav-link {{ request()->is('admin/security/api/keys*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API密钥管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.security.api.sdks.index') }}" class="nav-link {{ request()->is('admin/security/api/sdks*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>SDK管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.security.api.request-logs.index') }}" class="nav-link {{ request()->is('admin/security/api/request-logs') && !request()->is('admin/security/api/request-logs/statistics*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API请求日志</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.security.api.request-logs.statistics') }}" class="nav-link {{ request()->is('admin/security/api/request-logs/statistics*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>API使用统计</p>
            </a>
        </li>
    </ul>
</li>

<!-- 计费管理 -->
<li class="nav-item {{ request()->routeIs('admin.billing.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-credit-card"></i>
        <p>
            计费管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.billing.packages.index') }}" class="nav-link {{ request()->routeIs('admin.billing.packages.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>额度套餐管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.billing.products.index') }}" class="nav-link {{ request()->routeIs('admin.billing.products.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>商品管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.billing.orders.index') }}" class="nav-link {{ request()->routeIs('admin.billing.orders.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>订单管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.billing.user-packages.index') }}" class="nav-link {{ request()->routeIs('admin.billing.user-packages.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>用户套餐管理</p>
            </a>
        </li>
    </ul>
</li>

<!-- 会员管理 -->
<li class="nav-item {{ request()->routeIs('admin.membership.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('admin.membership.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>
            会员管理
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.membership.levels.index') }}" class="nav-link {{ request()->routeIs('admin.membership.levels.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>会员等级管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.membership.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.membership.subscriptions.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>会员订阅管理</p>
            </a>
        </li>
    </ul>
</li>
