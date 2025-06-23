// 主应用脚本 - 页面初始化
import { ChatUI } from './chat/ui.js';
import { ChatAPI } from './chat/api.js';
import { ChatCore } from './chat/core.js';
import { HistoryRenderer } from './history/render.js';
import { HistoryManager } from './history/manager.js';

// 全局实例
let ui, api, historyRenderer, historyManager, chatCore;

document.addEventListener('DOMContentLoaded', async () => {
    // 初始化模块
    ui = new ChatUI();
    api = new ChatAPI();
    chatCore = new ChatCore();
    historyManager = new HistoryManager();
    historyRenderer = new HistoryRenderer(historyManager);
    
    // 初始化页面
    initPage();
    
    // 检查用户登录状态
    if (localStorage.getItem('token')) {
        updateUserInterface(true);
    } else {
        updateUserInterface(false);
    }
    
    // 如果在聊天页面，则加载聊天组件
    if (window.location.pathname.includes('chat') && typeof initChat === 'function') {
        try {
            const chatInstance = initChat();
            if (chatInstance) {
                console.log('聊天组件初始化成功');
            }
        } catch (error) {
            console.error('聊天组件初始化失败:', error);
        }
    }

    // 设置UI回调
    ui.setCallback('onLogin', async (credentials) => {
        try {
            ui.showLoading();
            // 用邮箱登录
            const response = await api.login(credentials.email, credentials.password);
            if (response.success) {
                // 加载会话历史
                const sessions = await api.getSessions();
                historyRenderer.renderSessions(sessions);
                // 隐藏登录界面，显示主界面
                ui.hideLoginModal();
                document.getElementById('userName').value = credentials.email;
            } else {
                ui.showError('登录失败: ' + response.error);
            }
        } catch (error) {
            ui.showError(error.message);
        } finally {
            ui.hideLoading();
        }
    });

    ui.setCallback('onSendMessage', async (message) => {
        try {
            // 显示用户消息
            ui.addMessage({
                type: 'user',
                content: message,
                timestamp: new Date()
            });

            // 发送到服务器
            const response = await api.sendMessage(message);
            
            if (response.success) {
                // 显示AI响应
                ui.addMessage({
                    type: 'ai',
                    content: response.data.content,
                    timestamp: new Date(response.data.timestamp)
                });

                // 保存到历史记录
                historyManager.addMessage(response.data);
            } else {
                throw new Error(response.error);
            }
        } catch (error) {
            ui.showError('发送消息失败: ' + error.message);
        }
    });

    // 语音相关功能
    ui.setCallback('onVoiceRecord', async () => {
        try {
            // 请求麦克风权限
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const mediaRecorder = new MediaRecorder(stream);
            const audioChunks = [];

            mediaRecorder.addEventListener('dataavailable', (event) => {
                audioChunks.push(event.data);
            });

            mediaRecorder.addEventListener('stop', async () => {
                const audioBlob = new Blob(audioChunks);
                const response = await api.speechToText(audioBlob);
                
                if (response.success) {
                    document.getElementById('messageInput').value = response.data.text;
                }
            });

            // 开始录音
            mediaRecorder.start();
            setTimeout(() => mediaRecorder.stop(), 5000); // 5秒后停止录音
        } catch (error) {
            ui.showError('语音输入失败: ' + error.message);
        }
    });

    // 设置会话历史回调
    historyManager.setCallback('onHistoryChange', (history) => {
        chatCore.displayHistory(history);
    });

    historyRenderer.setCallback('onSelectSession', (sessionId) => {
        historyManager.loadSession(sessionId);
    });    // 初始化WebSocket连接
    initializeWebSocket();// 显示登录界面
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        // 检查是否是Bootstrap模态框
        if (loginModal.classList.contains('modal')) {
            // Bootstrap模态框
            const modal = new bootstrap.Modal(loginModal);
            modal.show();
        } else {
            // Tailwind模态框
            loginModal.classList.remove('hidden');
            loginModal.classList.add('flex');
        }
    }
});

