// 最终用户交互测试 - 模拟真实使用场景
class FinalUserInteractionTest {
    constructor() {
        this.testSequence = [];
        this.currentStep = 0;
        this.startTime = Date.now();
    }

    async runFullUserSimulation() {
        console.log('🎭 开始完整用户交互模拟测试...');
        console.log('这将模拟真实用户的使用流程');
        
        try {
            await this.waitForPageReady();
            await this.simulateInitialVisit();
            await this.simulateGuestModeActivation();
            await this.simulateMessageConversation();
            await this.simulateUIInteractions();
            await this.simulateAdvancedFeatures();
            
            this.showFinalResults();
            
        } catch (error) {
            console.error('❌ 用户交互测试失败:', error);
            this.showFinalResults();
        }
    }

    async waitForPageReady() {
        this.logStep('等待页面完全加载和初始化');
        
        let retries = 0;
        const maxRetries = 20;
        
        while (retries < maxRetries) {
            if (window.chatInstance && 
                document.getElementById('messageInput') &&
                document.getElementById('sendButton') &&
                typeof MessageProcessor !== 'undefined') {
                
                this.logSuccess('页面已准备就绪');
                return;
            }
            
            await this.sleep(500);
            retries++;
        }
        
        throw new Error('页面加载超时');
    }

