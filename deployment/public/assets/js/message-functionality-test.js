// 实时消息功能测试
class MessageFunctionalityTest {
    constructor() {
        this.testResults = [];
        this.messagesContainer = null;
    }

    async runTest() {
        console.log('🧪 开始消息功能测试...');
        
        // 等待系统初始化
        await this.waitForInitialization();
        
        // 测试消息处理器
        await this.testMessageProcessor();
        
        // 测试消息渲染
        await this.testMessageRendering();
        
        // 测试端到端消息流
        await this.testEndToEndMessageFlow();
        
        this.showResults();
    }

    async waitForInitialization() {
        console.log('⏳ 等待系统初始化...');
        
        let attempts = 0;
        const maxAttempts = 10;
        
        while (attempts < maxAttempts) {
            if (window.chatInstance && 
                typeof MessageProcessor !== 'undefined' && 
                typeof MessageRenderer !== 'undefined') {
                console.log('✅ 系统初始化完成');
                return;
            }
            
            await new Promise(resolve => setTimeout(resolve, 500));
            attempts++;
        }
        
        throw new Error('系统初始化超时');
    }

    async testMessageProcessor() {
        console.log('🔧 测试 MessageProcessor...');
        
        try {
            // 测试用户消息处理
            const userContent = "Hello **world**! This is a `code` example.";
            const processedUser = MessageProcessor.processUserMessage(userContent);
            
            console.log('用户消息输入:', userContent);
            console.log('用户消息处理结果:', processedUser);
            
            if (!processedUser.includes('<strong>world</strong>')) {
                throw new Error('用户消息Markdown处理失败');
            }
            
            if (!processedUser.includes('<code>code</code>')) {
                throw new Error('用户消息代码标记处理失败');
            }
            
            // 测试AI消息处理
            const aiContent = "# AI回复\n\n这是一个 **重要** 的回复，包含 `代码示例`。\n\n```javascript\nconsole.log('Hello World');\n```";
            const processedAI = MessageProcessor.processAssistantMessage(aiContent);
            
            console.log('AI消息输入:', aiContent);
            console.log('AI消息处理结果:', processedAI);
            
            if (!processedAI.includes('<h1>') || !processedAI.includes('<strong>重要</strong>')) {
                throw new Error('AI消息Markdown处理失败');
            }
            
            this.testResults.push({
                name: 'MessageProcessor',
                status: 'PASSED',
                details: 'Markdown处理正常'
            });
            
        } catch (error) {
            this.testResults.push({
                name: 'MessageProcessor',
                status: 'FAILED',
                details: error.message
            });
            console.error('❌ MessageProcessor测试失败:', error);
        }
    }

    async testMessageRendering() {
        console.log('🎨 测试消息渲染...');
        
        try {
            this.messagesContainer = document.getElementById('chatMessages');
            if (!this.messagesContainer) {
                throw new Error('未找到聊天消息容器');
            }
            
            // 清空容器
            this.messagesContainer.innerHTML = '';
            
            // 创建消息渲染器
            const renderer = new MessageRenderer(this.messagesContainer);
            
            // 测试用户消息渲染
            const userMessage = {
                id: 'test-user-msg',
                type: 'user',
                content: 'Hello, this is a **test** message!',
                timestamp: new Date()
            };
            
            await renderer.render(userMessage);
            await new Promise(resolve => setTimeout(resolve, 200));
            
            const userElement = this.messagesContainer.querySelector('[data-message-id="test-user-msg"]');
            if (!userElement) {
                throw new Error('用户消息元素未创建');
            }
            
            if (!userElement.classList.contains('user')) {
                throw new Error('用户消息样式类错误');
            }
            
            console.log('✅ 用户消息渲染成功');
            
            // 测试AI消息渲染
            const aiMessage = {
                id: 'test-ai-msg',
                type: 'assistant',
                content: 'This is an AI response with **formatting**.',
                timestamp: new Date()
            };
            
            await renderer.render(aiMessage);
            await new Promise(resolve => setTimeout(resolve, 200));
            
            const aiElement = this.messagesContainer.querySelector('[data-message-id="test-ai-msg"]');
            if (!aiElement) {
                throw new Error('AI消息元素未创建');
            }
            
            if (!aiElement.classList.contains('assistant')) {
                throw new Error('AI消息样式类错误');
            }
            
            // 检查AI消息的按钮
            const copyButton = aiElement.querySelector('.copy-button');
            const regenerateButton = aiElement.querySelector('.regenerate-button');
            const speakButton = aiElement.querySelector('.speak-button');
            
            if (!copyButton || !regenerateButton || !speakButton) {
                throw new Error('AI消息按钮未正确创建');
            }
            
            console.log('✅ AI消息渲染成功，包含所有操作按钮');
            
            this.testResults.push({
                name: 'MessageRendering',
                status: 'PASSED',
                details: '消息渲染功能正常'
            });
            
        } catch (error) {
            this.testResults.push({
                name: 'MessageRendering',
                status: 'FAILED',
                details: error.message
            });
            console.error('❌ 消息渲染测试失败:', error);
        }
    }

