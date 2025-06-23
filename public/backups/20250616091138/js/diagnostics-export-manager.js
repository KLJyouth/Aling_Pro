/**
 * AlingAi Pro - 诊断系统导出功能
 * 为系统诊断提供完整的导出和下载功能
 */

class DiagnosticsExportManager {
    constructor() {
        this.baseUrl = '/api/system/diagnostics';
        this.exportFormats = ['json', 'csv', 'html', 'txt'];
        this.isExporting = false;
    }

    /**
     * 获取认证Token
     */
    getAuthToken() {
        // 从全局adminDashboard获取token，或从localStorage获取
        if (window.adminDashboard && typeof window.adminDashboard.getAuthToken === 'function') {
            return window.adminDashboard.getAuthToken();
        }
        return localStorage.getItem('admin_token') || '';
    }

    /**
     * 导出单个格式的报告
     */
    async exportSingleFormat(format) {
        if (this.isExporting) {
            throw new Error('导出正在进行中，请稍候');
        }

        this.isExporting = true;
        try {
            const response = await fetch(`${this.baseUrl}/export?format=${format}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || '导出失败');
            }

            // 触发下载
            if (result.data && result.data.filename) {
                await this.downloadFile(result.data.filename);
                return {
                    success: true,
                    format: format,
                    filename: result.data.filename,
                    size: result.data.size
                };
            }

            throw new Error('服务器未返回文件信息');

        } finally {
            this.isExporting = false;
        }
    }

    /**
     * 批量导出所有格式
     */
    async exportAllFormats() {
        if (this.isExporting) {
            throw new Error('导出正在进行中，请稍候');
        }

        this.isExporting = true;
        const results = {};
        
        try {
            const formatsParam = this.exportFormats.join(',');
            const response = await fetch(`${this.baseUrl}/export?formats=${formatsParam}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || '批量导出失败');
            }

            // 下载所有成功生成的文件
            const exportResults = result.data;
            for (const [format, exportResult] of Object.entries(exportResults)) {
                if (exportResult.success && exportResult.filename) {
                    try {
                        await this.downloadFile(exportResult.filename);
                        results[format] = {
                            success: true,
                            filename: exportResult.filename,
                            size: exportResult.size
                        };
                        // 延迟以避免同时下载过多文件
                        await this.delay(500);
                    } catch (error) {
                        results[format] = {
                            success: false,
                            error: error.message
                        };
                    }
                } else {
                    results[format] = {
                        success: false,
                        error: exportResult.error || '导出失败'
                    };
                }
            }

            return results;

        } finally {
            this.isExporting = false;
        }
    }

    /**
     * 下载文件
     */
    async downloadFile(filename) {
        const response = await fetch(`${this.baseUrl}/download?filename=${encodeURIComponent(filename)}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${this.getAuthToken()}`
            }
        });

        if (!response.ok) {
            throw new Error(`下载失败: HTTP ${response.status}`);
        }

        // 创建下载链接
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.style.display = 'none';
        
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        // 清理URL对象
        setTimeout(() => window.URL.revokeObjectURL(url), 100);
    }

    /**
     * 清理临时文件
     */
    async cleanupTempFiles() {
        const response = await fetch(`${this.baseUrl}/cleanup`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${this.getAuthToken()}`
            }
        });

        if (!response.ok) {
            throw new Error(`清理失败: HTTP ${response.status}`);
        }

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || '清理失败');
        }

        return {
            success: true,
            deletedCount: result.data.deleted_count || 0,
            message: result.message
        };
    }

    /**
     * 创建导出进度指示器
     */
    createProgressIndicator(container, message = '正在导出...') {
        const progressHtml = `
            <div class="export-progress-indicator" id="export-progress-indicator">
                <div class="d-flex align-items-center mb-2">
                    <div class="spinner-border spinner-border-sm me-2 text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="export-progress-text">${message}</span>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="export-progress-bar"></div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('afterbegin', progressHtml);
        return container.querySelector('#export-progress-indicator');
    }

    /**
     * 更新进度指示器
     */
    updateProgress(progressElement, progress, message) {
        if (progressElement) {
            const textElement = progressElement.querySelector('.export-progress-text');
            const barElement = progressElement.querySelector('#export-progress-bar');
            
            if (textElement) textElement.textContent = message;
            if (barElement) barElement.style.width = `${progress}%`;
        }
    }

    /**
     * 移除进度指示器
     */
    removeProgress(progressElement) {
        if (progressElement && progressElement.parentNode) {
            progressElement.parentNode.removeChild(progressElement);
        }
    }

    /**
     * 显示成功消息
     */
    showSuccessMessage(container, message, duration = 3000) {
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // 自动消失
        if (duration > 0) {
            setTimeout(() => {
                const alert = container.querySelector('.alert-success');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, duration);
        }
    }

    /**
     * 显示错误消息
     */
    showErrorMessage(container, message, duration = 5000) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // 自动消失
        if (duration > 0) {
            setTimeout(() => {
                const alert = container.querySelector('.alert-danger');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, duration);
        }
    }

    /**
     * 获取格式化的文件大小
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * 延迟函数
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * 获取格式图标
     */
    getFormatIcon(format) {
        const icons = {
            json: 'bi-filetype-json',
            csv: 'bi-filetype-csv', 
            html: 'bi-filetype-html',
            txt: 'bi-filetype-txt'
        };
        return icons[format] || 'bi-file-earmark';
    }

    /**
     * 获取格式颜色
     */
    getFormatColor(format) {
        const colors = {
            json: '#f59e0b',
            csv: '#10b981',
            html: '#3b82f6',
            txt: '#6b7280'
        };
        return colors[format] || '#6b7280';
    }
}

// 创建全局实例
window.diagnosticsExportManager = new DiagnosticsExportManager();

// 导出到全局作用域以便在HTML中使用
window.DiagnosticsExportManager = DiagnosticsExportManager;

console.log('✅ 诊断系统导出管理器已加载');
