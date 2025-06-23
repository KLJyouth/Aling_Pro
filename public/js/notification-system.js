/**
 * AlingAi 实时通知系统
 * 为集成检测系统提供多种通知方式
 * 创建时间: 2025年5月30日
 */

class NotificationSystem {
    constructor() {
        this.isEnabled = true;
        this.soundEnabled = true;
        this.desktopEnabled = true;
        this.visualEnabled = true;
        this.notificationHistory = [];
        this.maxHistoryRecords = 100;
        
        // 通知类型配置
        this.notificationTypes = {
            success: {
                icon: '✅',
                color: '#28a745',
                sound: 'success',
                priority: 'normal'
            },
            warning: {
                icon: '⚠️',
                color: '#ffc107',
                sound: 'warning',
                priority: 'normal'
            },
            error: {
                icon: '❌',
                color: '#dc3545',
                sound: 'error',
                priority: 'high'
            },
            info: {
                icon: 'ℹ️',
                color: '#17a2b8',
                sound: 'info',
                priority: 'low'
            }
        };
        
        // 声音文件URLs (使用Web Audio API生成)
        this.sounds = {
            success: null,
            warning: null,
            error: null,
            info: null
        };
        
        // 初始化
        this.init();
    }

    /**
     * 初始化通知系统
     */
    async init() {
        // 请求桌面通知权限
        await this.requestPermissions();
        
        // 初始化声音系统
        this.initSounds();
        
        // 创建视觉通知容器
        this.createNotificationContainer();
        
        // 加载用户偏好设置
        this.loadSettings();
        
        
    }

