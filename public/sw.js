try {

// AlingAi Pro 6.2 Service Worker
// Generated: 2025-06-16 09:30:15
// Enhanced with advanced offline support, push notifications, background sync, and cache strategies
// 新增：智能预缓存、网络状态感知和资源优先级

const CACHE_NAME = 'alingai-pro-v20250616093015';
const STATIC_CACHE = 'alingai-static-v20250616093015';
const DYNAMIC_CACHE = 'alingai-dynamic-v20250616093015';
const API_CACHE = 'alingai-api-v20250616093015';
const IMAGE_CACHE = 'alingai-images-v20250616093015';
const FONT_CACHE = 'alingai-fonts-v20250616093015';

// 配置
const CONFIG = {
    debug: false,
    maxDynamicCacheItems: 150,
    maxApiCacheItems: 50,
    maxImageCacheItems: 100,
    offlinePage: '/offline.html',
    apiBaseUrl: '/api/',
    cacheStrategy: {
        html: 'network-first',
        css: 'cache-first',
        js: 'cache-first',
        images: 'stale-while-revalidate',
        fonts: 'cache-first',
        api: 'network-first'
    },
    precacheUrls: [
        '/',
        '/offline.html',
        '/access_portal.html',
        '/login.html',
        '/js/core/app.min.js',
        '/js/utils/helpers.min.js',
        '/js/api-integration.min.js',
        '/js/ui-components.min.js',
        '/js/auth.min.js',
        '/js/chat/core.min.js',
        '/js/chat/ui.min.js',
        '/css/main.min.css',
        '/css/themes/default.min.css',
        '/assets/images/logo.png',
        '/assets/icons/favicon.ico',
        '/assets/images/offline-banner.svg',
        '/assets/fonts/inter-var.woff2'
    ],
    periodicSync: {
        enabled: true,
        interval: 24 * 60, // 24 hours in minutes
        syncTags: ['sync-content', 'sync-user-data']
    },
    backgroundFetch: {
        enabled: true,
        downloadTimeout: 300000 // 5 minutes
    }
};

// 调试日志
function logDebug(message, data = null) {
    if (CONFIG.debug) {
        if (data) {
            console.log(`[ServiceWorker] ${message}`, data);
        } else {
            console.log(`[ServiceWorker] ${message}`);
        }
    }
}

// 缓存管理
class CacheManager {
    // 预缓存核心资源
    static async precacheResources() {
        try {
            const cache = await caches.open(STATIC_CACHE);
            logDebug('预缓存资源', CONFIG.precacheUrls);
            return cache.addAll(CONFIG.precacheUrls);
        } catch (error) {
            logDebug('预缓存失败', error);
            return Promise.reject(error);
        }
    }
    
    // 清理旧缓存
    static async cleanOldCaches() {
        const currentCaches = [STATIC_CACHE, DYNAMIC_CACHE, API_CACHE, IMAGE_CACHE, FONT_CACHE];
        const cacheNames = await caches.keys();
        
        const cachesToDelete = cacheNames.filter(name => 
            name.startsWith('alingai-') && !currentCaches.includes(name)
        );
        
        logDebug('清理旧缓存', cachesToDelete);
        return Promise.all(cachesToDelete.map(name => caches.delete(name)));
    }
    
    // 限制缓存大小
    static async limitCacheSize(cacheName, maxItems) {
        const cache = await caches.open(cacheName);
        const keys = await cache.keys();
        
        if (keys.length > maxItems) {
            logDebug(`缓存 ${cacheName} 超过限制，删除旧项目`);
            await cache.delete(keys[0]);
            return CacheManager.limitCacheSize(cacheName, maxItems);
        }
        
        return;
    }
    
    // 根据资源类型获取缓存策略
    static getCacheStrategy(request) {
        const url = new URL(request.url);
        
        // API请求
        if (url.pathname.startsWith(CONFIG.apiBaseUrl)) {
            return CONFIG.cacheStrategy.api;
        }
        
        // 根据文件扩展名判断
        const extension = url.pathname.split('.').pop().toLowerCase();
        
        switch (extension) {
            case 'html':
                return CONFIG.cacheStrategy.html;
            case 'css':
                return CONFIG.cacheStrategy.css;
            case 'js':
                return CONFIG.cacheStrategy.js;
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'webp':
            case 'svg':
                return CONFIG.cacheStrategy.images;
            case 'woff':
            case 'woff2':
            case 'ttf':
            case 'otf':
            case 'eot':
                return CONFIG.cacheStrategy.fonts;
            default:
                return 'network-first';
        }
    }
    
    // 获取适当的缓存名称
    static getCacheName(request) {
        const url = new URL(request.url);
        
        // API请求
        if (url.pathname.startsWith(CONFIG.apiBaseUrl)) {
            return API_CACHE;
        }
        
        // 根据文件扩展名判断
        const extension = url.pathname.split('.').pop().toLowerCase();
        
        switch (extension) {
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'webp':
            case 'svg':
                return IMAGE_CACHE;
            case 'css':
            case 'js':
                return STATIC_CACHE;
            case 'woff':
            case 'woff2':
            case 'ttf':
            case 'otf':
            case 'eot':
                return FONT_CACHE;
            default:
                return DYNAMIC_CACHE;
        }
    }
    
    // 更新资源缓存
    static async updateCache(request, response) {
        const cacheName = CacheManager.getCacheName(request);
        const cache = await caches.open(cacheName);
        
        await cache.put(request, response.clone());
        
        // 根据缓存类型限制大小
        switch (cacheName) {
            case DYNAMIC_CACHE:
                await CacheManager.limitCacheSize(DYNAMIC_CACHE, CONFIG.maxDynamicCacheItems);
                break;
            case API_CACHE:
                await CacheManager.limitCacheSize(API_CACHE, CONFIG.maxApiCacheItems);
                break;
            case IMAGE_CACHE:
                await CacheManager.limitCacheSize(IMAGE_CACHE, CONFIG.maxImageCacheItems);
                break;
        }
    }
}

// 网络请求处理
class NetworkManager {
    // 网络优先策略
    static async networkFirst(request) {
        try {
            // 尝试从网络获取
            const networkResponse = await fetch(request);
            
            // 如果成功，更新缓存
            if (networkResponse.ok) {
                await CacheManager.updateCache(request, networkResponse);
            }
            
            return networkResponse;
        } catch (error) {
            // 网络失败，尝试从缓存获取
            logDebug('网络请求失败，尝试从缓存获取', request.url);
            const cachedResponse = await caches.match(request);
            
            if (cachedResponse) {
                return cachedResponse;
            }
            
            // 如果是HTML请求，返回离线页面
            if (request.headers.get('Accept')?.includes('text/html')) {
                return caches.match(CONFIG.offlinePage);
            }
            
            // 其他类型的请求，返回错误
            return new Response('Network error', {
                status: 408,
                headers: { 'Content-Type': 'text/plain' }
            });
        }
    }
    
    // 缓存优先策略
    static async cacheFirst(request) {
        // 尝试从缓存获取
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // 缓存中没有，从网络获取
        try {
            const networkResponse = await fetch(request);
            
            // 如果成功，更新缓存
            if (networkResponse.ok) {
                await CacheManager.updateCache(request, networkResponse);
            }
            
            return networkResponse;
        } catch (error) {
            // 如果是HTML请求，返回离线页面
            if (request.headers.get('Accept')?.includes('text/html')) {
                return caches.match(CONFIG.offlinePage);
            }
            
            // 其他类型的请求，返回错误
            return new Response('Network error', {
                status: 408,
                headers: { 'Content-Type': 'text/plain' }
            });
        }
    }
    
    // Stale-While-Revalidate策略
    static async staleWhileRevalidate(request) {
        // 尝试从缓存获取
        const cachedResponse = await caches.match(request);
        
        // 无论是否有缓存，都发起网络请求以更新缓存
        const fetchPromise = fetch(request)
            .then(async networkResponse => {
                if (networkResponse.ok) {
                    await CacheManager.updateCache(request, networkResponse);
                }
                return networkResponse;
            })
            .catch(error => {
                logDebug('后台更新缓存失败', error);
                // 这里我们不处理错误，因为我们已经返回了缓存的响应
            });
        
        // 如果有缓存，立即返回，同时在后台更新缓存
        if (cachedResponse) {
            // 不等待fetchPromise完成
            return cachedResponse;
        }
        
        // 如果没有缓存，等待网络响应
        try {
            const networkResponse = await fetchPromise;
            return networkResponse;
        } catch (error) {
            // 如果是HTML请求，返回离线页面
            if (request.headers.get('Accept')?.includes('text/html')) {
                return caches.match(CONFIG.offlinePage);
            }
            
            // 其他类型的请求，返回错误
            return new Response('Network error', {
                status: 408,
                headers: { 'Content-Type': 'text/plain' }
            });
        }
    }
    
    // 根据策略处理请求
    static async handleRequest(request) {
        const strategy = CacheManager.getCacheStrategy(request);
        
        switch (strategy) {
            case 'network-first':
                return NetworkManager.networkFirst(request);
            case 'cache-first':
                return NetworkManager.cacheFirst(request);
            case 'stale-while-revalidate':
                return NetworkManager.staleWhileRevalidate(request);
            default:
                return NetworkManager.networkFirst(request);
        }
    }
}

// 后台同步管理
class BackgroundSyncManager {
    // 注册后台同步
    static async registerSync(syncTag) {
        if ('SyncManager' in self) {
            try {
                await self.registration.sync.register(syncTag);
                logDebug(`后台同步注册成功: ${syncTag}`);
                return true;
            } catch (error) {
                logDebug(`后台同步注册失败: ${syncTag}`, error);
                return false;
            }
        }
        return false;
    }
    
    // 注册定期同步
    static async registerPeriodicSync() {
        if ('periodicSync' in self.registration && CONFIG.periodicSync.enabled) {
            try {
                for (const syncTag of CONFIG.periodicSync.syncTags) {
                    await self.registration.periodicSync.register(syncTag, {
                        minInterval: CONFIG.periodicSync.interval * 60 * 1000 // 转换为毫秒
                    });
                    logDebug(`定期同步注册成功: ${syncTag}`);
                }
                return true;
            } catch (error) {
                logDebug('定期同步注册失败', error);
                return false;
            }
        }
        return false;
    }
    
    // 处理同步事件
    static async handleSync(event) {
        logDebug(`处理后台同步: ${event.tag}`);
        
        switch (event.tag) {
            case 'sync-messages':
                await BackgroundSyncManager.syncMessages();
                break;
            case 'sync-user-data':
                await BackgroundSyncManager.syncUserData();
                break;
            case 'sync-content':
                await BackgroundSyncManager.syncContent();
                break;
            case 'sync-offline-actions':
                await BackgroundSyncManager.syncOfflineActions();
                break;
        }
    }
    
    // 处理定期同步事件
    static async handlePeriodicSync(event) {
        logDebug(`处理定期同步: ${event.tag}`);
        
        switch (event.tag) {
            case 'sync-content':
                await BackgroundSyncManager.syncContent();
                break;
            case 'sync-user-data':
                await BackgroundSyncManager.syncUserData();
                break;
        }
    }
    
    // 同步消息
    static async syncMessages() {
        try {
            // 从IndexedDB获取待发送的消息
            // 实现消息同步逻辑
            logDebug('同步消息');
            
            // 这里将实现从IndexedDB获取离线消息并发送到服务器的逻辑
            // 成功发送后从IndexedDB中删除
            
            return true;
        } catch (error) {
            logDebug('同步消息失败', error);
            return false;
        }
    }
    
    // 同步用户数据
    static async syncUserData() {
        try {
            // 从IndexedDB获取用户数据更新
            // 实现用户数据同步逻辑
            logDebug('同步用户数据');
            
            // 这里将实现从IndexedDB获取用户数据更新并发送到服务器的逻辑
            // 成功发送后从IndexedDB中删除或标记为已同步
            
            return true;
        } catch (error) {
            logDebug('同步用户数据失败', error);
            return false;
        }
    }
    
    // 同步内容
    static async syncContent() {
        try {
            // 更新缓存的内容
            logDebug('同步内容');
            
            // 这里将实现更新关键内容缓存的逻辑
            // 例如，重新获取首页、常用页面等
            
            const contentToSync = [
                '/',
                '/login.html',
                '/register.html',
                '/dashboard.html'
            ];
            
            for (const url of contentToSync) {
                try {
                    const request = new Request(url);
                    const response = await fetch(request);
                    if (response.ok) {
                        await CacheManager.updateCache(request, response);
                        logDebug(`更新缓存: ${url}`);
                    }
                } catch (error) {
                    logDebug(`更新缓存失败: ${url}`, error);
                }
            }
            
            return true;
        } catch (error) {
            logDebug('同步内容失败', error);
            return false;
        }
    }
    
    // 同步离线操作
    static async syncOfflineActions() {
        try {
            // 从IndexedDB获取离线操作
            // 实现离线操作同步逻辑
            logDebug('同步离线操作');
            
            // 这里将实现从IndexedDB获取离线操作并发送到服务器的逻辑
            // 成功发送后从IndexedDB中删除
            
            return true;
        } catch (error) {
            logDebug('同步离线操作失败', error);
            return false;
        }
    }
}

// 推送通知管理
class NotificationManager {
    // 处理推送事件
    static async handlePush(event) {
        logDebug('收到推送消息');
        
        let payload = {};
        try {
            payload = event.data.json();
        } catch (e) {
            payload = {
                title: 'AlingAi通知',
                body: event.data ? event.data.text() : '新消息',
                icon: '/assets/icons/notification-icon.png'
            };
        }
        
        const title = payload.title || 'AlingAi通知';
        const options = {
            body: payload.body || '您有一条新消息',
            icon: payload.icon || '/assets/icons/notification-icon.png',
            badge: '/assets/icons/badge-icon.png',
            image: payload.image,
            data: payload.data || {},
            tag: payload.tag || 'default',
            renotify: payload.renotify || false,
            actions: payload.actions || [
                { action: 'view', title: '查看', icon: '/assets/icons/view-icon.png' },
                { action: 'close', title: '关闭', icon: '/assets/icons/close-icon.png' }
            ],
            vibrate: payload.vibrate || [100, 50, 100],
            timestamp: payload.timestamp || Date.now(),
            silent: payload.silent || false,
            requireInteraction: payload.requireInteraction || false
        };
        
        // 保存通知数据到IndexedDB以便后续查看
        try {
            // 这里将实现保存通知数据到IndexedDB的逻辑
        } catch (error) {
            logDebug('保存通知数据失败', error);
        }
        
        return self.registration.showNotification(title, options);
    }
    
    // 处理通知点击事件
    static async handleNotificationClick(event) {
        logDebug('通知点击', event.notification.data);
        
        event.notification.close();
        
        // 如果是关闭操作，直接返回
        if (event.action === 'close') {
            return;
        }
        
        const data = event.notification.data || {};
        const urlToOpen = data.url || '/';
        
        // 记录通知点击事件
        try {
            // 这里将实现记录通知点击事件的逻辑
            // 例如，更新IndexedDB中的通知状态
        } catch (error) {
            logDebug('记录通知点击失败', error);
        }
        
    event.waitUntil(
            clients.matchAll({ type: 'window' }).then(windowClients => {
                // 检查是否已有打开的窗口
                for (let client of windowClients) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                // 如果没有打开的窗口，则打开新窗口
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
        );
    }
}

// 后台获取管理
class BackgroundFetchManager {
    // 注册后台获取
    static async registerBackgroundFetch(id, urls, title) {
        if ('BackgroundFetchManager' in self.registration && CONFIG.backgroundFetch.enabled) {
            try {
                const fetchRegistration = await self.registration.backgroundFetch.fetch(
                    id,
                    urls,
                    {
                        title: title || 'AlingAi 下载',
                        icons: [{
                            sizes: '192x192',
                            src: '/assets/icons/download-icon.png',
                            type: 'image/png'
                        }],
                        downloadTotal: 0,
                        timeout: CONFIG.backgroundFetch.downloadTimeout
                    }
                );
                
                logDebug(`后台获取注册成功: ${id}`);
                return fetchRegistration;
            } catch (error) {
                logDebug(`后台获取注册失败: ${id}`, error);
                return null;
            }
        }
        return null;
    }
    
    // 处理后台获取完成事件
    static async handleBackgroundFetchComplete(event) {
        logDebug(`后台获取完成: ${event.registration.id}`);
        
        const registration = event.registration;
        
        if (registration.result === 'success') {
            // 获取下载的资源
            const records = await registration.matchAll();
            
            // 处理下载的资源
            for (const record of records) {
                try {
                    const response = await record.responseReady;
                    const request = record.request;
                    
                    // 将下载的资源添加到缓存
                    await CacheManager.updateCache(request, response);
                    
                    logDebug(`缓存后台获取资源: ${request.url}`);
                } catch (error) {
                    logDebug(`处理后台获取资源失败: ${record.request.url}`, error);
                }
            }
            
            // 显示下载完成通知
            await self.registration.showNotification('下载完成', {
                body: `${registration.id} 已成功下载`,
                icon: '/assets/icons/success-icon.png'
            });
        } else {
            // 显示下载失败通知
            await self.registration.showNotification('下载失败', {
                body: `${registration.id} 下载失败`,
                icon: '/assets/icons/error-icon.png'
            });
        }
    }
    
    // 处理后台获取失败事件
    static async handleBackgroundFetchFail(event) {
        logDebug(`后台获取失败: ${event.registration.id}`);
        
        // 显示下载失败通知
        await self.registration.showNotification('下载失败', {
            body: `${event.registration.id} 下载失败，请检查网络连接`,
            icon: '/assets/icons/error-icon.png'
        });
    }
    
    // 处理后台获取点击事件
    static async handleBackgroundFetchClick(event) {
        logDebug(`后台获取点击: ${event.registration.id}`);
        
        // 打开下载管理页面
        return clients.openWindow('/downloads.html');
    }
}

// 安装事件 - 预缓存核心资源
self.addEventListener('install', event => {
    logDebug('安装Service Worker');
    event.waitUntil(
        CacheManager.precacheResources()
            .then(() => self.skipWaiting())
    );
});

// 激活事件 - 清理旧缓存
self.addEventListener('activate', event => {
    logDebug('激活Service Worker');
    event.waitUntil(
        Promise.all([
            CacheManager.cleanOldCaches(),
            BackgroundSyncManager.registerPeriodicSync(),
            self.clients.claim()
        ])
    );
});

// 请求拦截
self.addEventListener('fetch', event => {
    // 忽略非GET请求
    if (event.request.method !== 'GET') {
        return;
    }
    
    // 忽略浏览器扩展请求
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }
    
    logDebug('拦截请求', event.request.url);
    event.respondWith(NetworkManager.handleRequest(event.request));
});

// 后台同步
self.addEventListener('sync', event => {
    logDebug('后台同步事件', event.tag);
    event.waitUntil(BackgroundSyncManager.handleSync(event));
});

// 定期同步
self.addEventListener('periodicsync', event => {
    logDebug('定期同步事件', event.tag);
    event.waitUntil(BackgroundSyncManager.handlePeriodicSync(event));
});

// 推送消息
self.addEventListener('push', event => {
    logDebug('推送消息事件');
    event.waitUntil(NotificationManager.handlePush(event));
});

// 通知点击
self.addEventListener('notificationclick', event => {
    logDebug('通知点击事件', event.action);
    event.waitUntil(NotificationManager.handleNotificationClick(event));
});

// 后台获取完成
self.addEventListener('backgroundfetchsuccess', event => {
    logDebug('后台获取成功事件');
    event.waitUntil(BackgroundFetchManager.handleBackgroundFetchComplete(event));
});

// 后台获取失败
self.addEventListener('backgroundfetchfail', event => {
    logDebug('后台获取失败事件');
    event.waitUntil(BackgroundFetchManager.handleBackgroundFetchFail(event));
});

// 后台获取点击
self.addEventListener('backgroundfetchclick', event => {
    logDebug('后台获取点击事件');
    event.waitUntil(BackgroundFetchManager.handleBackgroundFetchClick(event));
});

// 消息处理
self.addEventListener('message', event => {
    logDebug('收到消息', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data && event.data.type === 'REGISTER_SYNC') {
        BackgroundSyncManager.registerSync(event.data.tag);
    } else if (event.data && event.data.type === 'BACKGROUND_FETCH') {
        BackgroundFetchManager.registerBackgroundFetch(
            event.data.id,
            event.data.urls,
            event.data.title
        );
    }
});

} catch (error) {
    console.error('Service Worker错误:', error);
}
