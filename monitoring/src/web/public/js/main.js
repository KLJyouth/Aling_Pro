/**
 * AlingAi API监控系统 - 主要JavaScript文件
 * 处理所有页面共用的功能和逻辑
 */

document.addEventListener('DOMContentLoaded', function() {
  // 初始化工具提示
  initTooltips();
  
  // 初始化时间范围选择器
  initTimeRangeSelector();
  
  // 初始化刷新按钮
  initRefreshButton();
  
  // 初始化导出按钮
  initExportButton();
  
  // 检查告警更新
  checkAlerts();
  
  // 初始化自定义组件
  initCustomComponents();
  
  // 设置定时任务
  setInterval(checkAlerts, 60000); // 每分钟检查一次新告警
});

/**
 * 初始化工具提示
 */
function initTooltips() {
  // 检查Bootstrap工具提示是否可用
  if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
}

/**
 * 初始化时间范围选择器
 */
function initTimeRangeSelector() {
  const timeRangeLinks = document.querySelectorAll('.time-range');
  if (timeRangeLinks.length === 0) return;
  
  timeRangeLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const range = this.getAttribute('data-range');
      console.log('选择时间范围:', range);
      
      // 在这里实现时间范围切换逻辑
      // 例如更新URL参数、重新加载数据等
      
      // 更新当前选中的时间范围
      document.querySelector('.dropdown-toggle').textContent = this.textContent;
      
      // 触发自定义事件
      const event = new CustomEvent('timeRangeChanged', {
        detail: { range: range }
      });
      document.dispatchEvent(event);
    });
  });
}

/**
 * 初始化刷新按钮
 */
function initRefreshButton() {
  const refreshBtn = document.getElementById('refreshBtn');
  if (!refreshBtn) return;
  
  refreshBtn.addEventListener('click', function() {
    console.log('刷新数据');
    
    // 显示刷新图标旋转动画
    this.querySelector('i').classList.add('fa-spin');
    
    // 触发自定义事件
    const event = new CustomEvent('refreshData');
    document.dispatchEvent(event);
    
    // 模拟延迟后停止旋转
    setTimeout(() => {
      this.querySelector('i').classList.remove('fa-spin');
    }, 1000);
  });
}

/**
 * 初始化导出按钮
 */
function initExportButton() {
  const exportBtn = document.getElementById('exportBtn');
  if (!exportBtn) return;
  
  exportBtn.addEventListener('click', function() {
    console.log('导出数据');
    
    // 触发自定义事件
    const event = new CustomEvent('exportData');
    document.dispatchEvent(event);
  });
}

/**
 * 检查新告警
 */
function checkAlerts() {
  // 模拟AJAX请求检查新告警
  // 实际应用中应该通过API获取最新告警
  
  // 如果有新告警，显示通知
  if (Math.random() > 0.7) { // 模拟30%概率有新告警
    showAlertNotification({
      title: 'API响应时间过长',
      message: '用户API响应时间超过阈值 (2000ms)',
      time: new Date().toLocaleTimeString(),
      type: 'warning'
    });
  }
}

/**
 * 显示告警通知
 * @param {Object} alert 告警信息对象
 */
function showAlertNotification(alert) {
  const alertToast = document.getElementById('alertToast');
  if (!alertToast) return;
  
  // 设置告警内容
  document.getElementById('alertTitle').textContent = alert.title;
  document.getElementById('alertMessage').textContent = alert.message;
  document.getElementById('alertTime').textContent = alert.time;
  
  // 设置告警类型样式
  const alertHeader = alertToast.querySelector('.toast-header');
  alertHeader.className = 'toast-header';
  alertHeader.classList.add(alert.type === 'critical' ? 'bg-danger' : 'bg-warning');
  alertHeader.classList.add('text-white');
  
  // 显示通知
  const toast = new bootstrap.Toast(alertToast);
  toast.show();
}

/**
 * 初始化自定义组件
 */
