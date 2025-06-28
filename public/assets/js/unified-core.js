/**
 * AlingAi Pro - 统一核心脚本
 * 包含量子动画和交互功能
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 量子鼠标跟随效果
function initQuantumCursor() {
    // 检查是否为移动设备，如果是则不初始化鼠标效果
    if (window.matchMedia('(max-width: 768px)').matches || 
        'ontouchstart' in window || 
        navigator.maxTouchPoints > 0) {
        return;
    }
    
    // 创建鼠标跟随元素
    const cursor = document.createElement('div');
    cursor.className = 'quantum-cursor-fx';
    document.body.appendChild(cursor);
    
    // 鼠标移动事件
    document.addEventListener('mousemove', e => {
        cursor.style.left = `${e.clientX}px`;
        cursor.style.top = `${e.clientY}px`;
    });
    
    // 鼠标点击事件
    document.addEventListener('mousedown', () => {
        cursor.classList.add('click');
        setTimeout(() => cursor.classList.remove('click'), 300);
    });
    
    // 鼠标悬停在可交互元素上时的效果
    const interactiveElements = document.querySelectorAll('a, button, .clickable, [role="button"], .quantum-button, .sidebar-item, .nav-link, .glass-effect');
    
    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursor.classList.add('active');
        });
        
        el.addEventListener('mouseleave', () => {
            cursor.classList.remove('active');
        });
    });
    
    // 处理鼠标离开页面的情况
    document.addEventListener('mouseleave', () => {
        cursor.style.opacity = '0';
    });
    
    document.addEventListener('mouseenter', () => {
        cursor.style.opacity = '0.6';
    });
    
    // 处理文本输入区域
    const inputElements = document.querySelectorAll('input, textarea, [contenteditable="true"]');
    
    inputElements.forEach(el => {
        el.addEventListener('focus', () => {
            cursor.style.opacity = '0.2';
        });
        
        el.addEventListener('blur', () => {
            cursor.style.opacity = '0.6';
        });
    });
    
    // 性能优化：使用requestAnimationFrame
    let mouseX = 0;
    let mouseY = 0;
    let cursorX = 0;
    let cursorY = 0;
    
    document.addEventListener('mousemove', e => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
    
    function animateCursor() {
        const easing = 0.2;
        
        cursorX += (mouseX - cursorX) * easing;
        cursorY += (mouseY - cursorY) * easing;
        
        cursor.style.left = `${cursorX}px`;
        cursor.style.top = `${cursorY}px`;
        
        requestAnimationFrame(animateCursor);
    }
    
    animateCursor();
}

// 量子粒子初始化函数
function initQuantumParticles(elementId, options = {}) {
    const container = document.getElementById(elementId);
    if (!container) return;
    
    // 默认配置与用户配置合并
    const config = {
        particleCount: options.particleCount || 50,
        particleSize: options.particleSize || [2, 5],
        particleColor: options.particleColor || ['#3498db', '#9b59b6', '#2ecc71', '#f1c40f', '#e74c3c'],
        speed: options.speed || [0.5, 2],
        connectionDistance: options.connectionDistance || 150,
        connectionOpacity: options.connectionOpacity || 0.6,
        interactive: options.interactive !== undefined ? options.interactive : true
    };
    
    let particles = [];
    let width = container.offsetWidth;
    let height = container.offsetHeight;
    let canvas, ctx, animationFrame;
    
    // 创建画布
    function createCanvas() {
        canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        ctx = canvas.getContext('2d');
        container.appendChild(canvas);
    }
    
    // 粒子类
    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.size = Math.random() * (config.particleSize[1] - config.particleSize[0]) + config.particleSize[0];
            this.color = config.particleColor[Math.floor(Math.random() * config.particleColor.length)];
            this.speedX = (Math.random() - 0.5) * (config.speed[1] - config.speed[0]) + config.speed[0];
            this.speedY = (Math.random() - 0.5) * (config.speed[1] - config.speed[0]) + config.speed[0];
            this.opacity = Math.random() * 0.5 + 0.5;
        }
        
        update() {
            // 更新位置
            this.x += this.speedX;
            this.y += this.speedY;
            
            // 边界检查
            if (this.x < 0 || this.x > width) this.speedX *= -1;
            if (this.y < 0 || this.y > height) this.speedY *= -1;
            
            // 保持在边界内
            this.x = Math.max(0, Math.min(width, this.x));
            this.y = Math.max(0, Math.min(height, this.y));
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.globalAlpha = this.opacity;
            ctx.fill();
            ctx.globalAlpha = 1;
        }
    }
    
    // 初始化粒子
    function initParticles() {
        particles = [];
        for (let i = 0; i < config.particleCount; i++) {
            particles.push(new Particle());
        }
    }
    
    // 绘制粒子连线
    function drawConnections() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < config.connectionDistance) {
                    const opacity = (1 - distance / config.connectionDistance) * config.connectionOpacity;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = 'rgba(255, 255, 255, ' + opacity + ')';
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }
    }
    
    // 动画循环
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        drawConnections();
        
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        animationFrame = requestAnimationFrame(animate);
    }
    
    // 处理交互
    function handleInteraction() {
        if (!config.interactive) return;
        
        let mouseX, mouseY;
        let isActive = false;
        
        canvas.addEventListener('mousemove', e => {
            isActive = true;
            const rect = canvas.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
            
            particles.forEach(particle => {
                const dx = mouseX - particle.x;
                const dy = mouseY - particle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 100) {
                    const force = 0.2 * (1 - distance / 100);
                    particle.speedX += dx * force / 20;
                    particle.speedY += dy * force / 20;
                }
            });
        });
        
        canvas.addEventListener('mouseleave', () => {
            isActive = false;
        });
    }
    
    // 处理窗口大小变化
    function handleResize() {
        window.addEventListener('resize', () => {
            width = container.offsetWidth;
            height = container.offsetHeight;
            if (canvas) {
                canvas.width = width;
                canvas.height = height;
            }
        });
    }
    
    // 初始化
    function init() {
        createCanvas();
        initParticles();
        handleInteraction();
        handleResize();
        animate();
    }
    
    // 清理函数
    function cleanup() {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        if (canvas && canvas.parentNode) {
            canvas.parentNode.removeChild(canvas);
        }
    }
    
    init();
    
    // 返回清理函数，便于后续需要时清理资源
    return cleanup;
}

// 量子球体动画
function initQuantumSphere(elementId, options = {}) {
    const container = document.getElementById(elementId);
    if (!container) return;
    
    // 配置参数
    const config = {
        sphereCount: options.sphereCount || 3,
        particleCount: options.particleCount || 80,
        color: options.color || ['#3498db', '#9b59b6', '#2ecc71'],
        rotationSpeed: options.rotationSpeed || 0.01
    };
    
    let width = container.offsetWidth;
    let height = container.offsetHeight;
    let canvas, ctx, animationFrame;
    let spheres = [];
    let particles = [];
    
    // 创建画布
    function createCanvas() {
        canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        ctx = canvas.getContext('2d');
        container.appendChild(canvas);
    }
    
    // 量子球体类
    class QuantumSphere {
        constructor(index) {
            this.index = index;
            this.color = config.color[index % config.color.length];
            this.radius = 30 + index * 10;
            this.x = width / 2;
            this.y = height / 2;
            this.orbitRadius = 50 + index * 40;
            this.orbitSpeed = 0.02 - index * 0.005;
            this.angle = index * (Math.PI * 2 / config.sphereCount);
        }
        
        update() {
            this.angle += this.orbitSpeed;
            this.x = width / 2 + Math.cos(this.angle) * this.orbitRadius;
            this.y = height / 2 + Math.sin(this.angle) * this.orbitRadius;
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            const gradient = ctx.createRadialGradient(
                this.x, this.y, 0,
                this.x, this.y, this.radius
            );
            gradient.addColorStop(0, this.color);
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = gradient;
            ctx.globalAlpha = 0.7;
            ctx.fill();
            ctx.globalAlpha = 1;
        }
    }
    
    // 量子粒子类
    class QuantumParticle {
        constructor() {
            this.reset();
        }
        
        reset() {
            this.x = width / 2;
            this.y = height / 2;
            this.size = Math.random() * 3 + 1;
            this.color = config.color[Math.floor(Math.random() * config.color.length)];
            this.angle = Math.random() * Math.PI * 2;
            this.distance = Math.random() * 100 + 50;
            this.speed = Math.random() * 0.02 + 0.01;
            this.opacity = Math.random() * 0.5 + 0.2;
        }
        
        update() {
            this.angle += this.speed;
            
            if (spheres.length > 0) {
                const targetSphere = spheres[Math.floor(Math.random() * spheres.length)];
                this.x = targetSphere.x + Math.cos(this.angle) * this.distance;
                this.y = targetSphere.y + Math.sin(this.angle) * this.distance;
                
                const dx = this.x - width / 2;
                const dy = this.y - height / 2;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance > height / 1.5) {
                    this.reset();
                }
            }
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.globalAlpha = this.opacity;
            ctx.fill();
            ctx.globalAlpha = 1;
        }
    }
    
    // 初始化
    function init() {
        createCanvas();
        
        // 创建球体
        for (let i = 0; i < config.sphereCount; i++) {
            spheres.push(new QuantumSphere(i));
        }
        
        // 创建粒子
        for (let i = 0; i < config.particleCount; i++) {
            particles.push(new QuantumParticle());
        }
        
        // 处理窗口大小变化
        window.addEventListener('resize', () => {
            width = container.offsetWidth;
            height = container.offsetHeight;
            if (canvas) {
                canvas.width = width;
                canvas.height = height;
            }
        });
        
        animate();
    }
    
    // 动画循环
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        // 更新和绘制球体
        spheres.forEach(sphere => {
            sphere.update();
            sphere.draw();
        });
        
        // 更新和绘制粒子
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        animationFrame = requestAnimationFrame(animate);
    }
    
    // 清理函数
    function cleanup() {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        if (canvas && canvas.parentNode) {
            canvas.parentNode.removeChild(canvas);
        }
    }
    
    init();
    
    return cleanup;
}

// 数据初始化 - 仪表板数据
function initDashboardData() {
    return {
        todayChats: 126,
        apiCalls: 3475,
        tokenUsage: '54.3K',
        successRate: '99.8%',
        apiKeys: [
            { name: '生产环境', key: 'sk-prod-8f72b39c4de1a5e7...', permission: '完全访问', usage: '68%', status: 'active', created: '2024-06-01' },
            { name: '测试环境', key: 'sk-test-2a54c87b3f61d9e2...', permission: '只读', usage: '23%', status: 'active', created: '2024-06-10' }
        ],
        recentActivities: [
            { type: 'api_call', message: 'API调用成功 - /v1/completions', time: '2分钟前' },
            { type: 'user_login', message: '用户登录 - admin@example.com', time: '15分钟前' },
            { type: 'system_update', message: '系统更新完成 - v2.3.5', time: '1小时前' }
        ]
    };
}

// 加载仪表盘数据
function loadDashboardData(data) {
    // 加载统计卡片数据
    document.getElementById('todayChats') && (document.getElementById('todayChats').textContent = data.todayChats);
    document.getElementById('apiCalls') && (document.getElementById('apiCalls').textContent = data.apiCalls);
    document.getElementById('tokenUsage') && (document.getElementById('tokenUsage').textContent = data.tokenUsage);
    document.getElementById('successRate') && (document.getElementById('successRate').textContent = data.successRate);
    
    // API密钥表格填充
    const apiKeysTable = document.getElementById('apiKeysTable');
    if (apiKeysTable) {
        apiKeysTable.innerHTML = '';
        data.apiKeys.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td><div class="api-key-field api-key-masked">${item.key}</div></td>
                <td>${item.permission}</td>
                <td>
                    <div class="progress-cyber">
                        <div class="progress-fill" style="width: ${item.usage}"></div>
                    </div>
                    <span class="text-xs">${item.usage}</span>
                </td>
                <td>
                    <span class="status-indicator ${item.status === 'active' ? 'status-online' : 'status-error'}"></span>
                    <span>${item.status === 'active' ? '活跃' : '禁用'}</span>
                </td>
                <td>${item.created}</td>
                <td>
                    <button class="cyber-btn p-1 text-xs" onclick="copyApiKey(this)">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="cyber-btn p-1 text-xs" onclick="editApiKey(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="cyber-btn p-1 text-xs" onclick="deleteApiKey(this)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            apiKeysTable.appendChild(row);
        });
    }
    
    // 最近活动加载
    const recentActivitiesContainer = document.getElementById('recentActivities');
    if (recentActivitiesContainer) {
        recentActivitiesContainer.innerHTML = '';
        data.recentActivities.forEach(activity => {
            const div = document.createElement('div');
            div.className = 'flex items-center space-x-3 p-3 rounded-lg bg-white/5 mb-2';
            
            let iconClass = 'fas fa-exchange-alt';
            let iconBgClass = 'bg-quantum-blue';
            
            switch(activity.type) {
                case 'api_call':
                    iconClass = 'fas fa-exchange-alt';
                    iconBgClass = 'bg-quantum-blue';
                    break;
                case 'user_login':
                    iconClass = 'fas fa-user-shield';
                    iconBgClass = 'bg-quantum-green';
                    break;
                case 'system_update':
                    iconClass = 'fas fa-sync';
                    iconBgClass = 'bg-quantum-purple';
                    break;
            }
            
            div.innerHTML = `
                <div class="w-10 h-10 ${iconBgClass} rounded-full flex items-center justify-center">
                    <i class="${iconClass} text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-white">${activity.message}</p>
                    <p class="text-xs text-white/50">${activity.time}</p>
                </div>
            `;
            
            recentActivitiesContainer.appendChild(div);
        });
    }
}

// 聊天功能
function setupChatFunctions() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');
    
    if (!messageInput || !sendButton || !chatMessages) return;
    
    // 发送消息处理
    function sendMessage() {
        const message = messageInput.value.trim();
        if (message === '') return;
        
        // 添加用户消息
        appendMessage('user', message);
        
        // 清空输入框
        messageInput.value = '';
        
        // 显示AI响应中状态
        showTypingIndicator();
        
        // 模拟AI响应
        setTimeout(() => {
            removeTypingIndicator();
            appendMessage('ai', getAIResponse(message));
        }, 1500);
    }
    
    // 添加消息到聊天区域
    function appendMessage(type, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-4 ${type === 'user' ? 'ml-auto max-w-[80%]' : 'mr-auto max-w-[80%]'}`;
        
        messageDiv.innerHTML = `
            <div class="message-bubble p-4 rounded-2xl ${type === 'user' ? 'user-message ml-auto' : 'ai-message'}">
                <p class="whitespace-pre-wrap">${content}</p>
            </div>
            <div class="mt-1 text-xs text-gray-400 ${type === 'user' ? 'text-right' : ''}">
                ${type === 'user' ? '你' : 'AI助手'} · ${getCurrentTime()}
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // 显示AI正在输入指示器
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator mb-4 mr-auto';
        typingDiv.id = 'typing-indicator';
        
        typingDiv.innerHTML = `
            <div class="message-bubble ai-message p-4 rounded-2xl">
                <div class="flex items-center space-x-1">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // 移除AI正在输入指示器
    function removeTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    // 获取当前时间
    function getCurrentTime() {
        const now = new Date();
        return `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    }
    
    // 模拟AI响应
    function getAIResponse(message) {
        const responses = [
            "我理解你的问题，让我为你解答...",
            "这是一个很好的问题，根据我的分析...",
            "我已收到你的信息，正在处理中...",
            "根据最新的安全标准，我建议你...",
            "我可以帮助解决这个问题，首先..."
        ];
        
        return responses[Math.floor(Math.random() * responses.length)];
    }
    
    // 事件监听
    sendButton.addEventListener('click', sendMessage);
    
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // 初始欢迎消息
    appendMessage('ai', '你好！我是AlingAi Pro智能助手，有什么可以帮助你的吗？');
}

// 侧边栏功能
function setupSidebar() {
    const toggleButton = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }
    
    // 侧边栏项点击事件
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    const sections = document.querySelectorAll('.dashboard-section');
    
    sidebarItems.forEach(item => {
        item.addEventListener('click', () => {
            // 移除所有active类
            sidebarItems.forEach(i => i.classList.remove('active'));
            
            // 添加active类到当前项
            item.classList.add('active');
            
            // 显示对应的部分
            const sectionId = item.getAttribute('data-section') + '-section';
            sections.forEach(section => {
                section.classList.add('hidden');
            });
            
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
        });
    });
}

// 用户菜单功能
function setupUserMenu() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');
    
    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });
        
        // 点击其他地方关闭菜单
        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }
}

// 图表初始化函数
function setupCharts() {
    // 使用趋势图
    const usageTrendChart = document.getElementById('usageTrendChart');
    if (usageTrendChart) {
        const ctx = usageTrendChart.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                datasets: [
                    {
                        label: 'API调用',
                        data: [2350, 2540, 2120, 3240, 2980, 1890, 2450],
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: '对话次数',
                        data: [120, 145, 105, 180, 167, 90, 120],
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    }
    
    // 响应时间图表
    const responseTimeChart = document.getElementById('responseTimeChart');
    if (responseTimeChart) {
        const ctx = responseTimeChart.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['12时', '13时', '14时', '15时', '16时', '17时'],
                datasets: [
                    {
                        label: '响应时间 (ms)',
                        data: [12, 19, 28, 15, 14, 12],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(245, 158, 11, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(16, 185, 129, 0.6)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    }
}

// 数字动画效果
function animateNumbers() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(statValue => {
        const finalValue = statValue.textContent;
        
        // 只为纯数字执行动画
        if (!isNaN(parseInt(finalValue))) {
            const targetValue = parseInt(finalValue);
            let currentValue = 0;
            const duration = 1500; // ms
            const interval = 20; // ms
            const steps = duration / interval;
            const increment = targetValue / steps;
            
            statValue.textContent = '0';
            
            const counter = setInterval(() => {
                currentValue += increment;
                if (currentValue >= targetValue) {
                    clearInterval(counter);
                    statValue.textContent = finalValue;
                } else {
                    statValue.textContent = Math.floor(currentValue);
                }
            }, interval);
        }
    });
}

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    // 初始化量子动画
    const containers = {
        'quantum-particles': { type: 'particles' },
        'quantum-sphere': { type: 'sphere' }
    };
    
    for (const id in containers) {
        const el = document.getElementById(id);
        if (el) {
            if (containers[id].type === 'particles') {
                initQuantumParticles(id);
            } else if (containers[id].type === 'sphere') {
                initQuantumSphere(id);
            }
        }
    }
    
    // 设置仪表板
    if (document.getElementById('todayChats') || document.getElementById('apiCalls')) {
        const dashboardData = initDashboardData();
        loadDashboardData(dashboardData);
        setupCharts();
        animateNumbers();
    }
    
    // 设置聊天功能
    if (document.getElementById('messageInput') && document.getElementById('sendMessage')) {
        setupChatFunctions();
    }
    
    // 设置侧边栏
    if (document.getElementById('sidebar')) {
        setupSidebar();
    }
    
    // 设置用户菜单
    if (document.getElementById('userMenuBtn')) {
        setupUserMenu();
    }
}); 