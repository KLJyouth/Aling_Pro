/**
 * 社交分享和用户自定义系统
 * 允许用户自定义动画参数并分享创作
 */
class SocialCustomizationSystem {
    constructor() {
        this.userPreferences = {
            theme: 'quantum',
            speed: 1.0,
            particleCount: 20,
            colorScheme: 'default',
            effectIntensity: 1.0,
            soundEnabled: true,
            autoRestart: true,
            customCode: '#include <iostream>\\nusing namespace std;\\nint main() {\\n    cout << "Hello, World!" << endl;\\n    return 0;\\n}'
        };
        
        this.presetThemes = {
            quantum: {
                colorScheme: 'quantum',
                speed: 1.0,
                particleCount: 20,
                effectIntensity: 1.0
            },
            cyberpunk: {
                colorScheme: 'cyberpunk',
                speed: 1.5,
                particleCount: 30,
                effectIntensity: 1.3
            },
            minimal: {
                colorScheme: 'minimal',
                speed: 0.8,
                particleCount: 10,
                effectIntensity: 0.7
            },
            cosmic: {
                colorScheme: 'cosmic',
                speed: 1.2,
                particleCount: 40,
                effectIntensity: 1.5
            },
            retro: {
                colorScheme: 'retro',
                speed: 0.9,
                particleCount: 15,
                effectIntensity: 0.8
            }
        };
        
        this.socialPlatforms = {
            twitter: {
                url: 'https://twitter.com/intent/tweet',
                params: {
                    text: '看看我创建的量子C++动画效果！',
                    url: window.location.href,
                    hashtags: 'AlingAi,量子编程,C++动画'
                }
            },
            facebook: {
                url: 'https://www.facebook.com/sharer/sharer.php',
                params: {
                    u: window.location.href
                }
            },
            linkedin: {
                url: 'https://www.linkedin.com/sharing/share-offsite/',
                params: {
                    url: window.location.href
                }
            },
            weibo: {
                url: 'https://service.weibo.com/share/share.php',
                params: {
                    url: window.location.href,
                    title: '珑凌科技量子C++动画体验',
                    content: 'utf-8'
                }
            }
        };
        
        this.init();
    }
    
    init() {
        this.loadUserPreferences();
        this.createCustomizationPanel();
        this.setupShareButtons();
        this.setupPresetManager();
        console.log('🎨 社交自定义系统初始化完成');
    }
    
