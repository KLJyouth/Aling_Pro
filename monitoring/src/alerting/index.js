/**
 * 告警管理器模块 - 处理API监控告警
 */

const EmailAlerter = require('./channels/email_alerter');
const SmsAlerter = require('./channels/sms_alerter');
const WebhookAlerter = require('./channels/webhook_alerter');
const WebSocketAlerter = require('./channels/websocket_alerter');
const RuleEngine = require('./rule_engine');

class AlertManager {
  /**
   * 创建告警管理器
   * @param {Object} config - 告警配置
   * @param {Object} logger - 日志记录器
   * @param {Object} io - Socket.IO实例，用于WebSocket告警
   */
  constructor(config, logger, io) {
    this.config = config;
    this.logger = logger;
    this.io = io;
    this.enabled = config.enabled !== false;
    
    // 创建告警通道
    this.alertChannels = {};
    
    // 创建规则引擎
    this.ruleEngine = new RuleEngine(config.rules || [], logger);
    
    // 告警历史记录
    this.alertHistory = [];
    this.maxHistorySize = 1000;
  }

  /**
   * 初始化告警管理器
   * @returns {Promise<void>}
   */
  async init() {
    if (!this.enabled) {
      this.logger.info('告警管理器已禁用');
      return;
    }
    
    this.logger.info('初始化告警管理器...');
    
    try {
      // 初始化告警通道
      await this._initAlertChannels();
      
      // 设置WebSocket事件处理
      this._setupWebSocketEvents();
      
      this.logger.info('告警管理器初始化完成');
    } catch (error) {
      this.logger.error('初始化告警管理器时出错:', error);
      throw error;
    }
  }

  /**
   * 初始化告警通道
   * @returns {Promise<void>}
   * @private
   */
  async _initAlertChannels() {
    // 检查是否有配置的告警通道
    if (!this.config.channels || !Array.isArray(this.config.channels)) {
      this.logger.warn('未配置告警通道');
      return;
    }
    
    // 初始化各个告警通道
    for (const channelConfig of this.config.channels) {
      try {
        const type = channelConfig.type;
        
        switch (type) {
          case 'email':
            this.alertChannels.email = new EmailAlerter(channelConfig.config, this.logger);
            await this.alertChannels.email.init();
            break;
            
          case 'sms':
            this.alertChannels.sms = new SmsAlerter(channelConfig.config, this.logger);
            await this.alertChannels.sms.init();
            break;
            
          case 'webhook':
            this.alertChannels.webhook = new WebhookAlerter(channelConfig.config, this.logger);
            await this.alertChannels.webhook.init();
            break;
            
          case 'websocket':
            this.alertChannels.websocket = new WebSocketAlerter(channelConfig.config, this.logger, this.io);
            await this.alertChannels.websocket.init();
            break;
            
          default:
            this.logger.warn(`未知的告警通道类型: ${type}`);
        }
      } catch (error) {
        this.logger.error(`初始化告警通道 ${channelConfig.type} 时出错:`, error);
        // 继续初始化其他通道
      }
    }
    
    const channelCount = Object.keys(this.alertChannels).length;
    if (channelCount === 0) {
      this.logger.warn('未成功初始化任何告警通道');
    } else {
      this.logger.info(`已初始化 ${channelCount} 个告警通道`);
    }
  }

  /**
   * 设置WebSocket事件处理
   * @private
   */
  _setupWebSocketEvents() {
    if (!this.io) {
      return;
    }
    
    this.io.on('connection', (socket) => {
      this.logger.debug('客户端连接到告警WebSocket');
      
      // 发送最近的告警历史
      socket.emit('alert_history', this.alertHistory);
      
      // 处理告警确认
      socket.on('acknowledge_alert', (alertId) => {
        this._acknowledgeAlert(alertId, socket);
      });
      
      socket.on('disconnect', () => {
        this.logger.debug('客户端断开告警WebSocket连接');
      });
    });
  }

  /**
   * 处理告警确认
   * @param {string} alertId - 告警ID
   * @param {Object} socket - WebSocket连接
   * @private
   */
  _acknowledgeAlert(alertId, socket) {
    try {
      // 查找告警
      const alertIndex = this.alertHistory.findIndex(alert => alert.id === alertId);
      
      if (alertIndex >= 0) {
        // 更新告警状态
        const alert = this.alertHistory[alertIndex];
        alert.acknowledged = true;
        alert.acknowledged_at = new Date();
        
        this.logger.info(`告警 ${alertId} 已被确认`);
        
        // 广播告警更新
        this.io.emit('alert_updated', alert);
        
        // 发送确认响应
        socket.emit('acknowledge_success', { alertId });
      } else {
        socket.emit('acknowledge_error', { alertId, error: '未找到告警' });
      }
    } catch (error) {
      this.logger.error(`确认告警 ${alertId} 时出错:`, error);
      socket.emit('acknowledge_error', { alertId, error: error.message });
    }
  }

  /**
   * 处理指标数据并触发告警
   * @param {Object} metrics - 指标数据
   * @returns {Promise<void>}
   */
  async processMetrics(metrics) {
    if (!this.enabled) {
      return;
    }
    
    try {
      // 使用规则引擎评估指标
      const alerts = this.ruleEngine.evaluateMetrics(metrics);
      
      // 发送告警
      for (const alert of alerts) {
        await this.sendAlert(alert);
      }
    } catch (error) {
      this.logger.error('处理指标数据进行告警时出错:', error);
    }
  }

  /**
   * 发送告警
   * @param {Object} alert - 告警信息
   * @returns {Promise<void>}
   */
  async sendAlert(alert) {
    if (!this.enabled) {
      return;
    }
    
    try {
      // 添加时间戳和ID
      alert.timestamp = alert.timestamp || new Date();
      alert.id = alert.id || `alert-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      
      // 记录告警到历史
      this._addToHistory(alert);
      
      this.logger.info(`触发告警: ${alert.name} - ${alert.message}`);
      
      // 获取要发送到的通道
      const channels = alert.channels || Object.keys(this.alertChannels);
      
      // 发送到各个通道
      const promises = [];
      
      for (const channel of channels) {
        const alerter = this.alertChannels[channel];
        if (alerter) {
          promises.push(
            alerter.sendAlert(alert)
              .catch(error => {
                this.logger.error(`通过 ${channel} 发送告警时出错:`, error);
              })
          );
        }
      }
      
      await Promise.all(promises);
    } catch (error) {
      this.logger.error('发送告警时出错:', error);
    }
  }

  /**
   * 将告警添加到历史记录
   * @param {Object} alert - 告警信息
   * @private
   */
  _addToHistory(alert) {
    this.alertHistory.unshift(alert);
    
    // 限制历史大小
    if (this.alertHistory.length > this.maxHistorySize) {
      this.alertHistory = this.alertHistory.slice(0, this.maxHistorySize);
    }
  }

  /**
   * 获取告警历史
   * @param {number} limit - 限制返回数量
   * @returns {Array} 告警历史
   */
  getAlertHistory(limit = 100) {
    return this.alertHistory.slice(0, limit);
  }

  /**
   * 清除告警历史
   */
  clearAlertHistory() {
    this.alertHistory = [];
    this.logger.info('告警历史已清除');
  }
}

module.exports = AlertManager; 