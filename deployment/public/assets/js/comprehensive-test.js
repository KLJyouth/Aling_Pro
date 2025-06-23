// 综合功能测试脚本
class ComprehensiveTest {
    constructor() {
        this.testResults = [];
        this.totalTests = 0;
        this.passedTests = 0;
    }

    async runAllTests() {
        console.log('🚀 开始运行综合功能测试...');
        
        await this.testMessageProcessor();
        await this.testMessageRenderer();
        await this.testGlobalObjects();
        await this.testChatFunctionality();
        await this.testUIComponents();
        await this.testAPIConnection();
        
        this.displayResults();
    }

    async testMessageProcessor() {
        const testName = 'MessageProcessor 功能测试';
        try {
            // 测试 processUserMessage
            const userMessage = "Hello **world**!";
            const processedUser = MessageProcessor.processUserMessage(userMessage);
            this.assert(processedUser.includes('<strong>world</strong>'), 'User message markdown processing');
            
            // 测试 processAssistantMessage
            const assistantMessage = "# 标题\n\n这是一个 `代码` 示例。";
            const processedAssistant = MessageProcessor.processAssistantMessage(assistantMessage);
            this.assert(processedAssistant.includes('<h1>'), 'Assistant message markdown processing');
            
            // 测试空内容处理
            this.assert(MessageProcessor.processUserMessage('') === '', 'Empty user message handling');
            this.assert(MessageProcessor.processAssistantMessage(null) === '', 'Null assistant message handling');
            
            this.passTest(testName);
        } catch (error) {
            this.failTest(testName, error.message);
        }
    }

    async testMessageRenderer() {
        const testName = 'MessageRenderer 功能测试';
        try {
            // 创建测试容器
            const testContainer = document.createElement('div');
            testContainer.id = 'test-messages';
            document.body.appendChild(testContainer);
            
            const renderer = new MessageRenderer(testContainer);
            
            // 测试用户消息渲染
            const userMessage = {
                id: 'test-user-1',
                type: 'user',
                content: 'Hello, AI!',
                timestamp: new Date()
            };
            
            await renderer.render(userMessage);
            
            // 等待渲染完成
            await new Promise(resolve => setTimeout(resolve, 100));
            
            const userElement = testContainer.querySelector('[data-message-id="test-user-1"]');
            this.assert(userElement !== null, 'User message element created');
            this.assert(userElement.classList.contains('user'), 'User message has correct class');
            
            // 测试AI消息渲染
            const aiMessage = {
                id: 'test-ai-1',
                type: 'assistant',
                content: 'Hello! How can I help you?',
                timestamp: new Date()
            };
            
            await renderer.render(aiMessage);
            await new Promise(resolve => setTimeout(resolve, 100));
            
            const aiElement = testContainer.querySelector('[data-message-id="test-ai-1"]');
            this.assert(aiElement !== null, 'AI message element created');
            this.assert(aiElement.classList.contains('assistant'), 'AI message has correct class');
            
            // 测试按钮存在
            const copyButton = aiElement.querySelector('.copy-button');
            this.assert(copyButton !== null, 'Copy button exists');
            
            // 清理测试容器
            testContainer.remove();
            
            this.passTest(testName);
        } catch (error) {
            this.failTest(testName, error.message);
        }
    }

    async testGlobalObjects() {
        const testName = '全局对象可用性测试';
        try {
            // 测试类是否全局可用
            this.assert(typeof ChatCore !== 'undefined', 'ChatCore is globally available');
            this.assert(typeof ChatUI !== 'undefined', 'ChatUI is globally available');
            this.assert(typeof ChatAPI !== 'undefined', 'ChatAPI is globally available');
            this.assert(typeof MessageProcessor !== 'undefined', 'MessageProcessor is globally available');
            this.assert(typeof MessageRenderer !== 'undefined', 'MessageRenderer is globally available');
            
            // 测试 chatInstance
            this.assert(window.chatInstance !== null, 'chatInstance exists');
            this.assert(window.chatInstance.core !== undefined, 'chatInstance.core exists');
            this.assert(window.chatInstance.ui !== undefined, 'chatInstance.ui exists');
            this.assert(window.chatInstance.api !== undefined, 'chatInstance.api exists');
            
            this.passTest(testName);
        } catch (error) {
            this.failTest(testName, error.message);
        }
    }

