// 智能客服聊天模块
class ChatWidget {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.isTyping = false;
        
        // AI 回复模板
        this.responses = {
            greeting: [
                "您好！欢迎来到龙聆智能，我是您的专属AI助手。",
                "很高兴为您服务！请问有什么可以帮助您的吗？",
                "您好！我是龙聆智能的AI助手，随时为您解答问题。"
            ],
            services: [
                "我们提供AI对话系统、智能分析系统和自动化解决方案。您对哪个服务感兴趣呢？",
                "龙聆智能专注于企业级AI解决方案，包括智能客服、数据分析和业务自动化。",
                "我们的核心服务包括：\n• AI对话系统\n• 智能数据分析\n• 业务流程自动化\n• 企业级定制解决方案"
            ],
            contact: [
                "您可以通过以下方式联系我们：\n• 电话：400-8888-8888\n• 邮箱：info@alingai.com\n• 地址：北京市朝阳区科技园区",
                "如需详细咨询，建议您填写右侧的联系表单，我们的专业团队会尽快与您取得联系。",
                "我们的客服团队7×24小时在线，您也可以直接拨打热线：400-8888-8888"
            ],
            pricing: [
                "我们提供灵活的定价方案，包括基础版、专业版和企业版。具体价格需要根据您的业务需求定制。",
                "价格因服务类型和规模而异，建议您联系我们的销售团队获取专属报价。",
                "我们有免费试用版本，您可以先体验我们的服务，再选择合适的套餐。"
            ],
            default: [
                "感谢您的咨询！这个问题比较复杂，建议您联系我们的专业团队获取详细解答。",
                "抱歉，我可能没有完全理解您的问题。您可以换个方式问我，或者联系人工客服。",
                "这是一个很好的问题！为了给您最准确的答案，建议您直接与我们的技术专家交流。"
            ]
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
    }
    
    setupEventListeners() {
        // AI助手按钮点击
        const aiBtn = document.getElementById('aiAssistantBtn');
        const chatWidget = document.getElementById('chatWidget');
        const closeBtn = document.getElementById('closeChatWidget');
        const sendBtn = document.getElementById('sendChatMessage');
        const chatInput = document.getElementById('chatInput');
        
        aiBtn?.addEventListener('click', () => this.toggleWidget());
        closeBtn?.addEventListener('click', () => this.closeWidget());
        sendBtn?.addEventListener('click', () => this.sendMessage());
        
        // 回车发送消息
        chatInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // 自动调整输入框高度
        chatInput?.addEventListener('input', (e) => {
            this.adjustInputHeight(e.target);
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K: 打开聊天窗口
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.toggleWidget();
            }
            
            // Escape: 关闭聊天窗口
            if (e.key === 'Escape' && this.isOpen) {
                this.closeWidget();
            }
        });
    }
    
    toggleWidget() {
        if (this.isOpen) {
            this.closeWidget();
        } else {
            this.openWidget();
        }
    }
    
    openWidget() {
        const chatWidget = document.getElementById('chatWidget');
        const aiBtn = document.getElementById('aiAssistantBtn');
        
        if (chatWidget && aiBtn) {
            chatWidget.classList.remove('hidden');
            aiBtn.style.display = 'none';
            this.isOpen = true;
            
            // 动画效果
            chatWidget.style.opacity = '0';
            chatWidget.style.transform = 'translateY(20px) scale(0.95)';
            
            requestAnimationFrame(() => {
                chatWidget.style.transition = 'all 0.3s cubic-bezier(0.23, 1, 0.32, 1)';
                chatWidget.style.opacity = '1';
                chatWidget.style.transform = 'translateY(0) scale(1)';
            });
            
            // 焦点到输入框
            setTimeout(() => {
                document.getElementById('chatInput')?.focus();
            }, 300);
            
            // 如果是首次打开，显示欢迎消息
            if (this.messages.length === 1) {
                setTimeout(() => {
                    this.addBotMessage(this.getRandomResponse('greeting'));
                }, 500);
            }
        }
    }
    
    closeWidget() {
        const chatWidget = document.getElementById('chatWidget');
        const aiBtn = document.getElementById('aiAssistantBtn');
        
        if (chatWidget && aiBtn) {
            chatWidget.style.opacity = '0';
            chatWidget.style.transform = 'translateY(20px) scale(0.95)';
            
            setTimeout(() => {
                chatWidget.classList.add('hidden');
                aiBtn.style.display = 'flex';
                this.isOpen = false;
            }, 300);
        }
    }
    
    async sendMessage() {
        const chatInput = document.getElementById('chatInput');
        const message = chatInput?.value.trim();
        
        if (!message) return;
        
        // 添加用户消息
        this.addUserMessage(message);
        chatInput.value = '';
        
        // 显示机器人正在输入
        this.showTyping();
        
        // 模拟AI思考时间
        setTimeout(() => {
            this.hideTyping();
            const response = this.generateResponse(message);
            this.addBotMessage(response);
        }, 1000 + Math.random() * 2000);
    }
    
    addUserMessage(message) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-2 justify-end';
        messageDiv.innerHTML = `
            <div class="bg-gradient-to-r from-longling to-tech-blue rounded-lg p-3 text-sm max-w-xs">
                <p>${this.escapeHtml(message)}</p>
            </div>
            <div class="w-6 h-6 rounded-full bg-gray-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-xs"></i>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
        
        this.messages.push({ type: 'user', content: message, timestamp: Date.now() });
    }
    
    addBotMessage(message) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-2';
        messageDiv.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-r from-longling to-tech-blue flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-xs"></i>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-3 text-sm max-w-xs">
                <p>${this.escapeHtml(message).replace(/\n/g, '<br>')}</p>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
        
        this.messages.push({ type: 'bot', content: message, timestamp: Date.now() });
        
        // 语音朗读（如果开启了辅助功能）
        if (window.accessibilityManager?.screenReaderEnabled) {
            window.accessibilityManager.speak(message);
        }
    }
    
    showTyping() {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'flex items-start space-x-2';
        typingDiv.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-r from-longling to-tech-blue flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-xs"></i>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-3 text-sm">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        this.scrollToBottom();
        this.isTyping = true;
    }
    
    hideTyping() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
        this.isTyping = false;
    }
    
    generateResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        if (this.containsKeywords(lowerMessage, ['服务', '产品', '功能', '解决方案'])) {
            return this.getRandomResponse('services');
        }
        
        if (this.containsKeywords(lowerMessage, ['联系', '电话', '邮箱', '地址', '咨询'])) {
            return this.getRandomResponse('contact');
        }
        
        if (this.containsKeywords(lowerMessage, ['价格', '费用', '收费', '报价', '成本'])) {
            return this.getRandomResponse('pricing');
        }
        
        if (this.containsKeywords(lowerMessage, ['你好', '您好', 'hello', 'hi', '帮助'])) {
            return this.getRandomResponse('greeting');
        }
        
        return this.getRandomResponse('default');
    }
    
    containsKeywords(text, keywords) {
        return keywords.some(keyword => text.includes(keyword));
    }
    
    getRandomResponse(category) {
        const responses = this.responses[category] || this.responses.default;
        return responses[Math.floor(Math.random() * responses.length)];
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    adjustInputHeight(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// 初始化聊天组件
let chatWidget;

document.addEventListener('DOMContentLoaded', () => {
    chatWidget = new ChatWidget();
});

// 导出以供其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ChatWidget;
}
