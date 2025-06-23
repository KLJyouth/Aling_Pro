/**
 * 多种登录方式集成系统
 * 支持OAuth、SAML、生物识别、社交登录等多种认证方式
 */

class MultiAuthSystem {
    constructor() {
        this.config = {
            enabledProviders: {
                google: true,
                github: true,
                microsoft: true,
                wechat: true,
                qq: true,
                weibo: true,
                dingtalk: true,
                feishu: true,
                biometric: true,
                ldap: false,
                saml: false
            },
            oauth: {
                google: {
                    clientId: process.env.GOOGLE_CLIENT_ID || '',
                    redirectUri: window.location.origin + '/auth/google/callback',
                    scope: 'openid email profile'
                },
                github: {
                    clientId: process.env.GITHUB_CLIENT_ID || '',
                    redirectUri: window.location.origin + '/auth/github/callback',
                    scope: 'user:email'
                },
                microsoft: {
                    clientId: process.env.MICROSOFT_CLIENT_ID || '',
                    redirectUri: window.location.origin + '/auth/microsoft/callback',
                    scope: 'openid email profile'
                }
            },
            biometric: {
                enableFingerprint: true,
                enableFaceId: true,
                enableVoice: false,
                timeout: 30000
            }
        };
        
        this.providers = new Map();
        this.authState = {
            currentProvider: null,
            isAuthenticating: false,
            lastProvider: null,
            fallbackMethods: []
        };
        
        this.init();
    }

    // ==================== 初始化 ====================

    async init() {
        console.log('🔑 初始化多种登录方式系统...');
        
        try {
            await this.initializeProviders();
            this.setupEventListeners();
            this.loadAuthState();
            
            console.log('✅ 多种登录方式系统初始化完成');
            console.log('📋 可用登录方式:', Array.from(this.providers.keys()));
        } catch (error) {
            console.error('❌ 多种登录方式系统初始化失败:', error);
        }
    }

    async initializeProviders() {
        // 初始化OAuth提供商
        if (this.config.enabledProviders.google) {
            await this.initGoogleAuth();
        }
        
        if (this.config.enabledProviders.github) {
            await this.initGitHubAuth();
        }
        
        if (this.config.enabledProviders.microsoft) {
            await this.initMicrosoftAuth();
        }
        
        // 初始化中国社交登录
        if (this.config.enabledProviders.wechat) {
            await this.initWeChatAuth();
        }
        
        if (this.config.enabledProviders.qq) {
            await this.initQQAuth();
        }
        
        if (this.config.enabledProviders.weibo) {
            await this.initWeiboAuth();
        }
        
        // 初始化企业登录
        if (this.config.enabledProviders.dingtalk) {
            await this.initDingTalkAuth();
        }
        
        if (this.config.enabledProviders.feishu) {
            await this.initFeishuAuth();
        }
        
        // 初始化生物识别
        if (this.config.enabledProviders.biometric) {
            await this.initBiometricAuth();
        }
        
        // 初始化LDAP
        if (this.config.enabledProviders.ldap) {
            await this.initLDAPAuth();
        }
        
        // 初始化SAML
        if (this.config.enabledProviders.saml) {
            await this.initSAMLAuth();
        }
    }

    setupEventListeners() {
        // 监听OAuth回调
        window.addEventListener('message', (event) => {
            this.handleOAuthCallback(event);
        });
        
        // 监听生物识别事件
        document.addEventListener('biometricAuth', (event) => {
            this.handleBiometricResult(event.detail);
        });
    }

    // ==================== Google OAuth ====================

    async initGoogleAuth() {
        try {
            // 动态加载Google API
            await this.loadScript('https://accounts.google.com/gsi/client');
            
            this.providers.set('google', {
                type: 'oauth',
                name: 'Google',
                icon: 'fab fa-google',
                color: '#db4437',
                login: this.loginWithGoogle.bind(this),
                logout: this.logoutGoogle.bind(this)
            });
            
            console.log('✅ Google认证初始化完成');
        } catch (error) {
            console.error('❌ Google认证初始化失败:', error);
        }
    }

    async loginWithGoogle() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'google';
                
