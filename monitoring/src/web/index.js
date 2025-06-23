/**
 * AlingAi API监控系统 - Web界面模块
 */

const express = require('express');
const path = require('path');
const ejs = require('ejs');

// 导入组件
const components = require('./components');

class WebInterface {
  /**
   * 创建Web界面
   * @param {Object} app - Express应用实例
   * @param {Object} config - 配置对象
   * @param {Object} storageManager - 存储管理器
   * @param {Object} alertManager - 告警管理器
   * @param {Object} logger - 日志记录器
   */
  constructor(app, config, storageManager, alertManager, logger) {
    this.app = app;
    this.config = config;
    this.storageManager = storageManager;
    this.alertManager = alertManager;
    this.logger = logger;
    
    // 定义视图路径
    this.viewsPath = path.join(__dirname, 'views');
    this.publicPath = path.join(__dirname, 'public');
    
    // 初始化组件
    this.initComponents();
  }

  /**
   * 初始化组件
   * @private
   */
  initComponents() {
    this.statusBadge = new components.StatusBadge();
    this.apiChart = new components.ApiChart();
    this.alertList = new components.AlertList();
    this.apiTable = new components.ApiTable(this.statusBadge);
  }

  /**
   * 初始化Web界面
   * @returns {Promise<void>}
   */
  async init() {
    this.logger.info('初始化Web界面...');
    
    try {
      // 设置视图引擎
      this.app.set('view engine', 'ejs');
      this.app.set('views', this.viewsPath);
      
      // 设置静态文件目录
      this.app.use('/static', express.static(this.publicPath));
      
      // 注册路由
      this._registerRoutes();
      
      this.logger.info('Web界面初始化完成');
    } catch (error) {
      this.logger.error('初始化Web界面时出错:', error);
      throw error;
    }
  }

