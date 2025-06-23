/**
 * 电子邮件告警通道 - 发送电子邮件告警
 */

const nodemailer = require('nodemailer');

class EmailAlerter {
  /**
   * 创建电子邮件告警通道
   * @param {Object} config - 配置
   * @param {Object} logger - 日志记录器
   */
  constructor(config, logger) {
    this.config = config || {};
    this.logger = logger;
    this.transporter = null;
    this.enabled = this.config.enabled !== false;
  }

  /**
   * 初始化电子邮件告警通道
   * @returns {Promise<void>}
   */
  async init() {
    if (!this.enabled) {
      this.logger.info('电子邮件告警通道已禁用');
      return;
    }
    
    try {
      // 创建SMTP传输器
      this.transporter = nodemailer.createTransport({
        host: this.config.smtp_host || 'smtp.example.com',
        port: this.config.smtp_port || 587,
        secure: this.config.smtp_secure !== false, // true for 465, false for other ports
        auth: {
          user: this.config.smtp_user || 'user@example.com',
          pass: this.config.smtp_password || 'password'
        }
      });
      
      // 验证连接配置
      if (process.env.NODE_ENV !== 'test') {
        await this.transporter.verify();
      }
      
      this.logger.info('电子邮件告警通道初始化成功');
    } catch (error) {
      this.logger.error('初始化电子邮件告警通道时出错:', error);
      this.enabled = false;
      throw error;
    }
  }

  /**
   * 发送告警
   * @param {Object} alert - 告警信息
   * @returns {Promise<boolean>} 是否发送成功
   */
  async sendAlert(alert) {
    if (!this.enabled || !this.transporter) {
      this.logger.warn('电子邮件告警通道未启用或未初始化，无法发送告警');
      return false;
    }
    
    try {
      // 确定收件人
      const recipients = this._getRecipients(alert);
      
      if (!recipients || recipients.length === 0) {
        this.logger.warn(`告警 ${alert.name} 没有有效的收件人，跳过发送`);
        return false;
      }
      
      // 构建邮件主题
      const subject = this._buildSubject(alert);
      
      // 构建邮件内容
      const content = this._buildEmailContent(alert);
      
      // 发送邮件
      const info = await this.transporter.sendMail({
        from: this.config.from_address || '"API监控系统" <api-monitor@example.com>',
        to: recipients.join(', '),
        subject: subject,
        text: content.text,
        html: content.html
      });
      
      this.logger.info(`已发送告警邮件: ${subject} 至 ${recipients.join(', ')}`);
      this.logger.debug(`邮件发送详情: ${info.messageId}`);
      
      return true;
    } catch (error) {
      this.logger.error(`发送告警邮件时出错:`, error);
      return false;
    }
  }

  /**
   * 获取收件人列表
   * @param {Object} alert - 告警信息
   * @returns {Array} 收件人列表
   * @private
   */
  _getRecipients(alert) {
    // 首先检查告警特定的收件人
    if (alert.email_recipients && Array.isArray(alert.email_recipients) && alert.email_recipients.length > 0) {
      return alert.email_recipients;
    }
    
    // 然后检查特定告警级别的收件人
    const severityRecipients = this.config[`${alert.severity}_recipients`];
    if (severityRecipients && Array.isArray(severityRecipients) && severityRecipients.length > 0) {
      return severityRecipients;
    }
    
    // 最后使用默认收件人
    return this.config.recipients || [];
  }

  /**
   * 构建邮件主题
   * @param {Object} alert - 告警信息
   * @returns {string} 邮件主题
   * @private
   */
  _buildSubject(alert) {
    // 构建主题前缀
    let prefix = '[API监控';
    
    // 添加告警严重程度
    if (alert.severity) {
      const severityMap = {
        'info': '信息',
        'warning': '警告',
        'error': '错误',
        'critical': '严重'
      };
      const severityText = severityMap[alert.severity] || alert.severity;
      prefix += `-${severityText}`;
    }
    
    prefix += ']';
    
    // 添加告警状态
    if (alert.status === 'resolved') {
      return `${prefix} 已恢复: ${alert.name}`;
    } else {
      return `${prefix} ${alert.name}`;
    }
  }

