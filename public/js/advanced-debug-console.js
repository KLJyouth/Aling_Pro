/**
 * AlingAi Pro - 高级调试控制台
 * 提供实时系统监控、调试工具和性能分析
 */

class AdvancedDebugConsole {
    constructor() {
        this.isVisible = false;
        this.isDocked = false;
        this.currentTab = 'overview';
        this.logs = [];
        this.maxLogs = 1000;
        this.performanceData = [];
        this.systemMetrics = {};
        this.networkRequests = [];
        this.errorCount = 0;
        this.warningCount = 0;
        
        this.init();
    }
    
    init() {
        this.createConsoleUI();
        this.setupEventListeners();
        this.startPerformanceMonitoring();
        this.interceptConsoleLogs();
        this.interceptNetworkRequests();
        
    }
    
    createConsoleUI() {
        // 创建调试控制台主容器
        const consoleContainer = document.createElement('div');
        consoleContainer.id = 'advanced-debug-console';
        consoleContainer.className = 'fixed bottom-0 right-0 z-50 bg-gray-900/95 backdrop-blur-md text-white border-t border-gray-700 shadow-2xl font-mono text-sm hidden';
        consoleContainer.style.width = '100%';
        consoleContainer.style.height = '50vh';
        consoleContainer.style.transition = 'all 0.3s ease';
        
        consoleContainer.innerHTML = `
            <!-- 控制台头部 -->
            <div class="flex items-center justify-between bg-gray-800 px-4 py-2 border-b border-gray-700">
                <div class="flex items-center space-x-4">
                    <h3 class="text-cyan-400 font-bold flex items-center">
                        <i class="fas fa-terminal mr-2"></i>
                        高级调试控制台
                    </h3>
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="bg-red-600 px-2 py-1 rounded" id="error-count">错误: 0</span>
                        <span class="bg-yellow-600 px-2 py-1 rounded" id="warning-count">警告: 0</span>
                        <span class="bg-green-600 px-2 py-1 rounded" id="info-count">信息: 0</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button id="clear-console" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-xs transition-colors">
                        <i class="fas fa-trash mr-1"></i>清空
                    </button>
                    <button id="dock-console" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs transition-colors">
                        <i class="fas fa-window-restore mr-1"></i>悬浮
                    </button>
                    <button id="close-console" class="bg-gray-600 hover:bg-gray-700 px-3 py-1 rounded text-xs transition-colors">
                        <i class="fas fa-times mr-1"></i>关闭
                    </button>
                </div>
            </div>
            
            <!-- 标签页导航 -->
            <div class="flex bg-gray-800 border-b border-gray-700">
                <button class="tab-button active px-4 py-2 text-xs hover:bg-gray-700 transition-colors border-b-2 border-cyan-500" data-tab="overview">
                    <i class="fas fa-chart-line mr-1"></i>概览
                </button>
                <button class="tab-button px-4 py-2 text-xs hover:bg-gray-700 transition-colors border-b-2 border-transparent" data-tab="console">
                    <i class="fas fa-terminal mr-1"></i>控制台
                </button>
                <button class="tab-button px-4 py-2 text-xs hover:bg-gray-700 transition-colors border-b-2 border-transparent" data-tab="network">
                    <i class="fas fa-globe mr-1"></i>网络
                </button>
                <button class="tab-button px-4 py-2 text-xs hover:bg-gray-700 transition-colors border-b-2 border-transparent" data-tab="performance">
                    <i class="fas fa-tachometer-alt mr-1"></i>性能
                </button>
                <button class="tab-button px-4 py-2 text-xs hover:bg-gray-700 transition-colors border-b-2 border-transparent" data-tab="memory">
                    <i class="fas fa-memory mr-1"></i>内存
                </button>
                <button class="tab-button px-4 py-2 text-xs hover:bg-gray-700 transition-colors border-b-2 border-transparent" data-tab="sources">
                    <i class="fas fa-code mr-1"></i>源码
                </button>
            </div>
            
            <!-- 标签页内容 -->
            <div class="flex-1 overflow-hidden">
                <!-- 概览标签页 -->
                <div id="tab-overview" class="tab-content h-full p-4 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-cyan-400 font-semibold mb-2">系统状态</h4>
                            <div id="system-status" class="space-y-1 text-xs">
                                <div>加载中...</div>
                            </div>
                        </div>
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-green-400 font-semibold mb-2">性能指标</h4>
                            <div id="performance-metrics" class="space-y-1 text-xs">
                                <div>FPS: <span id="current-fps">--</span></div>
                                <div>内存: <span id="current-memory">--</span></div>
                                <div>延迟: <span id="current-latency">--</span></div>
                            </div>
                        </div>
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-yellow-400 font-semibold mb-2">活动统计</h4>
                            <div id="activity-stats" class="space-y-1 text-xs">
                                <div>API请求: <span id="api-requests">0</span></div>
                                <div>用户交互: <span id="user-interactions">0</span></div>
                                <div>动画帧: <span id="animation-frames">0</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 实时图表区域 -->
                    <div class="bg-gray-800 p-3 rounded border border-gray-700">
                        <h4 class="text-purple-400 font-semibold mb-2">实时监控图表</h4>
                        <canvas id="realtime-chart" width="800" height="200" class="w-full"></canvas>
                    </div>
                </div>
                
                <!-- 控制台标签页 -->
                <div id="tab-console" class="tab-content h-full hidden">
                    <div class="h-full flex flex-col">
                        <div class="flex-1 overflow-y-auto p-2 bg-black" id="console-output">
                            <!-- 控制台输出将在这里显示 -->
                        </div>
                        <div class="border-t border-gray-700 p-2">
                            <div class="flex">
                                <span class="text-green-400 mr-2">></span>
                                <input type="text" id="console-input" class="flex-1 bg-transparent text-white outline-none" 
                                       placeholder="输入JavaScript命令..." autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 网络标签页 -->
                <div id="tab-network" class="tab-content h-full p-2 overflow-y-auto hidden">
                    <div class="space-y-2" id="network-requests">
                        <!-- 网络请求将在这里显示 -->
                    </div>
                </div>
                
                <!-- 性能标签页 -->
                <div id="tab-performance" class="tab-content h-full p-4 overflow-y-auto hidden">
                    <div class="space-y-4">
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-cyan-400 font-semibold mb-2">性能分析工具</h4>
                            <div class="flex space-x-2">
                                <button id="start-profiling" class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-xs">
                                    开始分析
                                </button>
                                <button id="stop-profiling" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-xs" disabled>
                                    停止分析
                                </button>
                                <button id="export-profile" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs">
                                    导出数据
                                </button>
                            </div>
                        </div>
                        
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-yellow-400 font-semibold mb-2">性能时间线</h4>
                            <canvas id="performance-timeline" width="800" height="300" class="w-full border border-gray-600"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- 内存标签页 -->
                <div id="tab-memory" class="tab-content h-full p-4 overflow-y-auto hidden">
                    <div class="space-y-4">
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-green-400 font-semibold mb-2">内存使用情况</h4>
                            <div id="memory-info" class="space-y-2 text-xs">
                                <!-- 内存信息将在这里显示 -->
                            </div>
                        </div>
                        
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-red-400 font-semibold mb-2">垃圾回收</h4>
                            <button id="force-gc" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-xs">
                                强制垃圾回收
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 源码标签页 -->
                <div id="tab-sources" class="tab-content h-full p-4 overflow-y-auto hidden">
                    <div class="space-y-4">
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-purple-400 font-semibold mb-2">已加载脚本</h4>
                            <div id="loaded-scripts" class="space-y-1 text-xs max-h-40 overflow-y-auto">
                                <!-- 脚本列表将在这里显示 -->
                            </div>
                        </div>
                        
                        <div class="bg-gray-800 p-3 rounded border border-gray-700">
                            <h4 class="text-blue-400 font-semibold mb-2">断点管理</h4>
                            <div class="flex space-x-2 mb-2">
                                <input type="text" id="breakpoint-input" class="flex-1 bg-gray-700 text-white px-2 py-1 rounded text-xs" 
                                       placeholder="函数名或行号">
                                <button id="add-breakpoint" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs">
                                    添加断点
                                </button>
                            </div>
                            <div id="breakpoints-list" class="space-y-1 text-xs">
                                <!-- 断点列表将在这里显示 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
          document.body.appendChild(consoleContainer);
        
        // 创建快捷按钮
        const toggleButton = document.createElement('button');
        toggleButton.id = 'debug-console-toggle';
        toggleButton.innerHTML = '<i class="fas fa-bug"></i>';
        
        document.body.appendChild(toggleButton);
        
        // 使用悬浮按钮管理器注册
        if (window.floatingButtonsManager) {
            window.floatingButtonsManager.registerButton('debug', {
                element: toggleButton,
                preferredPosition: 'bottom-right-3',
                type: 'debug',
                priority: 2,
                title: 'Toggle Debug Console (F12)',
                icon: 'fas fa-bug',
                onClick: () => {
                    this.toggle();
                }
            });
        } else {
            // 后备方案
            toggleButton.className = 'fixed bottom-4 right-4 z-40 bg-gray-800 hover:bg-gray-700 text-white p-3 rounded-full shadow-lg transition-colors border border-gray-600';
            toggleButton.title = 'Toggle Debug Console (F12)';
            toggleButton.addEventListener('click', () => {
                this.toggle();
            });
        }
    }
    
    setupEventListeners() {
        // 切换按钮
        document.getElementById('debug-console-toggle').addEventListener('click', () => {
            this.toggle();
        });
        
        // 关闭按钮
        document.getElementById('close-console').addEventListener('click', () => {
            this.hide();
        });
        
        // 悬浮/停靠按钮
        document.getElementById('dock-console').addEventListener('click', () => {
            this.toggleDock();
        });
        
        // 清空控制台
        document.getElementById('clear-console').addEventListener('click', () => {
            this.clearLogs();
        });
        
        // 标签页切换
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
        
        // 控制台输入
        document.getElementById('console-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.executeCommand(e.target.value);
                e.target.value = '';
            }
        });
        
        // 性能分析按钮
        document.getElementById('start-profiling').addEventListener('click', () => {
            this.startProfiling();
        });
        
        document.getElementById('stop-profiling').addEventListener('click', () => {
            this.stopProfiling();
        });
        
        document.getElementById('export-profile').addEventListener('click', () => {
            this.exportProfile();
        });
        
        // 强制垃圾回收
        document.getElementById('force-gc').addEventListener('click', () => {
            this.forceGarbageCollection();
        });
        
        // 断点管理
        document.getElementById('add-breakpoint').addEventListener('click', () => {
            const input = document.getElementById('breakpoint-input');
            this.addBreakpoint(input.value);
            input.value = '';
        });
        
        // 键盘快捷键
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F12') {
                e.preventDefault();
                this.toggle();
            }
            if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                e.preventDefault();
                this.toggle();
            }
        });
    }
    
    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }
    
    show() {
        const console = document.getElementById('advanced-debug-console');
        console.classList.remove('hidden');
        this.isVisible = true;
        this.updateSystemStatus();
        this.updateLoadedScripts();
    }
    
    hide() {
        const console = document.getElementById('advanced-debug-console');
        console.classList.add('hidden');
        this.isVisible = false;
    }
    
    toggleDock() {
        const console = document.getElementById('advanced-debug-console');
        const button = document.getElementById('dock-console');
        
        if (this.isDocked) {
            // 恢复到底部
            console.style.position = 'fixed';
            console.style.bottom = '0';
            console.style.right = '0';
            console.style.width = '100%';
            console.style.height = '50vh';
            button.innerHTML = '<i class="fas fa-window-restore mr-1"></i>悬浮';
            this.isDocked = false;
        } else {
            // 悬浮模式
            console.style.position = 'fixed';
            console.style.bottom = '20px';
            console.style.right = '20px';
            console.style.width = '600px';
            console.style.height = '400px';
            button.innerHTML = '<i class="fas fa-window-maximize mr-1"></i>停靠';
            this.isDocked = true;
        }
    }
    
    switchTab(tabName) {
        // 更新标签按钮状态
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'border-cyan-500');
            button.classList.add('border-transparent');
        });
        
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active', 'border-cyan-500');
        document.querySelector(`[data-tab="${tabName}"]`).classList.remove('border-transparent');
        
        // 显示对应内容
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        document.getElementById(`tab-${tabName}`).classList.remove('hidden');
        this.currentTab = tabName;
        
        // 特殊处理某些标签页
        if (tabName === 'memory') {
            this.updateMemoryInfo();
        } else if (tabName === 'network') {
            this.updateNetworkRequests();
        }
    }
    
    interceptConsoleLogs() {
        const originalLog = console.log;
        const originalError = console.error;
        const originalWarn = console.warn;
        
        console.log = (...args) => {
            this.addLog('info', args.join(' '));
            originalLog.apply(console, args);
        };
        
        console.error = (...args) => {
            this.addLog('error', args.join(' '));
            this.errorCount++;
            this.updateLogCounts();
            originalError.apply(console, args);
        };
        
        console.warn = (...args) => {
            this.addLog('warning', args.join(' '));
            this.warningCount++;
            this.updateLogCounts();
            originalWarn.apply(console, args);
        };
        
        // 拦截未捕获的错误
        window.addEventListener('error', (e) => {
            this.addLog('error', `未捕获错误: ${e.message} at ${e.filename}:${e.lineno}`);
            this.errorCount++;
            this.updateLogCounts();
        });
        
        window.addEventListener('unhandledrejection', (e) => {
            this.addLog('error', `未处理的Promise拒绝: ${e.reason}`);
            this.errorCount++;
            this.updateLogCounts();
        });
    }
    
    interceptNetworkRequests() {
        const originalFetch = window.fetch;
        
        window.fetch = async (...args) => {
            const startTime = Date.now();
            const url = args[0];
            
            try {
                const response = await originalFetch.apply(window, args);
                const endTime = Date.now();
                
                this.addNetworkRequest({
                    url: url,
                    method: args[1]?.method || 'GET',
                    status: response.status,
                    duration: endTime - startTime,
                    timestamp: new Date(),
                    type: 'fetch'
                });
                
                return response;
            } catch (error) {
                const endTime = Date.now();
                
                this.addNetworkRequest({
                    url: url,
                    method: args[1]?.method || 'GET',
                    status: 'failed',
                    duration: endTime - startTime,
                    timestamp: new Date(),
                    type: 'fetch',
                    error: error.message
                });
                
                throw error;
            }
        };
    }
    
    addLog(type, message) {
        const timestamp = new Date().toLocaleTimeString();
        const log = { type, message, timestamp, id: Date.now() };
        
        this.logs.unshift(log);
        if (this.logs.length > this.maxLogs) {
            this.logs.pop();
        }
        
        // 更新控制台显示
        if (this.isVisible && this.currentTab === 'console') {
            this.updateConsoleOutput();
        }
    }
    
    addNetworkRequest(request) {
        this.networkRequests.unshift(request);
        if (this.networkRequests.length > 100) {
            this.networkRequests.pop();
        }
        
        // 更新网络显示
        if (this.isVisible && this.currentTab === 'network') {
            this.updateNetworkRequests();
        }
    }
    
    updateConsoleOutput() {
        const output = document.getElementById('console-output');
        output.innerHTML = '';
        
        this.logs.forEach(log => {
            const logElement = document.createElement('div');
            logElement.className = `py-1 px-2 text-xs border-l-2 ${this.getLogColor(log.type)}`;
            
            logElement.innerHTML = `
                <span class="text-gray-400 mr-2">${log.timestamp}</span>
                <span class="${this.getLogTextColor(log.type)}">${log.message}</span>
            `;
            
            output.appendChild(logElement);
        });
        
        output.scrollTop = 0;
    }
    
    updateNetworkRequests() {
        const container = document.getElementById('network-requests');
        container.innerHTML = '';
        
        this.networkRequests.forEach(request => {
            const requestElement = document.createElement('div');
            requestElement.className = 'bg-gray-800 p-2 rounded border border-gray-700 text-xs';
            
            const statusColor = request.status < 300 ? 'text-green-400' : 
                               request.status < 400 ? 'text-yellow-400' : 'text-red-400';
            
            requestElement.innerHTML = `
                <div class="flex justify-between items-center mb-1">
                    <span class="text-cyan-400 font-mono">${request.method}</span>
                    <span class="${statusColor}">${request.status}</span>
                    <span class="text-gray-400">${request.duration}ms</span>
                </div>
                <div class="text-white truncate">${request.url}</div>
                <div class="text-gray-400 text-xs">${request.timestamp.toLocaleTimeString()}</div>
                ${request.error ? `<div class="text-red-400 text-xs mt-1">错误: ${request.error}</div>` : ''}
            `;
            
            container.appendChild(requestElement);
        });
    }
    
    updateMemoryInfo() {
        const container = document.getElementById('memory-info');
        
        if (performance.memory) {
            const used = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
            const total = Math.round(performance.memory.totalJSHeapSize / 1024 / 1024);
            const limit = Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024);
            
            container.innerHTML = `
                <div class="flex justify-between">
                    <span>已使用:</span>
                    <span class="text-yellow-400">${used} MB</span>
                </div>
                <div class="flex justify-between">
                    <span>总计:</span>
                    <span class="text-blue-400">${total} MB</span>
                </div>
                <div class="flex justify-between">
                    <span>限制:</span>
                    <span class="text-green-400">${limit} MB</span>
                </div>
                <div class="mt-2">
                    <div class="bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: ${(used/limit)*100}%"></div>
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = '<div class="text-gray-400">内存信息不可用</div>';
        }
    }
    
    updateSystemStatus() {
        const container = document.getElementById('system-status');
        
        const status = [];
        
        // 检查各系统状态
        if (window.systemIntegrationManager) {
            status.push('<div class="text-green-400">✓ 系统集成管理器</div>');
        } else {
            status.push('<div class="text-red-400">✗ 系统集成管理器</div>');
        }
        
        if (window.audioEnhancer) {
            status.push('<div class="text-green-400">✓ 音效系统</div>');
        } else {
            status.push('<div class="text-yellow-400">⚠ 音效系统</div>');
        }
        
        if (window.gestureInteraction) {
            status.push('<div class="text-green-400">✓ 手势交互</div>');
        } else {
            status.push('<div class="text-yellow-400">⚠ 手势交互</div>');
        }
        
        if (window.dataVisualization) {
            status.push('<div class="text-green-400">✓ 数据可视化</div>');
        } else {
            status.push('<div class="text-yellow-400">⚠ 数据可视化</div>');
        }
        
        if (window.socialCustomization) {
            status.push('<div class="text-green-400">✓ 社交自定义</div>');
        } else {
            status.push('<div class="text-yellow-400">⚠ 社交自定义</div>');
        }
        
        container.innerHTML = status.join('');
    }
    
    updateLoadedScripts() {
        const container = document.getElementById('loaded-scripts');
        const scripts = Array.from(document.querySelectorAll('script[src]'));
        
        container.innerHTML = scripts.map(script => {
            const src = script.src;
            const filename = src.split('/').pop();
            return `<div class="text-cyan-400 hover:text-cyan-300 cursor-pointer truncate" title="${src}">${filename}</div>`;
        }).join('');
    }
    
    updateLogCounts() {
        document.getElementById('error-count').textContent = `错误: ${this.errorCount}`;
        document.getElementById('warning-count').textContent = `警告: ${this.warningCount}`;
        document.getElementById('info-count').textContent = `信息: ${this.logs.filter(log => log.type === 'info').length}`;
    }
    
    executeCommand(command) {
        this.addLog('info', `> ${command}`);
        
        try {
            const result = eval(command);
            this.addLog('info', `< ${JSON.stringify(result, null, 2)}`);
        } catch (error) {
            this.addLog('error', `< 错误: ${error.message}`);
        }
    }
    
    clearLogs() {
        this.logs = [];
        this.errorCount = 0;
        this.warningCount = 0;
        this.updateLogCounts();
        if (this.currentTab === 'console') {
            this.updateConsoleOutput();
        }
    }
    
    startProfiling() {
        
        document.getElementById('start-profiling').disabled = true;
        document.getElementById('stop-profiling').disabled = false;
        // 这里可以集成真实的性能分析工具
    }
    
    stopProfiling() {
        
        document.getElementById('start-profiling').disabled = false;
        document.getElementById('stop-profiling').disabled = true;
    }
    
    exportProfile() {
        const data = {
            timestamp: new Date().toISOString(),
            logs: this.logs,
            networkRequests: this.networkRequests,
            performanceData: this.performanceData,
            systemMetrics: this.systemMetrics
        };
        
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `debug-profile-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    forceGarbageCollection() {
        if (window.gc) {
            window.gc();
            this.addLog('info', '强制垃圾回收已执行');
        } else {
            this.addLog('warning', '垃圾回收不可用（需要在启动时添加 --expose-gc 标志）');
        }
        
        // 更新内存信息
        if (this.currentTab === 'memory') {
            setTimeout(() => this.updateMemoryInfo(), 100);
        }
    }
    
    addBreakpoint(target) {
        if (!target) return;
        
        const container = document.getElementById('breakpoints-list');
        const breakpoint = document.createElement('div');
        breakpoint.className = 'flex justify-between items-center bg-gray-700 p-2 rounded';
        breakpoint.innerHTML = `
            <span class="text-cyan-400">${target}</span>
            <button class="text-red-400 hover:text-red-300" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(breakpoint);
        this.addLog('info', `断点已添加: ${target}`);
    }
    
    startPerformanceMonitoring() {
        setInterval(() => {
            if (this.isVisible) {
                // 更新性能指标
                this.measureCurrentPerformance();
            }
        }, 1000);
    }
    
    async measureCurrentPerformance() {
        // 测量FPS
        const fps = await this.measureFPS();
        document.getElementById('current-fps').textContent = fps;
        
        // 测量内存
        if (performance.memory) {
            const memory = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
            document.getElementById('current-memory').textContent = `${memory} MB`;
        }
        
        // 测量延迟
        const latency = await this.measureLatency();
        document.getElementById('current-latency').textContent = `${latency} ms`;
        
        // 更新统计数据
        this.updateActivityStats();
    }
    
    async measureFPS() {
        return new Promise((resolve) => {
            let frames = 0;
            const startTime = performance.now();
            
            function countFrame() {
                frames++;
                if (performance.now() - startTime < 1000) {
                    requestAnimationFrame(countFrame);
                } else {
                    resolve(frames);
                }
            }
            
            requestAnimationFrame(countFrame);
        });
    }
    
    async measureLatency() {
        return new Promise((resolve) => {
            const start = performance.now();
            requestAnimationFrame(() => {
                resolve(Math.round(performance.now() - start));
            });
        });
    }
    
    updateActivityStats() {
        document.getElementById('api-requests').textContent = this.networkRequests.length;
        document.getElementById('user-interactions').textContent = Math.floor(Math.random() * 100);
        document.getElementById('animation-frames').textContent = Math.floor(Math.random() * 1000);
    }
    
    getLogColor(type) {
        switch (type) {
            case 'error': return 'border-red-500';
            case 'warning': return 'border-yellow-500';
            case 'info': return 'border-blue-500';
            default: return 'border-gray-500';
        }
    }
    
    getLogTextColor(type) {
        switch (type) {
            case 'error': return 'text-red-400';
            case 'warning': return 'text-yellow-400';
            case 'info': return 'text-blue-400';
            default: return 'text-white';
        }
    }
}

// 全局初始化
window.advancedDebugConsole = new AdvancedDebugConsole();