                if (window.google && window.google.accounts) {
                    window.google.accounts.id.initialize({
                        client_id: this.config.oauth.google.clientId,
                        callback: (response) => {
                            this.handleGoogleCallback(response, resolve, reject);
                        }
                    });
                    
                    window.google.accounts.id.prompt();
                } else {
                    // 使用弹窗方式
                    const authUrl = this.buildGoogleAuthUrl();
                    this.openAuthPopup(authUrl, 'google', resolve, reject);
                }
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    buildGoogleAuthUrl() {
        const params = new URLSearchParams({
            client_id: this.config.oauth.google.clientId,
            redirect_uri: this.config.oauth.google.redirectUri,
            response_type: 'code',
            scope: this.config.oauth.google.scope,
            access_type: 'offline',
            state: this.generateState()
        });
        
        return `https://accounts.google.com/oauth/authorize?${params.toString()}`;
    }

    async handleGoogleCallback(response, resolve, reject) {
        try {
            const credential = response.credential;
            const payload = JSON.parse(atob(credential.split('.')[1]));
            
            const authResult = {
                provider: 'google',
                id: payload.sub,
                email: payload.email,
                name: payload.name,
                picture: payload.picture,
                credential: credential,
                timestamp: Date.now()
            };
            
            this.authState.isAuthenticating = false;
            this.authState.lastProvider = 'google';
            
            resolve(authResult);
        } catch (error) {
            this.authState.isAuthenticating = false;
            reject(error);
        }
    }

    logoutGoogle() {
        if (window.google && window.google.accounts) {
            window.google.accounts.id.disableAutoSelect();
        }
    }

    // ==================== GitHub OAuth ====================

    async initGitHubAuth() {
        this.providers.set('github', {
            type: 'oauth',
            name: 'GitHub',
            icon: 'fab fa-github',
            color: '#333',
            login: this.loginWithGitHub.bind(this),
            logout: this.logoutGitHub.bind(this)
        });
        
        console.log('✅ GitHub认证初始化完成');
    }

    async loginWithGitHub() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'github';
                
                const authUrl = this.buildGitHubAuthUrl();
                this.openAuthPopup(authUrl, 'github', resolve, reject);
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    buildGitHubAuthUrl() {
        const params = new URLSearchParams({
            client_id: this.config.oauth.github.clientId,
            redirect_uri: this.config.oauth.github.redirectUri,
            scope: this.config.oauth.github.scope,
            state: this.generateState()
        });
        
        return `https://github.com/login/oauth/authorize?${params.toString()}`;
    }

    logoutGitHub() {
        // GitHub没有特殊的登出方法
    }

    // ==================== Microsoft OAuth ====================

    async initMicrosoftAuth() {
        try {
            // 可以集成Microsoft Graph SDK
            this.providers.set('microsoft', {
                type: 'oauth',
                name: 'Microsoft',
                icon: 'fab fa-microsoft',
                color: '#0078d4',
                login: this.loginWithMicrosoft.bind(this),
                logout: this.logoutMicrosoft.bind(this)
            });
            
            console.log('✅ Microsoft认证初始化完成');
        } catch (error) {
            console.error('❌ Microsoft认证初始化失败:', error);
        }
    }

    async loginWithMicrosoft() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'microsoft';
                
                const authUrl = this.buildMicrosoftAuthUrl();
                this.openAuthPopup(authUrl, 'microsoft', resolve, reject);
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    buildMicrosoftAuthUrl() {
        const params = new URLSearchParams({
            client_id: this.config.oauth.microsoft.clientId,
            response_type: 'code',
            redirect_uri: this.config.oauth.microsoft.redirectUri,
            scope: this.config.oauth.microsoft.scope,
            state: this.generateState()
        });
        
        return `https://login.microsoftonline.com/common/oauth2/v2.0/authorize?${params.toString()}`;
    }

    logoutMicrosoft() {
        // Microsoft特殊登出处理
    }

    // ==================== 微信登录 ====================

    async initWeChatAuth() {
        this.providers.set('wechat', {
            type: 'social',
            name: '微信',
            icon: 'fab fa-weixin',
            color: '#1aad19',
            login: this.loginWithWeChat.bind(this),
            logout: this.logoutWeChat.bind(this)
        });
        
        console.log('✅ 微信认证初始化完成');
    }

