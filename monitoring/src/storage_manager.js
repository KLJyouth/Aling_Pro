/**
 * AlingAi API监控系统 - 存储管理器
 */

const fs = require('fs');
const path = require('path');
const sqlite3 = require('sqlite3');
const { open } = require('sqlite');

class StorageManager {
  /**
   * 创建存储管理器
   * @param {Object} config - 存储配置
   * @param {Object} logger - 日志记录器
   */
  constructor(config, logger) {
    this.config = config || {};
    this.logger = logger;
    this.db = null;
    this.initialized = false;
  }

  /**
   * 初始化存储管理器
   * @returns {Promise<void>}
   */
  async init() {
    try {
      this.logger.info('初始化存储管理器...');

      // 确保目录存在
      const dbPath = this.config.connection?.filename || path.join(__dirname, '../data/monitoring.db');
      const dbDir = path.dirname(dbPath);
      
      if (!fs.existsSync(dbDir)) {
        fs.mkdirSync(dbDir, { recursive: true });
        this.logger.info(`创建数据目录: ${dbDir}`);
      }

      // 连接数据库
      this.db = await open({
        filename: dbPath,
        driver: sqlite3.Database
      });

      // 创建必要的表
      await this._createTables();

      this.initialized = true;
      this.logger.info('存储管理器初始化完成');
    } catch (error) {
      this.logger.error('初始化存储管理器时出错:', error);
      throw error;
    }
  }

  /**
   * 创建必要的数据表
   * @private
   * @returns {Promise<void>}
   */
  async _createTables() {
    try {
      // API表
      await this.db.exec(`
        CREATE TABLE IF NOT EXISTS apis (
          id TEXT PRIMARY KEY,
          name TEXT NOT NULL UNIQUE,
          url TEXT NOT NULL,
          method TEXT DEFAULT 'GET',
          headers TEXT,
          body TEXT,
          timeout INTEGER DEFAULT 5000,
          interval INTEGER DEFAULT 300,
          expected_status_code INTEGER DEFAULT 200,
          expected_response TEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
      `);

      // 指标表
      await this.db.exec(`
        CREATE TABLE IF NOT EXISTS metrics (
          id TEXT PRIMARY KEY,
          api_id TEXT NOT NULL,
          url TEXT NOT NULL,
          method TEXT NOT NULL,
          status_code INTEGER,
          response_time INTEGER,
          response_size INTEGER,
          error TEXT,
          success INTEGER DEFAULT 0,
          timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (api_id) REFERENCES apis(id)
        )
      `);

      // 告警表
      await this.db.exec(`
        CREATE TABLE IF NOT EXISTS alerts (
          id TEXT PRIMARY KEY,
          api_id TEXT NOT NULL,
          level TEXT NOT NULL,
          title TEXT NOT NULL,
          message TEXT NOT NULL,
          details TEXT,
          status TEXT DEFAULT 'pending',
          timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          resolved_at TIMESTAMP,
          FOREIGN KEY (api_id) REFERENCES apis(id)
        )
      `);

      // 健康检查表
      await this.db.exec(`
        CREATE TABLE IF NOT EXISTS health_checks (
          id TEXT PRIMARY KEY,
          name TEXT NOT NULL,
          status TEXT NOT NULL,
          response_time INTEGER,
          details TEXT,
          timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
      `);

      // 创建索引
      await this.db.exec(`
        CREATE INDEX IF NOT EXISTS idx_metrics_api_id ON metrics(api_id);
        CREATE INDEX IF NOT EXISTS idx_metrics_timestamp ON metrics(timestamp);
        CREATE INDEX IF NOT EXISTS idx_alerts_api_id ON alerts(api_id);
        CREATE INDEX IF NOT EXISTS idx_alerts_timestamp ON alerts(timestamp);
        CREATE INDEX IF NOT EXISTS idx_health_checks_timestamp ON health_checks(timestamp);
      `);

      this.logger.info('数据表创建/验证完成');
    } catch (error) {
      this.logger.error('创建数据表时出错:', error);
      throw error;
    }
  }