// 页面初始化函数
function initPage() {
    console.log('初始化页面...');
    
    // 设置动态背景
    setupDynamicBackground();
    
    // 设置AI助手
    setupAiAssistant();
    
    // 设置滚动动画
    setupScrollAnimations();
    
    // 初始化密码切换功能
    initPasswordToggle();
    
    // 如果在首页，加载首页特定功能
    if (window.location.pathname === '/' || window.location.pathname === '/index.html') {
        setupHomePage();
    }
    
    console.log('页面初始化完成');
}

// 更新用户界面状态
function updateUserInterface(isLoggedIn) {
    const loginBtn = document.getElementById('loginBtn');
    const mobileLoginBtn = document.getElementById('mobileLoginBtn');
    
    if (loginBtn) {
        if (isLoggedIn) {
            loginBtn.textContent = '控制台';
            loginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = '/dashboard.html';
            });
        } else {
            loginBtn.textContent = '登录';            loginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const loginModal = document.getElementById('loginModal');
                if (loginModal) {
                    // 检查是否是Bootstrap模态框
                    if (loginModal.classList.contains('modal')) {
                        // Bootstrap模态框
                        const modal = new bootstrap.Modal(loginModal);
                        modal.show();
                    } else {
                        // Tailwind模态框
                        loginModal.classList.remove('hidden');
                        loginModal.classList.add('flex');
                    }
                }
            });
        }
    }
    
    if (mobileLoginBtn) {
        if (isLoggedIn) {
            mobileLoginBtn.textContent = '控制台';
            mobileLoginBtn.addEventListener('click', () => {
                window.location.href = '/dashboard.html';
            });
        } else {
            mobileLoginBtn.textContent = '登录';            mobileLoginBtn.addEventListener('click', () => {
                const loginModal = document.getElementById('loginModal');
                if (loginModal) {
                    // 检查是否是Bootstrap模态框
                    if (loginModal.classList.contains('modal')) {
                        // Bootstrap模态框
                        const modal = new bootstrap.Modal(loginModal);
                        modal.show();
                    } else {
                        // Tailwind模态框
                        loginModal.classList.remove('hidden');
                        loginModal.classList.add('flex');
                    }
                }
            });
        }
    }
}

// 设置动态背景
function setupDynamicBackground() {
    const container = document.getElementById('backgroundContainer');
    if (!container) return;
    
    try {
        // 创建简单的粒子效果
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.classList.add('absolute', 'rounded-full', 'bg-tech-blue', 'opacity-20');
            
            // 随机大小
            const size = Math.random() * 6 + 2;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            // 随机位置
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            
            // 随机动画时间
            const duration = Math.random() * 20 + 10;
            particle.style.animation = `quantum-flicker ${duration}s infinite ease-in-out`;
            
            container.appendChild(particle);
        }
    } catch (error) {
        console.error('背景初始化失败:', error);
    }
}

// 设置AI助手按钮
function setupAiAssistant() {
    const aiButton = document.getElementById('xiaoDun');
    if (aiButton) {
        aiButton.addEventListener('click', () => {
            window.location.href = '/chat.html';
        });
    }
}

// 设置滚动动画
function setupScrollAnimations() {
    const scrollItems = document.querySelectorAll('.scroll-reveal');
    
    if (scrollItems.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        });
        
        scrollItems.forEach(item => {
            observer.observe(item);
        });
    }
}

// 设置首页特定功能
function setupHomePage() {
    console.log('设置首页功能');
    
    // 设置导航交互
    setupNavigation();
    
    // 其他首页特定的功能
}

// 设置导航交互
function setupNavigation() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // 平滑滚动
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // 在移动端关闭菜单
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
    });
}

// 初始化密码可见性切换
function initPasswordToggle() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = togglePassword.querySelector('i');
            if (icon) {
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
        });
    }
}

// 如果在聊天页面，初始化聊天功能
function initializeChat() {
    // 如果在其他页面加载了这个脚本，但没有chat相关模块，就不执行
    if (!window.ChatCore) {
        console.warn('ChatCore模块未加载，跳过聊天初始化');
        return;
    }
    
    try {
        // 这里仅在存在聊天模块时执行聊天初始化
        console.log('初始化聊天功能');
    } catch (error) {
        console.error('聊天初始化失败:', error);
    }
}

