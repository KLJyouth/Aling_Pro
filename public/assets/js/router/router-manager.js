/**
 * AlingAi Pro - 路由管理器
 * 现代化的单页应用路由系统，支持历史管理、动态加载和路由守卫
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @features
 * - 基于History API的路由
 * - 动态路由参数
 * - 路由守卫
 * - 懒加载组件
 * - 路由缓存
 * - 面包屑导航
 */

class AlingRouterManager {
    constructor(options = {}) {
        this.options = {
            mode: 'history', // history 或 hash
            base: '/',
            linkSelector: 'a[data-router-link]',
            containerSelector: '#app-content',
            loadingSelector: '#loading',
            errorSelector: '#error',
            enableCache: true,
            cacheSize: 10,
            transitionDuration: 300,
            scrollToTop: true,
            ...options
        };

        this.routes = new Map();
        this.guards = new Map();
        this.cache = new Map();
        this.history = [];
        this.currentRoute = null;
        this.isNavigating = false;
        this.components = new Map();
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.bindLinks();
        this.handleInitialRoute();
        
        
    }

    /**
     * 注册路由
     * @param {string} path - 路由路径
     * @param {object} config - 路由配置
     */
    addRoute(path, config) {
        if (typeof config === 'function') {
            config = { component: config };
        }

        const route = {
            path: this.normalizePath(path),
            component: config.component,
            title: config.title,
            meta: config.meta || {},
            beforeEnter: config.beforeEnter,
            children: config.children || [],
            params: {},
            query: {},
            ...config
        };

        // 处理动态路由参数
        route.paramNames = this.extractParamNames(path);
        route.regex = this.pathToRegex(path);

        this.routes.set(path, route);

        // 注册子路由
        if (config.children && config.children.length > 0) {
            config.children.forEach(child => {
                const childPath = this.joinPaths(path, child.path);
                this.addRoute(childPath, {
                    ...child,
                    parent: route
                });
            });
        }

        return this;
    }

    /**
     * 批量注册路由
     * @param {array} routes - 路由配置数组
     */
    addRoutes(routes) {
        routes.forEach(route => {
            this.addRoute(route.path, route);
        });
        return this;
    }

    /**
     * 注册路由守卫
     * @param {string} type - 守卫类型 (beforeEach, afterEach, beforeResolve)
     * @param {function} guard - 守卫函数
     */
    addGuard(type, guard) {
        if (!this.guards.has(type)) {
            this.guards.set(type, []);
        }
        this.guards.get(type).push(guard);
        return this;
    }

    /**
     * 路径转正则表达式
     */
    pathToRegex(path) {
        const paramRegex = /:([^\/]+)/g;
        const regexStr = path.replace(paramRegex, '([^/]+)');
        return new RegExp(`^${regexStr}$`);
    }

    /**
     * 提取路径参数名
     */
    extractParamNames(path) {
        const paramRegex = /:([^\/]+)/g;
        const params = [];
        let match;
        
        while ((match = paramRegex.exec(path)) !== null) {
            params.push(match[1]);
        }
        
        return params;
    }

    /**
     * 规范化路径
     */
    normalizePath(path) {
        if (!path.startsWith('/')) {
            path = '/' + path;
        }
        return path.replace(/\/+/g, '/').replace(/\/$/, '') || '/';
    }

    /**
     * 连接路径
     */
    joinPaths(parent, child) {
        return this.normalizePath(parent + '/' + child);
    }

    /**
     * 解析当前URL
     */
    parseCurrentPath() {
        let path, query;
        
        if (this.options.mode === 'hash') {
            const hash = window.location.hash.slice(1);
            [path, query] = hash.split('?');
        } else {
            path = window.location.pathname;
            query = window.location.search.slice(1);
        }

        return {
            path: this.normalizePath(path),
            query: this.parseQuery(query || ''),
            fullPath: path + (query ? '?' + query : '')
        };
    }

