/**
 * AlingAi API监控系统 - 主入口文件
 */

const express = require('express');
const { createServer } = require('http');
const path = require('path');
const bodyParser = require('body-parser');
const promClient = require('prom-client');
const winston = require('winston');
const ejs = require('ejs');
const morgan = require('morgan');

// 导入自定义模块
const MetricsCollector = require('./metrics_collector');
const StorageManager = require('./storage_manager');
const AlertManager = require('./alert_manager');
const WebInterface = require('./web');
const ConfigManager = require('./config_manager');

class MonitoringSystem {
  constructor(configPath) {
    this.app = express();
    this.server = createServer(this.app);
    this.configPath = configPath || path.join(__dirname, '../config/config.json');
    this.config = null;
    this.logger = null;
    this.metricsCollector = null;
    this.storageManager = null;
    this.alertManager = null;
    this.webInterface = null;
    this.configManager = null;
    this.registry = new promClient.Registry();
  }

  /**
   * 初始化系统
   * @returns {Promise<void>}
   */
  async init() {
    try {
      // 初始化配置管理器
      this.configManager = new ConfigManager(this.configPath);
      this.config = await this.configManager.loadConfig();

      // 设置日志记录器
      this.setupLogger();
      this.logger.info('正在初始化AlingAi API监控系统...');

      // 设置Express中间件
      this.setupExpress();

      // 初始化存储管理器
      this.storageManager = new StorageManager(this.config.storage, this.logger);
      await this.storageManager.init();
      this.logger.info('存储管理器初始化完成');

      // 初始化告警管理器
      this.alertManager = new AlertManager(this.config.alerts, this.storageManager, this.logger);
      await this.alertManager.init();
      this.logger.info('告警管理器初始化完成');

      // 初始化指标收集器
      this.metricsCollector = new MetricsCollector(
        this.config.apis,
        this.storageManager,
        this.alertManager,
        this.logger
      );
      await this.metricsCollector.init();
      this.logger.info('指标收集器初始化完成');

      // 初始化Web界面
      this.webInterface = new WebInterface(
        this.app,
        this.config,
        this.storageManager,
        this.alertManager,
        this.logger
      );
      await this.webInterface.init();
      this.logger.info('Web界面初始化完成');

      // 设置Prometheus指标
      this.setupPrometheusMetrics();

      // 启动监控系统
      await this.start();
      this.logger.info('AlingAi API监控系统已启动');
    } catch (error) {
      console.error('初始化监控系统时出错:', error);
      process.exit(1);
    }
  }

  /**
   * 设置Express中间件
   * @private
   */
  setupExpress() {
    this.app.use(bodyParser.json());
    this.app.use(bodyParser.urlencoded({ extended: true }));
    
    // 设置日志记录中间件
    this.app.use(morgan('combined', { stream: { write: message => this.logger.info(message.trim()) } }));
    
    // 设置视图引擎
    this.app.set('view engine', 'ejs');
    this.app.set('views', path.join(__dirname, 'web/views'));
    
    // 设置静态文件目录
    this.app.use('/static', express.static(path.join(__dirname, 'web/public')));
    
    // 设置基本路由
    this.app.get('/health', (req, res) => {
      res.status(200).json({ status: 'ok' });
    });
    
    // 设置Prometheus指标端点
    this.app.get('/metrics', async (req, res) => {
      try {
        res.set('Content-Type', this.registry.contentType);
        res.end(await this.registry.metrics());
      } catch (error) {
        this.logger.error('获取Prometheus指标时出错:', error);
        res.status(500).end();
      }
    });
  }

  /**
   * 设置日志记录器
   * @private
   */
  setupLogger() {
    const logFormat = winston.format.combine(
      winston.format.timestamp(),
      winston.format.printf(({ level, message, timestamp }) => {
        return `${timestamp} ${level.toUpperCase()}: ${message}`;
      })
    );

    this.logger = winston.createLogger({
      level: this.config.logLevel || 'info',
      format: logFormat,
      transports: [
        new winston.transports.Console(),
        new winston.transports.File({
          filename: path.join(__dirname, '../logs/error.log'),
          level: 'error'
        }),
        new winston.transports.File({
          filename: path.join(__dirname, '../logs/combined.log')
        })
      ]
    });
  }

