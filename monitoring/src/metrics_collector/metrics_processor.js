/**
 * 指标处理器 - 处理和分析指标数据
 */

class MetricsProcessor {
  /**
   * 创建指标处理器
   * @param {Object} config - 配置
   * @param {Object} storageManager - 存储管理器
   * @param {Object} logger - 日志记录器
   * @param {Object} promRegistry - Prometheus注册表
   */
  constructor(config, storageManager, logger, promRegistry) {
    this.config = config;
    this.storageManager = storageManager;
    this.logger = logger;
    this.promRegistry = promRegistry;
    this.promClient = require('prom-client');
    this.histograms = {};
    
    // 初始化Prometheus计数器和仪表
    this.counters = {
      totalRequests: new this.promClient.Counter({
        name: 'api_requests_total',
        help: 'API请求总数',
        labelNames: ['endpoint', 'method', 'status'],
        registers: [promRegistry]
      }),
      errorCount: new this.promClient.Counter({
        name: 'api_errors_total',
        help: 'API错误总数',
        labelNames: ['endpoint', 'method', 'status', 'error_type'],
        registers: [promRegistry]
      })
    };
    
    this.gauges = {
      responseTime: new this.promClient.Gauge({
        name: 'api_response_time',
        help: 'API响应时间（毫秒）',
        labelNames: ['endpoint', 'method'],
        registers: [promRegistry]
      }),
      activeSessions: new this.promClient.Gauge({
        name: 'active_sessions',
        help: '当前活跃会话数',
        registers: [promRegistry]
      })
    };
  }

  /**
   * 处理指标数据
   * @param {Array} metrics - 指标数据数组
   * @returns {Promise<void>}
   */
  async processMetrics(metrics) {
    if (!metrics || metrics.length === 0) {
      return;
    }

    try {
      this.logger.debug(`处理 ${metrics.length} 条指标数据`);

      // 根据指标类型进行分组
      const groupedMetrics = this._groupMetricsByType(metrics);

      // 处理API调用指标
      if (groupedMetrics.apiCalls && groupedMetrics.apiCalls.length > 0) {
        await this._processApiCallMetrics(groupedMetrics.apiCalls);
      }

      // 处理自定义指标
      if (groupedMetrics.custom && groupedMetrics.custom.length > 0) {
        await this._processCustomMetrics(groupedMetrics.custom);
      }

      // 处理健康检查指标
      if (groupedMetrics.healthChecks && groupedMetrics.healthChecks.length > 0) {
        await this._processHealthCheckMetrics(groupedMetrics.healthChecks);
      }

      this.logger.debug('指标处理完成');
    } catch (error) {
      this.logger.error('处理指标数据时出错:', error);
      throw error;
    }
  }

  /**
   * 根据类型对指标进行分组
   * @param {Array} metrics - 指标数据数组
   * @returns {Object} 分组后的指标
   * @private
   */
  _groupMetricsByType(metrics) {
    const result = {
      apiCalls: [],
      custom: [],
      healthChecks: []
    };

    for (const metric of metrics) {
      // 根据指标的type字段或其他特征进行分类
      if (metric.type === 'custom') {
        result.custom.push(metric);
      } else if (metric.type === 'health_check') {
        result.healthChecks.push(metric);
      } else {
        // 默认为API调用指标
        result.apiCalls.push(metric);
      }
    }

    return result;
  }

  /**
   * 处理API调用指标
   * @param {Array} metrics - API调用指标数组
   * @returns {Promise<void>}
   * @private
   */
  async _processApiCallMetrics(metrics) {
    try {
      // 增强API调用指标数据
      const enhancedMetrics = metrics.map(metric => this._enhanceApiCallMetric(metric));

      // 存储原始指标
      await this.storageManager.saveRawMetrics(enhancedMetrics);

      // 计算聚合指标（按API和时间间隔）
      const aggregatedMetrics = this._calculateAggregatedMetrics(enhancedMetrics);
      
      // 存储聚合指标
      if (aggregatedMetrics.length > 0) {
        await this.storageManager.saveAggregatedMetrics(aggregatedMetrics);
      }

      // 检查是否有异常指标，并记录
      const anomalies = this._detectAnomalies(enhancedMetrics);
      if (anomalies.length > 0) {
        await this.storageManager.saveAnomalies(anomalies);
      }
    } catch (error) {
      this.logger.error('处理API调用指标时出错:', error);
      throw error;
    }
  }

  /**
   * 增强API调用指标
   * @param {Object} metric - 原始API调用指标
   * @returns {Object} 增强后的指标
   * @private
   */
  _enhanceApiCallMetric(metric) {
    // 复制原始指标
    const enhancedMetric = { ...metric };

    // 添加时间戳（如果没有）
    if (!enhancedMetric.timestamp) {
      enhancedMetric.timestamp = new Date();
    }

    // 标准化URL（移除查询字符串）
    if (enhancedMetric.url) {
      enhancedMetric.normalized_url = this._normalizeUrl(enhancedMetric.url);
    }

    // 计算成功/失败状态
    enhancedMetric.is_success = this._isSuccessStatus(enhancedMetric.statusCode);

    // 计算性能等级
    enhancedMetric.performance_grade = this._calculatePerformanceGrade(enhancedMetric.duration);

    return enhancedMetric;
  }