    /**
     * 请求通知权限
     */
    async requestPermissions() {
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                try {
                    const permission = await Notification.requestPermission();
                    this.desktopEnabled = permission === 'granted';
                    
                } catch (error) {
                    console.warn('桌面通知权限请求失败:', error);
                    this.desktopEnabled = false;
                }
            } else {
                this.desktopEnabled = Notification.permission === 'granted';
            }
        } else {
            console.warn('此浏览器不支持桌面通知');
            this.desktopEnabled = false;
        }
    }    /**
     * 初始化声音系统
     */
    initSounds() {
        // 延迟音频初始化，等待用户交互
        this.audioInitialized = false;
        this.pendingAudioInit = true;
        
        // 添加点击监听器来初始化音频
        document.addEventListener('click', () => {
            if (this.pendingAudioInit && !this.audioInitialized) {
                this.initAudioContext();
            }
        }, { once: true });
        
        
    }
    
    /**
     * 实际初始化音频上下文
     */
    initAudioContext() {
        try {
            if ('AudioContext' in window || 'webkitAudioContext' in window) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // 恢复音频上下文（如果被暂停）
                if (this.audioContext.state === 'suspended') {
                    this.audioContext.resume().then(() => {
                        this.generateSounds();
                        this.audioInitialized = true;
                        this.pendingAudioInit = false;
                        
                    });
                } else {
                    this.generateSounds();
                    this.audioInitialized = true;
                    this.pendingAudioInit = false;
                    
                }
            } else {
                console.warn('此浏览器不支持Web Audio API');
                this.soundEnabled = false;
                this.pendingAudioInit = false;
            }
        } catch (error) {
            console.warn('音频初始化失败:', error);
            this.soundEnabled = false;
            this.pendingAudioInit = false;
        }
    }

    /**
     * 生成通知声音
     */
    generateSounds() {
        // 成功音 - 清脆的双音
        this.sounds.success = this.createTone([800, 1000], [0.1, 0.1], 'sine');
        
        // 警告音 - 中频三音
        this.sounds.warning = this.createTone([600, 700, 600], [0.15, 0.15, 0.15], 'triangle');
        
        // 错误音 - 低频长音
        this.sounds.error = this.createTone([300, 250], [0.3, 0.3], 'sawtooth');
        
        // 信息音 - 温和单音
        this.sounds.info = this.createTone([500], [0.2], 'sine');
    }

    /**
     * 创建音调
     */
    createTone(frequencies, durations, waveType = 'sine') {
        const sampleRate = this.audioContext.sampleRate;
        const totalDuration = durations.reduce((sum, dur) => sum + dur, 0);
        const buffer = this.audioContext.createBuffer(1, sampleRate * totalDuration, sampleRate);
        const data = buffer.getChannelData(0);
        
        let currentTime = 0;
        for (let i = 0; i < frequencies.length; i++) {
            const frequency = frequencies[i];
            const duration = durations[i];
            const startSample = Math.floor(currentTime * sampleRate);
            const endSample = Math.floor((currentTime + duration) * sampleRate);
            
            for (let sample = startSample; sample < endSample; sample++) {
                const time = sample / sampleRate;
                const envelope = Math.sin((time - currentTime) / duration * Math.PI); // 包络
                
                switch (waveType) {
                    case 'sine':
                        data[sample] = Math.sin(2 * Math.PI * frequency * time) * envelope * 0.1;
                        break;
                    case 'triangle':
                        data[sample] = (2 / Math.PI) * Math.asin(Math.sin(2 * Math.PI * frequency * time)) * envelope * 0.1;
                        break;
                    case 'sawtooth':
                        data[sample] = (2 * (frequency * time - Math.floor(frequency * time + 0.5))) * envelope * 0.1;
                        break;
                }
            }
            currentTime += duration;
        }
        
        return buffer;
    }    /**
     * 播放声音
     */
    playSound(type) {
        // 检查音频是否可用
        if (!this.soundEnabled || !this.audioInitialized || !this.audioContext || !this.sounds[type]) {
            return;
        }
        
        try {
            // 确保音频上下文处于运行状态
            if (this.audioContext.state === 'suspended') {
                this.audioContext.resume().then(() => {
                    this.playAudioBuffer(type);
                }).catch(error => {
                    console.warn('音频上下文恢复失败:', error);
                });
            } else {
                this.playAudioBuffer(type);
            }
        } catch (error) {
            console.warn('播放通知声音失败:', error);
        }
    }
    
    /**
     * 播放音频缓冲区
     */
    playAudioBuffer(type) {
        try {
            const source = this.audioContext.createBufferSource();
            source.buffer = this.sounds[type];
            source.connect(this.audioContext.destination);
            source.start();
        } catch (error) {
            console.warn('音频播放错误:', error);
        }
    }

    /**
     * 创建视觉通知容器
     */
    createNotificationContainer() {
        // 检查是否已存在
        if (document.getElementById('notification-container')) return;
        
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 350px;
            pointer-events: none;
        `;
        
        document.body.appendChild(container);

        // 添加CSS样式
        const style = document.createElement('style');
        style.textContent = `
            .notification-toast {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                margin-bottom: 10px;
                padding: 16px;
                border-left: 4px solid;
                backdrop-filter: blur(10px);
                animation: slideInRight 0.3s ease-out;
                pointer-events: auto;
                position: relative;
                overflow: hidden;
            }
            
            .notification-toast::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, currentColor, transparent);
                animation: shimmer 2s infinite;
            }
            
            .notification-header {
                display: flex;
                align-items: center;
                margin-bottom: 8px;
                font-weight: 600;
            }
            
            .notification-icon {
                font-size: 18px;
                margin-right: 8px;
            }
            
            .notification-title {
                flex: 1;
                font-size: 14px;
            }
            
            .notification-time {
                font-size: 11px;
                color: #666;
                margin-left: 8px;
            }
            
            .notification-body {
                font-size: 13px;
                color: #555;
                line-height: 1.4;
            }
            
            .notification-close {
                position: absolute;
                top: 8px;
                right: 8px;
                background: none;
                border: none;
                font-size: 16px;
                cursor: pointer;
                opacity: 0.5;
                transition: opacity 0.2s;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @keyframes shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            
            .notification-success { border-left-color: #28a745; color: #28a745; }
            .notification-warning { border-left-color: #ffc107; color: #ffc107; }
            .notification-error { border-left-color: #dc3545; color: #dc3545; }
            .notification-info { border-left-color: #17a2b8; color: #17a2b8; }
        `;
        document.head.appendChild(style);
    }

    /**
     * 发送通知
     */
    notify(title, message, type = 'info', options = {}) {
        if (!this.isEnabled) return;

        const notification = {
            id: this.generateId(),
            title,
            message,
            type,
            timestamp: Date.now(),
            options: {
                persistent: false,
                autoClose: true,
                duration: 5000,
                showDesktop: true,
                playSound: true,
                ...options
            }
        };

        // 添加到历史记录
        this.addToHistory(notification);

        // 显示桌面通知
        if (notification.options.showDesktop && this.desktopEnabled) {
            this.showDesktopNotification(notification);
        }

        // 显示视觉通知
        if (this.visualEnabled) {
            this.showVisualNotification(notification);
        }

        // 播放声音
        if (notification.options.playSound && this.soundEnabled) {
            this.playSound(type);
        }

        // 触发自定义事件
        this.dispatchNotificationEvent(notification);

        return notification.id;
    }

    /**
     * 显示桌面通知
     */
    showDesktopNotification(notification) {
        if (!this.desktopEnabled) return;

        const config = this.notificationTypes[notification.type];
        
        try {
            const desktopNotification = new Notification(notification.title, {
                body: notification.message,
                icon: `/favicon.ico`, // 使用项目图标
                badge: `/favicon.ico`,
                tag: notification.id,
                requireInteraction: notification.options.persistent,
                silent: !notification.options.playSound
            });

            // 自动关闭
            if (notification.options.autoClose && !notification.options.persistent) {
                setTimeout(() => {
                    desktopNotification.close();
                }, notification.options.duration);
            }

            // 点击处理
            desktopNotification.onclick = () => {
                window.focus();
                desktopNotification.close();
                this.handleNotificationClick(notification);
            };

        } catch (error) {
            console.warn('显示桌面通知失败:', error);
        }
    }

    /**
     * 显示视觉通知
     */
    showVisualNotification(notification) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const config = this.notificationTypes[notification.type];
        
        const toast = document.createElement('div');
        toast.className = `notification-toast notification-${notification.type}`;
        toast.id = `toast-${notification.id}`;
        
        toast.innerHTML = `
            <div class="notification-header">
                <span class="notification-icon">${config.icon}</span>
                <span class="notification-title">${notification.title}</span>
                <span class="notification-time">${this.formatTime(notification.timestamp)}</span>
            </div>
            <div class="notification-body">${notification.message}</div>
            <button class="notification-close" onclick="notificationSystem.closeToast('${notification.id}')">&times;</button>
        `;

        container.appendChild(toast);

        // 自动移除
        if (notification.options.autoClose) {
            setTimeout(() => {
                this.closeToast(notification.id);
            }, notification.options.duration);
        }

        // 添加点击事件
        toast.addEventListener('click', (e) => {
            if (e.target.className !== 'notification-close') {
                this.handleNotificationClick(notification);
            }
        });
    }

    /**
     * 关闭Toast通知
     */
    closeToast(notificationId) {
        const toast = document.getElementById(`toast-${notificationId}`);
        if (!toast) return;

        toast.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    /**
     * 处理通知点击
     */
    handleNotificationClick(notification) {
        // 触发自定义事件
        const event = new CustomEvent('notificationClick', { detail: notification });
        document.dispatchEvent(event);
        
        // 如果有回调函数
        if (notification.options.onClick) {
            notification.options.onClick(notification);
        }
    }

    /**
     * 触发通知事件
     */
    dispatchNotificationEvent(notification) {
        const event = new CustomEvent('notification', { detail: notification });
        document.dispatchEvent(event);
    }

    /**
     * 添加到历史记录
     */
    addToHistory(notification) {
        this.notificationHistory.unshift(notification);
        
        // 限制历史记录数量
        if (this.notificationHistory.length > this.maxHistoryRecords) {
            this.notificationHistory = this.notificationHistory.slice(0, this.maxHistoryRecords);
        }

        // 保存到本地存储
        this.saveToStorage();
    }

    /**
     * 获取通知历史
     */
    getHistory(type = null, limit = null) {
        let history = this.notificationHistory;
        
        if (type) {
            history = history.filter(n => n.type === type);
        }
        
        if (limit) {
            history = history.slice(0, limit);
        }
        
        return history;
    }

    /**
     * 清空历史记录
     */
    clearHistory() {
        this.notificationHistory = [];
        this.saveToStorage();
    }

    /**
     * 显示通知历史模态框
     */
    showHistoryModal() {
        const modalHtml = `
            <div class="modal fade" id="notificationHistoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-bell"></i> 通知历史记录
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <small class="text-muted">共 ${this.notificationHistory.length} 条通知记录</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="notificationSystem.clearHistory(); notificationSystem.showHistoryModal();">
                                        <i class="bi bi-trash"></i> 清空记录
                                    </button>
                                </div>
                            </div>
                            <div class="notification-history-list" style="max-height: 400px; overflow-y: auto;">
                                ${this.renderHistoryList()}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                            <button type="button" class="btn btn-primary" onclick="notificationSystem.exportHistory()">
                                <i class="bi bi-download"></i> 导出记录
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // 移除现有模态框
        const existingModal = document.getElementById('notificationHistoryModal');
        if (existingModal) {
            existingModal.remove();
        }

        // 添加新模态框
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // 显示模态框
        const modal = new bootstrap.Modal(document.getElementById('notificationHistoryModal'));
        modal.show();
    }

    /**
     * 渲染历史记录列表
     */
    renderHistoryList() {
        if (this.notificationHistory.length === 0) {
            return '<div class="text-center text-muted py-4">暂无通知记录</div>';
        }

        return this.notificationHistory.map(notification => {
            const config = this.notificationTypes[notification.type];
            const timeStr = new Date(notification.timestamp).toLocaleString();
            
            return `
                <div class="notification-history-item p-3 border-bottom">
                    <div class="d-flex align-items-start">
                        <span class="notification-icon me-2" style="color: ${config.color}">
                            ${config.icon}
                        </span>
                        <div class="flex-grow-1">
                            <div class="fw-bold mb-1">${notification.title}</div>
                            <div class="text-muted small mb-1">${notification.message}</div>
                            <div class="text-muted" style="font-size: 11px;">
                                ${timeStr}
                            </div>
                        </div>
                        <span class="badge bg-${this.getBootstrapColor(notification.type)} ms-2">
                            ${notification.type.toUpperCase()}
                        </span>
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * 获取Bootstrap颜色类
     */
    getBootstrapColor(type) {
        const colorMap = {
            success: 'success',
            warning: 'warning', 
            error: 'danger',
            info: 'info'
        };
        return colorMap[type] || 'secondary';
    }

    /**
     * 导出通知历史
     */
    exportHistory() {
        const data = {
            exportTime: new Date().toISOString(),
            totalNotifications: this.notificationHistory.length,
            notifications: this.notificationHistory
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `notification-history-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        this.notify('导出完成', '通知历史记录已导出到下载文件夹', 'success');
    }

    /**
     * 设置偏好
     */
    setPreferences(preferences) {
        Object.assign(this, preferences);
        this.saveToStorage();
        this.notify('设置已更新', '通知偏好设置已保存', 'info');
    }

    /**
     * 格式化时间
     */
    formatTime(timestamp) {
        const now = Date.now();
        const diff = now - timestamp;
        
        if (diff < 60000) return '刚刚';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}分钟前`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}小时前`;
        return new Date(timestamp).toLocaleDateString();
    }

    /**
     * 生成唯一ID
     */
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    /**
     * 保存到本地存储
     */
    saveToStorage() {
        try {
            const data = {
                isEnabled: this.isEnabled,
                soundEnabled: this.soundEnabled,
                desktopEnabled: this.desktopEnabled,
                visualEnabled: this.visualEnabled,
                history: this.notificationHistory.slice(0, this.maxHistoryRecords)
            };
            localStorage.setItem('aidNotificationSystem', JSON.stringify(data));
        } catch (error) {
            console.warn('保存通知系统设置失败:', error);
        }
    }

    /**
     * 从本地存储加载设置
     */
    loadSettings() {
        try {
            const data = JSON.parse(localStorage.getItem('aidNotificationSystem') || '{}');
            
            this.isEnabled = data.isEnabled !== false;
            this.soundEnabled = data.soundEnabled !== false;
            this.visualEnabled = data.visualEnabled !== false;
            this.notificationHistory = data.history || [];
            
            // 桌面通知权限需要重新检查
            this.desktopEnabled = this.desktopEnabled && (data.desktopEnabled !== false);
            
        } catch (error) {
            console.warn('加载通知系统设置失败:', error);
        }
    }

    /**
     * 获取系统状态
     */
    getStatus() {
        return {
            isEnabled: this.isEnabled,
            soundEnabled: this.soundEnabled,
            desktopEnabled: this.desktopEnabled,
            visualEnabled: this.visualEnabled,
            historyCount: this.notificationHistory.length,
            permissionStatus: Notification.permission
        };
    }
}

// 创建全局实例
window.notificationSystem = new NotificationSystem();

// 导出类
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSystem;
}
