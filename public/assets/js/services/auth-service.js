/**
 * 身份认证服务
 * 处理用户登录、注册、token管理等
 */

class AuthService {
    constructor(httpClient) {
        this.http = httpClient;
        this.currentUser = null;
        this.token = null;
        this.refreshTimer = null;
        this.eventBus = new EventTarget();
        
        this.init();
    }

    /**
     * 初始化
     */
    init() {
        // 从localStorage恢复token
        this.token = localStorage.getItem('auth_token');
        
        if (this.token) {
            // 设置HTTP客户端的默认header
            this.http.setDefaultHeaders({
                'Authorization': `Bearer ${this.token}`
            });
        }
    }

    /**
     * 检查认证状态
     */
    async checkAuthStatus() {
        if (!this.token) {
            return false;
        }

        try {
            const response = await this.http.get('/auth/me');
            this.currentUser = response.data.user;
            this.setupTokenRefresh();
            this.emit('authenticated', this.currentUser);
            return true;
        } catch (error) {
            console.error('认证检查失败:', error);
            this.logout();
            return false;
        }
    }

    /**
     * 用户登录
     */
    async login(credentials) {
        try {
            const response = await this.http.post('/auth/login', credentials);
            
            if (response.data.success) {
                this.token = response.data.token;
                this.currentUser = response.data.user;
                
                // 保存token
                localStorage.setItem('auth_token', this.token);
                
                // 设置HTTP客户端header
                this.http.setDefaultHeaders({
                    'Authorization': `Bearer ${this.token}`
                });
                
                // 设置token刷新
                this.setupTokenRefresh();
                
                // 触发认证事件
                this.emit('authenticated', this.currentUser);
                
                return {
                    success: true,
                    user: this.currentUser,
                    token: this.token
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '登录失败'
                };
            }
        } catch (error) {
            console.error('登录失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '登录失败，请检查网络连接'
            };
        }
    }

    /**
     * 用户注册
     */
    async register(userData) {
        try {
            const response = await this.http.post('/auth/register', userData);
            
            if (response.data.success) {
                return {
                    success: true,
                    message: response.data.message || '注册成功',
                    user: response.data.user
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '注册失败'
                };
            }
        } catch (error) {
            console.error('注册失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '注册失败，请检查网络连接'
            };
        }
    }

    /**
     * 用户登出
     */
    async logout() {
        try {
            // 通知服务器
            if (this.token) {
                await this.http.post('/auth/logout');
            }
        } catch (error) {
            console.error('登出请求失败:', error);
        } finally {
            // 清理本地状态
            this.clearAuthData();
            this.emit('unauthenticated');
        }
    }

    /**
     * 刷新token
     */
    async refreshToken() {
        try {
            const response = await this.http.post('/auth/refresh');
            
            if (response.data.success) {
                this.token = response.data.token;
                localStorage.setItem('auth_token', this.token);
                
                this.http.setDefaultHeaders({
                    'Authorization': `Bearer ${this.token}`
                });
                
                this.setupTokenRefresh();
                this.emit('tokenRefreshed', this.token);
                
                return true;
            } else {
                throw new Error('Token刷新失败');
            }
        } catch (error) {
            console.error('Token刷新失败:', error);
            this.logout();
            return false;
        }
    }

    /**
     * 修改密码
     */
    async changePassword(oldPassword, newPassword) {
        try {
            const response = await this.http.post('/auth/change-password', {
                old_password: oldPassword,
                new_password: newPassword
            });
            
            if (response.data.success) {
                return {
                    success: true,
                    message: response.data.message || '密码修改成功'
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '密码修改失败'
                };
            }
        } catch (error) {
            console.error('修改密码失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '密码修改失败'
            };
        }
    }

    /**
     * 忘记密码
     */
    async forgotPassword(email) {
        try {
            const response = await this.http.post('/auth/forgot-password', { email });
            
            if (response.data.success) {
                return {
                    success: true,
                    message: response.data.message || '重置链接已发送到您的邮箱'
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '发送失败'
                };
            }
        } catch (error) {
            console.error('发送重置邮件失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '发送失败'
            };
        }
    }

    /**
     * 重置密码
     */
    async resetPassword(token, newPassword) {
        try {
            const response = await this.http.post('/auth/reset-password', {
                token,
                password: newPassword
            });
            
            if (response.data.success) {
                return {
                    success: true,
                    message: response.data.message || '密码重置成功'
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '密码重置失败'
                };
            }
        } catch (error) {
            console.error('重置密码失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '密码重置失败'
            };
        }
    }

