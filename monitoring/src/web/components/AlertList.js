/**
 * 告警列表组件
 * 用于在仪表盘和告警页面中统一显示告警列表
 */

class AlertList {
  /**
   * 创建告警列表组件
   */
  constructor() {
    this.severityClasses = {
      'critical': 'alert-critical',
      'warning': 'alert-warning',
      'info': 'alert-info'
    };
  }

  /**
   * 渲染告警列表HTML
   * @param {Array} alerts - 告警对象数组
   * @param {Object} options - 渲染选项
   * @returns {string} HTML字符串
   */
  render(alerts, options = {}) {
    const limit = options.limit || alerts.length;
    const showActions = options.showActions !== undefined ? options.showActions : true;
    const showEmpty = options.showEmpty !== undefined ? options.showEmpty : true;
    const filteredAlerts = Array.isArray(alerts) ? alerts.slice(0, limit) : [];
    
    let html = '<div class="list-group">';
    
    if (filteredAlerts.length > 0) {
      filteredAlerts.forEach(alert => {
        const severityClass = this.severityClasses[alert.severity] || '';
        const timestamp = new Date(alert.timestamp).toLocaleString();
        
        html += `
          <div class="list-group-item list-group-item-action flex-column align-items-start ${severityClass}">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-1">${this._escapeHtml(alert.title)}</h5>
              <small>${timestamp}</small>
            </div>
            <p class="mb-1">${this._escapeHtml(alert.message)}</p>
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted">API: ${this._escapeHtml(alert.api_name)}</small>
              ${showActions ? this._renderActions(alert) : ''}
            </div>
          </div>
        `;
      });
    } else if (showEmpty) {
      html += `
        <div class="text-center py-3">
          <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
          <p class="mt-2">没有告警记录</p>
        </div>
      `;
    }
    
    html += '</div>';
    
    return html;
  }

  /**
   * 渲染告警操作按钮
   * @param {Object} alert - 告警对象
   * @returns {string} HTML字符串
   * @private
   */
  _renderActions(alert) {
    return `
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-outline-primary acknowledge-alert" data-alert-id="${alert.id}">
          确认
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger dismiss-alert" data-alert-id="${alert.id}">
          忽略
        </button>
      </div>
    `;
  }

  /**
   * 转义HTML特殊字符
   * @param {string} text - 要转义的文本
   * @returns {string} 转义后的文本
   * @private
   */
  _escapeHtml(text) {
    if (typeof text !== 'string') {
      return '';
    }
    
    return text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  /**
   * 生成告警筛选控件HTML
   * @returns {string} HTML字符串
   */
  renderFilters() {
    return `
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">筛选告警</h6>
        </div>
        <div class="card-body">
          <form id="alertFiltersForm">
            <div class="row">
              <div class="col-md-3 mb-3">
                <label for="filterSeverity" class="form-label">严重程度</label>
                <select class="form-select" id="filterSeverity">
                  <option value="">全部</option>
                  <option value="critical">严重</option>
                  <option value="warning">警告</option>
                  <option value="info">信息</option>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="filterApi" class="form-label">API</label>
                <select class="form-select" id="filterApi">
                  <option value="">全部</option>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="filterDateFrom" class="form-label">开始日期</label>
                <input type="date" class="form-control" id="filterDateFrom">
              </div>
              <div class="col-md-3 mb-3">
                <label for="filterDateTo" class="form-label">结束日期</label>
                <input type="date" class="form-control" id="filterDateTo">
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-filter"></i> 应用筛选
                </button>
                <button type="reset" class="btn btn-secondary">
                  <i class="bi bi-x-circle"></i> 重置
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    `;
  }
}

module.exports = AlertList; 