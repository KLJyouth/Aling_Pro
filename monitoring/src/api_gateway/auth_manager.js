/**
 * 身份验证管理器 - 管理API请求的身份验证
 */

const jwt = require('jsonwebtoken');

class AuthManager {
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.authStrategies = {
      none: this._validateNone.bind(this),
      bearer: this._validateBearer.bind(this),
      api_key: this._validateApiKey.bind(this),
      basic: this._validateBasic.bind(this)
    };
  }

  /**
   * 初始化身份验证管理器
   */
  async init() {
    this.logger.info('初始化身份验证管理器...');
    // 可以在这里加载密钥、用户信息等
    this.logger.info('身份验证管理器初始化完成');
    return Promise.resolve();
  }

  /**
   * 验证请求
   * @param {Object} req - Express请求对象
   * @returns {Object} 验证结果 { valid: boolean, message: string }
   */
  async validateRequest(req) {
    try {
      // 获取目标路由的身份验证方式
      const route = req.route;
      
      // 如果没有路由信息，允许请求通过（这由路由管理器处理）
      if (!route || !route.auth) {
        return { valid: true };
      }
      
      const authType = route.auth.type;
      
      // 检查是否支持该身份验证类型
      if (!this.authStrategies[authType]) {
        this.logger.warn(`不支持的身份验证类型: ${authType}`);
        return { valid: false, message: '不支持的身份验证类型' };
      }
      
      // 执行身份验证
      return await this.authStrategies[authType](req, route.auth);
    } catch (error) {
      this.logger.error('验证请求时出错:', error);
      return { valid: false, message: '身份验证过程中发生错误' };
    }
  }

  /**
   * 无需验证
   * @private
   */
  async _validateNone() {
    return { valid: true };
  }

  /**
   * Bearer令牌验证
   * @private
   */
  async _validateBearer(req, authConfig) {
    // 获取Authorization头
    const authHeader = req.headers.authorization;
    if (!authHeader) {
      return { valid: false, message: '缺少Authorization头' };
    }
    
    // 检查格式
    if (!authHeader.startsWith('Bearer ')) {
      return { valid: false, message: 'Authorization头格式不正确' };
    }
    
    // 提取令牌
    const token = authHeader.substring(7); // 去掉"Bearer "前缀
    
    // 如果配置了令牌环境变量，检查是否匹配
    if (authConfig.token_env) {
      const expectedToken = process.env[authConfig.token_env];
      if (expectedToken && token === expectedToken) {
        return { valid: true };
      }
    }
    
    // 如果配置了JWT验证
    if (authConfig.jwt) {
      try {
        // 验证JWT
        const decoded = jwt.verify(token, authConfig.jwt.secret || process.env.JWT_SECRET);
        
        // 可以在这里添加额外的验证逻辑，例如检查角色、权限等
        
        return { valid: true, user: decoded };
      } catch (error) {
        this.logger.debug('JWT验证失败:', error.message);
        return { valid: false, message: 'JWT验证失败' };
      }
    }
    
    // 如果到达这里，说明没有正确配置验证方式
    // 在开发环境可以放行，生产环境应该拒绝
    if (process.env.NODE_ENV === 'development') {
      this.logger.warn('开发环境中允许未经验证的Bearer令牌');
      return { valid: true };
    }
    
    return { valid: false, message: '无效的Bearer令牌' };
  }

  /**
   * API密钥验证
   * @private
   */
  async _validateApiKey(req, authConfig) {
    // 获取API密钥
    const headerName = authConfig.header_name || 'X-API-Key';
    const apiKey = req.headers[headerName.toLowerCase()];
    
    if (!apiKey) {
      return { valid: false, message: `缺少 ${headerName} 头` };
    }
    
    // 如果配置了API密钥环境变量，检查是否匹配
    if (authConfig.key_env) {
      const expectedKey = process.env[authConfig.key_env];
      if (expectedKey && apiKey === expectedKey) {
        return { valid: true };
      }
    }
    
    // 如果配置了API密钥列表，检查是否在列表中
    if (authConfig.keys && Array.isArray(authConfig.keys)) {
      if (authConfig.keys.includes(apiKey)) {
        return { valid: true };
      }
    }
    
    // 开发环境中的特殊处理
    if (process.env.NODE_ENV === 'development' && apiKey === 'dev-api-key') {
      this.logger.warn('使用开发API密钥');
      return { valid: true };
    }
    
    return { valid: false, message: '无效的API密钥' };
  }

  /**
   * 基本身份验证
   * @private
   */
  async _validateBasic(req, authConfig) {
    // 获取Authorization头
    const authHeader = req.headers.authorization;
    if (!authHeader) {
      return { valid: false, message: '缺少Authorization头' };
    }
    
    // 检查格式
    if (!authHeader.startsWith('Basic ')) {
      return { valid: false, message: 'Authorization头格式不正确' };
    }
    
    try {
      // 解码Base64凭据
      const base64Credentials = authHeader.substring(6); // 去掉"Basic "前缀
      const credentials = Buffer.from(base64Credentials, 'base64').toString('utf8');
      const [username, password] = credentials.split(':');
      
      if (!username || !password) {
        return { valid: false, message: '凭据格式不正确' };
      }
      
      // 如果配置了用户名和密码环境变量，检查是否匹配
      if (authConfig.username_env && authConfig.password_env) {
        const expectedUsername = process.env[authConfig.username_env];
        const expectedPassword = process.env[authConfig.password_env];
        
        if (expectedUsername && expectedPassword && 
            username === expectedUsername && password === expectedPassword) {
          return { valid: true };
        }
      }
      
      // 如果配置了用户列表，检查用户是否存在
      if (authConfig.users && Array.isArray(authConfig.users)) {
        const user = authConfig.users.find(u => 
          u.username === username && u.password === password
        );
        
        if (user) {
          return { valid: true, user: { username: user.username } };
        }
      }
      
      // 开发环境中的特殊处理
      if (process.env.NODE_ENV === 'development' && 
          username === 'admin' && password === 'admin') {
        this.logger.warn('使用开发环境默认凭据');
        return { valid: true, user: { username: 'admin' } };
      }
      
      return { valid: false, message: '无效的用户名或密码' };
    } catch (error) {
      this.logger.error('解析Basic身份验证凭据时出错:', error);
      return { valid: false, message: '无效的Basic身份验证凭据' };
    }
  }
}

module.exports = AuthManager; 