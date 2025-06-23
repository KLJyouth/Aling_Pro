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
     * 设置令牌刷新
     */
    setupTokenRefresh() {
        // 清除现有的刷新计时器
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        // 如果没有令牌，不设置刷新
        if (!this.token) return;
        
        try {
            // 解析JWT令牌
            const tokenParts = this.token.split('.');
            if (tokenParts.length !== 3) {
                console.error('Invalid token format');
                return;
            }
            
            // 解码令牌负载
            const payload = JSON.parse(atob(tokenParts[1]));
            
            // 检查过期时间
            if (!payload.exp) {
                console.warn('Token has no expiration time');
                return;
            }
            
            // 计算刷新时间（过期前5分钟）
            const expTime = payload.exp * 1000; // 转换为毫秒
            const refreshTime = expTime - Date.now() - (5 * 60 * 1000);
            
            // 如果令牌已经过期或即将过期，立即刷新
            if (refreshTime <= 0) {
                console.log('Token expired or about to expire, refreshing now');
                this.refreshToken();
                return;
            }
            
            // 设置定时器在令牌过期前刷新
            console.log(`Setting up token refresh in ${Math.floor(refreshTime / 1000 / 60)} minutes`);
            this.refreshTimer = setTimeout(() => {
                this.refreshToken();
            }, refreshTime);
            
        } catch (error) {
            console.error('Error setting up token refresh:', error);
        }
    }
    
    /**
     * 检查令牌是否过期
     */
    checkTokenExpiry() {
        if (!this.token) return true;
        
        try {
            // 解析JWT令牌
            const tokenParts = this.token.split('.');
            if (tokenParts.length !== 3) return true;
            
            // 解码令牌负载
            const payload = JSON.parse(atob(tokenParts[1]));
            
            // 检查过期时间
            if (!payload.exp) return false;
            
            // 比较过期时间与当前时间
            const now = Math.floor(Date.now() / 1000);
            return payload.exp <= now;
        } catch (error) {
            console.error('Error checking token expiry:', error);
            return true;
        }
    }
    
    /**
     * 刷新认证令牌
     */
    async refreshToken() {
        // 如果没有令牌，不进行刷新
        if (!this.token) return;
        
        try {
            console.log('Refreshing authentication token...');
            
            // 获取设备指纹
            const deviceFingerprint = await this.getDeviceFingerprint();
            
            // 发送刷新请求
            const response = await fetch(`${this.apiConfig.baseUrl}/refresh-token`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify({
                    device_fingerprint: deviceFingerprint,
                    session_id: localStorage.getItem('session_id') || null
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // 更新令牌
                this.token = result.token;
                localStorage.setItem('auth_token', this.token);
                localStorage.setItem('token', this.token);
                
                // 如果返回了新的用户数据，更新用户信息
                if (result.user) {
                    this.user = result.user;
                    localStorage.setItem('user_data', JSON.stringify(this.user));
                }
                
                // 如果返回了新的会话ID，更新会话ID
                if (result.session_id) {
                    localStorage.setItem('session_id', result.session_id);
                }
                
                // 设置下一次刷新
                this.setupTokenRefresh();
                
                console.log('Token refreshed successfully');
                return true;
            } else {
                console.error('Token refresh failed:', result.message);
                
                // 如果刷新失败且原因是令牌已失效，则登出
                if (result.error === 'invalid_token' || result.error === 'token_expired') {
                    console.warn('Token invalid or expired, logging out');
                    this.logout();
                }
                
                return false;
            }
        } catch (error) {
            console.error('Error refreshing token:', error);
            return false;
        }
    }
    
    /**
     * 获取设备指纹
     */
    async getDeviceFingerprint() {
        try {
            // 收集设备信息
            const deviceInfo = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                screenWidth: window.screen.width,
                screenHeight: window.screen.height,
                colorDepth: window.screen.colorDepth,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                timezoneOffset: new Date().getTimezoneOffset(),
                sessionStorage: !!window.sessionStorage,
                localStorage: !!window.localStorage,
                indexedDB: !!window.indexedDB,
                cpuCores: navigator.hardwareConcurrency || 0,
                deviceMemory: navigator.deviceMemory || 0,
                touchPoints: navigator.maxTouchPoints || 0
            };
            
            // 获取已存储的指纹
            const storedFingerprint = localStorage.getItem('device_fingerprint');
            
            // 如果已有指纹，返回
            if (storedFingerprint) {
                return storedFingerprint;
            }
            
            // 生成新指纹
            const fingerprintString = JSON.stringify(deviceInfo);
            const fingerprint = await this.hashString(fingerprintString);
            
            // 存储指纹
            localStorage.setItem('device_fingerprint', fingerprint);
            
            return fingerprint;
        } catch (error) {
            console.error('Error generating device fingerprint:', error);
            return 'unknown-device';
        }
    }
    
    /**
     * 哈希字符串（用于设备指纹）
     */
    async hashString(str) {
        try {
            // 使用SubtleCrypto API进行SHA-256哈希
            const encoder = new TextEncoder();
            const data = encoder.encode(str);
            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
            
            // 转换为十六进制字符串
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        } catch (error) {
            // 降级处理：简单哈希
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // 转换为32位整数
            }
            return hash.toString(16);
        }
    }
    
    /**
     * 使用带有认证的fetch发送请求
     */
    async authenticatedFetch(url, options = {}) {
        // 如果令牌已过期，尝试刷新
        if (this.checkTokenExpiry()) {
            const refreshed = await this.refreshToken();
            if (!refreshed) {
                // 如果刷新失败，重定向到登录页面
                this.logout();
                throw new Error('Authentication required');
            }
        }
        
        // 准备请求头
        const headers = options.headers || {};
        headers['Authorization'] = `Bearer ${this.token}`;
        
        // 添加设备指纹
        const deviceFingerprint = await this.getDeviceFingerprint();
        headers['X-Device-Fingerprint'] = deviceFingerprint;
        
        // 添加会话ID
        const sessionId = localStorage.getItem('session_id');
        if (sessionId) {
            headers['X-Session-ID'] = sessionId;
        }
        
        // 发送请求
        const response = await fetch(url, {
            ...options,
            headers
        });
        
        // 检查认证错误
        if (response.status === 401) {
            // 尝试刷新令牌
            const refreshed = await this.refreshToken();
            if (refreshed) {
                // 如果刷新成功，重试请求
                headers['Authorization'] = `Bearer ${this.token}`;
                return fetch(url, {
                    ...options,
                    headers
                });
            } else {
                // 如果刷新失败，登出
                this.logout();
                throw new Error('Authentication required');
            }
        }
        
        return response;
    }
    
    /**
     * 检查用户是否有特定权限
     */
    hasPermission(permission) {
        // 如果用户未登录，没有任何权限
        if (!this.isAuthenticated || !this.user) {
            return false;
        }
        
        // 如果用户是管理员，拥有所有权限
        if (this.user.role === 'admin') {
            return true;
        }
        
        // 检查用户权限列表
        if (this.user.permissions && Array.isArray(this.user.permissions)) {
            return this.user.permissions.includes(permission);
        }
        
        // 检查用户角色权限
        const rolePermissions = {
            'moderator': ['read', 'create', 'update'],
            'editor': ['read', 'create', 'update'],
            'viewer': ['read']
        };
        
        const userRole = this.user.role || 'viewer';
        const allowedPermissions = rolePermissions[userRole] || [];
        
        return allowedPermissions.includes(permission);
    }
    
    /**
     * 检查用户是否是管理员
     */
    isAdmin() {
        return this.isAuthenticated && this.user && this.user.role === 'admin';
    }
    
    /**
     * 检查用户是否是版主
     */
    isModerator() {
        return this.isAuthenticated && this.user && 
               (this.user.role === 'moderator' || this.user.role === 'admin');
    }
    
    /**
     * 添加社交媒体登录
     */
    async socialLogin(provider) {
        try {
            // 获取社交登录URL
            const response = await fetch(`${this.apiConfig.baseUrl}/social-login-url`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    provider,
                    redirect_uri: window.location.origin + '/auth/callback'
                })
            });
            
            const result = await response.json();
            
            if (result.success && result.url) {
                // 存储状态以便回调验证
                localStorage.setItem('social_login_state', result.state);
                localStorage.setItem('social_login_provider', provider);
                
                // 重定向到社交登录页面
                window.location.href = result.url;
                return true;
            } else {
                throw new Error(result.message || '社交登录初始化失败');
            }
        } catch (error) {
            console.error(`${provider} login error:`, error);
            this.showMessage(`${provider}登录失败: ${error.message}`, 'error');
            return false;
        }
    }
    
    /**
     * 处理社交登录回调
     */
    async handleSocialLoginCallback(params) {
        try {
            // 验证状态
            const storedState = localStorage.getItem('social_login_state');
            const provider = localStorage.getItem('social_login_provider');
            
            if (!storedState || !provider || storedState !== params.state) {
                throw new Error('无效的登录请求');
            }
            
            // 清除存储的状态
            localStorage.removeItem('social_login_state');
            localStorage.removeItem('social_login_provider');
            
            // 获取设备指纹
            const deviceFingerprint = await this.getDeviceFingerprint();
            
            // 发送验证请求
            const response = await fetch(`${this.apiConfig.baseUrl}/social-login-callback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    provider,
                    code: params.code,
                    state: params.state,
                    device_fingerprint: deviceFingerprint
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
                
                this.onAuthStateChanged(true);
                this.setupTokenRefresh();
                
                // 显示成功消息
                this.showMessage(`${provider}登录成功`, 'success');
                
                return { success: true, user: this.user };
            } else {
                throw new Error(result.message || '社交登录验证失败');
            }
        } catch (error) {
            console.error('Social login callback error:', error);
            this.showMessage(error.message, 'error');
            return { success: false, message: error.message };
        }
    }
    
    /**
     * 检测可疑登录
     */
    async detectSuspiciousLogin(loginData) {
        try {
            // 获取设备指纹
            const deviceFingerprint = await this.getDeviceFingerprint();
            
            // 获取地理位置（如果可用）
            let geolocation = null;
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        timeout: 5000,
                        maximumAge: 600000
                    });
                });
                
                geolocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
            } catch (error) {
                console.warn('Geolocation not available:', error);
            }
            
            // 收集登录数据
            const suspiciousLoginData = {
                ...loginData,
                device_fingerprint: deviceFingerprint,
                user_agent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                geolocation,
                screen: {
                    width: window.screen.width,
                    height: window.screen.height,
                    colorDepth: window.screen.colorDepth
                },
                window: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                referrer: document.referrer,
                previous_logins: JSON.parse(localStorage.getItem('previous_logins') || '[]')
            };
            
            // 发送检测请求
            const response = await fetch(`${this.apiConfig.baseUrl}/detect-suspicious-login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(suspiciousLoginData)
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error detecting suspicious login:', error);
            return { suspicious: false, confidence: 0 };
        }
    }

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
