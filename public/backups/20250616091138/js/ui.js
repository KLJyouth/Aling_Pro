// UI交互控制模块
import { auth } from './auth.js';

export class HomeUI {
    constructor() {
        this.dom = {
            mobileMenuBtn: document.getElementById('mobileMenuBtn'),
            mobileMenu: document.getElementById('mobileMenu'),
            loginModal: document.getElementById('loginModal'),
            loginBtn: document.getElementById('loginBtn'),
            mobileLoginBtn: document.getElementById('mobileLoginBtn'),
            closeLoginModal: document.getElementById('closeLoginModal'),
            loginForm: document.getElementById('loginForm'),
            scrollReveal: document.querySelectorAll('.scroll-reveal'),
            quantumModel: document.getElementById('quantum-model')
        };
        
        this.initOnDOMContentLoaded();
    }
    
    initOnDOMContentLoaded() {
        document.addEventListener('DOMContentLoaded', () => {
            this.init();
        });
    }
    
    init() {
        // 导航菜单控制
        this.initMenuControls();
        // 登录模态框控制
        this.initLoginModal();
        // 表单控制
        this.initFormHandlers();
        // 滚动动画
        this.initScrollAnimations();
        // 3D场景
        this.initQuantumScene();
        // 初始化键盘快捷键
        this.initKeyboardShortcuts();
    }
    
    initMenuControls() {
        const { mobileMenuBtn, mobileMenu } = this.dom;
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    }

