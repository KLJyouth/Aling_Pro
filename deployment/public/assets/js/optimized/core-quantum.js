/**
 * core-quantum.js - 龙凌科技优化合并文件
 * 生成时间: 2025-05-31T14:42:35.299Z
 * 包含文件: quantum-particles.js, quantum-demo.js, quantum-chat-integrator.js, quantum-animation.js
 */


/* ===== quantum-particles.js ===== */
/**
 * 量子粒子系统 - 珑凌科技先进UI美学
 * 创建3D量子粒子动画、云流动效果和龙主题视觉元素
 */

class QuantumParticleSystem {
    constructor() {
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.particles = [];
        this.quantumFields = [];
        this.dragonElements = [];
        this.cloudParticles = [];
        this.init();
    }

    init() {
        this.setupScene();
        this.createQuantumParticles();
        this.createDragonSpirals();
        this.createCloudFlow();
        this.createHologramEffects();
        this.setupEventListeners();
        this.animate();
    }

    setupScene() {
        // 创建3D场景
        this.scene = new THREE.Scene();
        
        // 设置相机
        this.camera = new THREE.PerspectiveCamera(
            75,
            window.innerWidth / window.innerHeight,
            0.1,
            1000
        );
        this.camera.position.z = 50;

        // 设置渲染器
        this.renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: true
        });
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.setClearColor(0x000000, 0);

        // 将渲染器添加到背景容器
        const container = document.getElementById('backgroundContainer');
        if (container) {
            container.appendChild(this.renderer.domElement);
        }
    }

    createQuantumParticles() {
        // 量子粒子几何体
        const particleCount = 2000;
        const geometry = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);
        const velocities = new Float32Array(particleCount * 3);

        // 量子色彩系统
        const quantumColors = [
            [0.42, 0.08, 1.0],    // 量子紫
            [0.0, 0.8, 1.0],      // 科技蓝
            [0.8, 0.0, 1.0],      // 龙灵紫
            [0.0, 1.0, 0.6],      // 量子绿
            [1.0, 0.4, 0.0]       // 港妙橙
        ];

        for (let i = 0; i < particleCount; i++) {
            const i3 = i * 3;

            // 位置 - 创建量子场分布
            positions[i3] = (Math.random() - 0.5) * 200;
            positions[i3 + 1] = (Math.random() - 0.5) * 200;
            positions[i3 + 2] = (Math.random() - 0.5) * 100;

            // 颜色 - 随机选择量子色彩
            const colorIndex = Math.floor(Math.random() * quantumColors.length);
            colors[i3] = quantumColors[colorIndex][0];
            colors[i3 + 1] = quantumColors[colorIndex][1];
            colors[i3 + 2] = quantumColors[colorIndex][2];

            // 速度 - 量子漂移
            velocities[i3] = (Math.random() - 0.5) * 0.02;
            velocities[i3 + 1] = (Math.random() - 0.5) * 0.02;
            velocities[i3 + 2] = (Math.random() - 0.5) * 0.01;
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        geometry.setAttribute('velocity', new THREE.BufferAttribute(velocities, 3));

        // 量子粒子材质
        const material = new THREE.PointsMaterial({
            size: 2,
            vertexColors: true,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending
        });

        this.quantumParticles = new THREE.Points(geometry, material);
        this.scene.add(this.quantumParticles);
    }

    createDragonSpirals() {
        // 创建龙形螺旋粒子系统
        const spiralCount = 3;
        
        for (let s = 0; s < spiralCount; s++) {
            const spiral = new THREE.Group();
            const particleCount = 500;
            const geometry = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 3);
            const colors = new Float32Array(particleCount * 3);

            for (let i = 0; i < particleCount; i++) {
                const i3 = i * 3;
                const t = (i / particleCount) * Math.PI * 8;
                const radius = 20 + Math.sin(t * 0.5) * 10;

                // 龙形螺旋路径
                positions[i3] = Math.cos(t) * radius + s * 30 - 30;
                positions[i3 + 1] = Math.sin(t) * radius;
                positions[i3 + 2] = t * 2 - 25;

                // 龙主题色彩
                const dragonColor = s === 0 ? [1, 0.8, 0] : s === 1 ? [0.8, 0, 1] : [0, 0.8, 1];
                colors[i3] = dragonColor[0];
                colors[i3 + 1] = dragonColor[1];
                colors[i3 + 2] = dragonColor[2];
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

            const material = new THREE.PointsMaterial({
                size: 1.5,
                vertexColors: true,
                transparent: true,
                opacity: 0.6,
                blending: THREE.AdditiveBlending
            });

            const dragonSpiral = new THREE.Points(geometry, material);
            spiral.add(dragonSpiral);
            this.dragonElements.push(spiral);
            this.scene.add(spiral);
        }
    }

    createCloudFlow() {
        // 创建云流动粒子系统
        const cloudCount = 1000;
        const geometry = new THREE.BufferGeometry();
        const positions = new Float32Array(cloudCount * 3);
        const colors = new Float32Array(cloudCount * 3);

        for (let i = 0; i < cloudCount; i++) {
            const i3 = i * 3;

            // 云流动布局
            positions[i3] = (Math.random() - 0.5) * 300;
            positions[i3 + 1] = Math.random() * 150 - 75;
            positions[i3 + 2] = Math.random() * 200 - 100;

            // 云色彩系统
            const intensity = Math.random() * 0.5 + 0.3;
            colors[i3] = intensity;
            colors[i3 + 1] = intensity * 1.2;
            colors[i3 + 2] = 1.0;
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

        const material = new THREE.PointsMaterial({
            size: 3,
            vertexColors: true,
            transparent: true,
            opacity: 0.4,
            blending: THREE.AdditiveBlending
        });

        this.cloudFlow = new THREE.Points(geometry, material);
        this.scene.add(this.cloudFlow);
    }

    createHologramEffects() {
        // 创建全息投影网格
        const hologramGeometry = new THREE.PlaneGeometry(100, 100, 50, 50);
        const hologramMaterial = new THREE.MeshBasicMaterial({
            color: 0x00ffff,
            wireframe: true,
            transparent: true,
            opacity: 0.1
        });

        this.hologramGrid = new THREE.Mesh(hologramGeometry, hologramMaterial);
        this.hologramGrid.rotation.x = -Math.PI / 2;
        this.hologramGrid.position.y = -50;
        this.scene.add(this.hologramGrid);
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        const time = Date.now() * 0.001;

        // 量子粒子动画
        if (this.quantumParticles) {
            const positions = this.quantumParticles.geometry.attributes.position.array;
            const velocities = this.quantumParticles.geometry.attributes.velocity.array;

            for (let i = 0; i < positions.length; i += 3) {
                // 量子纠缠运动
                positions[i] += velocities[i] + Math.sin(time + i) * 0.01;
                positions[i + 1] += velocities[i + 1] + Math.cos(time + i) * 0.01;
                positions[i + 2] += velocities[i + 2];

                // 边界重置
                if (Math.abs(positions[i]) > 100) velocities[i] *= -1;
                if (Math.abs(positions[i + 1]) > 100) velocities[i + 1] *= -1;
                if (Math.abs(positions[i + 2]) > 50) velocities[i + 2] *= -1;
            }

            this.quantumParticles.geometry.attributes.position.needsUpdate = true;
            this.quantumParticles.rotation.y += 0.001;
        }

        // 龙螺旋动画
        this.dragonElements.forEach((spiral, index) => {
            spiral.rotation.y += 0.005 * (index + 1);
            spiral.rotation.z += 0.002 * (index + 1);
        });

        // 云流动动画
        if (this.cloudFlow) {
            const positions = this.cloudFlow.geometry.attributes.position.array;
            for (let i = 0; i < positions.length; i += 3) {
                positions[i] += Math.sin(time + i * 0.01) * 0.1;
                positions[i + 1] += Math.cos(time + i * 0.01) * 0.05;
            }
            this.cloudFlow.geometry.attributes.position.needsUpdate = true;
        }

        // 全息网格动画
        if (this.hologramGrid) {
            this.hologramGrid.rotation.z += 0.001;
            this.hologramGrid.material.opacity = 0.1 + Math.sin(time * 2) * 0.05;
        }

        this.renderer.render(this.scene, this.camera);
    }

    setupEventListeners() {
        // 窗口大小调整
        window.addEventListener('resize', () => {
            this.camera.aspect = window.innerWidth / window.innerHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // 鼠标交互
        document.addEventListener('mousemove', (event) => {
            const mouseX = (event.clientX / window.innerWidth) * 2 - 1;
            const mouseY = -(event.clientY / window.innerHeight) * 2 + 1;

            // 相机跟随鼠标
            this.camera.position.x += (mouseX * 10 - this.camera.position.x) * 0.05;
            this.camera.position.y += (mouseY * 10 - this.camera.position.y) * 0.05;
        });
    }
}

// 量子UI增强系统
class QuantumUIEnhancer {
    constructor() {
        this.initQuantumEffects();
        this.initDragonElements();
        this.initParticleOrbits();
        this.init3DCards();
    }

    initQuantumEffects() {
        // 为量子纠缠元素添加动态效果
        const quantumElements = document.querySelectorAll('.quantum-entanglement');
        quantumElements.forEach((element, index) => {
            setInterval(() => {
                const intensity = Math.sin(Date.now() * 0.001 + index) * 0.5 + 0.5;
                element.style.filter = `hue-rotate(${intensity * 360}deg) brightness(${1 + intensity * 0.3})`;
            }, 50);
        });
    }

    initDragonElements() {
        // 龙主题元素交互效果
        const dragonElements = document.querySelectorAll('.dragon-scale, .dragon-spiral, .dragon-border');
        dragonElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                element.style.transform = 'scale(1.05) rotateY(10deg)';
                element.style.filter = 'drop-shadow(0 0 20px rgba(255, 215, 0, 0.6))';
            });

            element.addEventListener('mouseleave', () => {
                element.style.transform = 'scale(1) rotateY(0deg)';
                element.style.filter = 'none';
            });
        });
    }

    initParticleOrbits() {
        // 粒子轨道动画
        const orbitElements = document.querySelectorAll('.particle-orbit');
        orbitElements.forEach(element => {
            const particles = [];
            for (let i = 0; i < 5; i++) {
                const particle = document.createElement('div');
                particle.className = 'absolute w-2 h-2 bg-tech-blue rounded-full opacity-50';
                particle.style.cssText = `
                    position: absolute;
                    width: 8px;
                    height: 8px;
                    background: radial-gradient(circle, #00bfff, #6c13ff);
                    border-radius: 50%;
                    opacity: 0.7;
                    pointer-events: none;
                    z-index: -1;
                `;
                element.appendChild(particle);
                particles.push(particle);
            }

            let angle = 0;
            setInterval(() => {
                particles.forEach((particle, index) => {
                    const radius = 50 + index * 10;
                    const x = Math.cos(angle + index * Math.PI * 0.4) * radius;
                    const y = Math.sin(angle + index * Math.PI * 0.4) * radius;
                    
                    particle.style.transform = `translate(${x}px, ${y}px)`;
                });
                angle += 0.02;
            }, 16);
        });
    }

    init3DCards() {
        // 3D卡片效果增强
        const card3DElements = document.querySelectorAll('.card-3d');
        card3DElements.forEach(element => {
            element.addEventListener('mousemove', (e) => {
                const rect = element.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / centerY * 10;
                const rotateY = (centerX - x) / centerX * 10;
                
                element.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
            });

            element.addEventListener('mouseleave', () => {
                element.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
            });
        });
    }
}

