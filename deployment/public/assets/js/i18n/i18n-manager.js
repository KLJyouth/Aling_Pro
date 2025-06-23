/**
 * AlingAi Pro - 国际化管理器
 * 全面的多语言支持系统，支持动态切换、延迟加载和本地化格式化
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @features
 * - 多语言支持
 * - 动态语言切换
 * - 延迟加载翻译
 * - 日期时间格式化
 * - 数字货币格式化
 * - 复数规则支持
 * - 命名空间管理
 */

class AlingI18nManager {
    constructor(options = {}) {
        this.options = {
            defaultLocale: 'zh-CN',
            fallbackLocale: 'en-US',
            loadPath: '/assets/locales/{{lng}}/{{ns}}.json',
            defaultNamespace: 'common',
            namespaces: ['common', 'ui', 'errors', 'forms'],
            enableCache: true,
            enableInterpolation: true,
            enablePlurals: true,
            enableDateFormat: true,
            enableNumberFormat: true,
            missingKeyHandler: null,
            postProcessor: null,
            debug: false,
            ...options
        };

        this.currentLocale = this.options.defaultLocale;
        this.translations = new Map();
        this.formatters = new Map();
        this.loadedNamespaces = new Set();
        this.observers = new Set();
        this.cache = new Map();
        
        this.init();
    }

    async init() {
        // 检测用户偏好语言
        this.detectUserLocale();
        
        // 初始化格式化器
        this.initFormatters();
        
        // 加载默认翻译
        await this.loadNamespace(this.options.defaultNamespace);
        
        // 设置DOM观察器
        this.setupDOMObserver();
        
        console.log('✅ AlingAi 国际化管理器初始化完成');
    }

    /**
     * 检测用户偏好语言
     */
    detectUserLocale() {
        // 优先级：URL参数 > localStorage > navigator.language > 默认语言
        const urlParams = new URLSearchParams(window.location.search);
        const urlLang = urlParams.get('lang');
        
        const storedLang = localStorage.getItem('preferred_language');
        const browserLang = navigator.language || navigator.languages?.[0];
        
        const preferredLocale = urlLang || storedLang || browserLang || this.options.defaultLocale;
        
        this.setLocale(preferredLocale);
    }

    /**
     * 初始化格式化器
     */
    initFormatters() {
        // 日期格式化器
        this.formatters.set('date', new Map());
        this.formatters.set('number', new Map());
        this.formatters.set('currency', new Map());
        this.formatters.set('relative', new Map());
    }

    /**
     * 设置当前语言
     * @param {string} locale - 语言代码
     */
    async setLocale(locale) {
        if (this.currentLocale === locale) return;

        const oldLocale = this.currentLocale;
        this.currentLocale = locale;

        // 保存用户偏好
        localStorage.setItem('preferred_language', locale);

        // 加载语言包
        await this.loadLocaleData(locale);

        // 更新页面语言属性
        document.documentElement.lang = locale;

        // 重新渲染页面翻译
        this.translatePage();

        // 通知观察者
        this.notifyLocaleChange(locale, oldLocale);

        // 触发事件
        this.emit('locale:changed', { 
            locale, 
            oldLocale,
            translations: this.getTranslations(locale)
        });
    }

    /**
     * 获取当前语言
     */
    getLocale() {
        return this.currentLocale;
    }

    /**
     * 加载语言数据
     * @param {string} locale - 语言代码
     */
    async loadLocaleData(locale) {
        const promises = this.options.namespaces.map(namespace => 
            this.loadNamespace(namespace, locale)
        );
        
        await Promise.all(promises);
    }

