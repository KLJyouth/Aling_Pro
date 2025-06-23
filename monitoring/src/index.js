/**
 * AlingAi API监控系统 - 主入口文件
 */

const fs = require('fs');
const path = require('path');
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const http = require('http');
const socketIo = require('socket.io');
const winston = require('winston');
const dotenv = require('dotenv');
const promClient = require('prom-client');

// 加载环境变量
dotenv.config();

// 引入核心模块
const ApiGateway = require('./api_gateway');
const MetricsCollector = require('./metrics_collector');
const StorageManager = require('./storage');
const AlertManager = require('./alerting');
const HealthCheckService = require('./health_check');
const Scheduler = require('./scheduler');
const WebInterface = require('./web');

// 创建日志记录器
const logger = winston.createLogger({
  level: process.env.LOG_LEVEL || 'info',
  format: winston.format.combine(
    winston.format.timestamp(),
    winston.format.json()
  ),
  transports: [
    new winston.transports.Console(),
    new winston.transports.File({ filename: 'logs/error.log', level: 'error' }),
    new winston.transports.File({ filename: 'logs/combined.log' })
  ]
});

// 确保日志目录存在
const logDir = path.join(__dirname, '../logs');
if (!fs.existsSync(logDir)) {
  fs.mkdirSync(logDir, { recursive: true });
}

// 加载配置
let config;
try {
  const configPath = path.join(__dirname, '../config/config.json');
  const configData = fs.readFileSync(configPath, 'utf8');
  config = JSON.parse(configData);
  logger.info('配置文件加载成功');
} catch (error) {
  logger.error('加载配置文件失败:', error);
  process.exit(1);
}

// 创建Express应用
const app = express();
app.use(helmet());
app.use(compression());
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// 创建HTTP服务器
const server = http.createServer(app);

// 创建WebSocket服务器
const io = socketIo(server);

// 创建Prometheus注册表
const register = new promClient.Registry();
promClient.collectDefaultMetrics({ register });

// 添加Prometheus指标端点
app.get('/metrics', async (req, res) => {
  try {
    res.set('Content-Type', register.contentType);
    res.end(await register.metrics());
  } catch (err) {
    logger.error('获取Prometheus指标失败:', err);
    res.status(500).end();
  }
});

// 初始化各模块
const storageManager = new StorageManager(config.storage, logger);
const metricsCollector = new MetricsCollector(config.metrics_collector, storageManager, logger, register);
const alertManager = new AlertManager(config.alerting, logger, io);
const healthCheckService = new HealthCheckService(config.health_check, alertManager, logger);
const scheduler = new Scheduler(config.scheduler, { metricsCollector, alertManager, healthCheckService }, logger);
const apiGateway = new ApiGateway(config.api_gateway, metricsCollector, logger);
const webInterface = new WebInterface(app, config.system, storageManager, alertManager, logger);

// 初始化系统
async function initSystem() {
  try {
    logger.info('正在初始化监控系统...');
    
    // 连接存储
    await storageManager.connect();
    logger.info('存储连接成功');
    
    // 初始化其他组件
    await alertManager.init();
    await healthCheckService.init();
    await apiGateway.init();
    await webInterface.init();
    
    // 启动调度器
    scheduler.start();
    
    // 启动监控服务器
    server.listen(config.system.port, () => {
      logger.info(`监控系统已启动，监听端口 ${config.system.port}`);
    });
    
    // 启动API网关
    if (config.api_gateway.enabled !== false) {
      apiGateway.start();
    }
    
    // 处理进程退出
    process.on('SIGTERM', shutdown);
    process.on('SIGINT', shutdown);
    
    logger.info('监控系统初始化完成');
  } catch (error) {
    logger.error('系统初始化失败:', error);
    process.exit(1);
  }
}

// 关闭系统
async function shutdown() {
  logger.info('正在关闭监控系统...');
  
  // 停止各个服务
  scheduler.stop();
  await apiGateway.stop();
  await healthCheckService.stop();
  
  // 关闭服务器
  server.close(() => {
    logger.info('HTTP服务器已关闭');
  });
  
  // 关闭存储连接
  await storageManager.disconnect();
  
  logger.info('监控系统已关闭');
  process.exit(0);
}

// 启动系统
initSystem().catch(err => {
  logger.error('启动系统时发生错误:', err);
  process.exit(1);
});

// 导出各模块供其他文件使用
module.exports = {
  app,
  server,
  io,
  config,
  logger,
  storageManager,
  metricsCollector,
  alertManager,
  healthCheckService,
  scheduler,
  apiGateway,
  webInterface
}; 