// WebSocket连接管理
let wsConnection = null;
let wsReconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 5;

// 更新WebSocket状态指示器
function updateWebSocketStatus(status, message) {
    const wsStatus = document.getElementById('wsStatus');
    const wsIndicator = document.getElementById('wsIndicator');
    const wsStatusText = document.getElementById('wsStatusText');
    
    if (!wsStatus || !wsIndicator || !wsStatusText) {
        console.log('WebSocket状态元素未找到，状态:', status, message);
        return;
    }
    
    // 更新状态文本
    wsStatusText.textContent = message;
    
    // 更新指示器颜色
    wsIndicator.className = 'ws-indicator';
    wsStatus.className = 'ws-status';
    
    switch (status) {
        case 'connected':
            wsIndicator.classList.add('connected');
            wsStatus.classList.add('connected');
            break;
        case 'disconnected':
            wsIndicator.classList.add('disconnected');
            wsStatus.classList.add('disconnected');
            break;
        case 'error':
            wsIndicator.classList.add('error');
            wsStatus.classList.add('error');
            break;
        case 'connecting':
            wsIndicator.classList.add('connecting');
            wsStatus.classList.add('connecting');
            break;
    }
    
    console.log(`WebSocket状态更新: ${status} - ${message}`);
}

// 初始化WebSocket连接
function initializeWebSocket() {
    // 连接到独立的WebSocket服务器端口
    const wsUrl = `ws://127.0.0.1:8080/ws`;
    console.log('尝试连接WebSocket:', wsUrl);
    
    try {
        wsConnection = new WebSocket(wsUrl);
        
        wsConnection.onopen = function(event) {
            console.log('WebSocket连接已建立');
            updateWebSocketStatus('connected', '已连接');
            wsReconnectAttempts = 0;
            
            // 发送测试消息
            wsConnection.send(JSON.stringify({ 
                type: 'test', 
                message: 'Hello from AlingAi frontend' 
            }));
            
            // 发送心跳
            setInterval(() => {
                if (wsConnection && wsConnection.readyState === WebSocket.OPEN) {
                    wsConnection.send(JSON.stringify({ type: 'ping' }));
                }
            }, 30000);
        };
        
        wsConnection.onmessage = function(event) {
            handleWebSocketMessage(event);
        };
        
        wsConnection.onclose = function(event) {
            console.log('WebSocket连接已关闭', event);
            updateWebSocketStatus('disconnected', '连接断开');
            
            // 自动重连
            if (wsReconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                wsReconnectAttempts++;
                console.log(`尝试重连 ${wsReconnectAttempts}/${MAX_RECONNECT_ATTEMPTS}`);
                setTimeout(() => {
                    initializeWebSocket();
                }, 3000 * wsReconnectAttempts);
            }
        };
        
        wsConnection.onerror = function(error) {
            console.error('WebSocket错误:', error);
            updateWebSocketStatus('error', '连接错误');
        };
        
    } catch (error) {
        console.error('WebSocket连接失败:', error);
        updateWebSocketStatus('error', '连接失败');
    }
}

