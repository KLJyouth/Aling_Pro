/**
 * WebSocket告警通道 - 通过WebSocket发送实时告警
 */

class WebSocketAlerter {
  /**
   * 创建WebSocket告警通道
   * @param {Object} config - 配置
   * @param {Object} logger - 日志记录器
   * @param {Object} io - Socket.IO实例
   */
  constructor(config, logger, io) {
    this.config = config || {};
    this.logger = logger;
    this.io = io;
    this.enabled = this.config.enabled !== false;
    this.namespace = this.config.namespace || '/alerts';
  }

  /**
   * 初始化WebSocket告警通道
   * @returns {Promise<void>}
   */
  async init() {
    if (!this.enabled) {
      this.logger.info('WebSocket告警通道已禁用');
      return;
    }
    
    if (!this.io) {
      this.logger.error('WebSocket告警通道初始化失败: Socket.IO实例未提供');
      this.enabled = false;
      return;
    }
    
    try {
      // 创建告警命名空间
      if (this.namespace !== '/') {
        this.alertNamespace = this.io.of(this.namespace);
        
        // 设置命名空间的连接处理程序
        this.alertNamespace.on('connection', (socket) => {
          this._handleConnection(socket);
        });
      } else {
        // 使用主命名空间
        this.alertNamespace = this.io;
        
        // 连接处理程序已在主告警管理器中设置
      }
      
      this.logger.info(`WebSocket告警通道初始化成功 (命名空间: ${this.namespace})`);
    } catch (error) {
      this.logger.error('初始化WebSocket告警通道时出错:', error);
      this.enabled = false;
    }
  }

  /**
   * 处理新的WebSocket连接
   * @param {Object} socket - Socket.IO套接字
   * @private
   */
  _handleConnection(socket) {
    this.logger.debug(`客户端已连接到WebSocket告警通道: ${socket.id}`);
    
    // 处理客户端订阅特定严重程度的告警
    socket.on('subscribe', (severityLevels) => {
      this._handleSubscribe(socket, severityLevels);
    });
    
    // 处理客户端取消订阅
    socket.on('unsubscribe', (severityLevels) => {
      this._handleUnsubscribe(socket, severityLevels);
    });
    
    // 处理断开连接
    socket.on('disconnect', () => {
      this.logger.debug(`客户端断开WebSocket告警通道: ${socket.id}`);
    });
  }

  /**
   * 处理订阅请求
   * @param {Object} socket - Socket.IO套接字
   * @param {Array} severityLevels - 严重程度级别数组
   * @private
   */
  _handleSubscribe(socket, severityLevels) {
    if (!Array.isArray(severityLevels)) {
      severityLevels = [severityLevels];
    }
    
    for (const severity of severityLevels) {
      if (typeof severity === 'string') {
        socket.join(`severity-${severity}`);
        this.logger.debug(`客户端 ${socket.id} 已订阅 ${severity} 级别的告警`);
      }
    }
    
    socket.emit('subscribe_success', { severityLevels });
  }

  /**
   * 处理取消订阅请求
   * @param {Object} socket - Socket.IO套接字
   * @param {Array} severityLevels - 严重程度级别数组
   * @private
   */
  _handleUnsubscribe(socket, severityLevels) {
    if (!Array.isArray(severityLevels)) {
      severityLevels = [severityLevels];
    }
    
    for (const severity of severityLevels) {
      if (typeof severity === 'string') {
        socket.leave(`severity-${severity}`);
        this.logger.debug(`客户端 ${socket.id} 已取消订阅 ${severity} 级别的告警`);
      }
    }
    
    socket.emit('unsubscribe_success', { severityLevels });
  }

  /**
   * 发送告警
   * @param {Object} alert - 告警信息
   * @returns {Promise<boolean>} 是否发送成功
   */
  async sendAlert(alert) {
    if (!this.enabled || !this.alertNamespace) {
      this.logger.warn('WebSocket告警通道未启用或未初始化，无法发送告警');
      return false;
    }
    
    try {
      // 准备发送的告警数据
      const alertData = this._prepareAlertData(alert);
      
      // 发送给所有订阅了相应严重程度的客户端
      this.alertNamespace.to(`severity-${alert.severity}`).emit('alert', alertData);
      
      // 始终发送给订阅了"all"的客户端
      this.alertNamespace.to('severity-all').emit('alert', alertData);
      
      // 广播给所有连接的客户端
      if (this.config.broadcast_all === true) {
        this.alertNamespace.emit('alert', alertData);
      }
      
      this.logger.info(`已通过WebSocket发送告警: ${alert.name}`);
      
      return true;
    } catch (error) {
      this.logger.error('通过WebSocket发送告警时出错:', error);
      return false;
    }
  }

  /**
   * 准备告警数据
   * @param {Object} alert - 原始告警数据
   * @returns {Object} 准备发送的告警数据
   * @private
   */
  _prepareAlertData(alert) {
    // 创建一个新对象，避免修改原始告警
    const alertData = { ...alert };
    
    // 添加客户端显示所需的额外信息
    alertData.received_at = new Date();
    alertData.source = 'api_monitoring';
    
    // 确保ID存在
    if (!alertData.id) {
      alertData.id = `alert-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }
    
    return alertData;
  }
}

module.exports = WebSocketAlerter; 