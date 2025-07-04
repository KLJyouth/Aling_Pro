/**
 * 实时监控脚本 - 持续监控聊天系统状态
 * 用于实时发现和报告问题
 */

class RealTimeMonitor {
    constructor() {
        this.isMonitoring = false;
        this.monitorInterval = null;
        this.errorCount = 0;
        this.lastErrors = [];
        this.statusHistory = [];
    }

    startMonitoring() {
        if (this.isMonitoring) {
            console.log('⚠️ 监控已在运行中');
            return;
        }

        console.log('🚀 开始实时监控AlingAi聊天系统...');
        this.isMonitoring = true;
        
        // 监控控制台错误
        this.setupErrorCapture();
        
        // 定期检查系统状态
        this.monitorInterval = setInterval(() => {
            this.checkSystemStatus();
        }, 2000);

        // 监控DOM变化
        this.setupDOMObserver();
        
        console.log('✅ 监控系统已启动');
    }

    stopMonitoring() {
        if (!this.isMonitoring) return;
        
        this.isMonitoring = false;
        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
        }
        console.log('⏹️ 监控已停止');
    }

    setupErrorCapture() {
        // 保存原始的错误处理方法
        const originalError = window.console.error;
        const originalWarn = window.console.warn;
        
        window.console.error = (...args) => {
            this.errorCount++;
            const errorMsg = args.join(' ');
            this.lastErrors.push({
                type: 'error',
                message: errorMsg,
                timestamp: new Date().toISOString()
            });
            
            // 保持错误历史在合理范围内
            if (this.lastErrors.length > 10) {
                this.lastErrors.shift();
            }
            
            console.log(`%c🚨 [ERROR #${this.errorCount}] ${errorMsg}`, 'color: red; font-weight: bold');
            originalError.apply(console, args);
        };

        window.console.warn = (...args) => {
            const warnMsg = args.join(' ');
            console.log(`%c⚠️ [WARN] ${warnMsg}`, 'color: orange; font-weight: bold');
            originalWarn.apply(console, args);
        };

        // 监控未捕获的错误
        window.addEventListener('error', (event) => {
            this.errorCount++;
            const errorInfo = {
                type: 'uncaught',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                timestamp: new Date().toISOString()
            };
            
            this.lastErrors.push(errorInfo);
            console.log(`%c💥 [UNCAUGHT ERROR] ${event.message} at ${event.filename}:${event.lineno}`, 
                       'color: red; font-weight: bold; background: yellow');
        });
    }

    checkSystemStatus() {
        const status = {
            timestamp: new Date().toISOString(),
            chatContainer: !!document.getElementById('chatContainer'),
            messageInput: !!document.getElementById('messageInput'),
            sendButton: !!document.getElementById('sendButton'),
            messageCount: document.querySelectorAll('.message').length,
            hasGlobalChatCore: typeof window.ChatCore !== 'undefined',
            hasGlobalChatUI: typeof window.ChatUI !== 'undefined',
            hasGlobalChatAPI: typeof window.ChatAPI !== 'undefined',
            hasMessageProcessor: typeof window.MessageProcessor !== 'undefined',
            hasMessageRenderer: typeof window.MessageRenderer !== 'undefined',
            errorCount: this.errorCount,
            recentErrors: this.lastErrors.slice(-3)
        };

        // 检查关键功能
        if (typeof window.MessageProcessor !== 'undefined') {
            status.processorMethods = {
                processUserMessage: typeof MessageProcessor.processUserMessage === 'function',
                processAssistantMessage: typeof MessageProcessor.processAssistantMessage === 'function',
                escapeHtml: typeof MessageProcessor.escapeHtml === 'function'
            };
        }

        this.statusHistory.push(status);
        if (this.statusHistory.length > 20) {
            this.statusHistory.shift();
        }

        // 报告状态变化
        this.reportStatusChanges(status);
        
        // 暴露状态到全局
        window.currentSystemStatus = status;
    }

    reportStatusChanges(currentStatus) {
        const previousStatus = this.statusHistory[this.statusHistory.length - 2];
        if (!previousStatus) return;

        // 检查消息数量变化
        if (currentStatus.messageCount !== previousStatus.messageCount) {
            console.log(`%c📝 消息数量变化: ${previousStatus.messageCount} → ${currentStatus.messageCount}`, 
                       'color: blue; font-weight: bold');
        }

        // 检查错误增加
        if (currentStatus.errorCount > previousStatus.errorCount) {
            console.log(`%c🚨 新错误发生! 总计: ${currentStatus.errorCount}`, 
                       'color: red; font-weight: bold');
            if (currentStatus.recentErrors.length > 0) {
                const latestError = currentStatus.recentErrors[currentStatus.recentErrors.length - 1];
                console.log(`%c最新错误: ${latestError.message}`, 'color: red');
            }
        }

        // 检查关键组件丢失
        const criticalComponents = ['chatContainer', 'hasGlobalChatCore', 'hasMessageProcessor'];
        for (const component of criticalComponents) {
            if (previousStatus[component] && !currentStatus[component]) {
                console.log(`%c🔥 关键组件丢失: ${component}`, 'color: red; font-weight: bold; background: yellow');
            } else if (!previousStatus[component] && currentStatus[component]) {
                console.log(`%c✅ 关键组件恢复: ${component}`, 'color: green; font-weight: bold');
            }
        }
    }

    setupDOMObserver() {
        const chatContainer = document.getElementById('chatContainer');
        if (!chatContainer) {
            console.log('⚠️ chatContainer不存在，无法设置DOM观察器');
            return;
        }

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE && node.classList?.contains('message')) {
                            console.log(`%c➕ 新消息添加到DOM`, 'color: green');
                            this.validateNewMessage(node);
                        }
                    });
                }
            });
        });

        observer.observe(chatContainer, {
            childList: true,
            subtree: true
        });

        console.log('👁️ DOM观察器已设置');
    }

    validateNewMessage(messageElement) {
        const checks = [
            {
                name: '消息内容',
                test: () => messageElement.textContent && messageElement.textContent.trim().length > 0
            },
            {
                name: '消息类型类',
                test: () => messageElement.classList.contains('user-message') || 
                           messageElement.classList.contains('assistant-message')
            },
            {
                name: '消息结构',
                test: () => messageElement.querySelector('.message-content') !== null
            }
        ];

        let passed = 0;
        checks.forEach(check => {
            if (check.test()) {
                passed++;
                console.log(`%c  ✅ ${check.name}`, 'color: green');
            } else {
                console.log(`%c  ❌ ${check.name}`, 'color: red');
            }
        });

        console.log(`%c消息验证: ${passed}/${checks.length} 通过`, 
                   passed === checks.length ? 'color: green' : 'color: orange');
    }

    generateStatusReport() {
        const currentStatus = this.statusHistory[this.statusHistory.length - 1];
        if (!currentStatus) {
            console.log('❌ 无状态数据可报告');
            return;
        }

        console.log('\n' + '='.repeat(50));
        console.log('📊 AlingAi 实时状态报告');
        console.log('='.repeat(50));
        console.log(`🕒 当前时间: ${new Date().toLocaleString()}`);
        console.log(`💬 消息数量: ${currentStatus.messageCount}`);
        console.log(`🚨 错误计数: ${currentStatus.errorCount}`);
        console.log(`🔧 关键组件状态:`);
        console.log(`  - ChatContainer: ${currentStatus.chatContainer ? '✅' : '❌'}`);
        console.log(`  - MessageInput: ${currentStatus.messageInput ? '✅' : '❌'}`);
        console.log(`  - SendButton: ${currentStatus.sendButton ? '✅' : '❌'}`);
        console.log(`  - ChatCore: ${currentStatus.hasGlobalChatCore ? '✅' : '❌'}`);
        console.log(`  - ChatUI: ${currentStatus.hasGlobalChatUI ? '✅' : '❌'}`);
        console.log(`  - MessageProcessor: ${currentStatus.hasMessageProcessor ? '✅' : '❌'}`);
        
        if (currentStatus.processorMethods) {
            console.log(`🛠️ MessageProcessor方法:`);
            Object.entries(currentStatus.processorMethods).forEach(([method, exists]) => {
                console.log(`  - ${method}: ${exists ? '✅' : '❌'}`);
            });
        }

        if (currentStatus.recentErrors.length > 0) {
            console.log(`\n🚨 最近错误:`);
            currentStatus.recentErrors.forEach((error, index) => {
                console.log(`  ${index + 1}. [${error.type}] ${error.message}`);
            });
        }

        console.log('='.repeat(50));
    }

    // 手动触发测试
    async testMessageSending() {
        console.log('🧪 开始消息发送测试...');
        
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        if (!messageInput || !sendButton) {
            console.log('❌ 缺少必要的UI元素');
            return false;
        }

        const testMessage = `测试消息 - ${new Date().toLocaleTimeString()}`;
        
        // 输入测试消息
        messageInput.value = testMessage;
        console.log(`📝 输入测试消息: ${testMessage}`);
        
        // 记录当前消息数量
        const beforeCount = document.querySelectorAll('.message').length;
        
        // 触发发送
        sendButton.click();
        console.log('🚀 点击发送按钮');
        
        // 等待并检查结果
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const afterCount = document.querySelectorAll('.message').length;
        const success = afterCount > beforeCount;
        
        console.log(`📊 消息数量: ${beforeCount} → ${afterCount}`);
        console.log(`${success ? '✅' : '❌'} 消息发送测试${success ? '成功' : '失败'}`);
        
        return success;
    }
}

// 创建全局监控实例
window.realtimeMonitor = new RealTimeMonitor();

// 页面加载后自动开始监控
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            window.realtimeMonitor.startMonitoring();
            console.log('💡 提示: 使用 realtimeMonitor.generateStatusReport() 查看状态报告');
            console.log('💡 提示: 使用 realtimeMonitor.testMessageSending() 测试消息发送');
        }, 1000);
    });
} else {
    setTimeout(() => {
        window.realtimeMonitor.startMonitoring();
        console.log('💡 提示: 使用 realtimeMonitor.generateStatusReport() 查看状态报告');
        console.log('💡 提示: 使用 realtimeMonitor.testMessageSending() 测试消息发送');
    }, 1000);
}
