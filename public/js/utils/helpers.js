// 工具函数模块

/**
 * 格式化日期时间
 * @param {Date|string} date - 日期对象或日期字符串
 * @param {string} format - 格式字符串，默认'YYYY-MM-DD HH:mm:ss'
 * @returns {string} 格式化后的日期字符串
 */
export function formatDate(date, format = 'YYYY-MM-DD HH:mm:ss') {
    const d = new Date(date);
    if (isNaN(d.getTime())) return '';

    const pad = (num) => num.toString().padStart(2, '0');
    
    const replacements = {
        'YYYY': d.getFullYear(),
        'MM': pad(d.getMonth() + 1),
        'DD': pad(d.getDate()),
        'HH': pad(d.getHours()),
        'mm': pad(d.getMinutes()),
        'ss': pad(d.getSeconds())
    };

    return format.replace(/YYYY|MM|DD|HH|mm|ss/g, match => replacements[match]);
}

/**
 * 验证用户名
 * @param {string} username - 要验证的用户名
 * @returns {boolean} 是否有效
 */
export function validateUsername(username) {
    return typeof username === 'string' && 
           username.length >= 3 && 
           username.length <= 20 &&
           /^[a-zA-Z0-9_]+$/.test(username);
}

/**
 * 验证消息内容
 * @param {string} message - 要验证的消息
 * @returns {boolean} 是否有效
 */
export function validateMessage(message) {
    return typeof message === 'string' && 
           message.trim().length > 0 &&
           message.length <= 1000;
}

/**
 * 安全的DOM元素查询
 * @param {string} selector - CSS选择器
 * @param {HTMLElement} [parent=document] - 父元素
 * @returns {HTMLElement|null} 找到的元素或null
 */
export function safeQuerySelector(selector, parent = document) {
    try {
        return parent.querySelector(selector);
    } catch (error) {
        console.error('Invalid selector:', selector, error);
        return null;
    }
}

/**
 * 防抖函数
 * @param {Function} func - 要执行的函数
 * @param {number} delay - 延迟时间(ms)
 * @returns {Function} 防抖后的函数
 */
export function debounce(func, delay = 300) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

/**
 * 节流函数
 * @param {Function} func - 要执行的函数
 * @param {number} limit - 时间间隔(ms)
 * @returns {Function} 节流后的函数
 */
export function throttle(func, limit = 300) {
    let lastFunc;
    let lastRan;
    return function(...args) {
        if (!lastRan) {
            func.apply(this, args);
            lastRan = Date.now();
        } else {
            clearTimeout(lastFunc);
            lastFunc = setTimeout(() => {
                if (Date.now() - lastRan >= limit) {
                    func.apply(this, args);
                    lastRan = Date.now();
                }
            }, limit - (Date.now() - lastRan));
        }
    };
}

/**
 * 深度合并对象
 * @param {Object} target - 目标对象
 * @param {Object} source - 源对象
 * @returns {Object} 合并后的对象
 */
export function deepMerge(target, source) {
    const output = Object.assign({}, target);
    if (typeof target !== 'object' || typeof source !== 'object') {
        return source;
    }
    for (const key in source) {
        if (source.hasOwnProperty(key)) {
            if (typeof source[key] === 'object' && !Array.isArray(source[key])) {
                output[key] = deepMerge(target[key] || {}, source[key]);
            } else {
                output[key] = source[key];
            }
        }
    }
    return output;
}