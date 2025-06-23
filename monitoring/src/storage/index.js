/**
 * 存储管理器模块 - 管理监控数据的存储
 */

const TimescaleDBStorage = require('./timescale_storage');
const SQLiteStorage = require('./sqlite_storage');
const MemoryStorage = require('./memory_storage');

class StorageManager {
  /**
   * 创建存储管理器
   * @param {Object} config - 存储配置
   * @param {Object} logger - 日志记录器
   */
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.storage = null;
    
    // 根据配置选择存储实现
    this._initializeStorage();
  }

  /**
   * 初始化存储实现
   * @private
   */
  _initializeStorage() {
    const storageType = this.config.type || 'memory';
    
    switch (storageType.toLowerCase()) {
      case 'timescaledb':
        this.logger.info('使用TimescaleDB存储');
        this.storage = new TimescaleDBStorage(this.config, this.logger);
        break;
      case 'sqlite':
        this.logger.info('使用SQLite存储');
        this.storage = new SQLiteStorage(this.config, this.logger);
        break;
      case 'memory':
        this.logger.info('使用内存存储（仅用于开发/测试）');
        this.storage = new MemoryStorage(this.config, this.logger);
        break;
      default:
        this.logger.warn(`未知的存储类型: ${storageType}，使用内存存储`);
        this.storage = new MemoryStorage(this.config, this.logger);
    }
  }

  /**
   * 连接到存储
   * @returns {Promise<void>}
   */
  async connect() {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.connect();
      this.logger.info('已连接到存储');
    } catch (error) {
      this.logger.error('连接到存储时出错:', error);
      throw error;
    }
  }

  /**
   * 断开存储连接
   * @returns {Promise<void>}
   */
  async disconnect() {
    if (!this.storage) {
      return;
    }
    
    try {
      await this.storage.disconnect();
      this.logger.info('已断开存储连接');
    } catch (error) {
      this.logger.error('断开存储连接时出错:', error);
      throw error;
    }
  }

  /**
   * 保存原始指标数据
   * @param {Array} metrics - 指标数据数组
   * @returns {Promise<void>}
   */
  async saveRawMetrics(metrics) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.saveRawMetrics(metrics);
    } catch (error) {
      this.logger.error('保存原始指标时出错:', error);
      throw error;
    }
  }

  /**
   * 保存聚合指标数据
   * @param {Array} metrics - 聚合指标数据数组
   * @returns {Promise<void>}
   */
  async saveAggregatedMetrics(metrics) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.saveAggregatedMetrics(metrics);
    } catch (error) {
      this.logger.error('保存聚合指标时出错:', error);
      throw error;
    }
  }

  /**
   * 保存自定义指标
   * @param {Array} metrics - 自定义指标数组
   * @returns {Promise<void>}
   */
  async saveCustomMetrics(metrics) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.saveCustomMetrics(metrics);
    } catch (error) {
      this.logger.error('保存自定义指标时出错:', error);
      throw error;
    }
  }

  /**
   * 保存健康检查指标
   * @param {Array} metrics - 健康检查指标数组
   * @returns {Promise<void>}
   */
  async saveHealthCheckMetrics(metrics) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.saveHealthCheckMetrics(metrics);
    } catch (error) {
      this.logger.error('保存健康检查指标时出错:', error);
      throw error;
    }
  }

  /**
   * 保存异常数据
   * @param {Array} anomalies - 异常数据数组
   * @returns {Promise<void>}
   */
  async saveAnomalies(anomalies) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.saveAnomalies(anomalies);
    } catch (error) {
      this.logger.error('保存异常数据时出错:', error);
      throw error;
    }
  }

  /**
   * 获取指标数据
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 指标数据数组
   */
  async getMetrics(query) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      return await this.storage.getMetrics(query);
    } catch (error) {
      this.logger.error('获取指标时出错:', error);
      throw error;
    }
  }

  /**
   * 获取聚合指标
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 聚合指标数组
   */
  async getAggregatedMetrics(query) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      return await this.storage.getAggregatedMetrics(query);
    } catch (error) {
      this.logger.error('获取聚合指标时出错:', error);
      throw error;
    }
  }

  /**
   * 获取异常数据
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 异常数据数组
   */
  async getAnomalies(query) {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      return await this.storage.getAnomalies(query);
    } catch (error) {
      this.logger.error('获取异常数据时出错:', error);
      throw error;
    }
  }

  /**
   * 获取API列表
   * @returns {Promise<Array>} API列表
   */
  async getApiList() {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      return await this.storage.getApiList();
    } catch (error) {
      this.logger.error('获取API列表时出错:', error);
      throw error;
    }
  }

  /**
   * 清理旧数据
   * @returns {Promise<void>}
   */
  async cleanupOldData() {
    if (!this.storage) {
      throw new Error('存储未初始化');
    }
    
    try {
      await this.storage.cleanupOldData();
      this.logger.info('已清理旧数据');
    } catch (error) {
      this.logger.error('清理旧数据时出错:', error);
      throw error;
    }
  }
}

module.exports = StorageManager; 