  /**
   * 关闭数据库连接
   * @returns {Promise<void>}
   */
  async close() {
    if (this.db) {
      await this.db.close();
      this.db = null;
      this.initialized = false;
      this.logger.info('数据库连接已关闭');
    }
  }

  /**
   * 保存API配置
   * @param {Object} api - API配置对象
   * @returns {Promise<Object>} 保存的API对象
   */
  async saveApi(api) {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      const id = api.id || Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
      const now = new Date().toISOString();

      const result = await this.db.run(`
        INSERT INTO apis (
          id, name, url, method, headers, body, timeout, 
          interval, expected_status_code, expected_response, 
          created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON CONFLICT(id) DO UPDATE SET
          name = excluded.name,
          url = excluded.url,
          method = excluded.method,
          headers = excluded.headers,
          body = excluded.body,
          timeout = excluded.timeout,
          interval = excluded.interval,
          expected_status_code = excluded.expected_status_code,
          expected_response = excluded.expected_response,
          updated_at = excluded.updated_at
      `, [
        id,
        api.name,
        api.url,
        api.method || 'GET',
        api.headers ? JSON.stringify(api.headers) : null,
        api.body || null,
        api.timeout || 5000,
        api.interval || 300,
        api.expected_status_code || 200,
        api.expected_response || null,
        now,
        now
      ]);

      this.logger.info(`API配置已保存: ${api.name}`);
      return { ...api, id };
    } catch (error) {
      this.logger.error(`保存API配置时出错: ${api.name}`, error);
      throw error;
    }
  }

  /**
   * 获取API列表
   * @returns {Promise<Array>} API列表
   */
  async getApiList() {
    if (!this.initialized) {
      return [];
    }

    try {
      const apis = await this.db.all(`SELECT * FROM apis ORDER BY name`);
      
      // 解析JSON字段
      return apis.map(api => ({
        ...api,
        headers: api.headers ? JSON.parse(api.headers) : null
      }));
    } catch (error) {
      this.logger.error('获取API列表时出错:', error);
      return [];
    }
  }

  /**
   * 删除API
   * @param {string} apiId - API ID
   * @returns {Promise<boolean>} 是否删除成功
   */
  async deleteApi(apiId) {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      // 首先删除相关的指标和告警
      await this.db.run(`DELETE FROM metrics WHERE api_id = ?`, [apiId]);
      await this.db.run(`DELETE FROM alerts WHERE api_id = ?`, [apiId]);
      
      // 然后删除API本身
      const result = await this.db.run(`DELETE FROM apis WHERE id = ?`, [apiId]);
      
      this.logger.info(`API已删除: ${apiId}, 影响行数: ${result.changes}`);
      return result.changes > 0;
    } catch (error) {
      this.logger.error(`删除API时出错: ${apiId}`, error);
      throw error;
    }
  }

  /**
   * 保存指标数据
   * @param {Object} metric - 指标数据对象
   * @returns {Promise<Object>} 保存的指标对象
   */
  async saveMetric(metric) {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      const id = metric.id || Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
      const timestamp = metric.timestamp || new Date().toISOString();

      await this.db.run(`
        INSERT INTO metrics (
          id, api_id, url, method, status_code, response_time,
          response_size, error, success, timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        id,
        metric.api_id,
        metric.url,
        metric.method,
        metric.status_code || null,
        metric.response_time || null,
        metric.response_size || null,
        metric.error || null,
        metric.success ? 1 : 0,
        timestamp
      ]);

      return { ...metric, id, timestamp };
    } catch (error) {
      this.logger.error('保存指标数据时出错:', error);
      throw error;
    }
  }

  /**
   * 获取指标数据
   * @param {Object} options - 查询选项
   * @returns {Promise<Array>} 指标数据列表
   */
  async getMetrics(options = {}) {
    if (!this.initialized) {
      return [];
    }

    try {
      let query = `SELECT * FROM metrics`;
      const params = [];
      const conditions = [];

      if (options.api_id) {
        conditions.push(`api_id = ?`);
        params.push(options.api_id);
      }

      if (options.api_name) {
        query = `
          SELECT m.* FROM metrics m
          JOIN apis a ON m.api_id = a.id
          WHERE a.name = ?
        `;
        params.push(options.api_name);
      }

      if (options.start_time) {
        conditions.push(`timestamp >= ?`);
        params.push(typeof options.start_time === 'string' ? options.start_time : options.start_time.toISOString());
      }

      if (options.end_time) {
        conditions.push(`timestamp <= ?`);
        params.push(typeof options.end_time === 'string' ? options.end_time : options.end_time.toISOString());
      }

      if (options.success !== undefined) {
        conditions.push(`success = ?`);
        params.push(options.success ? 1 : 0);
      }

      if (conditions.length > 0) {
        // 如果查询已包含WHERE子句，则使用AND连接条件
        if (query.includes('WHERE')) {
          query += ` AND ${conditions.join(' AND ')}`;
        } else {
          query += ` WHERE ${conditions.join(' AND ')}`;
        }
      }

      query += ` ORDER BY timestamp DESC`;

      if (options.limit) {
        query += ` LIMIT ?`;
        params.push(options.limit);
      }

      return await this.db.all(query, params);
    } catch (error) {
      this.logger.error('获取指标数据时出错:', error);
      return [];
    }
  }

  /**
   * 获取聚合指标数据
   * @param {Object} options - 查询选项
   * @returns {Promise<Array>} 聚合指标数据
   */
  async getAggregatedMetrics(options = {}) {
    if (!this.initialized) {
      return [];
    }

    try {
      let query = `
        SELECT 
          api_id,
          strftime('%Y-%m-%d %H:00:00', timestamp) as hour,
          AVG(response_time) as avg_response_time,
          MIN(response_time) as min_response_time,
          MAX(response_time) as max_response_time,
          SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count,
          COUNT(*) as total_count,
          (SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as success_rate
        FROM metrics
      `;

      const params = [];
      const conditions = [];

      if (options.api_id) {
        conditions.push(`api_id = ?`);
        params.push(options.api_id);
      }

      if (options.api_name) {
        query = `
          SELECT 
            m.api_id,
            strftime('%Y-%m-%d %H:00:00', m.timestamp) as hour,
            AVG(m.response_time) as avg_response_time,
            MIN(m.response_time) as min_response_time,
            MAX(m.response_time) as max_response_time,
            SUM(CASE WHEN m.success = 1 THEN 1 ELSE 0 END) as success_count,
            COUNT(*) as total_count,
            (SUM(CASE WHEN m.success = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as success_rate
          FROM metrics m
          JOIN apis a ON m.api_id = a.id
          WHERE a.name = ?
        `;
        params.push(options.api_name);
        
        // 由于我们已经添加了WHERE子句，我们需要更新条件添加逻辑
        if (conditions.length > 0) {
          query += ` AND ${conditions.join(' AND ')}`;
        }
        
        conditions = []; // 清空条件，因为已经处理过
      }

      if (options.start_time) {
        conditions.push(`timestamp >= ?`);
        params.push(typeof options.start_time === 'string' ? options.start_time : options.start_time.toISOString());
      }

      if (options.end_time) {
        conditions.push(`timestamp <= ?`);
        params.push(typeof options.end_time === 'string' ? options.end_time : options.end_time.toISOString());
      }

      if (conditions.length > 0) {
        // 如果查询已包含WHERE子句，则使用AND连接条件
        if (query.includes('WHERE')) {
          query += ` AND ${conditions.join(' AND ')}`;
        } else {
          query += ` WHERE ${conditions.join(' AND ')}`;
        }
      }

      query += ` GROUP BY api_id, hour ORDER BY hour DESC`;

      if (options.limit) {
        query += ` LIMIT ?`;
        params.push(options.limit);
      }

      return await this.db.all(query, params);
    } catch (error) {
      this.logger.error('获取聚合指标数据时出错:', error);
      return [];
    }
  }

  /**
   * 保存告警
   * @param {Object} alert - 告警对象
   * @returns {Promise<Object>} 保存的告警对象
   */
  async saveAlert(alert) {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      const id = alert.id || Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
      const timestamp = alert.timestamp || new Date().toISOString();

      await this.db.run(`
        INSERT INTO alerts (
          id, api_id, level, title, message, details,
          status, timestamp, resolved_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        id,
        alert.api_id,
        alert.level,
        alert.title,
        alert.message,
        alert.details ? JSON.stringify(alert.details) : null,
        alert.status || 'pending',
        timestamp,
        alert.resolved_at || null
      ]);

      return { ...alert, id, timestamp };
    } catch (error) {
      this.logger.error('保存告警时出错:', error);
      throw error;
    }
  }

  /**
   * 获取告警历史
   * @param {Object} options - 查询选项
   * @returns {Promise<Array>} 告警列表
   */
  async getAlerts(options = {}) {
    if (!this.initialized) {
      return [];
    }

    try {
      let query = `
        SELECT a.*, api.name as api_name
        FROM alerts a
        JOIN apis api ON a.api_id = api.id
      `;
      
      const params = [];
      const conditions = [];

      if (options.api_id) {
        conditions.push(`a.api_id = ?`);
        params.push(options.api_id);
      }

      if (options.api_name) {
        conditions.push(`api.name = ?`);
        params.push(options.api_name);
      }

      if (options.level) {
        conditions.push(`a.level = ?`);
        params.push(options.level);
      }

      if (options.status) {
        conditions.push(`a.status = ?`);
        params.push(options.status);
      }

      if (options.start_time) {
        conditions.push(`a.timestamp >= ?`);
        params.push(typeof options.start_time === 'string' ? options.start_time : options.start_time.toISOString());
      }

      if (options.end_time) {
        conditions.push(`a.timestamp <= ?`);
        params.push(typeof options.end_time === 'string' ? options.end_time : options.end_time.toISOString());
      }

      if (conditions.length > 0) {
        query += ` WHERE ${conditions.join(' AND ')}`;
      }

      query += ` ORDER BY a.timestamp DESC`;

      if (options.limit) {
        query += ` LIMIT ?`;
        params.push(options.limit);
      }

      const alerts = await this.db.all(query, params);
      
      // 解析JSON字段
      return alerts.map(alert => ({
        ...alert,
        details: alert.details ? JSON.parse(alert.details) : null
      }));
    } catch (error) {
      this.logger.error('获取告警历史时出错:', error);
      return [];
    }
  }

  /**
   * 更新告警状态
   * @param {string} alertId - 告警ID
   * @param {string} status - 新状态
   * @returns {Promise<boolean>} 是否更新成功
   */
  async updateAlertStatus(alertId, status) {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      const now = new Date().toISOString();
      let result;

      if (status === 'resolved') {
        result = await this.db.run(`
          UPDATE alerts SET status = ?, resolved_at = ? WHERE id = ?
        `, [status, now, alertId]);
      } else {
        result = await this.db.run(`
          UPDATE alerts SET status = ? WHERE id = ?
        `, [status, alertId]);
      }

      this.logger.info(`告警状态已更新: ${alertId} -> ${status}`);
      return result.changes > 0;
    } catch (error) {
      this.logger.error(`更新告警状态时出错: ${alertId}`, error);
      throw error;
    }
  }

  /**
   * 保存健康检查结果
   * @param {Object} check - 健康检查结果对象
   * @returns {Promise<Object>} 保存的健康检查结果
   */
  async saveHealthCheck(check) {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      const id = check.id || Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
      const timestamp = check.timestamp || new Date().toISOString();

      await this.db.run(`
        INSERT INTO health_checks (
          id, name, status, response_time, details, timestamp
        ) VALUES (?, ?, ?, ?, ?, ?)
      `, [
        id,
        check.name,
        check.status,
        check.response_time || null,
        check.details ? JSON.stringify(check.details) : null,
        timestamp
      ]);

      return { ...check, id, timestamp };
    } catch (error) {
      this.logger.error('保存健康检查结果时出错:', error);
      throw error;
    }
  }

  /**
   * 获取健康检查指标
   * @param {Object} options - 查询选项
   * @returns {Promise<Array>} 健康检查指标列表
   */
  async getHealthCheckMetrics(options = {}) {
    if (!this.initialized) {
      return [];
    }

    try {
      let query = `SELECT * FROM health_checks`;
      const params = [];
      const conditions = [];

      if (options.name) {
        conditions.push(`name = ?`);
        params.push(options.name);
      }

      if (options.status) {
        conditions.push(`status = ?`);
        params.push(options.status);
      }

      if (options.start_time) {
        conditions.push(`timestamp >= ?`);
        params.push(typeof options.start_time === 'string' ? options.start_time : options.start_time.toISOString());
      }

      if (options.end_time) {
        conditions.push(`timestamp <= ?`);
        params.push(typeof options.end_time === 'string' ? options.end_time : options.end_time.toISOString());
      }

      if (conditions.length > 0) {
        query += ` WHERE ${conditions.join(' AND ')}`;
      }

      query += ` ORDER BY timestamp DESC`;

      if (options.limit) {
        query += ` LIMIT ?`;
        params.push(options.limit);
      }

      const checks = await this.db.all(query, params);
      
      // 解析JSON字段
      return checks.map(check => ({
        ...check,
        details: check.details ? JSON.parse(check.details) : null
      }));
    } catch (error) {
      this.logger.error('获取健康检查指标时出错:', error);
      return [];
    }
  }

  /**
   * 清理过期数据
   * @returns {Promise<Object>} 清理结果
   */
  async cleanupExpiredData() {
    if (!this.initialized) {
      throw new Error('存储管理器未初始化');
    }

    try {
      const retention = this.config.options?.dataRetention || 90; // 默认保留90天
      
      // 如果设置为0，表示永不过期
      if (retention === 0) {
        this.logger.info('数据保留期设置为永久，跳过清理');
        return { metrics: 0, alerts: 0, health_checks: 0 };
      }

      const cutoffDate = new Date();
      cutoffDate.setDate(cutoffDate.getDate() - retention);
      const cutoffTimestamp = cutoffDate.toISOString();

      // 清理指标数据
      const metricsResult = await this.db.run(`
        DELETE FROM metrics WHERE timestamp < ?
      `, [cutoffTimestamp]);

      // 清理已解决的告警
      const alertsResult = await this.db.run(`
        DELETE FROM alerts WHERE status = 'resolved' AND timestamp < ?
      `, [cutoffTimestamp]);

      // 清理健康检查数据
      const healthChecksResult = await this.db.run(`
        DELETE FROM health_checks WHERE timestamp < ?
      `, [cutoffTimestamp]);

      const result = {
        metrics: metricsResult.changes,
        alerts: alertsResult.changes,
        health_checks: healthChecksResult.changes
      };

      this.logger.info(`数据清理完成: 删除了 ${result.metrics} 条指标, ${result.alerts} 条告警, ${result.health_checks} 条健康检查记录`);
      return result;
    } catch (error) {
      this.logger.error('清理过期数据时出错:', error);
      throw error;
    }
  }
}

module.exports = StorageManager; 