    async loginWithWeChat() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'wechat';
                
                // 检测是否在微信内置浏览器
                if (this.isWeChatBrowser()) {
                    this.wechatInAppLogin(resolve, reject);
                } else {
                    this.wechatQRCodeLogin(resolve, reject);
                }
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    isWeChatBrowser() {
        return /MicroMessenger/i.test(navigator.userAgent);
    }

    async wechatInAppLogin(resolve, reject) {
        try {
            // 微信内置浏览器登录
            const authUrl = this.buildWeChatAuthUrl();
            window.location.href = authUrl;
        } catch (error) {
            reject(error);
        }
    }

    async wechatQRCodeLogin(resolve, reject) {
        try {
            // 二维码登录
            this.showWeChatQRCode(resolve, reject);
        } catch (error) {
            reject(error);
        }
    }

    buildWeChatAuthUrl() {
        const appId = this.config.wechat?.appId || '';
        const redirectUri = encodeURIComponent(window.location.origin + '/auth/wechat/callback');
        const state = this.generateState();
        
        return `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirectUri}&response_type=code&scope=snsapi_userinfo&state=${state}#wechat_redirect`;
    }

    showWeChatQRCode(resolve, reject) {
        // 创建二维码登录界面
        const modal = this.createAuthModal('wechat-qr', '微信扫码登录');
        
        const qrContainer = document.createElement('div');
        qrContainer.className = 'qr-container text-center p-6';
        qrContainer.innerHTML = `
            <div class="qr-code mb-4">
                <img src="/api/auth/wechat/qr" alt="微信登录二维码" class="w-48 h-48 mx-auto border rounded-lg">
            </div>
            <p class="text-gray-600 mb-2">请使用微信扫描二维码</p>
            <p class="text-sm text-gray-500">扫码后在手机上确认登录</p>
            <div class="mt-4">
                <button id="refreshQR" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-refresh mr-1"></i>刷新二维码
                </button>
            </div>
        `;
        
        modal.querySelector('.modal-body').appendChild(qrContainer);
        
        // 轮询检查登录状态
        this.pollWeChatLoginStatus(resolve, reject);
    }

    async pollWeChatLoginStatus(resolve, reject) {
        const pollInterval = setInterval(async () => {
            try {
                const response = await fetch('/api/auth/wechat/status');
                const result = await response.json();
                
                if (result.success) {
                    clearInterval(pollInterval);
                    this.closeAuthModal();
                    resolve(result.data);
                } else if (result.expired) {
                    clearInterval(pollInterval);
                    reject(new Error('二维码已过期'));
                }
            } catch (error) {
                clearInterval(pollInterval);
                reject(error);
            }
        }, 2000);
        
        // 5分钟超时
        setTimeout(() => {
            clearInterval(pollInterval);
            reject(new Error('登录超时'));
        }, 300000);
    }

    logoutWeChat() {
        // 微信登出处理
    }

    // ==================== QQ登录 ====================

    async initQQAuth() {
        this.providers.set('qq', {
            type: 'social',
            name: 'QQ',
            icon: 'fab fa-qq',
            color: '#12b7f5',
            login: this.loginWithQQ.bind(this),
            logout: this.logoutQQ.bind(this)
        });
        
        console.log('✅ QQ认证初始化完成');
    }

    async loginWithQQ() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'qq';
                
                const authUrl = this.buildQQAuthUrl();
                this.openAuthPopup(authUrl, 'qq', resolve, reject);
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    buildQQAuthUrl() {
        const params = new URLSearchParams({
            client_id: this.config.qq?.appId || '',
            redirect_uri: encodeURIComponent(window.location.origin + '/auth/qq/callback'),
            response_type: 'code',
            scope: 'get_user_info',
            state: this.generateState()
        });
        
        return `https://graph.qq.com/oauth2.0/authorize?${params.toString()}`;
    }

    logoutQQ() {
        // QQ登出处理
    }

    // ==================== 微博登录 ====================

