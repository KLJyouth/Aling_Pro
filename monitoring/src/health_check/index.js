/**
 * 健康检查服务 - 定期检查API的可用性和响应时间
 */

const axios = require('axios');
const HealthCheckRunner = require('./health_check_runner');

class HealthCheckService {
  /**
   * 创建健康检查服务
   * @param {Object} config - 健康检查配置
   * @param {Object} metricsCollector - 指标收集器
   * @param {Object} alertManager - 告警管理器
   * @param {Object} logger - 日志记录器
   */
  constructor(config, metricsCollector, alertManager, logger) {
    this.config = config;
    this.metricsCollector = metricsCollector;
    this.alertManager = alertManager;
    this.logger = logger;
    
    // 健康检查端点列表
    this.endpoints = [];
    
    // 健康检查运行器
    this.runner = new HealthCheckRunner(logger);
    
    // 是否正在运行
    this.isRunning = false;
    
    // HTTP客户端
    this.httpClient = axios.create({
      timeout: config.default_timeout || 30000,
      maxContentLength: 10 * 1024 * 1024, // 10MB
      validateStatus: () => true // 返回所有状态码，不抛异常
    });
  }

  /**
   * 初始化健康检查服务
   * @returns {Promise<void>}
   */
  async init() {
    try {
      this.logger.info('初始化健康检查服务...');
      
      // 加载端点配置
      await this._loadEndpointsConfig();
      
      // 设置健康检查运行器
      this.runner.setEndpoints(this.endpoints);
      this.runner.setHttpClient(this.httpClient);
      this.runner.setMetricsCollector(this.metricsCollector);
      this.runner.setAlertManager(this.alertManager);
      
      this.logger.info(`健康检查服务初始化完成，已加载 ${this.endpoints.length} 个端点`);
    } catch (error) {
      this.logger.error('初始化健康检查服务时出错:', error);
      throw error;
    }
  }

  /**
   * 加载端点配置
   * @returns {Promise<void>}
   * @private
   */
  async _loadEndpointsConfig() {
    try {
      // 从配置中获取端点
      if (this.config.endpoints && Array.isArray(this.config.endpoints)) {
        this.endpoints = this.config.endpoints.map(endpoint => ({
          name: endpoint.name,
          url: endpoint.url,
          method: endpoint.method || 'GET',
          body: endpoint.body,
          headers: endpoint.headers || {},
          timeout: endpoint.timeout || this.config.default_timeout || 30000,
          expected_status: endpoint.expected_status || 200,
          expected_response: endpoint.expected_response,
          interval: endpoint.interval || this.config.default_interval || 60, // 秒
          sla_threshold: endpoint.sla_threshold || this.config.default_sla_threshold || 5000, // 毫秒
          last_check: null,
          last_status: null,
          consecutiveFailures: 0,
          enabled: endpoint.enabled !== false
        }));
        
        this.logger.info(`已从配置加载 ${this.endpoints.length} 个健康检查端点`);
      } else {
        this.logger.warn('未找到健康检查端点配置');
        this.endpoints = [];
      }
    } catch (error) {
      this.logger.error('加载健康检查端点配置时出错:', error);
      throw error;
    }
  }

  /**
   * 启动健康检查服务
   * @returns {Promise<void>}
   */
  async start() {
    if (this.isRunning) {
      this.logger.warn('健康检查服务已在运行');
      return;
    }
    
    this.logger.info('启动健康检查服务...');
    
    try {
      // 检查是否有端点配置
      if (this.endpoints.length === 0) {
        this.logger.warn('没有可用的健康检查端点，健康检查服务不会启动');
        return;
      }
      
      // 启动运行器
      await this.runner.start();
      
      this.isRunning = true;
      this.logger.info('健康检查服务已启动');
    } catch (error) {
      this.logger.error('启动健康检查服务时出错:', error);
      throw error;
    }
  }

  /**
   * 停止健康检查服务
   * @returns {Promise<void>}
   */
  async stop() {
    if (!this.isRunning) {
      return;
    }
    
    this.logger.info('停止健康检查服务...');
    
    try {
      // 停止运行器
      await this.runner.stop();
      
      this.isRunning = false;
      this.logger.info('健康检查服务已停止');
    } catch (error) {
      this.logger.error('停止健康检查服务时出错:', error);
      throw error;
    }
  }

  /**
   * 重新加载端点配置
   * @returns {Promise<void>}
   */
  async reloadEndpoints() {
    this.logger.info('重新加载健康检查端点配置...');
    
    const wasRunning = this.isRunning;
    
    try {
      // 如果服务正在运行，先停止
      if (wasRunning) {
        await this.stop();
      }
      
      // 重新加载端点配置
      await this._loadEndpointsConfig();
      
      // 更新运行器的端点
      this.runner.setEndpoints(this.endpoints);
      
      // 如果服务之前在运行，重新启动
      if (wasRunning) {
        await this.start();
      }
      
      this.logger.info(`健康检查端点配置重新加载完成，已加载 ${this.endpoints.length} 个端点`);
    } catch (error) {
      this.logger.error('重新加载健康检查端点配置时出错:', error);
      
      // 尝试恢复服务状态
      if (wasRunning && !this.isRunning) {
        await this.start();
      }
      
      throw error;
    }
  }

