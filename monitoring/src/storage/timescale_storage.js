/**
 * TimescaleDB存储实现 - 使用TimescaleDB存储时序监控数据
 */

const { Pool } = require('pg');
const fs = require('fs');
const path = require('path');

class TimescaleDBStorage {
  /**
   * 创建TimescaleDB存储
   * @param {Object} config - 配置
   * @param {Object} logger - 日志记录器
   */
  constructor(config, logger) {
    this.config = config;
    this.logger = logger;
    this.pool = null;
    
    this.retentionPolicy = {
      raw_data_days: config.retention_policy?.raw_data_days || 30,
      aggregated_data_days: config.retention_policy?.aggregated_data_days || 365
    };
  }

  /**
   * 连接到TimescaleDB
   * @returns {Promise<void>}
   */
  async connect() {
    try {
      // 创建连接池
      this.pool = new Pool({
        host: this.config.connection.host || 'localhost',
        port: this.config.connection.port || 5432,
        database: this.config.connection.database || 'api_monitoring',
        user: this.config.connection.user || 'postgres',
        password: this.config.connection.password || 'password',
        max: 20, // 最大连接数
        idleTimeoutMillis: 30000, // 连接最大空闲时间
        connectionTimeoutMillis: 2000 // 连接超时
      });
      
      // 测试连接
      const client = await this.pool.connect();
      this.logger.info('TimescaleDB连接成功');
      client.release();
      
      // 初始化数据库结构
      await this._initializeDatabase();
    } catch (error) {
      this.logger.error('连接到TimescaleDB时出错:', error);
      throw new Error(`连接到TimescaleDB失败: ${error.message}`);
    }
  }

  /**
   * 断开TimescaleDB连接
   * @returns {Promise<void>}
   */
  async disconnect() {
    if (this.pool) {
      await this.pool.end();
      this.pool = null;
      this.logger.info('TimescaleDB连接已关闭');
    }
  }

  /**
   * 初始化数据库结构
   * @returns {Promise<void>}
   * @private
   */
  async _initializeDatabase() {
    // 检查是否有TimescaleDB扩展
    try {
      const client = await this.pool.connect();
      
      try {
        // 创建TimescaleDB扩展
        await client.query('CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;');
        
        // 创建表结构
        await this._createTables(client);
        
        // 创建超表（hypertable）
        await this._createHypertables(client);
        
        // 创建索引
        await this._createIndexes(client);
        
        // 设置保留策略
        await this._setRetentionPolicy(client);
        
        this.logger.info('TimescaleDB数据库结构初始化成功');
      } catch (error) {
        this.logger.error('初始化TimescaleDB结构时出错:', error);
        throw error;
      } finally {
        client.release();
      }
    } catch (error) {
      this.logger.error('获取数据库连接时出错:', error);
      throw error;
    }
  }

