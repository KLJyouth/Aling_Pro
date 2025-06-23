/**
 * 内存存储实现 - 使用内存存储监控数据（用于开发/测试）
 */

class MemoryStorage {
  /**
   * 创建内存存储
   * @param {Object} config - 配置
   * @param {Object} logger - 日志记录器
   */
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.connected = false;
    
    // 存储数据的内存结构
    this.data = {
      api_metrics: [],
      api_metrics_aggregated: [],
      custom_metrics: [],
      health_check_metrics: [],
      anomalies: []
    };
    
    // 保留策略（毫秒）
    this.retentionPolicy = {
      raw_data_ms: (config.retention_policy?.raw_data_days || 7) * 24 * 60 * 60 * 1000,
      aggregated_data_ms: (config.retention_policy?.aggregated_data_days || 30) * 24 * 60 * 60 * 1000
    };
    
    // 清理定时器
    this.cleanupInterval = null;
  }

  /**
   * 连接到存储
   * @returns {Promise<void>}
   */
  async connect() {
    this.connected = true;
    this.logger.info('内存存储已连接');
    
    // 设置定期清理旧数据
    this.cleanupInterval = setInterval(() => {
      this.cleanupOldData();
    }, 1 * 60 * 60 * 1000); // 每小时清理一次
    
    this.cleanupInterval.unref(); // 不阻止进程退出
    
    return Promise.resolve();
  }

  /**
   * 断开存储连接
   * @returns {Promise<void>}
   */
  async disconnect() {
    this.connected = false;
    
    if (this.cleanupInterval) {
      clearInterval(this.cleanupInterval);
      this.cleanupInterval = null;
    }
    
    this.logger.info('内存存储已断开连接');
    return Promise.resolve();
  }

  /**
   * 保存原始指标数据
   * @param {Array} metrics - 指标数据数组
   * @returns {Promise<void>}
   */
  async saveRawMetrics(metrics) {
    this._checkConnection();
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    // 复制并添加ID
    const now = Date.now();
    const enrichedMetrics = metrics.map((metric, index) => ({
      id: `${now}-${index}`,
      ...metric,
      // 确保timestamp是Date对象
      timestamp: metric.timestamp ? new Date(metric.timestamp) : new Date(),
      collected_at: metric.collected_at ? new Date(metric.collected_at) : new Date()
    }));
    
    this.data.api_metrics.push(...enrichedMetrics);
    this.logger.debug(`已保存 ${metrics.length} 条原始指标到内存`);
    
    return Promise.resolve();
  }

  /**
   * 保存聚合指标数据
   * @param {Array} metrics - 聚合指标数据数组
   * @returns {Promise<void>}
   */
  async saveAggregatedMetrics(metrics) {
    this._checkConnection();
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    // 复制并添加ID
    const now = Date.now();
    const enrichedMetrics = metrics.map((metric, index) => ({
      id: `${now}-${index}`,
      ...metric,
      // 确保interval是Date对象
      interval: metric.interval ? new Date(metric.interval) : new Date()
    }));
    
    // 检查是否有重复的指标（同一API和同一时间间隔）
    for (const metric of enrichedMetrics) {
      const existingIndex = this.data.api_metrics_aggregated.findIndex(m => 
        m.api_name === metric.api_name && 
        m.interval.getTime() === metric.interval.getTime()
      );
      
      if (existingIndex >= 0) {
        // 更新现有指标
        this.data.api_metrics_aggregated[existingIndex] = metric;
      } else {
        // 添加新指标
        this.data.api_metrics_aggregated.push(metric);
      }
    }
    
    this.logger.debug(`已保存 ${metrics.length} 条聚合指标到内存`);
    
    return Promise.resolve();
  }

  /**
   * 保存自定义指标
   * @param {Array} metrics - 自定义指标数组
   * @returns {Promise<void>}
   */
  async saveCustomMetrics(metrics) {
    this._checkConnection();
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    // 复制并添加ID
    const now = Date.now();
    const enrichedMetrics = metrics.map((metric, index) => ({
      id: `${now}-${index}`,
      ...metric,
      // 确保timestamp是Date对象
      timestamp: metric.timestamp ? new Date(metric.timestamp) : new Date(),
      collected_at: metric.collected_at ? new Date(metric.collected_at) : new Date()
    }));
    
    this.data.custom_metrics.push(...enrichedMetrics);
    this.logger.debug(`已保存 ${metrics.length} 条自定义指标到内存`);
    
    return Promise.resolve();
  }

  /**
   * 保存健康检查指标
   * @param {Array} metrics - 健康检查指标数组
   * @returns {Promise<void>}
   */
  async saveHealthCheckMetrics(metrics) {
    this._checkConnection();
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    // 复制并添加ID
    const now = Date.now();
    const enrichedMetrics = metrics.map((metric, index) => ({
      id: `${now}-${index}`,
      ...metric,
      // 确保timestamp是Date对象
      timestamp: metric.timestamp ? new Date(metric.timestamp) : new Date(),
      collected_at: metric.collected_at ? new Date(metric.collected_at) : new Date()
    }));
    
    this.data.health_check_metrics.push(...enrichedMetrics);
    this.logger.debug(`已保存 ${metrics.length} 条健康检查指标到内存`);
    
    return Promise.resolve();
  }

  /**
   * 保存异常数据
   * @param {Array} anomalies - 异常数据数组
   * @returns {Promise<void>}
   */
  async saveAnomalies(anomalies) {
    this._checkConnection();
    
    if (!anomalies || anomalies.length === 0) {
      return;
    }
    
    // 复制并添加ID
    const now = Date.now();
    const enrichedAnomalies = anomalies.map((anomaly, index) => ({
      id: `${now}-${index}`,
      ...anomaly,
      // 确保timestamp是Date对象
      timestamp: anomaly.timestamp ? new Date(anomaly.timestamp) : new Date(),
      is_resolved: anomaly.is_resolved || false
    }));
    
    this.data.anomalies.push(...enrichedAnomalies);
    this.logger.debug(`已保存 ${anomalies.length} 条异常数据到内存`);
    
    return Promise.resolve();
  }

  /**
   * 获取指标数据
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 指标数据数组
   */
  async getMetrics(query) {
    this._checkConnection();
    
    const { api_name, start_time, end_time, method, url, status_code, limit = 1000 } = query;
    
    let filteredMetrics = [...this.data.api_metrics];
    
    // 应用过滤条件
    if (api_name) {
      filteredMetrics = filteredMetrics.filter(m => m.api_name === api_name);
    }
    
    if (start_time) {
      const startDate = new Date(start_time);
      filteredMetrics = filteredMetrics.filter(m => m.timestamp >= startDate);
    }
    
    if (end_time) {
      const endDate = new Date(end_time);
      filteredMetrics = filteredMetrics.filter(m => m.timestamp <= endDate);
    }
    
    if (method) {
      filteredMetrics = filteredMetrics.filter(m => m.method === method);
    }
    
    if (url) {
      filteredMetrics = filteredMetrics.filter(m => m.url && m.url.includes(url));
    }
    
    if (status_code) {
      filteredMetrics = filteredMetrics.filter(m => m.status_code === status_code);
    }
    
    // 按时间排序并限制结果数量
    filteredMetrics.sort((a, b) => b.timestamp - a.timestamp);
    
    return Promise.resolve(filteredMetrics.slice(0, limit));
  }

  /**
   * 获取聚合指标
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 聚合指标数组
   */
  async getAggregatedMetrics(query) {
    this._checkConnection();
    
    const { api_name, start_time, end_time, limit = 1000 } = query;
    
    let filteredMetrics = [...this.data.api_metrics_aggregated];
    
    // 应用过滤条件
    if (api_name) {
      filteredMetrics = filteredMetrics.filter(m => m.api_name === api_name);
    }
    
    if (start_time) {
      const startDate = new Date(start_time);
      filteredMetrics = filteredMetrics.filter(m => m.interval >= startDate);
    }
    
    if (end_time) {
      const endDate = new Date(end_time);
      filteredMetrics = filteredMetrics.filter(m => m.interval <= endDate);
    }
    
    // 按时间排序并限制结果数量
    filteredMetrics.sort((a, b) => b.interval - a.interval);
    
    return Promise.resolve(filteredMetrics.slice(0, limit));
  }

  /**
   * 获取异常数据
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 异常数据数组
   */
  async getAnomalies(query) {
    this._checkConnection();
    
    const { type, start_time, end_time, is_resolved, limit = 1000 } = query;
    
    let filteredAnomalies = [...this.data.anomalies];
    
    // 应用过滤条件
    if (type) {
      filteredAnomalies = filteredAnomalies.filter(a => a.type === type);
    }
    
    if (start_time) {
      const startDate = new Date(start_time);
      filteredAnomalies = filteredAnomalies.filter(a => a.timestamp >= startDate);
    }
    
    if (end_time) {
      const endDate = new Date(end_time);
      filteredAnomalies = filteredAnomalies.filter(a => a.timestamp <= endDate);
    }
    
    if (is_resolved !== undefined) {
      filteredAnomalies = filteredAnomalies.filter(a => a.is_resolved === is_resolved);
    }
    
    // 按时间排序并限制结果数量
    filteredAnomalies.sort((a, b) => b.timestamp - a.timestamp);
    
    return Promise.resolve(filteredAnomalies.slice(0, limit));
  }

  /**
   * 获取API列表
   * @returns {Promise<Array>} API列表
   */
  async getApiList() {
    this._checkConnection();
    
    // 提取唯一的API名称
    const apiNames = new Set();
    
    this.data.api_metrics.forEach(metric => {
      if (metric.api_name) {
        apiNames.add(metric.api_name);
      }
    });
    
    return Promise.resolve(Array.from(apiNames).sort());
  }

  /**
   * 清理旧数据
   * @returns {Promise<void>}
   */
  async cleanupOldData() {
    this._checkConnection();
    
    const now = new Date();
    const rawDataCutoff = new Date(now.getTime() - this.retentionPolicy.raw_data_ms);
    const aggregatedDataCutoff = new Date(now.getTime() - this.retentionPolicy.aggregated_data_ms);
    
    // 清理原始指标
    const originalRawCount = this.data.api_metrics.length;
    this.data.api_metrics = this.data.api_metrics.filter(m => m.timestamp > rawDataCutoff);
    const removedRawCount = originalRawCount - this.data.api_metrics.length;
    
    // 清理自定义指标
    const originalCustomCount = this.data.custom_metrics.length;
    this.data.custom_metrics = this.data.custom_metrics.filter(m => m.timestamp > rawDataCutoff);
    const removedCustomCount = originalCustomCount - this.data.custom_metrics.length;
    
    // 清理健康检查指标
    const originalHealthCheckCount = this.data.health_check_metrics.length;
    this.data.health_check_metrics = this.data.health_check_metrics.filter(m => m.timestamp > rawDataCutoff);
    const removedHealthCheckCount = originalHealthCheckCount - this.data.health_check_metrics.length;
    
    // 清理聚合指标
    const originalAggregatedCount = this.data.api_metrics_aggregated.length;
    this.data.api_metrics_aggregated = this.data.api_metrics_aggregated.filter(m => m.interval > aggregatedDataCutoff);
    const removedAggregatedCount = originalAggregatedCount - this.data.api_metrics_aggregated.length;
    
    // 清理异常数据
    const originalAnomalyCount = this.data.anomalies.length;
    this.data.anomalies = this.data.anomalies.filter(a => a.timestamp > aggregatedDataCutoff);
    const removedAnomalyCount = originalAnomalyCount - this.data.anomalies.length;
    
    if (removedRawCount > 0 || removedCustomCount > 0 || removedHealthCheckCount > 0 || 
        removedAggregatedCount > 0 || removedAnomalyCount > 0) {
      this.logger.info(
        `已清理旧数据: 原始指标 ${removedRawCount}, 自定义指标 ${removedCustomCount}, ` +
        `健康检查指标 ${removedHealthCheckCount}, 聚合指标 ${removedAggregatedCount}, ` +
        `异常 ${removedAnomalyCount}`
      );
    }
    
    return Promise.resolve();
  }

  /**
   * 检查连接状态
   * @private
   */
  _checkConnection() {
    if (!this.connected) {
      throw new Error('内存存储未连接');
    }
  }
}

module.exports = MemoryStorage; 