    async initWeiboAuth() {
        this.providers.set('weibo', {
            type: 'social',
            name: '微博',
            icon: 'fab fa-weibo',
            color: '#e6162d',
            login: this.loginWithWeibo.bind(this),
            logout: this.logoutWeibo.bind(this)
        });
        
        console.log('✅ 微博认证初始化完成');
    }

    async loginWithWeibo() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'weibo';
                
                const authUrl = this.buildWeiboAuthUrl();
                this.openAuthPopup(authUrl, 'weibo', resolve, reject);
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    buildWeiboAuthUrl() {
        const params = new URLSearchParams({
            client_id: this.config.weibo?.appKey || '',
            redirect_uri: encodeURIComponent(window.location.origin + '/auth/weibo/callback'),
            response_type: 'code',
            state: this.generateState()
        });
        
        return `https://api.weibo.com/oauth2/authorize?${params.toString()}`;
    }

    logoutWeibo() {
        // 微博登出处理
    }

    // ==================== 钉钉登录 ====================

    async initDingTalkAuth() {
        this.providers.set('dingtalk', {
            type: 'enterprise',
            name: '钉钉',
            icon: 'fas fa-building',
            color: '#006aff',
            login: this.loginWithDingTalk.bind(this),
            logout: this.logoutDingTalk.bind(this)
        });
        
        console.log('✅ 钉钉认证初始化完成');
    }

    async loginWithDingTalk() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'dingtalk';
                
                if (this.isDingTalkBrowser()) {
                    this.dingtalkInAppLogin(resolve, reject);
                } else {
                    this.dingtalkWebLogin(resolve, reject);
                }
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    isDingTalkBrowser() {
        return /DingTalk/i.test(navigator.userAgent);
    }

    async dingtalkInAppLogin(resolve, reject) {
        try {
            // 钉钉内置浏览器登录
            // 需要钉钉JS SDK
            if (window.dd) {
                window.dd.ready(() => {
                    window.dd.runtime.permission.requestAuthCode({
                        corpId: this.config.dingtalk?.corpId || '',
                        onSuccess: (result) => {
                            this.handleDingTalkCallback(result, resolve, reject);
                        },
                        onFail: (error) => {
                            reject(error);
                        }
                    });
                });
            } else {
                reject(new Error('钉钉SDK未加载'));
            }
        } catch (error) {
            reject(error);
        }
    }

    async dingtalkWebLogin(resolve, reject) {
        try {
            const authUrl = this.buildDingTalkAuthUrl();
            this.openAuthPopup(authUrl, 'dingtalk', resolve, reject);
        } catch (error) {
            reject(error);
        }
    }

    buildDingTalkAuthUrl() {
        const params = new URLSearchParams({
            appid: this.config.dingtalk?.appId || '',
            redirect_uri: encodeURIComponent(window.location.origin + '/auth/dingtalk/callback'),
            response_type: 'code',
            scope: 'snsapi_login',
            state: this.generateState()
        });
        
        return `https://oapi.dingtalk.com/connect/oauth2/sns_authorize?${params.toString()}`;
    }

    async handleDingTalkCallback(result, resolve, reject) {
        try {
            // 处理钉钉回调
            const authResult = {
                provider: 'dingtalk',
                code: result.code,
                timestamp: Date.now()
            };
            
            resolve(authResult);
        } catch (error) {
            reject(error);
        }
    }

    logoutDingTalk() {
        // 钉钉登出处理
    }

    // ==================== 飞书登录 ====================

    async initFeishuAuth() {
        this.providers.set('feishu', {
            type: 'enterprise',
            name: '飞书',
            icon: 'fas fa-feather',
            color: '#00d4aa',
            login: this.loginWithFeishu.bind(this),
            logout: this.logoutFeishu.bind(this)
        });
        
        console.log('✅ 飞书认证初始化完成');
    }

    async loginWithFeishu() {
        return new Promise((resolve, reject) => {
            try {
                this.authState.isAuthenticating = true;
                this.authState.currentProvider = 'feishu';
                
                const authUrl = this.buildFeishuAuthUrl();
                this.openAuthPopup(authUrl, 'feishu', resolve, reject);
            } catch (error) {
                this.authState.isAuthenticating = false;
                reject(error);
            }
        });
    }