    async testEndToEndMessageFlow() {
        console.log('🔄 测试端到端消息流...');
        
        try {
            if (!window.chatInstance) {
                throw new Error('聊天实例未初始化');
            }
            
            const { core, ui, api } = window.chatInstance;
            
            // 记录初始消息数量
            const initialMessageCount = this.messagesContainer.children.length;
            
            // 模拟用户发送消息
            const testMessage = '这是一条端到端测试消息';
            
            console.log('📤 发送测试消息:', testMessage);
            
            // 处理用户消息
            const userMessage = await core.processUserMessage(testMessage);
            if (!userMessage || userMessage.type !== 'user') {
                throw new Error('用户消息处理失败');
            }
            
            // 添加用户消息到界面
            await ui.addMessage(userMessage);
            
            // 等待UI更新
            await new Promise(resolve => setTimeout(resolve, 300));
            
            // 检查用户消息是否显示
            const userMessageElement = this.messagesContainer.querySelector(`[data-message-id="${userMessage.id}"]`);
            if (!userMessageElement) {
                throw new Error('用户消息未显示在界面上');
            }
            
            console.log('✅ 用户消息成功显示');
            
            // 模拟API响应
            const mockResponse = {
                content: `我收到了您的消息："${testMessage}"。这是一个测试回复。`,
                model: 'test-model'
            };
            
            // 处理AI响应
            const aiMessage = await core.processResponse(mockResponse);
            if (!aiMessage || aiMessage.type !== 'ai') {
                throw new Error('AI消息处理失败');
            }
            
            // 添加AI消息到界面
            await ui.addMessage(aiMessage);
            
            // 等待UI更新
            await new Promise(resolve => setTimeout(resolve, 300));
            
            // 检查AI消息是否显示
            const aiMessageElement = this.messagesContainer.querySelector(`[data-message-id="${aiMessage.id}"]`);
            if (!aiMessageElement) {
                throw new Error('AI消息未显示在界面上');
            }
            
            console.log('✅ AI消息成功显示');
            
            // 检查消息数量增加
            const finalMessageCount = this.messagesContainer.children.length;
            if (finalMessageCount !== initialMessageCount + 2) {
                console.warn(`消息数量不符合预期: 期望${initialMessageCount + 2}, 实际${finalMessageCount}`);
            }
            
            this.testResults.push({
                name: 'EndToEndFlow',
                status: 'PASSED',
                details: '端到端消息流正常工作'
            });
            
        } catch (error) {
            this.testResults.push({
                name: 'EndToEndFlow',
                status: 'FAILED',
                details: error.message
            });
            console.error('❌ 端到端测试失败:', error);
        }
    }

    showResults() {
        console.log('\n📊 消息功能测试结果:');
        console.log('='.repeat(50));
        
        let passedTests = 0;
        const totalTests = this.testResults.length;
        
        this.testResults.forEach(result => {
            const status = result.status === 'PASSED' ? '✅' : '❌';
            console.log(`${status} ${result.name}: ${result.details}`);
            if (result.status === 'PASSED') passedTests++;
        });
        
        const successRate = totalTests > 0 ? (passedTests / totalTests * 100).toFixed(1) : 0;
        
        console.log('\n📈 测试统计:');
        console.log(`成功: ${passedTests}/${totalTests} (${successRate}%)`);
        
        if (passedTests === totalTests) {
            console.log('🎉 所有消息功能测试通过！');
            console.log('💬 消息系统已成功修复，可以正常收发消息。');
        } else {
            console.log('⚠️ 部分测试失败，需要进一步修复。');
        }
        
        // 显示当前消息容器状态
        console.log('\n📋 当前消息容器状态:');
        if (this.messagesContainer) {
            console.log(`消息数量: ${this.messagesContainer.children.length}`);
            console.log('消息列表:');
            Array.from(this.messagesContainer.children).forEach((msg, index) => {
                const id = msg.dataset.messageId || '未知';
                const type = msg.classList.contains('user') ? '用户' : 
                            msg.classList.contains('assistant') ? 'AI' : '其他';
                console.log(`  ${index + 1}. ${type}消息 (ID: ${id})`);
            });
        }
    }
}

// 自动运行测试
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(async () => {
            const test = new MessageFunctionalityTest();
            await test.runTest();
        }, 3000); // 等待3秒确保系统完全初始化
    });
} else {
    setTimeout(async () => {
        const test = new MessageFunctionalityTest();
        await test.runTest();
    }, 3000);
}

// 暴露为全局对象
window.MessageFunctionalityTest = MessageFunctionalityTest;
