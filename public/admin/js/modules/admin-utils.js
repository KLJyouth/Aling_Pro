/**
 * AlingAI Pro 管理后台工具函数
 * @version 1.0.0
 * @author AlingAi Team
 */

// 工具函数命名空间
const AdminUtils = {
    /**
     * 格式化日期时间
     * @param {Date|string|number} date - 日期对象、时间戳或日期字符串
     * @param {string} format - 格式化模式，默认为 'YYYY-MM-DD HH:mm:ss'
     * @returns {string} 格式化后的日期字符串
     */
    formatDateTime: function(date, format = 'YYYY-MM-DD HH:mm:ss') {
        const d = date instanceof Date ? date : new Date(date);
        
        if (isNaN(d.getTime())) {
            return 'Invalid Date';
        }
        
        const replacements = {
            'YYYY': d.getFullYear(),
            'MM': String(d.getMonth() + 1).padStart(2, '0'),
            'DD': String(d.getDate()).padStart(2, '0'),
            'HH': String(d.getHours()).padStart(2, '0'),
            'mm': String(d.getMinutes()).padStart(2, '0'),
            'ss': String(d.getSeconds()).padStart(2, '0'),
            'SSS': String(d.getMilliseconds()).padStart(3, '0')
        };
        
        return format.replace(/YYYY|MM|DD|HH|mm|ss|SSS/g, match => replacements[match]);
    },
    
    /**
     * 格式化文件大小
     * @param {number} bytes - 字节数
     * @param {number} decimals - 小数位数，默认为2
     * @returns {string} 格式化后的文件大小
     */
    formatFileSize: function(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
    },
    
    /**
     * 格式化持续时间
     * @param {number} seconds - 秒数
     * @returns {string} 格式化后的持续时间
     */
    formatDuration: function(seconds) {
        if (seconds < 60) {
            return `${seconds}秒`;
        } else if (seconds < 3600) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}分钟${remainingSeconds > 0 ? ` ${remainingSeconds}秒` : ''}`;
        } else if (seconds < 86400) {
            const hours = Math.floor(seconds / 3600);
            const remainingMinutes = Math.floor((seconds % 3600) / 60);
            return `${hours}小时${remainingMinutes > 0 ? ` ${remainingMinutes}分钟` : ''}`;
        } else {
            const days = Math.floor(seconds / 86400);
            const remainingHours = Math.floor((seconds % 86400) / 3600);
            return `${days}天${remainingHours > 0 ? ` ${remainingHours}小时` : ''}`;
        }
    },
    
    /**
     * 防抖函数
     * @param {Function} func - 要执行的函数
     * @param {number} wait - 等待时间（毫秒）
     * @returns {Function} 防抖处理后的函数
     */
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * 节流函数
     * @param {Function} func - 要执行的函数
     * @param {number} limit - 限制时间（毫秒）
     * @returns {Function} 节流处理后的函数
     */
    throttle: function(func, limit) {
        let inThrottle;
        return function executedFunction(...args) {
            if (!inThrottle) {
                func(...args);
                inThrottle = true;
                setTimeout(() => {
                    inThrottle = false;
                }, limit);
            }
        };
    },
    
    /**
     * 生成随机ID
     * @param {number} length - ID长度，默认为8
     * @returns {string} 随机ID
     */
    generateId: function(length = 8) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let id = '';
        for (let i = 0; i < length; i++) {
            id += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return id;
    },
    
    /**
     * 复制文本到剪贴板
     * @param {string} text - 要复制的文本
     * @returns {Promise<boolean>} 是否复制成功
     */
    copyToClipboard: async function(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return true;
            } else {
                // 回退方法
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                return successful;
            }
        } catch (error) {
            console.error('复制到剪贴板失败:', error);
            return false;
        }
    },
    
    /**
     * 下载数据为文件
     * @param {Object|string} data - 要下载的数据
     * @param {string} filename - 文件名
     * @param {string} type - MIME类型，默认为'application/json'
     */
    downloadData: function(data, filename, type = 'application/json') {
        const blob = typeof data === 'string' ? new Blob([data], { type }) 
                                             : new Blob([JSON.stringify(data, null, 2)], { type });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    },
    
    /**
     * 解析URL查询参数
     * @param {string} url - URL字符串，默认为当前URL
     * @returns {Object} 查询参数对象
     */
    parseQueryParams: function(url = window.location.href) {
        const params = {};
        const queryString = url.split('?')[1];
        
        if (!queryString) return params;
        
        const searchParams = new URLSearchParams(queryString);
        for (const [key, value] of searchParams.entries()) {
            params[key] = value;
        }
        
        return params;
    },
    
    /**
     * 格式化数字
     * @param {number} number - 要格式化的数字
     * @param {number} decimals - 小数位数，默认为0
     * @param {boolean} useGrouping - 是否使用千分位分隔符，默认为true
     * @returns {string} 格式化后的数字
     */
    formatNumber: function(number, decimals = 0, useGrouping = true) {
        return new Intl.NumberFormat('zh-CN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
            useGrouping: useGrouping
        }).format(number);
    },
    
    /**
     * 生成随机颜色
     * @param {boolean} asRgb - 是否返回RGB格式，默认为false（返回HEX格式）
     * @returns {string} 随机颜色
     */
    randomColor: function(asRgb = false) {
        const r = Math.floor(Math.random() * 256);
        const g = Math.floor(Math.random() * 256);
        const b = Math.floor(Math.random() * 256);
        
        if (asRgb) {
            return `rgb(${r}, ${g}, ${b})`;
        }
        
        return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    },
    
    /**
     * 验证电子邮件地址
     * @param {string} email - 电子邮件地址
     * @returns {boolean} 是否有效
     */
    validateEmail: function(email) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    },
    
    /**
     * 验证URL
     * @param {string} url - URL
     * @returns {boolean} 是否有效
     */
    validateUrl: function(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    },
    
    /**
     * 获取浏览器信息
     * @returns {Object} 浏览器信息
     */
    getBrowserInfo: function() {
        const userAgent = navigator.userAgent;
        let browserName = 'Unknown';
        let browserVersion = 'Unknown';
        
        // 检测浏览器
        if (userAgent.match(/chrome|chromium|crios/i)) {
            browserName = 'Chrome';
        } else if (userAgent.match(/firefox|fxios/i)) {
            browserName = 'Firefox';
        } else if (userAgent.match(/safari/i)) {
            browserName = 'Safari';
        } else if (userAgent.match(/opr\//i)) {
            browserName = 'Opera';
        } else if (userAgent.match(/edg/i)) {
            browserName = 'Edge';
        } else if (userAgent.match(/msie|trident/i)) {
            browserName = 'Internet Explorer';
        }
        
        // 提取版本号
        const versionMatch = userAgent.match(new RegExp(`${browserName}\\/([0-9\\.]+)`, 'i'));
        if (versionMatch && versionMatch[1]) {
            browserVersion = versionMatch[1];
        }
        
        return {
            name: browserName,
            version: browserVersion,
            userAgent: userAgent,
            platform: navigator.platform,
            language: navigator.language,
            cookiesEnabled: navigator.cookieEnabled
        };
    }
};

// 如果在Node.js环境中，导出模块
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminUtils;
} 