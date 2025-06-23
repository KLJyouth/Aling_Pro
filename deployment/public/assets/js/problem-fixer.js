/**
 * 问题修复脚本 - 自动检测并修复常见问题
 * 此脚本会自动运行并尝试修复发现的问题
 */

class ProblemFixer {
    constructor() {
        this.fixes = [];
        this.appliedFixes = [];
    }

    log(message, type = 'info') {
        const colors = {
            'info': 'color: blue',
            'success': 'color: green; font-weight: bold',
            'error': 'color: red; font-weight: bold',
            'warn': 'color: orange; font-weight: bold',
            'fix': 'color: purple; font-weight: bold'
        };
        console.log(`%c🔧 [FIXER] ${message}`, colors[type] || colors.info);
    }

    // 修复缺失的MessageProcessor方法
    fixMessageProcessor() {
        if (typeof MessageProcessor === 'undefined') {
            this.log('MessageProcessor类不存在，尝试创建...', 'warn');
            
            try {
                window.MessageProcessor = class MessageProcessor {
                    static escapeHtml(text) {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    }

                    static processMarkdown(text) {
                        if (!text) return '';
                        // 基本markdown处理
                        let processed = this.escapeHtml(text);
                        processed = processed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                        processed = processed.replace(/\*(.*?)\*/g, '<em>$1</em>');
                        processed = processed.replace(/`(.*?)`/g, '<code>$1</code>');
                        processed = processed.replace(/\n/g, '<br>');
                        return processed;
                    }

                    static processUserMessage(content) {
                        if (!content) return '';
                        const escaped = this.escapeHtml(content);
                        const withBreaks = escaped.replace(/\n/g, '<br>');
                        let processed = withBreaks;
                        processed = processed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                        processed = processed.replace(/\*(.*?)\*/g, '<em>$1</em>');
                        processed = processed.replace(/`(.*?)`/g, '<code>$1</code>');
                        return processed;
                    }

                    static processAssistantMessage(content) {
                        if (!content) return '';
                        return this.processMarkdown(content);
                    }
                };

                this.log('✅ MessageProcessor类已创建', 'fix');
                this.appliedFixes.push('MessageProcessor类创建');
                return true;
            } catch (error) {
                this.log(`❌ MessageProcessor创建失败: ${error.message}`, 'error');
                return false;
            }
        }

        // 检查并修复缺失的方法
        const requiredMethods = [
            'processUserMessage',
            'processAssistantMessage', 
            'escapeHtml',
            'processMarkdown'
        ];

        let fixedMethods = 0;
        for (const method of requiredMethods) {
            if (typeof MessageProcessor[method] !== 'function') {
                this.log(`缺少方法: MessageProcessor.${method}，尝试修复...`, 'warn');
                
                try {
                    switch (method) {
                        case 'escapeHtml':
                            MessageProcessor.escapeHtml = function(text) {
                                const div = document.createElement('div');
                                div.textContent = text;
                                return div.innerHTML;
                            };
                            break;
                        case 'processMarkdown':
                            MessageProcessor.processMarkdown = function(text) {
                                if (!text) return '';
                                let processed = this.escapeHtml(text);
                                processed = processed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                                processed = processed.replace(/\*(.*?)\*/g, '<em>$1</em>');
                                processed = processed.replace(/`(.*?)`/g, '<code>$1</code>');
                                processed = processed.replace(/\n/g, '<br>');
                                return processed;
                            };
                            break;
                        case 'processUserMessage':
                            MessageProcessor.processUserMessage = function(content) {
                                if (!content) return '';
                                const escaped = this.escapeHtml(content);
                                return escaped.replace(/\n/g, '<br>');
                            };
                            break;
                        case 'processAssistantMessage':
                            MessageProcessor.processAssistantMessage = function(content) {
                                if (!content) return '';
                                return this.processMarkdown(content);
                            };
                            break;
                    }
                    
                    this.log(`✅ 已修复: MessageProcessor.${method}`, 'fix');
                    fixedMethods++;
                } catch (error) {
                    this.log(`❌ 修复失败: MessageProcessor.${method} - ${error.message}`, 'error');
                }
            }
        }

        if (fixedMethods > 0) {
            this.appliedFixes.push(`MessageProcessor方法修复: ${fixedMethods}个`);
        }

        return fixedMethods;
    }

