/**
 * 指标收集器模块 - 收集和处理API性能和可用性数据
 */

const MetricsProcessor = require('./metrics_processor');
const MetricsBuffer = require('./metrics_buffer');

class MetricsCollector {
  /**
   * 构造函数
   * @param {Object} config 配置对象
   * @param {Object} storageManager 存储管理器
   * @param {Object} logger 日志记录器
   * @param {Object} promRegistry Prometheus注册表
   */
  constructor(config, storageManager, logger, promRegistry) {
    this.config = config;
    this.storageManager = storageManager;
    this.logger = logger;
    this.promRegistry = promRegistry;
    
    // 创建指标缓冲区，缓存指标数据
    this.metricsBuffer = new MetricsBuffer(
      config.metrics_buffer_size || 1000,
      logger
    );
    
    // 创建指标处理器，处理和分析指标
    this.metricsProcessor = new MetricsProcessor(
      config,
      storageManager,
      logger,
      promRegistry
    );
    
    // 定期刷新指标数据到存储
    this.flushInterval = null;
    this.flushIntervalMs = config.flush_interval_ms || 5000;
    
    // 抽样率（0-1之间的值，表示要收集的请求百分比）
    this.samplingRate = config.sampling_rate || 1.0;
  }

  /**
   * 记录API调用
   * @param {Object} callData - API调用数据
   */
  recordApiCall(callData) {
    try {
      // 检查是否应该根据抽样率收集此请求
      if (this.samplingRate < 1.0 && Math.random() > this.samplingRate) {
        return;
      }
      
      // 添加采集时间戳
      const metricData = {
        ...callData,
        collected_at: new Date()
      };
      
      // 将指标数据添加到缓冲区
      this.metricsBuffer.add(metricData);
      
      // 如果缓冲区已满，立即刷新
      if (this.metricsBuffer.isFull()) {
        this.flushMetrics();
      }
      
      // 检查是否需要启动刷新定时器
      this._ensureFlushInterval();
    } catch (error) {
      this.logger.error('记录API调用指标时出错:', error);
    }
  }

  /**
   * 记录自定义指标
   * @param {Object} metricData - 自定义指标数据
   */
  recordCustomMetric(metricData) {
    try {
      // 添加类型和采集时间戳
      const enrichedMetricData = {
        ...metricData,
        type: 'custom',
        collected_at: new Date()
      };
      
      // 将指标添加到缓冲区
      this.metricsBuffer.add(enrichedMetricData);
      
      // 检查是否需要启动刷新定时器
      this._ensureFlushInterval();
    } catch (error) {
      this.logger.error('记录自定义指标时出错:', error);
    }
  }

  /**
   * 刷新指标数据到存储
   */
  async flushMetrics() {
    try {
      // 从缓冲区获取所有指标
      const metrics = this.metricsBuffer.getAll();
      
      if (metrics.length === 0) {
        return;
      }
      
      this.logger.debug(`刷新 ${metrics.length} 条指标数据到存储`);
      
      // 处理指标数据
      await this.metricsProcessor.processMetrics(metrics);
      
      // 清空缓冲区
      this.metricsBuffer.clear();
    } catch (error) {
      this.logger.error('刷新指标数据时出错:', error);
    }
  }

  /**
   * 获取特定时间范围内的指标
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 指标数组
   */
  async getMetrics(query) {
    try {
      // 先刷新所有指标
      await this.flushMetrics();
      
      // 从存储中查询指标
      return await this.storageManager.getMetrics(query);
    } catch (error) {
      this.logger.error('获取指标时出错:', error);
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
      // 先刷新所有指标
      await this.flushMetrics();
      
      // 获取API性能摘要
      return await this.metricsProcessor.getApiPerformanceSummary(apiName, timeRange);
    } catch (error) {
      this.logger.error('获取API性能摘要时出错:', error);
      throw error;
    }
  }

  /**
   * 确保刷新定时器已启动
   * @private
   */
  _ensureFlushInterval() {
    if (!this.flushInterval) {
      this.flushInterval = setInterval(() => {
        this.flushMetrics().catch(err => {
          this.logger.error('自动刷新指标时出错:', err);
        });
      }, this.flushIntervalMs);
      
      // 防止定时器阻止进程退出
      this.flushInterval.unref();
    }
  }

  /**
   * 停止指标收集
   */
  stop() {
    if (this.flushInterval) {
      clearInterval(this.flushInterval);
      this.flushInterval = null;
    }
    
    // 最后一次刷新指标
    return this.flushMetrics();
  }
}

module.exports = MetricsCollector; 