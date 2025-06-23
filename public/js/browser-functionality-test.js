/**
 * 浏览器环境功能测试脚本
 * 此脚本用于在实际浏览器环境中测试聊天功能
 */

class BrowserFunctionalityTest {
    constructor() {
        this.testResults = [];
        this.errorCount = 0;
        this.successCount = 0;
    }

    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logMessage = `[${timestamp}] ${message}`;
        
        
        
        this.testResults.push({ message: logMessage, type });
    }

    async sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async testPageLoading() {
        this.log('🧪 测试页面加载状态...', 'info');
        
        try {
            // 检查关键DOM元素
            const chatContainer = document.getElementById('chatContainer');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            
            if (!chatContainer) throw new Error('chatContainer未找到');
            if (!messageInput) throw new Error('messageInput未找到');
            if (!sendButton) throw new Error('sendButton未找到');
            
            this.log('✅ 页面基本元素加载正常', 'success');
            this.successCount++;
            return true;
        } catch (error) {
            this.log(`❌ 页面加载测试失败: ${error.message}`, 'error');
            this.errorCount++;
            return false;
        }
    }

    async testGlobalObjects() {
        this.log('🧪 测试全局对象暴露...', 'info');
        
        try {
            if (typeof window.ChatCore === 'undefined') throw new Error('ChatCore未全局暴露');
            if (typeof window.ChatUI === 'undefined') throw new Error('ChatUI未全局暴露');
            if (typeof window.ChatAPI === 'undefined') throw new Error('ChatAPI未全局暴露');
            
            this.log('✅ 全局对象暴露正常', 'success');
            this.successCount++;
            return true;
        } catch (error) {
            this.log(`❌ 全局对象测试失败: ${error.message}`, 'error');
            this.errorCount++;
            return false;
        }
    }

    async testMessageProcessor() {
        this.log('🧪 测试MessageProcessor功能...', 'info');
        
        try {
            // 确保MessageProcessor类存在
            if (typeof MessageProcessor === 'undefined') {
                throw new Error('MessageProcessor类未定义');
            }
            
            // 测试processUserMessage方法
            if (typeof MessageProcessor.processUserMessage !== 'function') {
                throw new Error('processUserMessage方法未定义');
            }
            
            // 测试processAssistantMessage方法
            if (typeof MessageProcessor.processAssistantMessage !== 'function') {
                throw new Error('processAssistantMessage方法未定义');
            }
            
            // 测试实际处理
            const userMessage = MessageProcessor.processUserMessage('测试用户消息');
            const aiMessage = MessageProcessor.processAssistantMessage('**测试AI消息**');
            
            if (!userMessage || !aiMessage) {
                throw new Error('消息处理返回空值');
            }
            
            this.log('✅ MessageProcessor功能正常', 'success');
            this.successCount++;
            return true;
        } catch (error) {
            this.log(`❌ MessageProcessor测试失败: ${error.message}`, 'error');
            this.errorCount++;
            return false;
        }
    }

    async testUIButtons() {
        this.log('🧪 测试UI按钮事件处理...', 'info');
        
        try {
            const settingsBtn = document.getElementById('settingsBtn');
            const historyBtn = document.getElementById('historyBtn');
            const languageBtn = document.getElementById('languageBtn');
            
            if (!settingsBtn) throw new Error('设置按钮未找到');
            if (!historyBtn) throw new Error('历史按钮未找到');
            if (!languageBtn) throw new Error('语言按钮未找到');
            
            // 检查事件处理器是否已绑定
            const hasSettingsHandler = settingsBtn.onclick !== null;
            const hasHistoryHandler = historyBtn.onclick !== null;
            const hasLanguageHandler = languageBtn.onclick !== null;
            
            if (!hasSettingsHandler) throw new Error('设置按钮事件处理器未绑定');
            if (!hasHistoryHandler) throw new Error('历史按钮事件处理器未绑定');
            if (!hasLanguageHandler) throw new Error('语言按钮事件处理器未绑定');
            
            this.log('✅ UI按钮事件处理正常', 'success');
            this.successCount++;
            return true;
        } catch (error) {
            this.log(`❌ UI按钮测试失败: ${error.message}`, 'error');
            this.errorCount++;
            return false;
        }
    }

    async testAPIConnection() {
        this.log('🧪 测试API连接...', 'info');
        
        try {
            const response = await fetch('/api/status');
            if (!response.ok) throw new Error(`API状态检查失败: ${response.status}`);
            
            const data = await response.json();
            if (!data.status) throw new Error('API状态响应格式错误');
            
            this.log('✅ API连接正常', 'success');
            this.successCount++;
            return true;
        } catch (error) {
            this.log(`❌ API连接测试失败: ${error.message}`, 'error');
            this.errorCount++;
            return false;
        }
    }

    async testSendMessage() {
        this.log('🧪 测试消息发送功能...', 'info');
        
        try {
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            
            // 输入测试消息
            messageInput.value = '这是一条浏览器测试消息';
            
            // 模拟发送
            const sendEvent = new Event('click');
            sendButton.dispatchEvent(sendEvent);
            
            // 等待一些时间让消息处理
            await this.sleep(1000);
            
            // 检查消息是否出现在聊天容器中
            const chatContainer = document.getElementById('chatContainer');
            const messages = chatContainer.querySelectorAll('.message');
            
            if (messages.length === 0) {
                throw new Error('发送消息后聊天容器中未找到消息');
            }
            
            this.log('✅ 消息发送功能正常', 'success');
            this.successCount++;
            return true;
        } catch (error) {
            this.log(`❌ 消息发送测试失败: ${error.message}`, 'error');
            this.errorCount++;
            return false;
        }
    }

    async runAllTests() {
        this.log('🚀 开始浏览器功能测试...', 'info');
        this.log('=' * 50, 'info');
        
        const tests = [
            'testPageLoading',
            'testGlobalObjects', 
            'testMessageProcessor',
            'testUIButtons',
            'testAPIConnection',
            'testSendMessage'
        ];
        
        for (const testName of tests) {
            await this[testName]();
            await this.sleep(500); // 测试间隔
        }
        
        this.log('=' * 50, 'info');
        this.log(`🏁 测试完成！成功: ${this.successCount}, 失败: ${this.errorCount}`, 
                 this.errorCount === 0 ? 'success' : 'warn');
        
        // 如果有错误，显示详细信息
        if (this.errorCount > 0) {
            this.log('⚠️  发现问题，需要修复', 'warn');
        } else {
            this.log('🎉 所有测试通过！聊天功能工作正常', 'success');
        }
        
        return {
            success: this.successCount,
            errors: this.errorCount,
            total: tests.length,
            results: this.testResults
        };
    }
}

// 自动执行测试
document.addEventListener('DOMContentLoaded', async () => {
    // 等待页面完全加载
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    
    
    const tester = new BrowserFunctionalityTest();
    const results = await tester.runAllTests();
    
    // 将结果暴露到全局，供开发者检查
    window.browserTestResults = results;
});