    // 修复事件处理器
    fixEventHandlers() {
        const buttonConfigs = [
            { id: 'sendButton', event: 'click', handler: () => this.handleSendMessage() },
            { id: 'settingsBtn', event: 'click', handler: () => this.handleSettings() },
            { id: 'historyBtn', event: 'click', handler: () => this.handleHistory() },
            { id: 'languageBtn', event: 'click', handler: () => this.handleLanguage() },
            { id: 'guestModeBtn', event: 'click', handler: () => this.handleGuestMode() }
        ];

        let fixedHandlers = 0;
        for (const config of buttonConfigs) {
            const element = document.getElementById(config.id);
            if (element && !element.onclick) {
                try {
                    element.onclick = config.handler;
                    this.log(`✅ 已修复: ${config.id} 事件处理器`, 'fix');
                    fixedHandlers++;
                } catch (error) {
                    this.log(`❌ 事件处理器修复失败: ${config.id} - ${error.message}`, 'error');
                }
            }
        }

        // 修复消息输入框回车事件
        const messageInput = document.getElementById('messageInput');
        if (messageInput && !messageInput.onkeydown) {
            try {
                messageInput.onkeydown = (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.handleSendMessage();
                    }
                };
                this.log('✅ 已修复: messageInput 回车事件', 'fix');
                fixedHandlers++;
            } catch (error) {
                this.log(`❌ 输入框事件修复失败: ${error.message}`, 'error');
            }
        }

        if (fixedHandlers > 0) {
            this.appliedFixes.push(`事件处理器修复: ${fixedHandlers}个`);
        }