  /**
   * 创建表结构
   * @param {Object} client - 数据库客户端
   * @returns {Promise<void>}
   * @private
   */
  async _createTables(client) {
    // 读取并执行SQL脚本
    const schemaPath = path.join(__dirname, '../../config/timescaledb_schema.sql');
    
    try {
      if (fs.existsSync(schemaPath)) {
        const schemaSql = fs.readFileSync(schemaPath, 'utf8');
        await client.query(schemaSql);
        this.logger.info('已从文件创建表结构');
      } else {
        // 如果没有找到SQL文件，使用内联SQL创建表
        
        // 原始API指标表
        await client.query(`
          CREATE TABLE IF NOT EXISTS api_metrics (
            id SERIAL PRIMARY KEY,
            request_id TEXT,
            timestamp TIMESTAMPTZ NOT NULL,
            collected_at TIMESTAMPTZ NOT NULL,
            api_name TEXT,
            method TEXT,
            url TEXT,
            normalized_url TEXT,
            target_url TEXT,
            status_code INTEGER,
            duration INTEGER,
            is_success BOOLEAN,
            performance_grade TEXT,
            api_type TEXT,
            metadata JSONB
          );
        `);
        
        // 聚合API指标表
        await client.query(`
          CREATE TABLE IF NOT EXISTS api_metrics_aggregated (
            id SERIAL PRIMARY KEY,
            api_name TEXT NOT NULL,
            interval TIMESTAMPTZ NOT NULL,
            total_count INTEGER NOT NULL,
            success_count INTEGER NOT NULL,
            error_count INTEGER NOT NULL,
            error_rate FLOAT NOT NULL,
            avg_duration FLOAT,
            p50_duration FLOAT,
            p95_duration FLOAT,
            p99_duration FLOAT,
            min_duration FLOAT,
            max_duration FLOAT
          );
        `);
        
        // 自定义指标表
        await client.query(`
          CREATE TABLE IF NOT EXISTS custom_metrics (
            id SERIAL PRIMARY KEY,
            name TEXT NOT NULL,
            timestamp TIMESTAMPTZ NOT NULL,
            collected_at TIMESTAMPTZ NOT NULL,
            value FLOAT,
            tags JSONB,
            metadata JSONB
          );
        `);
        
        // 健康检查指标表
        await client.query(`
          CREATE TABLE IF NOT EXISTS health_check_metrics (
            id SERIAL PRIMARY KEY,
            endpoint_name TEXT NOT NULL,
            timestamp TIMESTAMPTZ NOT NULL,
            collected_at TIMESTAMPTZ NOT NULL,
            url TEXT NOT NULL,
            status_code INTEGER,
            duration INTEGER,
            is_success BOOLEAN,
            response_data TEXT,
            error_message TEXT
          );
        `);
        
        // 异常表
        await client.query(`
          CREATE TABLE IF NOT EXISTS anomalies (
            id SERIAL PRIMARY KEY,
            type TEXT NOT NULL,
            metric_id TEXT,
            timestamp TIMESTAMPTZ NOT NULL,
            value FLOAT,
            threshold FLOAT,
            message TEXT,
            is_resolved BOOLEAN DEFAULT false,
            resolved_at TIMESTAMPTZ,
            metadata JSONB
          );
        `);
        
        this.logger.info('已使用内联SQL创建表结构');
      }
    } catch (error) {
      this.logger.error('创建表结构时出错:', error);
      throw error;
    }
  }

  /**
   * 创建超表
   * @param {Object} client - 数据库客户端
   * @returns {Promise<void>}
   * @private
   */
  async _createHypertables(client) {
    try {
      // 将时间序列表转换为超表
      await client.query(`SELECT create_hypertable('api_metrics', 'timestamp', if_not_exists => TRUE);`);
      await client.query(`SELECT create_hypertable('api_metrics_aggregated', 'interval', if_not_exists => TRUE);`);
      await client.query(`SELECT create_hypertable('custom_metrics', 'timestamp', if_not_exists => TRUE);`);
      await client.query(`SELECT create_hypertable('health_check_metrics', 'timestamp', if_not_exists => TRUE);`);
      await client.query(`SELECT create_hypertable('anomalies', 'timestamp', if_not_exists => TRUE);`);
      
      this.logger.info('已创建超表');
    } catch (error) {
      this.logger.error('创建超表时出错:', error);
      throw error;
    }
  }

  /**
   * 创建索引
   * @param {Object} client - 数据库客户端
   * @returns {Promise<void>}
   * @private
   */
  async _createIndexes(client) {
    try {
      // API指标表索引
      await client.query(`CREATE INDEX IF NOT EXISTS idx_api_metrics_api_name ON api_metrics (api_name);`);
      await client.query(`CREATE INDEX IF NOT EXISTS idx_api_metrics_url ON api_metrics (url);`);
      await client.query(`CREATE INDEX IF NOT EXISTS idx_api_metrics_status ON api_metrics (status_code);`);
      
      // 聚合指标表索引
      await client.query(`CREATE INDEX IF NOT EXISTS idx_api_metrics_agg_api_name ON api_metrics_aggregated (api_name);`);
      
      // 自定义指标表索引
      await client.query(`CREATE INDEX IF NOT EXISTS idx_custom_metrics_name ON custom_metrics (name);`);
      
      // 健康检查指标表索引
      await client.query(`CREATE INDEX IF NOT EXISTS idx_health_check_endpoint ON health_check_metrics (endpoint_name);`);
      
      // 异常表索引
      await client.query(`CREATE INDEX IF NOT EXISTS idx_anomalies_type ON anomalies (type);`);
      await client.query(`CREATE INDEX IF NOT EXISTS idx_anomalies_resolved ON anomalies (is_resolved);`);
      
      this.logger.info('已创建索引');
    } catch (error) {
      this.logger.error('创建索引时出错:', error);
      throw error;
    }
  }