    /**
     * 加载命名空间
     * @param {string} namespace - 命名空间
     * @param {string} locale - 语言代码
     */
    async loadNamespace(namespace, locale = this.currentLocale) {
        const key = `${locale}:${namespace}`;
        
        // 检查缓存
        if (this.options.enableCache && this.cache.has(key)) {
            const cached = this.cache.get(key);
            this.setTranslations(locale, namespace, cached);
            return cached;
        }

        try {
            const url = this.options.loadPath
                .replace('{{lng}}', locale)
                .replace('{{ns}}', namespace);

            const response = await fetch(url);
            
            if (!response.ok) {
                // 如果当前语言加载失败，尝试加载回退语言
                if (locale !== this.options.fallbackLocale) {
                    return await this.loadNamespace(namespace, this.options.fallbackLocale);
                }
                throw new Error(`Failed to load ${namespace} for ${locale}`);
            }

            const translations = await response.json();
            
            // 缓存翻译数据
            if (this.options.enableCache) {
                this.cache.set(key, translations);
            }

            // 设置翻译
            this.setTranslations(locale, namespace, translations);
            this.loadedNamespaces.add(key);

            if (this.options.debug) {
                console.log(`翻译加载完成: ${key}`, translations);
            }

            return translations;

        } catch (error) {
            console.error(`加载翻译失败: ${key}`, error);
            
            // 如果不是回退语言，尝试回退语言
            if (locale !== this.options.fallbackLocale) {
                return await this.loadNamespace(namespace, this.options.fallbackLocale);
            }
            
            return {};
        }
    }

    /**
     * 设置翻译数据
     * @param {string} locale - 语言代码
     * @param {string} namespace - 命名空间
     * @param {object} translations - 翻译数据
     */
    setTranslations(locale, namespace, translations) {
        if (!this.translations.has(locale)) {
            this.translations.set(locale, new Map());
        }
        
        this.translations.get(locale).set(namespace, translations);
    }

    /**
     * 获取翻译数据
     * @param {string} locale - 语言代码
     * @param {string} namespace - 命名空间
     */
    getTranslations(locale = this.currentLocale, namespace = null) {
        const localeData = this.translations.get(locale);
        if (!localeData) return {};

        if (namespace) {
            return localeData.get(namespace) || {};
        }

        // 合并所有命名空间
        const merged = {};
        localeData.forEach((translations, ns) => {
            Object.assign(merged, translations);
        });
        
        return merged;
    }

    /**
     * 翻译文本
     * @param {string} key - 翻译键
     * @param {object} options - 选项
     */
    t(key, options = {}) {
        const {
            lng = this.currentLocale,
            ns = this.options.defaultNamespace,
            defaultValue = key,
            interpolation = {},
            count = null,
            context = null
        } = options;

        // 构建完整的键
        let fullKey = key;
        if (ns && !key.includes(':')) {
            fullKey = `${ns}:${key}`;
        }

        // 处理命名空间
        const [namespace, ...keyParts] = fullKey.split(':');
        const actualKey = keyParts.join(':') || namespace;
        const actualNs = keyParts.length > 0 ? namespace : ns;

        // 获取翻译
        let translation = this.getTranslation(lng, actualNs, actualKey);

        // 回退到默认语言
        if (!translation && lng !== this.options.fallbackLocale) {
            translation = this.getTranslation(this.options.fallbackLocale, actualNs, actualKey);
        }

        // 使用默认值
        if (!translation) {
            translation = defaultValue;
            
            // 调用缺失键处理器
            if (this.options.missingKeyHandler) {
                this.options.missingKeyHandler(key, lng, ns);
            }
        }

        // 处理复数
        if (this.options.enablePlurals && count !== null) {
            translation = this.handlePlurals(translation, count, lng);
        }

        // 处理上下文
        if (context) {
            const contextKey = `${actualKey}_${context}`;
            const contextTranslation = this.getTranslation(lng, actualNs, contextKey);
            if (contextTranslation) {
                translation = contextTranslation;
            }
        }

        // 处理插值
        if (this.options.enableInterpolation && typeof translation === 'string') {
            translation = this.interpolate(translation, interpolation);
        }

        // 后处理
        if (this.options.postProcessor) {
            translation = this.options.postProcessor(translation, key, options);
        }

        return translation;
    }

