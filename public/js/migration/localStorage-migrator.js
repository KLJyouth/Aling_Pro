/**
 * LocalStorage数据迁移工具
 * 将浏览器localStorage中的用户数据迁移到服务器数据库
 */
class LocalStorageMigrator {
    constructor() {
        this.apiBase = '/api/user-settings';
        this.migrationKey = 'localStorage_migrated';
        this.backupKey = 'localStorage_backup';
    }

    /**
     * 检查是否需要迁移
     */
    async needsMigration() {
        // 检查是否已经迁移过
        if (localStorage.getItem(this.migrationKey)) {
            return false;
        }

        // 检查是否有需要迁移的数据
        const migrateableData = this.getMigrateableData();
        return Object.keys(migrateableData).length > 0;
    }

    /**
     * 执行迁移
     */
    async migrate() {
        try {
            
            
            // 1. 备份当前localStorage数据
            this.backupLocalStorage();
            
            // 2. 获取需要迁移的数据
            const migrateableData = this.getMigrateableData();
            
            if (Object.keys(migrateableData).length === 0) {
                
                this.markMigrationComplete();
                return { success: true, message: '没有需要迁移的数据' };
            }

            // 3. 发送数据到服务器
            const response = await this.sendDataToServer(migrateableData);
            
            if (response.success) {
                // 4. 迁移成功，标记完成
                this.markMigrationComplete();
                
                // 5. 可选：清除已迁移的localStorage数据
                if (await this.confirmDataCleanup()) {
                    this.cleanupMigratedData();
                }
                
                
                return {
                    success: true,
                    message: '数据迁移成功',
                    importedCount: response.data.imported_count
                };
            } else {
                throw new Error(response.error || '迁移失败');
            }

        } catch (error) {
            console.error('localStorage迁移失败:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * 获取可迁移的数据
     */
    getMigrateableData() {
        const migrateableKeys = [
            // 认证相关
            'token', 'guestMode',
            
            // 聊天设置
            'chatSettings', 'currentUser', 'currentSessionId', 'chatHistory',
            'voiceInput', 'autoTTS',
            
            // 主题系统
            'theme', 'darkMode', 'customThemes',
            
            // 辅助功能
            'accessibility-settings', 'fontSize', 'highContrast',
            
            // 检测系统
            'detectionHistory', 'performanceBaseline', 'detectionSettings',
            
            // 其他设置
            'language', 'timezone'
        ];

        const data = {};
        
        migrateableKeys.forEach(key => {
            const value = localStorage.getItem(key);
            if (value !== null) {
                try {
                    // 尝试解析JSON，如果失败则使用原始字符串
                    data[key] = JSON.parse(value);
                } catch {
                    data[key] = value;
                }
            }
        });

        // 检查以特定前缀开头的键
        const prefixes = ['state_', 'chat_', 'theme_', 'detection_'];
        
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (prefixes.some(prefix => key.startsWith(prefix))) {
                const value = localStorage.getItem(key);
                if (value !== null) {
                    try {
                        data[key] = JSON.parse(value);
                    } catch {
                        data[key] = value;
                    }
                }
            }
        }

        return data;
    }

    /**
     * 发送数据到服务器
     */
    async sendDataToServer(data) {
        const response = await fetch(`${this.apiBase}/import-localstorage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.getAuthToken()}`
            },
            body: JSON.stringify({
                localStorage: data
            })
        });

        return await response.json();
    }

    /**
     * 备份localStorage数据
     */
    backupLocalStorage() {
        const backup = {};
        
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            backup[key] = localStorage.getItem(key);
        }

        localStorage.setItem(this.backupKey, JSON.stringify({
            timestamp: new Date().toISOString(),
            data: backup
        }));

        
    }

    /**
     * 恢复localStorage数据
     */
    restoreFromBackup() {
        const backupData = localStorage.getItem(this.backupKey);
        
        if (!backupData) {
            throw new Error('没有找到备份数据');
        }

        try {
            const backup = JSON.parse(backupData);
            
            // 清除当前数据（除了备份和迁移标记）
            const preserveKeys = [this.backupKey, this.migrationKey];
            const keysToRemove = [];
            
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (!preserveKeys.includes(key)) {
                    keysToRemove.push(key);
                }
            }
            
            keysToRemove.forEach(key => localStorage.removeItem(key));
            
            // 恢复备份数据
            Object.entries(backup.data).forEach(([key, value]) => {
                if (!preserveKeys.includes(key)) {
                    localStorage.setItem(key, value);
                }
            });
            
            
            return true;
            
        } catch (error) {
            console.error('恢复备份失败:', error);
            return false;
        }
    }

    /**
     * 清理已迁移的数据
     */
    cleanupMigratedData() {
        const migrateableData = this.getMigrateableData();
        
        Object.keys(migrateableData).forEach(key => {
            localStorage.removeItem(key);
        });

        
    }

    /**
     * 标记迁移完成
     */
    markMigrationComplete() {
        localStorage.setItem(this.migrationKey, JSON.stringify({
            timestamp: new Date().toISOString(),
            version: '1.0'
        }));
    }

    /**
     * 确认是否清理数据
     */
    async confirmDataCleanup() {
        // 在生产环境中可能需要用户确认
        if (window.confirm('迁移成功！是否清除浏览器中的旧数据？（推荐清除以避免冲突）')) {
            return true;
        }
        return false;
    }

    /**
     * 获取认证令牌
     */
    getAuthToken() {
        return localStorage.getItem('token') || localStorage.getItem('auth_token') || '';
    }

    /**
     * 显示迁移进度
     */
    showMigrationProgress(message) {
        // 可以在这里添加UI提示
        
        
        // 如果页面有进度显示元素
        const progressElement = document.getElementById('migration-progress');
        if (progressElement) {
            progressElement.textContent = message;
        }
    }

    /**
     * 获取迁移状态
     */
    getMigrationStatus() {
        const migrationData = localStorage.getItem(this.migrationKey);
        
        if (!migrationData) {
            return { migrated: false };
        }

        try {
            const data = JSON.parse(migrationData);
            return {
                migrated: true,
                timestamp: data.timestamp,
                version: data.version
            };
        } catch {
            return { migrated: false };
        }
    }

    /**
     * 强制重新迁移
     */
    async forceMigration() {
        localStorage.removeItem(this.migrationKey);
        return await this.migrate();
    }
}

