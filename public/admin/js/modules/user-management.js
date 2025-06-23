/**
 * AlingAi Pro 5.0 - 用户管理模块
 * 处理所有用户相关的管理功能
 */

class UserManagementModule {
    constructor() {
        this.apiBaseUrl = '/admin/api';
        this.currentPage = 1;
        this.pageSize = 20;
        this.totalUsers = 0;
        this.selectedUsers = new Set();
        this.filters = {
            search: '',
            status: '',
            role: '',
            dateRange: null
        };
        
        this.init();
    }
    
    init() {
        this.createUserInterface();
        this.bindEvents();
        this.loadUsers();
        this.loadRoles();
    }
    
    createUserInterface() {
        const container = document.getElementById('user-management-container');
        if (!container) {
            console.error('用户管理容器不存在');
            return;
        }
        
        container.innerHTML = `
            <div class="user-management-module">
                <!-- 用户管理头部 -->
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="module-title">
                                <i class="fas fa-users text-primary me-2"></i>
                                用户管理
                            </h2>
                            <p class="module-subtitle text-muted">管理系统用户账户、权限和余额</p>
                        </div>
                        <div class="header-actions">
                            <button class="btn btn-success" onclick="userModule.showCreateUserModal()">
                                <i class="fas fa-plus me-2"></i>添加用户
                            </button>
                            <button class="btn btn-outline-primary" onclick="userModule.exportUsers()">
                                <i class="fas fa-download me-2"></i>导出用户
                            </button>
                            <button class="btn btn-outline-secondary" onclick="userModule.refreshUsers()">
                                <i class="fas fa-refresh me-2"></i>刷新
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 统计卡片 -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number" id="totalUsersCount">0</h3>
                                <p class="stat-label">总用户数</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-success text-white">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number" id="activeUsersCount">0</h3>
                                <p class="stat-label">活跃用户</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-warning text-white">
                            <div class="stat-icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number" id="pendingUsersCount">0</h3>
                                <p class="stat-label">待审核</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-danger text-white">
                            <div class="stat-icon">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number" id="blockedUsersCount">0</h3>
                                <p class="stat-label">已封禁</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 搜索和筛选 -->
                <div class="filter-section card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="userSearchInput" 
                                           placeholder="搜索用户名、邮箱或手机号">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="statusFilter">
                                    <option value="">所有状态</option>
                                    <option value="active">正常</option>
                                    <option value="inactive">未激活</option>
                                    <option value="suspended">已暂停</option>
                                    <option value="banned">已封禁</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="roleFilter">
                                    <option value="">所有角色</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" id="dateFromFilter" placeholder="开始日期">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" id="dateToFilter" placeholder="结束日期">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="userModule.applyFilters()">
                                    <i class="fas fa-filter me-2"></i>应用筛选
                                </button>
                                <button class="btn btn-outline-secondary" onclick="userModule.clearFilters()">
                                    <i class="fas fa-times me-2"></i>清除筛选
                                </button>
                                <div class="float-end">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            批量操作
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="userModule.batchAction('activate')">
                                                <i class="fas fa-check me-2"></i>激活选中用户
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="userModule.batchAction('suspend')">
                                                <i class="fas fa-pause me-2"></i>暂停选中用户
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="userModule.batchAction('delete')">
                                                <i class="fas fa-trash me-2"></i>删除选中用户
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="userModule.batchAction('export')">
                                                <i class="fas fa-download me-2"></i>导出选中用户
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 用户列表 -->
                <div class="users-table-container card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                            </div>
                                        </th>
                                        <th width="60">头像</th>
                                        <th>用户信息</th>
                                        <th>角色</th>
                                        <th>状态</th>
                                        <th>余额</th>
                                        <th>最后登录</th>
                                        <th>注册时间</th>
                                        <th width="200">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- 用户数据将在这里动态加载 -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- 分页 -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="pagination-info">
                                显示第 <span id="pageStartIndex">1</span> - <span id="pageEndIndex">20</span> 条，
                                共 <span id="totalUsersDisplay">0</span> 条记录
                            </div>
                            <nav>
                                <ul class="pagination mb-0" id="usersPagination">
                                    <!-- 分页按钮将在这里动态生成 -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 创建/编辑用户模态框 -->
            <div class="modal fade" id="userModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userModalTitle">添加用户</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="userForm">
                                <input type="hidden" id="userId" name="id">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">用户名 *</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">邮箱 *</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">手机号</label>
                                            <input type="tel" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">角色 *</label>
                                            <select class="form-select" id="roleId" name="role_id" required>
                                                <option value="">请选择角色</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">密码</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                            <small class="form-text text-muted">编辑时留空表示不修改密码</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">确认密码</label>
                                            <input type="password" class="form-control" id="confirmPassword">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">初始余额</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="balance" name="balance" 
                                                       value="0" min="0" step="0.01">
                                                <span class="input-group-text">元</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">状态</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active">正常</option>
                                                <option value="inactive">未激活</option>
                                                <option value="suspended">已暂停</option>
                                                <option value="banned">已封禁</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">备注</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="button" class="btn btn-primary" onclick="userModule.saveUser()">保存</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 用户详情模态框 -->
            <div class="modal fade" id="userDetailModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">用户详情</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="userDetailContent">
                            <!-- 用户详情将在这里动态加载 -->
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    bindEvents() {
        // 搜索框实时搜索
        const searchInput = document.getElementById('userSearchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.filters.search = searchInput.value;
                    this.loadUsers();
                }, 500);
            });
        }
        
        // 全选复选框
        const selectAllCheckbox = document.getElementById('selectAllUsers');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.user-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                    if (e.target.checked) {
                        this.selectedUsers.add(checkbox.value);
                    } else {
                        this.selectedUsers.delete(checkbox.value);
                    }
                });
            });
        }
    }
    
    async loadUsers() {
        try {
            this.showLoading();
            
            const params = new URLSearchParams({
                page: this.currentPage,
                limit: this.pageSize,
                search: this.filters.search,
                status: this.filters.status,
                role: this.filters.role
            });
            
            if (this.filters.dateRange) {
                params.append('date_from', this.filters.dateRange.from);
                params.append('date_to', this.filters.dateRange.to);
            }
            
            const response = await this.apiRequest(`/users?${params}`);
            
            if (response.success) {
                this.renderUsers(response.data.users);
                this.updatePagination(response.data.pagination);
                this.updateStatistics(response.data.statistics);
            } else {
                this.showError('加载用户数据失败：' + response.error);
            }
            
        } catch (error) {
            console.error('加载用户数据错误：', error);
            this.showError('网络错误，请稍后重试');
        } finally {
            this.hideLoading();
        }
    }
    
    renderUsers(users) {
        const tbody = document.getElementById('usersTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = users.map(user => `
            <tr>
                <td>
                    <div class="form-check">
                        <input class="form-check-input user-checkbox" type="checkbox" 
                               value="${user.id}" onchange="userModule.toggleUserSelection(${user.id})">
                    </div>
                </td>
                <td>
                    <img src="${user.avatar || '/assets/images/default-avatar.png'}" 
                         class="rounded-circle" width="40" height="40" alt="头像">
                </td>
                <td>
                    <div class="user-info">
                        <div class="fw-bold">${user.username}</div>
                        <div class="text-muted small">${user.email}</div>
                        ${user.phone ? `<div class="text-muted small">${user.phone}</div>` : ''}
                    </div>
                </td>
                <td>
                    <span class="badge bg-info">${user.role_name || '未分配'}</span>
                </td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(user.status)}">${this.getStatusText(user.status)}</span>
                </td>
                <td>
                    <span class="fw-bold text-success">¥${parseFloat(user.balance || 0).toFixed(2)}</span>
                </td>
                <td>
                    <span class="text-muted">${user.last_login_at ? this.formatDateTime(user.last_login_at) : '从未登录'}</span>
                </td>
                <td>
                    <span class="text-muted">${this.formatDateTime(user.created_at)}</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="userModule.viewUser(${user.id})" 
                                title="查看详情">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="userModule.editUser(${user.id})" 
                                title="编辑">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="userModule.adjustBalance(${user.id})" 
                                title="调整余额">
                            <i class="fas fa-coins"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="userModule.viewChatHistory(${user.id})" 
                                title="聊天记录">
                            <i class="fas fa-comments"></i>
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" 
                                    title="更多操作">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="userModule.resetPassword(${user.id})">
                                    <i class="fas fa-key me-2"></i>重置密码
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="userModule.viewTokens(${user.id})">
                                    <i class="fas fa-coins me-2"></i>Token记录
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="userModule.viewLoginLogs(${user.id})">
                                    <i class="fas fa-history me-2"></i>登录日志
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="userModule.deleteUser(${user.id})">
                                    <i class="fas fa-trash me-2"></i>删除用户
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    // 工具方法
    getStatusBadgeClass(status) {
        const classes = {
            'active': 'bg-success',
            'inactive': 'bg-warning',
            'suspended': 'bg-secondary',
            'banned': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }
    
    getStatusText(status) {
        const texts = {
            'active': '正常',
            'inactive': '未激活',
            'suspended': '已暂停',
            'banned': '已封禁'
        };
        return texts[status] || '未知';
    }
    
    formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('zh-CN');
    }
    
