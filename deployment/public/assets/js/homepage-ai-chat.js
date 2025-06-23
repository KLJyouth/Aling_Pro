
        this.chatHistory = [];
        this.isTyping = false;
        this.currentTypingEffect = null;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        
        this.setupEventListeners();
        this.loadChatHistory();
        this.isInitialized = true;
        
        console.log('✅ 首页AI对话系统初始化完成');
    }
    
    setupEventListeners() {
        // AI助手浮动按钮
        const aiBtn = document.getElementById('aiAssistantBtn');
        if (aiBtn) {
            aiBtn.addEventListener('click', () => this.toggleChatWidget());
        }
        
        // 聊天窗口控制
        const closeChatBtn = document.getElementById('closeChatWidget');
        if (closeChatBtn) {
            closeChatBtn.addEventListener('click', () => this.hideChatWidget());
        }
        
        // 发送消息
        const sendBtn = document.getElementById('sendChatMessage');
        const chatInput = document.getElementById('chatInput');
        
        if (sendBtn) {
            sendBtn.addEventListener('click', () => this.sendMessage());
        }
        
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            // 输入监听
            chatInput.addEventListener('input', () => {
                this.updateSendButton();
            });
        }
        
        // 快捷操作按钮
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const text = e.target.textContent;
                this.sendQuickMessage(text);
            });
        });
        
        // 语音输入
        const voiceBtn = document.getElementById('voiceInputBtn');
        if (voiceBtn) {
            voiceBtn.addEventListener('click', () => this.startVoiceInput());
        }
        
        // 表情按钮
        const emojiBtn = document.getElementById('emojiBtn');
        if (emojiBtn) {
            emojiBtn.addEventListener('click', () => this.showEmojiPanel());
        }
    }
    
    toggleChatWidget() {
        const chatWidget = document.getElementById('chatWidget');
        if (!chatWidget) return;
        
        const isHidden = chatWidget.classList.contains('hidden');
        
        if (isHidden) {
            this.showChatWidget();
        } else {
            this.hideChatWidget();
        }
    }
    
    showChatWidget() {
        const chatWidget = document.getElementById('chatWidget');
        const aiBtn = document.getElementById('aiAssistantBtn');
        
        if (chatWidget) {
            chatWidget.classList.remove('hidden');
            setTimeout(() => {
                chatWidget.classList.remove('scale-95', 'opacity-0');
                chatWidget.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        
        if (aiBtn) {
            aiBtn.style.transform = 'scale(0.8)';
        }
        
        // 聚焦输入框
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            setTimeout(() => chatInput.focus(), 300);
        }
        
        console.log('💬 AI聊天窗口已打开');
    }
    
    hideChatWidget() {
        const chatWidget = document.getElementById('chatWidget');
        const aiBtn = document.getElementById('aiAssistantBtn');
        
        if (chatWidget) {
            chatWidget.classList.add('scale-95', 'opacity-0');
            chatWidget.classList.remove('scale-100', 'opacity-100');
            
            setTimeout(() => {
                chatWidget.classList.add('hidden');
            }, 300);
        }
        
        if (aiBtn) {
            aiBtn.style.transform = 'scale(1)';
        }
        
        console.log('💬 AI聊天窗口已关闭');
    }
    
    async sendMessage() {
        const chatInput = document.getElementById('chatInput');
        if (!chatInput) return;
        
        const message = chatInput.value.trim();
        if (!message || this.isTyping) return;
        
        // 添加用户消息
        this.addMessage(message, 'user');
        chatInput.value = '';
        this.updateSendButton();
        
        // 显示AI正在输入
        this.showTypingIndicator();
        
        try {
            // 模拟AI响应（实际应该调用AI API）
            const response = await this.getAIResponse(message);
            
            this.hideTypingIndicator();
            this.addMessage(response, 'ai');
            
            // 保存聊天历史
            this.saveChatHistory();
            
        } catch (error) {
            this.hideTypingIndicator();
            this.addMessage('抱歉，我现在无法响应。请稍后再试。', 'ai', true);
            console.error('AI响应错误:', error);
        }
    }
    
    sendQuickMessage(text) {
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.value = text;
            this.sendMessage();
        }
    }
    
    addMessage(content, sender, isError = false) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-2 message-item';
        
        const isUser = sender === 'user';
        const timestamp = new Date().toLocaleTimeString();
        
        messageDiv.innerHTML = `
            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                isUser ? 'bg-blue-500' : 'bg-gradient-to-r from-longling to-tech-blue'
            }">
                <i class="fas ${isUser ? 'fa-user' : 'fa-robot'} text-xs"></i>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-3 text-sm max-w-xs ${isError ? 'border border-red-500/20' : ''}">
                <p class="${isError ? 'text-red-400' : ''}">${content}</p>
                <div class="text-xs text-gray-500 mt-1">
                    <span>${timestamp}</span>
                </div>
            </div>
        `;
        
        // 动画效果
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateY(10px)';
        
        chatMessages.appendChild(messageDiv);
        
        // 添加到历史记录
        this.chatHistory.push({
            content,
            sender,
            timestamp: new Date().toISOString(),
            isError
        });
        
        // 动画显示
        setTimeout(() => {
            messageDiv.style.transition = 'all 0.3s ease';
            messageDiv.style.opacity = '1';
            messageDiv.style.transform = 'translateY(0)';
        }, 10);
        
        // 滚动到底部
        this.scrollToBottom();
    }
    
    showTypingIndicator() {
        if (this.isTyping) return;
        
        this.isTyping = true;
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typingIndicator';
        typingDiv.className = 'flex items-start space-x-2';
        typingDiv.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-gradient-to-r from-longling to-tech-blue flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-xs"></i>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-3 text-sm">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">AI正在思考中...</div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        this.scrollToBottom();
    }
    
    hideTypingIndicator() {
        this.isTyping = false;
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    async getAIResponse(message) {
        // 模拟AI响应延迟
        await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 2000));
        
        // 智能响应逻辑
        const responses = this.generateSmartResponse(message);
        return responses[Math.floor(Math.random() * responses.length)];
    }
    
    generateSmartResponse(message) {
        const msg = message.toLowerCase();
        
        // 产品相关
        if (msg.includes('产品') || msg.includes('服务')) {
            return [
                '珑凌科技专注于量子安全技术，提供企业级加密解决方案。我们的产品包括量子密钥分发、后量子加密算法等。',
                '我们的核心产品包括量子安全通信平台、智能威胁检测系统，以及企业级数据保护解决方案。您对哪个方面比较感兴趣？',
                '珑凌科技的产品矩阵涵盖了从底层加密到智能决策的全栈解决方案。需要我详细介绍某个特定产品吗？'
            ];
        }
        
        // 技术相关
        if (msg.includes('技术') || msg.includes('量子') || msg.includes('安全')) {
            return [
                '我们在量子密码学、后量子加密、以及AI安全等领域拥有先进技术。这些技术确保您的数据在量子计算时代仍然安全。',
                '珑凌科技的量子安全技术基于最新的密码学研究，能够抵御传统和量子计算攻击。您想了解具体的技术实现吗？',
                '我们的技术栈包括量子随机数生成、量子密钥分发协议、以及自适应安全算法。这些技术的结合为客户提供了前所未有的安全保障。'
            ];
        }
        
        // 商务合作
        if (msg.includes('合作') || msg.includes('商务') || msg.includes('联系')) {
            return [
                '感谢您对珑凌科技的关注！我们的商务团队很乐意与您探讨合作机会。您可以通过官网联系表单或直接联系我们的商务代表。',
                '我们欢迎各种形式的合作伙伴关系。无论是技术集成、渠道合作还是战略联盟，我们都有专业的团队为您提供支持。',
                '珑凌科技正在寻求与各行业领先企业的合作。请告诉我您的具体需求，我会为您安排合适的对接人员。'
            ];
        }
        
        // 价格相关
        if (msg.includes('价格') || msg.includes('费用') || msg.includes('成本')) {
            return [
                '我们提供灵活的定价方案，根据企业规模和具体需求定制。建议您联系我们的销售团队获取详细报价。',
                '珑凌科技的解决方案采用订阅制和一次性授权相结合的模式。具体价格取决于部署规模和服务等级。',
                '我们理解成本是企业决策的重要因素。我们的定价策略注重为客户提供最高的性价比，详情请咨询销售团队。'
            ];
        }
        
        // 问候和感谢
        if (msg.includes('你好') || msg.includes('hello') || msg.includes('hi')) {
            return [
                '您好！欢迎来到珑凌科技。我是您的AI助手，很高兴为您服务。请问有什么可以帮助您的吗？',
                '您好！我是珑凌科技的智能助手。我可以为您介绍我们的产品、技术或解答其他问题。请随时告诉我您的需求。',
                'Hi！感谢您对珑凌科技的关注。我会尽我所能为您提供帮助。您想了解我们的什么方面呢？'
            ];
        }
        
        if (msg.includes('谢谢') || msg.includes('感谢') || msg.includes('thank')) {
            return [
                '不客气！很高兴能为您提供帮助。如果您还有其他问题，请随时告诉我。',
                '您太客气了！为您服务是我的荣幸。珑凌科技随时为您提供支持。',
                '感谢您的认可！我们始终致力于为客户提供最优质的服务体验。'
            ];
        }
        
        // 默认响应
        return [
            '这是一个很有趣的问题。基于我对珑凌科技的了解，我建议您可以进一步了解我们的产品和服务。需要我为您详细介绍吗？',
            '感谢您的询问。珑凌科技在量子安全领域有丰富的经验和先进的技术。您可以浏览我们的官网了解更多信息，或者直接与我们的专家团队联系。',
            '您提出了一个很好的观点。作为量子安全领域的领导者，珑凌科技一直在不断创新和完善我们的解决方案。有什么具体问题我可以为您解答吗？',
            '我理解您的关注。珑凌科技致力于为客户提供最佳的量子安全解决方案。如果您需要更详细的信息，我可以为您安排专业顾问的咨询。'
        ];
    }
    
    updateSendButton() {
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendChatMessage');
        
        if (chatInput && sendBtn) {
            const hasContent = chatInput.value.trim().length > 0;
            sendBtn.disabled = !hasContent || this.isTyping;
            sendBtn.style.opacity = hasContent && !this.isTyping ? '1' : '0.5';
        }
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    startVoiceInput() {
        // 语音输入功能（需要浏览器支持）
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            
            recognition.lang = 'zh-CN';
            recognition.continuous = false;
            recognition.interimResults = false;
            
            recognition.onstart = () => {
                console.log('🎤 语音识别开始');
                const voiceBtn = document.getElementById('voiceInputBtn');
                if (voiceBtn) {
                    voiceBtn.innerHTML = '<i class="fas fa-stop text-xs"></i>';
                    voiceBtn.style.color = '#ef4444';
                }
            };
            
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                const chatInput = document.getElementById('chatInput');
                if (chatInput) {
                    chatInput.value = transcript;
                    this.updateSendButton();
                }
            };
            
            recognition.onend = () => {
                console.log('🎤 语音识别结束');
                const voiceBtn = document.getElementById('voiceInputBtn');
                if (voiceBtn) {
                    voiceBtn.innerHTML = '<i class="fas fa-microphone text-xs"></i>';
                    voiceBtn.style.color = '';
                }
            };
            
            recognition.onerror = (event) => {
                console.error('语音识别错误:', event.error);
            };
            
            recognition.start();
        } else {
            alert('您的浏览器不支持语音识别功能');
        }
    }
    
    showEmojiPanel() {
        // 简单的表情面板实现
        const emojis = ['😊', '😂', '🤔', '👍', '❤️', '🔥', '💯', '🎉', '😎', '🚀'];
        
        const existingPanel = document.getElementById('emojiPanel');
        if (existingPanel) {
            existingPanel.remove();
            return;
        }
        
        const panel = document.createElement('div');
        panel.id = 'emojiPanel';
        panel.className = 'absolute bottom-full right-0 mb-2 p-2 bg-gray-800 rounded-lg shadow-lg grid grid-cols-5 gap-1';
        panel.style.zIndex = '1000';
        
        emojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.textContent = emoji;
            btn.className = 'p-2 hover:bg-gray-700 rounded text-lg';
            btn.onclick = () => {
                const chatInput = document.getElementById('chatInput');
                if (chatInput) {
                    chatInput.value += emoji;
                    this.updateSendButton();
                }
                panel.remove();
            };
            panel.appendChild(btn);
        });
        
        const emojiBtn = document.getElementById('emojiBtn');
        if (emojiBtn) {
            emojiBtn.parentElement.style.position = 'relative';
            emojiBtn.parentElement.appendChild(panel);
        }
        
        // 点击外部关闭
        setTimeout(() => {
            document.addEventListener('click', function closePanel(e) {
                if (!panel.contains(e.target) && e.target.id !== 'emojiBtn') {
                    panel.remove();
                    document.removeEventListener('click', closePanel);
                }
            });
        }, 0);
    }
    
    saveChatHistory() {
        try {
            localStorage.setItem('homepage-chat-history', JSON.stringify(this.chatHistory.slice(-50)));
        } catch (error) {
            console.warn('保存聊天历史失败:', error);
        }
    }
    
    loadChatHistory() {
        try {
            const saved = localStorage.getItem('homepage-chat-history');
            if (saved) {
                this.chatHistory = JSON.parse(saved);
                // 重新显示最近的几条消息
                const recentMessages = this.chatHistory.slice(-5);
                recentMessages.forEach(msg => {
                    if (!msg.isError) {
                        this.addMessage(msg.content, msg.sender);
                    }
                });
            }
        } catch (error) {
            console.warn('加载聊天历史失败:', error);
        }
    }
    
    clearHistory() {
        this.chatHistory = [];
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            // 保留欢迎消息
            const welcomeMessage = chatMessages.querySelector('.message-item');
            chatMessages.innerHTML = '';
            if (welcomeMessage) {
                chatMessages.appendChild(welcomeMessage);
            }
        }
        this.saveChatHistory();
    }
}

// 初始化首页AI对话系统
document.addEventListener('DOMContentLoaded', () => {
    window.homepageAIChat = new HomepageAIChat();
});

// 导出
window.HomepageAIChat = HomepageAIChat;
