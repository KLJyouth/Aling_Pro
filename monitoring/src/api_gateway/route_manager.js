/**
 * 路由管理器 - 管理API路由规则
 */

const fs = require('fs');
const path = require('path');

class RouteManager {
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.routes = [];
    this.apiEndpointsConfig = null;
  }

  /**
   * 初始化路由管理器
   */
  async init() {
    try {
      this.logger.info('初始化路由管理器...');
      
      // 加载API端点配置
      await this._loadApiEndpointsConfig();
      
      // 创建路由规则
      this._createRouteRules();
      
      this.logger.info(`路由管理器初始化完成，已加载 ${this.routes.length} 个路由规则`);
    } catch (error) {
      this.logger.error('路由管理器初始化失败:', error);
      throw error;
    }
  }

  /**
   * 加载API端点配置
   * @private
   */
  async _loadApiEndpointsConfig() {
    try {
      // 尝试从主配置中获取API端点配置
      const configPath = path.join(__dirname, '../../config/config.json');
      const configData = await fs.promises.readFile(configPath, 'utf8');
      const config = JSON.parse(configData);
      
      if (config.api_endpoints) {
        this.apiEndpointsConfig = config.api_endpoints;
        this.logger.info('已从主配置文件加载API端点配置');
      } else {
        // 尝试从单独的文件加载
        const apiConfigPath = path.join(__dirname, '../../config/api_endpoints.json');
        try {
          const apiConfigData = await fs.promises.readFile(apiConfigPath, 'utf8');
          this.apiEndpointsConfig = JSON.parse(apiConfigData);
          this.logger.info('已从单独的配置文件加载API端点配置');
        } catch (error) {
          this.logger.warn('未找到API端点配置文件，将使用默认配置');
          this.apiEndpointsConfig = { monitored_apis: [] };
        }
      }
    } catch (error) {
      this.logger.error('加载API端点配置失败:', error);
      throw error;
    }
  }

  /**
   * 创建路由规则
   * @private
   */
  _createRouteRules() {
    if (!this.apiEndpointsConfig || !this.apiEndpointsConfig.monitored_apis) {
      this.logger.warn('未找到监控API配置，不创建路由规则');
      return;
    }
    
    for (const api of this.apiEndpointsConfig.monitored_apis) {
      // 处理基础URL
      const baseUrl = api.base_url.endsWith('/') 
        ? api.base_url.slice(0, -1) 
        : api.base_url;
      
      // 添加各个端点的路由规则
      if (api.endpoints && Array.isArray(api.endpoints)) {
        for (const endpoint of api.endpoints) {
          const path = endpoint.path.startsWith('/') 
            ? endpoint.path 
            : `/${endpoint.path}`;
          
          // 构建路由对象
          const route = {
            id: `${api.name}-${endpoint.method}-${path}`,
            type: api.type,
            name: api.name,
            method: endpoint.method,
            path: path,
            target: `${baseUrl}${path}`,
            timeout: endpoint.timeout_ms || this.config.request_timeout,
            sla: endpoint.sla_ms,
            expected_status: endpoint.expected_status,
            auth: api.auth
          };
          
          this.routes.push(route);
          this.logger.debug(`已添加路由: ${route.method} ${route.path} -> ${route.target}`);
        }
      }
    }
  }

  /**
   * 获取与请求匹配的路由
   * @param {Object} req - Express请求对象
   * @returns {Object|null} 匹配的路由对象，如果没有匹配则返回null
   */
  getRouteForRequest(req) {
    // 简单的路由匹配逻辑
    // 实际生产环境可能需要更复杂的路由匹配算法
    
    const method = req.method;
    const path = req.path;
    
    // 尝试精确匹配
    let matchedRoute = this.routes.find(route => 
      route.method === method && route.path === path
    );
    
    if (matchedRoute) {
      return matchedRoute;
    }
    
    // 尝试路径参数匹配（简单实现）
    // 例如，将 /users/123 匹配到 /users/{id}
    for (const route of this.routes) {
      if (route.method !== method) continue;
      
      // 检查路径是否包含参数占位符
      if (route.path.includes('{') && route.path.includes('}')) {
        // 创建正则表达式，将 {param} 替换为 ([^/]+)
        const regexPattern = route.path
          .replace(/\//g, '\\/') // 转义斜杠
          .replace(/{[^}]+}/g, '([^/]+)'); // 替换参数为捕获组
        
        const regex = new RegExp(`^${regexPattern}$`);
        
        if (regex.test(path)) {
          // 深拷贝路由对象，避免修改原始对象
          matchedRoute = { ...route };
          
          // 提取参数值
          const paramMatches = path.match(regex);
          if (paramMatches && paramMatches.length > 1) {
            // 提取参数名称
            const paramNames = [];
            const paramRegex = /{([^}]+)}/g;
            let paramMatch;
            while ((paramMatch = paramRegex.exec(route.path)) !== null) {
              paramNames.push(paramMatch[1]);
            }
            
            // 创建参数对象
            const params = {};
            for (let i = 0; i < paramNames.length; i++) {
              params[paramNames[i]] = paramMatches[i + 1];
            }
            
            // 将参数添加到路由对象
            matchedRoute.params = params;
            
            // 替换目标URL中的参数
            let targetUrl = matchedRoute.target;
            for (const [name, value] of Object.entries(params)) {
              targetUrl = targetUrl.replace(`{${name}}`, value);
            }
            matchedRoute.target = targetUrl;
          }
          
          return matchedRoute;
        }
      }
    }
    
    // 如果没有匹配的路由，尝试使用通配符
    // 这里假设有一个默认路由
    const defaultRoute = this.routes.find(route => route.path === '*');
    if (defaultRoute) {
      return { ...defaultRoute, target: defaultRoute.target };
    }
    
    this.logger.warn(`未找到匹配的路由: ${method} ${path}`);
    return null;
  }

  /**
   * 添加新路由
   * @param {Object} route - 路由对象
   */
  addRoute(route) {
    this.routes.push(route);
    this.logger.info(`已添加新路由: ${route.method} ${route.path} -> ${route.target}`);
  }

  /**
   * 删除路由
   * @param {string} routeId - 路由ID
   * @returns {boolean} 是否成功删除
   */
  removeRoute(routeId) {
    const initialLength = this.routes.length;
    this.routes = this.routes.filter(route => route.id !== routeId);
    
    const wasRemoved = this.routes.length < initialLength;
    if (wasRemoved) {
      this.logger.info(`已删除路由: ${routeId}`);
    } else {
      this.logger.warn(`未找到要删除的路由: ${routeId}`);
    }
    
    return wasRemoved;
  }

  /**
   * 获取所有路由
   * @returns {Array} 路由数组
   */
  getAllRoutes() {
    return [...this.routes];
  }
}

module.exports = RouteManager; 