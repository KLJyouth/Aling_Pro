// 辅助功能模块
class AccessibilityManager {
    constructor() {
        this.fontSizeLevel = 0; // -2 to +2
        this.highContrastEnabled = false;
        this.animationsEnabled = true;
        this.screenReaderEnabled = false;
        this.speechSynthesis = window.speechSynthesis;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadSettings();
        this.setupKeyboardShortcuts();
    }
    
    setupEventListeners() {
        // 工具栏切换
        const toggleBtn = document.getElementById('accessibilityToggle');
        const toolbar = document.getElementById('accessibilityToolbar');
        const closeBtn = document.getElementById('closeAccessibilityToolbar');
        
        toggleBtn?.addEventListener('click', () => this.toggleToolbar());
        closeBtn?.addEventListener('click', () => this.hideToolbar());
        
        // 字体大小控制
        document.getElementById('decreaseFontSize')?.addEventListener('click', () => this.adjustFontSize(-1));
        document.getElementById('resetFontSize')?.addEventListener('click', () => this.resetFontSize());
        document.getElementById('increaseFontSize')?.addEventListener('click', () => this.adjustFontSize(1));
        
        // 对比度切换
        document.getElementById('toggleHighContrast')?.addEventListener('click', () => this.toggleHighContrast());
        
        // 动画切换
        document.getElementById('toggleAnimations')?.addEventListener('click', () => this.toggleAnimations());
        
        // 朗读功能
        document.getElementById('toggleScreenReader')?.addEventListener('click', () => this.toggleScreenReader());
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Alt + A: 打开辅助功能工具栏
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                this.toggleToolbar();
            }
            
            // Alt + C: 切换对比度
            if (e.altKey && e.key === 'c') {
                e.preventDefault();
                this.toggleHighContrast();
            }
            