  /**
   * 标准化URL（移除查询字符串）
   * @param {string} url - 原始URL
   * @returns {string} 标准化后的URL
   * @private
   */
  _normalizeUrl(url) {
    try {
      // 移除查询字符串
      const urlObj = new URL(url, 'http://example.com');
      return `${urlObj.pathname}`;
    } catch (error) {
      // 如果无法解析URL，返回原始URL
      return url;
    }
  }

  /**
   * 检查HTTP状态码是否表示成功
   * @param {number} statusCode - HTTP状态码
   * @returns {boolean} 是否成功
   * @private
   */
  _isSuccessStatus(statusCode) {
    return statusCode >= 200 && statusCode < 400;
  }

  /**
   * 计算性能等级（A/B/C/D/F）
   * @param {number} duration - 请求持续时间（毫秒）
   * @returns {string} 性能等级
   * @private
   */
  _calculatePerformanceGrade(duration) {
    if (duration < 100) return 'A';
    if (duration < 300) return 'B';
    if (duration < 1000) return 'C';
    if (duration < 3000) return 'D';
    return 'F';
  }

  /**
   * 计算聚合指标
   * @param {Array} metrics - 增强后的指标数组
   * @returns {Array} 聚合指标数组
   * @private
   */
  _calculateAggregatedMetrics(metrics) {
    const aggregatedMetrics = [];
    
    // 按API和时间间隔分组
    const groups = this._groupMetricsByApiAndTimeInterval(metrics);
    
    // 为每个分组计算聚合指标
    for (const groupKey in groups) {
      const groupMetrics = groups[groupKey];
      
      // 提取分组信息
      const [apiName, interval] = groupKey.split('|');
      
      // 计算聚合指标
      const totalCount = groupMetrics.length;
      const successCount = groupMetrics.filter(m => m.is_success).length;
      const errorCount = totalCount - successCount;
      const errorRate = totalCount > 0 ? errorCount / totalCount : 0;
      
      // 计算响应时间统计数据
      const durations = groupMetrics.map(m => m.duration).filter(d => typeof d === 'number');
      const avgDuration = durations.length > 0 
        ? durations.reduce((sum, val) => sum + val, 0) / durations.length 
        : 0;
      
      durations.sort((a, b) => a - b);
      const p50Duration = durations.length > 0 
        ? durations[Math.floor(durations.length * 0.5)] 
        : 0;
      const p95Duration = durations.length > 0 
        ? durations[Math.floor(durations.length * 0.95)] 
        : 0;
      const p99Duration = durations.length > 0 
        ? durations[Math.floor(durations.length * 0.99)] 
        : 0;
      
      // 创建聚合指标对象
      const aggregatedMetric = {
        api_name: apiName,
        interval,
        timestamp: new Date(interval),
        total_count: totalCount,
        success_count: successCount,
        error_count: errorCount,
        error_rate: errorRate,
        avg_duration: avgDuration,
        p50_duration: p50Duration,
        p95_duration: p95Duration,
        p99_duration: p99Duration,
        min_duration: durations[0] || 0,
        max_duration: durations[durations.length - 1] || 0
      };
      
      aggregatedMetrics.push(aggregatedMetric);
    }
    
    return aggregatedMetrics;
  }

  /**
   * 将指标按API和时间间隔分组
   * @param {Array} metrics - 指标数组
   * @returns {Object} 分组后的指标
   * @private
   */
  _groupMetricsByApiAndTimeInterval(metrics) {
    const groups = {};
    
    for (const metric of metrics) {
      // 提取API名称
      const apiName = metric.name || 
                      (metric.targetUrl ? new URL(metric.targetUrl).hostname : 'unknown');
      
      // 确定时间间隔（按分钟）
      const timestamp = new Date(metric.timestamp);
      timestamp.setSeconds(0);
      timestamp.setMilliseconds(0);
      const interval = timestamp.toISOString();
      
      // 创建分组键
      const groupKey = `${apiName}|${interval}`;
      
      // 添加到分组
      if (!groups[groupKey]) {
        groups[groupKey] = [];
      }
      groups[groupKey].push(metric);
    }
    
    return groups;
  }

