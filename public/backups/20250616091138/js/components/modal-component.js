/**
 * AlingAi Pro - 模态框组件
 * 现代化、可定制的模态框组件，支持量子效果和多种样式
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @features
 * - 多种尺寸和样式
 * - 量子动画效果
 * - 可访问性支持
 * - 拖拽功能
 * - 嵌套模态框支持
 */

class AlingModalComponent {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            size: 'medium', // small, medium, large, full
            backdrop: true,
            keyboard: true,
            focus: true,
            show: false,
            quantumEffect: false,
            draggable: false,
            maxWidth: null,
            maxHeight: null,
            position: 'center', // center, top, bottom
            animation: 'fade', // fade, slide, zoom, quantum
            autoHeight: false,
            closeOnOutsideClick: true,
            showCloseButton: true,
            headerContent: '',
            bodyContent: '',
            footerContent: '',
            ...options
        };

        this.isVisible = false;
        this.backdrop = null;
        this.modal = null;
        this.focusableElements = [];
        this.previouslyFocused = null;
        
        this.init();
    }

    init() {
        this.createModal();
        this.bindEvents();
        
        if (this.options.show) {
            this.show();
        }
    }

    createModal() {
        // 创建背景遮罩
        if (this.options.backdrop) {
            this.backdrop = document.createElement('div');
            this.backdrop.className = `modal-backdrop ${this.options.quantumEffect ? 'quantum-backdrop' : ''}`;
            this.backdrop.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
                z-index: 1040;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
        }

        // 创建模态框容器
        this.modal = document.createElement('div');
        this.modal.className = `modal modal-${this.options.size} modal-${this.options.position}`;
        this.modal.setAttribute('role', 'dialog');
        this.modal.setAttribute('aria-modal', 'true');
        this.modal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            z-index: 1050;
            opacity: 0;
            transition: all 0.3s ease;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        `;

        // 设置尺寸
        this.setModalSize();

        // 创建模态框内容
        this.createModalContent();

        // 添加量子效果
        if (this.options.quantumEffect) {
            this.addQuantumEffects();
        }

        // 添加拖拽功能
        if (this.options.draggable) {
            this.makeDraggable();
        }
    }

    setModalSize() {
        const sizeMap = {
            small: { width: '400px', maxWidth: '90vw' },
            medium: { width: '600px', maxWidth: '90vw' },
            large: { width: '900px', maxWidth: '95vw' },
            full: { width: '100vw', height: '100vh', maxWidth: 'none' }
        };

        const size = sizeMap[this.options.size] || sizeMap.medium;
        Object.assign(this.modal.style, size);

        if (this.options.maxWidth) {
            this.modal.style.maxWidth = this.options.maxWidth;
        }
        if (this.options.maxHeight) {
            this.modal.style.maxHeight = this.options.maxHeight;
        }
    }

    createModalContent() {
        const content = document.createElement('div');
        content.className = 'modal-content';
        content.style.cssText = `
            background: var(--surface-color, #ffffff);
            border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        `;

        // 创建头部
        if (this.options.headerContent || this.options.showCloseButton) {
            const header = this.createHeader();
            content.appendChild(header);
        }

        // 创建主体
        const body = this.createBody();
        content.appendChild(body);

        // 创建底部
        if (this.options.footerContent) {
            const footer = this.createFooter();
            content.appendChild(footer);
        }

        this.modal.appendChild(content);
    }

    createHeader() {
        const header = document.createElement('div');
        header.className = 'modal-header';
        header.style.cssText = `
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        `;

        if (this.options.headerContent) {
            const title = document.createElement('h3');
            title.className = 'modal-title';
            title.style.cssText = `
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--text-color, #1f2937);
            `;
            
            if (typeof this.options.headerContent === 'string') {
                title.textContent = this.options.headerContent;
            } else {
                title.appendChild(this.options.headerContent);
            }
            
            header.appendChild(title);
        }

        if (this.options.showCloseButton) {
            const closeButton = document.createElement('button');
            closeButton.className = 'modal-close';
            closeButton.setAttribute('aria-label', '关闭');
            closeButton.style.cssText = `
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                padding: 4px;
                color: var(--text-muted, #6b7280);
                transition: color 0.2s ease;
                line-height: 1;
            `;
            closeButton.innerHTML = '×';
            closeButton.addEventListener('click', () => this.hide());
            header.appendChild(closeButton);
        }

        if (this.options.draggable) {
            header.style.cursor = 'move';
            header.setAttribute('data-modal-handle', '');
        }

        return header;
    }

    createBody() {
        const body = document.createElement('div');
        body.className = 'modal-body';
        body.style.cssText = `
            padding: 24px;
            flex: 1;
            overflow-y: auto;
            ${this.options.autoHeight ? 'height: auto;' : ''}
        `;

        if (this.options.bodyContent) {
            if (typeof this.options.bodyContent === 'string') {
                body.innerHTML = this.options.bodyContent;
            } else {
                body.appendChild(this.options.bodyContent);
            }
        }

        // 如果原始元素有内容，移动到模态框中
        if (this.element && this.element.innerHTML.trim()) {
            body.innerHTML = this.element.innerHTML;
        }

        return body;
    }

    createFooter() {
        const footer = document.createElement('div');
        footer.className = 'modal-footer';
        footer.style.cssText = `
            padding: 16px 24px;
            border-top: 1px solid var(--border-color, #e5e7eb);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-shrink: 0;
        `;

        if (typeof this.options.footerContent === 'string') {
            footer.innerHTML = this.options.footerContent;
        } else {
            footer.appendChild(this.options.footerContent);
        }

        return footer;
    }

    addQuantumEffects() {
        this.modal.classList.add('quantum-modal');
        
        // 添加量子粒子效果
        const particles = document.createElement('div');
        particles.className = 'quantum-particles';
        particles.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: -1;
        `;

        // 创建粒子
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: absolute;
                width: 2px;
                height: 2px;
                background: var(--quantum-color, #3b82f6);
                border-radius: 50%;
                animation: quantumFloat ${3 + Math.random() * 2}s infinite linear;
                opacity: ${0.3 + Math.random() * 0.4};
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
            `;
            particles.appendChild(particle);
        }

        this.modal.querySelector('.modal-content').appendChild(particles);

        // 添加量子动画样式
        if (!document.getElementById('quantum-modal-styles')) {
            const style = document.createElement('style');
            style.id = 'quantum-modal-styles';
            style.textContent = `
                @keyframes quantumFloat {
                    0%, 100% { transform: translateY(0) rotate(0deg); }
                    33% { transform: translateY(-10px) rotate(120deg); }
                    66% { transform: translateY(5px) rotate(240deg); }
                }
                
                .quantum-modal {
                    border: 1px solid rgba(59, 130, 246, 0.3);
                    box-shadow: 0 0 30px rgba(59, 130, 246, 0.2);
                }
                
                .quantum-backdrop {
                    background: linear-gradient(45deg, 
                        rgba(59, 130, 246, 0.1), 
                        rgba(147, 51, 234, 0.1)
                    ) !important;
                }
            `;
            document.head.appendChild(style);
        }
    }

    makeDraggable() {
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;

        const handle = this.modal.querySelector('[data-modal-handle]') || this.modal;

        handle.addEventListener('mousedown', (e) => {
            if (e.target.closest('.modal-close')) return;
            
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            
            if (e.target === handle || handle.contains(e.target)) {
                isDragging = true;
                this.modal.style.cursor = 'grabbing';
            }
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
                
                xOffset = currentX;
                yOffset = currentY;
                
                this.modal.style.transform = `translate(calc(-50% + ${currentX}px), calc(-50% + ${currentY}px)) scale(1)`;
            }
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                this.modal.style.cursor = '';
            }
        });
    }

    bindEvents() {
        // 键盘事件
        if (this.options.keyboard) {
            document.addEventListener('keydown', (e) => {
                if (this.isVisible && e.key === 'Escape') {
                    this.hide();
                }
            });
        }

        // 背景点击关闭
        if (this.backdrop && this.options.closeOnOutsideClick) {
            this.backdrop.addEventListener('click', () => this.hide());
        }

        // 焦点管理
        document.addEventListener('keydown', (e) => {
            if (this.isVisible && e.key === 'Tab') {
                this.handleTabKey(e);
            }
        });
    }

    handleTabKey(e) {
        const focusableElements = this.modal.querySelectorAll(
            'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                lastElement.focus();
                e.preventDefault();
            }
        } else {
            if (document.activeElement === lastElement) {
                firstElement.focus();
                e.preventDefault();
            }
        }
    }

    show() {
        if (this.isVisible) return;

        this.isVisible = true;
        this.previouslyFocused = document.activeElement;

        // 添加到DOM
        if (this.backdrop) {
            document.body.appendChild(this.backdrop);
        }
        document.body.appendChild(this.modal);

        // 禁用页面滚动
        document.body.style.overflow = 'hidden';

        // 显示动画
        requestAnimationFrame(() => {
            if (this.backdrop) {
                this.backdrop.style.opacity = '1';
            }
            
            this.modal.style.opacity = '1';
            this.modal.style.transform = this.options.draggable ? 
                'translate(-50%, -50%) scale(1)' : 
                'translate(-50%, -50%) scale(1)';
        });

        // 设置焦点
        if (this.options.focus) {
            setTimeout(() => {
                const firstFocusable = this.modal.querySelector(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                if (firstFocusable) {
                    firstFocusable.focus();
                }
            }, 100);
        }

        // 触发事件
        this.emit('show');
    }

    hide() {
        if (!this.isVisible) return;

        this.isVisible = false;

        // 隐藏动画
        this.modal.style.opacity = '0';
        this.modal.style.transform = 'translate(-50%, -50%) scale(0.7)';
        
        if (this.backdrop) {
            this.backdrop.style.opacity = '0';
        }

        // 移除元素
        setTimeout(() => {
            if (this.backdrop && this.backdrop.parentNode) {
                this.backdrop.parentNode.removeChild(this.backdrop);
            }
            if (this.modal.parentNode) {
                this.modal.parentNode.removeChild(this.modal);
            }

            // 恢复页面滚动
            document.body.style.overflow = '';

            // 恢复焦点
            if (this.previouslyFocused) {
                this.previouslyFocused.focus();
            }

            // 触发事件
            this.emit('hidden');
        }, 300);

        // 触发事件
        this.emit('hide');
    }

    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }

    setContent(content) {
        const body = this.modal.querySelector('.modal-body');
        if (body) {
            if (typeof content === 'string') {
                body.innerHTML = content;
            } else {
                body.innerHTML = '';
                body.appendChild(content);
            }
        }
    }

    setTitle(title) {
        const titleElement = this.modal.querySelector('.modal-title');
        if (titleElement) {
            titleElement.textContent = title;
        }
    }

    emit(eventName, data = {}) {
        const event = new CustomEvent(`modal:${eventName}`, {
            detail: { modal: this, ...data }
        });
        this.element.dispatchEvent(event);
        document.dispatchEvent(event);
    }

    destroy() {
        this.hide();
        setTimeout(() => {
            if (this.element) {
                this.element.removeAttribute('data-modal-initialized');
            }
        }, 350);
    }

    // 静态方法
    static create(options = {}) {
        const element = document.createElement('div');
        return new AlingModalComponent(element, options);
    }

    static confirm(message, options = {}) {
        return new Promise((resolve) => {
            const modal = AlingModalComponent.create({
                size: 'small',
                headerContent: options.title || '确认',
                bodyContent: `<p style="margin: 0; color: var(--text-color, #374151);">${message}</p>`,
                footerContent: `
                    <button class="btn btn-secondary" data-action="cancel">取消</button>
                    <button class="btn btn-primary" data-action="confirm">确认</button>
                `,
                ...options
            });

            modal.modal.addEventListener('click', (e) => {
                const action = e.target.getAttribute('data-action');
                if (action === 'confirm') {
                    resolve(true);
                    modal.hide();
                } else if (action === 'cancel') {
                    resolve(false);
                    modal.hide();
                }
            });

            modal.show();
        });
    }

    static alert(message, options = {}) {
        return new Promise((resolve) => {
            const modal = AlingModalComponent.create({
                size: 'small',
                headerContent: options.title || '提示',
                bodyContent: `<p style="margin: 0; color: var(--text-color, #374151);">${message}</p>`,
                footerContent: `<button class="btn btn-primary" data-action="ok">确定</button>`,
                ...options
            });

            modal.modal.addEventListener('click', (e) => {
                if (e.target.getAttribute('data-action') === 'ok') {
                    resolve();
                    modal.hide();
                }
            });

            modal.show();
        });
    }
}

// 导出组件
window.AlingModalComponent = AlingModalComponent;

// 全局工具函数
window.showModal = (options) => AlingModalComponent.create(options);
window.confirmDialog = (message, options) => AlingModalComponent.confirm(message, options);
window.alertDialog = (message, options) => AlingModalComponent.alert(message, options);

console.log('✅ AlingAi 模态框组件已加载');