    // 创建自定义面板
    createCustomizationPanel() {
        const panel = document.createElement('div');
        panel.id = 'customization-panel';
        panel.className = 'customization-panel hidden';
        
        panel.innerHTML = `
            <div class="panel-header">
                <h3>🎨 动画自定义</h3>
                <button class="close-btn" onclick="socialCustomization.togglePanel()">×</button>
            </div>
            
            <div class="panel-content">
                <!-- 主题选择 -->
                <div class="control-group">
                    <label>预设主题</label>
                    <select id="theme-select" onchange="socialCustomization.applyTheme(this.value)">
                        ${Object.keys(this.presetThemes).map(theme => 
                            `<option value="${theme}">${this.capitalizeFirst(theme)}</option>`
                        ).join('')}
                    </select>
                </div>
                
                <!-- 速度控制 -->
                <div class="control-group">
                    <label>动画速度: <span id="speed-value">${this.userPreferences.speed}</span></label>
                    <input type="range" id="speed-slider" min="0.1" max="3.0" step="0.1" 
                           value="${this.userPreferences.speed}" 
                           oninput="socialCustomization.updateSpeed(this.value)">
                </div>
                
                <!-- 粒子数量 -->
                <div class="control-group">
                    <label>粒子数量: <span id="particle-value">${this.userPreferences.particleCount}</span></label>
                    <input type="range" id="particle-slider" min="5" max="100" step="5" 
                           value="${this.userPreferences.particleCount}" 
                           oninput="socialCustomization.updateParticleCount(this.value)">
                </div>
                
                <!-- 效果强度 -->
                <div class="control-group">
                    <label>效果强度: <span id="intensity-value">${this.userPreferences.effectIntensity}</span></label>
                    <input type="range" id="intensity-slider" min="0.1" max="3.0" step="0.1" 
                           value="${this.userPreferences.effectIntensity}" 
                           oninput="socialCustomization.updateIntensity(this.value)">
                </div>
                
                <!-- 色彩方案 -->
                <div class="control-group">
                    <label>色彩方案</label>
                    <div class="color-scheme-grid">
                        <div class="color-option quantum" data-scheme="quantum" onclick="socialCustomization.updateColorScheme('quantum')"></div>
                        <div class="color-option cyberpunk" data-scheme="cyberpunk" onclick="socialCustomization.updateColorScheme('cyberpunk')"></div>
                        <div class="color-option aurora" data-scheme="aurora" onclick="socialCustomization.updateColorScheme('aurora')"></div>
                        <div class="color-option plasma" data-scheme="plasma" onclick="socialCustomization.updateColorScheme('plasma')"></div>
                        <div class="color-option matrix" data-scheme="matrix" onclick="socialCustomization.updateColorScheme('matrix')"></div>
                        <div class="color-option galaxy" data-scheme="galaxy" onclick="socialCustomization.updateColorScheme('galaxy')"></div>
                    </div>
                </div>
                
                <!-- 自定义代码 -->
                <div class="control-group">
                    <label>自定义C++代码</label>
                    <textarea id="custom-code" rows="6" 
                              onchange="socialCustomization.updateCustomCode(this.value)"
                              placeholder="输入您的C++代码...">${this.userPreferences.customCode}</textarea>
                </div>
                
                <!-- 音效控制 -->
                <div class="control-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="sound-toggle" 
                               ${this.userPreferences.soundEnabled ? 'checked' : ''} 
                               onchange="socialCustomization.toggleSound(this.checked)">
                        启用音效
                    </label>
                </div>
                
                <!-- 自动重启 -->
                <div class="control-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="auto-restart-toggle" 
                               ${this.userPreferences.autoRestart ? 'checked' : ''} 
                               onchange="socialCustomization.toggleAutoRestart(this.checked)">
                        自动重启动画
                    </label>
                </div>
                
                <!-- 操作按钮 -->
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="socialCustomization.applyChanges()">应用更改</button>
                    <button class="btn btn-secondary" onclick="socialCustomization.resetToDefault()">重置默认</button>
                    <button class="btn btn-success" onclick="socialCustomization.savePreset()">保存预设</button>
                    <button class="btn btn-warning" onclick="socialCustomization.exportSettings()">导出设置</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(panel);
        this.addPanelStyles();
    }
    
    // 创建分享按钮
    setupShareButtons() {
        const shareContainer = document.createElement('div');
        shareContainer.id = 'share-container';
        shareContainer.className = 'share-container';
        
        shareContainer.innerHTML = `
            <div class="share-header">
                <h4>📱 分享您的创作</h4>
            </div>
            <div class="share-buttons">
                <button class="share-btn twitter" onclick="socialCustomization.shareToSocial('twitter')">
                    <i class="fab fa-twitter"></i> Twitter
                </button>
                <button class="share-btn facebook" onclick="socialCustomization.shareToSocial('facebook')">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
                <button class="share-btn linkedin" onclick="socialCustomization.shareToSocial('linkedin')">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </button>
                <button class="share-btn weibo" onclick="socialCustomization.shareToSocial('weibo')">
                    <i class="fab fa-weibo"></i> 微博
                </button>
                <button class="share-btn copy" onclick="socialCustomization.copyShareLink()">
                    <i class="fas fa-link"></i> 复制链接
                </button>
                <button class="share-btn screenshot" onclick="socialCustomization.takeScreenshot()">
                    <i class="fas fa-camera"></i> 截图
                </button>
            </div>
        `;
        
        const customizationPanel = document.getElementById('customization-panel');
        if (customizationPanel) {
            customizationPanel.appendChild(shareContainer);
        }
    }
    
    // 设置预设管理器
    setupPresetManager() {
        // 创建预设管理区域
        const presetManager = document.createElement('div');
        presetManager.innerHTML = `
            <div class="preset-manager">
                <h4>💾 预设管理</h4>
                <div class="preset-list" id="preset-list">
                    <!-- 预设列表将动态生成 -->
                </div>
                <div class="preset-actions">
                    <input type="text" id="preset-name" placeholder="输入预设名称...">
                    <button onclick="socialCustomization.saveUserPreset()">保存新预设</button>
                </div>
            </div>
        `;
        
        const panelContent = document.querySelector('.panel-content');
        if (panelContent) {
            panelContent.appendChild(presetManager);
        }
        
        this.refreshPresetList();
    }
    
    // 用户偏好管理
    loadUserPreferences() {
        const saved = localStorage.getItem('alingai-animation-preferences');
        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                this.userPreferences = { ...this.userPreferences, ...parsed };
            } catch (error) {
                console.warn('加载用户偏好失败:', error);
            }
        }
    }
    
    saveUserPreferences() {
        localStorage.setItem('alingai-animation-preferences', JSON.stringify(this.userPreferences));
    }
    
    // 控制方法
    updateSpeed(value) {
        this.userPreferences.speed = parseFloat(value);
        document.getElementById('speed-value').textContent = value;
        this.applyToAnimation();
    }
    
    updateParticleCount(value) {
        this.userPreferences.particleCount = parseInt(value);
        document.getElementById('particle-value').textContent = value;
        this.applyToAnimation();
    }
    
    updateIntensity(value) {
        this.userPreferences.effectIntensity = parseFloat(value);
        document.getElementById('intensity-value').textContent = value;
        this.applyToAnimation();
    }
    
    updateColorScheme(scheme) {
        this.userPreferences.colorScheme = scheme;
        
        // 更新UI选中状态
        document.querySelectorAll('.color-option').forEach(el => {
            el.classList.remove('selected');
        });
        document.querySelector(`.color-option[data-scheme="${scheme}"]`).classList.add('selected');
        
        this.applyToAnimation();
    }
    
    updateCustomCode(code) {
        this.userPreferences.customCode = code;
    }
    
    toggleSound(enabled) {
        this.userPreferences.soundEnabled = enabled;
        
        if (window.audioEnhancement) {
            if (enabled) {
                window.audioEnhancement.enabled = true;
            } else {
                window.audioEnhancement.enabled = false;
            }
        }
    }
    
    toggleAutoRestart(enabled) {
        this.userPreferences.autoRestart = enabled;
    }
    
    // 主题应用
    applyTheme(themeName) {
        const theme = this.presetThemes[themeName];
        if (!theme) return;
        
        Object.entries(theme).forEach(([key, value]) => {
            this.userPreferences[key] = value;
        });
        
        this.updateUI();
        this.applyToAnimation();
    }
    
    updateUI() {
        document.getElementById('speed-slider').value = this.userPreferences.speed;
        document.getElementById('speed-value').textContent = this.userPreferences.speed;
        
        document.getElementById('particle-slider').value = this.userPreferences.particleCount;
        document.getElementById('particle-value').textContent = this.userPreferences.particleCount;
        
        document.getElementById('intensity-slider').value = this.userPreferences.effectIntensity;
        document.getElementById('intensity-value').textContent = this.userPreferences.effectIntensity;
        
        this.updateColorScheme(this.userPreferences.colorScheme);
    }
    
    applyToAnimation() {
        if (window.cppAnimation) {
            window.cppAnimation.updateParameter('speed', this.userPreferences.speed);
            window.cppAnimation.updateParameter('particleCount', this.userPreferences.particleCount);
            window.cppAnimation.updateParameter('effectIntensity', this.userPreferences.effectIntensity);
            window.cppAnimation.setColorScheme(this.userPreferences.colorScheme);
        }
    }
    
    applyChanges() {
        this.applyToAnimation();
        this.saveUserPreferences();
        
        // 重启动画以应用更改
        if (window.cppAnimation && this.userPreferences.autoRestart) {
            window.cppAnimation.restart();
        }
        
        this.showNotification('✨ 设置已应用！');
    }
    
    resetToDefault() {
        this.userPreferences = {
            theme: 'quantum',
            speed: 1.0,
            particleCount: 20,
            colorScheme: 'quantum',
            effectIntensity: 1.0,
            soundEnabled: true,
            autoRestart: true,
            customCode: '#include <iostream>\\nusing namespace std;\\nint main() {\\n    cout << "Hello, World!" << endl;\\n    return 0;\\n}'
        };
        
        this.updateUI();
        this.applyToAnimation();
        this.saveUserPreferences();
        
        this.showNotification('🔄 已重置为默认设置');
    }
    
    // 社交分享
    shareToSocial(platform) {
        const config = this.socialPlatforms[platform];
        if (!config) return;
        
        // 生成包含当前设置的分享链接
        const shareData = this.generateShareData();
        const shareUrl = this.createShareUrl(shareData);
        
        // 构建分享链接
        const params = new URLSearchParams();
        Object.entries(config.params).forEach(([key, value]) => {
            if (key === 'url') {
                params.append(key, shareUrl);
            } else {
                params.append(key, value);
            }
        });
        
        const finalUrl = `${config.url}?${params.toString()}`;
        
        // 打开分享窗口
        window.open(finalUrl, 'share', 'width=600,height=400,scrollbars=yes,resizable=yes');
    }
    
    generateShareData() {
        return {
            timestamp: Date.now(),
            preferences: this.userPreferences,
            version: '1.0'
        };
    }
    
    createShareUrl(data) {
        const encoded = btoa(JSON.stringify(data));
        return `${window.location.origin}${window.location.pathname}?shared=${encoded}`;
    }
    
    copyShareLink() {
        const shareData = this.generateShareData();
        const shareUrl = this.createShareUrl(shareData);
        
        navigator.clipboard.writeText(shareUrl).then(() => {
            this.showNotification('🔗 分享链接已复制到剪贴板！');
        }).catch(() => {
            // 备用方法
            const textArea = document.createElement('textarea');
            textArea.value = shareUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            this.showNotification('🔗 分享链接已复制！');
        });
    }
    
    async takeScreenshot() {
        try {
            if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
                const stream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                const video = document.createElement('video');
                video.srcObject = stream;
                video.play();
                
                video.addEventListener('loadedmetadata', () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);
                    
                    stream.getTracks().forEach(track => track.stop());
                    
                    // 下载截图
                    const link = document.createElement('a');
                    link.download = `alingai-animation-${Date.now()}.png`;
                    link.href = canvas.toDataURL();
                    link.click();
                    
                    this.showNotification('📸 截图已保存！');
                });
            } else {
                this.showNotification('❌ 您的浏览器不支持屏幕截图功能');
            }
        } catch (error) {
            console.warn('截图失败:', error);
            this.showNotification('❌ 截图失败，请检查权限设置');
        }
    }
    
    // 预设管理
    saveUserPreset() {
        const name = document.getElementById('preset-name').value.trim();
        if (!name) {
            this.showNotification('⚠️ 请输入预设名称');
            return;
        }
        
        const userPresets = this.getUserPresets();
        userPresets[name] = { ...this.userPreferences };
        
        localStorage.setItem('alingai-user-presets', JSON.stringify(userPresets));
        this.refreshPresetList();
        
        document.getElementById('preset-name').value = '';
        this.showNotification(`💾 预设 "${name}" 已保存`);
    }
    
    getUserPresets() {
        const saved = localStorage.getItem('alingai-user-presets');
        return saved ? JSON.parse(saved) : {};
    }
    
    refreshPresetList() {
        const presetList = document.getElementById('preset-list');
        if (!presetList) return;
        
        const userPresets = this.getUserPresets();
        
        let html = '';
        Object.entries(userPresets).forEach(([name, preset]) => {
            html += `
                <div class="preset-item">
                    <span class="preset-name">${name}</span>
                    <div class="preset-actions">
                        <button onclick="socialCustomization.loadUserPreset('${name}')" class="btn-small">加载</button>
                        <button onclick="socialCustomization.deleteUserPreset('${name}')" class="btn-small btn-danger">删除</button>
                    </div>
                </div>
            `;
        });
        
        if (html === '') {
            html = '<div class="no-presets">暂无自定义预设</div>';
        }
        
        presetList.innerHTML = html;
    }
    
    loadUserPreset(name) {
        const userPresets = this.getUserPresets();
        const preset = userPresets[name];
        
        if (preset) {
            this.userPreferences = { ...this.userPreferences, ...preset };
            this.updateUI();
            this.applyToAnimation();
            this.showNotification(`✨ 预设 "${name}" 已加载`);
        }
    }
    
    deleteUserPreset(name) {
        if (confirm(`确定要删除预设 "${name}" 吗？`)) {
            const userPresets = this.getUserPresets();
            delete userPresets[name];
            localStorage.setItem('alingai-user-presets', JSON.stringify(userPresets));
            this.refreshPresetList();
            this.showNotification(`🗑️ 预设 "${name}" 已删除`);
        }
    }
    
    // 设置导出/导入
    exportSettings() {
        const exportData = {
            preferences: this.userPreferences,
            userPresets: this.getUserPresets(),
            timestamp: Date.now(),
            version: '1.0'
        };
        
        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `alingai-settings-${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        
        URL.revokeObjectURL(url);
        this.showNotification('📁 设置已导出');
    }
    
    // UI 控制
    togglePanel() {
        const panel = document.getElementById('customization-panel');
        panel.classList.toggle('hidden');
    }
    
    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 255, 255, 0.9);
            color: black;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 10001;
            animation: slideIn 0.3s ease-out;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // 样式
    addPanelStyles() {
        const styles = document.createElement('style');
        styles.textContent = `
            .customization-panel {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 500px;
                max-width: 90vw;
                max-height: 90vh;
                background: rgba(0, 0, 0, 0.95);
                color: #00ff00;
                border: 2px solid #00ff00;
                border-radius: 10px;
                z-index: 10000;
                overflow-y: auto;
                backdrop-filter: blur(10px);
            }
            
            .customization-panel.hidden {
                display: none;
            }
            
            .panel-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                border-bottom: 1px solid #00ff00;
            }
            
            .close-btn {
                background: none;
                border: none;
                color: #00ff00;
                font-size: 24px;
                cursor: pointer;
            }
            
            .panel-content {
                padding: 20px;
            }
            
            .control-group {
                margin-bottom: 20px;
            }
            
            .control-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
            }
            
            .control-group input[type="range"] {
                width: 100%;
                margin: 5px 0;
            }
            
            .control-group select,
            .control-group textarea {
                width: 100%;
                background: rgba(0, 0, 0, 0.7);
                color: #00ff00;
                border: 1px solid #00ff00;
                border-radius: 4px;
                padding: 8px;
            }
            
            .color-scheme-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                margin-top: 10px;
            }
            
            .color-option {
                width: 60px;
                height: 40px;
                border-radius: 8px;
                cursor: pointer;
                border: 2px solid transparent;
                transition: all 0.3s ease;
            }
            
            .color-option.selected {
                border-color: #00ff00;
                box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
            }
            
            .color-option.quantum { background: linear-gradient(45deg, #00ff00, #0080ff, #ff00ff); }
            .color-option.cyberpunk { background: linear-gradient(45deg, #ff0080, #8000ff, #00ff80); }
            .color-option.aurora { background: linear-gradient(45deg, #80ff00, #00ffff, #ff8000); }
            .color-option.plasma { background: linear-gradient(45deg, #ff4000, #ff8000, #ffff00); }
            .color-option.matrix { background: linear-gradient(45deg, #00ff00, #008000, #004000); }
            .color-option.galaxy { background: linear-gradient(45deg, #4000ff, #8000ff, #ff00ff); }
            
            .checkbox-label {
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
            }
            
            .action-buttons {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin-top: 20px;
            }
            
            .btn {
                padding: 10px 15px;
                border: 1px solid #00ff00;
                background: rgba(0, 255, 0, 0.1);
                color: #00ff00;
                border-radius: 5px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .btn:hover {
                background: rgba(0, 255, 0, 0.2);
                box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
            }
            
            .btn-primary { border-color: #00ff00; }
            .btn-secondary { border-color: #888; color: #888; }
            .btn-success { border-color: #00ff80; color: #00ff80; }
            .btn-warning { border-color: #ffff00; color: #ffff00; }
            .btn-danger { border-color: #ff0080; color: #ff0080; }
            
            .share-container {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #00ff00;
            }
            
            .share-buttons {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                margin-top: 15px;
            }
            
            .share-btn {
                padding: 8px 12px;
                border: 1px solid;
                border-radius: 5px;
                background: rgba(0, 0, 0, 0.7);
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
                font-size: 12px;
            }
            
            .share-btn.twitter { border-color: #1da1f2; color: #1da1f2; }
            .share-btn.facebook { border-color: #4267b2; color: #4267b2; }
            .share-btn.linkedin { border-color: #0077b5; color: #0077b5; }
            .share-btn.weibo { border-color: #d52b1e; color: #d52b1e; }
            .share-btn.copy { border-color: #888; color: #888; }
            .share-btn.screenshot { border-color: #ff6b6b; color: #ff6b6b; }
            
            .preset-manager {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #00ff00;
            }
            
            .preset-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid rgba(0, 255, 0, 0.3);
            }
            
            .preset-actions {
                display: flex;
                gap: 8px;
            }
            
            .btn-small {
                padding: 4px 8px;
                font-size: 12px;
                border: 1px solid #00ff00;
                background: rgba(0, 255, 0, 0.1);
                color: #00ff00;
                border-radius: 3px;
                cursor: pointer;
            }
            
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    // 工具方法
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    // 检查URL中的分享参数
    checkSharedSettings() {
        const urlParams = new URLSearchParams(window.location.search);
        const shared = urlParams.get('shared');
        
        if (shared) {
            try {
                const decoded = atob(shared);
                const data = JSON.parse(decoded);
                
                if (data.preferences) {
                    this.userPreferences = { ...this.userPreferences, ...data.preferences };
                    this.applyToAnimation();
                    this.showNotification('🔗 已加载分享的设置！');
                }
            } catch (error) {
                console.warn('分享设置解析失败:', error);
            }
        }
    }
}

// 创建全局浮动按钮
function createCustomizationButton() {
    const button = document.createElement('button');
    button.id = 'customization-toggle';
    button.innerHTML = '🎨';
    
    document.body.appendChild(button);
    
    // 使用悬浮按钮管理器注册
    if (window.floatingButtonsManager) {
        window.floatingButtonsManager.registerButton('customization', {
            element: button,
            preferredPosition: 'bottom-right-2',
            type: 'customization',
            priority: 3,
            title: '自定义动画设置',
            icon: '',
            onClick: () => {
                window.socialCustomization.togglePanel();
            }
        });
    } else {
        // 后备方案：如果管理器未加载，使用传统方式
        button.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00ffff 0%, #00ff00 100%);
            color: #000;
            font-size: 20px;
            cursor: pointer;
            z-index: 1030;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        button.addEventListener('click', () => {
            window.socialCustomization.togglePanel();
        });
    }
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
    window.socialCustomization = new SocialCustomizationSystem();
    createCustomizationButton();
    
    // 检查分享设置
    setTimeout(() => {
        window.socialCustomization.checkSharedSettings();
    }, 1000);
});