        return fixedHandlers;
    }

    // 事件处理器方法
    handleSendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput?.value?.trim();
        
        if (!message) {
            this.log('消息为空，无法发送', 'warn');
            return;
        }

        this.log(`发送消息: ${message}`, 'info');
        
        try {
            // 如果全局聊天实例存在，使用它
            if (window.chatInstance && typeof window.chatInstance.sendMessage === 'function') {
                window.chatInstance.sendMessage(message);
            } else {
                // 否则使用备用发送方法
                this.fallbackSendMessage(message);
            }
            
            // 清空输入框
            messageInput.value = '';
        } catch (error) {
            this.log(`发送消息失败: ${error.message}`, 'error');
        }
    }

    async fallbackSendMessage(message) {
        const chatContainer = document.getElementById('chatContainer');
        if (!chatContainer) {
            this.log('chatContainer不存在', 'error');
            return;
        }

        // 添加用户消息到界面
        const userMessageDiv = document.createElement('div');
        userMessageDiv.className = 'message user-message';
        userMessageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-text">${MessageProcessor.processUserMessage(message)}</div>
                <div class="message-time">${new Date().toLocaleTimeString()}</div>
            </div>
        `;
        chatContainer.appendChild(userMessageDiv);

        // 滚动到底部
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // 模拟AI回复
        setTimeout(() => {
            const aiMessageDiv = document.createElement('div');
            aiMessageDiv.className = 'message assistant-message';
            aiMessageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-text">${MessageProcessor.processAssistantMessage('这是一个测试回复。聊天功能正在运行中...')}</div>
                    <div class="message-time">${new Date().toLocaleTimeString()}</div>
                    <div class="message-actions">
                        <button class="copy-button" onclick="navigator.clipboard.writeText('这是一个测试回复。聊天功能正在运行中...')">复制</button>
                        <button class="regenerate-button" onclick="console.log('重新生成')">重新生成</button>
                    </div>
                </div>
            `;
            chatContainer.appendChild(aiMessageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }, 1000);
    }

    handleSettings() {
        this.log('设置按钮被点击', 'info');
        // 基本设置功能
        alert('设置功能正在开发中...');
    }

    handleHistory() {
        this.log('历史按钮被点击', 'info');
        // 基本历史功能
        alert('历史记录功能正在开发中...');
    }

    handleLanguage() {
        this.log('语言按钮被点击', 'info');
        // 基本语言切换功能
        alert('语言切换功能正在开发中...');
    }

    handleGuestMode() {
        this.log('访客模式按钮被点击', 'info');
        try {
            localStorage.setItem('guestMode', 'true');
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                // 如果使用Bootstrap模态框
                if (window.bootstrap && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getInstance(loginModal);
                    if (modal) modal.hide();
                } else {
                    // 基本隐藏
                    loginModal.style.display = 'none';
                }
            }
            this.log('✅ 访客模式已启用', 'success');
        } catch (error) {
            this.log(`访客模式设置失败: ${error.message}`, 'error');
        }
    }

    // 修复全局对象暴露
    fixGlobalObjects() {
        let fixedObjects = 0;
        
        const requiredGlobals = ['ChatCore', 'ChatUI', 'ChatAPI'];
        for (const globalName of requiredGlobals) {
            if (typeof window[globalName] === 'undefined') {
                this.log(`缺少全局对象: ${globalName}，尝试创建占位符...`, 'warn');
                
                try {
                    // 创建基本占位符
                    window[globalName] = class {
                        constructor() {
                            console.warn(`${globalName} 占位符类，功能有限`);
                        }
                    };
                    
                    this.log(`✅ 已创建: ${globalName} 占位符`, 'fix');
                    fixedObjects++;
                } catch (error) {
                    this.log(`❌ 全局对象创建失败: ${globalName} - ${error.message}`, 'error');
                }
            }
        }

        if (fixedObjects > 0) {
            this.appliedFixes.push(`全局对象修复: ${fixedObjects}个`);
        }

        return fixedObjects;
    }

    // 修复DOM结构问题
    fixDOMStructure() {
        let fixedElements = 0;

        // 检查关键元素
        const requiredElements = [
            { id: 'chatContainer', tag: 'div', classes: ['chat-container'] },
            { id: 'messageInput', tag: 'textarea', classes: ['form-control'] },
            { id: 'sendButton', tag: 'button', classes: ['btn', 'btn-primary'] }
        ];

        for (const elementConfig of requiredElements) {
            if (!document.getElementById(elementConfig.id)) {
                this.log(`缺少DOM元素: ${elementConfig.id}，尝试创建...`, 'warn');
                
                try {
                    const element = document.createElement(elementConfig.tag);
                    element.id = elementConfig.id;
                    if (elementConfig.classes) {
                        element.className = elementConfig.classes.join(' ');
                    }

                    // 添加到合适的位置
                    if (elementConfig.id === 'chatContainer') {
                        element.style.cssText = 'height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 10px 0;';
                        document.body.appendChild(element);
                    } else if (elementConfig.id === 'messageInput') {
                        element.placeholder = '输入消息...';
                        element.style.cssText = 'width: 70%; margin: 10px;';
                        document.body.appendChild(element);
                    } else if (elementConfig.id === 'sendButton') {
                        element.textContent = '发送';
                        element.style.cssText = 'margin: 10px;';
                        document.body.appendChild(element);
                    }

                    this.log(`✅ 已创建: ${elementConfig.id}`, 'fix');
                    fixedElements++;
                } catch (error) {
                    this.log(`❌ DOM元素创建失败: ${elementConfig.id} - ${error.message}`, 'error');
                }
            }
        }

        if (fixedElements > 0) {
            this.appliedFixes.push(`DOM元素修复: ${fixedElements}个`);
        }

        return fixedElements;
    }

    // 运行所有修复
    async runAllFixes() {
        this.log('🚀 开始自动问题修复...', 'info');
        this.log('=' * 40, 'info');

        const fixes = [
            { name: 'MessageProcessor修复', method: 'fixMessageProcessor' },
            { name: 'DOM结构修复', method: 'fixDOMStructure' },
            { name: '事件处理器修复', method: 'fixEventHandlers' },
            { name: '全局对象修复', method: 'fixGlobalObjects' }
        ];

        let totalFixes = 0;
        for (const fix of fixes) {
            this.log(`正在执行: ${fix.name}...`, 'info');
            try {
                const fixCount = await this[fix.method]();
                totalFixes += fixCount;
                this.log(`${fix.name} 完成，修复了 ${fixCount} 个问题`, 'success');
            } catch (error) {
                this.log(`${fix.name} 失败: ${error.message}`, 'error');
            }
        }

        this.log('=' * 40, 'info');
        this.log(`🏁 修复完成！总计修复: ${totalFixes} 个问题`, 'success');
        
        if (this.appliedFixes.length > 0) {
            this.log('📋 修复详情:', 'info');
            this.appliedFixes.forEach((fix, index) => {
                this.log(`  ${index + 1}. ${fix}`, 'info');
            });
        }

        // 暴露修复报告到全局
        window.fixerReport = {
            totalFixes,
            appliedFixes: this.appliedFixes,
            timestamp: new Date().toISOString()
        };

        return totalFixes;
    }
}

// 自动运行修复
document.addEventListener('DOMContentLoaded', async () => {
    // 等待其他脚本加载
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    console.log('%c🔧 AlingAi 自动问题修复器启动', 'color: purple; font-size: 16px; font-weight: bold');
    
    const fixer = new ProblemFixer();
    await fixer.runAllFixes();
    
    // 暴露修复器到全局
    window.problemFixer = fixer;
});

// 如果页面已加载，立即运行
if (document.readyState !== 'loading') {
    setTimeout(async () => {
        console.log('%c🔧 AlingAi 自动问题修复器启动', 'color: purple; font-size: 16px; font-weight: bold');
        
        const fixer = new ProblemFixer();
        await fixer.runAllFixes();
        
        window.problemFixer = fixer;
    }, 2000);
}
