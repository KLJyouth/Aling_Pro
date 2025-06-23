// AlingAI Pro v4.0 - 认证系统
// 集成新的API端点
const auth = {
    // API配置
    apiConfig: {
        baseUrl: '/api',
        endpoints: {
            login: '/api/login.php',
            register: '/api/register.php',
            user: '/api/user.php',
            logout: '/api/login.php'
        }
    },
    
    // 认证状态
    isAuthenticated: false,
    user: null,
    token: null,
    refreshTimer: null,
    
    init() {
        this.checkAuthState();
        this.setupAuthListeners();
        this.setupTokenRefresh();
    },
    
    checkAuthState() {
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
    
    /**
     * 登录方法 - 使用新的API端点
     */
    async login(username, password, rememberMe = false) {
        try {
            const response = await fetch(this.apiConfig.endpoints.login, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                    remember_me: rememberMe
                })
            });

            const result = await response.json();

            if (result.success) {
                // 保存认证信息
                this.token = result.token;
                this.user = result.user;
                this.isAuthenticated = true;

                // 存储到localStorage
                localStorage.setItem('auth_token', this.token);
                localStorage.setItem('token', this.token);
                localStorage.setItem('user_data', JSON.stringify(this.user));
                
                if (result.session_id) {
                    localStorage.setItem('session_id', result.session_id);
                }

                // 如果记住我，设置更长的过期时间
                if (rememberMe) {
                    localStorage.setItem('remember_me', 'true');
                }

                this.onAuthStateChanged(true);
                this.setupTokenRefresh();
                this.hideLoginModal();

                // 显示成功消息
                this.showMessage('登录成功', 'success');

                return { success: true, user: this.user };
            } else {
                throw new Error(result.message || '登录失败');
            }
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: error.message };
        }
    },
    
    /**
     * 注册方法 - 使用新的API端点
     */
    async register(userData) {
        try {
            const response = await fetch(this.apiConfig.endpoints.register, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();

            if (result.success) {
                // 显示成功消息
                this.showMessage(result.message || '注册成功', 'success');
                return { success: true, user: result.user, message: result.message };
            } else {
                throw new Error(result.message || '注册失败');
            }
        } catch (error) {
            console.error('Register error:', error);
            this.showMessage(error.message, 'error');
            return { success: false, message: error.message };
        }
    },
    
    /**
     * 登出方法 - 使用新的API端点
     */
    async logout() {
        try {
            // 尝试调用服务器登出端点
            if (this.token) {
                await fetch(this.apiConfig.endpoints.logout, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                }).catch(err => console.warn('Logout API call failed:', err));
            }
        } catch (error) {
            console.warn('Logout error:', error);
        } finally {
            // 清理本地状态
            this.isAuthenticated = false;
            this.user = null;
            this.token = null;

            // 清理存储
            localStorage.removeItem('auth_token');
            localStorage.removeItem('token');
            localStorage.removeItem('user_data');
            localStorage.removeItem('session_id');
            localStorage.removeItem('remember_me');

            // 清理token刷新定时器
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
                this.refreshTimer = null;
            }

            this.onAuthStateChanged(false);

            // 重定向到登录页
            if (window.location.pathname !== '/login.html' && window.location.pathname !== '/') {
                window.location.href = '/login.html';
            }
        }
    },
    
    /**
     * 显示消息
     */
    showMessage(message, type = 'info') {
        console.log(`[${type.toUpperCase()}] ${message}`);
        
        // 如果存在Toast组件，使用它
        if (window.showToast) {
            window.showToast(message, type);
            return;
        }
        
        // 否则创建一个简单的消息框
        const toast = document.createElement('div');
        toast.className = `auth-toast ${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // 3秒后自动移除
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 500);
        }, 3000);
    },

    /**
     * 获取当前用户信息
     */
    async getCurrentUser() {
        if (!this.token) {
            throw new Error('未登录');
        }

        try {
            const response = await fetch(this.apiConfig.endpoints.user, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                this.user = result.data;
                localStorage.setItem('user_data', JSON.stringify(this.user));
                return this.user;
            } else {
                throw new Error(result.message || '获取用户信息失败');
            }
        } catch (error) {
            console.error('Get current user error:', error);
            throw error;
        }
    },

    /**
     * 更新用户信息
     */
    async updateProfile(profileData) {
        if (!this.user || !this.token) {
            throw new Error('未登录');
        }

        try {
            const response = await fetch(this.apiConfig.endpoints.user, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(profileData)
            });

            const result = await response.json();

            if (result.success) {
                this.user = result.data;
                localStorage.setItem('user_data', JSON.stringify(this.user));
                return this.user;
            } else {
                throw new Error(result.message || '更新用户信息失败');
            }
        } catch (error) {
            console.error('Update profile error:', error);
            throw error;
        }
    },

    /**
     * 修改密码
     */
    async changePassword(currentPassword, newPassword) {
        if (!this.token) {
            throw new Error('未登录');
        }

        try {
            const response = await fetch(this.apiConfig.endpoints.user + '/password', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    currentPassword: currentPassword,
                    newPassword: newPassword
                })
            });

            const result = await response.json();

            if (result.success) {
                return { success: true, message: result.message };
            } else {
                throw new Error(result.message || '修改密码失败');
            }
        } catch (error) {
            console.error('Change password error:', error);
            throw error;
        }
    },

    /**
     * 设置token自动刷新
     */
    setupTokenRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        // 每30分钟检查一次token状态
        this.refreshTimer = setInterval(() => {
            this.checkTokenExpiry();
        }, 30 * 60 * 1000);
    },

    /**
     * 检查token是否即将过期
     */
    checkTokenExpiry() {
        if (!this.token) {
            return;
        }

        try {
            // 解析JWT token的payload
            const payload = JSON.parse(atob(this.token.split('.')[1]));
            const exp = payload.exp * 1000; // 转换为毫秒
            const now = Date.now();
            const timeUntilExpiry = exp - now;

            // 如果token在5分钟内过期，尝试刷新
            if (timeUntilExpiry < 5 * 60 * 1000) {
                this.refreshToken();
            }
        } catch (error) {
            console.warn('Token expiry check failed:', error);
        }
    },

    /**
     * 刷新token
     */
    async refreshToken() {
        try {
            const response = await fetch(this.apiConfig.endpoints.user + '/refresh', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                this.token = result.token;
                localStorage.setItem('auth_token', this.token);
                localStorage.setItem('token', this.token);
                return true;
            } else {
                // 刷新失败，登出用户
                this.logout();
                return false;
            }
        } catch (error) {
            console.error('Token refresh error:', error);
            this.logout();
            return false;
        }
    },

    /**
     * 创建认证的fetch请求
     */
    async authenticatedFetch(url, options = {}) {
        if (!this.token) {
            throw new Error('未登录');
        }

        const headers = {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json',
            ...(options.headers || {})
        };

        const response = await fetch(url, {
            ...options,
            headers
        });

        // 如果返回401，token可能已过期
        if (response.status === 401) {
            const refreshed = await this.refreshToken();
            if (refreshed) {
                // 重试请求
                headers['Authorization'] = `Bearer ${this.token}`;
                return fetch(url, { ...options, headers });
            } else {
                throw new Error('认证失败');
            }
        }

        return response;
    },

    /**
     * 检查是否有特定权限
     */
    hasPermission(permission) {
        if (!this.user) {
            return false;
        }

        const userRole = this.user.role;
        
        // 管理员有所有权限
        if (userRole === 'admin') {
            return true;
        }

        // 这里可以根据需要实现更复杂的权限检查逻辑
        const rolePermissions = {
            'moderator': ['user.read', 'user.update', 'chat.read', 'chat.create', 'chat.update'],
            'user': ['user.read.own', 'user.update.own', 'chat.read.own', 'chat.create', 'chat.update.own']
        };

        const permissions = rolePermissions[userRole] || [];
        return permissions.includes(permission) || permissions.includes('*');
    },

    /**
     * 是否是管理员
     */
    isAdmin() {
        return this.user && this.user.role === 'admin';
    },

    /**
     * 是否是版主
     */
    isModerator() {
        return this.user && ['admin', 'moderator'].includes(this.user.role);
    },

    onAuthStateChanged(isLoggedIn) {
        
        
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
    }
};

// 导出 auth 对象
export { auth };