// 处理WebSocket消息
function handleWebSocketMessage(event) {
    try {
        const data = JSON.parse(event.data);
        console.log('收到WebSocket消息:', data.type, data);
        
        switch (data.type) {
            case 'welcome':
                console.log('WebSocket欢迎消息:', data.message);
                showNotification(`WebSocket已连接: ${data.message}`, 'success');
                break;
                
            case 'pong':
                console.log('收到心跳响应');
                break;
                
            case 'test_response':
                console.log('测试响应:', data.message);
                showNotification(`测试响应: ${data.message}`, 'info');
                break;
                
            case 'status':
                console.log('系统状态更新:', data.data);
                updateSystemStatus(data.data);
                break;
                
            case 'systemStatus':
                console.log('系统状态:', data.data);
                displaySystemStatus(data.data);
                break;
                
            case 'chatResponse':
                console.log('AI聊天响应:', data.data);
                handleChatResponse(data.data);
                break;
                
            case 'broadcast':
                console.log('广播消息:', data.message);
                showNotification(`广播: ${data.message}`, 'info');
                break;
                
            case 'error':
                console.error('WebSocket错误消息:', data.message);
                showNotification(`WebSocket错误: ${data.message}`, 'error');
                break;
                
            // 保留原有的消息类型
            case 'chat_message':
                if (ui && ui.addMessage) {
                    ui.addMessage({
                        type: 'ai',
                        content: data.content,
                        timestamp: new Date(data.timestamp)
                    });
                }
                break;
                
            case 'notification':
                showNotification(data.message, data.level || 'info');
                break;
                
            case 'status_update':
                console.log('状态更新:', data.status);
                break;
                
            default:
                console.log('未知WebSocket消息类型:', data.type);
        }
    } catch (error) {
        console.error('处理WebSocket消息失败:', error);
    }
}

// 更新系统状态显示
function updateSystemStatus(data) {
    // 更新WebSocket状态指示器中的详细信息
    const wsStatusText = document.getElementById('wsStatusText');
    if (wsStatusText) {
        wsStatusText.title = `服务器时间: ${data.serverTime}\n内存使用: ${Math.round(data.memoryUsage.used / 1024 / 1024)}MB\n连接数: ${data.connectedClients}`;
    }
}

// 显示详细系统状态
function displaySystemStatus(data) {
    const statusInfo = `
系统状态:
- 服务器运行时间: ${Math.round(data.uptime / 3600)}小时
- 内存使用: ${Math.round(data.memoryUsage.used / 1024 / 1024)}MB
- 连接客户端数: ${data.connectedClients}
- Node.js版本: ${data.nodeVersion}
- 平台: ${data.platform}
    `;
    showNotification(statusInfo, 'info', 10000);
}

// 处理AI聊天响应
function handleChatResponse(data) {
    if (window.pageEnhancements) {
        window.pageEnhancements.addChatMessage('ai', data.message);
    }
}

