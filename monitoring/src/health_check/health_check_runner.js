/**
 * 健康检查运行器 - 执行健康检查任务
 */

class HealthCheckRunner {
  /**
   * 创建健康检查运行器
   * @param {Object} logger - 日志记录器
   */
  constructor(logger) {
    this.logger = logger;
    this.endpoints = [];
    this.httpClient = null;
    this.metricsCollector = null;
    this.alertManager = null;
    
    // 定时器列表
    this.timers = {};
    
    // 是否正在运行
    this.isRunning = false;
  }

  /**
   * 设置端点列表
   * @param {Array} endpoints - 端点列表
   */
  setEndpoints(endpoints) {
    this.endpoints = [...endpoints];
  }

  /**
   * 设置HTTP客户端
   * @param {Object} httpClient - Axios实例
   */
  setHttpClient(httpClient) {
    this.httpClient = httpClient;
  }

  /**
   * 设置指标收集器
   * @param {Object} metricsCollector - 指标收集器
   */
  setMetricsCollector(metricsCollector) {
    this.metricsCollector = metricsCollector;
  }

  /**
   * 设置告警管理器
   * @param {Object} alertManager - 告警管理器
   */
  setAlertManager(alertManager) {
    this.alertManager = alertManager;
  }

  /**
   * 启动健康检查运行器
   * @returns {Promise<void>}
   */
  async start() {
    if (this.isRunning) {
      this.logger.warn('健康检查运行器已在运行');
      return;
    }
    
    if (!this.httpClient) {
      throw new Error('未设置HTTP客户端');
    }
    
    this.logger.info('启动健康检查运行器...');
    
    // 清除所有现有定时器
    this._clearAllTimers();
    
    // 为每个端点创建定时器
    for (const endpoint of this.endpoints) {
      if (endpoint.enabled) {
        this._createTimer(endpoint);
      }
    }
    
    this.isRunning = true;
    this.logger.info('健康检查运行器已启动');
  }

  /**
   * 停止健康检查运行器
   * @returns {Promise<void>}
   */
  async stop() {
    if (!this.isRunning) {
      return;
    }
    
    this.logger.info('停止健康检查运行器...');
    
    // 清除所有定时器
    this._clearAllTimers();
    
    this.isRunning = false;
    this.logger.info('健康检查运行器已停止');
  }

  /**
   * 创建定时器
   * @param {Object} endpoint - 端点配置
   * @private
   */
  _createTimer(endpoint) {
    // 如果已有定时器，先清除
    if (this.timers[endpoint.name]) {
      clearInterval(this.timers[endpoint.name]);
    }
    
    // 创建新定时器
    const intervalMs = endpoint.interval * 1000;
    this.timers[endpoint.name] = setInterval(() => {
      this.checkEndpoint(endpoint).catch(err => {
        this.logger.error(`检查端点 ${endpoint.name} 时出错:`, err);
      });
    }, intervalMs);
    
    // 防止定时器阻止进程退出
    this.timers[endpoint.name].unref();
    
    // 立即执行一次健康检查
    this.checkEndpoint(endpoint).catch(err => {
      this.logger.error(`初始检查端点 ${endpoint.name} 时出错:`, err);
    });
  }

  /**
   * 清除所有定时器
   * @private
   */
  _clearAllTimers() {
    for (const name in this.timers) {
      clearInterval(this.timers[name]);
      delete this.timers[name];
    }
  }

  /**
   * 检查所有端点
   * @returns {Promise<Object>} 检查结果
   */
  async checkAllEndpoints() {
    const results = {};
    
    for (const endpoint of this.endpoints) {
      if (endpoint.enabled) {
        try {
          results[endpoint.name] = await this.checkEndpoint(endpoint);
        } catch (error) {
          this.logger.error(`检查端点 ${endpoint.name} 时出错:`, error);
          results[endpoint.name] = {
            success: false,
            error: error.message
          };
        }
      }
    }
    
    return results;
  }