// 初始化系统
document.addEventListener('DOMContentLoaded', () => {
    // 等待Three.js加载完成
    if (typeof THREE !== 'undefined') {
        new QuantumParticleSystem();
        new QuantumUIEnhancer();
    } else {
        // 如果Three.js未加载，延迟初始化
        setTimeout(() => {
            if (typeof THREE !== 'undefined') {
                new QuantumParticleSystem();
                new QuantumUIEnhancer();
            }
        }, 1000);
    }
});

// 导出类供其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { QuantumParticleSystem, QuantumUIEnhancer };
}

/* ===== END quantum-particles.js ===== */


/* ===== quantum-demo.js ===== */
/**
 * 量子球聊天集成演示脚本
 * 展示3D量子球如何与聊天系统实时交互
 */

class QuantumChatDemo {
    constructor() {
        this.isRunning = false;
        this.demoSteps = [
            {
                name: '用户发送消息',
                action: 'userMessageSent',
                message: '你好，AI助手！',
                duration: 2000,
                description: '用户输入消息时，量子球呈现蓝色脉冲效果'
            },
            {
                name: 'AI开始思考',
                action: 'aiThinking',
                message: '',
                duration: 3000,
                description: 'AI处理消息时，量子球显示紫色波动和连接线加速'
            },
            {
                name: 'AI回复消息',
                action: 'aiResponseReceived',
                message: '你好！我是智能助手，有什么可以帮助您的吗？',
                duration: 2000,
                description: 'AI响应时，量子球呈现绿色光芒和光束效果'
            },
            {
                name: '网络错误',
                action: 'chatError',
                message: '网络连接失败',
                duration: 2000,
                description: '出现错误时，量子球显示红色警告和震动效果'
            }
        ];
        this.currentStep = 0;
    }
    
    async startDemo() {
        if (this.isRunning) {
            console.log('演示已在运行中...');
            return;
        }
        
        console.log('🎬 开始量子球聊天集成演示');
        this.isRunning = true;
        this.currentStep = 0;
        
        // 等待量子球集成器就绪
        if (!window.quantumChatIntegrator) {
            console.log('⏳ 等待量子球集成器就绪...');
            await this.waitForIntegrator();
        }
        
        // 创建演示UI
        this.createDemoUI();
        
        // 开始演示循环
        await this.runDemoLoop();
        
        this.isRunning = false;
        console.log('✅ 演示完成');
    }
    
    async waitForIntegrator() {
        return new Promise((resolve) => {
            const checkIntegrator = () => {
                if (window.quantumChatIntegrator) {
                    resolve();
                } else {
                    setTimeout(checkIntegrator, 500);
                }
            };
            checkIntegrator();
        });
    }
    