function initCustomComponents() {
  // 注册全局组件渲染函数
  window.renderStatusBadge = renderStatusBadge;
  window.renderAlertList = renderAlertList;
  
  // 绑定告警列表中的按钮事件
  document.addEventListener('click', function(e) {
    // 处理告警确认按钮
    if (e.target.classList.contains('acknowledge-alert') || 
        e.target.parentElement.classList.contains('acknowledge-alert')) {
      const btn = e.target.classList.contains('acknowledge-alert') ? 
        e.target : e.target.parentElement;
      const alertId = btn.getAttribute('data-alert-id');
      acknowledgeAlert(alertId);
    }
    
    // 处理告警忽略按钮
    if (e.target.classList.contains('dismiss-alert') || 
        e.target.parentElement.classList.contains('dismiss-alert')) {
      const btn = e.target.classList.contains('dismiss-alert') ? 
        e.target : e.target.parentElement;
      const alertId = btn.getAttribute('data-alert-id');
      dismissAlert(alertId);
    }
  });
}

/**
 * 渲染状态徽章
 * @param {string} status 状态值
 * @param {string} customText 自定义显示文本
 * @returns {string} HTML字符串
 */
function renderStatusBadge(status, customText) {
  const statusClasses = {
    'healthy': 'bg-success',
    'warning': 'bg-warning text-dark',
    'critical': 'bg-danger',
    'unknown': 'bg-secondary',
    'pending': 'bg-info'
  };
  
  const statusTexts = {
    'healthy': '正常',
    'warning': '警告',
    'critical': '严重',
    'unknown': '未知',
    'pending': '检查中'
  };
  
  const statusClass = statusClasses[status] || statusClasses.unknown;
  const displayText = customText || statusTexts[status] || '未知';
  
  return `<span class="badge rounded-pill ${statusClass}">${displayText}</span>`;
}

/**
 * 渲染告警列表
 * @param {Array} alerts 告警对象数组
 * @param {Object} options 渲染选项
 * @returns {string} HTML字符串
 */
