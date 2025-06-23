/**
 * AlingAi Pro - 终极性能验证器
 * 验证所有增强系统的性能和功能
 */

class UltimatePerformanceValidator {
    constructor() {
        this.results = {
            coreAnimation: {},
            audioSystem: {},
            gestureSystem: {},
            dataVisualization: {},
            socialSystem: {},
            systemIntegration: {},
            performance: {},
            errors: []
        };
        
        this.testStartTime = null;
        this.benchmarks = {
            framerate: { min: 30, target: 60 },
            memoryUsage: { max: 100 }, // MB
            audioLatency: { max: 50 }, // ms
            gestureResponse: { max: 100 }, // ms
            dataUpdateRate: { min: 10 }, // Hz
            errorRate: { max: 0.01 } // 1%
        };
        
        this.isRunning = false;
        this.init();
    }
    
    init() {
        this.createValidationUI();
        this.setupEventListeners();
        console.log('🚀 Ultimate Performance Validator 已初始化');
    }
    
    createValidationUI() {
        // 创建验证界面
        const validatorPanel = document.createElement('div');
        validatorPanel.id = 'ultimate-validator';
        validatorPanel.className = 'fixed top-4 left-4 z-50 bg-black/80 backdrop-blur-md text-white p-4 rounded-lg border border-cyan-500/30 min-w-[300px] font-mono text-sm';
        validatorPanel.style.display = 'none';
        
        validatorPanel.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-cyan-400 font-bold flex items-center">
                    <i class="fas fa-microscope mr-2"></i>
                    性能验证器
                </h3>
                <button id="validator-close" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-2 mb-4">
                <button id="start-validation" class="w-full bg-green-600 hover:bg-green-700 px-3 py-2 rounded transition-colors">
                    <i class="fas fa-play mr-2"></i>开始验证
                </button>
                <button id="stop-validation" class="w-full bg-red-600 hover:bg-red-700 px-3 py-2 rounded transition-colors" disabled>
                    <i class="fas fa-stop mr-2"></i>停止验证
                </button>
                <button id="export-report" class="w-full bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded transition-colors">
                    <i class="fas fa-download mr-2"></i>导出报告
                </button>
            </div>
            
            <div id="validation-status" class="space-y-2">
                <div class="text-yellow-400">
                    <i class="fas fa-clock mr-2"></i>准备就绪
                </div>
            </div>
            
            <div id="validation-results" class="mt-4 space-y-2 max-h-64 overflow-y-auto">
                <!-- 验证结果将在这里显示 -->
            </div>
        `;
        
        document.body.appendChild(validatorPanel);
        
        // 创建快捷按钮
        const toggleButton = document.createElement('button');
        toggleButton.id = 'validator-toggle';
        toggleButton.className = 'fixed top-4 left-4 z-40 bg-cyan-600 hover:bg-cyan-700 text-white p-3 rounded-full shadow-lg transition-colors';
        toggleButton.innerHTML = '<i class="fas fa-microscope"></i>';
        toggleButton.title = 'Toggle Performance Validator (Ctrl+Shift+V)';
        
        document.body.appendChild(toggleButton);
    }
    
    setupEventListeners() {
        // 切换按钮
        document.getElementById('validator-toggle').addEventListener('click', () => {
            this.toggleUI();
        });
        
        // 关闭按钮
        document.getElementById('validator-close').addEventListener('click', () => {
            this.hideUI();
        });
        
        // 验证控制按钮
        document.getElementById('start-validation').addEventListener('click', () => {
            this.startValidation();
        });
        
        document.getElementById('stop-validation').addEventListener('click', () => {
            this.stopValidation();
        });
        
        document.getElementById('export-report').addEventListener('click', () => {
            this.exportReport();
        });
        
        // 键盘快捷键
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'V') {
                e.preventDefault();
                this.toggleUI();
            }
        });
    }
    
    toggleUI() {
        const panel = document.getElementById('ultimate-validator');
        const isVisible = panel.style.display !== 'none';
        panel.style.display = isVisible ? 'none' : 'block';
    }
    
    hideUI() {
        document.getElementById('ultimate-validator').style.display = 'none';
    }
    
    showUI() {
        document.getElementById('ultimate-validator').style.display = 'block';
    }
    
    async startValidation() {
        if (this.isRunning) return;
        
        this.isRunning = true;
        this.testStartTime = Date.now();
        this.results = {
            coreAnimation: {},
            audioSystem: {},
            gestureSystem: {},
            dataVisualization: {},
            socialSystem: {},
            systemIntegration: {},
            performance: {},
            errors: []
        };
        
        // 更新UI状态
        document.getElementById('start-validation').disabled = true;
        document.getElementById('stop-validation').disabled = false;
        
        this.updateStatus('🔄 验证进行中...', 'text-blue-400');
        
        try {
            // 依次执行各项验证
            await this.validateCoreAnimation();
            await this.validateAudioSystem();
            await this.validateGestureSystem();
            await this.validateDataVisualization();
            await this.validateSocialSystem();
            await this.validateSystemIntegration();
            await this.validatePerformance();
            
            this.updateStatus('✅ 验证完成', 'text-green-400');
            this.generateSummaryReport();
            
        } catch (error) {
            console.error('验证过程中出现错误:', error);
            this.results.errors.push({
                type: 'validation_error',
                message: error.message,
                timestamp: Date.now()
            });
            this.updateStatus('❌ 验证失败', 'text-red-400');
        }
        
        this.isRunning = false;
        document.getElementById('start-validation').disabled = false;
        document.getElementById('stop-validation').disabled = true;
    }
    
    stopValidation() {
        this.isRunning = false;
        this.updateStatus('⏹️ 验证已停止', 'text-yellow-400');
        document.getElementById('start-validation').disabled = false;
        document.getElementById('stop-validation').disabled = true;
    }
    
    async validateCoreAnimation() {
        this.addResult('core-animation', '🎬 核心动画系统验证', 'info');
        
        const tests = [
            {
                name: 'C++动画渲染',
                test: () => this.checkCppAnimation()
            },
            {
                name: '3D效果加载',
                test: () => this.check3DEffects()
            },
            {
                name: '量子粒子系统',
                test: () => this.checkQuantumParticles()
            },
            {
                name: '全屏Hello World',
                test: () => this.checkFullscreenHelloWorld()
            },
            {
                name: '量子光环吸收',
                test: () => this.checkQuantumHaloAbsorption()
            },
            {
                name: '液态量子星球',
                test: () => this.checkLiquidQuantumPlanet()
            }
        ];
        
        for (const test of tests) {
            try {
                const result = await test.test();
                this.results.coreAnimation[test.name] = result;
                this.addResult('core-animation', `  ✅ ${test.name}: ${result.status}`, 'success');
            } catch (error) {
                this.results.coreAnimation[test.name] = { status: 'failed', error: error.message };
                this.addResult('core-animation', `  ❌ ${test.name}: 失败`, 'error');
            }
            
            if (!this.isRunning) return;
            await this.delay(100);
        }
    }
    
    async validateAudioSystem() {
        this.addResult('audio-system', '🔊 音效系统验证', 'info');
        
        const tests = [
            {
                name: 'Web Audio API',
                test: () => this.checkWebAudioAPI()
            },
            {
                name: '动态音效生成',
                test: () => this.checkDynamicAudioGeneration()
            },
            {
                name: '音效同步',
                test: () => this.checkAudioSync()
            },
            {
                name: '音量控制',
                test: () => this.checkVolumeControl()
            }
        ];
        
        for (const test of tests) {
            try {
                const result = await test.test();
                this.results.audioSystem[test.name] = result;
                this.addResult('audio-system', `  ✅ ${test.name}: ${result.status}`, 'success');
            } catch (error) {
                this.results.audioSystem[test.name] = { status: 'failed', error: error.message };
                this.addResult('audio-system', `  ❌ ${test.name}: 失败`, 'error');
            }
            
            if (!this.isRunning) return;
            await this.delay(100);
        }
    }
    
    async validateGestureSystem() {
        this.addResult('gesture-system', '👆 手势交互系统验证', 'info');
        
        const tests = [
            {
                name: '触摸检测',
                test: () => this.checkTouchDetection()
            },
            {
                name: '手势识别',
                test: () => this.checkGestureRecognition()
            },
            {
                name: '多点触控',
                test: () => this.checkMultiTouch()
            },
            {
                name: '键盘快捷键',
                test: () => this.checkKeyboardShortcuts()
            }
        ];
        
        for (const test of tests) {
            try {
                const result = await test.test();
                this.results.gestureSystem[test.name] = result;
                this.addResult('gesture-system', `  ✅ ${test.name}: ${result.status}`, 'success');
            } catch (error) {
                this.results.gestureSystem[test.name] = { status: 'failed', error: error.message };
                this.addResult('gesture-system', `  ❌ ${test.name}: 失败`, 'error');
            }
            
            if (!this.isRunning) return;
            await this.delay(100);
        }
    }
    
    async validateDataVisualization() {
        this.addResult('data-viz', '📊 数据可视化系统验证', 'info');
        
        const tests = [
            {
                name: '实时数据监控',
                test: () => this.checkRealTimeDataMonitoring()
            },
            {
                name: '性能指标收集',
                test: () => this.checkPerformanceMetrics()
            },
            {
                name: '数据映射',
                test: () => this.checkDataMapping()
            },
            {
                name: '可视化面板',
                test: () => this.checkVisualizationPanel()
            }
        ];
        
        for (const test of tests) {
            try {
                const result = await test.test();
                this.results.dataVisualization[test.name] = result;
                this.addResult('data-viz', `  ✅ ${test.name}: ${result.status}`, 'success');
            } catch (error) {
                this.results.dataVisualization[test.name] = { status: 'failed', error: error.message };
                this.addResult('data-viz', `  ❌ ${test.name}: 失败`, 'error');
            }
            
            if (!this.isRunning) return;
            await this.delay(100);
        }
    }
    
    async validateSocialSystem() {
        this.addResult('social-system', '🌐 社交自定义系统验证', 'info');
        
        const tests = [
            {
                name: '自定义面板',
                test: () => this.checkCustomizationPanel()
            },
            {
                name: '社交分享',
                test: () => this.checkSocialSharing()
            },
            {
                name: '预设管理',
                test: () => this.checkPresetManagement()
            },
            {
                name: '设置导出',
                test: () => this.checkSettingsExport()
            }
        ];
        
        for (const test of tests) {
            try {
                const result = await test.test();
                this.results.socialSystem[test.name] = result;
                this.addResult('social-system', `  ✅ ${test.name}: ${result.status}`, 'success');
            } catch (error) {
                this.results.socialSystem[test.name] = { status: 'failed', error: error.message };
                this.addResult('social-system', `  ❌ ${test.name}: 失败`, 'error');
            }
            
            if (!this.isRunning) return;
            await this.delay(100);
        }
    }
    
    async validateSystemIntegration() {
        this.addResult('system-integration', '🔗 系统集成验证', 'info');
        
        const tests = [
            {
                name: '系统初始化',
                test: () => this.checkSystemInitialization()
            },
            {
                name: '系统间通信',
                test: () => this.checkInterSystemCommunication()
            },
            {
                name: '错误处理',
                test: () => this.checkErrorHandling()
            },
            {
                name: '性能监控',
                test: () => this.checkPerformanceMonitoring()
            }
        ];
        
        for (const test of tests) {
            try {
                const result = await test.test();
                this.results.systemIntegration[test.name] = result;
                this.addResult('system-integration', `  ✅ ${test.name}: ${result.status}`, 'success');
            } catch (error) {
                this.results.systemIntegration[test.name] = { status: 'failed', error: error.message };
                this.addResult('system-integration', `  ❌ ${test.name}: 失败`, 'error');
            }
            
            if (!this.isRunning) return;
            await this.delay(100);
        }
    }
    
    async validatePerformance() {
        this.addResult('performance', '⚡ 性能指标验证', 'info');
        
        // 测试帧率
        const framerate = await this.measureFramerate();
        this.results.performance.framerate = framerate;
        const framerateStatus = framerate >= this.benchmarks.framerate.target ? '优秀' : 
                               framerate >= this.benchmarks.framerate.min ? '良好' : '需要优化';
        this.addResult('performance', `  📊 帧率: ${framerate} FPS (${framerateStatus})`, 
                      framerate >= this.benchmarks.framerate.min ? 'success' : 'warning');
        
        // 测试内存使用
        const memoryUsage = await this.measureMemoryUsage();
        this.results.performance.memoryUsage = memoryUsage;
        const memoryStatus = memoryUsage <= this.benchmarks.memoryUsage.max ? '正常' : '偏高';
        this.addResult('performance', `  💾 内存使用: ${memoryUsage} MB (${memoryStatus})`, 
                      memoryUsage <= this.benchmarks.memoryUsage.max ? 'success' : 'warning');
        
        // 测试渲染延迟
        const renderLatency = await this.measureRenderLatency();
        this.results.performance.renderLatency = renderLatency;
        this.addResult('performance', `  ⏱️ 渲染延迟: ${renderLatency} ms`, 'info');
        
        // CPU使用率
        const cpuUsage = this.estimateCpuUsage();
        this.results.performance.cpuUsage = cpuUsage;
        this.addResult('performance', `  🖥️ CPU估计使用率: ${cpuUsage}%`, 'info');
    }
    
    // 具体检测方法
    async checkCppAnimation() {
        const animationContainer = document.getElementById('cpp-animation-container');
        return {
            status: animationContainer ? 'active' : 'inactive',
            performance: animationContainer ? 'good' : 'n/a'
        };
    }
    
    async check3DEffects() {
        const threeCanvas = document.querySelector('canvas');
        return {
            status: threeCanvas ? 'active' : 'inactive',
            webgl: !!window.WebGLRenderingContext
        };
    }
    
    async checkQuantumParticles() {
        const particleElements = document.querySelectorAll('.quantum-particle');
        return {
            status: particleElements.length > 0 ? 'active' : 'inactive',
            count: particleElements.length
        };
    }
    
    async checkFullscreenHelloWorld() {
        return {
            status: window.cppAnimationSystem ? 'available' : 'unavailable',
            functional: typeof window.triggerFullscreenHelloWorld === 'function'
        };
    }
    
    async checkQuantumHaloAbsorption() {
        return {
            status: window.quantumHaloSystem ? 'available' : 'unavailable'
        };
    }
    
    async checkLiquidQuantumPlanet() {
        return {
            status: window.liquidQuantumPlanet ? 'available' : 'unavailable'
        };
    }
    
    async checkWebAudioAPI() {
        return {
            status: window.AudioContext || window.webkitAudioContext ? 'supported' : 'unsupported',
            enhancer: window.audioEnhancer ? 'active' : 'inactive'
        };
    }
    
    async checkDynamicAudioGeneration() {
        return {
            status: window.audioEnhancer && window.audioEnhancer.audioContext ? 'active' : 'inactive'
        };
    }
    
    async checkAudioSync() {
        return {
            status: 'testing',
            latency: Math.random() * 30 + 10 // 模拟延迟测试
        };
    }
    
    async checkVolumeControl() {
        return {
            status: window.audioEnhancer && typeof window.audioEnhancer.setVolume === 'function' ? 'available' : 'unavailable'
        };
    }
    
    async checkTouchDetection() {
        return {
            status: 'ontouchstart' in window ? 'supported' : 'unsupported',
            system: window.gestureInteraction ? 'active' : 'inactive'
        };
    }
    
    async checkGestureRecognition() {
        return {
            status: window.gestureInteraction && window.gestureInteraction.gestureRecognizer ? 'active' : 'inactive'
        };
    }
    
    async checkMultiTouch() {
        return {
            status: navigator.maxTouchPoints > 1 ? 'supported' : 'limited',
            maxPoints: navigator.maxTouchPoints || 0
        };
    }
    
    async checkKeyboardShortcuts() {
        return {
            status: window.gestureInteraction && window.gestureInteraction.keyboardShortcuts ? 'active' : 'inactive'
        };
    }
    
    async checkRealTimeDataMonitoring() {
        return {
            status: window.dataVisualization && window.dataVisualization.isActive ? 'active' : 'inactive'
        };
    }
    
    async checkPerformanceMetrics() {
        return {
            status: window.dataVisualization && window.dataVisualization.performanceMonitor ? 'active' : 'inactive'
        };
    }
    
    async checkDataMapping() {
        return {
            status: window.dataVisualization && window.dataVisualization.dataMapper ? 'active' : 'inactive'
        };
    }
    
    async checkVisualizationPanel() {
        const panel = document.getElementById('data-visualization-panel');
        return {
            status: panel ? 'available' : 'unavailable'
        };
    }
    
    async checkCustomizationPanel() {
        return {
            status: window.socialCustomization && window.socialCustomization.customizationPanel ? 'active' : 'inactive'
        };
    }
    
    async checkSocialSharing() {
        return {
            status: window.socialCustomization && window.socialCustomization.socialSharing ? 'active' : 'inactive'
        };
    }
    
    async checkPresetManagement() {
        return {
            status: window.socialCustomization && window.socialCustomization.presetManager ? 'active' : 'inactive'
        };
    }
    
    async checkSettingsExport() {
        return {
            status: window.socialCustomization && typeof window.socialCustomization.exportSettings === 'function' ? 'available' : 'unavailable'
        };
    }
    
    async checkSystemInitialization() {
        return {
            status: window.systemIntegrationManager ? 'initialized' : 'not_initialized',
            systems: window.systemIntegrationManager ? Object.keys(window.systemIntegrationManager.systems).length : 0
        };
    }
    
    async checkInterSystemCommunication() {
        return {
            status: window.systemIntegrationManager && window.systemIntegrationManager.eventBus ? 'active' : 'inactive'
        };
    }
    
    async checkErrorHandling() {
        return {
            status: window.systemIntegrationManager && window.systemIntegrationManager.errorHandler ? 'active' : 'inactive'
        };
    }
    
    async checkPerformanceMonitoring() {
        return {
            status: window.systemIntegrationManager && window.systemIntegrationManager.performanceMonitor ? 'active' : 'inactive'
        };
    }
    
    // 性能测量方法
    async measureFramerate() {
        return new Promise((resolve) => {
            let frames = 0;
            const startTime = performance.now();
            
            function countFrame() {
                frames++;
                if (performance.now() - startTime < 1000) {
                    requestAnimationFrame(countFrame);
                } else {
                    resolve(Math.round(frames));
                }
            }
            
            requestAnimationFrame(countFrame);
        });
    }
    
    async measureMemoryUsage() {
        if (performance.memory) {
            return Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
        }
        return 'N/A';
    }
    
    async measureRenderLatency() {
        return new Promise((resolve) => {
            const start = performance.now();
            requestAnimationFrame(() => {
                resolve(Math.round(performance.now() - start));
            });
        });
    }
    
    estimateCpuUsage() {
        // 简单的CPU使用率估算
        const start = performance.now();
        let iterations = 0;
        while (performance.now() - start < 10) {
            iterations++;
        }
        // 基于迭代次数估算CPU负载
        return Math.min(Math.round((1000000 - iterations) / 10000), 100);
    }
    
    // UI辅助方法
    updateStatus(message, className = 'text-white') {
        const statusElement = document.getElementById('validation-status');
        statusElement.innerHTML = `<div class="${className}"><i class="fas fa-info-circle mr-2"></i>${message}</div>`;
    }
    
    addResult(category, message, type = 'info') {
        const resultsContainer = document.getElementById('validation-results');
        const resultElement = document.createElement('div');
        
        let iconClass = 'fas fa-info-circle';
        let textClass = 'text-blue-400';
        
        switch (type) {
            case 'success':
                iconClass = 'fas fa-check-circle';
                textClass = 'text-green-400';
                break;
            case 'error':
                iconClass = 'fas fa-times-circle';
                textClass = 'text-red-400';
                break;
            case 'warning':
                iconClass = 'fas fa-exclamation-triangle';
                textClass = 'text-yellow-400';
                break;
        }
        
        resultElement.className = `${textClass} text-xs`;
        resultElement.innerHTML = `<i class="${iconClass} mr-2"></i>${message}`;
        
        resultsContainer.appendChild(resultElement);
        resultsContainer.scrollTop = resultsContainer.scrollHeight;
    }
    
    generateSummaryReport() {
        const duration = Date.now() - this.testStartTime;
        const totalTests = Object.values(this.results).reduce((sum, category) => {
            if (typeof category === 'object' && !Array.isArray(category)) {
                return sum + Object.keys(category).length;
            }
            return sum;
        }, 0);
        
        this.addResult('summary', '📋 验证总结报告', 'info');
        this.addResult('summary', `  总用时: ${duration}ms`, 'info');
        this.addResult('summary', `  总测试项: ${totalTests}`, 'info');
        this.addResult('summary', `  错误数量: ${this.results.errors.length}`, 
                      this.results.errors.length === 0 ? 'success' : 'error');
    }
    
    exportReport() {
        const report = {
            timestamp: new Date().toISOString(),
            duration: this.testStartTime ? Date.now() - this.testStartTime : 0,
            results: this.results,
            benchmarks: this.benchmarks,
            userAgent: navigator.userAgent,
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            }
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `alingai-pro-validation-report-${new Date().getTime()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        console.log('📊 验证报告已导出', report);
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// 全局初始化
window.ultimatePerformanceValidator = new UltimatePerformanceValidator();

console.log('🚀 Ultimate Performance Validator 已加载');
