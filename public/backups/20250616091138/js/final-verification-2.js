// 最终的AlingAi聊天功能验证脚本
console.log('🎯 AlingAi聊天功能最终验证脚本启动...');

class ChatFunctionVerifier {
    constructor() {
        this.results = {
            total: 0,
            passed: 0,
            failed: 0,
            tests: []
        };
        this.startTime = Date.now();
    }

    log(message, status = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const icons = { info: 'ℹ️', success: '✅', error: '❌', warning: '⚠️' };
        console.log(`${icons[status]} [${timestamp}] ${message}`);
    }

    addTest(name, passed, details = '') {
        this.results.total++;
        if (passed) this.results.passed++;
        else this.results.failed++;
        
        this.results.tests.push({ name, passed, details, timestamp: Date.now() });
        this.log(`${name} ${details ? `- ${details}` : ''}`, passed ? 'success' : 'error');
    }

    async sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // 1. 验证服务器状态
    async verifyServerHealth() {
        this.log('检查服务器健康状态...');
        try {
            const response = await fetch('/health');
            const data = await response.json();
            this.addTest('服务器健康检查', response.ok && data.status === 'healthy', 
                `状态: ${data.status}, 数据库: ${data.database}`);
        } catch (error) {
            this.addTest('服务器健康检查', false, error.message);
        }
    }

    // 2. 验证API连接
    async verifyAPIConnection() {
        this.log('测试聊天API连接...');
        try {
            const response = await fetch(API_ENDPOINTS.CHAT_DEEPSEEK, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    text: '这是API连接验证消息',
                    temperature: 0.7,
                    max_tokens: 100
                })
            });
            
            const data = await response.json();
            this.addTest('聊天API连接', response.ok && data.success, 
                `响应长度: ${data.assistantText?.length || 0} 字符`);
            
            return data;
        } catch (error) {
            this.addTest('聊天API连接', false, error.message);
            return null;
        }
    }

    // 3. 验证DOM元素
    verifyDOMElements() {
        this.log('检查页面DOM元素...');
        const requiredElements = {
            'messageInput': '消息输入框',
            'sendButton': '发送按钮',
            'chatMessages': '消息容器',
            'guestModeButton': '访客模式按钮',
            'recordButton': '录音按钮',
            'settingsBtn': '设置按钮',
            'historyBtn': '历史按钮',
            'loginModal': '登录模态框'
        };

        Object.entries(requiredElements).forEach(([id, name]) => {
            const element = document.getElementById(id);
            this.addTest(`DOM元素: ${name}`, element !== null, id);
        });
    }

    // 4. 验证JavaScript模块
    verifyJavaScriptModules() {
        this.log('检查JavaScript模块加载...');
        
        // 检查全局对象
        const globalChecks = {
            'ChatCore类': typeof ChatCore !== 'undefined',
            'ChatUI类': typeof ChatUI !== 'undefined', 
            'ChatAPI类': typeof ChatAPI !== 'undefined',
            'chatInstance实例': typeof window.chatInstance !== 'undefined' && window.chatInstance !== null,
            'manualTestHelper': typeof window.manualTestHelper !== 'undefined'
        };

        Object.entries(globalChecks).forEach(([name, exists]) => {
            this.addTest(`JS模块: ${name}`, exists);
        });
    }

    // 5. 验证访客模式
    async verifyGuestMode() {
        this.log('测试访客模式功能...');
        
        const guestButton = document.getElementById('guestModeButton');
        if (guestButton) {
            // 激活访客模式
            guestButton.click();
            await this.sleep(500);
            
            const guestMode = localStorage.getItem('guestMode');
            this.addTest('访客模式激活', guestMode === 'true');
        } else {
            this.addTest('访客模式激活', false, '按钮不存在');
        }
    }

    // 6. 验证消息发送
    async verifyMessageSending() {
        this.log('测试消息发送功能...');
        
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const chatMessages = document.getElementById('chatMessages');
        
        if (!messageInput || !sendButton || !chatMessages) {
            this.addTest('消息发送准备', false, '缺少必要元素');
            return;
        }

        // 记录发送前的消息数量
        const initialMessageCount = chatMessages.children.length;
        
        // 输入测试消息
        const testMessage = `验证测试消息 ${Date.now()}`;
        messageInput.value = testMessage;
        messageInput.dispatchEvent(new Event('input'));
        
        // 检查发送按钮状态
        const buttonEnabled = !sendButton.disabled;
        this.addTest('发送按钮状态', buttonEnabled);
        
        if (buttonEnabled) {
            // 点击发送按钮
            sendButton.click();
            
            // 等待消息处理
            await this.sleep(2000);
            
            // 检查消息数量变化
            const finalMessageCount = chatMessages.children.length;
            this.addTest('消息添加到界面', finalMessageCount > initialMessageCount, 
                `消息数量: ${initialMessageCount} → ${finalMessageCount}`);
        }
    }

    // 生成测试报告
    generateReport() {
        const duration = Date.now() - this.startTime;
        const successRate = Math.round((this.results.passed / this.results.total) * 100);
        
        console.log('\n' + '='.repeat(60));
        console.log('📊 AlingAi聊天功能验证报告');
        console.log('='.repeat(60));
        console.log(`⏱️  测试时长: ${Math.round(duration / 1000)}秒`);
        console.log(`📈 总测试数: ${this.results.total}`);
        console.log(`✅ 通过数量: ${this.results.passed}`);
        console.log(`❌ 失败数量: ${this.results.failed}`);
        console.log(`🎯 成功率: ${successRate}%`);
        
        // 详细结果
        console.log('\n📋 详细结果:');
        this.results.tests.forEach(test => {
            console.log(`  ${test.passed ? '✅' : '❌'} ${test.name} ${test.details ? `- ${test.details}` : ''}`);
        });
        
        // 建议和总结
        if (successRate >= 90) {
            console.log('\n✅ 聊天功能运行良好，可以正常使用！');
        } else if (successRate >= 70) {
            console.log('\n⚠️ 大部分功能正常，建议修复失败的测试项');
        } else {
            console.log('\n❌ 存在较多问题，需要进行全面检修');
        }
        
        // 保存结果到全局变量
        window.verificationReport = {
            ...this.results,
            duration,
            successRate,
            timestamp: new Date().toISOString()
        };
        
        return this.results;
    }

    // 执行所有验证
    async runAllVerifications() {
        this.log('开始执行完整的聊天功能验证...', 'info');
        
        // 按顺序执行所有验证
        await this.verifyServerHealth();
        await this.verifyAPIConnection();
        this.verifyDOMElements();
        this.verifyJavaScriptModules();
        await this.verifyGuestMode();
        await this.verifyMessageSending();
        
        return this.generateReport();
    }
}

// 创建验证器实例并运行
window.chatVerifier = new ChatFunctionVerifier();

// 延迟执行，确保页面完全加载
setTimeout(async () => {
    console.log('🎬 开始AlingAi聊天功能验证...');
    const results = await window.chatVerifier.runAllVerifications();
    console.log('🏁 验证完成！结果已保存到 window.verificationReport');
}, 3000);

// 导出手动测试函数
window.runManualVerification = () => {
    const verifier = new ChatFunctionVerifier();
    return verifier.runAllVerifications();
};

console.log('💡 手动运行验证: window.runManualVerification()');
