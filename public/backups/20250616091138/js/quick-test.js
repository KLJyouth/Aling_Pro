// 快速功能测试脚本
// 可以在浏览器控制台运行: loadScript('js/quick-test.js')

function quickTest() {
    console.log('🔍 快速功能测试开始...');
    
    // 1. 检查必要的DOM元素
    const elements = [
        'loginModal', 'messageInput', 'sendButton', 'chatMessages',
        'guestModeButton', 'userStatus', 'recordButton', 'imageGenButton'
    ];
    
    console.log('📋 DOM元素检查:');
    elements.forEach(id => {
        const exists = !!document.getElementById(id);
        console.log(`  ${exists ? '✅' : '❌'} ${id}`);
    });
    
    // 2. 检查聊天实例
    console.log('\n🤖 聊天实例检查:');
    const hasChat = !!window.chatInstance;
    const hasModules = hasChat && window.chatInstance.core && window.chatInstance.ui && window.chatInstance.api;
    console.log(`  ${hasChat ? '✅' : '❌'} chatInstance存在`);
    console.log(`  ${hasModules ? '✅' : '❌'} 所有模块加载完成`);
    
    // 3. 测试访客模式
    console.log('\n👤 访客模式测试:');
    const guestBtn = document.getElementById('guestModeButton');
    if (guestBtn) {
        guestBtn.click();
        setTimeout(() => {
            const isGuest = localStorage.getItem('guestMode') === 'true';
            console.log(`  ${isGuest ? '✅' : '❌'} 访客模式激活`);
            
            // 4. 测试消息输入
            console.log('\n💬 消息输入测试:');
            const input = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendButton');
            
            if (input && sendBtn) {
                // 测试空输入
                input.value = '';
                input.dispatchEvent(new Event('input'));
                const emptyDisabled = sendBtn.disabled;
                console.log(`  ${emptyDisabled ? '✅' : '❌'} 空输入时按钮禁用`);
                
                // 测试有内容
                input.value = '测试';
                input.dispatchEvent(new Event('input'));
                const hasContentEnabled = !sendBtn.disabled;
                console.log(`  ${hasContentEnabled ? '✅' : '❌'} 有内容时按钮启用`);
                
                input.value = ''; // 清空
            }
        }, 500);
    }
    
    console.log('\n🎯 快速测试完成，详细结果请查看上方日志');
}

// 自动运行
if (document.readyState === 'complete') {
    setTimeout(quickTest, 1000);
} else {
    window.addEventListener('load', () => setTimeout(quickTest, 1000));
}

// 导出函数以便手动调用
window.quickTest = quickTest;