  /**
   * 注册路由
   * @private
   */
  _registerRoutes() {
    // 首页 - 仪表盘概览
    this.app.get('/', async (req, res) => {
      try {
        const apiList = await this.storageManager.getApiList();
        const recentAlerts = this.alertManager.getAlertHistory(5);
        
        res.render('dashboard', {
          title: 'AlingAi API监控系统',
          apiList,
          recentAlerts,
          activeMenu: 'dashboard',
          components: {
            statusBadge: this.statusBadge,
            apiChart: this.apiChart,
            alertList: this.alertList,
            apiTable: this.apiTable
          }
        });
      } catch (error) {
        this.logger.error('渲染仪表盘页面时出错:', error);
        res.status(500).render('error', { 
          message: '加载仪表盘时出错',
          error: process.env.NODE_ENV === 'development' ? error : {} 
        });
      }
    });

    // API监控页面
    this.app.get('/apis', async (req, res) => {
      try {
        const apiList = await this.storageManager.getApiList();
        
        res.render('apis', {
          title: 'API监控 - AlingAi API监控系统',
          apiList,
          activeMenu: 'apis',
          components: {
            statusBadge: this.statusBadge,
            apiChart: this.apiChart,
            alertList: this.alertList,
            apiTable: this.apiTable
          }
        });
      } catch (error) {
        this.logger.error('渲染API监控页面时出错:', error);
        res.status(500).render('error', { 
          message: '加载API监控页面时出错',
          error: process.env.NODE_ENV === 'development' ? error : {} 
        });
      }
    });

    // API详情页面
    this.app.get('/apis/:apiName', async (req, res) => {
      try {
        const apiName = req.params.apiName;
        const apiMetrics = await this.storageManager.getMetrics({
          api_name: apiName,
          limit: 100
        });
        
        const aggregatedMetrics = await this.storageManager.getAggregatedMetrics({
          api_name: apiName,
          limit: 24
        });
        
        res.render('api-detail', {
          title: `${apiName} - AlingAi API监控系统`,
          apiName,
          apiMetrics,
          aggregatedMetrics,
          activeMenu: 'apis',
          components: {
            statusBadge: this.statusBadge,
            apiChart: this.apiChart,
            alertList: this.alertList,
            apiTable: this.apiTable
          }
        });
      } catch (error) {
        this.logger.error('渲染API详情页面时出错:', error);
        res.status(500).render('error', { 
          message: '加载API详情页面时出错',
          error: process.env.NODE_ENV === 'development' ? error : {} 
        });
      }
    });

    // 告警页面
    this.app.get('/alerts', (req, res) => {
      try {
        const alerts = this.alertManager.getAlertHistory(100);
        
        res.render('alerts', {
          title: '告警 - AlingAi API监控系统',
          alerts,
          activeMenu: 'alerts',
          components: {
            statusBadge: this.statusBadge,
            apiChart: this.apiChart,
            alertList: this.alertList,
            apiTable: this.apiTable
          }
        });
      } catch (error) {
        this.logger.error('渲染告警页面时出错:', error);
        res.status(500).render('error', { 
          message: '加载告警页面时出错',
          error: process.env.NODE_ENV === 'development' ? error : {} 
        });
      }
    });

    // 健康检查页面
    this.app.get('/health-checks', async (req, res) => {
      try {
        // 获取健康检查指标
        const healthChecks = await this.storageManager.getHealthCheckMetrics({
          limit: 100
        });
        
        res.render('health-checks', {
          title: '健康检查 - AlingAi API监控系统',
          healthChecks,
          activeMenu: 'health-checks',
          components: {
            statusBadge: this.statusBadge,
            apiChart: this.apiChart,
            alertList: this.alertList,
            apiTable: this.apiTable
          }
        });
      } catch (error) {
        this.logger.error('渲染健康检查页面时出错:', error);
        res.status(500).render('error', { 
          message: '加载健康检查页面时出错',
          error: process.env.NODE_ENV === 'development' ? error : {} 
        });
      }
    });

    // 设置页面
    this.app.get('/settings', (req, res) => {
      res.render('settings', {
        title: '设置 - AlingAi API监控系统',
        config: this.config,
        activeMenu: 'settings',
        components: {
          statusBadge: this.statusBadge,
          apiChart: this.apiChart,
          alertList: this.alertList,
          apiTable: this.apiTable
        }
      });
    });
    
    // 组件演示页面（仅在开发环境中可用）
    if (process.env.NODE_ENV === 'development' || true) { // 暂时总是可用，便于测试
      this.app.get('/components-demo', (req, res) => {
        res.render('components-demo', {
          title: '组件演示 - AlingAi API监控系统',
          activeMenu: '',
          components: {
            statusBadge: this.statusBadge,
            apiChart: this.apiChart,
            alertList: this.alertList,
            apiTable: this.apiTable
          }
        });
      });
    }

    // API - 获取最近的指标数据（用于图表实时更新）
    this.app.get('/api/metrics/:apiName', async (req, res) => {
      try {
        const apiName = req.params.apiName;
        const since = req.query.since ? new Date(req.query.since) : new Date(Date.now() - 3600000);
        
        const metrics = await this.storageManager.getMetrics({
          api_name: apiName,
          start_time: since,
          limit: 100
        });
        
        res.json(metrics);
      } catch (error) {
        this.logger.error('获取API指标时出错:', error);
        res.status(500).json({ error: '获取API指标时出错' });
      }
    });

    // API - 获取最近的告警数据（用于实时更新）
    this.app.get('/api/alerts', (req, res) => {
      try {
        const count = req.query.count ? parseInt(req.query.count, 10) : 10;
        const alerts = this.alertManager.getAlertHistory(count);
        
        res.json(alerts);
      } catch (error) {
        this.logger.error('获取告警数据时出错:', error);
        res.status(500).json({ error: '获取告警数据时出错' });
      }
    });

    // 404页面
    this.app.use((req, res) => {
      res.status(404).render('error', {
        message: '页面未找到',
        error: { status: 404 }
      });
    });
  }
}

module.exports = WebInterface; 