/**
 * 自动迁移管理器
 * 在页面加载时自动检查并执行迁移
 */
class AutoMigrationManager {
    constructor() {
        this.migrator = new LocalStorageMigrator();
        this.init();
    }

    async init() {
        // 等待DOM加载完成
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.checkAndMigrate());
        } else {
            await this.checkAndMigrate();
        }
    }

    async checkAndMigrate() {
        try {
            // 检查用户是否已登录
            if (!this.isUserLoggedIn()) {
                
                return;
            }

            // 检查是否需要迁移
            if (await this.migrator.needsMigration()) {
                
                
                // 显示迁移提示
                this.showMigrationPrompt();
            }

        } catch (error) {
            console.error('迁移检查失败:', error);
        }
    }

    /**
     * 检查用户是否已登录
     */
    isUserLoggedIn() {
        const token = localStorage.getItem('token') || localStorage.getItem('auth_token');
        return !!token;
    }

    /**
     * 显示迁移提示
     */
    showMigrationPrompt() {
        const prompt = document.createElement('div');
        prompt.id = 'migration-prompt';
        prompt.className = 'migration-prompt';
        prompt.innerHTML = `
            <div class="migration-content">
                <h3>🔄 数据升级</h3>
                <p>我们检测到您有本地存储的设置数据，为了提供更好的体验，建议将这些数据同步到云端。</p>
                <div class="migration-actions">
                    <button id="migrate-now" class="btn btn-primary">立即升级</button>
                    <button id="migrate-later" class="btn btn-secondary">稍后提醒</button>
                    <button id="migrate-skip" class="btn btn-text">跳过</button>
                </div>
                <div id="migration-progress" class="migration-progress" style="display: none;"></div>
            </div>
        `;

        // 添加样式
        const style = document.createElement('style');
        style.textContent = `
            .migration-prompt {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                max-width: 400px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .migration-content h3 {
                margin: 0 0 10px 0;
                color: #333;
            }
            .migration-content p {
                margin: 0 0 15px 0;
                color: #666;
                line-height: 1.4;
            }
            .migration-actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            .migration-actions .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }
            .btn-primary {
                background: #007bff;
                color: white;
            }
            .btn-secondary {
                background: #6c757d;
                color: white;
            }
            .btn-text {
                background: transparent;
                color: #6c757d;
            }
            .migration-progress {
                margin-top: 10px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 4px;
                font-size: 14px;
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(prompt);

        // 绑定事件
        this.bindMigrationEvents(prompt);
    }

    /**
     * 绑定迁移事件
     */
    bindMigrationEvents(prompt) {
        const migrateNow = prompt.querySelector('#migrate-now');
        const migrateLater = prompt.querySelector('#migrate-later');
        const migrateSkip = prompt.querySelector('#migrate-skip');
        const progressDiv = prompt.querySelector('#migration-progress');

        migrateNow.addEventListener('click', async () => {
            progressDiv.style.display = 'block';
            progressDiv.textContent = '正在迁移数据...';

            const result = await this.migrator.migrate();
            
            if (result.success) {
                progressDiv.textContent = `✅ ${result.message}`;
                setTimeout(() => prompt.remove(), 3000);
            } else {
                progressDiv.textContent = `❌ 迁移失败: ${result.error}`;
            }
        });

        migrateLater.addEventListener('click', () => {
            // 设置稍后提醒（24小时后）
            localStorage.setItem('migration_remind_after', Date.now() + 24 * 60 * 60 * 1000);
            prompt.remove();
        });

        migrateSkip.addEventListener('click', () => {
            // 标记为跳过（不再提醒）
            localStorage.setItem('migration_skipped', 'true');
            prompt.remove();
        });
    }
}

// 导出给其他模块使用
window.LocalStorageMigrator = LocalStorageMigrator;
window.AutoMigrationManager = AutoMigrationManager;

// 自动启动迁移管理器
new AutoMigrationManager();