    buildFeishuAuthUrl() {
        const params = new URLSearchParams({
            app_id: this.config.feishu?.appId || '',
            redirect_uri: encodeURIComponent(window.location.origin + '/auth/feishu/callback'),
            response_type: 'code',
            state: this.generateState()
        });
        
        return `https://passport.feishu.cn/suite/passport/oauth/authorize?${params.toString()}`;
    }

    logoutFeishu() {
        // 飞书登出处理
    }

    // ==================== 生物识别认证 ====================

    async initBiometricAuth() {
        try {
            if (window.PublicKeyCredential) {
                this.providers.set('biometric', {
                    type: 'biometric',
                    name: '生物识别',
                    icon: 'fas fa-fingerprint',
                    color: '#6366f1',
                    login: this.loginWithBiometric.bind(this),
                    register: this.registerBiometric.bind(this),
                    logout: this.logoutBiometric.bind(this)
                });
                
                console.log('✅ 生物识别认证初始化完成');
            } else {
                console.warn('⚠️ 浏览器不支持生物识别认证');
            }
        } catch (error) {
            console.error('❌ 生物识别认证初始化失败:', error);
        }
    }

    async loginWithBiometric() {
        try {
            this.authState.isAuthenticating = true;
            this.authState.currentProvider = 'biometric';
            
            // 检查是否已注册生物识别
            const hasCredentials = await this.hasBiometricCredentials();
            if (!hasCredentials) {
                throw new Error('未找到生物识别凭据，请先注册');
            }
            
            // 创建认证选项
            const options = {
                publicKey: {
                    challenge: new Uint8Array(32),
                    timeout: this.config.biometric.timeout,
                    rpId: window.location.hostname,
                    userVerification: 'required'
                }
            };
            
            // 填充随机挑战
            crypto.getRandomValues(options.publicKey.challenge);
            
            // 执行认证
            const credential = await navigator.credentials.get(options);
            
            const authResult = {
                provider: 'biometric',
                credentialId: credential.id,
                response: {
                    authenticatorData: Array.from(new Uint8Array(credential.response.authenticatorData)),
                    clientDataJSON: Array.from(new Uint8Array(credential.response.clientDataJSON)),
                    signature: Array.from(new Uint8Array(credential.response.signature))
                },
                timestamp: Date.now()
            };
            
            this.authState.isAuthenticating = false;
            return authResult;
            
        } catch (error) {
            this.authState.isAuthenticating = false;
            
            if (error.name === 'NotAllowedError') {
                throw new Error('生物识别认证被取消');
            } else if (error.name === 'InvalidStateError') {
                throw new Error('生物识别设备不可用');
            } else {
                throw new Error('生物识别认证失败: ' + error.message);
            }
        }
    }

    async registerBiometric(userId, userName) {
        try {
            // 创建注册选项
            const options = {
                publicKey: {
                    challenge: new Uint8Array(32),
                    rp: {
                        name: 'AlingAi Pro',
                        id: window.location.hostname
                    },
                    user: {
                        id: new TextEncoder().encode(userId),
                        name: userName,
                        displayName: userName
                    },
                    pubKeyCredParams: [
                        { alg: -7, type: 'public-key' },  // ES256
                        { alg: -257, type: 'public-key' } // RS256
                    ],
                    authenticatorSelection: {
                        authenticatorAttachment: 'platform',
                        userVerification: 'required',
                        requireResidentKey: false
                    },
                    timeout: this.config.biometric.timeout,
                    attestation: 'direct'
                }
            };
            
            // 填充随机挑战
            crypto.getRandomValues(options.publicKey.challenge);
            
            // 执行注册
            const credential = await navigator.credentials.create(options);
            
            // 保存凭据信息
            const credentialData = {
                id: credential.id,
                publicKey: Array.from(new Uint8Array(credential.response.getPublicKey())),
                userId: userId,
                timestamp: Date.now()
            };
            
            localStorage.setItem('biometricCredential', JSON.stringify(credentialData));
            
            return {
                success: true,
                credentialId: credential.id
            };
            
        } catch (error) {
            if (error.name === 'NotSupportedError') {
                throw new Error('此设备不支持生物识别');
            } else if (error.name === 'NotAllowedError') {
                throw new Error('生物识别注册被取消');
            } else {
                throw new Error('生物识别注册失败: ' + error.message);
            }
        }
    }