    /**
     * 验证邮箱
     */
    async verifyEmail(token) {
        try {
            const response = await this.http.post('/auth/verify-email', { token });
            
            if (response.data.success) {
                if (this.currentUser) {
                    this.currentUser.email_verified = true;
                    this.emit('userUpdated', this.currentUser);
                }
                
                return {
                    success: true,
                    message: response.data.message || '邮箱验证成功'
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '邮箱验证失败'
                };
            }
        } catch (error) {
            console.error('邮箱验证失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '邮箱验证失败'
            };
        }
    }

    /**
     * 重发验证邮件
     */
    async resendVerificationEmail() {
        try {
            const response = await this.http.post('/auth/resend-verification');
            
            if (response.data.success) {
                return {
                    success: true,
                    message: response.data.message || '验证邮件已重新发送'
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '发送失败'
                };
            }
        } catch (error) {
            console.error('重发验证邮件失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '发送失败'
            };
        }
    }

    /**
     * 更新用户信息
     */
    async updateProfile(userData) {
        try {
            const response = await this.http.put('/user/profile', userData);
            
            if (response.data.success) {
                this.currentUser = { ...this.currentUser, ...response.data.user };
                this.emit('userUpdated', this.currentUser);
                
                return {
                    success: true,
                    message: response.data.message || '个人信息更新成功',
                    user: this.currentUser
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || '更新失败'
                };
            }
        } catch (error) {
            console.error('更新用户信息失败:', error);
            return {
                success: false,
                message: error.response?.data?.message || '更新失败'
            };
        }
    }

    /**
     * 设置token自动刷新
     */
    setupTokenRefresh() {
        // 清除现有的定时器
        if (this.refreshTimer) {
            clearTimeout(this.refreshTimer);
        }

        // 解析token获取过期时间
        try {
            const payload = JSON.parse(atob(this.token.split('.')[1]));
            const expirationTime = payload.exp * 1000; // 转换为毫秒
            const currentTime = Date.now();
            const timeUntilExpiry = expirationTime - currentTime;
            
            // 在token过期前5分钟刷新
            const refreshTime = Math.max(timeUntilExpiry - 5 * 60 * 1000, 0);
            
            if (refreshTime > 0) {
                this.refreshTimer = setTimeout(() => {
                    this.refreshToken();
                }, refreshTime);
            } else {
                // token已过期或即将过期，立即刷新
                this.refreshToken();
            }
        } catch (error) {
            console.error('解析token失败:', error);
            // 设置默认的刷新时间（55分钟）
            this.refreshTimer = setTimeout(() => {
                this.refreshToken();
            }, 55 * 60 * 1000);
        }
    }

    /**
     * 清理认证数据
     */
    clearAuthData() {
        this.currentUser = null;
        this.token = null;
        
        localStorage.removeItem('auth_token');
        
        // 清除HTTP客户端的Authorization header
        this.http.setDefaultHeaders({
            'Authorization': undefined
        });
        
        // 清除刷新定时器
        if (this.refreshTimer) {
            clearTimeout(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    /**
     * 检查用户权限
     */
    hasPermission(permission) {
        if (!this.currentUser) {
            return false;
        }
        
        // 管理员拥有所有权限
        if (this.currentUser.role === 'admin') {
            return true;
        }
        
        // 检查用户权限
        return this.currentUser.permissions?.includes(permission) || false;
    }

    /**
     * 检查用户角色
     */
    hasRole(role) {
        return this.currentUser?.role === role;
    }

    /**
     * 获取当前用户
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * 获取当前token
     */
    getToken() {
        return this.token;
    }

    /**
     * 是否已认证
     */
    isAuthenticated() {
        return !!this.token && !!this.currentUser;
    }

    /**
     * 监听认证事件
     */
    on(eventName, handler) {
        this.eventBus.addEventListener(eventName, handler);
    }

    /**
     * 移除事件监听器
     */
    off(eventName, handler) {
        this.eventBus.removeEventListener(eventName, handler);
    }

    /**
     * 发送事件
     */
    emit(eventName, data = null) {
        this.eventBus.dispatchEvent(new CustomEvent(eventName, { detail: data }));
    }
}

export { AuthService };