// 通知系统
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification-item p-4 rounded-lg shadow-lg mb-2 transform translate-x-full transition-transform duration-300`;
    
    // 根据类型设置样式
    switch (type) {
        case 'success':
            notification.classList.add('bg-green-600', 'text-white');
            break;
        case 'error':
            notification.classList.add('bg-red-600', 'text-white');
            break;
        case 'warning':
            notification.classList.add('bg-yellow-600', 'text-white');
            break;
        default:
            notification.classList.add('bg-blue-600', 'text-white');
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(notification);
    
    // 显示动画
    requestAnimationFrame(() => {
        notification.classList.remove('translate-x-full');
    });
    
    // 自动移除
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }
}

// 初始化页面功能
function initializePageFeatures() {
    console.log('初始化页面功能...');
    
    // 初始化无障碍工具栏
    initializeAccessibilityToolbar();
    
    // 初始化返回顶部按钮
    initializeBackToTop();
    
    // 初始化页面进度条
    initializePageProgress();
    
    // 初始化量子动画
    initializeQuantumAnimations();
    
    // 初始化矩阵效果
    initMatrixEffect();
    
    console.log('页面功能初始化完成');
}

// 初始化无障碍工具栏
function initializeAccessibilityToolbar() {
    const toolbar = document.getElementById('accessibilityToolbar');
    const toggleBtn = document.getElementById('toggleAccessibilityToolbar');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (toolbar) {
                toolbar.classList.toggle('translate-x-full');
            }
        });
    }
    
    // 高对比度切换
    const highContrastBtn = document.getElementById('toggleHighContrast');
    if (highContrastBtn) {
        highContrastBtn.addEventListener('click', () => {
            document.body.classList.toggle('high-contrast');
            const isHighContrast = document.body.classList.contains('high-contrast');
            localStorage.setItem('highContrast', isHighContrast.toString());
            showNotification(isHighContrast ? '已开启高对比度' : '已关闭高对比度', 'info');
        });
    }
    
    // 字体大小调整
    const increaseFontBtn = document.getElementById('increaseFontSize');
    const decreaseFontBtn = document.getElementById('decreaseFontSize');
    
    if (increaseFontBtn) {
        increaseFontBtn.addEventListener('click', () => {
            adjustFontSize(1);
        });
    }
    
    if (decreaseFontBtn) {
        decreaseFontBtn.addEventListener('click', () => {
            adjustFontSize(-1);
        });
    }
    
    // 屏幕阅读器
    const screenReaderBtn = document.getElementById('toggleScreenReader');
    if (screenReaderBtn) {
        screenReaderBtn.addEventListener('click', () => {
            showNotification('屏幕阅读器功能已激活', 'info');
        });
    }
}

// 调整字体大小
function adjustFontSize(change) {
    const currentSize = parseInt(getComputedStyle(document.documentElement).fontSize) || 16;
    const newSize = Math.max(12, Math.min(24, currentSize + change));
    
    document.documentElement.style.fontSize = newSize + 'px';
    localStorage.setItem('fontSize', newSize.toString());
    showNotification(`字体大小已调整为 ${newSize}px`, 'info');
}

// 初始化返回顶部按钮
function initializeBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) return;
    
    // 监听滚动事件
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            backToTopBtn.classList.add('opacity-0', 'pointer-events-none');
        }
    });
    
    // 点击回到顶部
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// 初始化页面进度条
function initializePageProgress() {
    const progressBar = document.getElementById('pageProgress');
    if (!progressBar) return;
    
    window.addEventListener('scroll', () => {
        const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (window.pageYOffset / windowHeight) * 100;
        progressBar.style.width = scrolled + '%';
    });
}

// 初始化量子动画
function initializeQuantumAnimations() {
    // 创建量子粒子
    createQuantumParticles();
    
    // 创建轨道动画
    createOrbitalAnimations();
    
    console.log('量子动画初始化完成');
}

// 创建量子粒子效果
function createQuantumParticles() {
    const container = document.getElementById('quantumParticles');
    if (!container) return;

    // 创建多个量子粒子
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'absolute w-2 h-2 rounded-full bg-blue-400 opacity-30';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animation = `quantumFloat ${3 + Math.random() * 4}s ease-in-out infinite`;
        particle.style.animationDelay = Math.random() * 2 + 's';
        
        container.appendChild(particle);
    }
}

// 创建轨道动画
function createOrbitalAnimations() {
    const container = document.getElementById('orbitalContainer');
    if (!container) return;

    // 创建轨道系统
    const orbitalSystem = document.createElement('div');
    orbitalSystem.className = 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2';
    orbitalSystem.style.width = '200px';
    orbitalSystem.style.height = '200px';

    // 创建中心核心
    const core = document.createElement('div');
    core.className = 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-4 h-4 bg-yellow-400 rounded-full opacity-60';
    orbitalSystem.appendChild(core);

    // 创建轨道和电子
    for (let i = 0; i < 3; i++) {
        const orbit = document.createElement('div');
        orbit.className = 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 border border-blue-300 rounded-full opacity-20';
        const size = 60 + i * 40;
        orbit.style.width = size + 'px';
        orbit.style.height = size + 'px';
        
        const electron = document.createElement('div');
        electron.className = 'absolute w-2 h-2 bg-blue-500 rounded-full';
        electron.style.animation = `orbital ${2 + i}s linear infinite`;
        electron.style.top = '0';
        electron.style.left = '50%';
        electron.style.transform = 'translateX(-50%)';
        
        orbit.appendChild(electron);
        orbitalSystem.appendChild(orbit);
    }

    container.appendChild(orbitalSystem);
}

// 初始化矩阵效果
function initMatrixEffect() {
    const canvas = document.getElementById('matrixCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    
    // 设置画布大小
    const resizeCanvas = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    };
    
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // 矩阵字符
    const matrix = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789@#$%^&*()*&^%+-/~{[|`]}";
    const matrixArray = matrix.split("");

    const fontSize = 10;
    const columns = canvas.width / fontSize;
    const drops = [];

    // 初始化雨滴
    for (let x = 0; x < columns; x++) {
        drops[x] = 1;
    }

    // 绘制函数
    function drawMatrix() {
        ctx.fillStyle = 'rgba(0, 0, 0, 0.04)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#0F4';
        ctx.font = fontSize + 'px monospace';

        for (let i = 0; i < drops.length; i++) {
            const text = matrixArray[Math.floor(Math.random() * matrixArray.length)];
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);

            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    }

    // 启动动画
    setInterval(drawMatrix, 35);
}

