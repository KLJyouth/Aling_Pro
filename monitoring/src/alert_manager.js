/**
 * AlingAi API监控系统 - 告警管理器
 */

const nodemailer = require('nodemailer');
const axios = require('axios');

class AlertManager {
  /**
   * 创建告警管理器
   * @param {Object} config - 告警配置
   * @param {Object} storageManager - 存储管理器
   * @param {Object} logger - 日志记录器
   */
  constructor(config, storageManager, logger) {
    this.config = config || {};
    this.storageManager = storageManager;
    this.logger = logger;
    this.emailTransporter = null;
    this.alertHistory = [];
    this.maxHistorySize = 100;
  }

  /**
   * 初始化告警管理器
   * @returns {Promise<void>}
   */
  async init() {
    try {
      this.logger.info('初始化告警管理器...');
      
      // 检查是否启用告警
      if (this.config.enabled !== false) {
        // 初始化邮件发送器
        if (this.config.providers?.email?.enabled) {
          this._initEmailTransporter();
        }
        
        // 加载历史告警
        await this._loadAlertHistory();
      } else {
        this.logger.info('告警功能已禁用');
      }
      
      this.logger.info('告警管理器初始化完成');
    } catch (error) {
      this.logger.error('初始化告警管理器时出错:', error);
      throw error;
    }
  }

  /**
   * 初始化邮件发送器
   * @private
   */
  _initEmailTransporter() {
    const emailConfig = this.config.providers.email;
    
    if (!emailConfig.smtpServer || !emailConfig.smtpPort) {
      this.logger.warn('邮件服务器配置不完整，邮件告警将不可用');
      return;
    }
    
    try {
      this.emailTransporter = nodemailer.createTransport({
        host: emailConfig.smtpServer,
        port: emailConfig.smtpPort,
        secure: emailConfig.secure,
        auth: emailConfig.auth && emailConfig.auth.user ? {
          user: emailConfig.auth.user,
          pass: emailConfig.auth.pass
        } : undefined
      });
      
      this.logger.info('邮件发送器初始化成功');
    } catch (error) {
      this.logger.error('初始化邮件发送器时出错:', error);
    }
  }

  /**
   * 加载历史告警
   * @private
   * @returns {Promise<void>}
   */
  async _loadAlertHistory() {
    try {
      if (this.storageManager) {
        const alerts = await this.storageManager.getAlerts({ limit: this.maxHistorySize });
        this.alertHistory = alerts;
        this.logger.info(`已加载 ${alerts.length} 条历史告警`);
      }
    } catch (error) {
      this.logger.error('加载历史告警时出错:', error);
    }
  }

  /**
   * 触发告警
   * @param {Object} alert - 告警对象
   * @returns {Promise<Object>} 处理后的告警对象
   */
  async triggerAlert(alert) {
    try {
      if (!this.config.enabled) {
        this.logger.info('告警功能已禁用，跳过告警:', alert.title);
        return alert;
      }
      
      this.logger.info(`触发告警: ${alert.title}`);
      
      // 补充告警信息
      const fullAlert = {
        ...alert,
        id: alert.id || Date.now().toString(36) + Math.random().toString(36).substr(2, 5),
        timestamp: alert.timestamp || new Date().toISOString(),
        status: 'pending'
      };
      
      // 保存告警到存储
      if (this.storageManager) {
        await this.storageManager.saveAlert(fullAlert);
      }
      
      // 更新内存中的告警历史
      this.alertHistory.unshift(fullAlert);
      if (this.alertHistory.length > this.maxHistorySize) {
        this.alertHistory.pop();
      }
      
      // 发送告警通知
      await this._sendAlertNotifications(fullAlert);
      
      return fullAlert;
    } catch (error) {
      this.logger.error('触发告警时出错:', error);
      throw error;
    }
  }

  /**
   * 发送告警通知
   * @private
   * @param {Object} alert - 告警对象
   * @returns {Promise<void>}
   */
  async _sendAlertNotifications(alert) {
    try {
      const promises = [];
      
      // 发送邮件告警
      if (this.config.providers?.email?.enabled && this.emailTransporter) {
        promises.push(this._sendEmailAlert(alert));
      }
      
      // 发送Webhook告警
      if (this.config.providers?.webhook?.enabled) {
        promises.push(this._sendWebhookAlert(alert));
      }
      
      // 等待所有通知发送完成
      await Promise.all(promises);
    } catch (error) {
      this.logger.error('发送告警通知时出错:', error);
    }
  }