  /**
   * 添加健康检查端点
   * @param {Object} endpoint - 端点配置
   * @returns {Promise<void>}
   */
  async addEndpoint(endpoint) {
    if (!endpoint.name || !endpoint.url) {
      throw new Error('端点必须包含name和url');
    }
    
    // 检查是否已存在同名端点
    const existingIndex = this.endpoints.findIndex(e => e.name === endpoint.name);
    if (existingIndex >= 0) {
      throw new Error(`端点 ${endpoint.name} 已存在`);
    }
    
    // 创建新端点
    const newEndpoint = {
      name: endpoint.name,
      url: endpoint.url,
      method: endpoint.method || 'GET',
      body: endpoint.body,
      headers: endpoint.headers || {},
      timeout: endpoint.timeout || this.config.default_timeout || 30000,
      expected_status: endpoint.expected_status || 200,
      expected_response: endpoint.expected_response,
      interval: endpoint.interval || this.config.default_interval || 60, // 秒
      sla_threshold: endpoint.sla_threshold || this.config.default_sla_threshold || 5000, // 毫秒
      last_check: null,
      last_status: null,
      consecutiveFailures: 0,
      enabled: endpoint.enabled !== false
    };
    
    // 添加到端点列表
    this.endpoints.push(newEndpoint);
    
    // 更新运行器的端点
    this.runner.setEndpoints(this.endpoints);
    
    this.logger.info(`已添加健康检查端点: ${endpoint.name}`);
  }

  /**
   * 更新健康检查端点
   * @param {string} name - 端点名称
   * @param {Object} updates - 更新内容
   * @returns {Promise<void>}
   */
  async updateEndpoint(name, updates) {
    // 查找端点
    const endpointIndex = this.endpoints.findIndex(e => e.name === name);
    if (endpointIndex < 0) {
      throw new Error(`端点 ${name} 不存在`);
    }
    
    // 更新端点
    const endpoint = this.endpoints[endpointIndex];
    
    Object.keys(updates).forEach(key => {
      // 不允许更新某些字段
      if (['last_check', 'last_status', 'consecutiveFailures'].includes(key)) {
        return;
      }
      
      endpoint[key] = updates[key];
    });
    
    // 更新运行器的端点
    this.runner.setEndpoints(this.endpoints);
    
    this.logger.info(`已更新健康检查端点: ${name}`);
  }

  /**
   * 删除健康检查端点
   * @param {string} name - 端点名称
   * @returns {Promise<void>}
   */
  async removeEndpoint(name) {
    // 查找端点
    const initialLength = this.endpoints.length;
    this.endpoints = this.endpoints.filter(e => e.name !== name);
    
    if (this.endpoints.length === initialLength) {
      throw new Error(`端点 ${name} 不存在`);
    }
    
    // 更新运行器的端点
    this.runner.setEndpoints(this.endpoints);
    
    this.logger.info(`已删除健康检查端点: ${name}`);
  }

  /**
   * 启用或禁用健康检查端点
   * @param {string} name - 端点名称
   * @param {boolean} enabled - 是否启用
   * @returns {Promise<void>}
   */
  async setEndpointEnabled(name, enabled) {
    // 查找端点
    const endpointIndex = this.endpoints.findIndex(e => e.name === name);
    if (endpointIndex < 0) {
      throw new Error(`端点 ${name} 不存在`);
    }
    
    // 更新端点状态
    this.endpoints[endpointIndex].enabled = enabled;
    
    // 更新运行器的端点
    this.runner.setEndpoints(this.endpoints);
    
    this.logger.info(`已${enabled ? '启用' : '禁用'}健康检查端点: ${name}`);
  }

  /**
   * 获取所有健康检查端点
   * @returns {Array} 端点列表
   */
  getEndpoints() {
    return [...this.endpoints];
  }

  /**
   * 获取端点状态
   * @returns {Array} 端点状态列表
   */
  getStatus() {
    return this.endpoints.map(endpoint => ({
      name: endpoint.name,
      url: endpoint.url,
      enabled: endpoint.enabled,
      last_check: endpoint.last_check,
      last_status: endpoint.last_status,
      consecutiveFailures: endpoint.consecutiveFailures
    }));
  }

  /**
   * 手动执行健康检查
   * @param {string} name - 端点名称，如果不指定则检查所有端点
   * @returns {Promise<Object>} 检查结果
   */
  async runCheck(name) {
    if (name) {
      // 查找端点
      const endpoint = this.endpoints.find(e => e.name === name);
      if (!endpoint) {
        throw new Error(`端点 ${name} 不存在`);
      }
      
      // 运行单个端点的健康检查
      return await this.runner.checkEndpoint(endpoint);
    } else {
      // 运行所有端点的健康检查
      return await this.runner.checkAllEndpoints();
    }
  }
}

module.exports = HealthCheckService; 