    // API请求方法
    async apiRequest(endpoint, options = {}) {
        const token = localStorage.getItem('admin_access_token');
        const defaultOptions = {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        };
        
        const response = await fetch(`${this.apiBaseUrl}${endpoint}`, {
            ...defaultOptions,
            ...options
        });
        
        return await response.json();
    }
    
    // 显示加载状态
    showLoading() {
        const tbody = document.getElementById('usersTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="spinner-border text-primary me-2"></div>
                        加载中...
                    </td>
                </tr>
            `;
        }
    }
    
    hideLoading() {
        // 由renderUsers方法替换内容，这里不需要额外操作
    }
    
    showError(message) {
        // 这里可以集成全局的错误提示系统
        alert('错误: ' + message);
    }
    
    showSuccess(message) {
        // 这里可以集成全局的成功提示系统
        alert('成功: ' + message);
    }
    
    // 更新分页
    updatePagination(pagination) {
        const paginationContainer = document.getElementById('usersPagination');
        const startIndex = document.getElementById('pageStartIndex');
        const endIndex = document.getElementById('pageEndIndex');
        const totalDisplay = document.getElementById('totalUsersDisplay');
        
        if (startIndex && endIndex && totalDisplay) {
            const start = (pagination.currentPage - 1) * pagination.perPage + 1;
            const end = Math.min(pagination.currentPage * pagination.perPage, pagination.total);
            
            startIndex.textContent = start;
            endIndex.textContent = end;
            totalDisplay.textContent = pagination.total.toLocaleString();
        }
        
        if (paginationContainer) {
            let paginationHtml = '';
            
            // 上一页
            if (pagination.currentPage > 1) {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="userModule.goToPage(${pagination.currentPage - 1})">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                `;
            }
            
            // 页码
            const startPage = Math.max(1, pagination.currentPage - 2);
            const endPage = Math.min(pagination.totalPages, pagination.currentPage + 2);
            
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === pagination.currentPage ? 'active' : '';
                paginationHtml += `
                    <li class="page-item ${activeClass}">
                        <a class="page-link" href="#" onclick="userModule.goToPage(${i})">${i}</a>
                    </li>
                `;
            }
            