    /**
     * 解析查询参数
     */
    parseQuery(queryString) {
        const query = {};
        if (!queryString) return query;

        queryString.split('&').forEach(param => {
            const [key, value] = param.split('=');
            if (key) {
                query[decodeURIComponent(key)] = decodeURIComponent(value || '');
            }
        });

        return query;
    }

    /**
     * 查询字符串转对象
     */
    stringifyQuery(query) {
        const params = [];
        for (const [key, value] of Object.entries(query)) {
            if (value !== null && value !== undefined && value !== '') {
                params.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
            }
        }
        return params.join('&');
    }

    /**
     * 匹配路由
     */
    matchRoute(path) {
        for (const [routePath, route] of this.routes) {
            const match = path.match(route.regex);
            if (match) {
                // 提取参数
                const params = {};
                route.paramNames.forEach((name, index) => {
                    params[name] = match[index + 1];
                });

                return {
                    ...route,
                    params,
                    matched: match
                };
            }
        }
        return null;
    }

    /**
     * 设置事件监听器
     */
    setupEventListeners() {
        // 监听浏览器前进后退
        window.addEventListener('popstate', (e) => {
            this.handleRouteChange();
        });

        // 监听链接点击
        document.addEventListener('click', (e) => {
            this.handleLinkClick(e);
        });
    }

