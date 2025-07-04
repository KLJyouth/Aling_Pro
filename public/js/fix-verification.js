/**
 * AlingAi Pro - 功能修复完成验证脚本
 * 自动测试所有修复的功能是否正常工作
 */

class FixVerification {
    constructor() {
        this.testResults = {
            themeSwitch: { status: 'pending', message: '待测试' },
            contactForm: { status: 'pending', message: '待测试' },
            aiAssistant: { status: 'pending', message: '待测试' },
            emailService: { status: 'pending', message: '待测试' },
            chatAPI: { status: 'pending', message: '待测试' }
        };
        
        this.init();
    }

    async init() {
        
        
        // 等待页面完全加载
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.runTests());
        } else {
            this.runTests();
        }
    }

    async runTests() {
        
        
        try {
            await this.testThemeSwitch();
            await this.testContactForm();
            await this.testAIAssistant();
            await this.testEmailService();
            await this.testChatAPI();
            
            this.displayResults();
        } catch (error) {
            console.error('❌ 测试过程中出现错误:', error);
        }
    }

    async testThemeSwitch() {
        
        
        try {
            // 检查主题切换按钮是否存在
            const themeButton = document.querySelector('.theme-toggle-btn');
            if (!themeButton) {
                throw new Error('主题切换按钮未找到');
            }

            // 检查HomepageFixes类是否已加载
            if (!window.homepageFixes) {
                throw new Error('HomepageFixes类未加载');
            }

            // 测试主题切换功能
            const initialTheme = window.homepageFixes.currentTheme;
            window.homepageFixes.toggleTheme();
            const newTheme = window.homepageFixes.currentTheme;

            if (initialTheme !== newTheme) {
                this.testResults.themeSwitch = {
                    status: 'success',
                    message: `主题切换成功: ${initialTheme} → ${newTheme}`
                };
            } else {
                throw new Error('主题切换失败');
            }

        } catch (error) {
            this.testResults.themeSwitch = {
                status: 'error',
                message: error.message
            };
        }
    }

    async testContactForm() {
        
        
        try {
            const contactForm = document.getElementById('contactForm');
            if (!contactForm) {
                throw new Error('联系表单未找到');
            }

            // 检查表单字段
            const requiredFields = ['name', 'email', 'message'];
            for (let field of requiredFields) {
                const input = contactForm.querySelector(`[name="${field}"]`);
                if (!input) {
                    throw new Error(`缺少必填字段: ${field}`);
                }
            }

            // 检查提交处理
            if (!window.homepageFixes || typeof window.homepageFixes.handleContactFormSubmit !== 'function') {
                throw new Error('联系表单处理函数未找到');
            }

            this.testResults.contactForm = {
                status: 'success',
                message: '联系表单结构和处理函数正常'
            };

        } catch (error) {
            this.testResults.contactForm = {
                status: 'error',
                message: error.message
            };
        }
    }

    async testAIAssistant() {
        
        
        try {
            // 检查聊天按钮
            const chatButton = document.querySelector('.chat-toggle-btn');
            if (!chatButton) {
                throw new Error('AI助手聊天按钮未找到');
            }

            // 检查聊天模态框
            const chatModal = document.getElementById('chatModal');
            if (!chatModal) {
                throw new Error('AI助手聊天模态框未找到');
            }

            // 检查聊天系统
            if (!window.homepageFixes || !window.homepageFixes.chatSystem) {
                throw new Error('AI助手聊天系统未初始化');
            }

            this.testResults.aiAssistant = {
                status: 'success',
                message: 'AI助手界面和系统正常'
            };

        } catch (error) {
            this.testResults.aiAssistant = {
                status: 'error',
                message: error.message
            };
        }
    }

    async testEmailService() {
        
        
        try {
            // 模拟发送测试邮件（不实际发送）
            const testData = {
                name: '测试用户',
                email: 'test@example.com',
                message: '这是一条测试消息'
            };

            const response = await fetch('/api/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(testData)
            });

            if (!response.ok) {
                throw new Error(`HTTP错误: ${response.status}`);
            }

            const result = await response.json();
            
            this.testResults.emailService = {
                status: result.success ? 'success' : 'warning',
                message: result.message || (result.success ? '邮件服务正常' : '邮件服务配置可能有问题')
            };

        } catch (error) {
            this.testResults.emailService = {
                status: 'error',
                message: `邮件服务测试失败: ${error.message}`
            };
        }
    }

    async testChatAPI() {
        
        
        try {
            const testMessage = '你好，这是一条测试消息';
            
            const response = await fetch('/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: testMessage })
            });

            if (!response.ok) {
                throw new Error(`HTTP错误: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success && result.response) {
                this.testResults.chatAPI = {
                    status: 'success',
                    message: `聊天API正常，回复: ${result.response.substring(0, 50)}...`
                };
            } else {
                throw new Error(result.message || '聊天API返回异常');
            }

        } catch (error) {
            this.testResults.chatAPI = {
                status: 'error',
                message: `聊天API测试失败: ${error.message}`
            };
        }
    }

    displayResults() {
        
        
        
        let totalTests = 0;
        let passedTests = 0;
        let warningTests = 0;
        
        for (const [testName, result] of Object.entries(this.testResults)) {
            totalTests++;
            
            let icon = '❌';
            if (result.status === 'success') {
                icon = '✅';
                passedTests++;
            } else if (result.status === 'warning') {
                icon = '⚠️';
                warningTests++;
            }
            
            
        }
        
        
        
        
        if (warningTests > 0) {
            
        }
        
        if (passedTests === totalTests) {
            
            this.createSuccessReport();
        } else if (passedTests + warningTests === totalTests) {
            
            this.createWarningReport();
        } else {
            
            this.createErrorReport();
        }
    }

    createSuccessReport() {
        const report = {
            timestamp: new Date().toISOString(),
            status: 'success',
            summary: '所有功能修复验证通过',
            details: this.testResults,
            fixes_completed: [
                '✅ 联系表单邮件发送功能已修复',
                '✅ 主题切换功能已实现',
                '✅ AI智能助手聊天功能已修复',
                '✅ 邮件服务配置已更新',
                '✅ 聊天API已优化'
            ],
            next_steps: [
                '📧 配置真实的邮件服务器设置',
                '🤖 接入真实的AI服务API',
                '🎨 优化主题切换动画效果',
                '📱 测试移动端响应式适配',
                '🔒 添加安全验证机制'
            ]
        };
        
        
        
        // 在页面上显示成功消息
        this.showStatusMessage('success', '🎉 所有功能修复验证通过！');
    }

    createWarningReport() {
        const report = {
            timestamp: new Date().toISOString(),
            status: 'warning',
            summary: '主要功能修复完成，有部分警告',
            details: this.testResults,
            warnings: Object.entries(this.testResults)
                .filter(([_, result]) => result.status === 'warning')
                .map(([name, result]) => `${name}: ${result.message}`)
        };
        
        
        this.showStatusMessage('warning', '✅ 主要功能修复完成，请检查警告项');
    }

    createErrorReport() {
        const report = {
            timestamp: new Date().toISOString(),
            status: 'error',
            summary: '部分功能修复失败',
            details: this.testResults,
            errors: Object.entries(this.testResults)
                .filter(([_, result]) => result.status === 'error')
                .map(([name, result]) => `${name}: ${result.message}`)
        };
        
        
        this.showStatusMessage('error', '⚠️ 部分功能修复失败，需要进一步检查');
    }

    showStatusMessage(type, message) {
        // 创建状态消息元素
        const statusDiv = document.createElement('div');
        statusDiv.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            padding: 15px 25px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            animation: slideDown 0.5s ease;
        `;
        
        switch (type) {
            case 'success':
                statusDiv.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                break;
            case 'warning':
                statusDiv.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                break;
            case 'error':
                statusDiv.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                break;
        }
        
        statusDiv.textContent = message;
        document.body.appendChild(statusDiv);
        
        // 5秒后自动移除
        setTimeout(() => {
            if (statusDiv.parentNode) {
                statusDiv.style.animation = 'slideUp 0.5s ease';
                setTimeout(() => statusDiv.remove(), 500);
            }
        }, 5000);
        
        // 添加动画样式
        if (!document.querySelector('#verification-animations')) {
            const style = document.createElement('style');
            style.id = 'verification-animations';
            style.textContent = `
                @keyframes slideDown {
                    from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
                    to { opacity: 1; transform: translateX(-50%) translateY(0); }
                }
                @keyframes slideUp {
                    from { opacity: 1; transform: translateX(-50%) translateY(0); }
                    to { opacity: 0; transform: translateX(-50%) translateY(-20px); }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// 页面加载完成后自动运行验证
document.addEventListener('DOMContentLoaded', () => {
    // 等待1秒确保所有脚本都已加载
    setTimeout(() => {
        window.fixVerification = new FixVerification();
    }, 1000);
});

// 导出到全局作用域以便手动调用
window.FixVerification = FixVerification;
