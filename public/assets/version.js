/**
 * AlingAi资源版本控制
 * 用于缓存破坏和资源版本管理
 * @version 2.0.0
 */
try {
    // 使用当前时间戳作为资源版本号
    window.ASSET_VERSION = '1749036344';
    
    // 暴露版本信息的公共方法
    window.getAssetVersionedUrl = function(url) {
        if (!url) return '';
        const separator = url.includes('?') ? '&' : '?';
        return `${url}${separator}v=${window.ASSET_VERSION}`;
    };
} catch (error) {
    console.error('资源版本初始化失败:', error);
    // 提供后备版本号
    window.ASSET_VERSION = new Date().getTime().toString();
}