    /**
     * 绑定路由链接
     */
    bindLinks() {
        document.querySelectorAll(this.options.linkSelector).forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const to = link.getAttribute('href') || link.getAttribute('data-to');
                if (to) {
                    this.push(to);
                }
            });
        });
    }

    /**
     * 处理链接点击
     */
    handleLinkClick(e) {
        const link = e.target.closest('a');
        if (!link) return;

        // 检查是否为路由链接
        if (link.hasAttribute('data-router-link') || 
            link.getAttribute('href')?.startsWith('/')) {
            
            e.preventDefault();
            const to = link.getAttribute('href');
            if (to && !link.hasAttribute('target')) {
                this.push(to);
            }
        }
    }

    /**
     * 处理初始路由
     */
    handleInitialRoute() {
        this.handleRouteChange();
    }

    /**
     * 处理路由变化
     */
    async handleRouteChange() {
        if (this.isNavigating) return;

        const { path, query, fullPath } = this.parseCurrentPath();
        const route = this.matchRoute(path);

        if (!route) {
            this.handleNotFound(path);
            return;
        }

        // 构建新路由对象
        const newRoute = {
            ...route,
            path,
            fullPath,
            query,
            hash: window.location.hash
        };

        try {
            // 执行beforeEach守卫
            const canNavigate = await this.executeGuards('beforeEach', newRoute, this.currentRoute);
            if (!canNavigate) return;

            // 执行路由级beforeEnter守卫
            if (route.beforeEnter) {
                const canEnter = await route.beforeEnter(newRoute, this.currentRoute);
                if (!canEnter) return;
            }

            // 执行beforeResolve守卫
            await this.executeGuards('beforeResolve', newRoute, this.currentRoute);

            // 渲染组件
            await this.renderRoute(newRoute);

            // 更新当前路由
            const oldRoute = this.currentRoute;
            this.currentRoute = newRoute;

            // 添加到历史记录
            this.history.push(newRoute);

            // 执行afterEach守卫
            await this.executeGuards('afterEach', newRoute, oldRoute);

            // 更新页面标题
            if (route.title) {
                document.title = route.title;
            }

            // 滚动到顶部
            if (this.options.scrollToTop) {
                window.scrollTo(0, 0);
            }

            // 触发路由变化事件
            this.emit('route:changed', { to: newRoute, from: oldRoute });

        } catch (error) {
            console.error('路由导航错误:', error);
            this.handleError(error);
        }
    }

    /**
     * 执行路由守卫
     */
    async executeGuards(type, to, from) {
        const guards = this.guards.get(type) || [];
        
        for (const guard of guards) {
            try {
                const result = await guard(to, from, (next) => {
                    if (next === false) return false;
                    if (typeof next === 'string') {
                        this.push(next);
                        return false;
                    }
                    return true;
                });
                
                if (result === false) return false;
            } catch (error) {
                console.error(`路由守卫 ${type} 执行错误:`, error);
                return false;
            }
        }
        
        return true;
    }

    /**
     * 渲染路由组件
     */
    async renderRoute(route) {
        this.isNavigating = true;
        
        try {
            // 显示加载状态
            this.showLoading();

            // 获取组件
            let component = route.component;
            
            // 如果是函数，执行懒加载
            if (typeof component === 'function') {
                component = await component();
            }

            // 如果是字符串，从缓存或网络加载
            if (typeof component === 'string') {
                component = await this.loadComponent(component, route);
            }

            // 渲染组件
            await this.renderComponent(component, route);

            this.hideLoading();

        } catch (error) {
            this.hideLoading();
            throw error;
        } finally {
            this.isNavigating = false;
        }
    }

    /**
     * 加载组件
     */
    async loadComponent(componentPath, route) {
        // 检查缓存
        if (this.options.enableCache && this.cache.has(componentPath)) {
            return this.cache.get(componentPath);
        }

        try {
            let component;
            
            // 如果是URL，加载远程组件
            if (componentPath.startsWith('http') || componentPath.endsWith('.js')) {
                const module = await import(componentPath);
                component = module.default || module;
            } 
            // 如果是HTML内容
            else if (componentPath.includes('<')) {
                component = componentPath;
            }
            // 否则尝试从组件注册表获取
            else {
                component = this.components.get(componentPath);
                if (!component) {
                    throw new Error(`Component not found: ${componentPath}`);
                }
            }

            // 缓存组件
            if (this.options.enableCache) {
                this.setCache(componentPath, component);
            }

            return component;

        } catch (error) {
            console.error('组件加载失败:', error);
            throw error;
        }
    }

    /**
     * 渲染组件到容器
     */
    async renderComponent(component, route) {
        const container = document.querySelector(this.options.containerSelector);
        if (!container) {
            throw new Error('路由容器未找到');
        }

        // 添加路由过渡效果
        container.style.opacity = '0';
        
        setTimeout(async () => {
            try {
                if (typeof component === 'function') {
                    // 执行组件函数
                    await component(container, route);
                } else if (typeof component === 'string') {
                    // 直接设置HTML内容
                    container.innerHTML = component;
                } else if (component && component.render) {
                    // 调用组件的render方法
                    await component.render(container, route);
                } else {
                    throw new Error('无效的组件类型');
                }

                // 重新绑定路由链接
                this.bindLinks();

                // 恢复透明度
                container.style.opacity = '1';

            } catch (error) {
                container.style.opacity = '1';
                throw error;
            }
        }, this.options.transitionDuration);
    }

    /**
     * 注册组件
     */
    registerComponent(name, component) {
        this.components.set(name, component);
        return this;
    }

    /**
     * 设置缓存
     */
    setCache(key, value) {
        if (this.cache.size >= this.options.cacheSize) {
            const firstKey = this.cache.keys().next().value;
            this.cache.delete(firstKey);
        }
        this.cache.set(key, value);
    }

    /**
     * 显示加载状态
     */
    showLoading() {
        const loading = document.querySelector(this.options.loadingSelector);
        if (loading) {
            loading.style.display = 'block';
        }
    }

    /**
     * 隐藏加载状态
     */
    hideLoading() {
        const loading = document.querySelector(this.options.loadingSelector);
        if (loading) {
            loading.style.display = 'none';
        }
    }

    /**
     * 处理404错误
     */
    handleNotFound(path) {
        const notFoundRoute = this.routes.get('/404') || this.routes.get('*');
        if (notFoundRoute) {
            this.renderRoute({
                ...notFoundRoute,
                path,
                params: { path },
                query: {}
            });
        } else {
            this.handleError(new Error(`页面未找到: ${path}`));
        }
    }

    /**
     * 处理错误
     */
    handleError(error) {
        console.error('路由错误:', error);
        
        const errorContainer = document.querySelector(this.options.errorSelector);
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="error-message">
                    <h3>页面加载失败</h3>
                    <p>${error.message}</p>
                    <button onclick="location.reload()">重新加载</button>
                </div>
            `;
            errorContainer.style.display = 'block';
        }

        this.emit('route:error', { error });
    }

    /**
     * 编程式导航 - push
     */
    push(path, query = {}) {
        const queryString = this.stringifyQuery(query);
        const fullPath = path + (queryString ? '?' + queryString : '');
        
        if (this.options.mode === 'hash') {
            window.location.hash = fullPath;
        } else {
            window.history.pushState(null, '', this.options.base + fullPath);
            this.handleRouteChange();
        }
    }

    /**
     * 编程式导航 - replace
     */
    replace(path, query = {}) {
        const queryString = this.stringifyQuery(query);
        const fullPath = path + (queryString ? '?' + queryString : '');
        
        if (this.options.mode === 'hash') {
            window.location.replace(window.location.href.split('#')[0] + '#' + fullPath);
        } else {
            window.history.replaceState(null, '', this.options.base + fullPath);
            this.handleRouteChange();
        }
    }

    /**
     * 后退
     */
    back() {
        window.history.back();
    }

    /**
     * 前进
     */
    forward() {
        window.history.forward();
    }

    /**
     * 跳转指定步数
     */
    go(n) {
        window.history.go(n);
    }

    /**
     * 获取当前路由信息
     */
    getCurrentRoute() {
        return this.currentRoute;
    }

    /**
     * 生成路由路径
     */
    generatePath(name, params = {}, query = {}) {
        const route = Array.from(this.routes.values()).find(r => r.name === name);
        if (!route) {
            throw new Error(`路由 ${name} 不存在`);
        }

        let path = route.path;
        
        // 替换参数
        route.paramNames.forEach(param => {
            if (params[param]) {
                path = path.replace(`:${param}`, params[param]);
            }
        });

        // 添加查询参数
        const queryString = this.stringifyQuery(query);
        return path + (queryString ? '?' + queryString : '');
    }

    /**
     * 获取路由历史
     */
    getHistory() {
        return [...this.history];
    }

    /**
     * 清除缓存
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * 事件发射器
     */
    emit(eventName, data) {
        const event = new CustomEvent(eventName, { detail: data });
        document.dispatchEvent(event);
    }

    /**
     * 销毁路由器
     */
    destroy() {
        // 移除事件监听器
        window.removeEventListener('popstate', this.handleRouteChange);
        document.removeEventListener('click', this.handleLinkClick);
        
        // 清除缓存和数据
        this.routes.clear();
        this.guards.clear();
        this.cache.clear();
        this.components.clear();
        this.history = [];
        this.currentRoute = null;
    }
}

// 创建路由工具函数
class RouterUtils {
    static createBreadcrumb(route, routes) {
        const breadcrumb = [];
        let current = route;
        
        while (current) {
            breadcrumb.unshift({
                name: current.meta?.title || current.title,
                path: current.path,
                route: current
            });
            current = current.parent;
        }
        
        return breadcrumb;
    }
    
    static isActiveRoute(currentPath, targetPath, exact = false) {
        if (exact) {
            return currentPath === targetPath;
        }
        return currentPath.startsWith(targetPath);
    }
    
    static parseRouteConfig(config) {
        // 解析路由配置的辅助函数
        const routes = [];
        
        config.forEach(item => {
            const route = {
                path: item.path,
                component: item.component,
                title: item.title,
                meta: item.meta || {},
                children: item.children || []
            };
            
            routes.push(route);
        });
        
        return routes;
    }
}

// 导出
window.AlingRouterManager = AlingRouterManager;
window.RouterUtils = RouterUtils;

// 创建全局路由实例
window.router = new AlingRouterManager();


