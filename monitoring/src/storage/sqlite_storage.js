/**
 * SQLite存储实现 - 使用SQLite存储监控数据
 */

const sqlite3 = require('sqlite3');
const path = require('path');
const fs = require('fs');

class SQLiteStorage {
  /**
   * 创建SQLite存储
   * @param {Object} config - 配置
   * @param {Object} logger - 日志记录器
   */
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.db = null;
    
    // 数据库文件路径
    this.dbPath = config.connection?.file_path || path.join(__dirname, '../../../data/monitoring.db');
    
    // 保留策略（天）
    this.retentionPolicy = {
      raw_data_days: config.retention_policy?.raw_data_days || 30,
      aggregated_data_days: config.retention_policy?.aggregated_data_days || 365
    };
    
    // 清理定时器
    this.cleanupInterval = null;
  }

  /**
   * 连接到SQLite数据库
   * @returns {Promise<void>}
   */
  async connect() {
    try {
      // 确保目录存在
      const dbDir = path.dirname(this.dbPath);
      if (!fs.existsSync(dbDir)) {
        fs.mkdirSync(dbDir, { recursive: true });
      }
      
      // 创建连接
      this.db = await this._createConnection();
      
      // 初始化表结构
      await this._initializeDatabase();
      
      // 设置定期清理
      this.cleanupInterval = setInterval(() => {
        this.cleanupOldData().catch(err => 
          this.logger.error('清理旧数据时出错:', err)
        );
      }, 24 * 60 * 60 * 1000); // 每天清理一次
      
      this.cleanupInterval.unref(); // 不阻止进程退出
      
      this.logger.info(`SQLite存储已连接: ${this.dbPath}`);
    } catch (error) {
      this.logger.error('连接到SQLite数据库时出错:', error);
      throw error;
    }
  }

  /**
   * 创建数据库连接
   * @returns {Promise<Object>} 数据库连接
   * @private
   */
  _createConnection() {
    return new Promise((resolve, reject) => {
      const db = new sqlite3.Database(this.dbPath, (err) => {
        if (err) {
          reject(err);
        } else {
          // 启用外键约束
          db.run('PRAGMA foreign_keys = ON', (pragmaErr) => {
            if (pragmaErr) {
              reject(pragmaErr);
            } else {
              resolve(db);
            }
          });
        }
      });
    });
  }

  /**
   * 断开SQLite连接
   * @returns {Promise<void>}
   */
  async disconnect() {
    if (this.cleanupInterval) {
      clearInterval(this.cleanupInterval);
      this.cleanupInterval = null;
    }
    
    if (this.db) {
      return new Promise((resolve, reject) => {
        this.db.close((err) => {
          if (err) {
            this.logger.error('关闭SQLite连接时出错:', err);
            reject(err);
          } else {
            this.db = null;
            this.logger.info('SQLite连接已关闭');
            resolve();
          }
        });
      });
    }
    
    return Promise.resolve();
  }

  /**
   * 初始化数据库结构
   * @returns {Promise<void>}
   * @private
   */
  async _initializeDatabase() {
    try {
      // 创建表结构
      await this._createTables();
      
      // 创建索引
      await this._createIndexes();
      
      this.logger.info('SQLite数据库结构初始化成功');
    } catch (error) {
      this.logger.error('初始化SQLite数据库结构时出错:', error);
      throw error;
    }
  }

  /**
   * 创建表结构
   * @returns {Promise<void>}
   * @private
   */
  async _createTables() {
    const queries = [
      // 原始API指标表
      `CREATE TABLE IF NOT EXISTS api_metrics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        request_id TEXT,
        timestamp DATETIME NOT NULL,
        collected_at DATETIME NOT NULL,
        api_name TEXT,
        method TEXT,
        url TEXT,
        normalized_url TEXT,
        target_url TEXT,
        status_code INTEGER,
        duration INTEGER,
        is_success INTEGER,
        performance_grade TEXT,
        api_type TEXT,
        metadata TEXT
      )`,
      
      // 聚合API指标表
      `CREATE TABLE IF NOT EXISTS api_metrics_aggregated (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        api_name TEXT NOT NULL,
        interval DATETIME NOT NULL,
        total_count INTEGER NOT NULL,
        success_count INTEGER NOT NULL,
        error_count INTEGER NOT NULL,
        error_rate REAL NOT NULL,
        avg_duration REAL,
        p50_duration REAL,
        p95_duration REAL,
        p99_duration REAL,
        min_duration REAL,
        max_duration REAL,
        UNIQUE(api_name, interval)
      )`,
      
      // 自定义指标表
      `CREATE TABLE IF NOT EXISTS custom_metrics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        timestamp DATETIME NOT NULL,
        collected_at DATETIME NOT NULL,
        value REAL,
        tags TEXT,
        metadata TEXT
      )`,
      
      // 健康检查指标表
      `CREATE TABLE IF NOT EXISTS health_check_metrics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint_name TEXT NOT NULL,
        timestamp DATETIME NOT NULL,
        collected_at DATETIME NOT NULL,
        url TEXT NOT NULL,
        status_code INTEGER,
        duration INTEGER,
        is_success INTEGER,
        response_data TEXT,
        error_message TEXT
      )`,
      
      // 异常表
      `CREATE TABLE IF NOT EXISTS anomalies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        metric_id TEXT,
        timestamp DATETIME NOT NULL,
        value REAL,
        threshold REAL,
        message TEXT,
        is_resolved INTEGER DEFAULT 0,
        resolved_at DATETIME,
        metadata TEXT
      )`
    ];
    
    for (const query of queries) {
      await this._run(query);
    }
  }

  /**
   * 创建索引
   * @returns {Promise<void>}
   * @private
   */
  async _createIndexes() {
    const queries = [
      // API指标表索引
      `CREATE INDEX IF NOT EXISTS idx_api_metrics_timestamp ON api_metrics (timestamp)`,
      `CREATE INDEX IF NOT EXISTS idx_api_metrics_api_name ON api_metrics (api_name)`,
      `CREATE INDEX IF NOT EXISTS idx_api_metrics_url ON api_metrics (url)`,
      `CREATE INDEX IF NOT EXISTS idx_api_metrics_status ON api_metrics (status_code)`,
      
      // 聚合指标表索引
      `CREATE INDEX IF NOT EXISTS idx_api_metrics_agg_interval ON api_metrics_aggregated (interval)`,
      `CREATE INDEX IF NOT EXISTS idx_api_metrics_agg_api_name ON api_metrics_aggregated (api_name)`,
      
      // 自定义指标表索引
      `CREATE INDEX IF NOT EXISTS idx_custom_metrics_timestamp ON custom_metrics (timestamp)`,
      `CREATE INDEX IF NOT EXISTS idx_custom_metrics_name ON custom_metrics (name)`,
      
      // 健康检查指标表索引
      `CREATE INDEX IF NOT EXISTS idx_health_check_timestamp ON health_check_metrics (timestamp)`,
      `CREATE INDEX IF NOT EXISTS idx_health_check_endpoint ON health_check_metrics (endpoint_name)`,
      
      // 异常表索引
      `CREATE INDEX IF NOT EXISTS idx_anomalies_timestamp ON anomalies (timestamp)`,
      `CREATE INDEX IF NOT EXISTS idx_anomalies_type ON anomalies (type)`,
      `CREATE INDEX IF NOT EXISTS idx_anomalies_resolved ON anomalies (is_resolved)`
    ];
    
    for (const query of queries) {
      await this._run(query);
    }
  }

  /**
   * 保存原始指标数据
   * @param {Array} metrics - 指标数据数组
   * @returns {Promise<void>}
   */
  async saveRawMetrics(metrics) {
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const query = `
      INSERT INTO api_metrics (
        request_id, timestamp, collected_at, api_name, method, url,
        normalized_url, target_url, status_code, duration, is_success,
        performance_grade, api_type, metadata
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;
    
    try {
      await this._beginTransaction();
      
      for (const metric of metrics) {
        await this._run(query, [
          metric.requestId || null,
          this._formatDate(metric.timestamp),
          this._formatDate(metric.collected_at),
          metric.name || (metric.targetUrl ? new URL(metric.targetUrl).hostname : 'unknown'),
          metric.method || null,
          metric.url || null,
          metric.normalized_url || null,
          metric.targetUrl || null,
          metric.statusCode || null,
          metric.duration || null,
          metric.is_success ? 1 : 0,
          metric.performance_grade || null,
          metric.apiType || null,
          metric.metadata ? JSON.stringify(metric.metadata) : null
        ]);
      }
      
      await this._commitTransaction();
      this.logger.debug(`已保存 ${metrics.length} 条原始指标`);
    } catch (error) {
      await this._rollbackTransaction();
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const query = `
      INSERT INTO api_metrics_aggregated (
        api_name, interval, total_count, success_count, error_count,
        error_rate, avg_duration, p50_duration, p95_duration, p99_duration,
        min_duration, max_duration
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ON CONFLICT(api_name, interval) DO UPDATE SET
        total_count = excluded.total_count,
        success_count = excluded.success_count,
        error_count = excluded.error_count,
        error_rate = excluded.error_rate,
        avg_duration = excluded.avg_duration,
        p50_duration = excluded.p50_duration,
        p95_duration = excluded.p95_duration,
        p99_duration = excluded.p99_duration,
        min_duration = excluded.min_duration,
        max_duration = excluded.max_duration
    `;
    
    try {
      await this._beginTransaction();
      
      for (const metric of metrics) {
        await this._run(query, [
          metric.api_name,
          this._formatDate(metric.interval || metric.timestamp),
          metric.total_count,
          metric.success_count,
          metric.error_count,
          metric.error_rate,
          metric.avg_duration,
          metric.p50_duration,
          metric.p95_duration,
          metric.p99_duration,
          metric.min_duration,
          metric.max_duration
        ]);
      }
      
      await this._commitTransaction();
      this.logger.debug(`已保存 ${metrics.length} 条聚合指标`);
    } catch (error) {
      await this._rollbackTransaction();
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const query = `
      INSERT INTO custom_metrics (
        name, timestamp, collected_at, value, tags, metadata
      ) VALUES (?, ?, ?, ?, ?, ?)
    `;
    
    try {
      await this._beginTransaction();
      
      for (const metric of metrics) {
        await this._run(query, [
          metric.name,
          this._formatDate(metric.timestamp),
          this._formatDate(metric.collected_at),
          metric.value,
          metric.tags ? JSON.stringify(metric.tags) : null,
          metric.metadata ? JSON.stringify(metric.metadata) : null
        ]);
      }
      
      await this._commitTransaction();
      this.logger.debug(`已保存 ${metrics.length} 条自定义指标`);
    } catch (error) {
      await this._rollbackTransaction();
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const query = `
      INSERT INTO health_check_metrics (
        endpoint_name, timestamp, collected_at, url, status_code,
        duration, is_success, response_data, error_message
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;
    
    try {
      await this._beginTransaction();
      
      for (const metric of metrics) {
        await this._run(query, [
          metric.endpoint_name,
          this._formatDate(metric.timestamp),
          this._formatDate(metric.collected_at),
          metric.url,
          metric.status_code,
          metric.duration,
          metric.is_success ? 1 : 0,
          metric.response_data,
          metric.error_message
        ]);
      }
      
      await this._commitTransaction();
      this.logger.debug(`已保存 ${metrics.length} 条健康检查指标`);
    } catch (error) {
      await this._rollbackTransaction();
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!anomalies || anomalies.length === 0) {
      return;
    }
    
    const query = `
      INSERT INTO anomalies (
        type, metric_id, timestamp, value, threshold,
        message, is_resolved, metadata
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `;
    
    try {
      await this._beginTransaction();
      
      for (const anomaly of anomalies) {
        await this._run(query, [
          anomaly.type,
          anomaly.metric_id,
          this._formatDate(anomaly.timestamp),
          anomaly.value,
          anomaly.threshold,
          anomaly.message,
          anomaly.is_resolved ? 1 : 0,
          anomaly.metadata ? JSON.stringify(anomaly.metadata) : null
        ]);
      }
      
      await this._commitTransaction();
      this.logger.debug(`已保存 ${anomalies.length} 条异常数据`);
    } catch (error) {
      await this._rollbackTransaction();
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    const { api_name, start_time, end_time, method, url, status_code, limit = 1000 } = query;
    
    let sql = `
      SELECT * FROM api_metrics
      WHERE 1=1
    `;
    
    const params = [];
    
    if (api_name) {
      sql += ` AND api_name = ?`;
      params.push(api_name);
    }
    
    if (start_time) {
      sql += ` AND timestamp >= ?`;
      params.push(this._formatDate(start_time));
    }
    
    if (end_time) {
      sql += ` AND timestamp <= ?`;
      params.push(this._formatDate(end_time));
    }
    
    if (method) {
      sql += ` AND method = ?`;
      params.push(method);
    }
    
    if (url) {
      sql += ` AND url LIKE ?`;
      params.push(`%${url}%`);
    }
    
    if (status_code) {
      sql += ` AND status_code = ?`;
      params.push(status_code);
    }
    
    sql += ` ORDER BY timestamp DESC LIMIT ?`;
    params.push(limit);
    
    try {
      const rows = await this._all(sql, params);
      
      // 处理布尔值和JSON字段
      return rows.map(row => ({
        ...row,
        is_success: Boolean(row.is_success),
        metadata: row.metadata ? JSON.parse(row.metadata) : null
      }));
    } catch (error) {
      this.logger.error('获取指标数据时出错:', error);
      throw error;
    }
  }

  /**
   * 获取聚合指标
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 聚合指标数组
   */
  async getAggregatedMetrics(query) {
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    const { api_name, start_time, end_time, limit = 1000 } = query;
    
    let sql = `
      SELECT * FROM api_metrics_aggregated
      WHERE 1=1
    `;
    
    const params = [];
    
    if (api_name) {
      sql += ` AND api_name = ?`;
      params.push(api_name);
    }
    
    if (start_time) {
      sql += ` AND interval >= ?`;
      params.push(this._formatDate(start_time));
    }
    
    if (end_time) {
      sql += ` AND interval <= ?`;
      params.push(this._formatDate(end_time));
    }
    
    sql += ` ORDER BY interval DESC LIMIT ?`;
    params.push(limit);
    
    try {
      return await this._all(sql, params);
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    const { type, start_time, end_time, is_resolved, limit = 1000 } = query;
    
    let sql = `
      SELECT * FROM anomalies
      WHERE 1=1
    `;
    
    const params = [];
    
    if (type) {
      sql += ` AND type = ?`;
      params.push(type);
    }
    
    if (start_time) {
      sql += ` AND timestamp >= ?`;
      params.push(this._formatDate(start_time));
    }
    
    if (end_time) {
      sql += ` AND timestamp <= ?`;
      params.push(this._formatDate(end_time));
    }
    
    if (is_resolved !== undefined) {
      sql += ` AND is_resolved = ?`;
      params.push(is_resolved ? 1 : 0);
    }
    
    sql += ` ORDER BY timestamp DESC LIMIT ?`;
    params.push(limit);
    
    try {
      const rows = await this._all(sql, params);
      
      // 处理布尔值和JSON字段
      return rows.map(row => ({
        ...row,
        is_resolved: Boolean(row.is_resolved),
        metadata: row.metadata ? JSON.parse(row.metadata) : null
      }));
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    try {
      const rows = await this._all(`
        SELECT DISTINCT api_name FROM api_metrics
        WHERE api_name IS NOT NULL
        ORDER BY api_name
      `);
      
      return rows.map(row => row.api_name);
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
    if (!this.db) {
      throw new Error('数据库连接未初始化');
    }
    
    const rawDataCutoff = this._formatDate(new Date(Date.now() - this.retentionPolicy.raw_data_days * 24 * 60 * 60 * 1000));
    const aggregatedDataCutoff = this._formatDate(new Date(Date.now() - this.retentionPolicy.aggregated_data_days * 24 * 60 * 60 * 1000));
    
    try {
      await this._beginTransaction();
      
      // 清理原始指标
      const apiMetricsResult = await this._run(`
        DELETE FROM api_metrics
        WHERE timestamp < ?
      `, [rawDataCutoff]);
      
      // 清理自定义指标
      const customMetricsResult = await this._run(`
        DELETE FROM custom_metrics
        WHERE timestamp < ?
      `, [rawDataCutoff]);
      
      // 清理健康检查指标
      const healthCheckResult = await this._run(`
        DELETE FROM health_check_metrics
        WHERE timestamp < ?
      `, [rawDataCutoff]);
      
      // 清理聚合指标
      const aggregatedResult = await this._run(`
        DELETE FROM api_metrics_aggregated
        WHERE interval < ?
      `, [aggregatedDataCutoff]);
      
      // 清理异常
      const anomaliesResult = await this._run(`
        DELETE FROM anomalies
        WHERE timestamp < ?
      `, [aggregatedDataCutoff]);
      
      await this._commitTransaction();
      
      // 获取删除的行数
      const removedRawCount = apiMetricsResult.changes;
      const removedCustomCount = customMetricsResult.changes;
      const removedHealthCheckCount = healthCheckResult.changes;
      const removedAggregatedCount = aggregatedResult.changes;
      const removedAnomalyCount = anomaliesResult.changes;
      
      if (removedRawCount > 0 || removedCustomCount > 0 || removedHealthCheckCount > 0 || 
          removedAggregatedCount > 0 || removedAnomalyCount > 0) {
        this.logger.info(
          `已清理旧数据: 原始指标 ${removedRawCount}, 自定义指标 ${removedCustomCount}, ` +
          `健康检查指标 ${removedHealthCheckCount}, 聚合指标 ${removedAggregatedCount}, ` +
          `异常 ${removedAnomalyCount}`
        );
      }
    } catch (error) {
      await this._rollbackTransaction();
      this.logger.error('清理旧数据时出错:', error);
      throw error;
    }
  }

  /**
   * 执行SQL语句
   * @param {string} sql - SQL语句
   * @param {Array} params - 参数
   * @returns {Promise<Object>} 结果
   * @private
   */
  _run(sql, params = []) {
    return new Promise((resolve, reject) => {
      this.db.run(sql, params, function(err) {
        if (err) {
          reject(err);
        } else {
          resolve({ lastID: this.lastID, changes: this.changes });
        }
      });
    });
  }

  /**
   * 查询单条记录
   * @param {string} sql - SQL语句
   * @param {Array} params - 参数
   * @returns {Promise<Object>} 记录
   * @private
   */
  _get(sql, params = []) {
    return new Promise((resolve, reject) => {
      this.db.get(sql, params, (err, row) => {
        if (err) {
          reject(err);
        } else {
          resolve(row);
        }
      });
    });
  }

  /**
   * 查询多条记录
   * @param {string} sql - SQL语句
   * @param {Array} params - 参数
   * @returns {Promise<Array>} 记录数组
   * @private
   */
  _all(sql, params = []) {
    return new Promise((resolve, reject) => {
      this.db.all(sql, params, (err, rows) => {
        if (err) {
          reject(err);
        } else {
          resolve(rows);
        }
      });
    });
  }

  /**
   * 开始事务
   * @returns {Promise<void>}
   * @private
   */
  _beginTransaction() {
    return this._run('BEGIN TRANSACTION');
  }

  /**
   * 提交事务
   * @returns {Promise<void>}
   * @private
   */
  _commitTransaction() {
    return this._run('COMMIT');
  }

  /**
   * 回滚事务
   * @returns {Promise<void>}
   * @private
   */
  _rollbackTransaction() {
    return this._run('ROLLBACK')
      .catch(err => {
        this.logger.error('回滚事务时出错:', err);
      });
  }

  /**
   * 格式化日期为SQLite日期格式
   * @param {Date|string} date - 日期
   * @returns {string} 格式化后的日期
   * @private
   */
  _formatDate(date) {
    if (!date) {
      return new Date().toISOString();
    }
    
    if (typeof date === 'string') {
      return new Date(date).toISOString();
    }
    
    return date.toISOString();
  }
}

module.exports = SQLiteStorage; 