    async hasBiometricCredentials() {
        try {
            const stored = localStorage.getItem('biometricCredential');
            return !!stored;
        } catch (error) {
            return false;
        }
    }

    logoutBiometric() {
        // 生物识别登出处理（可选择清除凭据）
    }

    // ==================== LDAP认证 ====================

    async initLDAPAuth() {
        this.providers.set('ldap', {
            type: 'enterprise',
            name: 'LDAP',
            icon: 'fas fa-network-wired',
            color: '#4f46e5',
            login: this.loginWithLDAP.bind(this),
            logout: this.logoutLDAP.bind(this)
        });
        
        console.log('✅ LDAP认证初始化完成');
    }

    async loginWithLDAP(credentials) {
        try {
            this.authState.isAuthenticating = true;
            this.authState.currentProvider = 'ldap';
            
            // LDAP认证需要后端处理
            const response = await fetch('/api/auth/ldap', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: credentials.username,
                    password: credentials.password,
                    domain: credentials.domain || ''
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.authState.isAuthenticating = false;
                return {
                    provider: 'ldap',
                    ...result.data
                };
            } else {
                throw new Error(result.error || 'LDAP认证失败');
            }
            
        } catch (error) {
            this.authState.isAuthenticating = false;
            throw error;
        }
    }

    logoutLDAP() {
        // LDAP登出处理
    }

    // ==================== SAML认证 ====================

    async initSAMLAuth() {
        this.providers.set('saml', {
            type: 'enterprise',
            name: 'SAML SSO',
            icon: 'fas fa-key',
            color: '#059669',
            login: this.loginWithSAML.bind(this),
            logout: this.logoutSAML.bind(this)
        });
        
        console.log('✅ SAML认证初始化完成');
    }

    async loginWithSAML() {
        try {
            this.authState.isAuthenticating = true;
            this.authState.currentProvider = 'saml';
            
            // 重定向到SAML IdP
            const samlUrl = '/api/auth/saml/login';
            window.location.href = samlUrl;
            
        } catch (error) {
            this.authState.isAuthenticating = false;
            throw error;
        }
    }

    logoutSAML() {
        // SAML SLO (Single Logout)
        window.location.href = '/api/auth/saml/logout';
    }

    // ==================== 通用认证方法 ====================

    async login(provider, credentials = null) {
        try {
            if (!this.providers.has(provider)) {
                throw new Error(`不支持的登录方式: ${provider}`);
            }
            
            const providerConfig = this.providers.get(provider);
            console.log(`🔑 开始${providerConfig.name}登录...`);
            
            let result;
            if (provider === 'ldap' || provider === 'saml') {
                result = await providerConfig.login(credentials);
            } else {
                result = await providerConfig.login();
            }
            
            this.authState.lastProvider = provider;
            this.saveAuthState();
            
            console.log(`✅ ${providerConfig.name}登录成功`);
            return result;
            
        } catch (error) {
            console.error(`❌ ${provider}登录失败:`, error);
            throw error;
        }
    }

    logout(provider = null) {
        const targetProvider = provider || this.authState.lastProvider;
        
        if (targetProvider && this.providers.has(targetProvider)) {
            const providerConfig = this.providers.get(targetProvider);
            if (providerConfig.logout) {
                providerConfig.logout();
            }
        }
        
        this.authState.currentProvider = null;
        this.authState.lastProvider = null;
        this.saveAuthState();
    }

    // ==================== 通用工具方法 ====================

    generateState() {
        return 'state_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    async loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    openAuthPopup(url, provider, resolve, reject) {
        const popup = window.open(
            url,
            `${provider}_auth`,
            'width=500,height=600,scrollbars=yes,resizable=yes,status=yes'
        );
        
        if (!popup) {
            reject(new Error('弹窗被阻止，请允许弹窗后重试'));
            return;
        }
        
        // 监听弹窗关闭
        const checkClosed = setInterval(() => {
            if (popup.closed) {
                clearInterval(checkClosed);
                if (this.authState.isAuthenticating) {
                    this.authState.isAuthenticating = false;
                    reject(new Error('登录被取消'));
                }
            }
        }, 1000);
        
        // 存储回调函数
        this.authCallbacks = { resolve, reject, checkClosed };
    }