  /**
   * 检查单个端点
   * @param {Object} endpoint - 端点配置
   * @returns {Promise<Object>} 检查结果
   */
  async checkEndpoint(endpoint) {
    if (!this.httpClient) {
      throw new Error('未设置HTTP客户端');
    }
    
    const startTime = Date.now();
    let success = false;
    let statusCode = null;
    let responseData = null;
    let errorMessage = null;
    
    try {
      this.logger.debug(`开始检查端点: ${endpoint.name} (${endpoint.url})`);
      
      // 准备请求配置
      const requestConfig = {
        url: endpoint.url,
        method: endpoint.method || 'GET',
        headers: endpoint.headers || {},
        timeout: endpoint.timeout || 30000
      };
      
      // 如果有请求体，添加到配置中
      if (endpoint.body) {
        requestConfig.data = endpoint.body;
      }
      
      // 发送请求
      const response = await this.httpClient(requestConfig);
      
      // 记录响应数据
      statusCode = response.status;
      
      // 限制响应数据大小
      if (response.data) {
        if (typeof response.data === 'string') {
          responseData = response.data.substring(0, 1000);
        } else {
          try {
            responseData = JSON.stringify(response.data).substring(0, 1000);
          } catch (e) {
            responseData = '[无法序列化响应数据]';
          }
        }
      }
      
      // 检查状态码是否符合预期
      const expectedStatus = endpoint.expected_status || 200;
      const statusMatches = Array.isArray(expectedStatus) 
        ? expectedStatus.includes(response.status)
        : response.status === expectedStatus;
      
      // 检查响应内容是否符合预期
      let contentMatches = true;
      if (endpoint.expected_response) {
        if (typeof endpoint.expected_response === 'string') {
          contentMatches = responseData && responseData.includes(endpoint.expected_response);
        } else if (endpoint.expected_response instanceof RegExp) {
          contentMatches = endpoint.expected_response.test(responseData);
        }
      }
      
      // 判断检查是否成功
      success = statusMatches && contentMatches;
      
      // 如果检查失败，记录错误消息
      if (!success) {
        if (!statusMatches) {
          errorMessage = `状态码不符合预期: ${response.status} (期望 ${expectedStatus})`;
        } else if (!contentMatches) {
          errorMessage = `响应内容不符合预期`;
        }
      }
    } catch (error) {
      success = false;
      errorMessage = error.message;
      this.logger.debug(`检查端点 ${endpoint.name} 失败:`, error);
    }
    
    // 计算耗时
    const duration = Date.now() - startTime;
    
    // 更新端点状态
    endpoint.last_check = new Date();
    endpoint.last_status = success;
    
    // 更新连续失败次数
    if (success) {
      endpoint.consecutiveFailures = 0;
    } else {
      endpoint.consecutiveFailures++;
    }
    
    // 创建健康检查指标
    const metric = {
      type: 'health_check',
      timestamp: new Date(),
      collected_at: new Date(),
      endpoint_name: endpoint.name,
      url: endpoint.url,
      status_code: statusCode,
      duration: duration,
      is_success: success,
      response_data: responseData,
      error_message: errorMessage
    };
    
    // 收集指标
    if (this.metricsCollector) {
      this.metricsCollector.recordCustomMetric(metric);
    }
    
    // 处理告警
    this._handleAlerts(endpoint, metric);
    
    // 记录日志
    if (success) {
      this.logger.debug(`端点 ${endpoint.name} 健康检查成功，耗时 ${duration}ms`);
    } else {
      this.logger.warn(`端点 ${endpoint.name} 健康检查失败: ${errorMessage}`);
    }
    
    // 返回检查结果
    return {
      name: endpoint.name,
      url: endpoint.url,
      timestamp: endpoint.last_check,
      success: success,
      statusCode: statusCode,
      duration: duration,
      error: errorMessage,
      consecutiveFailures: endpoint.consecutiveFailures
    };
  }

  /**
   * 处理告警
   * @param {Object} endpoint - 端点配置
   * @param {Object} metric - 健康检查指标
   * @private
   */
  _handleAlerts(endpoint, metric) {
    if (!this.alertManager) {
      return;
    }
    
    // 检查是否需要触发告警
    const alerts = [];
    
    // 检查可用性告警
    if (!metric.is_success) {
      // 根据连续失败次数判断是否触发告警
      if (endpoint.consecutiveFailures >= (endpoint.alert_threshold || 1)) {
        alerts.push({
          name: `健康检查失败: ${endpoint.name}`,
          severity: endpoint.consecutiveFailures >= (endpoint.critical_threshold || 3) ? 'critical' : 'error',
          message: `端点 ${endpoint.name} (${endpoint.url}) 连续 ${endpoint.consecutiveFailures} 次健康检查失败: ${metric.error_message}`,
          metric: metric
        });
      }
    }
    
    // 检查响应时间告警
    if (metric.is_success && metric.duration > endpoint.sla_threshold) {
      alerts.push({
        name: `响应时间过长: ${endpoint.name}`,
        severity: 'warning',
        message: `端点 ${endpoint.name} (${endpoint.url}) 响应时间 ${metric.duration}ms 超过阈值 ${endpoint.sla_threshold}ms`,
        metric: metric
      });
    }
    
    // 发送告警
    for (const alert of alerts) {
      this.alertManager.sendAlert(alert).catch(err => {
        this.logger.error(`发送告警 ${alert.name} 时出错:`, err);
      });
    }
  }
}

module.exports = HealthCheckRunner; 