    /**
     * 获取翻译
     * @param {string} locale - 语言代码
     * @param {string} namespace - 命名空间
     * @param {string} key - 键
     */
    getTranslation(locale, namespace, key) {
        const localeData = this.translations.get(locale);
        if (!localeData) return null;

        const nsData = localeData.get(namespace);
        if (!nsData) return null;

        // 支持嵌套键 (例如: "user.profile.name")
        return this.getNestedValue(nsData, key);
    }

    /**
     * 获取嵌套值
     * @param {object} obj - 对象
     * @param {string} path - 路径
     */
    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined ? current[key] : null;
        }, obj);
    }

    /**
     * 处理复数
     * @param {string|object} translation - 翻译
     * @param {number} count - 数量
     * @param {string} locale - 语言代码
     */
    handlePlurals(translation, count, locale) {
        if (typeof translation === 'string') return translation;
        if (typeof translation !== 'object') return translation;

        // 获取复数规则
        const rule = this.getPluralRule(count, locale);
        
        // 查找对应的复数形式
        const pluralKey = this.getPluralKey(rule);
        return translation[pluralKey] || translation.other || translation.one || '';
    }

    /**
     * 获取复数规则
     * @param {number} count - 数量
     * @param {string} locale - 语言代码
     */
    getPluralRule(count, locale) {
        // 简化的复数规则，实际项目中可能需要更复杂的规则
        const rules = {
            'zh-CN': () => 'other', // 中文没有复数
            'en-US': (n) => n === 1 ? 'one' : 'other',
            'ru-RU': (n) => {
                if (n % 10 === 1 && n % 100 !== 11) return 'one';
                if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) return 'few';
                return 'many';
            }
        };

        const rule = rules[locale] || rules['en-US'];
        return rule(count);
    }

    /**
     * 获取复数键
     * @param {string} rule - 规则
     */
    getPluralKey(rule) {
        const keyMap = {
            'zero': 'zero',
            'one': 'one',
            'two': 'two',
            'few': 'few',
            'many': 'many',
            'other': 'other'
        };

        return keyMap[rule] || 'other';
    }

    /**
     * 插值处理
     * @param {string} text - 文本
     * @param {object} values - 插值数据
     */
    interpolate(text, values) {
        return text.replace(/\{\{([^}]+)\}\}/g, (match, key) => {
            const value = this.getNestedValue(values, key.trim());
            return value !== null && value !== undefined ? value : match;
        });
    }

    /**
     * 格式化日期
     * @param {Date|string|number} date - 日期
     * @param {object} options - 格式化选项
     */
    formatDate(date, options = {}) {
        const {
            locale = this.currentLocale,
            format = 'short',
            ...formatOptions
        } = options;

        const dateObj = typeof date === 'string' || typeof date === 'number' 
            ? new Date(date) 
            : date;

        // 预定义格式
        const formats = {
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' },
            time: { hour: '2-digit', minute: '2-digit' },
            datetime: { 
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }
        };

        const formatConfig = formats[format] || formatOptions;

        // 获取或创建格式化器
        const key = `${locale}-${JSON.stringify(formatConfig)}`;
        let formatter = this.formatters.get('date').get(key);
        
        if (!formatter) {
            formatter = new Intl.DateTimeFormat(locale, formatConfig);
            this.formatters.get('date').set(key, formatter);
        }

        return formatter.format(dateObj);
    }

    /**
     * 格式化数字
     * @param {number} number - 数字
     * @param {object} options - 格式化选项
     */
    formatNumber(number, options = {}) {
        const {
            locale = this.currentLocale,
            style = 'decimal',
            ...formatOptions
        } = options;

        const key = `${locale}-${style}-${JSON.stringify(formatOptions)}`;
        let formatter = this.formatters.get('number').get(key);
        
        if (!formatter) {
            formatter = new Intl.NumberFormat(locale, { style, ...formatOptions });
            this.formatters.get('number').set(key, formatter);
        }

        return formatter.format(number);
    }

    /**
     * 格式化货币
     * @param {number} amount - 金额
     * @param {string} currency - 货币代码
     * @param {object} options - 格式化选项
     */
    formatCurrency(amount, currency = 'USD', options = {}) {
        const {
            locale = this.currentLocale,
            ...formatOptions
        } = options;

        return this.formatNumber(amount, {
            locale,
            style: 'currency',
            currency,
            ...formatOptions
        });
    }

    /**
     * 格式化相对时间
     * @param {Date|string|number} date - 日期
     * @param {object} options - 选项
     */
    formatRelativeTime(date, options = {}) {
        const {
            locale = this.currentLocale,
            numeric = 'auto'
        } = options;

        const dateObj = typeof date === 'string' || typeof date === 'number' 
            ? new Date(date) 
            : date;

        const now = new Date();
        const diffMs = dateObj.getTime() - now.getTime();
        const diffSec = Math.round(diffMs / 1000);
        const diffMin = Math.round(diffSec / 60);
        const diffHour = Math.round(diffMin / 60);
        const diffDay = Math.round(diffHour / 24);

        // 获取或创建格式化器
        const key = `${locale}-${numeric}`;
        let formatter = this.formatters.get('relative').get(key);
        
        if (!formatter) {
            formatter = new Intl.RelativeTimeFormat(locale, { numeric });
            this.formatters.get('relative').set(key, formatter);
        }

        // 确定使用的单位和值
        if (Math.abs(diffSec) < 60) {
            return formatter.format(diffSec, 'second');
        } else if (Math.abs(diffMin) < 60) {
            return formatter.format(diffMin, 'minute');
        } else if (Math.abs(diffHour) < 24) {
            return formatter.format(diffHour, 'hour');
        } else {
            return formatter.format(diffDay, 'day');
        }
    }

    /**
     * 设置DOM观察器
     */
    setupDOMObserver() {
        // 观察DOM变化，自动翻译新添加的元素
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        this.translateElement(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * 翻译页面
     */
    translatePage() {
        this.translateElement(document.body);
    }

    /**
     * 翻译元素
     * @param {Element} element - 要翻译的元素
     */
    translateElement(element) {
        // 翻译具有 data-i18n 属性的元素
        const elements = element.querySelectorAll('[data-i18n]');
        
        elements.forEach(el => {
            const key = el.getAttribute('data-i18n');
            const options = this.parseDataAttributes(el);
            
            if (key) {
                const translation = this.t(key, options);
                
                // 根据属性决定设置方式
                const attr = el.getAttribute('data-i18n-attr');
                if (attr) {
                    el.setAttribute(attr, translation);
                } else {
                    el.textContent = translation;
                }
            }
        });

        // 翻译元素本身（如果有data-i18n属性）
        if (element.hasAttribute && element.hasAttribute('data-i18n')) {
            const key = element.getAttribute('data-i18n');
            const options = this.parseDataAttributes(element);
            
            if (key) {
                const translation = this.t(key, options);
                
                const attr = element.getAttribute('data-i18n-attr');
                if (attr) {
                    element.setAttribute(attr, translation);
                } else {
                    element.textContent = translation;
                }
            }
        }
    }

    /**
     * 解析数据属性
     * @param {Element} element - 元素
     */
    parseDataAttributes(element) {
        const options = {};
        
        // 解析插值数据
        const interpolation = element.getAttribute('data-i18n-values');
        if (interpolation) {
            try {
                options.interpolation = JSON.parse(interpolation);
            } catch (e) {
                console.warn('无效的插值数据:', interpolation);
            }
        }

        // 解析数量
        const count = element.getAttribute('data-i18n-count');
        if (count) {
            options.count = parseInt(count, 10);
        }

        // 解析上下文
        const context = element.getAttribute('data-i18n-context');
        if (context) {
            options.context = context;
        }

        // 解析命名空间
        const ns = element.getAttribute('data-i18n-ns');
        if (ns) {
            options.ns = ns;
        }

        return options;
    }

    /**
     * 添加语言变化观察者
     * @param {function} callback - 回调函数
     */
    onLocaleChange(callback) {
        this.observers.add(callback);
        
        // 返回取消订阅函数
        return () => {
            this.observers.delete(callback);
        };
    }

    /**
     * 通知语言变化
     * @param {string} newLocale - 新语言
     * @param {string} oldLocale - 旧语言
     */
    notifyLocaleChange(newLocale, oldLocale) {
        this.observers.forEach(callback => {
            try {
                callback(newLocale, oldLocale);
            } catch (error) {
                console.error('语言变化观察者执行失败:', error);
            }
        });
    }

    /**
     * 获取支持的语言列表
     */
    getSupportedLocales() {
        return Array.from(this.translations.keys());
    }

    /**
     * 检查语言是否支持
     * @param {string} locale - 语言代码
     */
    isLocaleSupported(locale) {
        return this.translations.has(locale);
    }

    /**
     * 添加翻译
     * @param {string} locale - 语言代码
     * @param {string} namespace - 命名空间
     * @param {object} translations - 翻译数据
     */
    addTranslations(locale, namespace, translations) {
        if (!this.translations.has(locale)) {
            this.translations.set(locale, new Map());
        }

        const localeData = this.translations.get(locale);
        const existing = localeData.get(namespace) || {};
        
        // 深度合并翻译数据
        const merged = this.deepMerge(existing, translations);
        localeData.set(namespace, merged);

        // 如果是当前语言，重新翻译页面
        if (locale === this.currentLocale) {
            this.translatePage();
        }
    }

    /**
     * 深度合并对象
     * @param {object} target - 目标对象
     * @param {object} source - 源对象
     */
    deepMerge(target, source) {
        const result = { ...target };
        
        for (const key in source) {
            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                result[key] = this.deepMerge(result[key] || {}, source[key]);
            } else {
                result[key] = source[key];
            }
        }
        
        return result;
    }

    /**
     * 事件发射器
     */
    emit(eventName, data) {
        const event = new CustomEvent(`i18n:${eventName}`, { detail: data });
        document.dispatchEvent(event);
    }

    /**
     * 销毁实例
     */
    destroy() {
        this.translations.clear();
        this.formatters.clear();
        this.cache.clear();
        this.observers.clear();
        this.loadedNamespaces.clear();
    }
}

// 创建便捷函数
function createI18nHelpers(i18n) {
    return {
        t: (key, options) => i18n.t(key, options),
        tDate: (date, options) => i18n.formatDate(date, options),
        tNumber: (number, options) => i18n.formatNumber(number, options),
        tCurrency: (amount, currency, options) => i18n.formatCurrency(amount, currency, options),
        tRelative: (date, options) => i18n.formatRelativeTime(date, options),
        setLocale: (locale) => i18n.setLocale(locale),
        getLocale: () => i18n.getLocale()
    };
}

// 导出
window.AlingI18nManager = AlingI18nManager;
window.createI18nHelpers = createI18nHelpers;

// 创建全局实例
window.i18n = new AlingI18nManager();
window.$t = window.i18n.t.bind(window.i18n);
window.$tDate = window.i18n.formatDate.bind(window.i18n);
window.$tNumber = window.i18n.formatNumber.bind(window.i18n);
window.$tCurrency = window.i18n.formatCurrency.bind(window.i18n);
window.$tRelative = window.i18n.formatRelativeTime.bind(window.i18n);

console.log('✅ AlingAi 国际化管理器已加载');
