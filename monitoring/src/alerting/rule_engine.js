/**
 * 规则引擎 - 评估指标并触发告警
 */

class RuleEngine {
  /**
   * 创建规则引擎
   * @param {Array} rules - 告警规则列表
   * @param {Object} logger - 日志记录器
   */
  constructor(rules, logger) {
    this.rules = rules || [];
    this.logger = logger;
    
    // 已触发的告警，用于防止重复告警
    // 格式：{ [ruleId-targetId]: { timestamp, count, status } }
    this.triggeredAlerts = {};
    
    // 告警解决状态
    this.ALERT_STATUS = {
      ACTIVE: 'active',
      RESOLVED: 'resolved'
    };
    
    // 告警清理间隔（毫秒）
    this.cleanupInterval = 1 * 60 * 60 * 1000; // 1小时
    this._setupCleanupInterval();
  }

  /**
   * 设置定期清理已解决的告警
   * @private
   */
  _setupCleanupInterval() {
    setInterval(() => {
      this._cleanupResolvedAlerts();
    }, this.cleanupInterval);
  }

  /**
   * 清理已解决的告警
   * @private
   */
  _cleanupResolvedAlerts() {
    const now = Date.now();
    let cleanupCount = 0;
    
    // 清理24小时前解决的告警
    const cutoffTime = now - 24 * 60 * 60 * 1000;
    
    for (const key in this.triggeredAlerts) {
      const alert = this.triggeredAlerts[key];
      if (alert.status === this.ALERT_STATUS.RESOLVED && alert.resolvedAt < cutoffTime) {
        delete this.triggeredAlerts[key];
        cleanupCount++;
      }
    }
    
    if (cleanupCount > 0) {
      this.logger.debug(`已清理 ${cleanupCount} 个已解决的告警记录`);
    }
  }

  /**
   * 评估指标数据
   * @param {Object|Array} metrics - 指标数据
   * @returns {Array} 触发的告警列表
   */
  evaluateMetrics(metrics) {
    if (!metrics) {
      return [];
    }
    
    // 确保metrics是数组
    const metricsArray = Array.isArray(metrics) ? metrics : [metrics];
    
    // 触发的告警列表
    const triggeredAlerts = [];
    
    // 评估每条指标
    for (const metric of metricsArray) {
      // 对每条规则进行评估
      for (const rule of this.rules) {
        try {
          const alertResult = this._evaluateRule(rule, metric);
          
          if (alertResult) {
            triggeredAlerts.push(alertResult);
          }
        } catch (error) {
          this.logger.error(`评估规则 "${rule.name}" 时出错:`, error);
        }
      }
    }
    
    return triggeredAlerts;
  }

  /**
   * 评估单个规则
   * @param {Object} rule - 告警规则
   * @param {Object} metric - 指标数据
   * @returns {Object|null} 告警对象或null
   * @private
   */
  _evaluateRule(rule, metric) {
    // 构建规则目标的唯一标识
    const targetId = this._getTargetId(metric);
    const ruleId = rule.name || rule.id || JSON.stringify(rule.condition);
    const alertKey = `${ruleId}-${targetId}`;
    
    // 计算告警条件
    const conditionMet = this._evaluateCondition(rule.condition, metric);
    
    // 如果条件满足
    if (conditionMet) {
      // 检查是否已经触发过该告警
      if (this.triggeredAlerts[alertKey]) {
        const existingAlert = this.triggeredAlerts[alertKey];
        
        // 如果告警已解决，重新激活
        if (existingAlert.status === this.ALERT_STATUS.RESOLVED) {
          existingAlert.status = this.ALERT_STATUS.ACTIVE;
          existingAlert.timestamp = Date.now();
          existingAlert.count += 1;
          
          // 创建新的告警对象返回
          return this._createAlertObject(rule, metric, existingAlert.count);
        } else {
          // 已经是活跃状态，增加计数但不触发新告警
          existingAlert.count += 1;
          return null;
        }
      } else {
        // 新告警
        this.triggeredAlerts[alertKey] = {
          timestamp: Date.now(),
          count: 1,
          status: this.ALERT_STATUS.ACTIVE
        };
        
        // 创建告警对象
        return this._createAlertObject(rule, metric, 1);
      }
    } else {
      // 条件不满足，如果之前有告警，标记为已解决
      if (this.triggeredAlerts[alertKey] && 
          this.triggeredAlerts[alertKey].status === this.ALERT_STATUS.ACTIVE) {
        
        this.triggeredAlerts[alertKey].status = this.ALERT_STATUS.RESOLVED;
        this.triggeredAlerts[alertKey].resolvedAt = Date.now();
        
        // 返回恢复告警
        return this._createRecoveryAlertObject(rule, metric);
      }
    }
    
    return null;
  }

