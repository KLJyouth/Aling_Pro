// 前端修复脚本 - 修复登录窗口和量子3D模型显示问题
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 前端修复脚本已加载');    // 修复登录按钮事件
    const loginBtn = document.getElementById('login-btn');
    const loginModal = document.getElementById('login-modal');
    
    if (loginBtn && loginModal) {
        loginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🔓 登录按钮被点击');
            showLoginModal();
        });
    }

    // 登录模态窗口显示函数
    function showLoginModal() {
        if (loginModal) {
            loginModal.classList.remove('hidden');
            loginModal.classList.add('flex');
            
            const modalCard = loginModal.querySelector('.glass-card');
            if (modalCard) {
                modalCard.classList.add('modal-enter');
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }
            
            // 锁定body滚动
            document.body.style.overflow = 'hidden';
            console.log('✅ 登录模态窗口已显示');
        }
    }

    // 登录模态窗口隐藏函数
    function hideLoginModal() {
        if (loginModal) {
            const modalCard = loginModal.querySelector('.glass-card');
            if (modalCard) {
                modalCard.classList.add('modal-exit');
                modalCard.classList.remove('scale-100', 'opacity-100');
                modalCard.classList.add('scale-95', 'opacity-0');
            }
            
            setTimeout(() => {
                loginModal.classList.add('hidden');
                loginModal.classList.remove('flex');
                document.body.style.overflow = '';
            }, 300);
            
            console.log('✅ 登录模态窗口已隐藏');
        }
    }    // 绑定关闭事件
    const closeLoginModal = loginModal ? loginModal.querySelector('.login-modal-close') : null;
    if (closeLoginModal) {
        closeLoginModal.addEventListener('click', hideLoginModal);
    }

    // 点击背景关闭
    if (loginModal) {
        loginModal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideLoginModal();
            }
        });
    }

    // ESC键关闭
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !loginModal.classList.contains('hidden')) {
            hideLoginModal();
        }
    });    // 修复量子3D模型 - 现在使用增强版本

    // 修复产品矩阵和技术实力内容显示
    function enhanceContentVisibility() {
        const sections = ['#products', '#technology'];
        
        sections.forEach(selector => {
            const section = document.querySelector(selector);
            if (section) {
                section.style.opacity = '1';
                section.style.visibility = 'visible';
                
                // 为卡片添加渐入动画
                const cards = section.querySelectorAll('.product-card-enhanced, .glass-card');
                cards.forEach((card, index) => {
                    card.style.animationDelay = `${index * 0.2}s`;
                    card.classList.add('animate-fade-in-up');
                });
            }
        });
    }

    // 修复按钮点击事件
    function setupButtonEvents() {
        // "了解更多" 按钮事件
        const learnMoreBtns = document.querySelectorAll('.button-enhanced-primary');
        learnMoreBtns.forEach(btn => {
            if (btn.textContent.includes('了解更多')) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('🔍 了解更多按钮被点击');
                    
                    // 滚动到产品矩阵部分
                    const productsSection = document.getElementById('products');
                    if (productsSection) {
                        productsSection.scrollIntoView({ behavior: 'smooth' });
                    }
                    
                    // 添加点击动画
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            }
        });

        // "预约演示" 按钮事件
        const demoBtns = document.querySelectorAll('.button-enhanced-secondary');
        demoBtns.forEach(btn => {
            if (btn.textContent.includes('预约演示')) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('📅 预约演示按钮被点击');
                    
                    // 显示预约演示模态窗口或跳转到联系页面
                    showDemoModal();
                    
                    // 添加点击动画
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            }
        });
    }

    // 预约演示模态窗口
    function showDemoModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="glass-card max-w-md w-full m-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">预约演示</h3>
                    <button class="text-gray-400 hover:text-white transition-colors" onclick="this.closest('.fixed').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">姓名</label>
                        <input type="text" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请输入您的姓名">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">邮箱</label>
                        <input type="email" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请输入您的邮箱">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">公司名称</label>
                        <input type="text" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="请输入公司名称">
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button class="button-enhanced-primary flex-1" onclick="alert('预约演示功能开发中...'); this.closest('.fixed').remove();">
                            立即预约
                        </button>
                        <button class="button-enhanced-secondary flex-1" onclick="this.closest('.fixed').remove();">
                            取消
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // 语言切换功能
    function setupLanguageSwitch() {
        const langSelector = document.querySelector('.lang-selector-enhanced');
        if (langSelector) {
            langSelector.addEventListener('click', function() {
                console.log('🌐 语言选择器被点击');
                showLanguageOptions();
            });
        }

        // 移动端语言选择
        const mobileSelect = document.querySelector('#mobileMenu select');
        if (mobileSelect) {
            mobileSelect.addEventListener('change', function() {
                switchLanguage(this.value);
            });
        }
    }

    function showLanguageOptions() {
        const dropdown = document.createElement('div');
        dropdown.className = 'absolute top-full right-0 mt-2 bg-gray-800 border border-gray-600 rounded-lg shadow-lg z-50';
        dropdown.innerHTML = `
            <div class="py-2">
                <button class="w-full px-4 py-2 text-left hover:bg-gray-700 transition-colors" onclick="switchLanguage('zh'); this.closest('div').remove();">
                    🇨🇳 中文
                </button>
                <button class="w-full px-4 py-2 text-left hover:bg-gray-700 transition-colors" onclick="switchLanguage('en'); this.closest('div').remove();">
                    🇺🇸 English
                </button>
            </div>
        `;
        
        const langSelector = document.querySelector('.lang-selector-enhanced');
        langSelector.style.position = 'relative';
        langSelector.appendChild(dropdown);
        
        // 点击外部关闭
        setTimeout(() => {
            document.addEventListener('click', function closeDropdown(e) {
                if (!langSelector.contains(e.target)) {
                    if (dropdown.parentNode) {
                        dropdown.remove();
                    }
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }, 0);
    }

    window.switchLanguage = function(lang) {
        console.log('🔄 切换语言到:', lang);
        
        const currentLang = document.querySelector('.current-lang');
        if (currentLang) {
            currentLang.textContent = lang === 'zh' ? '中文' : 'English';
        }
        
        // 简单的语言切换实现（可以扩展为更完整的国际化）
        if (lang === 'en') {
            // 这里可以添加英文翻译逻辑
            console.log('切换到英文模式');
        } else {
            console.log('切换到中文模式');
        }
    };

    // 创建量子彩虹丝带
    function createQuantumRainbowRibbon() {
        const ribbon = document.createElement('div');
        ribbon.className = 'quantum-rainbow-ribbon';
        document.body.appendChild(ribbon);
        console.log('🌈 量子彩虹丝带已添加');
    }

    // 增强3D量子球 - 添加龙渊深邃元素
    function enhanceQuantumModel() {
        const quantumContainer = document.getElementById('quantum-model');
        if (!quantumContainer || !window.THREE) {
            console.warn('⚠️ Three.js未加载或量子模型容器未找到');
            return;
        }

        try {
            // 清空容器
            quantumContainer.innerHTML = '';
            
            // 创建场景
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(
                75, 
                quantumContainer.offsetWidth / quantumContainer.offsetHeight, 
                0.1, 
                1000
            );
            
            const renderer = new THREE.WebGLRenderer({
                alpha: true, 
                antialias: true 
            });
            
            renderer.setSize(quantumContainer.offsetWidth, quantumContainer.offsetHeight);
            renderer.setClearColor(0x000000, 0);
            quantumContainer.appendChild(renderer.domElement);

            // 创建主量子球体 - 龙渊核心
            const sphereGeometry = new THREE.SphereGeometry(1, 64, 64);
            const sphereMaterial = new THREE.ShaderMaterial({
                uniforms: {
                    time: { value: 0 },
                    resolution: { value: new THREE.Vector2(quantumContainer.offsetWidth, quantumContainer.offsetHeight) }
                },
                vertexShader: `
                    varying vec2 vUv;
                    varying vec3 vPosition;
                    void main() {
                        vUv = uv;
                        vPosition = position;
                        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
                    }
                `,
                fragmentShader: `
                    uniform float time;
                    varying vec2 vUv;
                    varying vec3 vPosition;
                    
                    void main() {
                        vec2 uv = vUv;
                        
                        // 龙渊深邃效果 - 深海蓝紫渐变
                        vec3 deepAbyss = vec3(0.02, 0.08, 0.2);
                        vec3 dragonCore = vec3(0.4, 0.1, 0.8);
                        vec3 quantumEnergy = vec3(0.0, 0.8, 1.0);
                        
                        // 动态波纹效果
                        float wave1 = sin(length(vPosition) * 8.0 - time * 2.0) * 0.5 + 0.5;
                        float wave2 = sin(length(vPosition) * 12.0 + time * 1.5) * 0.3 + 0.7;
                        
                        // 青春活力脉冲
                        float pulse = sin(time * 3.0) * 0.3 + 0.7;
                        
                        // 颜色混合
                        vec3 color = mix(deepAbyss, dragonCore, wave1);
                        color = mix(color, quantumEnergy, wave2 * pulse);
                        
                        // 代码矩阵效果
                        float codePattern = step(0.8, sin(uv.x * 50.0 + time) * sin(uv.y * 50.0 + time * 0.7));
                        color += vec3(0.0, 1.0, 0.5) * codePattern * 0.3;
                        
                        gl_FragColor = vec4(color, 0.8);
                    }
                `,
                transparent: true,
                wireframe: false
            });
            const sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
            scene.add(sphere);

            // 创建龙形光环
            const ringGeometry = new THREE.RingGeometry(1.2, 1.5, 32);
            const ringMaterial = new THREE.MeshBasicMaterial({
                color: 0x6c13ff,
                transparent: true,
                opacity: 0.3,
                side: THREE.DoubleSide
            });
            const ring = new THREE.Mesh(ringGeometry, ringMaterial);
            ring.rotation.x = Math.PI / 2;
            scene.add(ring);

            // 创建代码粒子系统
            const particleCount = 200;
            const particles = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 3);
            const colors = new Float32Array(particleCount * 3);
            
            for (let i = 0; i < particleCount; i++) {
                const i3 = i * 3;
                positions[i3] = (Math.random() - 0.5) * 8;
                positions[i3 + 1] = (Math.random() - 0.5) * 8;
                positions[i3 + 2] = (Math.random() - 0.5) * 8;
                
                // 青春活力配色
                const colorChoice = Math.random();
                if (colorChoice < 0.33) {
                    colors[i3] = 0.0; colors[i3 + 1] = 1.0; colors[i3 + 2] = 1.0; // 青色
                } else if (colorChoice < 0.66) {
                    colors[i3] = 0.4; colors[i3 + 1] = 0.1; colors[i3 + 2] = 1.0; // 紫色
                } else {
                    colors[i3] = 0.0; colors[i3 + 1] = 1.0; colors[i3 + 2] = 0.5; // 绿色
                }
            }
            
            particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            particles.setAttribute('color', new THREE.BufferAttribute(colors, 3));
            
            const particleMaterial = new THREE.PointsMaterial({
                size: 0.05,
                vertexColors: true,
                transparent: true,
                opacity: 0.8
            });
            
            const particleSystem = new THREE.Points(particles, particleMaterial);
            scene.add(particleSystem);

            camera.position.z = 4;

            // 动画循环
            function animate() {
                requestAnimationFrame(animate);
                
                const time = Date.now() * 0.001;
                
                // 更新着色器时间
                sphereMaterial.uniforms.time.value = time;
                
                // 主球体旋转
                sphere.rotation.x += 0.005;
                sphere.rotation.y += 0.01;
                
                // 龙形光环旋转
                ring.rotation.z += 0.02;
                
                // 粒子动画 - 代码雨效果
                const positions = particleSystem.geometry.attributes.position.array;
                for (let i = 0; i < particleCount; i++) {
                    const i3 = i * 3;
                    positions[i3 + 1] -= 0.02; // 向下飘落
                    
                    if (positions[i3 + 1] < -4) {
                        positions[i3 + 1] = 4;
                        positions[i3] = (Math.random() - 0.5) * 8;
                        positions[i3 + 2] = (Math.random() - 0.5) * 8;
                    }
                }
                particleSystem.geometry.attributes.position.needsUpdate = true;

                renderer.render(scene, camera);
            }

            animate();
            console.log('✨ 龙渊深邃量子球初始化成功');

            // 窗口大小调整
            window.addEventListener('resize', function() {
                if (quantumContainer.offsetWidth > 0) {
                    camera.aspect = quantumContainer.offsetWidth / quantumContainer.offsetHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(quantumContainer.offsetWidth, quantumContainer.offsetHeight);
                }
            });

        } catch (error) {
            console.error('❌ 增强量子模型初始化失败:', error);
            
            // 后备方案：显示2D动画
            quantumContainer.innerHTML = `
                <div class="flex items-center justify-center h-full relative">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-r from-purple-600 via-blue-500 to-cyan-400 animate-spin relative">
                        <div class="absolute inset-2 rounded-full bg-gray-900 flex items-center justify-center">
                            <div class="text-2xl">⚛️</div>
                        </div>
                    </div>
                </div>
            `;
        }
    }    // 初始化所有功能
    enhanceContentVisibility();
    setupButtonEvents();
    setupLanguageSwitch();
    createQuantumRainbowRibbon();
    
    // 替换原有的量子模型初始化
    if (window.THREE) {
        enhanceQuantumModel();
    } else {
        // 如果Three.js未加载，显示后备方案
        const quantumContainer = document.getElementById('quantum-model');
        if (quantumContainer) {
            quantumContainer.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="w-32 h-32 border-4 border-blue-500 rounded-full animate-spin border-t-transparent"></div>
                </div>
            `;
        }
    }

    console.log('🎉 前端修复完成！');
});
