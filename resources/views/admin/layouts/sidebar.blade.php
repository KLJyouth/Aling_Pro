<!-- 数据库超级运维 -->
<li class="nav-item has-treeview {{ request()->is('admin/database*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/database*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-database"></i>
        <p>
            数据库超级运维
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.database.index') }}" class="nav-link {{ request()->is('admin/database') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>概览</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.database.tables') }}" class="nav-link {{ request()->is('admin/database/tables') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>表管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.database.structure') }}" class="nav-link {{ request()->is('admin/database/structure') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>数据库结构</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.database.backup.index') }}" class="nav-link {{ request()->is('admin/database/backup') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>备份管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.database.monitor') }}" class="nav-link {{ request()->is('admin/database/monitor') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>性能监控</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.database.slow-queries') }}" class="nav-link {{ request()->is('admin/database/slow-queries') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>慢查询分析</p>
            </a>
        </li>
    </ul>
</li> 