  /**
   * 获取指标的目标ID
   * @param {Object} metric - 指标数据
   * @returns {string} 目标ID
   * @private
   */
  _getTargetId(metric) {
    // 根据指标类型生成目标ID
    if (metric.api_name) {
      return `api-${metric.api_name}`;
    } else if (metric.url) {
      return `url-${metric.url}`;
    } else if (metric.endpoint_name) {
      return `endpoint-${metric.endpoint_name}`;
    } else {
      return `metric-${JSON.stringify(metric).slice(0, 50)}`;
    }
  }

  /**
   * 评估告警条件
   * @param {string} condition - 告警条件表达式
   * @param {Object} metric - 指标数据
   * @returns {boolean} 是否满足条件
   * @private
   */
  _evaluateCondition(condition, metric) {
    if (!condition) {
      return false;
    }
    
    try {
      // 简单的条件表达式处理
      // 支持的操作符: >, <, >=, <=, ==, !=
      
      // 替换变量
      let processedCondition = condition;
      const variableRegex = /([a-zA-Z_][a-zA-Z0-9_]*)/g;
      
      processedCondition = processedCondition.replace(variableRegex, (match) => {
        // 检查metric中是否有该属性
        if (metric[match] !== undefined) {
          // 如果是字符串，添加引号
          if (typeof metric[match] === 'string') {
            return `"${metric[match]}"`;
          }
          return metric[match];
        }
        return match; // 保持原样
      });
      
      // 安全地评估条件
      // 使用Function构造函数而不是eval
      const evaluator = new Function('return ' + processedCondition);
      return evaluator();
    } catch (error) {
      this.logger.error(`评估条件 "${condition}" 时出错:`, error);
      return false;
    }
  }

  /**
   * 创建告警对象
   * @param {Object} rule - 告警规则
   * @param {Object} metric - 指标数据
   * @param {number} count - 触发次数
   * @returns {Object} 告警对象
   * @private
   */
  _createAlertObject(rule, metric, count) {
    // 构建基本告警信息
    const alert = {
      name: rule.name,
      severity: rule.severity || 'warning',
      condition: rule.condition,
      channels: rule.channels || [],
      timestamp: new Date(),
      count: count,
      status: 'active',
      metric: this._sanitizeMetric(metric)
    };
    
    // 构建告警消息
    alert.message = this._formatAlertMessage(rule, metric, count);
    
    return alert;
  }

  /**
   * 创建恢复告警对象
   * @param {Object} rule - 告警规则
   * @param {Object} metric - 指标数据
   * @returns {Object} 恢复告警对象
   * @private
   */
  _createRecoveryAlertObject(rule, metric) {
    // 构建恢复告警信息
    const alert = {
      name: `${rule.name} - 已恢复`,
      severity: 'info',
      condition: rule.condition,
      channels: rule.channels || [],
      timestamp: new Date(),
      status: 'resolved',
      metric: this._sanitizeMetric(metric)
    };
    
    // 构建恢复消息
    alert.message = this._formatRecoveryMessage(rule, metric);
    
    return alert;
  }

  /**
   * 格式化告警消息
   * @param {Object} rule - 告警规则
   * @param {Object} metric - 指标数据
   * @param {number} count - 触发次数
   * @returns {string} 格式化的消息
   * @private
   */
  _formatAlertMessage(rule, metric, count) {
    let message = '';
    
    // 如果规则定义了消息模板，使用它
    if (rule.message_template) {
      message = this._interpolateTemplate(rule.message_template, metric);
    } else {
      // 否则，构建默认消息
      message = `告警: ${rule.name}`;
      
      // 添加API或服务名称
      if (metric.api_name) {
        message += ` - API: ${metric.api_name}`;
      } else if (metric.endpoint_name) {
        message += ` - 端点: ${metric.endpoint_name}`;
      } else if (metric.url) {
        message += ` - URL: ${metric.url}`;
      }
      
      // 添加条件信息
      message += ` - 条件: ${rule.condition}`;
      
      // 添加值信息
      if (rule.condition.includes('response_time') && metric.duration !== undefined) {
        message += ` - 响应时间: ${metric.duration}ms`;
      } else if (rule.condition.includes('error_rate') && metric.error_rate !== undefined) {
        message += ` - 错误率: ${(metric.error_rate * 100).toFixed(2)}%`;
      } else if (rule.condition.includes('availability') && metric.availability !== undefined) {
        message += ` - 可用性: ${(metric.availability * 100).toFixed(2)}%`;
      }
    }
    
    // 添加触发次数
    if (count > 1) {
      message += ` (已触发 ${count} 次)`;
    }
    
    return message;
  }

