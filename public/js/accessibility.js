/**
 * 网站无障碍功能脚本
 * 
 * 提供网站无障碍功能，包括高对比度模式、字体大小调整和屏幕阅读器兼容性增强
 */

(function() {
    'use strict';
    
    // 初始设置
    let currentFontSize = 100; // 百分比
    let isHighContrast = false;
    
    // 从本地存储加载用户设置
    function loadAccessibilitySettings() {
        if (localStorage.getItem('accessibility')) {
            const settings = JSON.parse(localStorage.getItem('accessibility'));
            currentFontSize = settings.fontSize || 100;
            isHighContrast = settings.highContrast || false;
            
            // 应用设置
            applyFontSize();
            applyContrastMode();
        }
    }
    
    // 保存用户设置到本地存储
    function saveAccessibilitySettings() {
        const settings = {
            fontSize: currentFontSize,
            highContrast: isHighContrast
        };
        
        localStorage.setItem('accessibility', JSON.stringify(settings));
    }
    
    // 应用字体大小
    function applyFontSize() {
        document.documentElement.style.fontSize = `${currentFontSize}%`;
    }
    
    // 应用高对比度模式
    function applyContrastMode() {
        if (isHighContrast) {
            document.body.classList.add('high-contrast');
        } else {
            document.body.classList.remove('high-contrast');
        }
    }
    
    // 增加字体大小
    function increaseFontSize() {
        if (currentFontSize < 200) {
            currentFontSize += 10;
            applyFontSize();
            saveAccessibilitySettings();
            
            // 通知屏幕阅读器
            announceToScreenReader(`字体大小已增加到${currentFontSize}%`);
        }
    }
    
    // 减小字体大小
    function decreaseFontSize() {
        if (currentFontSize > 70) {
            currentFontSize -= 10;
            applyFontSize();
            saveAccessibilitySettings();
            
            // 通知屏幕阅读器
            announceToScreenReader(`字体大小已减小到${currentFontSize}%`);
        }
    }
    
    // 切换高对比度模式
    function toggleHighContrast() {
        isHighContrast = !isHighContrast;
        applyContrastMode();
        saveAccessibilitySettings();
        
        // 通知屏幕阅读器
        announceToScreenReader(isHighContrast ? '已开启高对比度模式' : '已关闭高对比度模式');
    }
    
    // 重置所有设置
    function resetAccessibility() {
        currentFontSize = 100;
        isHighContrast = false;
        
        applyFontSize();
        applyContrastMode();
        saveAccessibilitySettings();
        
        // 通知屏幕阅读器
        announceToScreenReader('已重置所有无障碍设置');
    }
    
    // 创建辅助功能公告区域(用于屏幕阅读器)
    function createAnnouncementArea() {
        let announce = document.getElementById('accessibility-announcement');
        
        if (!announce) {
            announce = document.createElement('div');
            announce.id = 'accessibility-announcement';
            announce.setAttribute('aria-live', 'polite');
            announce.setAttribute('aria-atomic', 'true');
            announce.style.position = 'absolute';
            announce.style.width = '1px';
            announce.style.height = '1px';
            announce.style.padding = '0';
            announce.style.margin = '-1px';
            announce.style.overflow = 'hidden';
            announce.style.clip = 'rect(0, 0, 0, 0)';
            announce.style.whiteSpace = 'nowrap';
            announce.style.border = '0';
            
            document.body.appendChild(announce);
        }
        
        return announce;
    }
    
    // 向屏幕阅读器通知消息
    function announceToScreenReader(message) {
        const announce = createAnnouncementArea();
        announce.textContent = message;
    }
    
    // 创建无障碍控制面板
    function createAccessibilityPanel() {
        const panel = document.createElement('div');
        panel.className = 'accessibility-panel';
        panel.setAttribute('aria-label', '无障碍选项');
        
        // 控制面板按钮
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'accessibility-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-universal-access"></i>';
        toggleBtn.setAttribute('aria-label', '打开无障碍选项');
        toggleBtn.setAttribute('title', '无障碍选项');
        
        // 控制面板内容
        const panelContent = document.createElement('div');
        panelContent.className = 'accessibility-panel-content';
        
        // 字体大小控制
        const fontSizeGroup = document.createElement('div');
        fontSizeGroup.className = 'accessibility-group';
        
        const fontSizeLabel = document.createElement('div');
        fontSizeLabel.className = 'accessibility-label';
        fontSizeLabel.textContent = '字体大小';
        
        const fontControls = document.createElement('div');
        fontControls.className = 'accessibility-controls';
        
        const decreaseBtn = document.createElement('button');
        decreaseBtn.innerHTML = 'A-';
        decreaseBtn.setAttribute('aria-label', '减小字体');
        
        const resetFontBtn = document.createElement('button');
        resetFontBtn.innerHTML = 'A';
        resetFontBtn.setAttribute('aria-label', '重置字体大小');
        
        const increaseBtn = document.createElement('button');
        increaseBtn.innerHTML = 'A+';
        increaseBtn.setAttribute('aria-label', '增大字体');
        
        fontControls.appendChild(decreaseBtn);
        fontControls.appendChild(resetFontBtn);
        fontControls.appendChild(increaseBtn);
        
        fontSizeGroup.appendChild(fontSizeLabel);
        fontSizeGroup.appendChild(fontControls);
        
        // 对比度控制
        const contrastGroup = document.createElement('div');
        contrastGroup.className = 'accessibility-group';
        
        const contrastLabel = document.createElement('div');
        contrastLabel.className = 'accessibility-label';
        contrastLabel.textContent = '高对比度';
        
        const contrastControl = document.createElement('div');
        contrastControl.className = 'accessibility-controls';
        
        const contrastBtn = document.createElement('button');
        contrastBtn.innerHTML = '开关';
        contrastBtn.setAttribute('aria-label', '切换高对比度模式');
        
        contrastControl.appendChild(contrastBtn);
        contrastGroup.appendChild(contrastLabel);
        contrastGroup.appendChild(contrastControl);
        
        // 重置按钮
        const resetGroup = document.createElement('div');
        resetGroup.className = 'accessibility-group';
        
        const resetBtn = document.createElement('button');
        resetBtn.className = 'accessibility-reset';
        resetBtn.textContent = '重置所有设置';
        
        resetGroup.appendChild(resetBtn);
        
        // 组装面板
        panelContent.appendChild(fontSizeGroup);
        panelContent.appendChild(contrastGroup);
        panelContent.appendChild(resetGroup);
        
        panel.appendChild(toggleBtn);
        panel.appendChild(panelContent);
        
        // 事件处理
        toggleBtn.addEventListener('click', function() {
            panel.classList.toggle('active');
            
            if (panel.classList.contains('active')) {
                toggleBtn.setAttribute('aria-label', '关闭无障碍选项');
            } else {
                toggleBtn.setAttribute('aria-label', '打开无障碍选项');
            }
        });
        
        decreaseBtn.addEventListener('click', decreaseFontSize);
        increaseBtn.addEventListener('click', increaseFontSize);
        resetFontBtn.addEventListener('click', function() {
            currentFontSize = 100;
            applyFontSize();
            saveAccessibilitySettings();
            announceToScreenReader('已重置字体大小');
        });
        
        contrastBtn.addEventListener('click', toggleHighContrast);
        resetBtn.addEventListener('click', resetAccessibility);
        
        // 添加到文档
        document.body.appendChild(panel);
    }
    
    // 页面加载完成后初始化
    window.addEventListener('DOMContentLoaded', function() {
        // 创建公告区域
        createAnnouncementArea();
        
        // 加载并应用设置
        loadAccessibilitySettings();
        
        // 创建控制面板
        createAccessibilityPanel();
        
        // 添加键盘快捷键
        document.addEventListener('keydown', function(e) {
            // Alt + 加号: 增加字体
            if (e.altKey && e.key === '+') {
                increaseFontSize();
                e.preventDefault();
            }
            
            // Alt + 减号: 减小字体
            if (e.altKey && e.key === '-') {
                decreaseFontSize();
                e.preventDefault();
            }
            
            // Alt + C: 切换高对比度
            if (e.altKey && e.key === 'c') {
                toggleHighContrast();
                e.preventDefault();
            }
            
            // Alt + R: 重置所有设置
            if (e.altKey && e.key === 'r') {
                resetAccessibility();
                e.preventDefault();
            }
        });
    });
})();