  /**
   * 设置Prometheus指标
   * @private
   */
  setupPrometheusMetrics() {
    // 清除默认指标
    this.registry.clear();

    // 添加默认指标
    promClient.collectDefaultMetrics({ register: this.registry });

    // 添加自定义指标
    this.httpRequestDurationMicroseconds = new promClient.Histogram({
      name: 'http_request_duration_seconds',
      help: 'Duration of HTTP requests in seconds',
      labelNames: ['method', 'route', 'status_code'],
      buckets: [0.1, 0.3, 0.5, 0.7, 1, 3, 5, 7, 10]
    });
    this.registry.registerMetric(this.httpRequestDurationMicroseconds);

    // 添加HTTP请求计数器
    this.httpRequestsTotal = new promClient.Counter({
      name: 'http_requests_total',
      help: 'Total number of HTTP requests',
      labelNames: ['method', 'route', 'status_code']
    });
    this.registry.registerMetric(this.httpRequestsTotal);

    // 中间件计算请求持续时间
    this.app.use((req, res, next) => {
      const start = process.hrtime();
      
      res.on('finish', () => {
        const duration = process.hrtime(start);
        const durationInSeconds = duration[0] + duration[1] / 1e9;
        const route = req.route ? req.route.path : req.path;
        
        this.httpRequestDurationMicroseconds
          .labels(req.method, route, res.statusCode)
          .observe(durationInSeconds);
          
        this.httpRequestsTotal
          .labels(req.method, route, res.statusCode)
          .inc();
      });
      
      next();
    });
  }

  /**
   * 启动监控系统
   * @returns {Promise<void>}
   * @private
   */
  async start() {
    const port = this.config.port || 3000;
    
    return new Promise((resolve, reject) => {
      this.server.listen(port, err => {
        if (err) {
          reject(err);
          return;
        }
        
        this.logger.info(`服务器运行在: http://localhost:${port}`);
        resolve();
      });
    });
  }

  /**
   * 关闭监控系统
   * @returns {Promise<void>}
   */
  async shutdown() {
    this.logger.info('正在关闭AlingAi API监控系统...');
    
    // 停止指标收集
    if (this.metricsCollector) {
      await this.metricsCollector.stop();
    }
    
    // 关闭存储连接
    if (this.storageManager) {
      await this.storageManager.close();
    }
    
    // 关闭HTTP服务器
    await new Promise(resolve => {
      this.server.close(resolve);
    });
    
    this.logger.info('AlingAi API监控系统已关闭');
  }
}

// 导出监控系统类
module.exports = MonitoringSystem;

// 如果直接运行此文件，则初始化并启动系统
if (require.main === module) {
  const configPath = process.env.CONFIG_PATH;
  const monitoringSystem = new MonitoringSystem(configPath);
  
  monitoringSystem.init().catch(error => {
    console.error('启动监控系统时出错:', error);
    process.exit(1);
  });
  
  // 处理进程信号
  process.on('SIGINT', async () => {
    console.log('接收到SIGINT信号，正在优雅关闭...');
    await monitoringSystem.shutdown();
    process.exit(0);
  });
  
  process.on('SIGTERM', async () => {
    console.log('接收到SIGTERM信号，正在优雅关闭...');
    await monitoringSystem.shutdown();
    process.exit(0);
  });
  
  process.on('uncaughtException', error => {
    console.error('未捕获的异常:', error);
    monitoringSystem.shutdown().then(() => {
      process.exit(1);
    });
  });
  
  process.on('unhandledRejection', (reason, promise) => {
    console.error('未处理的Promise拒绝:', reason);
    monitoringSystem.shutdown().then(() => {
      process.exit(1);
    });
  });
} 