            // Alt + S: 朗读当前焦点元素
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                this.speakFocusedElement();
            }
            
            // Alt + -/+: 字体大小调整
            if (e.altKey && e.key === '-') {
                e.preventDefault();
                this.adjustFontSize(-1);
            }
            if (e.altKey && e.key === '=') {
                e.preventDefault();
                this.adjustFontSize(1);
            }
        });
    }
    
    toggleToolbar() {
        const toolbar = document.getElementById('accessibilityToolbar');
        if (toolbar) {
            toolbar.classList.toggle('-translate-y-full');
        }
    }
    
    hideToolbar() {
        const toolbar = document.getElementById('accessibilityToolbar');
        if (toolbar) {
            toolbar.classList.add('-translate-y-full');
        }
    }
    
    adjustFontSize(delta) {
        this.fontSizeLevel = Math.max(-2, Math.min(2, this.fontSizeLevel + delta));
        this.applyFontSize();
        this.saveSettings();
        
        // 朗读反馈
        if (this.screenReaderEnabled) {
            this.speak(`字体大小调整为${this.fontSizeLevel > 0 ? '增大' : this.fontSizeLevel < 0 ? '减小' : '正常'}`);
        }
    }
    
    resetFontSize() {
        this.fontSizeLevel = 0;
        this.applyFontSize();
        this.saveSettings();
        
        if (this.screenReaderEnabled) {
            this.speak('字体大小已重置');
        }
    }
    
    applyFontSize() {
        const baseSize = 16; // 基础字体大小
        const newSize = baseSize + (this.fontSizeLevel * 2);
        document.documentElement.style.fontSize = `${newSize}px`;
    }
    
    toggleHighContrast() {
        this.highContrastEnabled = !this.highContrastEnabled;
        
        if (this.highContrastEnabled) {
            document.body.classList.add('high-contrast');
            this.addHighContrastStyles();
        } else {
            document.body.classList.remove('high-contrast');
            this.removeHighContrastStyles();
        }
        
        this.saveSettings();
        this.updateButtonText();
        
        if (this.screenReaderEnabled) {
            this.speak(`高对比度模式${this.highContrastEnabled ? '已开启' : '已关闭'}`);
        }
    }
    
    addHighContrastStyles() {
        if (!document.getElementById('high-contrast-styles')) {
            const styles = document.createElement('style');
            styles.id = 'high-contrast-styles';
            styles.textContent = `
                .high-contrast {
                    filter: contrast(150%) brightness(1.2);
                }
                .high-contrast .glass-card {
                    background: rgba(0, 0, 0, 0.9) !important;
                    border: 2px solid #ffffff !important;
                }
                .high-contrast .text-gray-300,
                .high-contrast .text-gray-400,
                .high-contrast .text-gray-500 {
                    color: #ffffff !important;
                }
                .high-contrast button:focus,
                .high-contrast a:focus {
                    outline: 3px solid #ffff00 !important;
                    outline-offset: 2px !important;
                }
            `;
            document.head.appendChild(styles);
        }
    }
    
    removeHighContrastStyles() {
        const styles = document.getElementById('high-contrast-styles');
        if (styles) {
            styles.remove();
        }
    }
    
    toggleAnimations() {
        this.animationsEnabled = !this.animationsEnabled;
        
        if (!this.animationsEnabled) {
            document.body.classList.add('no-animations');
            this.addNoAnimationStyles();
        } else {
            document.body.classList.remove('no-animations');
            this.removeNoAnimationStyles();
        }
        
        this.saveSettings();
        this.updateButtonText();
        
        if (this.screenReaderEnabled) {
            this.speak(`动画${this.animationsEnabled ? '已启用' : '已禁用'}`);
        }
    }
    
    addNoAnimationStyles() {
        if (!document.getElementById('no-animation-styles')) {
            const styles = document.createElement('style');
            styles.id = 'no-animation-styles';
            styles.textContent = `
                .no-animations *,
                .no-animations *::before,
                .no-animations *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                    scroll-behavior: auto !important;
                }
            `;
            document.head.appendChild(styles);
        }
    }
    
    removeNoAnimationStyles() {
        const styles = document.getElementById('no-animation-styles');
        if (styles) {
            styles.remove();
        }
    }
    
    toggleScreenReader() {
        this.screenReaderEnabled = !this.screenReaderEnabled;
        this.saveSettings();
        this.updateButtonText();
        
        if (this.screenReaderEnabled) {
            this.speak('朗读功能已开启');
            this.setupScreenReaderFeatures();
        } else {
            this.speak('朗读功能已关闭');
            this.speechSynthesis.cancel();
        }
    }
    
    setupScreenReaderFeatures() {
        // 添加焦点朗读
        document.addEventListener('focusin', (e) => {
            if (this.screenReaderEnabled) {
                this.speakElement(e.target);
            }
        });
        
        // 添加悬停朗读（可选）
        document.addEventListener('mouseenter', (e) => {
            if (this.screenReaderEnabled && e.target.matches('button, a, [role="button"]')) {
                this.speakElement(e.target);
            }
        }, true);
    }
    
    speakElement(element) {
        if (!element || !this.speechSynthesis) return;
        
        let text = '';
        
        // 获取元素的可访问名称
        if (element.getAttribute('aria-label')) {
            text = element.getAttribute('aria-label');
        } else if (element.getAttribute('title')) {
            text = element.getAttribute('title');
        } else if (element.textContent) {
            text = element.textContent.trim();
        } else if (element.getAttribute('alt')) {
            text = element.getAttribute('alt');
        }
        
        // 添加元素类型信息
        if (element.tagName === 'BUTTON') {
            text = `按钮: ${text}`;
        } else if (element.tagName === 'A') {
            text = `链接: ${text}`;
        } else if (element.tagName === 'INPUT') {
            text = `输入框: ${text}`;
        }
        
        if (text) {
            this.speak(text);
        }
    }
    
    speakFocusedElement() {
        const focusedElement = document.activeElement;
        if (focusedElement) {
            this.speakElement(focusedElement);
        }
    }
    
    speak(text) {
        if (!this.speechSynthesis || !text) return;
        
        // 停止当前朗读
        this.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.8;
        utterance.pitch = 1;
        utterance.volume = 0.8;
        
        // 使用中文语音（如果可用）
        const voices = this.speechSynthesis.getVoices();
        const chineseVoice = voices.find(voice => voice.lang.includes('zh'));
        if (chineseVoice) {
            utterance.voice = chineseVoice;
        }
        
        this.speechSynthesis.speak(utterance);
    }
    
    updateButtonText() {
        const contrastBtn = document.getElementById('toggleHighContrast');
        const animationBtn = document.getElementById('toggleAnimations');
        const readerBtn = document.getElementById('toggleScreenReader');
        
        if (contrastBtn) {
            contrastBtn.textContent = this.highContrastEnabled ? '正常对比度' : '高对比度';
        }
        
        if (animationBtn) {
            animationBtn.textContent = this.animationsEnabled ? '禁用动画' : '启用动画';
        }
        
        if (readerBtn) {
            const icon = readerBtn.querySelector('i');
            const text = readerBtn.querySelector('span');
            if (icon && text) {
                icon.className = this.screenReaderEnabled ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                text.textContent = this.screenReaderEnabled ? '关闭朗读' : '朗读';
            }
        }
    }
    
    saveSettings() {
        const settings = {
            fontSizeLevel: this.fontSizeLevel,
            highContrastEnabled: this.highContrastEnabled,
            animationsEnabled: this.animationsEnabled,
            screenReaderEnabled: this.screenReaderEnabled
        };
        
        localStorage.setItem('accessibility-settings', JSON.stringify(settings));
    }
    
    loadSettings() {
        const saved = localStorage.getItem('accessibility-settings');
        if (saved) {
            try {
                const settings = JSON.parse(saved);
                
                this.fontSizeLevel = settings.fontSizeLevel || 0;
                this.highContrastEnabled = settings.highContrastEnabled || false;
                this.animationsEnabled = settings.animationsEnabled !== false; // 默认启用
                this.screenReaderEnabled = settings.screenReaderEnabled || false;
                
                // 应用设置
                this.applyFontSize();
                if (this.highContrastEnabled) {
                    document.body.classList.add('high-contrast');
                    this.addHighContrastStyles();
                }
                if (!this.animationsEnabled) {
                    document.body.classList.add('no-animations');
                    this.addNoAnimationStyles();
                }
                
                this.updateButtonText();
                
            } catch (error) {
                console.warn('无法加载辅助功能设置:', error);
            }
        }
    }
}

// 初始化辅助功能管理器
let accessibilityManager;

document.addEventListener('DOMContentLoaded', () => {
    accessibilityManager = new AccessibilityManager();
});

// 导出以供其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AccessibilityManager;
}
