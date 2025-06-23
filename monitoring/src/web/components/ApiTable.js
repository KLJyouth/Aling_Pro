/**
 * API表格组件
 * 用于在仪表盘和API列表页面中统一显示API状态表格
 */

class ApiTable {
  /**
   * 创建API表格组件
   * @param {Object} options - 配置选项
   */
  constructor(statusBadge) {
    this.statusBadge = statusBadge;
  }

  /**
   * 渲染API表格HTML
   * @param {Array} apiList - API对象数组
   * @param {Object} options - 渲染选项
   * @returns {string} HTML字符串
   */
  render(apiList, options = {}) {
    const limit = options.limit || apiList.length;
    const showPagination = options.showPagination !== undefined ? options.showPagination : true;
    const filteredApis = Array.isArray(apiList) ? apiList.slice(0, limit) : [];
    const tableId = options.tableId || 'apiTable';
    
    let html = `
      <div class="table-responsive">
        <table class="table table-bordered" id="${tableId}" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>API名称</th>
              <th>状态</th>
              <th>平均响应时间</th>
              <th>成功率</th>
              <th>最后检查时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
    `;
    
    if (filteredApis.length > 0) {
      filteredApis.forEach(api => {
        html += `
          <tr data-api-name="${this._escapeHtml(api.name)}">
            <td>${this._escapeHtml(api.name)}</td>
            <td>
              <span class="badge rounded-pill status-unknown" data-api-status="${api.name}">状态获取中...</span>
            </td>
            <td class="response-time-${api.name}">计算中...</td>
            <td class="success-rate-${api.name}">计算中...</td>
            <td class="last-check-${api.name}">--</td>
            <td>
              <a href="/apis/${encodeURIComponent(api.name)}" class="btn btn-primary btn-sm">
                <i class="bi bi-graph-up"></i> 详情
              </a>
            </td>
          </tr>
        `;
      });
    } else {
      html += `
        <tr>
          <td colspan="6" class="text-center">没有API数据</td>
        </tr>
      `;
    }
    
    html += `
          </tbody>
        </table>
      </div>
    `;
    
    if (showPagination && filteredApis.length > 10) {
      html += this._renderPagination(tableId);
    }
    
    // 添加初始化脚本
    html += `
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // 初始化数据表格
          if (typeof $.fn.DataTable !== 'undefined') {
            $('#${tableId}').DataTable({
              language: {
                search: "搜索:",
                lengthMenu: "显示 _MENU_ 条记录",
                info: "显示第 _START_ 至 _END_ 条记录，共 _TOTAL_ 条",
                infoEmpty: "显示第 0 至 0 条记录，共 0 条",
                infoFiltered: "(从 _MAX_ 条记录过滤)",
                paginate: {
                  first: "首页",
                  last: "末页",
                  next: "下一页",
                  previous: "上一页"
                }
              }
            });
          }
          
          // 获取API状态数据
          updateApiStatusData();
          
          // 定期更新API状态
          setInterval(updateApiStatusData, 60000);
        });
        
        function updateApiStatusData() {
          // 模拟获取API状态数据
          // 实际应用中应该通过API获取最新状态
          const apiRows = document.querySelectorAll('table#${tableId} tbody tr[data-api-name]');
          
          apiRows.forEach(row => {
            const apiName = row.getAttribute('data-api-name');
            
            // 模拟随机状态
            setTimeout(() => {
              const statuses = ['healthy', 'warning', 'critical', 'unknown'];
              const randomStatus = statuses[Math.floor(Math.random() * 4)];
              const responseTime = Math.floor(Math.random() * 1000) + 50;
              const successRate = (Math.random() * 20 + 80).toFixed(2);
              
              // 更新状态指示器
              updateApiStatus(apiName, randomStatus);
              
              // 更新其他指标
              document.querySelector('.response-time-' + apiName).textContent = responseTime + ' ms';
              document.querySelector('.success-rate-' + apiName).textContent = successRate + '%';
              document.querySelector('.last-check-' + apiName).textContent = new Date().toLocaleTimeString();
            }, Math.random() * 1000);
          });
        }
        
        function updateApiStatus(apiName, status) {
          const statusElement = document.querySelector('[data-api-status="' + apiName + '"]');
          if (!statusElement) return;
          
          // 移除所有状态类
          statusElement.classList.remove('status-unknown', 'bg-success', 'bg-warning', 'bg-danger', 'bg-secondary', 'text-dark');
          
          // 添加新状态类和文本
          if (status === 'healthy') {
            statusElement.classList.add('bg-success');
            statusElement.textContent = '正常';
          } else if (status === 'warning') {
            statusElement.classList.add('bg-warning', 'text-dark');
            statusElement.textContent = '警告';
          } else if (status === 'critical') {
            statusElement.classList.add('bg-danger');
            statusElement.textContent = '严重';
          } else {
            statusElement.classList.add('bg-secondary');
            statusElement.textContent = '未知';
          }
        }
      </script>
    `;
    
    return html;
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
   * 渲染分页控件
   * @param {string} tableId - 表格ID
   * @returns {string} HTML字符串
   * @private
   */
  _renderPagination(tableId) {
    return `
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="dataTables_info" id="${tableId}_info" role="status" aria-live="polite">
          显示1到10条，共<span id="${tableId}_total">0</span>条记录
        </div>
        <div class="dataTables_paginate paging_simple_numbers" id="${tableId}_paginate">
          <ul class="pagination">
            <li class="paginate_button page-item previous disabled" id="${tableId}_previous">
              <a href="#" aria-controls="${tableId}" data-dt-idx="0" tabindex="0" class="page-link">上一页</a>
            </li>
            <li class="paginate_button page-item active">
              <a href="#" aria-controls="${tableId}" data-dt-idx="1" tabindex="0" class="page-link">1</a>
            </li>
            <li class="paginate_button page-item next disabled" id="${tableId}_next">
              <a href="#" aria-controls="${tableId}" data-dt-idx="2" tabindex="0" class="page-link">下一页</a>
            </li>
          </ul>
        </div>
      </div>
    `;
  }

  /**
   * 生成API筛选控件HTML
   * @returns {string} HTML字符串
   */
  renderFilters() {
    return `
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">筛选API</h6>
        </div>
        <div class="card-body">
          <form id="apiFiltersForm">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="filterStatus" class="form-label">状态</label>
                <select class="form-select" id="filterStatus">
                  <option value="">全部</option>
                  <option value="healthy">正常</option>
                  <option value="warning">警告</option>
                  <option value="critical">严重</option>
                  <option value="unknown">未知</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="filterResponseTime" class="form-label">响应时间</label>
                <select class="form-select" id="filterResponseTime">
                  <option value="">全部</option>
                  <option value="fast">快速 (< 100ms)</option>
                  <option value="medium">中等 (100ms - 500ms)</option>
                  <option value="slow">慢速 (> 500ms)</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="filterSuccessRate" class="form-label">成功率</label>
                <select class="form-select" id="filterSuccessRate">
                  <option value="">全部</option>
                  <option value="high">高 (> 99%)</option>
                  <option value="medium">中 (95% - 99%)</option>
                  <option value="low">低 (< 95%)</option>
                </select>
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

module.exports = ApiTable; 