import { UIUtils } from './utils/ui-utils.js';
import { MessageRenderer } from './message-renderer.js';

// UI交互模块
export class ChatUI {
    constructor() {
        // 初始化所有回调函数为安全空函数
        this.callbacks = {
            onLogin: () => console.warn('onLogin callback not set'),
            onSendMessage: () => console.warn('onSendMessage callback not set'),
            onSelectSession: () => console.warn('onSelectSession callback not set'),
            onSpeak: () => console.warn('onSpeak callback not set'),
            onRegenerate: () => console.warn('onRegenerate callback not set')
        };

        this.isInitialized = false;
        this.currentLang = 'zh';
        this.canUseSpeech = false;
        
        // MessageRenderer将在initialize中初始化
        this.messageRenderer = null;
    }
    
    async initialize() {
        try {
            // 等待DOM加载完成
            if (document.readyState === 'loading') {
                await new Promise(resolve => document.addEventListener('DOMContentLoaded', resolve));
            }

            // 初始化语言设置
            await this._initializeLanguage();

            // 绑定DOM元素
            this._bindDOMElements();

            // 验证必要的DOM元素
            this._validateRequiredElements();

            // 初始化消息渲染器
            this.messageRenderer = new MessageRenderer(this.dom.chatMessages);
            this.messageRenderer.setRegenerateCallback((messageId) => {
                this.callCallback('onRegenerate', messageId);
            });

            // 初始化事件监听
            this._initEventListeners();

            // 初始化highlight.js
            this._initCodeHighlight();
              // 初始化界面设置
            this._initializeSettings();
              // 检查登录状态，如果需要则显示登录模态框
            const token = localStorage.getItem('token');
            const guestMode = localStorage.getItem('guestMode');
            
            if (!token && !guestMode) {
                
                // 延迟显示登录模态框，让其他初始化完成
                setTimeout(() => {
                    this.showLoginModal();
                }, 1000);            } else if (guestMode === 'true') {
                
                this.updateUserStatus('访客模式');
                UIUtils.showToast('当前为访客模式，功能受限', 'info');
            }

            this.isInitialized = true;
            
        } catch (error) {
            console.error('ChatUI initialization failed:', error);
            UIUtils.showError('界面初始化失败');
            throw error;
        }
    }    // 私有方法: 绑定DOM元素
    _bindDOMElements() {
        this.dom = {
            loginModal: document.getElementById('loginModal'),
            loginButton: document.getElementById('loginButton'),
            guestModeButton: document.getElementById('guestModeButton'),
            loginUsername: document.getElementById('loginUsername'),
            loginPassword: document.getElementById('loginPassword'),
            loginError: document.getElementById('loginError'),
            userStatus: document.getElementById('userStatus'),
            messageInput: document.getElementById('messageInput'),
            sendButton: document.getElementById('sendButton'),
            chatMessages: document.getElementById('chatMessages'),
            recordButton: document.getElementById('recordButton'),
            imageGenButton: document.getElementById('imageGenButton'),
            ttsButton: document.getElementById('ttsButton'),
            historyButton: document.getElementById('historyBtn'),
            historySidebar: document.getElementById('historySidebar'),
            closeHistory: document.getElementById('closeHistory'),
            settingsBtn: document.getElementById('settingsBtn'),
            historySearch: document.getElementById('historySearch'),
            historyList: document.getElementById('historyList'),
            loadingOverlay: document.getElementById('loadingOverlay'),
            settingsModal: document.getElementById('settingsModal'),
            langSwitchBtn: document.getElementById('langSwitchBtn')
        };
    }    // 私有方法: 验证必要的DOM元素
    _validateRequiredElements() {
        const requiredElements = ['messageInput', 'sendButton', 'chatMessages'];
        const missingElements = [];
        
        for (const element of requiredElements) {
            if (!this.dom[element]) {
                missingElements.push(element);
            }
        }
        
        if (missingElements.length > 0) {
            console.warn(`部分DOM元素未找到: ${missingElements.join(', ')}`);
            // 不抛出错误，允许部分功能正常工作
        }
    }// 私有方法: 初始化事件监听
    _initEventListeners() {
        // 登录事件
        this._initLoginEvents();
        
        // 消息发送事件
        this._initMessageEvents();
        
        // 历史记录事件
        this._initHistoryEvents();
        
        // 语音相关事件
        this._initVoiceEvents();
        
        // 图像相关事件        this._initImageEvents();
        
        // 设置相关事件
        this._initSettingsEvents();
    }

