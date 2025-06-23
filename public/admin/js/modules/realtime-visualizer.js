/**
 * AlingAI Pro 实时数据可视化模块
 * 提供高级数据可视化和实时数据流处理功能
 * @version 1.0.0
 * @author AlingAi Team
 */

class RealtimeVisualizer {
    constructor(options = {}) {
        this.options = Object.assign({
            container: null,
            dataSource: null,
            refreshInterval: 5000,
            maxDataPoints: 100,
            theme: 'light',
            animations: true,
            responsive: true,
            legendPosition: 'top',
            tooltips: true,
            title: '',
            type: 'line', // 可选：line, bar, area, scatter, pie, donut
            colors: [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
            ]
        }, options);
        
        this.data = {
            labels: [],
            datasets: []
        };
        
        this.chart = null;
        this.refreshTimer = null;
        this.isInitialized = false;
        this.dataBuffer = [];
    }
    
    /**
     * 初始化可视化
     */
    async initialize() {
        if (this.isInitialized) return;
        
        try {
            // 检查依赖
            this.checkDependencies();
            
            // 初始化容器
            this.initializeContainer();
            
            // 创建图表
            await this.createChart();
            
            // 设置数据源
            if (this.options.dataSource) {
                await this.setDataSource(this.options.dataSource);
            }
            
            this.isInitialized = true;
            this.log('可视化模块初始化完成');
        } catch (error) {
            this.log('可视化模块初始化失败: ' + error.message, 'error');
            throw error;
        }
    }
    
    /**
     * 检查依赖
     */
    checkDependencies() {
        if (typeof Chart === 'undefined') {
            throw new Error('Chart.js 未加载，请先加载 Chart.js 库');
        }
        
        if (!this.options.container) {
            throw new Error('未指定容器元素');
        }
        
        const container = this.getContainer();
        if (!container) {
            throw new Error(`找不到容器元素: ${this.options.container}`);
        }
    }
    
    /**
     * 获取容器元素
     */
    getContainer() {
        if (typeof this.options.container === 'string') {
            return document.querySelector(this.options.container);
        }
        
        return this.options.container;
    }
    
    /**
     * 初始化容器
     */
    initializeContainer() {
        const container = this.getContainer();
        
        // 清空容器
        container.innerHTML = '';
        
        // 创建画布
        const canvas = document.createElement('canvas');
        canvas.id = `chart-${Math.random().toString(36).substr(2, 9)}`;
        container.appendChild(canvas);
        
        this.canvas = canvas;
    }
    
    /**
     * 创建图表
     */
    async createChart() {
        const ctx = this.canvas.getContext('2d');
        
        // 配置图表
        const config = {
            type: this.options.type,
            data: this.data,
            options: {
                responsive: this.options.responsive,
                maintainAspectRatio: false,
                animation: this.options.animations ? {
                    duration: 1000,
                    easing: 'easeOutQuart'
                } : false,
                plugins: {
                    legend: {
                        display: true,
                        position: this.options.legendPosition
                    },
                    tooltip: {
                        enabled: this.options.tooltips
                    },
                    title: {
                        display: !!this.options.title,
                        text: this.options.title
                    }
                },
                scales: this.getScalesConfig()
            }
        };
        
        // 创建图表
        this.chart = new Chart(ctx, config);
    }
    
