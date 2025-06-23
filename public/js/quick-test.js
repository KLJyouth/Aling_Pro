try {
// 快速功能测试脚本
// 可以在浏览器控制台运行: loadScript('js/quick-test.js')

function quickTest() {
    
    
    // 1. 检查必要的DOM元素
    const elements = [
        'loginModal', 'messageInput', 'sendButton', 'chatMessages',
        'guestModeButton', 'userStatus', 'recordButton', 'imageGenButton'
    ];
    
    
    elements.forEach(id => {
        const exists = !!document.getElementById(id);
        
    });
    
    // 2. 检查聊天实例
    
    const hasChat = !!window.chatInstance;
    const hasModules = hasChat && window.chatInstance.core && window.chatInstance.ui && window.chatInstance.api;
    
    
    
    // 3. 测试访客模式
    
    const guestBtn = document.getElementById('guestModeButton');
    if (guestBtn) {
        guestBtn.click();
        setTimeout(() => {
            const isGuest = localStorage.getItem('guestMode') === 'true';
            
            
            // 4. 测试消息输入
            
            const input = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendButton');
            
            if (input && sendBtn) {
                // 测试空输入
                input.value = '';
                input.dispatchEvent(new Event('input'));
                const emptyDisabled = sendBtn.disabled;
                
                
                // 测试有内容
                input.value = '测试';
                input.dispatchEvent(new Event('input'));
                const hasContentEnabled = !sendBtn.disabled;
                
                
                input.value = ''; // 清空
            }
        }, 500);
    }
    
    
}

// 自动运行
if (document.readyState === 'complete') {
    setTimeout(quickTest, 1000);
} else {
    window.addEventListener('load', () => setTimeout(quickTest, 1000));
}

// 导出函数以便手动调用
window.quickTest = quickTest;

} catch (error) {
    console.error(error);
    // 处理错误
}