  /**
   * 检测异常指标
   * @param {Array} metrics - 增强后的指标数组
   * @returns {Array} 异常数组
   * @private
   */
  _detectAnomalies(metrics) {
    const anomalies = [];
    
    for (const metric of metrics) {
      // 检测响应时间异常
      if (metric.duration > 5000) { // 如果响应时间超过5秒
        anomalies.push({
          type: 'high_latency',
          metric_id: metric.requestId,
          timestamp: metric.timestamp,
          value: metric.duration,
          threshold: 5000,
          message: `高延迟: ${metric.duration}ms > 5000ms`
        });
      }
      
      // 检测错误状态码
      if (metric.statusCode >= 500) {
        anomalies.push({
          type: 'server_error',
          metric_id: metric.requestId,
          timestamp: metric.timestamp,
          value: metric.statusCode,
          message: `服务器错误: ${metric.statusCode}`
        });
      }
    }
    
    return anomalies;
  }

  /**
   * 处理自定义指标
   * @param {Array} metrics - 自定义指标数组
   * @returns {Promise<void>}
   * @private
   */
  async _processCustomMetrics(metrics) {
    try {
      // 存储自定义指标
      await this.storageManager.saveCustomMetrics(metrics);
    } catch (error) {
      this.logger.error('处理自定义指标时出错:', error);
      throw error;
    }
  }

  /**
   * 处理健康检查指标
   * @param {Array} metrics - 健康检查指标数组
   * @returns {Promise<void>}
   * @private
   */
  async _processHealthCheckMetrics(metrics) {
    try {
      // 存储健康检查指标
      await this.storageManager.saveHealthCheckMetrics(metrics);
    } catch (error) {
      this.logger.error('处理健康检查指标时出错:', error);
      throw error;
    }
  }

  /**
   * 获取API性能摘要
   * @param {string} apiName - API名称
   * @param {Object} timeRange - 时间范围
   * @returns {Promise<Object>} 性能摘要
   */
  async getApiPerformanceSummary(apiName, timeRange) {
    try {
      // 从存储中获取聚合指标
      const aggregatedMetrics = await this.storageManager.getAggregatedMetrics({
        api_name: apiName,
        start_time: timeRange.start,
        end_time: timeRange.end
      });
      
      if (aggregatedMetrics.length === 0) {
        return {
          api_name: apiName,
          time_range: timeRange,
          total_requests: 0,
          error_rate: 0,
          avg_duration: 0,
          p95_duration: 0,
          availability: 0
        };
      }
      
      // 计算总请求数
      const totalRequests = aggregatedMetrics.reduce((sum, m) => sum + m.total_count, 0);
      
      // 计算错误率
      const totalErrors = aggregatedMetrics.reduce((sum, m) => sum + m.error_count, 0);
      const errorRate = totalRequests > 0 ? totalErrors / totalRequests : 0;
      
      // 计算平均响应时间
      const weightedDurations = aggregatedMetrics.reduce(
        (sum, m) => sum + (m.avg_duration * m.total_count), 0
      );
      const avgDuration = totalRequests > 0 ? weightedDurations / totalRequests : 0;
      
      // 计算P95响应时间（简化实现）
      const p95Durations = aggregatedMetrics.map(m => m.p95_duration);
      p95Durations.sort((a, b) => a - b);
      const p95Duration = p95Durations.length > 0 
        ? p95Durations[Math.floor(p95Durations.length * 0.95)] 
        : 0;
      
      // 计算可用性（成功请求百分比）
      const availability = totalRequests > 0 
        ? (totalRequests - totalErrors) / totalRequests 
        : 0;
      
      return {
        api_name: apiName,
        time_range: timeRange,
        total_requests: totalRequests,
        error_rate: errorRate,
        avg_duration: avgDuration,
        p95_duration: p95Duration,
        availability: availability
      };
    } catch (error) {
      this.logger.error('获取API性能摘要时出错:', error);
      throw error;
    }
  }

  /**
   * 增加分位数统计的方法
   * @param {string} metricName 指标名称
   * @param {number} value 指标值
   * @param {object} labels 标签
   */
  createHistogram(metricName, value, labels = {}) {
    if (!this.histograms[metricName]) {
      // 创建直方图
      this.histograms[metricName] = new this.promClient.Histogram({
        name: `${metricName}_seconds`,
        help: `${metricName} 时间分布统计`,
        labelNames: Object.keys(labels),
        buckets: [0.01, 0.05, 0.1, 0.5, 1, 2, 5, 10]  // 分位数桶
      });
    }
    
    this.histograms[metricName].observe(labels, value / 1000); // 转换为秒
  }

  /**
   * 处理API响应时间指标
   * @param {Object} metric 
   */
  processResponseTimeMetric(metric) {
    // ... existing code ...
    
    // 添加直方图统计
    this.createHistogram('api_response_time', metric.value, {
      endpoint: metric.labels.endpoint,
      method: metric.labels.method,
      status: metric.labels.status
    });
  }

  /**
   * 处理数据库查询时间指标
   * @param {Object} metric 
   */
  processDatabaseQueryTimeMetric(metric) {
    // ... existing code ...
    
    // 添加直方图统计
    this.createHistogram('database_query_time', metric.value, {
      query_type: metric.labels.query_type,
      table: metric.labels.table
    });
  }
}

module.exports = MetricsProcessor; 