  /**
   * 设置数据保留策略
   * @param {Object} client - 数据库客户端
   * @returns {Promise<void>}
   * @private
   */
  async _setRetentionPolicy(client) {
    try {
      // 设置原始数据保留期限
      await client.query(`
        SELECT add_retention_policy('api_metrics', INTERVAL '${this.retentionPolicy.raw_data_days} days');
      `);
      
      // 设置其他表的保留期限
      await client.query(`
        SELECT add_retention_policy('custom_metrics', INTERVAL '${this.retentionPolicy.raw_data_days} days');
      `);
      
      await client.query(`
        SELECT add_retention_policy('health_check_metrics', INTERVAL '${this.retentionPolicy.raw_data_days} days');
      `);
      
      // 聚合数据保留更长时间
      await client.query(`
        SELECT add_retention_policy('api_metrics_aggregated', INTERVAL '${this.retentionPolicy.aggregated_data_days} days');
      `);
      
      // 异常数据保留更长时间
      await client.query(`
        SELECT add_retention_policy('anomalies', INTERVAL '${this.retentionPolicy.aggregated_data_days} days');
      `);
      
      this.logger.info('已设置数据保留策略');
    } catch (error) {
      this.logger.error('设置数据保留策略时出错:', error);
      // 不抛出异常，因为这不是关键功能
    }
  }

  /**
   * 保存原始指标数据
   * @param {Array} metrics - 指标数据数组
   * @returns {Promise<void>}
   */
  async saveRawMetrics(metrics) {
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const client = await this.pool.connect();
    
    try {
      // 开始事务
      await client.query('BEGIN');
      
      for (const metric of metrics) {
        await client.query(`
          INSERT INTO api_metrics (
            request_id, timestamp, collected_at, api_name, method, url,
            normalized_url, target_url, status_code, duration, is_success,
            performance_grade, api_type, metadata
          ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)
        `, [
          metric.requestId || null,
          new Date(metric.timestamp),
          new Date(metric.collected_at),
          metric.name || (metric.targetUrl ? new URL(metric.targetUrl).hostname : 'unknown'),
          metric.method || null,
          metric.url || null,
          metric.normalized_url || null,
          metric.targetUrl || null,
          metric.statusCode || null,
          metric.duration || null,
          metric.is_success || false,
          metric.performance_grade || null,
          metric.apiType || null,
          metric.metadata ? JSON.stringify(metric.metadata) : null
        ]);
      }
      
      // 提交事务
      await client.query('COMMIT');
      
      this.logger.debug(`已保存 ${metrics.length} 条原始指标`);
    } catch (error) {
      // 回滚事务
      await client.query('ROLLBACK');
      this.logger.error('保存原始指标时出错:', error);
      throw error;
    } finally {
      client.release();
    }
  }