    handleOAuthCallback(event) {
        if (event.origin !== window.location.origin) return;
        
        const { provider, result, error } = event.data;
        
        if (this.authCallbacks) {
            clearInterval(this.authCallbacks.checkClosed);
            
            if (error) {
                this.authCallbacks.reject(new Error(error));
            } else {
                this.authCallbacks.resolve(result);
            }
            
            this.authCallbacks = null;
        }
        
        this.authState.isAuthenticating = false;
    }

    createAuthModal(id, title) {
        // 创建模态框
        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="modal-header border-b p-4">
                    <h3 class="text-lg font-semibold">${title}</h3>
                    <button class="close-modal absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- 内容将在这里填充 -->
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // 绑定关闭事件
        modal.querySelector('.close-modal').addEventListener('click', () => {
            this.closeAuthModal();
        });
        
        return modal;
    }

    closeAuthModal() {
        const modals = document.querySelectorAll('[id$="-modal"], [id$="-qr"]');
        modals.forEach(modal => modal.remove());
    }

    // ==================== 状态管理 ====================

    loadAuthState() {
        try {
            const stored = localStorage.getItem('multiAuthState');
            if (stored) {
                this.authState = { ...this.authState, ...JSON.parse(stored) };
            }
        } catch (error) {
            console.warn('加载认证状态失败:', error);
        }
    }

    saveAuthState() {
        try {
            localStorage.setItem('multiAuthState', JSON.stringify(this.authState));
        } catch (error) {
            console.warn('保存认证状态失败:', error);
        }
    }

    // ==================== 公共接口 ====================

    getAvailableProviders() {
        return Array.from(this.providers.entries()).map(([key, config]) => ({
            key,
            name: config.name,
            type: config.type,
            icon: config.icon,
            color: config.color
        }));
    }

    getProvidersByType(type) {
        return this.getAvailableProviders().filter(provider => provider.type === type);
    }

    isProviderAvailable(provider) {
        return this.providers.has(provider);
    }

    getCurrentProvider() {
        return this.authState.currentProvider;
    }

    getLastProvider() {
        return this.authState.lastProvider;
    }

    isAuthenticating() {
        return this.authState.isAuthenticating;
    }

    // 创建登录按钮
    createLoginButton(provider, container) {
        if (!this.providers.has(provider)) {
            console.error('不支持的登录方式:', provider);
            return null;
        }
        
        const config = this.providers.get(provider);
        const button = document.createElement('button');
        
        button.className = 'auth-provider-btn w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors';
        button.style.borderColor = config.color;
        button.innerHTML = `
            <i class="${config.icon} mr-2" style="color: ${config.color}"></i>
            使用${config.name}登录
        `;
        
        button.addEventListener('click', async () => {
            try {
                button.disabled = true;
                button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>登录中...`;
                
                const result = await this.login(provider);
                
                // 触发登录成功事件
                window.dispatchEvent(new CustomEvent('authSuccess', {
                    detail: { provider, result }
                }));
                
            } catch (error) {
                console.error('登录失败:', error);
                
                // 触发登录失败事件
                window.dispatchEvent(new CustomEvent('authError', {
                    detail: { provider, error: error.message }
                }));
                
            } finally {
                button.disabled = false;
                button.innerHTML = `
                    <i class="${config.icon} mr-2" style="color: ${config.color}"></i>
                    使用${config.name}登录
                `;
            }
        });
        
        if (container) {
            container.appendChild(button);
        }
        
        return button;
    }

    // ==================== 清理方法 ====================

    destroy() {
        console.log('🧹 清理多种登录方式系统...');
        
        // 清理所有provider的登出
        this.providers.forEach((config, provider) => {
            if (config.logout) {
                config.logout();
            }
        });
        
        this.saveAuthState();
    }
}

// 全局实例
window.MultiAuthSystem = MultiAuthSystem;

console.log('🔑 多种登录方式系统模块已加载');