    async testChatFunctionality() {
        const testName = '聊天基础功能测试';
        try {
            if (!window.chatInstance) {
                throw new Error('chatInstance not initialized');
            }
            
            const { core, ui, api } = window.chatInstance;
            
            // 测试消息处理
            const testMessage = "Test message";
            const userMessage = await core.processUserMessage(testMessage);
            this.assert(userMessage.type === 'user', 'User message type correct');
            this.assert(userMessage.content === testMessage, 'User message content preserved');
            this.assert(userMessage.id !== undefined, 'User message has ID');
            
            // 测试UI组件
            const sendButton = document.getElementById('sendBtn');
            this.assert(sendButton !== null, 'Send button exists');
            
            const messageInput = document.getElementById('messageInput');
            this.assert(messageInput !== null, 'Message input exists');
            
            const messagesContainer = document.getElementById('messagesContainer');
            this.assert(messagesContainer !== null, 'Messages container exists');
            
            this.passTest(testName);
        } catch (error) {
            this.failTest(testName, error.message);
        }
    }

    async testUIComponents() {
        const testName = 'UI组件功能测试';
        try {
            // 测试按钮事件处理器
            const settingsBtn = document.getElementById('settingsBtn');
            this.assert(settingsBtn !== null, 'Settings button exists');
            this.assert(settingsBtn.onclick !== null, 'Settings button has click handler');
            
            const historyBtn = document.getElementById('historyBtn');
            this.assert(historyBtn !== null, 'History button exists');
            this.assert(historyBtn.onclick !== null, 'History button has click handler');
            
            const langSwitchBtn = document.getElementById('langSwitchBtn');
            this.assert(langSwitchBtn !== null, 'Language switch button exists');
            this.assert(langSwitchBtn.onclick !== null, 'Language switch button has click handler');
            
            // 测试模态框
            const loginModal = document.getElementById('loginModal');
            this.assert(loginModal !== null, 'Login modal exists');
            
            this.passTest(testName);
        } catch (error) {
            this.failTest(testName, error.message);
        }
    }

    async testAPIConnection() {
        const testName = 'API连接测试';
        try {
            if (!window.chatInstance?.api) {
                throw new Error('API instance not available');
            }
            
            // 测试基础配置
            const api = window.chatInstance.api;
            this.assert(api.config !== undefined, 'API config exists');
            
            // 测试健康检查端点（如果可用）
            try {
                const response = await fetch('/api/health');
                if (response.ok) {
                    this.assert(true, 'Health check endpoint accessible');
                } else {
                    console.warn('Health check endpoint not available or returned error');
                }
            } catch (error) {
                console.warn('Health check test failed:', error.message);
            }
            
            this.passTest(testName);
        } catch (error) {
            this.failTest(testName, error.message);
        }
    }

    assert(condition, description) {
        this.totalTests++;
        if (condition) {
            this.passedTests++;
            console.log(`✅ ${description}`);
        } else {
            console.error(`❌ ${description}`);
            throw new Error(`Assertion failed: ${description}`);
        }
    }

    passTest(testName) {
        this.testResults.push({ name: testName, status: 'PASSED', error: null });
        console.log(`🟢 ${testName} - PASSED`);
    }

    failTest(testName, error) {
        this.testResults.push({ name: testName, status: 'FAILED', error });
        console.error(`🔴 ${testName} - FAILED: ${error}`);
    }

    displayResults() {
        console.log('\n📊 测试结果汇总:');
        console.log(`总测试数: ${this.totalTests}`);
        console.log(`通过的断言: ${this.passedTests}`);
        console.log(`失败的断言: ${this.totalTests - this.passedTests}`);
        console.log(`成功率: ${((this.passedTests / this.totalTests) * 100).toFixed(1)}%`);
        
        console.log('\n📋 详细结果:');
        this.testResults.forEach(result => {
            const status = result.status === 'PASSED' ? '✅' : '❌';
            console.log(`${status} ${result.name}`);
            if (result.error) {
                console.log(`   错误: ${result.error}`);
            }
        });
        
        // 总结
        const passedSuites = this.testResults.filter(r => r.status === 'PASSED').length;
        const totalSuites = this.testResults.length;
        console.log(`\n🎯 整体成功率: ${((passedSuites / totalSuites) * 100).toFixed(1)}% (${passedSuites}/${totalSuites} 测试套件通过)`);
        
        if (passedSuites === totalSuites) {
            console.log('🎉 所有测试都通过了！聊天功能已基本修复。');
        } else {
            console.log('⚠️ 仍有部分功能需要修复。');
        }
    }
}

// 页面加载完成后运行测试
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const test = new ComprehensiveTest();
            test.runAllTests();
        }, 2000); // 等待2秒确保所有组件初始化完成
    });
} else {
    setTimeout(() => {
        const test = new ComprehensiveTest();
        test.runAllTests();
    }, 2000);
}

// 将测试类暴露为全局对象以便手动调用
window.ComprehensiveTest = ComprehensiveTest;