// 在页面加载完成后初始化所有功能
document.addEventListener('DOMContentLoaded', function() {
    // 初始化页面功能
    initializePageFeatures();
    
    // 恢复用户设置
    restoreUserSettings();
});

// 恢复用户设置
function restoreUserSettings() {
    // 恢复字体大小
    const savedFontSize = localStorage.getItem('fontSize');
    if (savedFontSize) {
        document.documentElement.style.fontSize = savedFontSize + 'px';
    }
    
    // 恢复高对比度模式
    const highContrast = localStorage.getItem('highContrast');
    if (highContrast === 'true') {
        document.body.classList.add('high-contrast');
    }
}

// 全局量子动画系统实例 - 延迟初始化
window.quantumAnimation = null;

// DOM准备就绪后初始化量子动画系统
function initializeQuantumAnimationSystem() {
    // 检查DOM元素是否存在
    const quantumLoader = document.getElementById('quantumLoader');
    const quantumBalls = document.getElementById('quantumBalls');
    const validationText = document.getElementById('validationText');
    
    if (quantumLoader && quantumBalls && validationText) {
        window.quantumAnimation = new QuantumAnimationSystem();
        console.log('QuantumAnimationSystem 初始化成功');
        return true;
    } else {
        console.warn('QuantumAnimationSystem 初始化失败: DOM元素不存在', {
            quantumLoader: !!quantumLoader,
            quantumBalls: !!quantumBalls,
            validationText: !!validationText
        });
        return false;
    }
}

// 尝试初始化或延迟到DOM准备就绪
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeQuantumAnimationSystem);
} else {
    initializeQuantumAnimationSystem();
}

// 等待PageEnhancements类加载完成后再创建实例
if (typeof PageEnhancements !== 'undefined') {
    // 全局页面增强实例
    window.pageEnhancements = new PageEnhancements();
} else {
    // 延迟初始化
    setTimeout(() => {
        if (typeof PageEnhancements !== 'undefined') {
            window.pageEnhancements = new PageEnhancements();
        }
    }, 100);
}

// 添加验证功能的示例函数
window.validateWithQuantumAnimation = async function(validationFunction, successMessage, errorMessage) {
    try {
        // 检查量子动画系统是否已初始化
        if (window.quantumAnimation) {
            await window.quantumAnimation.simulateValidation(2000);
        }
        
        const result = await validationFunction();
        
        if (result.success) {
            if (window.quantumAnimation) {
                window.quantumAnimation.animateSuccess(successMessage || '验证成功！');
            }
        } else {
            if (window.quantumAnimation) {
                window.quantumAnimation.animateError(errorMessage || '验证失败！');
            }
        }
        
        return result;
    } catch (error) {
        if (window.quantumAnimation) {
            window.quantumAnimation.animateError(errorMessage || '验证失败！');
        }
        throw error;
    }
};

// 量子动画系统类
class QuantumAnimationSystem {
    constructor() {
        this.container = document.getElementById('quantumLoader');
        this.ballsContainer = document.getElementById('quantumBalls');
        this.validationText = document.getElementById('validationText');
        this.particles = [];
        
        // 检查DOM元素是否存在
        this.isInitialized = !!(this.container && this.ballsContainer && this.validationText);
        
        if (!this.isInitialized) {
            console.warn('QuantumAnimationSystem: 部分DOM元素未找到', {
                container: !!this.container,
                ballsContainer: !!this.ballsContainer,
                validationText: !!this.validationText
            });
        } else {
            console.log('QuantumAnimationSystem: 初始化成功，所有DOM元素已找到');
        }
    }
    