function renderAlertList(alerts, options = {}) {
  const limit = options.limit || alerts.length;
  const showActions = options.showActions !== undefined ? options.showActions : true;
  const showEmpty = options.showEmpty !== undefined ? options.showEmpty : true;
  const filteredAlerts = Array.isArray(alerts) ? alerts.slice(0, limit) : [];
  
  let html = '<div class="list-group">';
  
  if (filteredAlerts.length > 0) {
    filteredAlerts.forEach(alert => {
      const severityClass = alert.severity === 'critical' ? 'alert-critical' : 
        (alert.severity === 'warning' ? 'alert-warning' : 'alert-info');
      const timestamp = new Date(alert.timestamp).toLocaleString();
      
      html += `
        <div class="list-group-item list-group-item-action flex-column align-items-start ${severityClass}">
          <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">${escapeHtml(alert.title)}</h5>
            <small>${timestamp}</small>
          </div>
          <p class="mb-1">${escapeHtml(alert.message)}</p>
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">API: ${escapeHtml(alert.api_name)}</small>
            ${showActions ? `
              <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary acknowledge-alert" data-alert-id="${alert.id}">
                  确认
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger dismiss-alert" data-alert-id="${alert.id}">
                  忽略
                </button>
              </div>
            ` : ''}
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
 * 确认告警
 * @param {string} alertId 告警ID
 */
function acknowledgeAlert(alertId) {
  console.log('确认告警:', alertId);
  
  // 这里应该发送AJAX请求确认告警
  // 暂时只显示提示
  alert(`已确认告警: ${alertId}`);
  
  // 模拟从DOM移除告警元素
  const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`).closest('.list-group-item');
  if (alertElement) {
    alertElement.classList.add('alert-success');
    alertElement.classList.remove('alert-critical', 'alert-warning', 'alert-info');
    
    setTimeout(() => {
      alertElement.style.opacity = '0';
      setTimeout(() => {
        alertElement.remove();
        
        // 检查是否所有告警都已处理
        const alertsList = alertElement.closest('.list-group');
        if (alertsList && alertsList.querySelectorAll('.list-group-item').length === 0) {
          alertsList.innerHTML = `
            <div class="text-center py-3">
              <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
              <p class="mt-2">没有告警记录</p>
            </div>
          `;
        }
      }, 500);
    }, 1000);
  }
}

/**
 * 忽略告警
 * @param {string} alertId 告警ID
 */
function dismissAlert(alertId) {
  console.log('忽略告警:', alertId);
  
  // 这里应该发送AJAX请求忽略告警
  // 暂时只显示提示
  alert(`已忽略告警: ${alertId}`);
  
  // 模拟从DOM移除告警元素
  const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`).closest('.list-group-item');
  if (alertElement) {
    alertElement.style.opacity = '0';
    setTimeout(() => {
      alertElement.remove();
      
      // 检查是否所有告警都已处理
      const alertsList = alertElement.closest('.list-group');
      if (alertsList && alertsList.querySelectorAll('.list-group-item').length === 0) {
        alertsList.innerHTML = `
          <div class="text-center py-3">
            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
            <p class="mt-2">没有告警记录</p>
          </div>
        `;
      }
    }, 500);
  }
}

/**
 * 格式化日期时间
 * @param {Date|string|number} date 日期对象或时间戳
 * @param {string} format 格式字符串
 * @returns {string} 格式化后的日期字符串
 */
function formatDateTime(date, format = 'YYYY-MM-DD HH:mm:ss') {
  if (!date) return '';
  
  // 如果有moment.js可用
  if (typeof moment !== 'undefined') {
    return moment(date).format(format);
  }
  
  // 否则使用原生Date
  const d = new Date(date);
  return d.toLocaleString();
}

/**
 * 格式化数字
 * @param {number} num 要格式化的数字
 * @param {number} decimals 小数位数
 * @returns {string} 格式化后的数字字符串
 */
function formatNumber(num, decimals = 2) {
  if (isNaN(num)) return '0';
  return Number(num).toFixed(decimals);
}

/**
 * 格式化字节大小
 * @param {number} bytes 字节数
 * @returns {string} 格式化后的字符串
 */
function formatBytes(bytes) {
  if (bytes === 0) return '0 Bytes';
  
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * 格式化时间间隔
 * @param {number} ms 毫秒数
 * @returns {string} 格式化后的时间间隔
 */
function formatDuration(ms) {
  if (ms < 1000) return ms + ' ms';
  
  const seconds = ms / 1000;
  if (seconds < 60) return seconds.toFixed(2) + ' 秒';
  
  const minutes = seconds / 60;
  if (minutes < 60) return minutes.toFixed(1) + ' 分钟';
  
  const hours = minutes / 60;
  if (hours < 24) return hours.toFixed(1) + ' 小时';
  
  const days = hours / 24;
  return days.toFixed(1) + ' 天';
}

/**
 * 格式化状态码为友好文本
 * @param {number} code HTTP状态码
 * @returns {string} 状态码描述
 */
function formatStatusCode(code) {
  const statusMap = {
    200: '成功',
    201: '已创建',
    204: '无内容',
    400: '请求错误',
    401: '未授权',
    403: '禁止访问',
    404: '未找到',
    500: '服务器错误',
    502: '网关错误',
    503: '服务不可用',
    504: '网关超时'
  };
  
  return statusMap[code] || '未知状态 (' + code + ')';
}

/**
 * 转义HTML特殊字符
 * @param {string} text 要转义的文本
 * @returns {string} 转义后的文本
 */
function escapeHtml(text) {
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
 * 简单的防抖函数
 * @param {Function} func 要执行的函数
 * @param {number} wait 等待时间(毫秒)
 * @returns {Function} 防抖处理后的函数
 */
function debounce(func, wait = 300) {
  let timeout;
  
  return function(...args) {
    const context = this;
    clearTimeout(timeout);
    
    timeout = setTimeout(() => {
      func.apply(context, args);
    }, wait);
  };
}

/**
 * 简单的节流函数
 * @param {Function} func 要执行的函数
 * @param {number} limit 时间限制(毫秒)
 * @returns {Function} 节流处理后的函数
 */
function throttle(func, limit = 300) {
  let inThrottle;
  
  return function(...args) {
    const context = this;
    
    if (!inThrottle) {
      func.apply(context, args);
      inThrottle = true;
      
      setTimeout(() => {
        inThrottle = false;
      }, limit);
    }
  };
} 