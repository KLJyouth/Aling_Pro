/**
 * 配置管理系统
 * 提供系统配置的完整管理功能
 * 
 * @version 5.0
 * @since 2024-12-19
 */
class ConfigurationManager {
    constructor() {
        this.apiBase = '/api/admin/configuration';
        this.currentCategory = '';
        this.currentView = 'card';
        this.configurations = {};
        this.searchTimeout = null;
        
        // 绑定事件
        this.bindEvents();
    }

    /**
     * 初始化
     */
    async init() {
        try {
            await this.loadConfigurations();
            this.setupCategoryNavigation();
            this.displayConfigurations();
            
            console.log('✅ 配置管理系统初始化完成');
        } catch (error) {
            console.error('❌ 配置管理系统初始化失败:', error);
            this.showError('系统初始化失败');
        }
    }

    /**
     * 绑定事件
     */
    bindEvents() {
        // 搜索框
        document.addEventListener('input', (e) => {
            if (e.target.id === 'searchInput') {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.searchConfigurations(e.target.value);
                }, 300);
            }
        });

        // 分类切换类型检测
        document.addEventListener('change', (e) => {
            if (e.target.id === 'configType') {
                this.updateValueHint(e.target.value);
            }
        });

        // 表单验证
        document.addEventListener('input', (e) => {
            if (e.target.id === 'configKey') {
                this.validateConfigKey(e.target.value);
            } else if (e.target.id === 'configValue') {
                this.validateConfigValue(e.target.value);
            }
        });
    }

    /**
     * 加载所有配置
     */
    async loadConfigurations(category = '') {
        try {
            this.showLoading();
            
            const url = category ? 
                `${this.apiBase}/category/${category}` : 
                `${this.apiBase}/all`;
                
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                this.configurations = result.data;
                this.updateStatistics();
            } else {
                throw new Error(result.message || '加载配置失败');
            }
            
        } catch (error) {
            console.error('加载配置失败:', error);
            this.showError('加载配置失败: ' + error.message);
        }
    }

    /**
     * 显示配置列表
     */
    displayConfigurations() {
        const container = document.getElementById('configContainer');
        
        if (!this.configurations || Object.keys(this.configurations).length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">暂无配置数据</h5>
                    <p class="text-muted">点击"新增配置"添加第一个配置项</p>
                </div>
            `;
            return;
        }

        if (this.currentView === 'table') {
            this.displayTableView();
        } else {
            this.displayCardView();
        }
    }

    /**
     * 卡片视图
     */
    displayCardView() {
        const container = document.getElementById('configContainer');
        let html = '';

        // 按分类组织配置
        const categorizedConfigs = this.categorizeCongfigurations();

        Object.entries(categorizedConfigs).forEach(([category, configs]) => {
            if (this.currentCategory && this.currentCategory !== category) {
                return;
            }

            html += `
                <div class="config-category-section mb-4">
                    <h4 class="category-title mb-3">
                        <i class="bi bi-${this.getCategoryIcon(category)} me-2"></i>
                        ${this.getCategoryName(category)}
                        <span class="badge bg-secondary ms-2">${Object.keys(configs).length}</span>
                    </h4>
                    <div class="row">
            `;

            Object.entries(configs).forEach(([key, config]) => {
                html += this.renderConfigCard(key, config, category);
            });

            html += `
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    /**
     * 表格视图
     */
    displayTableView() {
        const container = document.getElementById('configContainer');
        
        let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="20%">配置键</th>
                            <th width="15%">类型</th>
                            <th width="25%">值</th>
                            <th width="20%">描述</th>
                            <th width="10%">分类</th>
                            <th width="10%">操作</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        // 扁平化配置
        const flatConfigs = this.flattenConfigurations();

        flatConfigs.forEach(({ key, config, category }) => {
            if (this.currentCategory && this.currentCategory !== category) {
                return;
            }

            html += `
                <tr>
                    <td>
                        <code class="config-key">${key}</code>
                    </td>
                    <td>
                        <span class="config-type badge bg-secondary">${config.type}</span>
                    </td>
                    <td>
                        <div class="config-value-preview" title="${this.escapeHtml(config.value)}">
                            ${this.formatValue(config.value, config.type, 50)}
                        </div>
                    </td>
                    <td>
                        <small class="text-muted">${config.description || '-'}</small>
                    </td>
                    <td>
                        <span class="badge bg-info">${this.getCategoryName(category)}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="configManager.editConfig('${key}', '${category}')" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="configManager.deleteConfig('${key}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = html;
    }

    /**
     * 渲染配置卡片
     */
    renderConfigCard(key, config, category) {
        const isPassword = config.type === 'password' || key.toLowerCase().includes('password');
        const displayValue = isPassword ? '••••••••' : this.formatValue(config.value, config.type);

        return `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="config-item">
                    <div class="config-item-header">
                        <div>
                            <div class="config-key">${key}</div>
                            <span class="config-type">${config.type}</span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="configManager.editConfig('${key}', '${category}')">
                                    <i class="bi bi-pencil me-2"></i>编辑
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="configManager.duplicateConfig('${key}', '${category}')">
                                    <i class="bi bi-files me-2"></i>复制
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="configManager.viewHistory('${key}')">
                                    <i class="bi bi-clock-history me-2"></i>历史
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="configManager.deleteConfig('${key}')">
                                    <i class="bi bi-trash me-2"></i>删除
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="config-value">${displayValue}</div>
                    
                    ${config.description ? `<div class="config-description">${config.description}</div>` : ''}
                    
                    <div class="config-meta mt-2">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            ${config.updated_at ? new Date(config.updated_at).toLocaleString() : '未知'}
                        </small>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * 设置分类导航
     */
    setupCategoryNavigation() {
        const navLinks = document.querySelectorAll('.config-nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // 更新激活状态
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                // 切换分类
                const category = link.dataset.category;
                this.switchCategory(category);
            });
        });
    }

    /**
     * 切换分类
     */
    async switchCategory(category) {
        try {
            this.currentCategory = category;
            await this.loadConfigurations(category);
            this.displayConfigurations();
        } catch (error) {
            console.error('切换分类失败:', error);
            this.showError('切换分类失败');
        }
    }

    /**
     * 搜索配置
     */
    async searchConfigurations(query) {
        try {
            if (!query.trim()) {
                this.displayConfigurations();
                return;
            }

            const response = await fetch(`${this.apiBase}/search?q=${encodeURIComponent(query)}&category=${this.currentCategory}`);
            const result = await response.json();

            if (result.success) {
                this.displaySearchResults(result.data.results, query);
            } else {
                throw new Error(result.message || '搜索失败');
            }

        } catch (error) {
            console.error('搜索配置失败:', error);
            this.showError('搜索失败');
        }
    }

    /**
     * 显示搜索结果
     */
    displaySearchResults(results, query) {
        const container = document.getElementById('configContainer');

        if (results.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">未找到匹配的配置</h5>
                    <p class="text-muted">搜索关键词: "${query}"</p>
                </div>
            `;
            return;
        }

        let html = `
            <div class="search-results">
                <div class="alert alert-info">
                    <i class="bi bi-search me-2"></i>
                    找到 <strong>${results.length}</strong> 个匹配的配置项
                </div>
                <div class="row">
        `;

        results.forEach(config => {
            html += this.renderConfigCard(config.key, config, config.category);
        });

        html += `
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    /**
     * 显示新增配置模态框
     */
    showAddModal() {
        document.getElementById('configModalTitle').textContent = '新增配置';
        document.getElementById('configForm').reset();
        document.getElementById('configKey').readOnly = false;
        
        // 设置默认值
        document.getElementById('configType').value = 'string';
        document.getElementById('configCategory').value = this.currentCategory || 'system';
        
        this.updateValueHint('string');
        
        new bootstrap.Modal(document.getElementById('configModal')).show();
    }

    /**
     * 编辑配置
     */
    async editConfig(key, category) {
        try {
            // 获取配置详情
            const config = this.getConfigFromStore(key, category);
            
            if (!config) {
                throw new Error('配置不存在');
            }

            // 填充表单
            document.getElementById('configModalTitle').textContent = '编辑配置';
            document.getElementById('configKey').value = key;
            document.getElementById('configKey').readOnly = true;
            document.getElementById('configType').value = config.type;
            document.getElementById('configCategory').value = category;
            document.getElementById('configValue').value = config.value;
            document.getElementById('configDescription').value = config.description || '';
            
            this.updateValueHint(config.type);
            
            new bootstrap.Modal(document.getElementById('configModal')).show();

        } catch (error) {
            console.error('编辑配置失败:', error);
            this.showError('编辑配置失败');
        }
    }

    /**
     * 保存配置
     */
    async saveConfig() {
        try {
            const formData = this.getFormData();
            
            // 验证表单
            if (!this.validateForm(formData)) {
                return;
            }

            // 发送请求
            const response = await fetch(`${this.apiBase}/set`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('配置保存成功');
                bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
                await this.refreshAll();
            } else {
                throw new Error(result.message || '保存失败');
            }

        } catch (error) {
            console.error('保存配置失败:', error);
            this.showError('保存配置失败: ' + error.message);
        }
    }

    /**
     * 删除配置
     */
    async deleteConfig(key) {
        if (!confirm(`确定要删除配置 "${key}" 吗？此操作不可撤销。`)) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/delete/${key}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('配置删除成功');
                await this.refreshAll();
            } else {
                throw new Error(result.message || '删除失败');
            }

        } catch (error) {
            console.error('删除配置失败:', error);
            this.showError('删除配置失败: ' + error.message);
        }
    }

    /**
     * 显示导出模态框
     */
    showExportModal() {
        new bootstrap.Modal(document.getElementById('exportModal')).show();
    }

    /**
     * 导出配置
     */
    async exportConfigs() {
        try {
            const category = document.getElementById('exportCategory').value;
            const format = document.querySelector('input[name="exportFormat"]:checked').value;

            const params = new URLSearchParams();
            if (category) params.set('category', category);
            params.set('format', format);

            const response = await fetch(`${this.apiBase}/export?${params}`);
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `config_export_${Date.now()}.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
                this.showSuccess('配置导出成功');
            } else {
                throw new Error('导出失败');
            }

        } catch (error) {
            console.error('导出配置失败:', error);
            this.showError('导出配置失败');
        }
    }

    /**
     * 显示导入模态框
     */
    showImportModal() {
        document.getElementById('importData').value = '';
        new bootstrap.Modal(document.getElementById('importModal')).show();
    }

    /**
     * 导入配置
     */
    async importConfigs() {
        try {
            const content = document.getElementById('importData').value.trim();
            const format = document.getElementById('importFormat').value;
            const overwrite = document.getElementById('overwriteExisting').checked;

            if (!content) {
                this.showError('请输入配置数据');
                return;
            }

            const response = await fetch(`${this.apiBase}/import`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    content,
                    format,
                    overwrite
                })
            });

            const result = await response.json();

            if (result.success) {
                const summary = result.data.summary;
                const total = Object.values(summary).reduce((a, b) => a + b, 0);
                
                this.showSuccess(`配置导入完成：共 ${total} 项，成功 ${summary.imported || 0} 项`);
                bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();
                await this.refreshAll();
            } else {
                throw new Error(result.message || '导入失败');
            }

        } catch (error) {
            console.error('导入配置失败:', error);
            this.showError('导入配置失败: ' + error.message);
        }
    }

    /**
     * 显示历史模态框
     */
    async showHistoryModal() {
        const modal = new bootstrap.Modal(document.getElementById('historyModal'));
        modal.show();
        
        await this.loadHistory();
    }

    /**
     * 加载配置历史
     */
    async loadHistory(key = null) {
        try {
            const container = document.getElementById('historyContainer');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

            const params = new URLSearchParams();
            if (key) params.set('key', key);
            params.set('limit', '100');

            const response = await fetch(`${this.apiBase}/history?${params}`);
            const result = await response.json();

            if (result.success) {
                this.displayHistory(result.data.history);
            } else {
                throw new Error(result.message || '加载历史失败');
            }

        } catch (error) {
            console.error('加载历史失败:', error);
            document.getElementById('historyContainer').innerHTML = 
                '<div class="alert alert-danger">加载历史记录失败</div>';
        }
    }

    /**
     * 显示历史记录
     */
    displayHistory(history) {
        const container = document.getElementById('historyContainer');

        if (history.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-clock-history display-4 text-muted"></i>
                    <p class="mt-3 text-muted">暂无历史记录</p>
                </div>
            `;
            return;
        }

        let html = '';
        history.forEach(record => {
            html += `
                <div class="history-item">
                    <div class="history-meta">
                        <strong>${record.setting_key}</strong> - 
                        ${new Date(record.changed_at).toLocaleString()} - 
                        ${record.changed_by || 'system'}
                    </div>
                    <div class="history-changes">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">旧值:</small>
                                <div class="bg-light p-2 rounded">
                                    <code>${this.formatValue(record.old_value, record.setting_type)}</code>
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">新值:</small>
                                <div class="bg-light p-2 rounded">
                                    <code>${this.formatValue(record.new_value, record.setting_type)}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="history-actions mt-2">
                        <button class="btn btn-sm btn-outline-warning" 
                                onclick="configManager.rollbackConfig('${record.setting_key}', '${record.version}')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>回滚
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    /**
     * 回滚配置
     */
    async rollbackConfig(key, version) {
        if (!confirm(`确定要将配置 "${key}" 回滚到版本 "${version}" 吗？`)) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ key, version })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('配置回滚成功');
                await this.refreshAll();
                await this.loadHistory();
            } else {
                throw new Error(result.message || '回滚失败');
            }

        } catch (error) {
            console.error('回滚配置失败:', error);
            this.showError('回滚配置失败: ' + error.message);
        }
    }

    /**
     * 清除缓存
     */
    async clearCache() {
        if (!confirm('确定要清除所有配置缓存吗？')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/cache/clear`, {
                method: 'POST'
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('配置缓存已清除');
            } else {
                throw new Error(result.message || '清除缓存失败');
            }

        } catch (error) {
            console.error('清除缓存失败:', error);
            this.showError('清除缓存失败');
        }
    }

    /**
     * 切换视图模式
     */
    toggleView(view) {
        this.currentView = view;
        this.displayConfigurations();
        
        // 更新按钮状态
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    /**
     * 刷新所有数据
     */
    async refreshAll() {
        await this.loadConfigurations(this.currentCategory);
        this.displayConfigurations();
        await this.updateStatistics();
    }

    /**
     * 更新统计信息
     */
    async updateStatistics() {
        try {
            const response = await fetch(`${this.apiBase}/statistics`);
            const result = await response.json();

            if (result.success) {
                const stats = result.data;
                document.getElementById('totalConfigs').textContent = stats.total_settings;
                document.getElementById('categoryCount').textContent = Object.keys(stats.by_category).length;
                document.getElementById('recentChanges').textContent = stats.recent_changes;
            }

        } catch (error) {
            console.error('更新统计信息失败:', error);
        }
    }

    // ========== 工具方法 ==========

    /**
     * 获取表单数据
     */
    getFormData() {
        return {
            key: document.getElementById('configKey').value.trim(),
            value: document.getElementById('configValue').value,
            type: document.getElementById('configType').value,
            category: document.getElementById('configCategory').value,
            description: document.getElementById('configDescription').value.trim()
        };
    }

    /**
     * 验证表单
     */
    validateForm(data) {
        const errors = [];

        // 验证配置键
        if (!data.key) {
            errors.push('配置键不能为空');
        } else if (!/^[a-zA-Z][a-zA-Z0-9_\.]*$/.test(data.key)) {
            errors.push('配置键格式无效');
        }

        // 验证值
        if (!data.value && data.value !== '0' && data.value !== 'false') {
            errors.push('配置值不能为空');
        }

        // 根据类型验证值
        switch (data.type) {
            case 'integer':
                if (!Number.isInteger(Number(data.value))) {
                    errors.push('值必须是整数');
                }
                break;
            case 'float':
                if (isNaN(Number(data.value))) {
                    errors.push('值必须是数字');
                }
                break;
            case 'boolean':
                if (!['true', 'false', '1', '0', 'yes', 'no'].includes(data.value.toLowerCase())) {
                    errors.push('布尔值必须是 true/false、1/0 或 yes/no');
                }
                break;
            case 'json':
                try {
                    JSON.parse(data.value);
                } catch (e) {
                    errors.push('JSON 格式无效');
                }
                break;
            case 'email':
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.value)) {
                    errors.push('邮箱格式无效');
                }
                break;
            case 'url':
                try {
                    new URL(data.value);
                } catch (e) {
                    errors.push('URL 格式无效');
                }
                break;
        }

        if (errors.length > 0) {
            this.showError(errors.join('<br>'));
            return false;
        }

        return true;
    }

    /**
     * 更新值提示
     */
    updateValueHint(type) {
        const hints = {
            'string': '输入文本字符串',
            'integer': '输入整数，如: 123',
            'float': '输入数字，如: 123.45',
            'boolean': '输入 true/false、1/0 或 yes/no',
            'json': '输入有效的 JSON 格式，如: {"key": "value"}',
            'datetime': '输入日期时间，如: 2024-01-01 12:00:00',
            'email': '输入邮箱地址，如: user@example.com',
            'url': '输入完整的 URL，如: https://example.com',
            'password': '输入密码（显示时会被隐藏）'
        };

        document.getElementById('valueHint').textContent = hints[type] || '请输入配置值';
    }

    /**
     * 验证配置键
     */
    validateConfigKey(key) {
        const input = document.getElementById('configKey');
        
        if (!/^[a-zA-Z][a-zA-Z0-9_\.]*$/.test(key)) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    }

    /**
     * 验证配置值
     */
    validateConfigValue(value) {
        const type = document.getElementById('configType').value;
        const input = document.getElementById('configValue');
        
        let valid = true;
        
        switch (type) {
            case 'json':
                try {
                    JSON.parse(value);
                } catch (e) {
                    valid = false;
                }
                break;
                
            case 'email':
                valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                break;
                
            case 'url':
                try {
                    new URL(value);
                } catch (e) {
                    valid = false;
                }
                break;
        }

        if (valid) {
            input.classList.remove('is-invalid');
        } else {
            input.classList.add('is-invalid');
        }
    }

    /**
     * 格式化值显示
     */
    formatValue(value, type, maxLength = 100) {
        if (value === null || value === undefined) {
            return '<span class="text-muted">NULL</span>';
        }

        let formatted = String(value);

        switch (type) {
            case 'boolean':
                return value ? '<span class="text-success">true</span>' : '<span class="text-danger">false</span>';
            
            case 'json':
                try {
                    formatted = JSON.stringify(JSON.parse(value), null, 2);
                } catch (e) {
                    // 保持原值
                }
                break;
                
            case 'password':
                return '<span class="text-muted">••••••••</span>';
        }

        if (formatted.length > maxLength) {
            formatted = formatted.substring(0, maxLength) + '...';
        }

        return this.escapeHtml(formatted);
    }

    /**
     * 分类配置
     */
    categorizeConfigurations() {
        const categorized = {};

        Object.entries(this.configurations).forEach(([category, configs]) => {
            categorized[category] = configs;
        });

        return categorized;
    }

    /**
     * 扁平化配置
     */
    flattenConfigurations() {
        const flattened = [];

        Object.entries(this.configurations).forEach(([category, configs]) => {
            Object.entries(configs).forEach(([key, config]) => {
                flattened.push({ key, config, category });
            });
        });

        return flattened.sort((a, b) => a.key.localeCompare(b.key));
    }

    /**
     * 从存储中获取配置
     */
    getConfigFromStore(key, category) {
        return this.configurations[category] && this.configurations[category][key];
    }

    /**
     * 获取分类图标
     */
    getCategoryIcon(category) {
        const icons = {
            'system': 'cpu',
            'security': 'shield-lock',
            'database': 'database',
            'cache': 'lightning',
            'email': 'envelope',
            'ai': 'robot',
            'api': 'code-slash',
            'ui': 'palette',
            'performance': 'speedometer2',
            'backup': 'archive',
            'monitoring': 'graph-up',
            'logging': 'journal-text'
        };
        return icons[category] || 'gear';
    }

    /**
     * 获取分类名称
     */
    getCategoryName(category) {
        const names = {
            'system': '系统配置',
            'security': '安全配置',
            'database': '数据库配置',
            'cache': '缓存配置',
            'email': '邮件配置',
            'ai': 'AI服务配置',
            'api': 'API配置',
            'ui': '界面配置',
            'performance': '性能配置',
            'backup': '备份配置',
            'monitoring': '监控配置',
            'logging': '日志配置'
        };
        return names[category] || category;
    }

    /**
     * HTML转义
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * 显示加载中
     */
    showLoading() {
        document.getElementById('configContainer').innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
            </div>
        `;
    }

    /**
     * 显示成功消息
     */
    showSuccess(message) {
        this.showAlert(message, 'success');
    }

    /**
     * 显示错误消息
     */
    showError(message) {
        this.showAlert(message, 'danger');
    }

    /**
     * 显示警告消息
     */
    showAlert(message, type = 'info') {
        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // 插入到页面顶部
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        // 5秒后自动移除
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}