  /**
   * 发送邮件告警
   * @private
   * @param {Object} alert - 告警对象
   * @returns {Promise<void>}
   */
  async _sendEmailAlert(alert) {
    try {
      const emailConfig = this.config.providers.email;
      
      if (!emailConfig.to || emailConfig.to.length === 0) {
        this.logger.warn('未配置邮件收件人，跳过邮件告警');
        return;
      }
      
      const mailOptions = {
        from: emailConfig.from || 'api-monitoring@example.com',
        to: Array.isArray(emailConfig.to) ? emailConfig.to.join(',') : emailConfig.to,
        subject: `[${alert.level.toUpperCase()}] ${alert.title}`,
        html: `
          <h2>${alert.title}</h2>
          <p><strong>级别:</strong> ${alert.level.toUpperCase()}</p>
          <p><strong>时间:</strong> ${new Date(alert.timestamp).toLocaleString()}</p>
          <p><strong>API:</strong> ${alert.api_name || alert.api_id}</p>
          <p><strong>消息:</strong> ${alert.message}</p>
          ${alert.details ? `<h3>详细信息:</h3><pre>${JSON.stringify(alert.details, null, 2)}</pre>` : ''}
        `
      };
      
      await this.emailTransporter.sendMail(mailOptions);
      this.logger.info(`邮件告警已发送: ${alert.title}`);
    } catch (error) {
      this.logger.error('发送邮件告警时出错:', error);
    }
  }

  /**
   * 发送Webhook告警
   * @private
   * @param {Object} alert - 告警对象
   * @returns {Promise<void>}
   */
  async _sendWebhookAlert(alert) {
    try {
      const webhookConfig = this.config.providers.webhook;
      
      if (!webhookConfig.url) {
        this.logger.warn('未配置Webhook URL，跳过Webhook告警');
        return;
      }
      
      const response = await axios.post(webhookConfig.url, alert, {
        headers: webhookConfig.headers || {},
        timeout: 5000
      });
      
      this.logger.info(`Webhook告警已发送: ${alert.title}, 状态码: ${response.status}`);
    } catch (error) {
      this.logger.error('发送Webhook告警时出错:', error);
    }
  }

  /**
   * 解决告警
   * @param {string} alertId - 告警ID
   * @returns {Promise<boolean>} 是否解决成功
   */
  async resolveAlert(alertId) {
    try {
      // 更新存储中的告警状态
      if (this.storageManager) {
        await this.storageManager.updateAlertStatus(alertId, 'resolved');
      }
      
      // 更新内存中的告警状态
      const alertIndex = this.alertHistory.findIndex(a => a.id === alertId);
      if (alertIndex >= 0) {
        this.alertHistory[alertIndex].status = 'resolved';
        this.alertHistory[alertIndex].resolved_at = new Date().toISOString();
      }
      
      this.logger.info(`告警已解决: ${alertId}`);
      return true;
    } catch (error) {
      this.logger.error(`解决告警时出错: ${alertId}`, error);
      return false;
    }
  }

  /**
   * 确认告警
   * @param {string} alertId - 告警ID
   * @returns {Promise<boolean>} 是否确认成功
   */
  async acknowledgeAlert(alertId) {
    try {
      // 更新存储中的告警状态
      if (this.storageManager) {
        await this.storageManager.updateAlertStatus(alertId, 'acknowledged');
      }
      
      // 更新内存中的告警状态
      const alertIndex = this.alertHistory.findIndex(a => a.id === alertId);
      if (alertIndex >= 0) {
        this.alertHistory[alertIndex].status = 'acknowledged';
      }
      
      this.logger.info(`告警已确认: ${alertId}`);
      return true;
    } catch (error) {
      this.logger.error(`确认告警时出错: ${alertId}`, error);
      return false;
    }
  }

  /**
   * 获取告警历史
   * @param {number} limit - 限制数量
   * @returns {Array} 告警历史
   */
  getAlertHistory(limit = 0) {
    if (limit > 0) {
      return this.alertHistory.slice(0, limit);
    }
    return this.alertHistory;
  }

  /**
   * 测试告警系统
   * @returns {Promise<Object>} 测试结果
   */
  async testAlerts() {
    try {
      const testAlert = {
        id: 'test-' + Date.now().toString(36),
        api_id: 'test-api',
        api_name: 'Test API',
        level: 'info',
        title: '告警系统测试',
        message: '这是一个测试告警，用于验证告警系统是否正常工作。',
        timestamp: new Date().toISOString(),
        details: {
          test: true,
          timestamp: Date.now()
        }
      };
      
      // 发送测试告警通知
      await this._sendAlertNotifications(testAlert);
      
      return {
        success: true,
        message: '测试告警已发送',
        alert: testAlert
      };
    } catch (error) {
      this.logger.error('测试告警系统时出错:', error);
      return {
        success: false,
        message: '测试告警发送失败',
        error: error.message
      };
    }
  }
}

module.exports = AlertManager; 