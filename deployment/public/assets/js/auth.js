// 登录状态管理
const auth = {
    isAuthenticated: false,
    user: null,
    token: null,

    init() {
        this.checkAuthState();
        this.setupAuthListeners();
    },    checkAuthState() {
        const token = localStorage.getItem('token') || localStorage.getItem('auth_token');
        const user = localStorage.getItem('user_data');
        
        if (token && user) {
            try {
                this.token = token;
                this.user = JSON.parse(user);
                this.isAuthenticated = true;
                // 统一token存储
                localStorage.setItem('token', token);
                localStorage.setItem('auth_token', token);
                this.onAuthStateChanged(true);
            } catch (error) {
                console.error('Auth state parse error:', error);
                this.logout();
            }
        } else if (token) {
            // 只有token没有用户数据，尝试验证token
            this.token = token;
            this.isAuthenticated = true;
            // 统一token存储
            localStorage.setItem('token', token);
            localStorage.setItem('auth_token', token);
            this.onAuthStateChanged(true);
        } else {
            this.isAuthenticated = false;
            this.user = null;
            this.token = null;
            this.onAuthStateChanged(false);
        }
    },
      showLoginModal() {
        const loginModal = document.getElementById('login-modal');
        if (loginModal) {
            loginModal.classList.remove('hidden');
            loginModal.classList.add('flex');
        } else {
            console.warn('登录模态框不存在');
        }
    },
    
    hideLoginModal() {
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            loginModal.classList.add('hidden');
            loginModal.classList.remove('flex');
        }
    },
    
    setupAuthListeners() {
        const loginForm = document.getElementById('loginForm');
        const loginModal = document.getElementById('loginModal');
        const closeLoginModal = document.getElementById('closeLoginModal');
        const loginBtn = document.getElementById('loginBtn');
        const logoutBtn = document.getElementById('logoutBtn');

        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(loginForm);
                await this.login(formData.get('username'), formData.get('password'));
            });
        }

        if (closeLoginModal) {
            closeLoginModal.addEventListener('click', () => {
                this.hideLoginModal();
            });
        }

        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                this.showLoginModal();
            });
        }

        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.logout();
            });
        }
    },
      async login(username, password) {
        try {
            console.log('尝试登录:', username);
            
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });            if (!response.ok) {
                console.error('登录响应错误:', response.status);
                let errorMessage = `服务器响应错误: ${response.status} ${response.statusText}`;
                
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.message || errorData.error || errorMessage;
                } catch (e) {
                    // 如果不是JSON响应，尝试获取文本
                    try {
                        const errorText = await response.text();
                        if (errorText) {
                            errorMessage = errorText;
                        }
                    } catch (textError) {
                        console.error('无法解析错误响应:', textError);
                    }
                }
                
                if (typeof notifications !== 'undefined' && notifications.error) {
                    notifications.error(errorMessage);
                } else if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                }
                
                return { 
                    success: false, 
                    error: errorMessage
                };
            }            const data = await response.json();
            console.log('登录响应:', data);
            
            if (data.success && data.token) {
                this.token = data.token;
                this.user = data.user;
                this.isAuthenticated = true;
                
                // 统一存储token和用户信息
                localStorage.setItem('token', data.token);
                localStorage.setItem('auth_token', data.token);
                localStorage.setItem('user_data', JSON.stringify(data.user));
                
                this.hideLoginModal();
                this.onAuthStateChanged(true);
                
                if (typeof notifications !== 'undefined' && notifications.success) {
                    notifications.success('登录成功');
                } else if (typeof showNotification === 'function') {
                    showNotification('登录成功', 'success');
                } else {
                    console.log('登录成功');
                }
                
                return { success: true, user: data.user };
            } else {
                const errorMessage = data.error || '登录失败';
                
                if (typeof notifications !== 'undefined' && notifications.error) {
                    notifications.error(errorMessage);
                } else if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                } else {
                    console.error('登录失败:', errorMessage);
                }
                
                return { success: false, error: errorMessage };
            }
        } catch (error) {
            console.error('登录过程出错:', error);
            
            if (typeof notifications !== 'undefined' && notifications.error) {
                notifications.error(`登录出错: ${error.message}`);
            } else if (typeof showNotification === 'function') {
                showNotification('登录出错', 'error', error.message);
            }
            
            return { success: false, error: error.message };
        }
    },
    
    onAuthStateChanged(isLoggedIn) {
        console.log('Auth state changed:', isLoggedIn ? 'logged in' : 'logged out');
        
        // 更新需要授权的元素显示状态
        const authElements = document.querySelectorAll('[data-auth-required]');
        authElements.forEach(el => {
            el.style.display = isLoggedIn ? '' : 'none';
        });
        
        // 更新头部UI
        this.updateHeaderUI(isLoggedIn);
        
        // 触发自定义事件
        const event = new CustomEvent('authStateChanged', { 
            detail: { isAuthenticated: isLoggedIn }
        });
        document.dispatchEvent(event);
    },
    
    updateHeaderUI(isLoggedIn) {
        // 查找页面上的用户相关元素
        const userMenu = document.getElementById('userMenu');
        const loginBtn = document.getElementById('loginBtn');
        const logoutBtn = document.getElementById('logoutBtn');
        const userDisplay = document.getElementById('userDisplay');
        const userDisplayName = document.getElementById('userDisplayName');
        
        if (userMenu && isLoggedIn && this.user) {
            userMenu.classList.remove('hidden');
            if (userDisplay) {
                userDisplay.textContent = this.user.username || this.user.email || '用户';
            }
            if (userDisplayName) {
                userDisplayName.textContent = this.user.username || this.user.name || this.user.email || '用户';
            }
        } else if (userMenu) {
            userMenu.classList.add('hidden');
        }
        
        // 更新按钮状态
        if (loginBtn) {
            loginBtn.style.display = isLoggedIn ? 'none' : 'block';
        }
        
        if (logoutBtn) {
            logoutBtn.style.display = isLoggedIn ? 'block' : 'none';
        }
    },    logout() {
        this.token = null;
        this.user = null;
        this.isAuthenticated = false;
        
        // 清理所有可能的token存储
        localStorage.removeItem('auth_token');
        localStorage.removeItem('token');
        localStorage.removeItem('user_data');
        localStorage.removeItem('guestMode');
        
        this.onAuthStateChanged(false);
          if (typeof notifications !== 'undefined' && notifications.info) {
            notifications.info('已退出登录');
        } else if (typeof showNotification === 'function') {
            showNotification('已退出登录', 'info');
        } else {
            console.log('已退出登录');
        }
    },

    // 获取存储的token
    getToken() {
        return this.token || localStorage.getItem('token') || localStorage.getItem('auth_token');
    },

    // 设置token
    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('token', token);
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('token');
            localStorage.removeItem('auth_token');
        }
    }
};

// 导出 auth 对象
export { auth };
