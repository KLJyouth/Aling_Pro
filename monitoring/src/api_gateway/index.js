/**
 * API网关模块 - 处理API请求的路由和监控
 */

const express = require('express');
const http = require('http');
const axios = require('axios');
const bodyParser = require('body-parser');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const rateLimit = require('express-rate-limit');
const { v4: uuidv4 } = require('uuid');
const ProxyManager = require('./proxy_manager');
const RouteManager = require('./route_manager');
const AuthManager = require('./auth_manager');

class ApiGateway {
  constructor(config, metricsCollector, logger) {
    this.config = config;
    this.metricsCollector = metricsCollector;
    this.logger = logger;
    this.app = express();
    this.server = null;
    this.proxyManager = new ProxyManager(config, logger);
    this.routeManager = new RouteManager(config, logger);
    this.authManager = new AuthManager(config, logger);
  }

  /**
   * 初始化API网关
   */
  async init() {
    this.logger.info('初始化API网关...');
    
    // 配置中间件
    this.app.use(helmet());
    this.app.use(compression());
    this.app.use(cors());
    this.app.use(bodyParser.json({ limit: '10mb' }));
    this.app.use(bodyParser.urlencoded({ extended: true, limit: '10mb' }));
    
    // 请求唯一标识中间件
    this.app.use((req, res, next) => {
      req.id = uuidv4();
      next();
    });
    
    // 请求日志中间件
    this.app.use((req, res, next) => {
      const startTime = Date.now();
      
      // 记录请求完成后的数据
      res.on('finish', () => {
        const duration = Date.now() - startTime;
        this.logger.info(`API请求 ${req.method} ${req.originalUrl} - ${res.statusCode} - ${duration}ms`);
        
        // 收集指标
        this.metricsCollector.recordApiCall({
          requestId: req.id,
          method: req.method,
          url: req.originalUrl,
          statusCode: res.statusCode,
          duration: duration,
          timestamp: new Date()
        });
      });
      
      next();
    });
    
    // 限流中间件（如果启用）
    if (this.config.throttling_enabled) {
      const limiter = rateLimit({
        windowMs: 60 * 1000, // 1分钟
        max: this.config.max_requests_per_second || 100,
        standardHeaders: true,
        message: { error: '请求频率过高，请稍后再试' }
      });
      this.app.use(limiter);
    }

    // 初始化路由
    await this.routeManager.init();
    
    // 初始化身份验证
    await this.authManager.init();
    
    // 注册路由
    this._registerRoutes();

    this.logger.info('API网关初始化完成');
  }

  /**
   * 注册API路由
   */
  _registerRoutes() {
    // 健康检查端点
    this.app.get('/health', (req, res) => {
      res.status(200).json({ status: 'ok' });
    });

    // 代理端点 - 处理所有API请求
    if (this.config.proxy_mode) {
      this.app.use('*', async (req, res) => {
        try {
          const startTime = Date.now();
          
          // 验证请求身份
          const authResult = await this.authManager.validateRequest(req);
          if (!authResult.valid) {
            return res.status(401).json({ error: authResult.message });
          }
          
          // 获取目标路由
          const route = this.routeManager.getRouteForRequest(req);
          if (!route) {
            return res.status(404).json({ error: '未找到API路由' });
          }
          
          // 代理请求
          const proxyResult = await this.proxyManager.proxyRequest(req, route);
          
          // 发送响应
          res.status(proxyResult.status || 500).set(proxyResult.headers || {}).send(proxyResult.data);
          
          // 记录指标
          const duration = Date.now() - startTime;
          this.metricsCollector.recordApiCall({
            requestId: req.id,
            method: req.method,
            url: req.originalUrl,
            targetUrl: route.target,
            statusCode: proxyResult.status,
            duration: duration,
            timestamp: new Date(),
            apiType: route.type
          });
        } catch (error) {
          this.logger.error('代理请求失败:', error);
          res.status(500).json({ error: '代理请求失败' });
        }
      });
    }
  }

  /**
   * 启动API网关服务器
   */
  start() {
    this.server = this.app.listen(this.config.port, () => {
      this.logger.info(`API网关已启动，监听端口 ${this.config.port}`);
    });
  }

  /**
   * 停止API网关服务器
   */
  async stop() {
    if (this.server) {
      return new Promise((resolve) => {
        this.server.close(() => {
          this.logger.info('API网关已关闭');
          resolve();
        });
      });
    }
    return Promise.resolve();
  }
}

module.exports = ApiGateway; 