    createDemoUI() {
        // 创建演示控制面板
        const existingPanel = document.getElementById('quantumDemoPanel');
        if (existingPanel) {
            existingPanel.remove();
        }
        
        const demoPanel = document.createElement('div');
        demoPanel.id = 'quantumDemoPanel';
        demoPanel.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #6b46c1;
            backdrop-filter: blur(10px);
            z-index: 1000;
            min-width: 300px;
            font-family: system-ui, -apple-system, sans-serif;
        `;
        
        demoPanel.innerHTML = `
            <h3 style="margin: 0 0 15px 0; color: #8b5cf6;">🌌 量子球演示</h3>
            <div id="demoStatus" style="margin-bottom: 15px; font-size: 14px;">准备开始...</div>
            <div id="demoProgress" style="width: 100%; height: 4px; background: #333; border-radius: 2px; margin-bottom: 15px;">
                <div id="demoProgressBar" style="width: 0%; height: 100%; background: linear-gradient(45deg, #6b46c1, #8b5cf6); border-radius: 2px; transition: width 0.3s ease;"></div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button id="demoPause" style="padding: 8px 16px; background: #f59e0b; border: none; border-radius: 5px; color: white; cursor: pointer;">暂停</button>
                <button id="demoStop" style="padding: 8px 16px; background: #ef4444; border: none; border-radius: 5px; color: white; cursor: pointer;">停止</button>
                <button id="demoRestart" style="padding: 8px 16px; background: #10b981; border: none; border-radius: 5px; color: white; cursor: pointer;">重新开始</button>
            </div>
        `;
        
        document.body.appendChild(demoPanel);
        
        // 绑定按钮事件
        document.getElementById('demoPause').onclick = () => this.pauseDemo();
        document.getElementById('demoStop').onclick = () => this.stopDemo();
        document.getElementById('demoRestart').onclick = () => this.restartDemo();
    }
    
    async runDemoLoop() {
        while (this.isRunning && this.currentStep < this.demoSteps.length) {
            const step = this.demoSteps[this.currentStep];
            
            console.log(`🎭 演示步骤 ${this.currentStep + 1}: ${step.name}`);
            this.updateDemoUI(step);
            
            // 触发量子球动画
            await this.executeStep(step);
            
            // 等待动画完成
            await this.sleep(step.duration);
            
            this.currentStep++;
            this.updateProgress();
        }
        
        // 演示完成，重新开始
        if (this.isRunning) {
            this.currentStep = 0;
            await this.sleep(2000); // 暂停2秒后重新开始
            await this.runDemoLoop();
        }
    }
    
    async executeStep(step) {
        try {
            switch (step.action) {
                case 'userMessageSent':
                    if (window.quantumChatIntegrator) {
                        await window.quantumChatIntegrator.triggerUserMessageAnimation(step.message);
                    }
                    break;
                    
                case 'aiThinking':
                    if (window.quantumChatIntegrator) {
                        await window.quantumChatIntegrator.triggerAIThinkingAnimation();
                    }
                    break;
                    
                case 'aiResponseReceived':
                    if (window.quantumChatIntegrator) {
                        await window.quantumChatIntegrator.triggerAIResponseAnimation(step.message);
                    }
                    break;
                    
                case 'chatError':
                    if (window.quantumChatIntegrator) {
                        await window.quantumChatIntegrator.triggerErrorAnimation(new Error(step.message));
                    }
                    break;
            }
        } catch (error) {
            console.error('演示步骤执行失败:', error);
        }
    }
    
    updateDemoUI(step) {
        const statusDiv = document.getElementById('demoStatus');
        if (statusDiv) {
            statusDiv.innerHTML = `
                <div style="font-weight: bold; color: #8b5cf6; margin-bottom: 5px;">${step.name}</div>
                <div style="font-size: 12px; color: #ccc;">${step.description}</div>
                ${step.message ? `<div style="font-size: 12px; color: #10b981; margin-top: 5px; font-style: italic;">"${step.message}"</div>` : ''}
            `;
        }
    }
    
    updateProgress() {
        const progressBar = document.getElementById('demoProgressBar');
        if (progressBar) {
            const progress = ((this.currentStep) / this.demoSteps.length) * 100;
            progressBar.style.width = `${progress}%`;
        }
    }
    
    pauseDemo() {
        this.isRunning = false;
        console.log('⏸️ 演示已暂停');
    }
    
    stopDemo() {
        this.isRunning = false;
        this.currentStep = 0;
        
        const panel = document.getElementById('quantumDemoPanel');
        if (panel) {
            panel.remove();
        }
        
        console.log('⏹️ 演示已停止');
    }
    
    async restartDemo() {
        this.stopDemo();
        await this.sleep(500);
        await this.startDemo();
    }
    
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // 手动触发单个动画的方法
    async triggerAnimation(action, message = '') {
        const step = this.demoSteps.find(s => s.action === action) || {
            action,
            message,
            duration: 2000,
            name: action,
            description: `手动触发 ${action} 动画`
        };
        
        console.log(`🎯 手动触发动画: ${action}`);
        await this.executeStep(step);
    }
}

// 全局演示实例
window.quantumChatDemo = new QuantumChatDemo();

// 自动检测页面环境并提供适当的演示
document.addEventListener('DOMContentLoaded', () => {
    // 检查是否在首页
    if (window.location.pathname === '/' || window.location.pathname.includes('index.html')) {
        console.log('🏠 检测到首页环境，量子球演示可用');
        
        // 添加快捷键启动演示
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'Q') {
                e.preventDefault();
                window.quantumChatDemo.startDemo();
                console.log('🚀 快捷键启动演示: Ctrl+Shift+Q');
            }
        });
        
        // 延迟自动启动演示
        setTimeout(() => {
            if (window.quantumChatIntegrator) {
                console.log('🎬 3秒后自动启动量子球演示，按 Ctrl+Shift+Q 可随时启动');
                setTimeout(() => {
                    window.quantumChatDemo.startDemo();
                }, 3000);
            }
        }, 5000);
    }
    
    // 检查是否在聊天页面
    if (window.location.pathname.includes('chat.html')) {
        console.log('💬 检测到聊天页面环境');
        
        // 为聊天页面添加量子球状态指示器
        setTimeout(() => {
            const statusIndicator = document.createElement('div');
            statusIndicator.className = 'quantum-status-indicator';
            statusIndicator.id = 'quantumStatusIndicator';
            statusIndicator.title = '量子球连接状态';
            document.body.appendChild(statusIndicator);
            
            // 监听集成器状态
            if (window.quantumChatIntegrator) {
                window.quantumChatIntegrator.on('connected', () => {
                    statusIndicator.className = 'quantum-status-indicator';
                });
                
                window.quantumChatIntegrator.on('disconnected', () => {
                    statusIndicator.className = 'quantum-status-indicator disconnected';
                });
                
                window.quantumChatIntegrator.on('quantumBallAnimation', (data) => {
                    if (data.eventType === 'aiThinking') {
                        statusIndicator.className = 'quantum-status-indicator thinking';
                        setTimeout(() => {
                            statusIndicator.className = 'quantum-status-indicator';
                        }, 3000);
                    }
                });
            }
        }, 2000);
    }
    
    // 检查是否在测试页面
    if (window.location.pathname.includes('quantum-test.html')) {
        console.log('🧪 检测到测试页面环境');
        
        // 为测试页面添加快速演示按钮
        setTimeout(() => {
            const testContainer = document.querySelector('.test-container');
            if (testContainer) {
                const demoButton = document.createElement('button');
                demoButton.className = 'test-button';
                demoButton.textContent = '开始完整演示';
                demoButton.style.background = 'linear-gradient(45deg, #10b981, #34d399)';
                demoButton.onclick = () => window.quantumChatDemo.startDemo();
                
                // 插入到测试按钮区域
                const buttonContainer = testContainer.querySelector('.text-center');
                if (buttonContainer) {
                    buttonContainer.appendChild(document.createElement('br'));
                    buttonContainer.appendChild(demoButton);
                }
            }
        }, 1000);
    }
});

// 导出演示类
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuantumChatDemo;
}

console.log('🎭 量子球聊天集成演示模块已加载');
console.log('💡 使用 window.quantumChatDemo.startDemo() 开始演示');
console.log('⌨️ 首页快捷键: Ctrl+Shift+Q');

/* ===== END quantum-demo.js ===== */


/* ===== quantum-chat-integrator.js ===== */
/**
 * 量子球-聊天系统实时集成模块
 * 连接3D量子球可视化与聊天功能，提供实时动画反馈
 */

class QuantumChatIntegrator {
    constructor() {
        this.wsConnection = null;
        this.quantumBallSystem = null;
        this.chatSystem = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 3000;
        this.quantumSphereRef = null; // 量子球系统引用（首页用）
        
        // 动画状态管理
        this.currentAnimation = null;
        this.animationQueue = [];
        this.isAnimating = false;
        
        // 事件监听器
        this.eventListeners = new Map();
        
        this.init();
    }
    
    // 设置量子球系统引用（用于首页）
    setQuantumSphereReference(quantumSphereObjects) {
        this.quantumSphereRef = quantumSphereObjects;
        console.log('🎯 量子球系统引用已设置', Object.keys(quantumSphereObjects));
    }
    
    async init() {
        console.log('🌊 初始化量子球-聊天集成系统...');
        
        // 等待量子球系统就绪
        await this.waitForQuantumBallSystem();
        
        // 初始化WebSocket连接
        await this.initWebSocketConnection();
        
        // 设置聊天事件监听
        this.setupChatEventListeners();
        
        // 设置页面级事件监听
        this.setupPageEventListeners();
        
        console.log('✅ 量子球-聊天集成系统初始化完成');
    }
      async waitForQuantumBallSystem() {
        return new Promise((resolve) => {
            const checkQuantumSystem = () => {
                // 检查量子球系统和动画系统是否存在
                const hasQuantumParticleSystem = window.quantumParticleSystem || 
                    document.getElementById('backgroundContainer') ||
                    window.QuantumParticleSystem;
                
                const hasQuantumAnimationSystem = window.quantumAnimation || 
                    window.QuantumAnimationSystem;
                
                if (hasQuantumParticleSystem && hasQuantumAnimationSystem) {
                    this.quantumBallSystem = window.quantumParticleSystem || 
                                           window.QuantumParticleSystem ||
                                           this.createQuantumBallProxy();
                    
                    console.log('🎯 量子球系统和动画系统已就绪');
                    resolve();
                } else {
                    console.log('⏳ 等待量子系统初始化...', {
                        quantumParticleSystem: !!hasQuantumParticleSystem,
                        quantumAnimationSystem: !!hasQuantumAnimationSystem
                    });
                    setTimeout(checkQuantumSystem, 500);
                }
            };
            
            checkQuantumSystem();
        });
    }
      createQuantumBallProxy() {
        // 创建量子球系统代理，用于兼容性
        return {
            updateState: (state) => {
                console.log('📊 量子球状态更新:', state);
                this.triggerVisualFeedback(state);
            },
            triggerAnimation: (type, data) => {
                console.log('🎬 触发量子球动画:', type, data);
                // 使用正确的方法名
                this.triggerChatEvent(type, data);
            }
        };
    }

    // 添加视觉反馈方法
    triggerVisualFeedback(state) {
        console.log('🎨 应用视觉反馈:', state);
        
        // 应用到背景容器
        const container = document.getElementById('backgroundContainer');
        if (container && state.mode) {
            container.className = `quantum-${state.mode}`;
            
            // 应用颜色变化
            if (state.colors && state.colors.length > 0) {
                this.applyColorAnimation(container, state.colors);
            }
            
            // 应用效果动画
            if (state.effects && state.effects.length > 0) {
                this.applyEffectAnimation(container, state.effects);
            }
        }
        
        // 应用到量子加载器
        const quantumLoader = document.getElementById('quantumLoader');
        if (quantumLoader && state.visible) {
            quantumLoader.style.display = 'flex';
            
            setTimeout(() => {
                quantumLoader.style.display = 'none';
            }, state.duration || 2000);
        }
    }
    
    async initWebSocketConnection() {
        try {
            const wsUrl = `ws://${window.location.host}/ws`;
            console.log('🔌 连接WebSocket:', wsUrl);
            
            this.wsConnection = new WebSocket(wsUrl);
            
            this.wsConnection.onopen = () => {
                console.log('✅ WebSocket连接已建立');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                
                // 发送初始化消息
                this.sendWebSocketMessage({
                    type: 'quantumBallSync',
                    action: 'init',
                    data: {
                        page: window.location.pathname,
                        timestamp: new Date().toISOString()
                    }
                });
                
                this.emit('connected');
            };
            
            this.wsConnection.onmessage = (event) => {
                this.handleWebSocketMessage(event);
            };
            
            this.wsConnection.onclose = () => {
                console.log('🔌 WebSocket连接已关闭');
                this.isConnected = false;
                this.scheduleReconnect();
                this.emit('disconnected');
            };
            
            this.wsConnection.onerror = (error) => {
                console.error('❌ WebSocket连接错误:', error);
                this.emit('error', error);
            };
            
        } catch (error) {
            console.error('❌ WebSocket初始化失败:', error);
            this.scheduleReconnect();
        }
    }
    
    scheduleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`🔄 尝试第${this.reconnectAttempts}次重连...`);
            
            setTimeout(() => {
                this.initWebSocketConnection();
            }, this.reconnectInterval * this.reconnectAttempts);
        } else {
            console.error('❌ WebSocket重连失败，已达到最大重试次数');
            this.emit('reconnectFailed');
        }
    }
    
    handleWebSocketMessage(event) {
        try {
            const message = JSON.parse(event.data);
            console.log('📨 收到WebSocket消息:', message.type);
            
            switch (message.type) {
                case 'quantumBallUpdate':
                    this.handleQuantumBallUpdate(message.data);
                    break;
                    
                case 'quantumBallAnimation':
                    this.handleQuantumBallAnimation(message);
                    break;
                    
                case 'chatResponse':
                    this.handleChatResponse(message.data);
                    break;
                    
                case 'welcome':
                    console.log('🎉 WebSocket欢迎消息:', message.message);
                    break;
                    
                case 'pong':
                    // 心跳响应
                    break;
                    
                default:
                    console.log('📨 未处理的消息类型:', message.type);
            }
        } catch (error) {
            console.error('❌ 处理WebSocket消息失败:', error);
        }
    }
    
    handleQuantumBallUpdate(data) {
        console.log('🌊 量子球状态更新:', data);
        
        if (this.quantumBallSystem && this.quantumBallSystem.updateState) {
            this.quantumBallSystem.updateState(data);
        } else {
            this.applyQuantumBallStyles(data);
        }
        
        this.emit('quantumBallUpdate', data);
    }
      handleQuantumBallAnimation(message) {
        console.log('🎬 量子球动画:', message.eventType);
        
        const { eventType, animation, chatData } = message;
        
        // 应用动画到首页量子球系统
        this.applyAnimationToQuantumSphere(eventType, animation);
        
        // 添加到动画队列
        this.animationQueue.push({
            type: eventType,
            animation: animation,
            data: chatData,
            timestamp: Date.now()
        });
        
        // 处理动画队列
        this.processAnimationQueue();
        
        this.emit('quantumBallAnimation', { eventType, animation, chatData });
    }
    
    async processAnimationQueue() {
        if (this.isAnimating || this.animationQueue.length === 0) {
            return;
        }
        
        this.isAnimating = true;
        
        while (this.animationQueue.length > 0) {
            const animationItem = this.animationQueue.shift();
            await this.executeAnimation(animationItem);
        }
        
        this.isAnimating = false;
    }
    
    async executeAnimation(animationItem) {
        const { type, animation, data } = animationItem;
        
        console.log(`🎭 执行动画: ${type}`, animation);
        
        try {
            // 应用动画到量子球系统
            if (this.quantumBallSystem && this.quantumBallSystem.triggerAnimation) {
                this.quantumBallSystem.triggerAnimation(type, animation);
            } else {
                await this.executeAnimationFallback(type, animation);
            }
            
            // 等待动画完成
            if (animation.duration) {
                await new Promise(resolve => setTimeout(resolve, animation.duration));
            }
            
        } catch (error) {
            console.error('❌ 执行动画失败:', error);
        }
    }
    
    async executeAnimationFallback(type, animation) {
        // 后备动画实现
        const container = document.getElementById('backgroundContainer');
        if (!container) return;
        
        const animationClass = `quantum-animation-${type}`;
        container.classList.add(animationClass);
        
        // 应用样式变化
        if (animation.colors) {
            this.applyColorAnimation(container, animation.colors);
        }
        
        if (animation.effects) {
            this.applyEffectAnimation(container, animation.effects);
        }
        
        // 清理动画类
        setTimeout(() => {
            container.classList.remove(animationClass);
        }, animation.duration || 2000);
    }
    
    applyColorAnimation(container, colors) {
        const colorMappings = {
            'tech-blue': '#0ea5e9',
            'quantum': '#6b46c1',
            'longling': '#8b5cf6',
            'red': '#ef4444',
            'orange': '#f97316'
        };
        
        const cssColors = colors.map(color => colorMappings[color] || color).join(', ');
        container.style.background = `linear-gradient(45deg, ${cssColors})`;
        container.style.opacity = '0.3';
        
        setTimeout(() => {
            container.style.background = '';
            container.style.opacity = '';
        }, 2000);
    }
    
    applyEffectAnimation(container, effects) {
        effects.forEach(effect => {
            switch (effect) {
                case 'pulse':
                    container.style.animation = 'pulse 1s ease-in-out infinite';
                    break;
                case 'shake':
                    container.style.animation = 'shake 0.5s ease-in-out infinite';
                    break;
                case 'glow':
                    container.style.boxShadow = '0 0 20px rgba(107, 70, 193, 0.5)';
                    break;
            }
        });
        
        setTimeout(() => {
            container.style.animation = '';
            container.style.boxShadow = '';
        }, 2000);
    }
    
    setupChatEventListeners() {
        console.log('📡 设置聊天事件监听器...');
        
        // 监听消息发送事件
        document.addEventListener('chatMessageSent', (event) => {
            this.handleChatMessageSent(event.detail);
        });
        
        // 监听AI响应事件
        document.addEventListener('chatResponseReceived', (event) => {
            this.handleChatResponseReceived(event.detail);
        });
        
        // 监听聊天错误事件
        document.addEventListener('chatError', (event) => {
            this.handleChatError(event.detail);
        });
        
        // 监听聊天模块的直接调用
        if (window.chatInstance) {
            this.integrateChatInstance(window.chatInstance);
        }
        
        // 监听聊天实例创建
        document.addEventListener('chatInstanceCreated', (event) => {
            this.integrateChatInstance(event.detail);
        });
    }
    
    integrateChatInstance(chatInstance) {
        console.log('🔗 集成聊天实例...');
        
        this.chatSystem = chatInstance;
        
        // 如果聊天实例有事件系统，则集成
        if (chatInstance.core && chatInstance.core.on) {
            chatInstance.core.on('messageSent', (data) => {
                this.triggerChatEvent('userMessageSent', data);
            });
            
            chatInstance.core.on('responseReceived', (data) => {
                this.triggerChatEvent('aiResponseReceived', data);
            });
            
            chatInstance.core.on('error', (data) => {
                this.triggerChatEvent('chatError', data);
            });
        }
        
        // 覆盖聊天API调用以注入量子球事件
        if (chatInstance.api && chatInstance.api.sendMessage) {
            const originalSendMessage = chatInstance.api.sendMessage.bind(chatInstance.api);
            
            chatInstance.api.sendMessage = async (message, options = {}) => {
                // 触发用户消息动画
                this.triggerChatEvent('userMessageSent', { message, options });
                
                try {
                    // 触发AI思考动画
                    this.triggerChatEvent('aiThinking', { message });
                    
                    const result = await originalSendMessage(message, options);
                    
                    // 触发AI响应动画
                    this.triggerChatEvent('aiResponseReceived', { 
                        message, 
                        response: result,
                        options 
                    });
                    
                    return result;
                } catch (error) {
                    // 触发错误动画
                    this.triggerChatEvent('chatError', { message, error, options });
                    throw error;
                }
            };
        }
    }
    
    handleChatMessageSent(data) {
        console.log('💬 用户消息发送:', data);
        this.triggerChatEvent('userMessageSent', data);
    }
    
    handleChatResponseReceived(data) {
        console.log('🤖 AI响应接收:', data);
        this.triggerChatEvent('aiResponseReceived', data);
    }
    
    handleChatError(data) {
        console.log('❌ 聊天错误:', data);
        this.triggerChatEvent('chatError', data);
    }
      triggerChatEvent(eventType, data) {
        console.log(`🚀 触发聊天事件: ${eventType}`, data);
        
        // 首先尝试直接应用到首页量子球系统
        if (this.quantumSphereRef) {
            this.applyAnimationToQuantumSphere(eventType, data);
        }
        
        // 如果WebSocket连接可用，也通过WebSocket发送
        if (this.isConnected && this.wsConnection) {
            this.sendWebSocketMessage({
                type: 'chatEvent',
                eventType: eventType,
                data: data,
                timestamp: new Date().toISOString()
            });
        } else {
            console.log('🎨 使用本地动画效果 (WebSocket未连接)');
            // 使用本地动画效果作为fallback
            this.applyLocalAnimationEffect(eventType, data);
        }
        
        // 触发自定义事件，供其他模块监听
        document.dispatchEvent(new CustomEvent(`quantumChatEvent:${eventType}`, {
            detail: { eventType, data, timestamp: new Date().toISOString() }
        }));
    }
    
    // 本地动画效果fallback
    applyLocalAnimationEffect(eventType, data) {
        const backgroundContainer = document.getElementById('backgroundContainer') || 
                                  document.getElementById('minimalistBackground');
        
        if (!backgroundContainer) {
            console.warn('⚠️ 未找到背景容器，跳过动画效果');
            return;
        }
        
        // 添加动画类
        const animationClass = `chat-event-${eventType}`;
        backgroundContainer.classList.add(animationClass);
        
        // 应用颜色效果
        switch (eventType) {
            case 'userMessageSent':
                backgroundContainer.style.filter = 'hue-rotate(30deg) brightness(1.1)';
                break;
            case 'aiThinking':
                backgroundContainer.style.filter = 'hue-rotate(180deg) brightness(0.9)';
                break;
            case 'aiResponseReceived':
                backgroundContainer.style.filter = 'hue-rotate(120deg) brightness(1.2)';
                break;
            case 'chatError':
                backgroundContainer.style.filter = 'hue-rotate(0deg) brightness(1.3) saturate(1.5)';
                break;
        }
        
        // 清除效果
        setTimeout(() => {
            backgroundContainer.classList.remove(animationClass);
            backgroundContainer.style.filter = '';
        }, 2000);
    }
    
    setupPageEventListeners() {
        // 监听页面焦点变化
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.triggerQuantumBallMode('idle');
            } else {
                this.triggerQuantumBallMode('active');
            }
        });
        
        // 监听页面卸载
        window.addEventListener('beforeunload', () => {
            if (this.wsConnection) {
                this.wsConnection.close();
            }
        });
    }
    
    triggerQuantumBallMode(mode) {
        this.sendWebSocketMessage({
            type: 'quantumBallSync',
            data: {
                mode: mode,
                timestamp: new Date().toISOString()
            }
        });
    }
    
    sendWebSocketMessage(message) {
        if (this.wsConnection && this.wsConnection.readyState === WebSocket.OPEN) {
            this.wsConnection.send(JSON.stringify(message));
        } else {
            console.warn('⚠️ WebSocket未连接，消息发送失败:', message);
        }
    }
    
    // 事件系统
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }
    
    off(event, callback) {
        if (this.eventListeners.has(event)) {
            const callbacks = this.eventListeners.get(event);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        }
    }
    
    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`❌ 事件处理器错误 (${event}):`, error);
                }
            });
        }
    }
    
    // 公共API方法
    async callQuantumAPI(endpoint, method = 'GET', data = null) {
        try {
            const url = `/api/quantum${endpoint}`;
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                }
            };
            
            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }
            
            const response = await fetch(url, options);
            const result = await response.json();
            
            console.log(`📡 量子球API调用: ${endpoint}`, result);
            return result;
        } catch (error) {
            console.error(`❌ 量子球API调用失败: ${endpoint}`, error);
            throw error;
        }
    }
    
    // 聊天页面直接调用的动画方法
    async triggerUserMessageAnimation(message) {
        console.log('🗨️ 触发用户消息动画', message);
        return this.triggerChatEvent('userMessageSent', { message });
    }
    
    async triggerAIThinkingAnimation() {
        console.log('🤔 触发AI思考动画');
        return this.triggerChatEvent('aiThinking', {});
    }
    
    async triggerAIResponseAnimation(response) {
        console.log('🤖 触发AI响应动画', response);
        return this.triggerChatEvent('aiResponseReceived', { response });
    }
    
    async triggerErrorAnimation(error) {
        console.log('❌ 触发错误动画', error);
        return this.triggerChatEvent('chatError', { error });
    }
    
    // 应用动画到首页量子球系统
    applyAnimationToQuantumSphere(animationType, animationData) {
        if (!this.quantumSphereRef) {
            console.warn('⚠️ 首页量子球系统引用未设置');
            return;
        }
        
        const { quantumSphere, particleCloud, waveForm, connectionLines, lightBeams } = this.quantumSphereRef;
        
        switch (animationType) {
            case 'userMessageSent':
                this.applyUserMessageAnimation(quantumSphere, particleCloud);
                break;
            case 'aiThinking':
                this.applyAIThinkingAnimation(waveForm, connectionLines);
                break;
            case 'aiResponseReceived':
                this.applyAIResponseAnimation(quantumSphere, lightBeams);
                break;
            case 'chatError':
                this.applyErrorAnimation(quantumSphere, particleCloud);
                break;
        }
    }
    
    applyUserMessageAnimation(quantumSphere, particleCloud) {
        if (quantumSphere && quantumSphere.mesh) {
            // 用户消息：蓝色脉冲
            quantumSphere.mesh.material.color.setHex(0x0ea5e9);
            quantumSphere.mesh.scale.setScalar(1.2);
            
            setTimeout(() => {
                quantumSphere.mesh.material.color.setHex(0x6C13FF);
                quantumSphere.mesh.scale.setScalar(1.0);
            }, 1000);
        }
        
        if (particleCloud && particleCloud.particles) {
            particleCloud.particles.forEach(particle => {
                particle.material.color.setHex(0x0ea5e9);
                particle.userData.speed *= 1.5;
            });
            
            setTimeout(() => {
                particleCloud.particles.forEach(particle => {
                    particle.material.color.setHex(Math.random() > 0.5 ? 0x6C13FF : 0x00D4FF);
                    particle.userData.speed /= 1.5;
                });
            }, 2000);
        }
    }
    
    applyAIThinkingAnimation(waveForm, connectionLines) {
        if (waveForm && waveForm.waveMesh) {
            // AI思考：波形加速
            waveForm.time += 0.1;
            waveForm.waveMesh.material.color.setHex(0x6b46c1);
            waveForm.waveMesh.material.opacity = 0.8;
        }
        
        if (connectionLines && connectionLines.lines) {
            connectionLines.lines.forEach(line => {
                line.material.color.setHex(0x8b5cf6);
                line.userData.rotationSpeed *= 2;
            });
        }
    }
    
    applyAIResponseAnimation(quantumSphere, lightBeams) {
        if (quantumSphere && quantumSphere.mesh) {
            // AI响应：绿色光芒
            quantumSphere.mesh.material.color.setHex(0x10b981);
            
            if (quantumSphere.core) {
                quantumSphere.core.material.color.setHex(0x34d399);
            }
            
            setTimeout(() => {
                quantumSphere.mesh.material.color.setHex(0x6C13FF);
                if (quantumSphere.core) {
                    quantumSphere.core.material.color.setHex(0xFF2B75);
                }
            }, 2000);
        }
        
        if (lightBeams && lightBeams.beams) {
            lightBeams.beams.forEach(beam => {
                beam.material.color.setHex(0x10b981);
                beam.material.opacity = 1.0;
            });
            
            setTimeout(() => {
                lightBeams.beams.forEach(beam => {
                    beam.material.color.setHex(0x00D4FF);
                    beam.material.opacity = 0.6;
                });
            }, 1500);
        }
    }
    
    applyErrorAnimation(quantumSphere, particleCloud) {
        if (quantumSphere && quantumSphere.mesh) {
            // 错误：红色警告
            quantumSphere.mesh.material.color.setHex(0xef4444);
            
            // 震动效果
            let shakeCount = 0;
            const shakeInterval = setInterval(() => {
                quantumSphere.mesh.position.x += (Math.random() - 0.5) * 0.2;
                quantumSphere.mesh.position.y += (Math.random() - 0.5) * 0.2;
                shakeCount++;
                
                if (shakeCount > 10) {
                    clearInterval(shakeInterval);
                    quantumSphere.mesh.position.set(0, 0, -5);
                    quantumSphere.mesh.material.color.setHex(0x6C13FF);
                }
            }, 100);
        }
        
        if (particleCloud && particleCloud.particles) {
            particleCloud.particles.forEach(particle => {
                particle.material.color.setHex(0xef4444);
                particle.userData.speed *= 0.5;
            });
            
            setTimeout(() => {
                particleCloud.particles.forEach(particle => {
                    particle.material.color.setHex(Math.random() > 0.5 ? 0x6C13FF : 0x00D4FF);
                    particle.userData.speed *= 2;
                });
            }, 3000);
        }
    }
    
    // 调试和测试方法
    testIntegration() {
        console.log('🧪 测试量子球-聊天集成...');
        
        // 测试各种动画
        const testAnimations = [
            'userMessageSent',
            'aiThinking', 
            'aiResponseReceived',
            'chatError'
        ];
        
        testAnimations.forEach((animation, index) => {
            setTimeout(() => {
                this.triggerChatEvent(animation, {
                    test: true,
                    message: `测试动画: ${animation}`,
                    timestamp: new Date().toISOString()
                });
            }, index * 3000);
        });
    }
      getSystemStatus() {
        return {
            isConnected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts,
            hasQuantumBallSystem: !!this.quantumBallSystem,
            hasChatSystem: !!this.chatSystem,
            animationQueueLength: this.animationQueue.length,
            isAnimating: this.isAnimating,
            currentPage: window.location.pathname
        };
    }
    
    // 公共初始化方法 - 供外部调用
    async initialize() {
        console.log('🚀 QuantumChatIntegrator 公共初始化开始...');
        try {
            // 如果已经初始化过，直接返回
            if (this.isConnected) {
                console.log('✅ QuantumChatIntegrator 已经初始化，跳过重复初始化');
                return Promise.resolve();
            }
            
            // 调用内部初始化方法
            await this.init();
            
            console.log('✅ QuantumChatIntegrator 公共初始化完成');
            return Promise.resolve();
        } catch (error) {
            console.error('❌ QuantumChatIntegrator 初始化失败:', error);
            throw error;
        }
    }
}