    async simulateValidation(duration = 2000) {
        if (!this.isInitialized || !this.container) {
            console.warn('QuantumAnimationSystem: 系统未正确初始化，跳过动画');
            return;
        }
        
        this.container.style.display = 'block';
        this.createQuantumBalls();
        
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve();
            }, duration);
        });
    }
    
    createQuantumBalls() {
        if (!this.ballsContainer) return;
        
        this.ballsContainer.innerHTML = '';
        
        for (let i = 0; i < 8; i++) {
            const ball = document.createElement('div');
            ball.className = 'quantum-ball';
            
            const angle = (i / 8) * 2 * Math.PI;
            const radius = 100;
            const x = Math.cos(angle) * radius + 120;
            const y = Math.sin(angle) * radius + 120;
            
            ball.style.left = x + 'px';
            ball.style.top = y + 'px';
            ball.style.animationDelay = (i * 0.2) + 's';
            
            this.ballsContainer.appendChild(ball);
        }
    }
    
    animateSuccess(message) {
        if (this.validationText) {
            this.validationText.textContent = message;
        }
        
        const balls = this.ballsContainer?.querySelectorAll('.quantum-ball');
        balls?.forEach(ball => {
            ball.classList.add('success');
        });
        
        setTimeout(() => {
            this.hide();
        }, 1500);
    }
      animateError(message) {
        if (this.validationText) {
            this.validationText.textContent = message;
        }
        
        const balls = this.ballsContainer?.querySelectorAll('.quantum-ball');
        balls?.forEach(ball => {
            ball.classList.add('error');
        });
        
        setTimeout(() => {
            this.hide();
        }, 1500);
    }
    
    hide() {
        if (this.container) {
            this.container.style.display = 'none';
        }
    }
}

// 页面增强类
class PageEnhancements {
    constructor() {
        this.init();
    }
    
    init() {
        // 初始化聊天小部件
        this.initChatWidget();
        
        // 初始化历史记录面板
        this.initHistoryPanel();
    }
    
    initChatWidget() {
        const aiAssistantBtn = document.getElementById('aiAssistantBtn');
        const chatWidget = document.getElementById('chatWidget');
        const closeChatWidget = document.getElementById('closeChatWidget');
        const sendChatMessage = document.getElementById('sendChatMessage');
        const chatInput = document.getElementById('chatInput');
        
        if (aiAssistantBtn && chatWidget) {
            aiAssistantBtn.addEventListener('click', () => {
                chatWidget.classList.toggle('hidden');
            });
        }
        
        if (closeChatWidget && chatWidget) {
            closeChatWidget.addEventListener('click', () => {
                chatWidget.classList.add('hidden');
            });
        }
        
        if (sendChatMessage && chatInput) {
            const sendMessage = () => {
                const message = chatInput.value.trim();
                if (message) {
                    this.addChatMessage('user', message);
                    chatInput.value = '';
                    
                    // 模拟AI响应
                    setTimeout(() => {
                        this.addChatMessage('ai', '感谢您的问题，我正在为您查找相关信息...');
                    }, 1000);
                }
            };
            
            sendChatMessage.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
    }
    
    addChatMessage(type, content) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-2';
        
        if (type === 'user') {
            messageDiv.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-gray-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-xs"></i>
                </div>
                <div class="bg-blue-600/50 rounded-lg p-3 text-sm max-w-xs">
                    <p>${content}</p>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-gradient-to-r from-longling to-tech-blue flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-xs"></i>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-3 text-sm max-w-xs">
                    <p>${content}</p>
                </div>
            `;
        }
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    initHistoryPanel() {
        const historySearchInput = document.getElementById('historySearchInput');
        
        if (historySearchInput) {
            historySearchInput.addEventListener('input', (e) => {
                this.filterHistory(e.target.value);
            });
        }
    }
    
    filterHistory(searchTerm) {
        // 历史记录过滤逻辑
        console.log('搜索历史记录:', searchTerm);
    }
}
