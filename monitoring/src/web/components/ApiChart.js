/**
 * API图表组件
 * 用于在仪表盘和API详情页面中统一显示API指标图表
 */

class ApiChart {
  /**
   * 创建API图表组件
   */
  constructor() {
    this.chartColors = {
      success: 'rgba(28, 200, 138, 0.6)',
      error: 'rgba(231, 74, 59, 0.6)',
      warning: 'rgba(246, 194, 62, 0.6)',
      timeout: 'rgba(54, 185, 204, 0.6)',
      primary: 'rgba(78, 115, 223, 0.6)'
    };
  }

  /**
   * 生成响应时间图表的HTML容器和初始化脚本
   * @param {string} containerId - 图表容器ID
   * @param {string} title - 图表标题
   * @param {Object} options - 其他选项
   * @returns {string} HTML字符串
   */
  renderResponseTimeChart(containerId, title, options = {}) {
    const chartHeight = options.height || '300px';
    
    return `
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary">${title}</h6>
          <div class="dropdown no-arrow">
            <button class="btn btn-link btn-sm dropdown-toggle" type="button" id="${containerId}Options"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
              <a class="dropdown-item refresh-chart" href="#" data-chart="${containerId}">刷新图表</a>
              <a class="dropdown-item download-chart" href="#" data-chart="${containerId}">下载图表</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item change-time-range" href="#" data-chart="${containerId}" data-range="1h">最近1小时</a>
              <a class="dropdown-item change-time-range" href="#" data-chart="${containerId}" data-range="6h">最近6小时</a>
              <a class="dropdown-item change-time-range" href="#" data-chart="${containerId}" data-range="24h">最近24小时</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="chart-area" style="height: ${chartHeight};">
            <canvas id="${containerId}"></canvas>
          </div>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // 初始化响应时间图表
          const ctx = document.getElementById('${containerId}').getContext('2d');
          window.charts = window.charts || {};
          
          window.charts['${containerId}'] = new Chart(ctx, {
            type: 'line',
            data: {
              labels: [],
              datasets: []
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'top',
                },
                tooltip: {
                  mode: 'index',
                  intersect: false,
                }
              },
              scales: {
                x: {
                  title: {
                    display: true,
                    text: '时间'
                  }
                },
                y: {
                  beginAtZero: true,
                  title: {
                    display: true,
                    text: '响应时间 (ms)'
                  }
                }
              }
            }
          });
        });
      </script>
    `;
  }

  /**
   * 生成状态分布图表的HTML容器和初始化脚本
   * @param {string} containerId - 图表容器ID
   * @param {string} title - 图表标题
   * @param {Object} options - 其他选项
   * @returns {string} HTML字符串
   */
  renderStatusChart(containerId, title, options = {}) {
    const chartHeight = options.height || '300px';
    
    return `
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">${title}</h6>
        </div>
        <div class="card-body">
          <div class="chart-pie" style="height: ${chartHeight};">
            <canvas id="${containerId}"></canvas>
          </div>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // 初始化状态分布图表
          const ctx = document.getElementById('${containerId}').getContext('2d');
          window.charts = window.charts || {};
          
          window.charts['${containerId}'] = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['成功', '错误', '超时'],
              datasets: [{
                data: [0, 0, 0],
                backgroundColor: [
                  '${this.chartColors.success}',
                  '${this.chartColors.error}',
                  '${this.chartColors.timeout}'
                ],
                borderColor: [
                  '${this.chartColors.success.replace('0.6', '1')}',
                  '${this.chartColors.error.replace('0.6', '1')}',
                  '${this.chartColors.timeout.replace('0.6', '1')}'
                ],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'bottom'
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      const label = context.label || '';
                      const value = context.raw || 0;
                      const total = context.dataset.data.reduce((a, b) => a + b, 0);
                      const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                      return `${label}: ${value} (${percentage}%)`;
                    }
                  }
                }
              }
            }
          });
        });
      </script>
    `;
  }

  /**
   * 更新图表数据的JavaScript代码
   * @param {string} chartId - 图表ID
   * @param {Object} data - 图表数据
   * @returns {string} JavaScript代码
   */
  updateChartScript(chartId, data) {
    return `
      if (window.charts && window.charts['${chartId}']) {
        const chart = window.charts['${chartId}'];
        chart.data.labels = ${JSON.stringify(data.labels)};
        chart.data.datasets = ${JSON.stringify(data.datasets)};
        chart.update();
      }
    `;
  }
}

module.exports = ApiChart; 