    initLoginModal() {
        const { loginModal, loginBtn, mobileLoginBtn, closeLoginModal } = this.dom;

        const showLoginModal = () => {
            if (loginModal) {
                loginModal.classList.remove('hidden');
                loginModal.classList.add('flex');
            }
        };

        const hideLoginModal = () => {
            if (loginModal) {
                loginModal.classList.remove('flex');
                loginModal.classList.add('hidden');
            }
        };

        // 绑定事件
        loginBtn?.addEventListener('click', showLoginModal);
        mobileLoginBtn?.addEventListener('click', showLoginModal);
        closeLoginModal?.addEventListener('click', hideLoginModal);

        // 点击模态框外部关闭
        loginModal?.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                hideLoginModal();
            }
        });

        // 保存为实例方法以供其他方法使用
        this.showLoginModal = showLoginModal;
        this.hideLoginModal = hideLoginModal;
    }    initFormHandlers() {
        const { loginForm } = this.dom;
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(loginForm);
                const username = formData.get('username');
                const password = formData.get('password');
                
                if (!username || !password) {
                    this.showError('登录失败', '请输入用户名和密码');
                    return;
                }
                
                try {
                    const result = await auth.login(username, password);
                    if (result.success) {
                        this.hideLoginModal();
                        // 刷新UI状态
                        this.updateAuthStatus();
                        // 显示成功消息
                        if (typeof showNotification === 'function') {
                            showNotification('登录成功', 'success');
                        }
                    } else {
                        this.showError('登录失败', result.error || '用户名或密码错误');
                        // 显示错误在表单中
                        const errorElement = document.querySelector('#loginForm .error-message');
                        if (errorElement) {
                            errorElement.textContent = result.error || '用户名或密码错误';
                            errorElement.style.display = 'block';
                        }
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    this.showError('登录失败', error.message || '网络错误，请稍后重试');
                }
            });
        }
    }

    initScrollAnimations() {
        const { scrollReveal } = this.dom;
        if (!scrollReveal || scrollReveal.length === 0) return;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            },
            {
                threshold: 0.3
            }
        );

        scrollReveal.forEach(element => observer.observe(element));
    }

    initQuantumScene() {
        const { quantumModel: container } = this.dom;
        if (!container) return;

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });

        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        // 创建量子球体
        const geometry = new THREE.SphereGeometry(2, 32, 32);
        const material = new THREE.MeshPhongMaterial({
            color: 0x6C13FF,
            emissive: 0x6C13FF,
            emissiveIntensity: 0.2,
            transparent: true,
            opacity: 0.8
        });
        const sphere = new THREE.Mesh(geometry, material);
        scene.add(sphere);

        // 添加环绕的粒子
        const particlesGeometry = new THREE.BufferGeometry();
        const particlesCount = 1000;
        const positions = new Float32Array(particlesCount * 3);

        for (let i = 0; i < particlesCount * 3; i += 3) {
            const theta = Math.random() * Math.PI * 2;
            const phi = Math.acos((Math.random() * 2) - 1);
            const radius = 3 + Math.random();

            positions[i] = radius * Math.sin(phi) * Math.cos(theta);
            positions[i + 1] = radius * Math.sin(phi) * Math.sin(theta);
            positions[i + 2] = radius * Math.cos(phi);
        }

        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        const particlesMaterial = new THREE.PointsMaterial({
            color: 0x00D4FF,
            size: 0.02,
            transparent: true,
            opacity: 0.8
        });
        const particles = new THREE.Points(particlesGeometry, particlesMaterial);
        scene.add(particles);

        // 添加环境光和点光源
        const ambientLight = new THREE.AmbientLight(0x404040);
        const pointLight = new THREE.PointLight(0x00D4FF, 1, 100);
        pointLight.position.set(10, 10, 10);
        scene.add(ambientLight);
        scene.add(pointLight);

        camera.position.z = 8;

        // 动画循环
        function animate() {
            requestAnimationFrame(animate);

            sphere.rotation.x += 0.001;
            sphere.rotation.y += 0.002;

            particles.rotation.x -= 0.0005;
            particles.rotation.y -= 0.001;

            renderer.render(scene, camera);
        }

        animate();

        // 响应窗口大小变化
        window.addEventListener('resize', () => {
            const width = container.clientWidth;
            const height = container.clientHeight;

            camera.aspect = width / height;
            camera.updateProjectionMatrix();

            renderer.setSize(width, height);
        });
    }

    // 初始化键盘快捷键
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + L: 打开登录框
            if ((e.ctrlKey || e.metaKey) && e.key === 'l' && !e.shiftKey) {
                e.preventDefault();
                if (auth.isAuthenticated) {
                    auth.logout();
                } else {
                    this.showLoginModal();
                }
            }
            
            // Escape: 关闭模态框
            if (e.key === 'Escape') {
                this.hideLoginModal();
            }
            
            // Ctrl/Cmd + /: 显示快捷键帮助
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                this.showShortcutHelp();
            }
            
            // F: 切换全屏模式
            if (e.key === 'F' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    this.toggleFullscreen();
                }
            }
        });
    }

    // 显示快捷键帮助
    showShortcutHelp() {
        const helpModal = document.createElement('div');
        helpModal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]';
        helpModal.innerHTML = `
            <div class="glass-card p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-heading">键盘快捷键</h3>
                    <button class="text-gray-400 hover:text-white" onclick="this.closest('.fixed').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>登录/登出</span>
                        <kbd class="bg-gray-800 px-2 py-1 rounded text-xs">Ctrl + L</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>关闭弹窗</span>
                        <kbd class="bg-gray-800 px-2 py-1 rounded text-xs">Esc</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>显示帮助</span>
                        <kbd class="bg-gray-800 px-2 py-1 rounded text-xs">Ctrl + /</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span>全屏模式</span>
                        <kbd class="bg-gray-800 px-2 py-1 rounded text-xs">F</kbd>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(helpModal);
        
        // 点击外部关闭
        helpModal.addEventListener('click', (e) => {
            if (e.target === helpModal) {
                helpModal.remove();
            }
        });
    }

    // 切换全屏模式
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log('无法进入全屏模式:', err);
                this.showNotification('浏览器不支持全屏模式', 'warning');
            });
        } else {
            document.exitFullscreen();
        }
    }

    showError(title, message) {
        if (typeof showNotification === 'function') {
            showNotification(title, message, 'error');
        } else {
            console.error(title + ':', message);
            alert(title + ': ' + message);
        }
    }    updateAuthStatus() {
        // 更新所有需要基于认证状态改变的UI元素
        const authToken = localStorage.getItem('token') || localStorage.getItem('auth_token');
        const isLoggedIn = !!authToken;
        
        const { loginBtn, mobileLoginBtn } = this.dom;
        
        if (loginBtn) {
            loginBtn.textContent = isLoggedIn ? '控制台' : '登录';
            loginBtn.href = isLoggedIn ? '/dashboard.html' : '#';
        }
        
        if (mobileLoginBtn) {
            mobileLoginBtn.textContent = isLoggedIn ? '控制台' : '登录';
            mobileLoginBtn.href = isLoggedIn ? '/dashboard.html' : '#';
        }
    }
}

// 创建并导出UI实例
export const ui = new HomeUI();
