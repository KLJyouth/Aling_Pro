/**
 * 代理管理器 - 处理API请求的转发
 */

const axios = require('axios');
const qs = require('querystring');
const { URL } = require('url');

class ProxyManager {
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.httpClient = axios.create({
      timeout: config.request_timeout || 30000,
      maxContentLength: 10 * 1024 * 1024, // 10MB
      maxBodyLength: 10 * 1024 * 1024, // 10MB
      validateStatus: () => true // 返回所有状态码，不抛异常
    });
  }

  /**
   * 代理请求到目标服务器
   * @param {Object} req - Express请求对象
   * @param {Object} route - 路由配置对象
   * @returns {Promise<Object>} 响应对象
   */
  async proxyRequest(req, route) {
    try {
      const targetUrl = this._buildTargetUrl(req, route);
      const startTime = Date.now();
      
      this.logger.debug(`代理请求: ${req.method} ${req.originalUrl} -> ${targetUrl}`);
      
      const headers = this._buildRequestHeaders(req, route);
      const requestData = this._getRequestData(req);
      
      const response = await this.httpClient({
        method: req.method,
        url: targetUrl,
        headers: headers,
        data: requestData,
        params: req.query
      });
      
      const duration = Date.now() - startTime;
      this.logger.debug(`代理请求完成: ${req.method} ${targetUrl} - ${response.status} - ${duration}ms`);
      
      // 返回响应数据
      return {
        status: response.status,
        headers: this._filterResponseHeaders(response.headers),
        data: response.data
      };
    } catch (error) {
      this.logger.error(`代理请求失败: ${error.message}`, { 
        url: req.originalUrl,
        method: req.method,
        error: error.message,
        stack: error.stack
      });
      
      return {
        status: 502,
        headers: { 'Content-Type': 'application/json' },
        data: { error: '代理请求失败', message: error.message }
      };
    }
  }

  /**
   * 构建目标URL
   * @private
   */
  _buildTargetUrl(req, route) {
    // 构建基础URL
    let targetUrl = route.target;
    
    // 添加路径
    if (req.path && req.path !== '/') {
      // 确保不重复添加路径
      if (!targetUrl.endsWith('/') && !req.path.startsWith('/')) {
        targetUrl += '/';
      } else if (targetUrl.endsWith('/') && req.path.startsWith('/')) {
        targetUrl = targetUrl.slice(0, -1);
      }
      targetUrl += req.path;
    }
    
    // 添加查询参数
    if (req.query && Object.keys(req.query).length > 0) {
      const url = new URL(targetUrl);
      for (const [key, value] of Object.entries(req.query)) {
        url.searchParams.append(key, value);
      }
      targetUrl = url.toString();
    }
    
    return targetUrl;
  }

  /**
   * 构建请求头
   * @private
   */
  _buildRequestHeaders(req, route) {
    const headers = { ...req.headers };
    
    // 删除不需要转发的头部
    delete headers.host;
    delete headers.connection;
    
    // 添加转发相关头部
    headers['x-forwarded-for'] = req.ip;
    headers['x-forwarded-proto'] = req.protocol;
    headers['x-forwarded-host'] = req.get('host');
    
    // 添加跟踪ID
    headers['x-request-id'] = req.id;
    
    // 添加路由特定的头部
    if (route.headers) {
      Object.assign(headers, route.headers);
    }
    
    return headers;
  }

  /**
   * 获取请求数据
   * @private
   */
  _getRequestData(req) {
    if (req.method === 'GET' || req.method === 'HEAD') {
      return undefined;
    }
    
    // 根据Content-Type返回不同格式的数据
    const contentType = req.get('Content-Type') || '';
    
    if (contentType.includes('application/json')) {
      return req.body;
    } else if (contentType.includes('application/x-www-form-urlencoded')) {
      return qs.stringify(req.body);
    } else if (contentType.includes('multipart/form-data')) {
      // multipart/form-data需要特殊处理，这里仅返回原始数据
      return req.body;
    } else {
      // 默认返回原始数据
      return req.body;
    }
  }

  /**
   * 过滤响应头
   * @private
   */
  _filterResponseHeaders(headers) {
    const filteredHeaders = { ...headers };
    
    // 删除不需要的响应头
    delete filteredHeaders['transfer-encoding'];
    delete filteredHeaders['connection'];
    
    return filteredHeaders;
  }
}

module.exports = ProxyManager; 