    // 私有方法: 初始化登录相关事件
    _initLoginEvents() {
        if (this.dom.loginButton) {
            this.dom.loginButton.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const username = this.dom.loginUsername?.value?.trim();
                const password = this.dom.loginPassword?.value?.trim();
                
                if (!username || !password) {
                    this.showLoginError('请输入用户名和密码');
                    return;
                }
                
                try {
                    // 尝试使用全局auth模块进行登录
                    if (typeof auth !== 'undefined' && auth.login) {
                        const result = await auth.login(username, password);
                        if (result.success) {
                            this.hideLoginModal();
                            this.updateUserStatus(`欢迎, ${result.user?.username || '用户'}`);
                            UIUtils.showToast('登录成功', 'success');
                            this.clearLoginForm();
                        } else {
                            this.showLoginError(result.error || '登录失败');
                        }
                    } else {
                        // 如果auth模块不可用，使用本地登录逻辑
                        const response = await fetch(API_ENDPOINTS.AUTH_LOGIN, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ username, password })
                        });
                        
                        const data = await response.json();
                        if (data.success && data.token) {
                            localStorage.setItem('token', data.token);
                            localStorage.setItem('auth_token', data.token);
                            localStorage.setItem('user_data', JSON.stringify(data.user));
                            
                            this.hideLoginModal();
                            this.updateUserStatus(`欢迎, ${data.user?.username || '用户'}`);
                            UIUtils.showToast('登录成功', 'success');
                            this.clearLoginForm();
                        } else {
                            this.showLoginError(data.error || '登录失败');
                        }
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    this.showLoginError('网络错误，请稍后重试');
                }
            });
        }
        
        // 访客模式按钮事件
        if (this.dom.guestModeButton) {
            this.dom.guestModeButton.addEventListener('click', () => {
                
                localStorage.setItem('guestMode', 'true');
                this.hideLoginModal();
                this.updateUserStatus('访客模式');
                UIUtils.showToast('已进入访客模式，功能受限', 'info');
            });
        }
    }    // 私有方法: 初始化消息相关事件
    _initMessageEvents() {
        if (this.dom.sendButton) {
            this.dom.sendButton.addEventListener('click', () => this.sendMessage());
        }
        
        if (this.dom.messageInput) {
            this.dom.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // 输入监听 - 启用/禁用发送按钮
            this.dom.messageInput.addEventListener('input', (e) => {
                const hasText = e.target.value.trim().length > 0;
                if (this.dom.sendButton) {
                    this.dom.sendButton.disabled = !hasText;
                }
            });
        }
    }

    // 私有方法: 初始化历史记录相关事件
    _initHistoryEvents() {
        this.dom.historyButton?.addEventListener('click', () => {
            this.toggleHistorySidebar();
        });

        this.dom.closeHistory?.addEventListener('click', () => {
            this.toggleHistorySidebar(false);
        });

        this.dom.historySearch?.addEventListener('input', (e) => {
            this.filterHistoryItems(e.target.value.toLowerCase());
        });
    }    // 私有方法: 初始化语音相关事件
    _initVoiceEvents() {
        this.dom.recordButton?.addEventListener('click', () => {
            this.callCallback('onVoiceRecord');
        });
        
        this.dom.ttsButton?.addEventListener('click', () => {
            this.callCallback('onTTSToggle');
        });
    }

    // 私有方法: 初始化图像相关事件
    _initImageEvents() {
        this.dom.imageGenButton?.addEventListener('click', () => {
            this.callCallback('onImageGenerate');
        });
    }

    // 私有方法: 初始化设置相关事件
    _initSettingsEvents() {
        this.dom.settingsBtn?.addEventListener('click', () => {
            this.showSettings();
        });
        
        this.dom.langSwitchBtn?.addEventListener('click', () => {
            this.switchLanguage();
        });
    }
      // 设置回调函数
    setCallback(name, fn) {
        if (typeof fn === 'function') {
            this.callbacks[name] = fn;
        } else {
            console.error(`Invalid callback for ${name}`);
        }
    }
    
    // 批量设置回调函数
    setCallbacks(callbacks) {
        if (callbacks && typeof callbacks === 'object') {
            Object.keys(callbacks).forEach(name => {
                this.setCallback(name, callbacks[name]);
            });
        }
    }

    // 安全调用回调
    callCallback(name, ...args) {
        try {
            return this.callbacks[name](...args);
        } catch (error) {
            console.error(`Error in ${name} callback:`, error);
            UIUtils.showError('操作失败');
        }
    }    // 发送消息
    sendMessage() {
        const message = this.dom.messageInput.value.trim();
        if (message) {
            // 立即禁用发送按钮和清空输入框
            this.disableSendButton();
            this.dom.messageInput.value = '';
            
            // 调用发送消息回调
            this.callCallback('onSendMessage', message);
            
            // 重新焦点到输入框
            this.dom.messageInput.focus();
        }
    }// 显示消息（带动画效果）
    async addMessage(message) {
        if (!this.isInitialized) {
            throw new Error('ChatUI not initialized');
        }
        
        // 规范化消息对象
        const processedMessage = {
            id: message.id || Date.now().toString(),
            type: message.type === 'user' ? 'user' : 'ai',
            content: message.content,
            timestamp: message.timestamp || new Date()
        };
        
        // 渲染消息到界面
        await this.messageRenderer.render(processedMessage);
        
        // 自动滚动到最新消息
        this.scrollToBottom();
        
        // AI消息完成后，重新启用发送按钮
        if (processedMessage.type === 'ai') {
            this.enableSendButton();
        }
    }
    
    // 滚动到底部
    scrollToBottom() {
        if (this.dom.chatMessages) {
            this.dom.chatMessages.scrollTop = this.dom.chatMessages.scrollHeight;
        }
    }

    // 显示加载状态
    showLoading(message = '处理中...') {
        // 创建加载元素
        const loadingId = 'loading-' + Date.now();
        const loadingMessage = {
            id: loadingId,
            type: 'loading',
            content: message,
            timestamp: new Date().toISOString()
        };
        
        this.addMessage(loadingMessage);
        return loadingId;
    }
    
    // 隐藏加载状态
    hideLoading(loadingId) {
        const loadingElement = document.querySelector(`[data-message-id="${loadingId}"]`);
        if (loadingElement) {
            loadingElement.remove();
        }
    }
    
    // 显示错误信息
    showError(message, details = '') {
        const errorMessage = {
            id: 'error-' + Date.now(),
            type: 'error',
            content: message,
            details: details,
            timestamp: new Date().toISOString()
        };
        
        this.addMessage(errorMessage);
        
        // 同时显示通知
        if (typeof notifications !== 'undefined') {
            notifications.show(message, 'error', details);
        }
    }
    
    // 显示成功信息
    showSuccess(message) {
        if (typeof notifications !== 'undefined') {
            notifications.show(message, 'success');
        }
    }
    
    // 显示网络错误
    showNetworkError() {
        this.showError('网络连接异常', '请检查网络连接后重试');
    }
    
    // 显示API错误
    showAPIError(error) {
        const message = error.message || '服务器响应异常';
        this.showError(message, '请稍后重试或联系技术支持');
    }

    // 显示登录模态框
    showLoginModal() {
        if (this.dom.loginModal) {
            const loginModal = new bootstrap.Modal(this.dom.loginModal);
            loginModal.show();
        }
    }
    
    // 隐藏登录模态框
    hideLoginModal() {
        if (this.dom.loginModal) {
            const loginModal = bootstrap.Modal.getInstance(this.dom.loginModal);
            if (loginModal) {
                loginModal.hide();
            }
        }
    }
    
    // 显示设置模态框
    showSettings() {
        if (this.dom.settingsModal) {
            const settingsModal = new bootstrap.Modal(this.dom.settingsModal);
            settingsModal.show();
        }
    }
    
    // 隐藏设置模态框
    hideSettings() {
        if (this.dom.settingsModal) {
            const settingsModal = bootstrap.Modal.getInstance(this.dom.settingsModal);
            if (settingsModal) {
                settingsModal.hide();
            }
        }
    }
    
    // 渲染历史会话列表
    renderSessionList(sessions) {
        if (!this.dom.historyList || !sessions || !Array.isArray(sessions)) {
            return;
        }
        
        this.dom.historyList.innerHTML = '';
        
        if (sessions.length === 0) {
            this.dom.historyList.innerHTML = `
                <div class="text-center text-muted p-3">
                    <i class="bi bi-chat-text mb-2 fs-3"></i>
                    <p>暂无历史会话</p>
                </div>
            `;
            return;
        }
        
        sessions.forEach(session => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            item.dataset.sessionId = session.id;
            
            const date = new Date(session.timestamp || Date.now());
            const formattedDate = date.toLocaleDateString('zh-CN', {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric'
            });
            
            item.innerHTML = `
                <div class="d-flex flex-column">
                    <span class="fw-bold text-truncate" style="max-width: 180px;">
                        ${session.title || '未命名对话'}
                    </span>
                    <small class="text-muted">${formattedDate}</small>
                </div>
                <span class="badge bg-primary rounded-pill">${session.messageCount || 0}</span>
            `;
            
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.callCallback('onSelectSession', session.id);
                
                // 设置活动状态
                this.dom.historyList.querySelectorAll('.active').forEach(
                    el => el.classList.remove('active')
                );
                item.classList.add('active');
                
                // 在小屏幕上关闭侧边栏
                if (window.innerWidth < 992) {
                    this.toggleHistorySidebar(false);
                }
            });
            
            this.dom.historyList.appendChild(item);
        });
    }
    
    // 渲染历史消息
    renderHistory(messages) {
        if (!this.dom.chatMessages || !messages || !Array.isArray(messages)) {
            return;
        }
        
        // 清空现有消息
        this.dom.chatMessages.innerHTML = '';
        
        // 如果没有消息，显示欢迎提示
        if (messages.length === 0) {
            this.showWelcomeMessage();
            return;
        }
        
        // 渲染所有历史消息
        messages.forEach(message => this.addMessage(message));
    }
    
    // 显示欢迎消息
    showWelcomeMessage() {
        const welcomeDiv = document.createElement('div');
        welcomeDiv.className = 'text-center my-5 py-5';
        welcomeDiv.innerHTML = `
            <img src="logo.svg" alt="Logo" width="80" height="80" class="mb-4">
            <h2 class="mb-4">欢迎使用智能助手</h2>
            <p class="mb-4 text-muted">我可以帮助您回答问题、编写内容、提供创意灵感等</p>
            <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
                <button class="btn btn-outline-primary suggestion-btn">
                    <i class="bi bi-lightbulb"></i> 推荐一本好书
                </button>
                <button class="btn btn-outline-primary suggestion-btn">
                    <i class="bi bi-code-square"></i> 帮我写一个函数
                </button>
                <button class="btn btn-outline-primary suggestion-btn">
                    <i class="bi bi-translate"></i> 翻译一段文字
                </button>
                <button class="btn btn-outline-primary suggestion-btn">
                    <i class="bi bi-card-text"></i> 写一篇短文
                </button>
            </div>
        `;
        
        // 添加建议按钮事件
        welcomeDiv.querySelectorAll('.suggestion-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.dom.messageInput.value = btn.textContent.trim();
                this.dom.messageInput.focus();
                this.dom.sendButton.removeAttribute('disabled');
            });
        });
        
        this.dom.chatMessages.appendChild(welcomeDiv);
    }
    
    // 切换历史侧边栏
    toggleHistorySidebar(show) {
        if (!this.dom.historySidebar) return;
        
        const sidebar = bootstrap.Offcanvas.getInstance(this.dom.historySidebar) || 
                          new bootstrap.Offcanvas(this.dom.historySidebar);
        
        if (show === undefined) {
            // 切换显示状态
            sidebar.toggle();
        } else if (show) {
            // 显示
            sidebar.show();
        } else {
            // 隐藏
            sidebar.hide();
        }
    }

    // 清理资源
    cleanup() {
        if (this.messageRenderer) {
            this.messageRenderer.cleanup();
        }
    }

    // 初始化代码高亮
    _initCodeHighlight() {
        // 动态加载highlight.js样式
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.8.0/build/styles/default.min.css';
        document.head.appendChild(link);
        
        // 动态加载highlight.js脚本
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.8.0/build/highlight.min.js';
        script.onload = () => {
            hljs.highlightAll();
        };
        document.head.appendChild(script);
    }

    // 初始化图片上传
    initImageUpload() {
        // 创建隐藏的文件输入框
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);
        
        // 创建图片上传按钮
        const uploadButton = document.createElement('button');
        uploadButton.className = 'image-upload-button';
        uploadButton.innerHTML = '<i class="fas fa-image"></i>';
        uploadButton.title = '上传图片';
        
        // 添加到消息输入区域
        const inputContainer = this.dom.messageInput.parentElement;
        inputContainer.insertBefore(uploadButton, this.dom.messageInput);
        
        // 绑定事件
        uploadButton.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', this.handleImageUpload.bind(this));
    }

    // 处理图片上传
    async handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        try {
            const formData = new FormData();
            formData.append('image', file);
            
            const response = await fetch(API_ENDPOINTS.UPLOAD_IMAGE, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error('上传失败');
            
            const data = await response.json();
            const imageUrl = data.url;
            
            // 在输入框中插入图片标记
            const imageMarkdown = `![图片](${imageUrl})`;
            this.insertTextAtCursor(imageMarkdown);
            
        } catch (error) {
            this.showError('图片上传失败：' + error.message);
        }
        
        // 清空文件输入框
        event.target.value = '';
    }

    // 在光标位置插入文本
    insertTextAtCursor(text) {
        const input = this.dom.messageInput;
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const before = input.value.substring(0, start);
        const after = input.value.substring(end);
        
        input.value = before + text + after;
        input.selectionStart = input.selectionEnd = start + text.length;
        input.focus();
    }

    async login(email, password) {
        // 登录时用邮箱
        const res = await fetch(API_ENDPOINTS.AUTH_LOGIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (data.success && data.token) {
            localStorage.setItem('token', data.token);
        }
        return data;
    }

    // 注册
    async register(username, email, password) {
        const res = await fetch(API_ENDPOINTS.AUTH_REGISTER, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        });
        const data = await res.json();
        if (data.success && data.token) {
            localStorage.setItem('token', data.token);
        }
        return data;
    }

    // 带身份验证的请求
    async fetchWithAuth(url, options = {}) {
        // 自动带上 token
        const token = localStorage.getItem('token');
        options.headers = options.headers || {};
        if (token) {
            options.headers['Authorization'] = 'Bearer ' + token;
        }
        return fetch(url, options);
    }

    async switchLanguage() {
        this.currentLang = this.currentLang === 'zh' ? 'en' : 'zh';
        const t = this.i18n[this.currentLang];
        
        // 更新所有UI元素的文本
        document.getElementById('headerTitle').textContent = t.title;
        document.getElementById('langSwitchBtn').textContent = this.currentLang === 'zh' ? 'EN' : '中';
        document.getElementById('messageInput').placeholder = t.inputPlaceholder;
        document.getElementById('sendButton').innerHTML = `<i class="bi bi-send"></i> ${t.send}`;
        document.getElementById('recordButton').innerHTML = `<i class="bi bi-mic"></i> ${t.voice}`;
        document.getElementById('imageGenButton').innerHTML = `<i class="bi bi-image"></i> ${t.image}`;
        document.getElementById('ttsButton').innerHTML = `<i class="bi bi-play-circle"></i> ${t.tts}`;
    }
    
    enableSpeechSynthesis() {
        this.canUseSpeech = true;
        
    }
      async _initializeLanguage() {
        this.currentLang = 'zh';
        this.canUseSpeech = false;
        
        // 初始化i18n配置
        this.i18n = {
            zh: {
                title: 'AI智能助手',
                inputPlaceholder: '输入消息...',
                send: '发送',
                voice: '语音输入',
                image: '生成图片',
                tts: '播放语音'
            },
            en: {
                title: 'AI Assistant',
                inputPlaceholder: 'Type a message...',
                send: 'Send',
                voice: 'Voice',
                image: 'Image',
                tts: 'TTS'
            }
        };
        
        // 应用初始语言设置
        await this.switchLanguage();
    }

    // 初始化设置
    _initializeSettings() {
        // 读取并应用本地存储的设置
        const darkMode = localStorage.getItem('darkMode') === 'true';
        const voiceInput = localStorage.getItem('voiceInput') === 'true';
        const autoTTS = localStorage.getItem('autoTTS') === 'true';
        
        // 应用设置
        if (darkMode) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
          // 更新开关状态
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        if (darkModeSwitch) darkModeSwitch.checked = darkMode;
        
        const voiceInputSwitch = document.getElementById('voiceInputSwitch');
        if (voiceInputSwitch) voiceInputSwitch.checked = voiceInput;
        
        const autoTTSSwitch = document.getElementById('autoTTSSwitch');
        if (autoTTSSwitch) autoTTSSwitch.checked = autoTTS;
        
        // 设置开关事件监听
        document.getElementById('darkModeSwitch')?.addEventListener('change', (e) => {
            document.documentElement.setAttribute('data-bs-theme', e.target.checked ? 'dark' : 'light');
            localStorage.setItem('darkMode', e.target.checked);
        });
        
        document.getElementById('voiceInputSwitch')?.addEventListener('change', (e) => {
            localStorage.setItem('voiceInput', e.target.checked);
        });
        
        document.getElementById('autoTTSSwitch')?.addEventListener('change', (e) => {
            localStorage.setItem('autoTTS', e.target.checked);
        });    }

    // 更新用户状态显示
    updateUserStatus(status) {
        if (this.dom.userStatus) {
            this.dom.userStatus.textContent = status;
        }
    }

    // 清除用户状态
    clearUserStatus() {
        if (this.dom.userStatus) {
            this.dom.userStatus.textContent = '';
        }
    }
    // 启用发送按钮
    enableSendButton() {
        if (this.dom.sendButton && this.dom.messageInput) {
            const hasText = this.dom.messageInput.value.trim().length > 0;
            this.dom.sendButton.disabled = !hasText;
            if (hasText) {
                this.dom.messageInput.focus();
            }
        }
    }
    
    // 禁用发送按钮
    disableSendButton() {
        if (this.dom.sendButton) {
            this.dom.sendButton.disabled = true;
        }
    }

    // 显示登录错误
    showLoginError(message) {
        if (this.dom.loginError) {
            this.dom.loginError.textContent = message;
            this.dom.loginError.classList.remove('d-none');
        }
        UIUtils.showError(message);
    }
    
    // 清空登录表单
    clearLoginForm() {
        if (this.dom.loginUsername) this.dom.loginUsername.value = '';
        if (this.dom.loginPassword) this.dom.loginPassword.value = '';
        if (this.dom.loginError) this.dom.loginError.classList.add('d-none');
    }
}