            // 下一页
            if (pagination.currentPage < pagination.totalPages) {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="userModule.goToPage(${pagination.currentPage + 1})">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                `;
            }
            
            paginationContainer.innerHTML = paginationHtml;
        }
    }
    
    // 更新统计数据
    updateStatistics(stats) {
        if (stats) {
            document.getElementById('totalUsersCount').textContent = stats.total || 0;
            document.getElementById('activeUsersCount').textContent = stats.active || 0;
            document.getElementById('pendingUsersCount').textContent = stats.pending || 0;
            document.getElementById('blockedUsersCount').textContent = stats.blocked || 0;
        }
    }
    
    // 加载角色数据
    async loadRoles() {
        try {
            const response = await this.apiRequest('/roles');
            if (response.success) {
                const roleSelect = document.getElementById('roleId');
                const roleFilter = document.getElementById('roleFilter');
                
                if (roleSelect) {
                    roleSelect.innerHTML = '<option value="">请选择角色</option>';
                    response.data.forEach(role => {
                        roleSelect.innerHTML += `<option value="${role.id}">${role.display_name}</option>`;
                    });
                }
                
                if (roleFilter) {
                    roleFilter.innerHTML = '<option value="">所有角色</option>';
                    response.data.forEach(role => {
                        roleFilter.innerHTML += `<option value="${role.id}">${role.display_name}</option>`;
                    });
                }
            }
        } catch (error) {
            console.error('加载角色数据失败:', error);
        }
    }
    
    // 页面跳转
    goToPage(page) {
        this.currentPage = page;
        this.loadUsers();
    }
    
    // 应用筛选
    applyFilters() {
        this.filters.search = document.getElementById('userSearchInput').value;
        this.filters.status = document.getElementById('statusFilter').value;
        this.filters.role = document.getElementById('roleFilter').value;
        
        const dateFrom = document.getElementById('dateFromFilter').value;
        const dateTo = document.getElementById('dateToFilter').value;
        
        if (dateFrom && dateTo) {
            this.filters.dateRange = { from: dateFrom, to: dateTo };
        } else {
            this.filters.dateRange = null;
        }
        
        this.currentPage = 1;
        this.loadUsers();
    }
    
    // 清除筛选
    clearFilters() {
        document.getElementById('userSearchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('roleFilter').value = '';
        document.getElementById('dateFromFilter').value = '';
        document.getElementById('dateToFilter').value = '';
        
        this.filters = {
            search: '',
            status: '',
            role: '',
            dateRange: null
        };
        
        this.currentPage = 1;
        this.loadUsers();
    }
    
    // 切换用户选择
    toggleUserSelection(userId) {
        if (this.selectedUsers.has(userId.toString())) {
            this.selectedUsers.delete(userId.toString());
        } else {
            this.selectedUsers.add(userId.toString());
        }
    }
    
    // 显示创建用户模态框
    showCreateUserModal() {
        const modal = new bootstrap.Modal(document.getElementById('userModal'));
        document.getElementById('userModalTitle').textContent = '添加用户';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        modal.show();
    }
    
    // 编辑用户
    async editUser(userId) {
        try {
            const response = await this.apiRequest(`/users/${userId}`);
            if (response.success) {
                const user = response.data;
                
                document.getElementById('userModalTitle').textContent = '编辑用户';
                document.getElementById('userId').value = user.id;
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('phone').value = user.phone || '';
                document.getElementById('roleId').value = user.role_id;
                document.getElementById('balance').value = user.balance || 0;
                document.getElementById('status').value = user.status;
                document.getElementById('notes').value = user.notes || '';
                
                const modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
            } else {
                this.showError('加载用户信息失败：' + response.error);
            }
        } catch (error) {
            console.error('编辑用户错误:', error);
            this.showError('网络错误，请稍后重试');
        }
    }
    
    // 保存用户
    async saveUser() {
        const form = document.getElementById('userForm');
        const formData = new FormData(form);
        const userId = formData.get('id');
        
        // 验证密码
        const password = formData.get('password');
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (password && password !== confirmPassword) {
            this.showError('两次输入的密码不一致');
            return;
        }
        
        const userData = {};
        for (let [key, value] of formData.entries()) {
            if (key !== 'id' && value) {
                userData[key] = value;
            }
        }
        
        try {
            let response;
            if (userId) {
                // 更新用户
                response = await this.apiRequest(`/users/${userId}`, {
                    method: 'PUT',
                    body: JSON.stringify(userData)
                });
            } else {
                // 创建用户
                response = await this.apiRequest('/users', {
                    method: 'POST',
                    body: JSON.stringify(userData)
                });
            }
            
            if (response.success) {
                this.showSuccess(userId ? '用户更新成功' : '用户创建成功');
                const modal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
                modal.hide();
                this.loadUsers();
            } else {
                this.showError('保存失败：' + response.error);
            }
        } catch (error) {
            console.error('保存用户错误:', error);
            this.showError('网络错误，请稍后重试');
        }
    }
    
    // 查看用户详情
    async viewUser(userId) {
        try {
            const response = await this.apiRequest(`/users/${userId}/details`);
            if (response.success) {
                const user = response.data;
                this.renderUserDetail(user);
                const modal = new bootstrap.Modal(document.getElementById('userDetailModal'));
                modal.show();
            } else {
                this.showError('加载用户详情失败：' + response.error);
            }
        } catch (error) {
            console.error('查看用户详情错误:', error);
            this.showError('网络错误，请稍后重试');
        }
    }
    
    // 渲染用户详情
    renderUserDetail(user) {
        const content = document.getElementById('userDetailContent');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">基本信息</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">用户名:</dt>
                                <dd class="col-sm-8">${user.username}</dd>
                                <dt class="col-sm-4">邮箱:</dt>
                                <dd class="col-sm-8">${user.email}</dd>
                                <dt class="col-sm-4">手机号:</dt>
                                <dd class="col-sm-8">${user.phone || '-'}</dd>
                                <dt class="col-sm-4">角色:</dt>
                                <dd class="col-sm-8"><span class="badge bg-info">${user.role_name}</span></dd>
                                <dt class="col-sm-4">状态:</dt>
                                <dd class="col-sm-8"><span class="badge ${this.getStatusBadgeClass(user.status)}">${this.getStatusText(user.status)}</span></dd>
                                <dt class="col-sm-4">余额:</dt>
                                <dd class="col-sm-8">¥${parseFloat(user.balance || 0).toFixed(2)}</dd>
                                <dt class="col-sm-4">注册时间:</dt>
                                <dd class="col-sm-8">${this.formatDateTime(user.created_at)}</dd>
                                <dt class="col-sm-4">最后登录:</dt>
                                <dd class="col-sm-8">${user.last_login_at ? this.formatDateTime(user.last_login_at) : '从未登录'}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">最近活动</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                ${user.recent_activities ? user.recent_activities.map(activity => `
                                    <div class="timeline-item">
                                        <small class="text-muted">${this.formatDateTime(activity.created_at)}</small>
                                        <div>${activity.description}</div>
                                    </div>
                                `).join('') : '<p class="text-muted">暂无活动记录</p>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // 删除用户
    async deleteUser(userId) {
        if (!confirm('确定要删除此用户吗？此操作不可恢复。')) {
            return;
        }
        
        try {
            const response = await this.apiRequest(`/users/${userId}`, {
                method: 'DELETE'
            });
            
            if (response.success) {
                this.showSuccess('用户删除成功');
                this.loadUsers();
            } else {
                this.showError('删除失败：' + response.error);
            }
        } catch (error) {
            console.error('删除用户错误:', error);
            this.showError('网络错误，请稍后重试');
        }
    }
    
    // 批量操作
    async batchAction(action) {
        if (this.selectedUsers.size === 0) {
            this.showError('请先选择要操作的用户');
            return;
        }
        
        const userIds = Array.from(this.selectedUsers);
        let confirmMessage = '';
        
        switch (action) {
            case 'activate':
                confirmMessage = `确定要激活选中的 ${userIds.length} 个用户吗？`;
                break;
            case 'suspend':
                confirmMessage = `确定要暂停选中的 ${userIds.length} 个用户吗？`;
                break;
            case 'delete':
                confirmMessage = `确定要删除选中的 ${userIds.length} 个用户吗？此操作不可恢复。`;
                break;
            case 'export':
                this.exportSelectedUsers();
                return;
        }
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        try {
            const response = await this.apiRequest('/users/batch', {
                method: 'POST',
                body: JSON.stringify({
                    action: action,
                    user_ids: userIds
                })
            });
            
            if (response.success) {
                this.showSuccess(`批量${action}操作成功`);
                this.selectedUsers.clear();
                this.loadUsers();
            } else {
                this.showError('批量操作失败：' + response.error);
            }
        } catch (error) {
            console.error('批量操作错误:', error);
            this.showError('网络错误，请稍后重试');
        }
    }
    
    // 导出用户
    exportUsers() {
        // 这里实现导出功能
        this.showSuccess('导出功能开发中...');
    }
    
    // 导出选中用户
    exportSelectedUsers() {
        // 这里实现导出选中用户功能
        this.showSuccess('导出选中用户功能开发中...');
    }
    
    // 刷新用户列表
    refreshUsers() {
        this.loadUsers();
    }
}

// 导出到全局
window.UserManagementModule = UserManagementModule;