  /**
   * 格式化恢复消息
   * @param {Object} rule - 告警规则
   * @param {Object} metric - 指标数据
   * @returns {string} 格式化的恢复消息
   * @private
   */
  _formatRecoveryMessage(rule, metric) {
    let message = `已恢复: ${rule.name}`;
    
    // 添加API或服务名称
    if (metric.api_name) {
      message += ` - API: ${metric.api_name}`;
    } else if (metric.endpoint_name) {
      message += ` - 端点: ${metric.endpoint_name}`;
    } else if (metric.url) {
      message += ` - URL: ${metric.url}`;
    }
    
    // 添加条件信息
    message += ` - 条件: ${rule.condition} 已不再满足`;
    
    // 添加当前值信息
    if (rule.condition.includes('response_time') && metric.duration !== undefined) {
      message += ` - 当前响应时间: ${metric.duration}ms`;
    } else if (rule.condition.includes('error_rate') && metric.error_rate !== undefined) {
      message += ` - 当前错误率: ${(metric.error_rate * 100).toFixed(2)}%`;
    } else if (rule.condition.includes('availability') && metric.availability !== undefined) {
      message += ` - 当前可用性: ${(metric.availability * 100).toFixed(2)}%`;
    }
    
    return message;
  }

  /**
   * 插值消息模板
   * @param {string} template - 消息模板
   * @param {Object} metric - 指标数据
   * @returns {string} 插值后的消息
   * @private
   */
  _interpolateTemplate(template, metric) {
    // 替换模板中的变量
    return template.replace(/\${([^}]+)}/g, (match, key) => {
      // 检查metric中是否有该属性
      if (metric[key] !== undefined) {
        return metric[key];
      }
      
      // 特殊格式化
      if (key === 'error_rate_percent' && metric.error_rate !== undefined) {
        return (metric.error_rate * 100).toFixed(2) + '%';
      } else if (key === 'availability_percent' && metric.availability !== undefined) {
        return (metric.availability * 100).toFixed(2) + '%';
      }
      
      return match; // 保持原样
    });
  }

  /**
   * 清理指标对象，移除过大的字段
   * @param {Object} metric - 原始指标
   * @returns {Object} 清理后的指标
   * @private
   */
  _sanitizeMetric(metric) {
    // 创建一个新对象，避免修改原始指标
    const sanitized = { ...metric };
    
    // 移除可能很大的字段
    delete sanitized.response_data;
    delete sanitized.request_body;
    delete sanitized.response_body;
    
    // 如果有metadata字段，确保它不会太大
    if (sanitized.metadata && typeof sanitized.metadata === 'object') {
      try {
        const metadataStr = JSON.stringify(sanitized.metadata);
        if (metadataStr.length > 1000) {
          sanitized.metadata = JSON.parse(metadataStr.slice(0, 1000) + '..."');
        }
      } catch (e) {
        delete sanitized.metadata;
      }
    }
    
    return sanitized;
  }

  /**
   * 添加新规则
   * @param {Object} rule - 告警规则
   */
  addRule(rule) {
    if (!rule.name) {
      throw new Error('规则必须有名称');
    }
    
    if (!rule.condition) {
      throw new Error('规则必须有条件');
    }
    
    this.rules.push(rule);
    this.logger.info(`已添加新规则: ${rule.name}`);
  }

  /**
   * 移除规则
   * @param {string} ruleName - 规则名称
   * @returns {boolean} 是否成功移除
   */
  removeRule(ruleName) {
    const initialLength = this.rules.length;
    this.rules = this.rules.filter(rule => rule.name !== ruleName);
    
    const removed = this.rules.length < initialLength;
    if (removed) {
      this.logger.info(`已移除规则: ${ruleName}`);
    } else {
      this.logger.warn(`未找到规则: ${ruleName}`);
    }
    
    return removed;
  }

  /**
   * 获取所有规则
   * @returns {Array} 规则列表
   */
  getRules() {
    return [...this.rules];
  }
}

module.exports = RuleEngine; 