  /**
   * 构建邮件内容
   * @param {Object} alert - 告警信息
   * @returns {Object} 邮件内容 { text, html }
   * @private
   */
  _buildEmailContent(alert) {
    // 构建纯文本内容
    let textContent = `${alert.message}\n\n`;
    
    // 添加时间戳
    textContent += `时间: ${alert.timestamp.toLocaleString()}\n`;
    
    // 添加严重程度
    textContent += `严重程度: ${alert.severity}\n`;
    
    // 添加状态
    textContent += `状态: ${alert.status === 'resolved' ? '已恢复' : '活跃'}\n`;
    
    // 如果有指标数据，添加相关信息
    if (alert.metric) {
      textContent += '\n指标详情:\n';
      
      if (alert.metric.api_name) {
        textContent += `API: ${alert.metric.api_name}\n`;
      }
      
      if (alert.metric.url) {
        textContent += `URL: ${alert.metric.url}\n`;
      }
      
      if (alert.metric.duration !== undefined) {
        textContent += `响应时间: ${alert.metric.duration}ms\n`;
      }
      
      if (alert.metric.status_code !== undefined) {
        textContent += `状态码: ${alert.metric.status_code}\n`;
      }
      
      if (alert.metric.error_rate !== undefined) {
        textContent += `错误率: ${(alert.metric.error_rate * 100).toFixed(2)}%\n`;
      }
      
      if (alert.metric.availability !== undefined) {
        textContent += `可用性: ${(alert.metric.availability * 100).toFixed(2)}%\n`;
      }
    }
    
    // 添加链接到监控系统
    if (this.config.monitor_url) {
      textContent += `\n查看详情: ${this.config.monitor_url}\n`;
    }
    
    // 构建HTML内容
    let htmlContent = `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: ${this._getSeverityColor(alert.severity)};">${this._buildSubject(alert)}</h2>
        <p style="font-size: 16px;">${alert.message}</p>
        <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
          <p><strong>时间:</strong> ${alert.timestamp.toLocaleString()}</p>
          <p><strong>严重程度:</strong> <span style="color: ${this._getSeverityColor(alert.severity)};">${alert.severity}</span></p>
          <p><strong>状态:</strong> <span style="color: ${alert.status === 'resolved' ? '#28a745' : '#dc3545'};">${alert.status === 'resolved' ? '已恢复' : '活跃'}</span></p>
    `;
    
    // 如果有指标数据，添加相关信息
    if (alert.metric) {
      htmlContent += `
        <div style="margin-top: 15px;">
          <h3 style="font-size: 18px;">指标详情</h3>
          <table style="width: 100%; border-collapse: collapse;">
      `;
      
      if (alert.metric.api_name) {
        htmlContent += `<tr><td style="padding: 8px 0;"><strong>API:</strong></td><td style="padding: 8px 0;">${alert.metric.api_name}</td></tr>`;
      }
      
      if (alert.metric.url) {
        htmlContent += `<tr><td style="padding: 8px 0;"><strong>URL:</strong></td><td style="padding: 8px 0;">${alert.metric.url}</td></tr>`;
      }
      
      if (alert.metric.duration !== undefined) {
        htmlContent += `<tr><td style="padding: 8px 0;"><strong>响应时间:</strong></td><td style="padding: 8px 0;">${alert.metric.duration}ms</td></tr>`;
      }
      
      if (alert.metric.status_code !== undefined) {
        htmlContent += `<tr><td style="padding: 8px 0;"><strong>状态码:</strong></td><td style="padding: 8px 0;">${alert.metric.status_code}</td></tr>`;
      }
      
      if (alert.metric.error_rate !== undefined) {
        htmlContent += `<tr><td style="padding: 8px 0;"><strong>错误率:</strong></td><td style="padding: 8px 0;">${(alert.metric.error_rate * 100).toFixed(2)}%</td></tr>`;
      }
      
      if (alert.metric.availability !== undefined) {
        htmlContent += `<tr><td style="padding: 8px 0;"><strong>可用性:</strong></td><td style="padding: 8px 0;">${(alert.metric.availability * 100).toFixed(2)}%</td></tr>`;
      }
      
      htmlContent += `
          </table>
        </div>
      `;
    }
    
    // 添加链接到监控系统
    if (this.config.monitor_url) {
      htmlContent += `
        <div style="margin-top: 20px;">
          <a href="${this.config.monitor_url}" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">查看详情</a>
        </div>
      `;
    }
    
    // 结束HTML内容
    htmlContent += `
        </div>
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #777;">
          此邮件由API监控系统自动发送，请勿回复。
        </div>
      </div>
    `;
    
    return {
      text: textContent,
      html: htmlContent
    };
  }

  /**
   * 获取告警严重程度对应的颜色
   * @param {string} severity - 告警严重程度
   * @returns {string} 颜色代码
   * @private
   */
  _getSeverityColor(severity) {
    const colorMap = {
      'info': '#17a2b8',
      'warning': '#ffc107',
      'error': '#dc3545',
      'critical': '#dc3545'
    };
    
    return colorMap[severity] || '#17a2b8';
  }
}

module.exports = EmailAlerter; 