    /**
     * 获取坐标轴配置
     */
    getScalesConfig() {
        // 根据图表类型返回不同的配置
        switch (this.options.type) {
            case 'pie':
            case 'donut':
                return {}; // 饼图不需要坐标轴
            case 'bar':
                return {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: this.options.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                };
            case 'line':
            case 'area':
            case 'scatter':
            default:
                return {
                    x: {
                        grid: {
                            color: this.options.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: this.options.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                };
        }
    }
    
    /**
     * 设置数据源
     * @param {string|Function} dataSource - 数据源URL或数据提供函数
     */
    async setDataSource(dataSource) {
        // 清除现有定时器
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
        
        this.options.dataSource = dataSource;
        
        // 加载初始数据
        await this.refreshData();
        
        // 设置定时刷新
        if (this.options.refreshInterval > 0) {
            this.refreshTimer = setInterval(() => {
                this.refreshData();
            }, this.options.refreshInterval);
        }
    }
    
    /**
     * 刷新数据
     */
    async refreshData() {
        try {
            let newData;
            
            if (typeof this.options.dataSource === 'function') {
                // 如果数据源是函数，调用它获取数据
                newData = await this.options.dataSource();
            } else if (typeof this.options.dataSource === 'string') {
                // 如果数据源是URL，从服务器获取数据
                const response = await fetch(this.options.dataSource);
                if (!response.ok) {
                    throw new Error(`API请求失败: ${response.status} ${response.statusText}`);
                }
                newData = await response.json();
            } else {
                throw new Error('无效的数据源');
            }
            
            // 更新图表数据
            this.updateChartData(newData);
            
            return newData;
        } catch (error) {
            this.log('刷新数据失败: ' + error.message, 'error');
            throw error;
        }
    }
    
    /**
     * 更新图表数据
     * @param {Object} newData - 新数据
     */
    updateChartData(newData) {
        if (!newData || !this.chart) return;
        
        // 处理不同格式的数据
        if (Array.isArray(newData)) {
            // 如果是数组，假设是时间序列数据
            this.updateTimeSeriesData(newData);
        } else if (newData.labels && newData.datasets) {
            // 如果已经是Chart.js格式的数据
            this.data = newData;
            this.chart.data = newData;
            this.chart.update();
        } else {
            // 其他格式，尝试转换
            this.convertAndUpdateData(newData);
        }
    }
    
    /**
     * 更新时间序列数据
     * @param {Array} dataPoints - 数据点数组
     */
    updateTimeSeriesData(dataPoints) {
        // 将新数据点添加到缓冲区
        this.dataBuffer = [...this.dataBuffer, ...dataPoints];
        
        // 如果超出最大数据点数量，移除旧数据
        if (this.dataBuffer.length > this.options.maxDataPoints) {
            this.dataBuffer = this.dataBuffer.slice(-this.options.maxDataPoints);
        }
        
        // 提取标签和值
        const labels = this.dataBuffer.map(point => {
            if (point.timestamp) {
                // 如果有时间戳，格式化为时间
                return this.formatTimestamp(point.timestamp);
            }
            return point.label || '';
        });
        
        // 提取数据集
        const datasets = [];
        const seriesKeys = this.getSeriesKeys(this.dataBuffer);
        
        seriesKeys.forEach((key, index) => {
            const color = this.options.colors[index % this.options.colors.length];
            
            datasets.push({
                label: key,
                data: this.dataBuffer.map(point => point[key] || 0),
                backgroundColor: this.getBackgroundColor(color, this.options.type),
                borderColor: color,
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 3,
                fill: this.options.type === 'area'
            });
        });
        
        // 更新图表数据
        this.data = { labels, datasets };
        this.chart.data = this.data;
        this.chart.update();
    }
    
    /**
     * 获取数据系列的键
     * @param {Array} dataPoints - 数据点数组
     * @returns {Array} 数据系列键数组
     */
    getSeriesKeys(dataPoints) {
        if (!dataPoints || dataPoints.length === 0) return [];
        
        // 收集所有可能的键
        const keys = new Set();
        dataPoints.forEach(point => {
            Object.keys(point).forEach(key => {
                // 排除特殊键
                if (!['timestamp', 'label', 'id', 'time'].includes(key)) {
                    keys.add(key);
                }
            });
        });
        
        return Array.from(keys);
    }
    
    /**
     * 获取背景颜色
     * @param {string} color - 基础颜色
     * @param {string} chartType - 图表类型
     * @returns {string|CanvasGradient} 背景颜色或渐变
     */
    getBackgroundColor(color, chartType) {
        if (chartType === 'line' || chartType === 'area') {
            // 为线图和面积图创建渐变
            const ctx = this.canvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, this.canvas.height);
            gradient.addColorStop(0, this.hexToRgba(color, 0.5));
            gradient.addColorStop(1, this.hexToRgba(color, 0.05));
            return gradient;
        }
        
        // 为其他图表类型返回半透明颜色
        return this.hexToRgba(color, 0.7);
    }
    
    /**
     * 将十六进制颜色转换为RGBA
     * @param {string} hex - 十六进制颜色
     * @param {number} alpha - 透明度
     * @returns {string} RGBA颜色字符串
     */
    hexToRgba(hex, alpha) {
        // 检查是否已经是RGB或RGBA格式
        if (hex.startsWith('rgb')) {
            return hex;
        }
        
        // 移除#前缀
        hex = hex.replace('#', '');
        
        // 扩展3位颜色到6位
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        
        // 解析RGB值
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    
    /**
     * 格式化时间戳
     * @param {number|string|Date} timestamp - 时间戳
     * @returns {string} 格式化的时间字符串
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        
        if (isNaN(date.getTime())) {
            return timestamp;
        }
        
        return date.toLocaleTimeString();
    }
    
    /**
     * 转换并更新数据
     * @param {Object} data - 原始数据
     */
    convertAndUpdateData(data) {
        // 尝试将各种数据格式转换为Chart.js格式
        const labels = [];
        const datasets = [];
        
        // 处理对象格式的数据
        if (typeof data === 'object' && !Array.isArray(data)) {
            const keys = Object.keys(data);
            const values = Object.values(data);
            
            labels.push(...keys);
            datasets.push({
                label: '数据',
                data: values,
                backgroundColor: keys.map((_, i) => 
                    this.options.colors[i % this.options.colors.length]
                ),
                borderWidth: 1
            });
        }
        
        // 更新图表数据
        if (labels.length > 0 && datasets.length > 0) {
            this.data = { labels, datasets };
            this.chart.data = this.data;
            this.chart.update();
        } else {
            this.log('无法转换数据格式', 'warn');
        }
    }
    
    /**
     * 添加数据点
     * @param {Object} dataPoint - 单个数据点
     */
    addDataPoint(dataPoint) {
        if (!this.chart) return;
        
        this.dataBuffer.push(dataPoint);
        
        // 如果超出最大数据点数量，移除最旧的数据点
        if (this.dataBuffer.length > this.options.maxDataPoints) {
            this.dataBuffer.shift();
        }
        
        // 更新图表
        this.updateTimeSeriesData(this.dataBuffer);
    }
    
    /**
     * 清除数据
     */
    clearData() {
        this.dataBuffer = [];
        
        if (this.chart) {
            this.data = {
                labels: [],
                datasets: []
            };
            
            this.chart.data = this.data;
            this.chart.update();
        }
    }
    
    /**
     * 更新图表类型
     * @param {string} type - 图表类型
     */
    updateChartType(type) {
        if (!this.chart) return;
        
        this.options.type = type;
        this.chart.config.type = type;
        
        // 更新数据集配置
        this.chart.data.datasets.forEach((dataset, index) => {
            const color = this.options.colors[index % this.options.colors.length];
            dataset.backgroundColor = this.getBackgroundColor(color, type);
            dataset.fill = type === 'area';
        });
        
        // 更新坐标轴配置
        this.chart.options.scales = this.getScalesConfig();
        
        this.chart.update();
    }
    
    /**
     * 更新主题
     * @param {string} theme - 主题 ('light' 或 'dark')
     */
    updateTheme(theme) {
        this.options.theme = theme;
        
        // 更新坐标轴颜色
        this.chart.options.scales = this.getScalesConfig();
        
        // 更新字体颜色
        this.chart.options.plugins.legend.labels.color = 
            theme === 'dark' ? '#F3F4F6' : '#1F2937';
        
        this.chart.options.plugins.title.color = 
            theme === 'dark' ? '#F3F4F6' : '#1F2937';
        
        this.chart.update();
    }
    
    /**
     * 导出图表为图片
     * @param {string} format - 导出格式 ('png', 'jpeg', 'webp')
     * @returns {string} 数据URL
     */
    exportAsImage(format = 'png') {
        if (!this.chart) return null;
        
        return this.chart.toBase64Image(format);
    }
    
    /**
     * 导出数据为CSV
     * @returns {string} CSV字符串
     */
    exportAsCSV() {
        if (!this.data || !this.data.labels || !this.data.datasets) {
            return '';
        }
        
        // 创建表头
        const headers = ['Label', ...this.data.datasets.map(ds => ds.label || 'Series')];
        
        // 创建数据行
        const rows = this.data.labels.map((label, i) => {
            return [
                label,
                ...this.data.datasets.map(ds => ds.data[i])
            ];
        });
        
        // 组合CSV
        const csv = [
            headers.join(','),
            ...rows.map(row => row.join(','))
        ].join('\n');
        
        return csv;
    }
    
    /**
     * 销毁实例
     */
    destroy() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
        
        this.isInitialized = false;
        this.log('可视化模块已销毁');
    }
    
    /**
     * 记录日志
     * @param {string} message - 日志消息
     * @param {string} level - 日志级别
     */
    log(message, level = 'info') {
        const timestamp = new Date().toISOString();
        
        switch (level) {
            case 'error':
                console.error(`[RealtimeVisualizer] ${timestamp} - ${message}`);
                break;
            case 'warn':
                console.warn(`[RealtimeVisualizer] ${timestamp} - ${message}`);
                break;
            case 'info':
            default:
                console.info(`[RealtimeVisualizer] ${timestamp} - ${message}`);
                break;
        }
    }
}

// 如果在浏览器环境中，将RealtimeVisualizer添加到全局对象
if (typeof window !== 'undefined') {
    window.RealtimeVisualizer = RealtimeVisualizer;
}

// 如果在Node.js环境中，导出模块
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealtimeVisualizer;
} 