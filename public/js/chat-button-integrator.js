try {
/**
 * AlingAi Pro - 聊天按钮集成管理器
 * 专门处理聊天相关按钮的统一管理
 */

class ChatButtonIntegrator {
    constructor() {
        this.chatButtons = new Map();
        this.init();
    }

    init() {
        // 等待DOM加载完成后初始化
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.integrateExistingButtons();
            });
        } else {
            this.integrateExistingButtons();
        }
    }

    integrateExistingButtons() {
        // 延迟执行，确保其他系统已初始化
        setTimeout(() => {
            this.integrateChatButtons();
        }, 1000);
    }

    integrateChatButtons() {
        // 查找并集成现有的聊天按钮
        this.integrateQuantumChatButton();
        this.integrateAIAssistantButton();
        this.integrateOtherChatButtons();
    }

    integrateQuantumChatButton() {
        const floatingButton = document.getElementById('floating-chat-button');
        if (floatingButton && window.floatingButtonsManager) {
            // 移除现有样式
            floatingButton.className = '';
            floatingButton.style.cssText = '';
            
            // 重新注册到管理器
            window.floatingButtonsManager.registerButton('quantum-chat', {
                element: floatingButton,
                preferredPosition: 'bottom-right-1',
                type: 'chat',
                priority: 5,
                title: '量子智能助手',
                icon: 'fas fa-comments'
            });

            
        }
    }

    integrateAIAssistantButton() {
        const aiAssistantBtn = document.getElementById('aiAssistantBtn');
        if (aiAssistantBtn && window.floatingButtonsManager) {
            // 如果已经是悬浮按钮样式，则集成
            const computedStyle = window.getComputedStyle(aiAssistantBtn);
            if (computedStyle.position === 'fixed') {
                aiAssistantBtn.className = '';
                aiAssistantBtn.style.cssText = '';
                
                window.floatingButtonsManager.registerButton('ai-assistant', {
                    element: aiAssistantBtn,
                    preferredPosition: 'bottom-right-1',
                    type: 'chat',
                    priority: 4,
                    title: 'AI智能助手',
                    icon: 'fas fa-robot'
                });

                
            }
        }
    }

    integrateOtherChatButtons() {
        // 查找其他可能的聊天按钮
        const chatToggleBtn = document.getElementById('chatToggleBtn');
        const chatButton = document.getElementById('chatButton');
        
        [chatToggleBtn, chatButton].forEach((btn, index) => {
            if (btn && window.floatingButtonsManager) {
                const computedStyle = window.getComputedStyle(btn);
                if (computedStyle.position === 'fixed') {
                    btn.className = '';
                    btn.style.cssText = '';
                    
                    window.floatingButtonsManager.registerButton(`chat-${index}`, {
                        element: btn,
                        preferredPosition: 'bottom-right-1',
                        type: 'chat',
                        priority: 3 - index,
                        title: '聊天助手',
                        icon: 'fas fa-comment-dots'
                    });

                    
                }
            }
        });
    }

    createUnifiedChatButton() {
        // 如果没有找到现有按钮，创建一个统一的聊天按钮
        if (this.chatButtons.size === 0) {
            const unifiedButton = document.createElement('button');
            unifiedButton.id = 'unified-chat-button';
            unifiedButton.innerHTML = '<i class="fas fa-comments"></i>';
            
            document.body.appendChild(unifiedButton);
            
            if (window.floatingButtonsManager) {
                window.floatingButtonsManager.registerButton('unified-chat', {
                    element: unifiedButton,
                    preferredPosition: 'bottom-right-1',
                    type: 'chat',
                    priority: 10,
                    title: '智能聊天助手',
                    icon: 'fas fa-comments',
                    onClick: this.handleUnifiedChatClick.bind(this)
                });

                
            }
        }
    }

    handleUnifiedChatClick() {
        // 尝试打开任何可用的聊天界面
        const chatContainer = document.getElementById('chat-container');
        const chatWidget = document.getElementById('chatWidget');
        const aiChatContainer = document.getElementById('ai-chat-container');

        if (chatContainer) {
            // 量子聊天
            if (window.quantumChatIntegrator && typeof window.quantumChatIntegrator.showChatInterface === 'function') {
                window.quantumChatIntegrator.showChatInterface();
            }
        } else if (chatWidget) {
            // 标准聊天小部件
            chatWidget.classList.remove('hidden');
        } else if (aiChatContainer) {
            // AI聊天容器
            if (window.aiChat && typeof window.aiChat.showChat === 'function') {
                window.aiChat.showChat();
            }
        } else {
            // 创建基本聊天界面
            this.createBasicChatInterface();
        }
    }

    createBasicChatInterface() {
        // 创建一个基本的聊天界面
        const chatInterface = document.createElement('div');
        chatInterface.id = 'basic-chat-interface';
        chatInterface.innerHTML = `
            <div class="fixed bottom-20 right-20 w-80 h-96 bg-white rounded-lg shadow-2xl border z-50">
                <div class="bg-blue-600 text-white p-4 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold">智能助手</h3>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-4 h-64 overflow-y-auto">
                    <div class="mb-4 p-3 bg-gray-100 rounded-lg">
                        您好！我是AlingAi Pro的智能助手。有什么可以帮助您的吗？
                    </div>
                </div>
                <div class="p-4 border-t">
                    <div class="flex gap-2">
                        <input type="text" placeholder="输入消息..." class="flex-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            发送
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(chatInterface);
        
    }
}

// 全局初始化
window.chatButtonIntegrator = new ChatButtonIntegrator();



} catch (error) {
    console.error(error);
    // 处理错误
}