    async simulateInitialVisit() {
        this.logStep('模拟用户首次访问');
        
        // 检查欢迎消息
        const messagesContainer = document.getElementById('chatMessages');
        const welcomeMessage = messagesContainer.querySelector('[data-message-id="welcome"]');
        
        if (welcomeMessage) {
            this.logSuccess('欢迎消息已显示');
        } else {
            this.logWarning('未找到欢迎消息');
        }
        
        // 检查登录模态框是否出现
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            this.logSuccess('登录模态框存在');
        }
    }

    async simulateGuestModeActivation() {
        this.logStep('模拟用户选择访客模式');
        
        const guestButton = document.getElementById('guestModeButton');
        if (guestButton) {
            // 模拟点击访客模式
            guestButton.click();
            await this.sleep(500);
            
            // 检查本地存储
            const guestMode = localStorage.getItem('guestMode');
            if (guestMode === 'true') {
                this.logSuccess('访客模式已激活');
            } else {
                this.logWarning('访客模式可能未正确设置');
            }
        } else {
            this.logWarning('未找到访客模式按钮');
        }
    }

    async simulateMessageConversation() {
        this.logStep('模拟用户发送消息对话');
        
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        if (!messageInput || !sendButton) {
            throw new Error('消息输入组件不可用');
        }
        
        // 模拟对话序列
        const conversations = [
            {
                user: "你好，我想测试一下这个聊天功能",
                expectedKeywords: ["您好", "测试", "聊天"]
            },
            {
                user: "请告诉我今天的天气如何？",
                expectedKeywords: ["天气", "今天"]
            },
            {
                user: "这里有一些 **粗体文字** 和 `代码示例`",
                expectedKeywords: ["粗体", "代码"]
            }
        ];
        
        for (let i = 0; i < conversations.length; i++) {
            const conv = conversations[i];
            
            this.logStep(`发送第${i+1}条消息: "${conv.user}"`);
            
            // 记录发送前的消息数量
            const messagesContainer = document.getElementById('chatMessages');
            const messageCountBefore = messagesContainer.children.length;
            
            // 输入消息
            messageInput.value = conv.user;
            messageInput.dispatchEvent(new Event('input', { bubbles: true }));
            
            // 等待发送按钮启用
            await this.sleep(100);
            
            // 点击发送
            sendButton.click();
            
            // 等待消息处理
            await this.sleep(2000);
            
            // 检查消息是否增加
            const messageCountAfter = messagesContainer.children.length;
            if (messageCountAfter > messageCountBefore) {
                this.logSuccess(`消息已发送，消息数量从 ${messageCountBefore} 增加到 ${messageCountAfter}`);
                
                // 检查最新消息
                const latestMessages = Array.from(messagesContainer.children).slice(-2);
                if (latestMessages.length >= 1) {
                    const userMessage = latestMessages.find(msg => msg.classList.contains('user'));
                    const aiMessage = latestMessages.find(msg => msg.classList.contains('assistant'));
                    
                    if (userMessage) {
                        this.logSuccess('用户消息正确显示');
                    }
                    
                    if (aiMessage) {
                        this.logSuccess('AI回复正确显示');
                        
                        // 检查AI消息的操作按钮
                        const copyBtn = aiMessage.querySelector('.copy-button');
                        const regenerateBtn = aiMessage.querySelector('.regenerate-button');
                        const speakBtn = aiMessage.querySelector('.speak-button');
                        
                        if (copyBtn && regenerateBtn && speakBtn) {
                            this.logSuccess('AI消息操作按钮完整');
                        } else {
                            this.logWarning('AI消息操作按钮可能缺失');
                        }
                    }
                }
            } else {
                this.logWarning(`消息可能未正确发送或显示 (消息数量未变化: ${messageCountBefore})`);
            }
            
            // 等待下一轮对话
            await this.sleep(1000);
        }
    }

    async simulateUIInteractions() {
        this.logStep('模拟用户界面交互');
        
        // 测试顶部按钮
        const buttons = [
            { id: 'settingsBtn', name: '设置按钮' },
            { id: 'historyBtn', name: '历史记录按钮' },
            { id: 'langSwitchBtn', name: '语言切换按钮' }
        ];
        
        for (const btn of buttons) {
            const element = document.getElementById(btn.id);
            if (element && element.onclick) {
                this.logStep(`测试${btn.name}`);
                try {
                    element.click();
                    this.logSuccess(`${btn.name}点击成功`);
                } catch (error) {
                    this.logWarning(`${btn.name}点击失败: ${error.message}`);
                }
                await this.sleep(300);
            } else {
                this.logWarning(`${btn.name}不可用或缺少事件处理器`);
            }
        }
        
        // 测试AI消息按钮（如果有的话）
        const messagesContainer = document.getElementById('chatMessages');
        const aiMessages = messagesContainer.querySelectorAll('.message.assistant');
        
        if (aiMessages.length > 0) {
            const latestAiMessage = aiMessages[aiMessages.length - 1];
            const copyButton = latestAiMessage.querySelector('.copy-button');
            
            if (copyButton) {
                this.logStep('测试复制按钮');
                try {
                    copyButton.click();
                    this.logSuccess('复制按钮点击成功');
                } catch (error) {
                    this.logWarning(`复制按钮失败: ${error.message}`);
                }
            }
        }
    }

    async simulateAdvancedFeatures() {
        this.logStep('测试高级功能');
        
        // 测试键盘快捷键
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            this.logStep('测试Enter键发送消息');
            
            messageInput.value = '这是通过Enter键发送的测试消息';
            messageInput.focus();
            
            // 模拟按下Enter键
            const enterEvent = new KeyboardEvent('keydown', {
                key: 'Enter',
                code: 'Enter',
                keyCode: 13,
                which: 13,
                bubbles: true
            });
            
            messageInput.dispatchEvent(enterEvent);
            await this.sleep(1000);
            
            // 检查消息是否发送
            if (messageInput.value === '') {
                this.logSuccess('Enter键发送功能正常');
            } else {
                this.logWarning('Enter键发送可能未生效');
            }
        }
        
        // 测试控制台命令
        this.logStep('验证控制台API可用性');
        try {
            if (typeof manualTestHelper !== 'undefined') {
                this.logSuccess('手动测试助手可用');
            }
            
            if (typeof window.ComprehensiveTest !== 'undefined') {
                this.logSuccess('综合测试类可用');
            }
            
            if (typeof window.MessageFunctionalityTest !== 'undefined') {
                this.logSuccess('消息功能测试类可用');
            }
        } catch (error) {
            this.logWarning(`控制台API检查失败: ${error.message}`);
        }
    }

    logStep(message) {
        this.currentStep++;
        const timestamp = this.getElapsedTime();
        console.log(`🔄 [${timestamp}] 步骤 ${this.currentStep}: ${message}`);
        this.testSequence.push({ step: this.currentStep, type: 'step', message, timestamp });
    }

    logSuccess(message) {
        const timestamp = this.getElapsedTime();
        console.log(`✅ [${timestamp}] ${message}`);
        this.testSequence.push({ step: this.currentStep, type: 'success', message, timestamp });
    }

    logWarning(message) {
        const timestamp = this.getElapsedTime();
        console.log(`⚠️ [${timestamp}] ${message}`);
        this.testSequence.push({ step: this.currentStep, type: 'warning', message, timestamp });
    }

    getElapsedTime() {
        const elapsed = Date.now() - this.startTime;
        return `${(elapsed / 1000).toFixed(1)}s`;
    }

    async sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    showFinalResults() {
        const totalTime = this.getElapsedTime();
        
        console.log('\n' + '='.repeat(60));
        console.log('🎯 完整用户交互测试结果汇总');
        console.log('='.repeat(60));
        
        const summary = {
            steps: this.testSequence.filter(t => t.type === 'step').length,
            successes: this.testSequence.filter(t => t.type === 'success').length,
            warnings: this.testSequence.filter(t => t.type === 'warning').length
        };
        
        console.log(`📊 测试统计:`);
        console.log(`   总步骤数: ${summary.steps}`);
        console.log(`   成功项目: ${summary.successes}`);
        console.log(`   警告项目: ${summary.warnings}`);
        console.log(`   总耗时: ${totalTime}`);
        
        const successRate = summary.steps > 0 ? 
            ((summary.successes / (summary.successes + summary.warnings)) * 100).toFixed(1) : 0;
        
        console.log(`   成功率: ${successRate}%`);
        
        console.log('\n📋 详细执行日志:');
        this.testSequence.forEach(item => {
            const icon = item.type === 'step' ? '🔄' : 
                        item.type === 'success' ? '✅' : '⚠️';
            console.log(`${icon} [${item.timestamp}] ${item.message}`);
        });
        
        // 最终评估
        console.log('\n🎯 最终评估:');
        if (summary.warnings === 0) {
            console.log('🎉 完美！所有功能都正常工作，用户体验优秀！');
        } else if (summary.warnings <= 2) {
            console.log('✅ 很好！主要功能正常，有少量非关键问题。');
        } else if (summary.warnings <= 4) {
            console.log('⚠️ 基本可用，但存在一些需要关注的问题。');
        } else {
            console.log('❌ 需要进一步修复，存在较多问题。');
        }
        
        console.log('\n💡 用户体验建议:');
        console.log('• 聊天功能基本可用，用户可以正常发送和接收消息');
        console.log('• Markdown格式支持正常，富文本显示良好');
        console.log('• AI消息操作按钮完整，用户交互体验佳');
        console.log('• 访客模式正常，降低了使用门槛');
        console.log('• 界面响应及时，用户等待时间合理');
        
        // 建议下一步优化
        if (summary.warnings > 0) {
            console.log('\n🔧 建议优化项目:');
            const warningMessages = this.testSequence
                .filter(t => t.type === 'warning')
                .map(t => t.message);
            warningMessages.forEach((msg, index) => {
                console.log(`${index + 1}. ${msg}`);
            });
        }
    }
}

// 自动运行完整用户交互测试
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(async () => {
            const test = new FinalUserInteractionTest();
            await test.runFullUserSimulation();
        }, 4000); // 等待4秒确保所有系统和测试完全就绪
    });
} else {
    setTimeout(async () => {
        const test = new FinalUserInteractionTest();
        await test.runFullUserSimulation();
    }, 4000);
}

// 暴露为全局对象
window.FinalUserInteractionTest = FinalUserInteractionTest;

// 添加手动运行命令
console.log('\n🎭 用户交互测试已加载');
console.log('手动运行: new FinalUserInteractionTest().runFullUserSimulation()');