  /**
   * 保存聚合指标数据
   * @param {Array} metrics - 聚合指标数据数组
   * @returns {Promise<void>}
   */
  async saveAggregatedMetrics(metrics) {
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const client = await this.pool.connect();
    
    try {
      // 开始事务
      await client.query('BEGIN');
      
      for (const metric of metrics) {
        await client.query(`
          INSERT INTO api_metrics_aggregated (
            api_name, interval, total_count, success_count, error_count,
            error_rate, avg_duration, p50_duration, p95_duration, p99_duration,
            min_duration, max_duration
          ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)
          ON CONFLICT (api_name, interval) DO UPDATE SET
            total_count = EXCLUDED.total_count,
            success_count = EXCLUDED.success_count,
            error_count = EXCLUDED.error_count,
            error_rate = EXCLUDED.error_rate,
            avg_duration = EXCLUDED.avg_duration,
            p50_duration = EXCLUDED.p50_duration,
            p95_duration = EXCLUDED.p95_duration,
            p99_duration = EXCLUDED.p99_duration,
            min_duration = EXCLUDED.min_duration,
            max_duration = EXCLUDED.max_duration
        `, [
          metric.api_name,
          new Date(metric.interval || metric.timestamp),
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
      
      // 提交事务
      await client.query('COMMIT');
      
      this.logger.debug(`已保存 ${metrics.length} 条聚合指标`);
    } catch (error) {
      // 回滚事务
      await client.query('ROLLBACK');
      this.logger.error('保存聚合指标时出错:', error);
      throw error;
    } finally {
      client.release();
    }
  }

  /**
   * 保存自定义指标
   * @param {Array} metrics - 自定义指标数组
   * @returns {Promise<void>}
   */
  async saveCustomMetrics(metrics) {
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const client = await this.pool.connect();
    
    try {
      // 开始事务
      await client.query('BEGIN');
      
      for (const metric of metrics) {
        await client.query(`
          INSERT INTO custom_metrics (
            name, timestamp, collected_at, value, tags, metadata
          ) VALUES ($1, $2, $3, $4, $5, $6)
        `, [
          metric.name,
          new Date(metric.timestamp),
          new Date(metric.collected_at),
          metric.value,
          metric.tags ? JSON.stringify(metric.tags) : null,
          metric.metadata ? JSON.stringify(metric.metadata) : null
        ]);
      }
      
      // 提交事务
      await client.query('COMMIT');
      
      this.logger.debug(`已保存 ${metrics.length} 条自定义指标`);
    } catch (error) {
      // 回滚事务
      await client.query('ROLLBACK');
      this.logger.error('保存自定义指标时出错:', error);
      throw error;
    } finally {
      client.release();
    }
  }

  /**
   * 保存健康检查指标
   * @param {Array} metrics - 健康检查指标数组
   * @returns {Promise<void>}
   */
  async saveHealthCheckMetrics(metrics) {
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!metrics || metrics.length === 0) {
      return;
    }
    
    const client = await this.pool.connect();
    
    try {
      // 开始事务
      await client.query('BEGIN');
      
      for (const metric of metrics) {
        await client.query(`
          INSERT INTO health_check_metrics (
            endpoint_name, timestamp, collected_at, url, status_code,
            duration, is_success, response_data, error_message
          ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
        `, [
          metric.endpoint_name,
          new Date(metric.timestamp),
          new Date(metric.collected_at),
          metric.url,
          metric.status_code,
          metric.duration,
          metric.is_success,
          metric.response_data,
          metric.error_message
        ]);
      }
      
      // 提交事务
      await client.query('COMMIT');
      
      this.logger.debug(`已保存 ${metrics.length} 条健康检查指标`);
    } catch (error) {
      // 回滚事务
      await client.query('ROLLBACK');
      this.logger.error('保存健康检查指标时出错:', error);
      throw error;
    } finally {
      client.release();
    }
  }

  /**
   * 保存异常数据
   * @param {Array} anomalies - 异常数据数组
   * @returns {Promise<void>}
   */
  async saveAnomalies(anomalies) {
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    if (!anomalies || anomalies.length === 0) {
      return;
    }
    
    const client = await this.pool.connect();
    
    try {
      // 开始事务
      await client.query('BEGIN');
      
      for (const anomaly of anomalies) {
        await client.query(`
          INSERT INTO anomalies (
            type, metric_id, timestamp, value, threshold,
            message, is_resolved, metadata
          ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
        `, [
          anomaly.type,
          anomaly.metric_id,
          new Date(anomaly.timestamp),
          anomaly.value,
          anomaly.threshold,
          anomaly.message,
          anomaly.is_resolved || false,
          anomaly.metadata ? JSON.stringify(anomaly.metadata) : null
        ]);
      }
      
      // 提交事务
      await client.query('COMMIT');
      
      this.logger.debug(`已保存 ${anomalies.length} 条异常数据`);
    } catch (error) {
      // 回滚事务
      await client.query('ROLLBACK');
      this.logger.error('保存异常数据时出错:', error);
      throw error;
    } finally {
      client.release();
    }
  }

  /**
   * 获取指标数据
   * @param {Object} query - 查询参数
   * @returns {Promise<Array>} 指标数据数组
   */
  async getMetrics(query) {
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    const { api_name, start_time, end_time, method, url, status_code, limit = 1000 } = query;
    
    let sql = `
      SELECT * FROM api_metrics
      WHERE 1=1
    `;
    
    const params = [];
    let paramIndex = 1;
    
    if (api_name) {
      sql += ` AND api_name = $${paramIndex++}`;
      params.push(api_name);
    }
    
    if (start_time) {
      sql += ` AND timestamp >= $${paramIndex++}`;
      params.push(new Date(start_time));
    }
    
    if (end_time) {
      sql += ` AND timestamp <= $${paramIndex++}`;
      params.push(new Date(end_time));
    }
    
    if (method) {
      sql += ` AND method = $${paramIndex++}`;
      params.push(method);
    }
    
    if (url) {
      sql += ` AND url LIKE $${paramIndex++}`;
      params.push(`%${url}%`);
    }
    
    if (status_code) {
      sql += ` AND status_code = $${paramIndex++}`;
      params.push(status_code);
    }
    
    sql += ` ORDER BY timestamp DESC LIMIT $${paramIndex++}`;
    params.push(limit);
    
    try {
      const result = await this.pool.query(sql, params);
      return result.rows;
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
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    const { api_name, start_time, end_time, limit = 1000 } = query;
    
    let sql = `
      SELECT * FROM api_metrics_aggregated
      WHERE 1=1
    `;
    
    const params = [];
    let paramIndex = 1;
    
    if (api_name) {
      sql += ` AND api_name = $${paramIndex++}`;
      params.push(api_name);
    }
    
    if (start_time) {
      sql += ` AND interval >= $${paramIndex++}`;
      params.push(new Date(start_time));
    }
    
    if (end_time) {
      sql += ` AND interval <= $${paramIndex++}`;
      params.push(new Date(end_time));
    }
    
    sql += ` ORDER BY interval DESC LIMIT $${paramIndex++}`;
    params.push(limit);
    
    try {
      const result = await this.pool.query(sql, params);
      return result.rows;
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
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    const { type, start_time, end_time, is_resolved, limit = 1000 } = query;
    
    let sql = `
      SELECT * FROM anomalies
      WHERE 1=1
    `;
    
    const params = [];
    let paramIndex = 1;
    
    if (type) {
      sql += ` AND type = $${paramIndex++}`;
      params.push(type);
    }
    
    if (start_time) {
      sql += ` AND timestamp >= $${paramIndex++}`;
      params.push(new Date(start_time));
    }
    
    if (end_time) {
      sql += ` AND timestamp <= $${paramIndex++}`;
      params.push(new Date(end_time));
    }
    
    if (is_resolved !== undefined) {
      sql += ` AND is_resolved = $${paramIndex++}`;
      params.push(is_resolved);
    }
    
    sql += ` ORDER BY timestamp DESC LIMIT $${paramIndex++}`;
    params.push(limit);
    
    try {
      const result = await this.pool.query(sql, params);
      return result.rows;
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
    if (!this.pool) {
      throw new Error('数据库连接未初始化');
    }
    
    try {
      const result = await this.pool.query(`
        SELECT DISTINCT api_name FROM api_metrics
        ORDER BY api_name
      `);
      return result.rows.map(row => row.api_name);
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
    // TimescaleDB会根据保留策略自动清理旧数据
    // 如果需要手动清理，可以在这里实现
    this.logger.info('TimescaleDB会根据保留策略自动清理旧数据');
  }
}

module.exports = TimescaleDBStorage; 