// 全局初始化
let quantumChatIntegrator = null;

// 页面加载时自动初始化
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        try {
            quantumChatIntegrator = new QuantumChatIntegrator();
            window.quantumChatIntegrator = quantumChatIntegrator;
            
            console.log('🌊 量子球-聊天集成器已全局初始化');
            
            // 通知其他模块集成器已就绪
            document.dispatchEvent(new CustomEvent('quantumChatIntegratorReady', {
                detail: quantumChatIntegrator
            }));
              } catch (error) {
            console.error('❌ 量子球-聊天集成器初始化失败:', error);
        }
    }, 1000); // 延迟1秒确保其他系统初始化完成
});

// 添加全局初始化方法供外部调用
if (typeof window !== 'undefined') {
    window.initializeQuantumChatIntegrator = function() {
        if (window.quantumChatIntegrator) {
            console.log('🌊 量子球-聊天集成器手动初始化...');
            return window.quantumChatIntegrator;
        } else {
            console.warn('⚠️ 量子球-聊天集成器尚未就绪，请稍后重试');
            return null;
        }
    };    // 处理量子球点击事件
    window.handleQuantumBallClick = function(event) {
        console.log('🎯 量子球点击事件触发', event);
        
        // 防止事件冒泡
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // 获取集成器实例
        const integrator = window.quantumChatIntegrator;
        if (integrator) {
            // 触发量子动画效果
            if (typeof integrator.triggerQuantumAnimation === 'function') {
                integrator.triggerQuantumAnimation('click', {
                    position: event ? { x: event.clientX, y: event.clientY } : null,
                    intensity: 'high',
                    duration: 2000
                });
            }
            
            // 显示聊天界面
            if (typeof integrator.showChatInterface === 'function') {
                integrator.showChatInterface();
            }
            
            // 发送交互事件到服务器
            if (integrator.wsConnection && integrator.wsConnection.readyState === WebSocket.OPEN) {
                integrator.wsConnection.send(JSON.stringify({
                    type: 'quantum_ball_interaction',
                    timestamp: Date.now(),
                    data: {
                        action: 'click',
                        position: event ? { x: event.clientX, y: event.clientY } : null
                    }
                }));
            }
        } else {
            console.warn('⚠️ 量子聊天集成器尚未初始化');
        }
        
        return true;
    };

    // 初始化聊天系统
    window.initializeChatSystem = function() {
        console.log('💬 初始化聊天系统...');
        
        try {
            // 获取聊天容器
            const chatContainer = document.getElementById('chat-container');
            const floatingButton = document.getElementById('floating-chat-button');
            const quantumOrb = document.getElementById('quantum-orb-container');
            
            if (!chatContainer || !floatingButton) {
                console.warn('⚠️ 聊天界面元素未找到，创建基础结构...');
                this.createChatInterface();
            }
            
            // 设置聊天事件监听器
            this.setupChatInterfaceEvents();
              // 初始化聊天历史 (async operation handled with Promise)
            this.loadChatHistory().then(() => {
                console.log('📚 聊天历史加载完成');
            }).catch(error => {
                console.warn('⚠️ 聊天历史加载失败:', error);
            });
            
            // 连接到聊天服务 (async operation handled with Promise)
            this.connectToChatService().then(() => {
                console.log('🔗 聊天服务连接成功');
            }).catch(error => {
                console.warn('⚠️ 聊天服务连接失败:', error);
            });
              // 显示量子球（如果存在）
            if (quantumOrb) {
                quantumOrb.style.display = 'block';
                setTimeout(() => {
                    quantumOrb.style.opacity = '1';
                }, 100);
            }
            
            console.log('✅ 聊天系统初始化完成');
            return true;
        } catch (error) {
            console.error('❌ 聊天系统初始化失败:', error);
            return false;
        }
    };

    // 连接到量子球
    window.connectToQuantumOrb = function() {
        console.log('🔗 连接量子球系统...');
        
        const quantumOrb = document.getElementById('quantum-orb-container');
        
        if (quantumOrb) {
            // 添加点击事件监听器
            quantumOrb.addEventListener('click', (event) => {
                this.handleQuantumBallClick(event);
            });
            
            // 添加悬停效果
            quantumOrb.addEventListener('mouseenter', () => {
                this.triggerQuantumAnimation('hover', { intensity: 'medium' });
            });
            
            quantumOrb.addEventListener('mouseleave', () => {
                this.triggerQuantumAnimation('idle', { intensity: 'low' });
            });
            
            // 显示量子球
            quantumOrb.style.display = 'block';
            
            console.log('✅ 量子球连接成功');
            return true;
        } else {
            console.warn('⚠️ 量子球容器未找到');
            return false;
        }    };

    // 创建聊天界面（如果不存在）
    window.createChatInterface = function() {
        console.log('🏗️ 创建聊天界面...');
        
        // 检查是否已存在
        if (document.getElementById('chat-container')) {
            return;
        }
        
        // 创建聊天容器HTML
        const chatHTML = `
            <div id="floating-chat-button" class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-gradient-to-br from-tech-blue to-purple-500 rounded-full shadow-lg cursor-pointer hover:scale-110 transition-all duration-300 flex items-center justify-center">
                <i class="fas fa-comments text-white text-xl"></i>
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full animate-pulse"></div>
            </div>
            
            <div id="chat-container" class="fixed bottom-6 right-6 z-40 w-80 h-96 bg-gradient-to-br from-gray-900 to-gray-800 rounded-lg shadow-2xl border border-tech-blue opacity-0 transform translate-y-8 transition-all duration-300" style="display: none;">
                <div class="chat-header p-4 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-white font-semibold">智能助手</h3>
                    <button id="close-chat" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="chat-messages p-4 h-64 overflow-y-auto">
                    <div class="message bot-message mb-3">
                        <div class="message-content bg-gray-700 p-3 rounded-lg text-white text-sm">
                            您好！我是珑凌科技的智能助手，有什么可以帮助您的吗？
                        </div>
                    </div>
                </div>
                <div class="chat-input p-4 border-t border-gray-700">
                    <div class="flex gap-2">
                        <input type="text" id="chat-input" placeholder="输入消息..." class="flex-1 bg-gray-700 text-white p-2 rounded border border-gray-600 focus:border-tech-blue focus:outline-none">
                        <button id="send-chat" class="bg-tech-blue text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // 添加到页面
        document.body.insertAdjacentHTML('beforeend', chatHTML);
        console.log('✅ 聊天界面创建完成');    };

    // 设置聊天界面事件
    window.setupChatInterfaceEvents = function() {
        const floatingButton = document.getElementById('floating-chat-button');
        const chatContainer = document.getElementById('chat-container');
        const closeButton = document.getElementById('close-chat');
        const sendButton = document.getElementById('send-chat');
        const chatInput = document.getElementById('chat-input');
        
        // 浮动按钮点击事件
        if (floatingButton) {
            floatingButton.addEventListener('click', () => {
                this.showChatInterface();
            });
        }
        
        // 关闭按钮事件
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                this.hideChatInterface();
            });
        }
        
        // 发送按钮事件
        if (sendButton) {
            sendButton.addEventListener('click', () => {
                this.sendMessage();
            });
        }
        
        // 输入框回车事件
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }    };

    // 显示聊天界面
    window.showChatInterface = function() {
        const chatContainer = document.getElementById('chat-container');
        const floatingButton = document.getElementById('floating-chat-button');
        
        if (chatContainer) {
            chatContainer.style.display = 'block';
            setTimeout(() => {
                chatContainer.style.opacity = '1';
                chatContainer.style.transform = 'translateY(0)';
            }, 10);
        }
        
        if (floatingButton) {
            floatingButton.style.display = 'none';
        }
        
        // 触发量子动画
        this.triggerQuantumAnimation('chat_open', { intensity: 'high' });    };

    // 隐藏聊天界面
    window.hideChatInterface = function() {
        const chatContainer = document.getElementById('chat-container');
        const floatingButton = document.getElementById('floating-chat-button');
        
        if (chatContainer) {
            chatContainer.style.opacity = '0';
            chatContainer.style.transform = 'translateY(8px)';
            setTimeout(() => {
                chatContainer.style.display = 'none';
            }, 300);
        }
        
        if (floatingButton) {
            floatingButton.style.display = 'flex';
        }
        
        // 触发量子动画
        this.triggerQuantumAnimation('chat_close', { intensity: 'medium' });    };

    // 发送消息
    window.sendMessage = function() {
        const chatInput = document.getElementById('chat-input');
        const messagesContainer = document.querySelector('.chat-messages');
        
        if (!chatInput || !messagesContainer) return;
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // 添加用户消息到界面
        this.addMessageToChat('user', message);
        
        // 清空输入框
        chatInput.value = '';
        
        // 发送到服务器
        if (this.wsConnection && this.wsConnection.readyState === WebSocket.OPEN) {
            this.wsConnection.send(JSON.stringify({
                type: 'chat_message',
                message: message,
                timestamp: Date.now()
            }));
        }
        
        // 触发量子动画
        this.triggerQuantumAnimation('message_sent', { 
            intensity: 'medium',
            message: message 
        });    };

    // 添加消息到聊天界面
    window.addMessageToChat = function(sender, message) {
        const messagesContainer = document.querySelector('.chat-messages');
        if (!messagesContainer) return;
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${sender}-message mb-3`;
        
        const isUser = sender === 'user';
        messageElement.innerHTML = `
            <div class="message-content ${isUser ? 'bg-tech-blue ml-8' : 'bg-gray-700 mr-8'} p-3 rounded-lg text-white text-sm">
                ${message}
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;    };

    // 加载聊天历史
    window.loadChatHistory = async function() {
        try {
            // 这里可以从本地存储或服务器加载聊天历史
            const history = localStorage.getItem('chat_history');
            if (history) {
                const messages = JSON.parse(history);
                const messagesContainer = document.querySelector('.chat-messages');
                if (messagesContainer && messages.length > 0) {
                    messagesContainer.innerHTML = ''; // 清空默认消息
                    messages.forEach(msg => {
                        this.addMessageToChat(msg.sender, msg.message);
                    });
                }
            }
        } catch (error) {
            console.error('加载聊天历史失败:', error);
        }    };

    // 连接到聊天服务
    window.connectToChatService = async function() {
        try {
            // 这里可以建立与聊天服务的连接
            console.log('🔗 连接到聊天服务...');
            // 实际实现会根据具体的聊天服务API来定制
        } catch (error) {
            console.error('连接聊天服务失败:', error);
        }    };

    // 触发量子动画
    window.triggerQuantumAnimation = function(type, data = {}) {
        console.log('🎬 触发量子动画:', type, data);
        
        // 应用到量子球
        const quantumOrb = document.getElementById('quantum-orb-container');
        if (quantumOrb) {
            const core = quantumOrb.querySelector('.orb-core');
            const ring = quantumOrb.querySelector('.orb-ring');
            const glow = quantumOrb.querySelector('.orb-glow');
            
            switch (type) {
                case 'click':
                    if (core) core.style.animation = 'pulse 0.5s ease-in-out';
                    if (ring) ring.style.animation = 'spin 1s linear infinite';
                    break;
                case 'hover':
                    if (glow) glow.style.opacity = '0.4';
                    break;
                case 'idle':
                    if (glow) glow.style.opacity = '0.2';
                    break;
                case 'message_sent':
                    if (core) {
                        core.style.background = 'linear-gradient(45deg, #00ff88, #0088ff)';
                        setTimeout(() => {
                            core.style.background = '';
                        }, 1000);
                    }
                    break;
            }
        }
        
        // 应用到背景粒子系统
        if (window.quantumParticleSystem) {
            try {
                window.quantumParticleSystem.triggerAnimation(type, data);
            } catch (error) {
                console.warn('量子粒子系统动画触发失败:', error);
            }        }    };

    // 触发聊天事件（兼容性方法）
    window.triggerChatEvent = function(eventType, data = {}) {
        console.log('📡 触发聊天事件:', eventType, data);
        
        // 这是一个兼容性方法，用于向后兼容
        switch (eventType) {
            case 'click':
            case 'quantum_ball_click':
                this.handleQuantumBallClick(data.event);
                break;
                
            case 'message_sent':
                this.triggerQuantumAnimation('message_sent', data);
                break;
                
            case 'chat_open':
                this.showChatInterface();
                break;
                
            case 'chat_close':
                this.hideChatInterface();
                break;
                
            case 'animation':
                this.triggerQuantumAnimation(data.type || 'pulse', data);
                break;
                
            default:
                console.log('🔄 通用聊天事件:', eventType, data);
                this.triggerQuantumAnimation(eventType, data);
        }
        
        // 发送事件到WebSocket（如果连接）
        if (this.wsConnection && this.wsConnection.readyState === WebSocket.OPEN) {
            this.wsConnection.send(JSON.stringify({
                type: 'chat_event',
                eventType: eventType,
                data: data,
                timestamp: Date.now()
            }));
        }
        
        return true;
    }
}

// 导出给其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuantumChatIntegrator;
}

// 确保类在全局作用域中可用
if (typeof window !== 'undefined') {
    window.QuantumChatIntegrator = QuantumChatIntegrator;
}

console.log('📦 量子球-聊天集成模块已加载');

/* ===== END quantum-chat-integrator.js ===== */

